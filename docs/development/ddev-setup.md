# DDEV Setup

CakePhpViteHelper works seamlessly with DDEV for local development.

## Setup

### 1. Expose Vite Port

Create `.ddev/config.vite.yaml`:

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

### 2. Restart DDEV

```bash
ddev restart
```

Done! Vite auto-starts with DDEV and HMR works automatically.

## Running Vite Manually

If you prefer manual control, skip the hooks and run:

```bash
ddev exec npm run dev
ddev exec 'echo "https://${DDEV_HOSTNAME}:5173" > hot'
```

## Production Build

```bash
ddev exec npm run build
```

## Troubleshooting

### 403 Forbidden from Vite

Add `allowedHosts` to `vite.config.js`:

```js
server: {
    host: process.env.DDEV_HOSTNAME || '0.0.0.0',
    port: 5173,
    allowedHosts: [
        process.env.DDEV_HOSTNAME,
        '.localhost.dev',
        '.ddev.site',
        'localhost',
    ].filter(Boolean),
},
```

### "Vite manifest not found" Error

Check if hot file has correct URL:

```bash
ddev exec "cat hot"
# Should show: https://your-project.ddev.site:5173
```

Fix it:

```bash
ddev exec 'echo "https://${DDEV_HOSTNAME}:5173" > hot'
```

### Port Already in Use

1. Change port in `vite.config.js`
2. Update `.ddev/config.vite.yaml` to match
3. Restart DDEV
