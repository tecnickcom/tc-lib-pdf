<?php

declare(strict_types=1);

/**
 * ImageCacheAdapter.php
 *
 * @since     2026-06-16
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Cache;

use Com\Tecnick\Pdf\Image\ImageCacheInterface;

/**
 * Com\Tecnick\Pdf\Cache\ImageCacheAdapter
 *
 * Bridges a generic tc-lib-pdf CacheInterface to the image library's
 * ImageCacheInterface, so a single shared cache backend can also store
 * processed image data.
 *
 * @since     2026-06-16
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type ImageRawData from \Com\Tecnick\Pdf\Image\Import
 */
final class ImageCacheAdapter implements ImageCacheInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    /**
     * @return ImageRawData|null
     */
    public function get(string $key): ?array
    {
        /** @var mixed $value */
        $value = $this->cache->get($key);
        if (!\is_array($value)) {
            // Degrade an unexpected type to a miss rather than corrupting output.
            return null;
        }

        /** @var ImageRawData $value */
        return $value;
    }

    /**
     * @param ImageRawData $data
     */
    public function set(string $key, array $data): void
    {
        $this->cache->set($key, $data);
    }
}
