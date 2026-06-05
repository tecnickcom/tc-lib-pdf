# Remote Resources and fileOptions

Back to root overview: [README.md](../README.md#in-depth-documentation)

By default `tc-lib-pdf` **does not fetch any remote URLs**. Images, fonts, and SVG files referenced by HTTP or HTTPS are blocked unless you explicitly allow the originating hosts. Local file paths are never restricted.

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

## All fileOptions Keys

| Key | Type | Default | Description |
|-----|------|---------|-------------|
| `allowedHosts` | `string[]` | `[]` (none) | Host names the library may fetch over HTTP/HTTPS. Remote loading is disabled when this list is empty. |
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
