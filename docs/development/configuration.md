# Configuration

## Overview

Configuration for CakePhpViteHelper involves Vite settings and understanding how the hot file works.

## Vite Configuration

After running `bin/cake vite-helper install`, a `vite.config.js` is created:

```js
import { defineConfig, loadEnv } from "vite";
import phpVitePlugin from "php-vite-plugin";
import FullReload from "vite-plugin-full-reload";
import path from "path";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, path.resolve(__dirname, "config"), "");
    const appUrl = env.APP_URL || process.env.DDEV_PRIMARY_URL || "http://127.0.0.1:8765";

    return {
        plugins: [
            phpVitePlugin({
                envFile: path.resolve(__dirname, "config/.env"),
                phpUrl: appUrl,
                publicDir: "webroot/build",
                input: [
                    path.resolve(__dirname, "resources/js/app.js"),
                    path.resolve(__dirname, "resources/css/app.css"),
                ]
            }),
            FullReload(["templates/**/*"]),
        ],
        server: {
            host: process.env.DDEV_HOSTNAME || "0.0.0.0",
            port: 5173,
            // Required for Vite 5+ with DDEV/Docker
            allowedHosts: [
                process.env.DDEV_HOSTNAME,
                '.localhost.dev',
                '.ddev.site',
                'localhost',
            ].filter(Boolean),
        },
    };
});
```

## How the Hot File Works

The `ViteHelper` uses a `hot` file to detect if Vite dev server is running:

| File State | Location | Behavior |
|------------|----------|----------|
| `hot` exists | Project root | Assets from Vite dev server (HMR) |
| `hot` missing | - | Assets from `webroot/build/` (production) |

**Hot file content**: Full URL to Vite dev server

```
# Without DDEV
http://localhost:5173

# With DDEV
https://your-project.ddev.site:5173
```

The `php-vite-plugin` creates this file when running `npm run dev`. For DDEV, you may need to overwrite it with the correct URL (see [DDEV Setup](ddev-setup.md)).

## Entry Points

Add multiple entry points in `vite.config.js`:

```js
phpVitePlugin({
    input: [
        path.resolve(__dirname, "resources/js/app.js"),
        path.resolve(__dirname, "resources/js/admin.js"),
        path.resolve(__dirname, "resources/css/app.css"),
    ]
}),
```

Use in templates:

```php
// Main layout
<?= $this->Vite->asset(['resources/js/app.js', 'resources/css/app.css']) ?>

// Admin layout
<?= $this->Vite->asset(['resources/js/admin.js', 'resources/css/app.css']) ?>
```

## Vite 5+ Settings

Vite 5+ requires `allowedHosts` for security when using Docker/DDEV:

```js
server: {
    host: process.env.DDEV_HOSTNAME || "0.0.0.0",
    port: 5173,
    allowedHosts: [
        process.env.DDEV_HOSTNAME,
        '.localhost.dev',
        '.ddev.site',
        'localhost',
    ].filter(Boolean),
},
```

## Package Manager Options

The install command supports different package managers:

```bash
# npm (default)
bin/cake vite-helper install

# pnpm
bin/cake vite-helper install --pm=pnpm

# yarn
bin/cake vite-helper install --pm=yarn
```

## Files Created/Modified

The `vite-helper install` command:

- Creates `vite.config.js`
- Creates `package.json` with Vite dependencies
- Creates `resources/js/app.js`
- Creates `resources/css/app.css`
- Updates `.gitignore` with `hot` and `/webroot/build/`
