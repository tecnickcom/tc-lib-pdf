# Remote Resources and fileOptions

Back to root overview: [README.md](../README.md#in-depth-documentation)

By default `tc-lib-pdf` **does not fetch any remote URLs**. Images, fonts, and SVG files referenced by HTTP or HTTPS are blocked unless you explicitly allow the originating hosts. Local file reads are split between internal library IO and markup-originated resource loads, with separate allowlists.

Remote access is controlled by the optional `$fileOptions` array passed as the last argument to the `Tcpdf` constructor (and forwarded to `initClassObjects()`).

## Allowing Remote Hosts

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    fileOptions: [
        'allowedHosts' => ['cdn.example.com', 'assets.myapp.io'],
    ],
);
```

Only the listed host names are permitted. Any attempt to load a resource from an unlisted host is silently blocked. Supply an explicit allowlist rather than a wildcard to limit the attack surface when user-supplied URLs might reach this code path.

## Restricting Local Paths

`allowedPaths` controls which local path prefixes may be read by the shared file helper for internal library IO, such as temp-backed signing flows, fonts, and other explicit file operations. If you omit it, the library computes a default set of trusted roots that covers the system temp directory, the package root, and bundled `vendor/tecnickcom` assets. The helper that returns these defaults is `Com\\Tecnick\\Pdf\\Base::defaultFileAllowedPaths()`.

`markupAllowedPaths` controls which local path prefixes may be read when resources are referenced by rendered HTML, CSS, or SVG markup. If you omit it, the library reuses an explicit `allowedPaths` value when one is provided; otherwise it computes a stricter default that excludes the system temp directory. The helper that returns those stricter defaults is `Com\\Tecnick\\Pdf\\Base::defaultMarkupAllowedPaths()`.

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    fileOptions: [
        'allowedPaths' => [
            (string) realpath(sys_get_temp_dir()),
            (string) realpath(__DIR__ . '/../storage/pdf-assets'),
            (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
        ],
        'markupAllowedPaths' => [
            (string) realpath(__DIR__ . '/../storage/pdf-assets'),
            (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
        ],
    ],
);
```

Supplying `allowedPaths` replaces the internal-file defaults instead of merging with them. Include every local directory the PDF run needs for trusted internal operations, such as temp-backed signature files, image fixtures, custom font directories, or cache-backed assets.

Supplying `markupAllowedPaths` replaces the stricter markup defaults instead of merging with them. Include only directories that should be reachable from rendered markup.

## All fileOptions Keys

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `allowedHosts` | `string[]` | `[]` (none) | Host names the library may fetch over HTTP/HTTPS. Remote loading is disabled when this list is empty. |
| `allowedPaths` | `string[]` | Computed internal trusted roots | Local path prefixes permitted for internal file reads. Passing this key replaces the defaults, so include all required temp/cache/font directories. |
| `markupAllowedPaths` | `string[]` | Explicit `allowedPaths` value, else stricter computed roots | Local path prefixes permitted for file reads triggered by rendered HTML, CSS, or SVG markup. Passing this key replaces the markup defaults. |
| `maxRemoteSize` | `int` | `52428800` (50 MiB) | Maximum bytes accepted for a single remote download. Requests exceeding this limit are aborted. |
| `curlopts` | `array<int, bool\|int\|string>` | `[]` | Per-request cURL options (keyed by `CURLOPT_*` constants) merged on top of the built-in defaults. |
| `defaultCurlOpts` | `array<int, bool\|int\|string>` | `null` | Replaces the built-in default cURL option set entirely. Omit this key to keep the safe defaults. |
| `fixedCurlOpts` | `array<int, bool\|int\|string>` | `null` | cURL options that are always enforced and cannot be overridden by `curlopts` - useful for pinning TLS settings in locked-down environments. |

## Example: Pinning TLS and Setting a Short Timeout

```php
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    fileOptions: [
        'allowedHosts'  => ['cdn.example.com'],
        'allowedPaths'  => [
            (string) realpath(sys_get_temp_dir()),
            (string) realpath(__DIR__ . '/../storage/pdf-assets'),
            (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
        ],
        'markupAllowedPaths' => [
            (string) realpath(__DIR__ . '/../storage/pdf-assets'),
            (string) realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
        ],
        'maxRemoteSize' => 10 * 1024 * 1024, // 10 MiB
        'curlopts'      => [
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
        ],
        'fixedCurlOpts' => [
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ],
    ],
);
```
