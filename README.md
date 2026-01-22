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
- Works with and without Docker/DDEV

## Requirements

| Requirement | Version |
|-------------|---------|
| PHP | >= 8.1 |
| CakePHP | ^5.0 |
| Node.js | >= 18.0 |

## Installation

1. Add the repository to your `composer.json`:

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

2. Install via Composer:

```bash
composer require cakephpmitra/vite-plugin:dev-main
```

3. Load the plugin in `config/plugins.php`:

```php
return [
    // ... other plugins
    'CakePhpViteHelper' => [],
];
```

Or via CLI:

```bash
bin/cake plugin load CakePhpViteHelper
```

4. Install Node packages with Vite configuration:

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

## DDEV Setup

If using DDEV, create `.ddev/config.vite.yaml`:

```yaml
web_extra_exposed_ports:
  - name: vite
    container_port: 5173
    http_port: 5172
    https_port: 5173

hooks:
  post-start:
    - exec: "[ -f package.json ] && npm install || true"
    - exec: "[ -f vite.config.js ] && (npm run dev > /dev/null 2>&1 &) && sleep 3 && echo \"https://${DDEV_HOSTNAME}:5173\" > hot || true"
```

Then restart DDEV:

```bash
ddev restart
```

See [DDEV Setup Guide](docs/development/ddev-setup.md) for detailed instructions and troubleshooting.

## How It Works

The `ViteHelper` uses a `hot` file to determine the development server URL:

| Mode | Hot File | Behavior |
|------|----------|----------|
| Development | Exists with URL | Assets loaded from Vite dev server (HMR enabled) |
| Production | Does not exist | Assets loaded from `webroot/build/` (compiled) |

See [Configuration Guide](docs/development/configuration.md) for details.

## Important: Subdirectory Deployments

When deploying your CakePHP app under a subdirectory/alias (e.g., `https://example.com/myapp/`), asset URLs are automatically handled using `Router::url()`.

This ensures assets like `/build/assets/app.js` correctly become `/myapp/build/assets/app.js`.

**No additional configuration required** - the helper auto-detects the base path.

## Documentation

See the [docs](docs/) folder for detailed documentation:

- [Features](docs/features/) - Usage and helper methods
- [Development](docs/development/) - Configuration and DDEV setup
  - [Configuration](docs/development/configuration.md) - Vite and hot file configuration
  - [DDEV Setup](docs/development/ddev-setup.md) - Complete DDEV guide
  - [Production](docs/development/production.md) - Production deployment

## Contributing

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

## Issues

Report bugs and feature requests on the [Issue Tracker](https://github.com/CakePHPMitra/Vite-Plugin/issues).

## Author

[Atul Mahankal](https://atulmahankal.github.io/atulmahankal/)

## License

MIT License - see [LICENSE](LICENSE) file.
