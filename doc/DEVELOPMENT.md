# Development and Packaging

Back to root overview: [README.md](../README.md#in-depth-documentation)

## Development

```bash
# Install all development dependencies
make deps

# List all available Make targets
make help

# Run the full quality pipeline (lint, static analysis, tests, coverage)
make qa

# Generate PDF/X + PDF/UA sample matrix and run external validators (if installed)
make preflight
```

Build artifacts and reports are written to the `target/` directory.

## Packaging

The primary distribution channel is Composer. For system-level deployments, RPM and DEB packages are also provided.

```bash
make rpm   # build RPM package  -> target/RPM/
make deb   # build DEB package  -> target/DEB/
```

When using the RPM or DEB package, bootstrap the library with its system autoloader:

```php
require_once '/usr/share/php/Com/Tecnick/Pdf/autoload.php';
```
