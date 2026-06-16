<?php

declare(strict_types=1);

/**
 * CacheInterface.php
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

/**
 * Com\Tecnick\Pdf\Cache\CacheInterface
 *
 * Optional external cache that tc-lib-pdf can reuse across all the
 * sub-libraries that support caching (currently font subsets and processed
 * images, possibly others in the future).
 *
 * A single implementation of this contract can be passed to the Tcpdf
 * constructor and is internally adapted to each sub-library's own cache
 * interface, so the same backend (filesystem, APCu, Redis, a PSR-16 cache,
 * ...) serves every cacheable subsystem with one connection and one
 * configuration.
 *
 * This library intentionally ships NO concrete implementation: the backend,
 * its (de)serialization, expiration and size limits are entirely the
 * application's responsibility. Implementations MUST be best-effort and MUST
 * NOT throw on a miss or a transient backend failure, so that a cache problem
 * never breaks PDF generation.
 *
 * Keys are already namespaced and schema-versioned by each sub-library
 * (e.g. "tc-lib-pdf-font:subset:v1:..." and "tc-lib-pdf-image:v1:..."), so a
 * single shared store is collision-safe.
 *
 * Security: the cache store is a trust boundary. Cached values are embedded
 * verbatim into generated PDFs, so anyone able to write to the backend can
 * influence document output. Use a store only your application can write to,
 * and when an implementation deserializes data it MUST disable object
 * restoration (e.g. unserialize($s, ['allowed_classes' => false])).
 *
 * @since     2026-06-16
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
interface CacheInterface
{
    /**
     * Cacheable subsystem type: TrueType font subset programs.
     */
    public const TYPE_FONT = 'font';

    /**
     * Cacheable subsystem type: processed image data.
     */
    public const TYPE_IMAGE = 'image';

    /**
     * Retrieve a previously stored value, or null on a miss.
     *
     * @param string $key Cache key.
     *
     * @return mixed Stored value, or null when absent.
     */
    public function get(string $key): mixed;

    /**
     * Store a value.
     *
     * @param string $key   Cache key.
     * @param mixed  $value Value to store.
     */
    public function set(string $key, mixed $value): void;
}
