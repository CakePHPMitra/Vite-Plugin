# Asset Management

## Overview

CakePhpViteHelper manages frontend assets using Vite's build system.

## Directory Structure

```
your-app/
├── resources/
│   ├── js/
│   │   └── app.js
│   ├── css/
│   │   └── app.css
│   └── img/
│       └── logo.svg
├── webroot/
│   └── build/        # Production builds go here
└── vite.config.js
```

## Development Workflow

1. **Start Vite dev server**:
   ```bash
   npm run dev
   ```

2. **Edit files** in `resources/` directory

3. **Changes appear instantly** via Hot Module Replacement

## Production Build

1. **Build assets**:
   ```bash
   npm run build
   ```

2. **Assets are compiled** to `webroot/build/`

3. **Manifest file** is generated for asset versioning

## Supported File Types

- JavaScript (.js, .ts, .jsx, .tsx)
- CSS (.css, .scss, .sass, .less)
- Images (.png, .jpg, .svg, .gif, .webp)
- Fonts (.woff, .woff2, .ttf, .eot)

## Asset Versioning

Production builds include content hashes in filenames for cache busting:

```
app.js → app-a1b2c3d4.js
app.css → app-e5f6g7h8.css
```

The manifest file (`webroot/build/manifest.json`) maps original filenames to hashed versions.
