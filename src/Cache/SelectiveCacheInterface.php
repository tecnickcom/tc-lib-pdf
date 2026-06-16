<?php

declare(strict_types=1);

/**
 * SelectiveCacheInterface.php
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
 * Com\Tecnick\Pdf\Cache\SelectiveCacheInterface
 *
 * Optional extension of CacheInterface for backends that only want to cache
 * some of the cacheable subsystems (for example font subsets but not images).
 *
 * A plain CacheInterface is consulted for every cacheable subsystem. When an
 * implementation also implements this interface, each subsystem is wired only
 * when supports() returns true for its type; unsupported types are disabled
 * entirely, so the cache is never queried nor written for them and the
 * implementation never has to handle their data.
 *
 * @since     2026-06-16
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
interface SelectiveCacheInterface extends CacheInterface
{
    /**
     * Whether this cache handles the given cacheable subsystem type.
     *
     * @param CacheInterface::TYPE_* $type Subsystem type to query.
     *
     * @return bool True to enable caching for the type, false to disable it.
     */
    public function supports(string $type): bool;
}
