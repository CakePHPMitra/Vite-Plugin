<?php

declare(strict_types=1);

namespace CakePhpViteHelper\Command;

use Cake\Core\Configure;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Symfony\Component\Process\Process;

class InstallCommand extends Command
{
    protected const COMMAND_NAME = 'vite-helper install';

    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = self::COMMAND_NAME;

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return self::COMMAND_NAME;
    }

    /**
     * SECURITY: Validate that a path is within the project root.
     * Prevents directory traversal attacks.
     *
     * @param string $path Path to validate
     * @return bool True if path is safe
     */
    protected function isPathSafe(string $path): bool
    {
        $realPath = realpath(dirname($path));
        $realRoot = realpath(ROOT);

        // If directory doesn't exist yet, check parent
        if ($realPath === false) {
            $realPath = realpath(dirname(dirname($path)));
        }

        if ($realPath === false || $realRoot === false) {
            return false;
        }

        // Ensure path is within ROOT
        return str_starts_with($realPath, $realRoot);
    }

    /**
     * SECURITY: Safe file write with path validation
     *
     * @param string $path File path
     * @param string $content Content to write
     * @param ConsoleIo $io Console IO for error messages
     * @return bool True if successful
     */
    protected function safeFileWrite(string $path, string $content, ConsoleIo $io): bool
    {
        if (!$this->isPathSafe($path)) {
            $io->err("Security error: Path '$path' is outside project root");
            return false;
        }

        return file_put_contents($path, $content) !== false;
    }

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser->setDescription('Install and configure Vite for CakePHP project')
            ->addOption('pm', [
                'help' => 'Choose package manager: npm, yarn, pnpm',
                'choices' => ['npm', 'yarn', 'pnpm'],
            ]);

        return $parser;
    }

    private function comment(string $text): string
    {
        return "\033[2m{$text}\033[0m";
    }

    public function execute(Arguments $args, ConsoleIo $io): int
    {
        $pm = $args->getOption('pm');

        // Prompt interactively if not provided
        if (!$pm) {
            $pm = $io->askChoice(
                'Which package manager do you want to use?',
                ['npm', 'yarn', 'pnpm'],
                'npm' // default
            );
        }

        $root = ROOT;
        $packageJsonPath = $root . '/package.json';

        $io->out('<info>⚡ CakePHP Vite Plugin Installer</info>');
        $io->out("📦 Using <info>$pm</info> as package manager");

        // Create package.json if missing
        if (!file_exists($packageJsonPath)) {
            $package = [
                'private' => true,
                'type' => 'module',
                'scripts' => [
                    'dev' => 'vite',
                    'build' => 'vite build',
                    'watch' => 'vite build --watch',
                ],
                'devDependencies' => new \stdClass(),
            ];
            if (!$this->safeFileWrite(
                $packageJsonPath,
                json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                $io
            )) {
                return static::CODE_ERROR;
            }
            $io->success('✅ Created package.json');
        } else {
            // Merge required scripts into existing package.json
            $package = json_decode(file_get_contents($packageJsonPath), true) ?? [];
            $package['private'] = true;
            $package['type'] = 'module';
            $package['scripts'] = array_merge($package['scripts'] ?? [], [
                'dev' => 'vite',
                'build' => 'vite build',
                'watch' => 'vite build --watch',
            ]);
            if (!$this->safeFileWrite(
                $packageJsonPath,
                json_encode($package, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL,
                $io
            )) {
                return static::CODE_ERROR;
            }
            $io->success('✅ Updated package.json with Vite scripts');
        }

        // Install dependencies
        $installCmd = match ($pm) {
            'yarn' => ['yarn', 'add', '-D', 'vite', 'vite-plugin-full-reload', 'https://github.com/mahankals/php-vite-plugin'],
            'pnpm' => ['pnpm', 'add', '-D', 'vite', 'vite-plugin-full-reload', 'https://github.com/mahankals/php-vite-plugin'],
            default => ['npm', 'install', '-D', 'vite', 'vite-plugin-full-reload', 'https://github.com/mahankals/php-vite-plugin'],
        };

        $io->out('');
        $io->out('<info>📦 Installing dependencies...</info>');
        if ($this->runProcess($installCmd, $io) !== 0) {
            $io->err('❌ Failed to install dependencies.');
            return static::CODE_ERROR;
        }

        // Run init
        $io->out('');
        $io->out('<info>⚙️ Initializing php-vite-plugin...</info>');
        if ($this->runProcess(['npx', 'php-vite-plugin', 'init'], $io) !== 0) {
            $io->err('❌ Failed to initialize php-vite-plugin.');
            return static::CODE_ERROR;
        }

        $file = ROOT . '/vite.config.js';
        $content = file_get_contents($file);

        // Define replacements
        $replacements = [
            'const env = loadEnv(mode, path.resolve(__dirname, "."), "");'
            => 'const env = loadEnv(mode, path.resolve(__dirname, "config"), "");',
            'envFile: path.resolve(__dirname, ".env")'
            => 'envFile: path.resolve(__dirname, "config/.env")',
            'publicDir: "public/build",'
            => 'publicDir: "webroot/build",',
            'FullReload(["views/**/*"])'
            => 'FullReload(["templates/**/*"])',
            'host: localIPs["Ethernet"] ?? localIPs["Wi-Fi"] ?? "0.0.0.0"'
            => 'host: process.env.DDEV_HOSTNAME || (localIPs["Ethernet"] ?? localIPs["Wi-Fi"] ?? "0.0.0.0")',
        ];

        // Apply replacements
        $newContent = str_replace(array_keys($replacements), array_values($replacements), $content);

        // Update .gitignore with Vite entries
        $this->updateGitignore($io);

        // Create DDEV config if DDEV is detected
        $this->createDdevConfig($io);

        // Save back vite.config.js
        if (!$this->safeFileWrite($file, $newContent, $io)) {
            $io->err('❌ Failed to save vite.config.js');
            return static::CODE_ERROR;
        }
        $version = Configure::version(); // gets CakePHP version

        $io->out('');
        $io->success("🎉 Vite is now configured for CakePHP $version!");
        $io->comment("👉 Run:");
        $io->out("   bin/cake server   ", 0);
        $io->out($this->comment("# start CakePHP"));
        $io->out("   $pm run dev       ", 0);
        $io->out($this->comment("# start Vite development server."));
        $io->out("   or");
        $io->out("   $pm run build     ", 0);
        $io->out($this->comment("# build assets with Vite."));

        return static::CODE_SUCCESS;
    }

    protected function runProcess(array $cmd, ConsoleIo $io): int
    {
        $process = new Process($cmd, ROOT, null, null, null);
        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        $process->run(function ($type, $buffer) use ($io) {
            $type === Process::ERR ? $io->err($buffer) : $io->out($buffer);
        });

        return $process->getExitCode();
    }

    protected function updateGitignore(ConsoleIo $io): void
    {
        $gitignorePath = ROOT . '/.gitignore';
        $viteEntries = [
            '# Vite',
            'hot',
            '/webroot/build/',
        ];

        $content = file_exists($gitignorePath) ? file_get_contents($gitignorePath) : '';

        // Check if Vite section already exists
        if (str_contains($content, '# Vite')) {
            return;
        }

        // Add Vite entries to .gitignore
        $viteSection = PHP_EOL . implode(PHP_EOL, $viteEntries) . PHP_EOL;
        if (!$this->safeFileWrite($gitignorePath, $content . $viteSection, $io)) {
            $io->err('⚠️  Could not update .gitignore');
            return;
        }

        $io->success('✅ Updated .gitignore with Vite entries');
    }

    protected function createDdevConfig(ConsoleIo $io): void
    {
        $ddevDir = ROOT . '/.ddev';
        if (!file_exists($ddevDir . '/config.yaml')) {
            return; // Not a DDEV project
        }

        $configFile = $ddevDir . '/config.vite.yaml';
        if (file_exists($configFile)) {
            $io->out('ℹ️  DDEV Vite config already exists');
            return;
        }

        $content = <<<YAML
web_extra_exposed_ports:
  - name: vite
    container_port: 5173
    http_port: 5172
    https_port: 5173

hooks:
  post-start:
    - exec: "[ -f package.json ] && npm install || true"
    - exec: "[ -f vite.config.js ] && (npm run dev > /dev/null 2>&1 &) || true"
YAML;

        if (!$this->safeFileWrite($configFile, $content . PHP_EOL, $io)) {
            $io->err('⚠️  Could not create DDEV config');
            return;
        }
        $io->success('✅ Created .ddev/config.vite.yaml');
    }
}
