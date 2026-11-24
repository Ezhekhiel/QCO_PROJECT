<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class ServeDev extends Command
{
    protected $signature = 'dev:serve';
    protected $description = 'Start built-in PHP server using VITE_HOST and APP_PORT from .env';

    public function handle(): int
    {
        $envVars = $this->readDotEnv(base_path('.env'));

        $host = $envVars['VITE_HOST'] ?? $_ENV['VITE_HOST'] ?? $_SERVER['VITE_HOST'] ?? '127.0.0.1';
        $port = $envVars['APP_PORT'] ?? $_ENV['APP_PORT'] ?? $_SERVER['APP_PORT'] ?? '8000';
        $docRoot = public_path();

        $this->info("Starting PHP server at http://{$host}:{$port}");

        $process = new Process(['php', '-S', "{$host}:{$port}", '-t', $docRoot]);
        $process->setTimeout(null);
        $process->setTty(Process::isTtySupported());

        return $process->run(function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });
    }

    /**
     * @return array<string, string>
     */
    private function readDotEnv(string $path): array
    {
        if (! is_file($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $vars = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            if (str_starts_with($trimmed, 'export ')) {
                $trimmed = trim(substr($trimmed, 7));
            }

            $eqPos = strpos($trimmed, '=');
            if ($eqPos === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $eqPos));
            $val = trim(substr($trimmed, $eqPos + 1));

            // Hapus tanda kutip pembungkus jika ada
            if (strlen($val) >= 2) {
                $first = $val[0];
                $last = $val[strlen($val) - 1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                    $val = substr($val, 1, -1);
                }
            }

            if ($key !== '') {
                $vars[$key] = $val;
            }
        }

        return $vars;
    }
}
