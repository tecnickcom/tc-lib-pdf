<?php

declare(strict_types=1);

/**
 * PageTemplateInterface.php
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
 * Com\Tecnick\Pdf\Import\PageTemplateInterface
 *
 * Contract for an immutable value object representing a page imported as a Form XObject.
 * Separating this into an interface allows the import implementation to be moved to a
 * dedicated library (e.g. tc-lib-pdf-import) without breaking callers in tc-lib-pdf.
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
interface PageTemplateInterface
{
    /**
     * XObject template ID (e.g. "IMP1") used to reference the Form XObject in content streams.
     */
    public function getXobjId(): string;

    /**
     * Width of the imported page in PDF user units (points).
     */
    public function getWidth(): float;

    /**
     * Height of the imported page in PDF user units (points).
     */
    public function getHeight(): float;

    /**
     * Page rotation in degrees (0, 90, 180, 270).
     */
    public function getRotation(): int;

    /**
     * Stable identifier of the source document this template was imported from.
     */
    public function getSourceId(): string;

    /**
     * 1-based page number in the source document.
     */
    public function getSourcePage(): int;

    /**
     * Effective page box as [x0, y0, x1, y1] in points.
     *
     * @return array<int, float>
     */
    public function getMediaBox(): array;
}
