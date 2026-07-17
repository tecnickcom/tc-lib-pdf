<?php

/**
 * FakeSelectiveCache.php
 *
 * @since       2026-06-16
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

use Com\Tecnick\Pdf\Cache\CacheInterface;
use Com\Tecnick\Pdf\Cache\SelectiveCacheInterface;

/**
 * SelectiveCacheInterface test double with a configurable supported-type list.
 */
class FakeSelectiveCache extends FakeCache implements SelectiveCacheInterface
{
    /** @var list<CacheInterface::TYPE_*> */
    public array $supported = [];

    public function supports(string $type): bool
    {
        return \in_array($type, $this->supported, true);
    }
}
