<?php

declare(strict_types=1);

/**
 * FontSubsetCacheAdapter.php
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

use Com\Tecnick\Pdf\Font\FontSubsetCacheInterface;

/**
 * Com\Tecnick\Pdf\Cache\FontSubsetCacheAdapter
 *
 * Bridges a generic tc-lib-pdf CacheInterface to the font library's
 * FontSubsetCacheInterface, so a single shared cache backend can also store
 * font subset programs.
 *
 * @since     2026-06-16
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
final class FontSubsetCacheAdapter implements FontSubsetCacheInterface
{
    public function __construct(
        private readonly CacheInterface $cache,
    ) {}

    public function get(string $key): ?string
    {
        /** @var mixed $value */
        $value = $this->cache->get($key);

        // Degrade an unexpected type to a miss rather than corrupting output.
        return \is_string($value) ? $value : null;
    }

    public function set(string $key, string $subsetFont): void
    {
        $this->cache->set($key, $subsetFont);
    }
}
