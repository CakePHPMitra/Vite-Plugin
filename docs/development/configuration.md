# Configuration

## Overview

Configuration for CakePhpViteHelper involves both Vite and CakePHP settings.

## Vite Configuration

After running `bin/cake vite-helper install`, a `vite.config.js` is created:

```js
import { defineConfig } from 'vite';
import liveReload from 'vite-plugin-live-reload';

export default defineConfig({
    plugins: [
        liveReload([
            'templates/**/*.php',
            'src/**/*.php',
        ]),
    ],
    root: 'resources',
    base: '/build/',
    build: {
        outDir: '../webroot/build',
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',
            },
        },
    },
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: true,
    },
});
```

## Entry Points

Define entry points in `rollupOptions.input`:

```js
rollupOptions: {
    input: {
        app: 'resources/js/app.js',
        admin: 'resources/js/admin.js',
    },
},
```

## Dev Server Settings

```js
server: {
    host: 'localhost',
    port: 5173,
    strictPort: true,
    cors: true,
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
