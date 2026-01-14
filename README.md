# CakePhpViteHelper for CakePHP 5

[![CakePHP 5](https://img.shields.io/badge/CakePHP-5.x-red.svg)](https://cakephp.org)
[![PHP 8.1+](https://img.shields.io/badge/PHP-8.1%2B-blue.svg)](https://php.net)
[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Integrate Vite with CakePHP 5 for modern frontend asset bundling with hot module replacement.

## Features

- Easy asset mapping (build/resources) with application
- Hot Module Replacement (HMR) with live reload
- DDEV support out of the box
- CLI command for quick setup

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | >= 8.1 |
| CakePHP | ^5.0 |
| Node.js | >= 18.0 |

## Installation

### Step 1: Add Repository

Add the GitHub repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/CakePHPMitra/Vite-Plugin"
        }
    ]
}
```

### Step 2: Install via Composer

```bash
composer require cakephpmitra/vite-plugin:dev-main
```

### Step 3: Load Plugin

**Option A - Using CLI:**

```bash
bin/cake plugin load CakePhpViteHelper
```

**Option B - In Application.php:**

```php
// In src/Application.php bootstrap() method
$this->addPlugin('CakePhpViteHelper');
```

### Step 4: Setup Vite

Install Node packages and generate Vite configuration:

```bash
bin/cake vite-helper install
```

## Quick Start

### 1. Create Resources

Create `resources/js/app.js`:
```js
console.log("Welcome to CakePHP!");
```

Create `resources/css/app.css`:
```css
body {
  background-color: skyblue;
}
```

### 2. Include in Layout

```php
// templates/layout/default.php
<?= $this->Vite->asset(['resources/js/app.js', 'resources/css/app.css']) ?>
```

### 3. Run Vite

Development with HMR:
```bash
npm run dev
```

Production build:
```bash
npm run build
```

## Documentation

See the [docs](docs/) folder for detailed documentation:

- [Features](docs/features/) - Usage and helper methods
- [Development](docs/development/) - Configuration and DDEV setup

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Issues

Report bugs and feature requests on the [Issue Tracker](https://github.com/CakePHPMitra/Vite-Plugin/issues).

## Author

[Atul Mahankal](https://atulmahankal.github.io/atulmahankal/)

## License

MIT License - see [LICENSE](LICENSE) file.
