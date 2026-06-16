# External Cache

Back to root overview: [README.md](../README.md#in-depth-documentation)

Generating font subsets and processing images (decode, resize, re-encode) is computationally expensive. `tc-lib-pdf` can reuse these results across `Tcpdf` instances and PHP processes through an **optional external cache** that you provide.

No cache backend is shipped: you implement a tiny interface that bridges to whatever store you already use (filesystem, APCu, Redis, a PSR-16 cache, ...). Caching is **disabled by default** — when no cache is supplied, behavior is unchanged.

One cache instance is reused by every cacheable subsystem (currently font subsets and images, more may be added later), so a single backend, connection, and configuration serves them all.

## The CacheInterface

Implement `Com\Tecnick\Pdf\Cache\CacheInterface`:

```php
namespace Com\Tecnick\Pdf\Cache;

interface CacheInterface
{
    public function get(string $key): mixed;          // stored value, or null on a miss
    public function set(string $key, mixed $value): void;
}
```

Both methods MUST be **best-effort** and **MUST NOT throw**: a backend miss or transient failure must surface as `null` (on `get`) or a silent no-op (on `set`). The font and image libraries call the cache directly and do not catch exceptions, so a throwing implementation will break PDF generation.

## Enabling the Cache

Pass your implementation as the `cache` argument of the `Tcpdf` constructor (the last parameter):

```php
$cache = new MyRedisCache(); // implements Com\Tecnick\Pdf\Cache\CacheInterface

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    subsetfont: true, // required for the font subset cache to be exercised
    cache: $cache,
);
```

A minimal in-memory implementation (useful for tests or single-request deduplication):

```php
use Com\Tecnick\Pdf\Cache\CacheInterface;

$cache = new class implements CacheInterface {
    /** @var array<string, mixed> */
    private array $store = [];

    public function get(string $key): mixed
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }
};
```

## What Gets Cached

| Subsystem | Type constant | Cached value | Key prefix |
|-----------|---------------|--------------|------------|
| Font subsets | `CacheInterface::TYPE_FONT` | Raw subset font program (`string`, uncompressed) | `tc-lib-pdf-font:subset:v1:` |
| Images | `CacheInterface::TYPE_IMAGE` | Processed image snapshot (`array`) | `tc-lib-pdf-image:v1:` |

Keys are already namespaced and schema-versioned by each sub-library, so a single shared store is collision-safe. The font subset cache is only consulted when font subsetting is enabled (`subsetfont: true`).

The library never evicts entries: expiration, size limits, and (de)serialization are entirely the backend's responsibility. When an implementation deserializes data it MUST disable object restoration, e.g. `unserialize($data, ['allowed_classes' => false])`.

## Caching Only Some Types

To cache only a subset of the subsystems, implement `Com\Tecnick\Pdf\Cache\SelectiveCacheInterface` (which extends `CacheInterface`) and report which types you handle:

```php
namespace Com\Tecnick\Pdf\Cache;

interface SelectiveCacheInterface extends CacheInterface
{
    /** @param CacheInterface::TYPE_* $type */
    public function supports(string $type): bool;
}
```

When `supports()` returns `false` for a type, that type is disabled entirely: the cache is never queried or written for it, and your implementation never receives its data. A plain `CacheInterface` (without `supports()`) caches every type.

Example — cache font subsets but never images:

```php
use Com\Tecnick\Pdf\Cache\CacheInterface;
use Com\Tecnick\Pdf\Cache\SelectiveCacheInterface;

$cache = new class implements SelectiveCacheInterface {
    /** @var array<string, mixed> */
    private array $store = [];

    public function supports(string $type): bool
    {
        return $type === CacheInterface::TYPE_FONT;
    }

    public function get(string $key): mixed
    {
        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->store[$key] = $value;
    }
};
```

## Security

The cache store is a **trust boundary**. Cached values are embedded verbatim into generated PDFs, so anyone able to write to the backend can influence document output. Use a store only your application can write to, and always deserialize with object restoration disabled (`['allowed_classes' => false]`).
