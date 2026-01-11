# ViteHelper Usage

## Overview

The `ViteHelper` provides methods for including Vite-bundled assets in your CakePHP templates.

## Loading the Helper

The helper is automatically loaded when the plugin is enabled. To use it manually:

```php
// src/View/AppView.php
public function initialize(): void
{
    parent::initialize();
    $this->loadHelper('CakePhpViteHelper.Vite');
}
```

## Including Assets

### Basic Usage

Include JavaScript and CSS files:

```php
<?= $this->Vite->asset(['resources/js/app.js', 'resources/css/app.css']) ?>
```

### Single File

```php
<?= $this->Vite->asset('resources/js/app.js') ?>
```

### Multiple Files

```php
<?= $this->Vite->asset([
    'resources/js/app.js',
    'resources/js/utils.js',
    'resources/css/app.css',
]) ?>
```

## Getting Asset URLs

Get the URL of a resource (useful for images):

```php
<?= $this->Vite->url('resources/img/logo.svg') ?>
```

### Development Mode

Returns Vite dev server URL:
```
https://localhost:5173/resources/img/logo.svg
```

### Production Mode

Returns built asset URL with hash:
```
/build/assets/logo-1vCAGwyb.svg
```

## How It Works

1. **Development**: Assets are served from Vite dev server with HMR
2. **Production**: Assets are loaded from the `webroot/build` directory

The helper automatically detects which mode based on the presence of the Vite dev server.
