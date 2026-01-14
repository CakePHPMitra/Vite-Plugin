# Contributing to CakePhpViteHelper

Thank you for your interest in contributing to CakePhpViteHelper!

## How to Contribute

1. Fork the repository
1. Create a feature/bugfix branch
1. Write tests for new functionality
1. Ensure all tests pass
1. Submit a pull request

## Development Setup

1. Clone the repository into your CakePHP application's `plugins` directory:

   ```bash
   cd /path/to/your-cakephp-app/plugins
   git clone https://github.com/CakePHPMitra/Vite-Plugin.git
   ```

1. Add the autoloader to your application's `composer.json`:

   ```json
   "autoload": {
       "psr-4": {
           "CakePhpViteHelper\\": "plugins/Vite-Plugin/src/"
       }
   }
   ```

1. Install dependencies:

   ```bash
   composer install
   ```

1. Dump the autoloader:

   ```bash
   composer dump-autoload
   ```

1. Load the plugin in your `Application.php`:

   ```php
   public function bootstrap(): void
   {
       parent::bootstrap();
       $this->addPlugin('CakePhpViteHelper');
   }
   ```

## Running Tests

```bash
composer test
```

## Code Style

This project follows CakePHP coding standards. Run the code sniffer before submitting:

```bash
composer cs-check
```

## Pull Request Guidelines

- Keep PRs focused on a single feature or fix
- Update documentation if needed
- Add tests for new functionality
- Ensure all tests pass before submitting

## Reporting Issues

- Use the [Issue Tracker](https://github.com/CakePHPMitra/Vite-Plugin/issues)
- Include steps to reproduce the issue
- Provide CakePHP and PHP version information

## License

By contributing, you agree that your contributions will be licensed under the MIT License.
