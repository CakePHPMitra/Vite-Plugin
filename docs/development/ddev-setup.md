# DDEV Setup

## Overview

CakePhpViteHelper works seamlessly with DDEV for local development.

## Configuration

Add the following to your `.ddev/config.yaml`:

```yaml
web_extra_exposed_ports:
  - name: vite
    container_port: 5173
    http_port: 5172
    https_port: 5173
```

## Restart DDEV

After updating the configuration:

```bash
ddev restart
```

## Running Vite

### Inside DDEV Container

```bash
ddev ssh
npm run dev
```

### Outside DDEV Container

```bash
ddev exec npm run dev
```

## Accessing Vite Dev Server

With DDEV, the Vite dev server is accessible at:

```
https://your-project.ddev.site:5173
```

## Troubleshooting

### Port Already in Use

If port 5173 is already in use:

1. Change the port in `vite.config.js`:
   ```js
   server: {
       port: 5174,
   }
   ```

2. Update `.ddev/config.yaml` to match

3. Restart DDEV

### HMR Not Working

Ensure your `vite.config.js` has the correct server settings:

```js
server: {
    host: '0.0.0.0',
    port: 5173,
    strictPort: true,
    hmr: {
        host: 'your-project.ddev.site',
        port: 5173,
        protocol: 'wss',
    },
},
```
