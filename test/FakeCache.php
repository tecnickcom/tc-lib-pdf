<?php

/**
 * FakeCache.php
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

/**
 * In-memory CacheInterface test double that records the keys it is asked about.
 */
class FakeCache implements CacheInterface
{
    /** @var array<string, mixed> */
    public array $store = [];

    /** @var list<string> */
    public array $getKeys = [];

    /** @var list<string> */
    public array $setKeys = [];

    public function get(string $key): mixed
    {
        $this->getKeys[] = $key;
        return $this->store[$key] ?? null;
    }

    public function set(string $key, mixed $value): void
    {
        $this->setKeys[] = $key;
        $this->store[$key] = $value;
    }
}
