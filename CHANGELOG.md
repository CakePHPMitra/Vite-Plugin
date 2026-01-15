# Changelog

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](https://semver.org/).

---

## [Unreleased]

### Added
- Subdirectory/alias deployment support using `Router::url()` for asset paths

### Changed
- `url()` method now uses `Router::url()` to ensure proper base path handling

---

## [1.0.0] - 2025-01-15

### Added
- Initial release of CakePhpViteHelper plugin for CakePHP 5
- `ViteHelper` for including Vite-bundled assets in templates
- `InstallCommand` CLI for setting up Vite configuration
- Support for development server with HMR
- Support for production builds
- DDEV integration support
