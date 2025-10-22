<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use RuntimeException;

class UseEnvironmentCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'env:use
        {environment? : The environment key to activate}
        {--list : List available environments}
        {--force : Overwrite the existing .env without confirmation}
        {--dry-run : Output the merged environment instead of writing .env}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the .env file from the environment templates';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $directory = config('environments.directory');
        $environments = config('environments.environments', []);

        if (! File::isDirectory($directory)) {
            throw new RuntimeException("Environment directory [{$directory}] does not exist.");
        }

        if ($this->option('list')) {
            $this->listEnvironments($environments, $directory);

            return self::SUCCESS;
        }

        $key = $this->argument('environment');

        if ($key === null) {
            $key = $this->promptForEnvironmentKey($environments);
        }

        if (! Arr::exists($environments, $key)) {
            $this->components->error("Unknown environment [{$key}].");

            $this->listEnvironments($environments, $directory);

            return self::INVALID;
        }

        $baseFile = $directory.DIRECTORY_SEPARATOR.'base.env';
        $environmentFile = $directory.DIRECTORY_SEPARATOR.$environments[$key]['file'];

        $this->ensureFileExists($baseFile);
        $this->ensureFileExists($environmentFile);

        $output = $this->buildEnvironment($baseFile, $environmentFile, $key);

        if ($this->option('dry-run')) {
            $this->line($output);

            return self::SUCCESS;
        }

        $target = base_path('.env');

        if (! $this->option('force') && File::exists($target)) {
            if (File::get($target) === $output) {
                $this->components->info('.env is already up to date.');

                return self::SUCCESS;
            }

            if (! $this->components->confirm('Overwrite the existing .env file?', true)) {
                $this->components->warn('No changes were written.');

                return self::SUCCESS;
            }
        }

        File::put($target, $output);

        $this->components->info("Environment [{$key}] applied to .env");

        return self::SUCCESS;
    }

    /**
     * Prompt the user to choose an environment key.
     */
    protected function promptForEnvironmentKey(array $environments): string
    {
        if (empty($environments)) {
            throw new RuntimeException('No environments are registered in config/environments.php.');
        }

        return $this->choice(
            'Select an environment',
            array_keys($environments),
            0
        );
    }

    /**
     * Ensure the given file exists.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function ensureFileExists(string $path): void
    {
        if (! File::exists($path)) {
            throw new FileNotFoundException("Environment definition file [{$path}] not found.");
        }
    }

    /**
     * Build the merged environment content.
     */
    protected function buildEnvironment(string $baseFile, string $environmentFile, string $key): string
    {
        $baseLines = File::lines($baseFile)->toArray();
        $baseLines = $this->stripLeadingCommentBlock($baseLines);

        $overrides = $this->parseEnvFile($environmentFile);

        $output = $this->buildHeader($key);

        foreach ($baseLines as $line) {
            if (! $this->isKeyValueLine($line)) {
                $output[] = $line;
                continue;
            }

            [$envKey] = explode('=', $line, 2);
            $envKey = rtrim($envKey);

            if (array_key_exists($envKey, $overrides)) {
                $line = $envKey.'='.$overrides[$envKey];
                unset($overrides[$envKey]);
            }

            $output[] = $line;
        }

        if (! empty($overrides)) {
            $output[] = '';
            $output[] = '# -----------------------------------------------------------------------------';
            $output[] = '# Additional Environment Overrides';

            foreach ($overrides as $envKey => $value) {
                $output[] = $envKey.'='.$value;
            }
        }

        return rtrim(implode(PHP_EOL, $output)).PHP_EOL;
    }

    /**
     * Parse environment key/value pairs from the given file.
     */
    protected function parseEnvFile(string $path): array
    {
        $variables = [];

        foreach (File::lines($path) as $line) {
            $line = rtrim($line, "\r\n");
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (! str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $variables[rtrim($key)] = ltrim($value);
        }

        return $variables;
    }

    /**
     * Determine if the line represents a key/value entry.
     */
    protected function isKeyValueLine(string $line): bool
    {
        $trimmed = ltrim($line);

        return $trimmed !== '' && ! str_starts_with($trimmed, '#') && str_contains($line, '=');
    }

    /**
     * Strip the leading comment block from the base file.
     *
     * @param  array<int, string>  $lines
     * @return array<int, string>
     */
    protected function stripLeadingCommentBlock(array $lines): array
    {
        $removedHeader = false;

        while (! empty($lines)) {
            $first = ltrim($lines[0]);

            if ($first === '' && $removedHeader) {
                array_shift($lines);

                break;
            }

            if ($first === '' && ! $removedHeader) {
                array_shift($lines);

                continue;
            }

            if (str_starts_with($first, '#')) {
                $removedHeader = true;
                array_shift($lines);

                continue;
            }

            break;
        }

        return $lines;
    }

    /**
     * Build the file header comment.
     *
     * @return array<int, string>
     */
    protected function buildHeader(string $environment): array
    {
        $upper = strtoupper($environment);

        return [
            '# =============================================================================',
            "# GENERATED ENVIRONMENT FILE ({$upper})",
            '# -----------------------------------------------------------------------------',
            "# Managed via `php artisan env:use {$environment}`. Update environments/base.env",
            "# and environments/{$environment}.env to change values.",
            '# =============================================================================',
            '',
        ];
    }

    /**
     * Render the registered environment list.
     */
    protected function listEnvironments(array $environments, string $directory): void
    {
        if (empty($environments)) {
            $this->components->warn('No environment definitions were found.');

            return;
        }

        $this->components->info('Available environments:');

        foreach ($environments as $key => $meta) {
            $label = $meta['label'] ?? ucfirst($key);
            $description = $meta['description'] ?? '';
            $file = $directory.DIRECTORY_SEPARATOR.$meta['file'];

            $status = File::exists($file) ? '✓' : '✗';

            $this->line(sprintf(
                '  [%s] %s %s%s',
                $key,
                $status,
                $label,
                $description ? " — {$description}" : ''
            ));
        }
    }
}
