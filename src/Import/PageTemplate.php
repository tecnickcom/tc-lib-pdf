<?php

declare(strict_types=1);

/**
 * PageTemplate.php
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
 * Com\Tecnick\Pdf\Import\PageTemplate
 *
 * Immutable value object representing a page that has been imported as a Form XObject.
 *
 * @since     2026-05-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type PageBox array{0: float, 1: float, 2: float, 3: float}
 */
class PageTemplate implements PageTemplateInterface
{
    /**
     * XObject template ID (e.g. "TPL1").
     *
     * @var string
     */
    public readonly string $xobjId;

    /**
     * Width of the imported page in user units.
     *
     * @var float
     */
    public readonly float $width;

    /**
     * Height of the imported page in user units.
     *
     * @var float
     */
    public readonly float $height;

    /**
     * Page rotation in degrees (0, 90, 180, 270).
     *
     * @var int
     */
    public readonly int $rotation;

    /**
     * Source document identifier.
     *
     * @var string
     */
    public readonly string $sourceId;

    /**
     * 1-based page number in the source document.
     *
     * @var int
     */
    public readonly int $sourcePage;

    /**
     * MediaBox as [x0, y0, x1, y1] in points.
     *
     * @var array<int, float>
     */
    public readonly array $mediaBox;

    /**
     * Constructor.
     *
     * @param string             $xobjId     XObject template ID.
     * @param float              $width      Width in user units.
     * @param float              $height     Height in user units.
     * @param int                $rotation   Page rotation in degrees.
     * @param string             $sourceId   Source document identifier.
     * @param int                $sourcePage 1-based page number.
     * @param array<int, float>  $mediaBox   MediaBox [x0, y0, x1, y1] in points.
     */
    public function __construct(
        string $xobjId,
        float $width,
        float $height,
        int $rotation,
        string $sourceId,
        int $sourcePage,
        array $mediaBox,
    ) {
        $this->xobjId = $xobjId;
        $this->width = $width;
        $this->height = $height;
        $this->rotation = $rotation;
        $this->sourceId = $sourceId;
        $this->sourcePage = $sourcePage;
        $this->mediaBox = $mediaBox;
    }

    public function getXobjId(): string
    {
        return $this->xobjId;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function getRotation(): int
    {
        return $this->rotation;
    }

    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    public function getSourcePage(): int
    {
        return $this->sourcePage;
    }

    /**
     * @return array<int, float>
     */
    public function getMediaBox(): array
    {
        return $this->mediaBox;
    }
}
