# Production Deployment

## Building for Production

Run the build command to compile and optimize assets:

```bash
# Without DDEV
npm run build

# With DDEV
ddev exec npm run build
```

This creates optimized assets in `webroot/build/` with:
- Minified JavaScript and CSS
- Content-hashed filenames for cache busting
- A `manifest.json` mapping original paths to hashed versions

## Deployment Checklist

1. **Build assets**: Run `npm run build`
2. **Remove hot file**: Delete the `hot` file if it exists
3. **Commit build folder**: Include `webroot/build/` in your deployment
4. **Clear cache**: Run `bin/cake cache clear_all` after deployment

## How Production Mode Works

| Condition | Behavior |
|-----------|----------|
| `hot` file missing | ViteHelper reads from `webroot/build/manifest.json` |
| Manifest exists | Assets load from `webroot/build/` with hashed filenames |
| Manifest missing | Error thrown with helpful message |

## Git Strategy

### Option 1: Commit Built Assets (Recommended for simple deployments)

```bash
# Build locally
npm run build

# Commit the build folder
git add webroot/build/
git commit -m "Build assets for production"
```

### Option 2: Build on Server (CI/CD pipelines)

Add to your deployment script:

```bash
npm ci
npm run build
rm -f hot  # Ensure hot file is removed
```

## Subdirectory Deployments

When deploying under a subdirectory (e.g., `https://example.com/myapp/`), asset URLs are automatically handled. The ViteHelper uses `Router::url()` to detect the base path.

**No additional configuration required.**

## Troubleshooting

### "Vite manifest not found"

The manifest file doesn't exist at `webroot/build/manifest.json`.

**Fix**: Run `npm run build` to generate the manifest.

### Assets Not Loading

1. Check `webroot/build/` contains compiled assets
2. Verify `manifest.json` exists and is valid JSON
3. Ensure the `hot` file is deleted in production
4. Clear CakePHP cache: `bin/cake cache clear_all`

### Wrong Asset Paths

If assets load from wrong paths after deployment:

1. Check your web server's document root points to `webroot/`
2. For subdirectory deployments, ensure `App.baseUrl` is configured correctly in `config/app.php`
