<?php

declare(strict_types=1);

/**
 * ObjectMap.php
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Import;

/**
 * Com\Tecnick\Pdf\Import\ObjectMap
 *
 * Tracks the mapping from source object references to destination PDF object numbers
 * and maintains a queue of objects that still need to be serialized into the output PDF.
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class ObjectMap
{
    /**
     * Map from source object reference (e.g. "3_0") to destination object number.
     *
     * @var array<string, int>
     */
    private array $map = [];

    /**
     * Set of source references for which allocation is in progress (cycle guard).
     *
     * @var array<string, bool>
     */
    private array $pending = [];

    /**
     * Queue of source references whose PDF data still needs to be emitted.
     * Keys are source ref strings, values are raw PDF object byte strings.
     *
     * @var array<string, string>
     */
    private array $queue = [];

    /**
     * Check whether a source reference has already been mapped to a destination object number.
     *
     * @param string $srcRef Source object reference (e.g. "3_0").
     *
     * @return bool True if already mapped.
     */
    public function has(string $srcRef): bool
    {
        return isset($this->map[$srcRef]);
    }

    /**
     * Check whether allocation of a source reference is already in progress (cycle detection).
     *
     * @param string $srcRef Source object reference.
     *
     * @return bool True if in progress.
     */
    public function isInProgress(string $srcRef): bool
    {
        return isset($this->pending[$srcRef]);
    }

    /**
     * Allocate a new destination object number for a source reference and mark it as in-progress.
     * If the source reference is already mapped, returns the existing number.
     *
     * @param string $srcRef Source object reference.
     * @param int    $pon    Current PDF object number counter (modified in place).
     *
     * @return int The allocated destination object number.
     */
    public function allocate(string $srcRef, int &$pon): int
    {
        if (isset($this->map[$srcRef])) {
            return $this->map[$srcRef];
        }

        $num = ++$pon;
        $this->map[$srcRef] = $num;
        $this->pending[$srcRef] = true;
        return $num;
    }

    /**
     * Get the destination object number for a source reference.
     *
     * @param string $srcRef Source object reference.
     *
     * @return int Destination object number.
     *
     * @throws ImportException If the reference has not been allocated.
     */
    public function get(string $srcRef): int
    {
        return $this->map[$srcRef] ?? throw new ImportException('Object reference not allocated: ' . $srcRef);
    }

    /**
     * Mark a source reference as resolved and enqueue its serialized PDF data.
     *
     * @param string $srcRef  Source object reference.
     * @param string $pdfData Serialized PDF object data.
     */
    public function enqueue(string $srcRef, string $pdfData): void
    {
        unset($this->pending[$srcRef]);
        $this->queue[$srcRef] = $pdfData;
    }

    /**
     * Drain the queue and return all pending serialized PDF object data.
     * Clears the queue.
     *
     * @return string Concatenated PDF object data for all queued objects.
     */
    public function flush(): string
    {
        $out = '';
        foreach ($this->queue as $data) {
            $out .= $data;
        }

        $this->queue = [];
        return $out;
    }

    /**
     * Return the full map for inspection (e.g. to resolve /XObject references in resource dicts).
     *
     * @return array<string, int>
     */
    public function getMap(): array
    {
        return $this->map;
    }
}
