# DDEV Setup

## Overview

CakePhpViteHelper works seamlessly with DDEV for local development. The install command automatically configures the Vite host to use `DDEV_HOSTNAME` when available.

## Configuration

Add the following to your `.ddev/config.yaml`:

```yaml
web_extra_exposed_ports:
  - name: vite
    container_port: 5173
    http_port: 5172
    https_port: 5173
```

Then restart DDEV:

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

## Automatic DDEV Support

The install command automatically configures `vite.config.js` to detect DDEV environments via the `DDEV_HOSTNAME` environment variable. No manual host configuration is required.

## Troubleshooting

### Port Already in Use

If port 5173 is already in use:

1. Change the port in `vite.config.js`
2. Update `.ddev/config.yaml` to match
3. Restart DDEV
