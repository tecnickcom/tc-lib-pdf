<?php

/**
 * TestableHTML.php
 *
 * @since       2002-08-03
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test;

/**
 * @phpstan-import-type THTMLAttrib from \Com\Tecnick\Pdf\HTML
 * @phpstan-import-type THTMLRenderContext from \Com\Tecnick\Pdf\HTML
 * @phpstan-import-type THTMLTableState from \Com\Tecnick\Pdf\HTML
 * @phpstan-import-type BorderStyle from \Com\Tecnick\Pdf\CSS
 */
class TestableHTML extends \Com\Tecnick\Pdf\Tcpdf
{
    /** @var THTMLRenderContext */
    private array $testhrc = [
        'cellctx' => [
            'originx' => 0.0,
            'originy' => 0.0,
            'lineoriginx' => 0.0,
            'maxwidth' => 0.0,
            'maxheight' => 0.0,
            'lineadvance' => 0.0,
            'linebottom' => 0.0,
            'lineascent' => 0.0,
            'linewordspacing' => 0.0,
            'linewrapped' => false,
            'textindentapplied' => false,
            'basefont' => '',
        ],
        'fontcache' => [],
        'liststack' => [],
        'tablestack' => [],
        'bcellctx' => [],
        'blockbuf' => [],
        'linkstack' => [],
        'listack' => [],
        'prelevel' => 0,
        'dom' => [],
    ];

    public function exposeSanitizeHTML(string $html): string
    {
        return $this->sanitizeHTML($html);
    }

    private function initExposeRenderContextIfNeeded(): void
    {
        if (isset($this->testhrc['cellctx']) && \is_array($this->testhrc['cellctx'])) {
            return;
        }

        $this->initHTMLCellContext($this->testhrc, 0.0, 0.0, 0.0, 0.0);
    }

    /** @phpstan-return THTMLRenderContext */
    public function exposeGetHTMLRenderContext(): array
    {
        return $this->testhrc;
    }

    /** @return array<int, string> */
    public function exposeParseHTMLTagMethods(): array
    {
        $ref = new \ReflectionClass(\Com\Tecnick\Pdf\HTML::class);
        $names = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PROTECTED) as $method) {
            $name = $method->getName();
            if (
                \str_starts_with($name, 'parseHTMLTagOPEN')
                || \str_starts_with($name, 'parseHTMLTagCLOSE')
            ) {
                $names[] = $name;
            }
        }
        \sort($names);

        return $names;
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeInvokeParseHTMLTagMethod(
        string $method,
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        $out = $this->{$method}($this->testhrc, 0, $tpx, $tpy, $tpw, $tph);
        if (!\is_string($out)) {
            return '';
        }

        return $out;
    }

    /** @phpstan-return THTMLAttrib */
    public function exposeGetHTMLRootProperties(): array
    {
        return $this->getHTMLRootProperties();
    }

    /** @phpstan-return array<int, THTMLAttrib> */
    public function exposeGetHTMLDOM(string $html): array
    {
        return $this->getHTMLDOM($html);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<int, float>
     */
    public function exposeComputeHTMLTableColWidths(
        array $dom,
        int $tablekey,
        int $cols,
        float $availableWidth,
    ): array {
        return $this->computeHTMLTableColWidths($dom, $tablekey, $cols, $availableWidth);
    }

    /** @phpstan-param array<string, mixed> $markerStyles */
    public function exposeGetHTMLliBullet(
        int $depth,
        int $count,
        float $posx = 0,
        float $posy = 0,
        string $type = '',
        array $markerStyles = [],
    ): string {
        return $this->getHTMLliBullet($depth, $count, $posx, $posy, $type, $markerStyles);
    }

    public function exposePageBreak(): int
    {
        return $this->pageBreak();
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeProcessHTMLDOMText(array &$dom, string $element, int $key, int $parent): void
    {
        $this->processHTMLDOMText($dom, $element, $key, $parent);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeInheritHTMLProperties(array &$dom, int $key, int $parent): void
    {
        $this->inheritHTMLProperties($dom, $key, $parent);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<int, string> $elm
     */
    public function exposeProcessHTMLDOMClosingTag(
        array &$dom,
        array $elm,
        int $key,
        int $parent,
        string $cssarray,
    ): void {
        $this->processHTMLDOMClosingTag($dom, $elm, $key, $parent, $cssarray);
    }

    /**
    * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<string, string> $css
     * @phpstan-param array<int> $level
     */
    public function exposeProcessHTMLDOMOpeningTag(
        array &$dom,
        array $css,
        array $level,
        string $element,
        int $key,
        bool $thead,
    ): void {
        $this->processHTMLDOMOpeningTag($dom, $css, $level, $element, $key, $thead);
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeParseHTMLText(
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        return $this->parseHTMLText($this->testhrc, 0, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeParseHTMLTextWithDom(
        array $dom,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->parseHTMLText($this->testhrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    public function exposeInitHTMLCellContext(
        float $originx,
        float $originy,
        float $maxwidth,
        float $maxheight,
    ): void {
        $this->initHTMLCellContext($this->testhrc, $originx, $originy, $maxwidth, $maxheight);
    }

    public function exposeSetHTMLLineState(
        float $lineadvance,
        float $linebottom,
        bool $linewrapped,
        float $lineascent = 0.0,
    ): void {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['cellctx']['lineadvance'] = $lineadvance;
        $this->testhrc['cellctx']['linebottom'] = $linebottom;
        $this->testhrc['cellctx']['lineascent'] = $lineascent;
        $this->testhrc['cellctx']['linewordspacing'] = 0.0;
        $this->testhrc['cellctx']['linewrapped'] = $linewrapped;
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeOpenHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        return $this->openHTMLBlock($this->testhrc, 0, $tpx, $tpy, $tpw);
    }

    /** @phpstan-param THTMLAttrib $elm */
    public function exposeCloseHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        return $this->closeHTMLBlock($this->testhrc, 0, $tpx, $tpy, $tpw);
    }

    public function exposePdfuaClampHeadingRole(string $role): string
    {
        return $this->pdfuaClampHeadingRole($role);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeParseHTMLTagOPENbrWithDom(
        array $dom,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->parseHTMLTagOPENbr($this->testhrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /** @param array<string, mixed> $elm */
    public function exposeGetHTMLInputDisplayValue(array $elm): string
    {
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\HTML::class, 'getHTMLInputDisplayValue');

        /** @var string */
        return $method->invoke($this, $elm);
    }

    /** @param array<string, mixed> $elm */
    public function exposeGetHTMLSelectDisplayValue(array $elm): string
    {
        $method = new \ReflectionMethod(\Com\Tecnick\Pdf\HTML::class, 'getHTMLSelectDisplayValue');

        /** @var string */
        return $method->invoke($this, $elm);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function exposeGetHTMLTableCellBorderStylesWithDom(array $dom, int $key): array
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLTableCellBorderStyles($this->testhrc, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return ?array<string, mixed>
     */
    public function exposeGetHTMLTableCellFillStyleWithDom(array $dom, int $key): ?array
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLTableCellFillStyle($this->testhrc, $key);
    }

    /** @phpstan-return array<string, mixed> */
    public function exposeGetHTMLFillStyle(string $fillcolor): array
    {
        return $this->getHTMLFillStyle($fillcolor);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeHasBlockLvBgAncestorWithDom(array $dom, int $key): bool
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->hasBlockLvBgAncestor($this->testhrc, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeFindHTMLAncestorOpeningTagWithDom(array $dom, int $key, string $tagname): int
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->findHTMLAncestorOpeningTag($this->testhrc, $key, $tagname);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<string, string> $attr
     *
     * @return string|array<string, mixed>
     */
    public function exposeGetHTMLInputButtonActionWithDom(array $dom, int $key, string $type, array $attr): string|array
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLInputButtonAction($this->testhrc, $key, $type, $attr);
    }

    /** @return ?array{m: string, p: array<int, mixed>} */
    public function exposeParseHTMLTcpdfSerializedData(string $data): ?array
    {
        return $this->parseHTMLTcpdfSerializedData($data);
    }

    public function exposeIsAllowedHTMLTcpdfMethod(string $method): bool
    {
        return $this->isAllowedHTMLTcpdfMethod($method);
    }

    public function exposeExecuteHTMLTcpdfPageBreak(string $mode, float &$tpx, float &$tpw): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->executeHTMLTcpdfPageBreak($this->testhrc, $mode, $tpx, $tpw);
    }

    /** @param array<int, array<string, mixed>> $tablestack */
    public function exposeSetHTMLTableStack(array $tablestack): void
    {
        $this->initExposeRenderContextIfNeeded();
        // @phpstan-ignore assign.propertyType
        $this->testhrc['tablestack'] = $tablestack;
    }

    /** @phpstan-param BorderStyle $style */
    public function exposeGetHTMLCollapsedBorderStyleName(array $style): string
    {
        return $this->getHTMLCollapsedBorderStyleName($style);
    }

    public function exposeResetHTMLTableStackOnPageBreak(float $tpy): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->resetHTMLTableStackOnPageBreak($this->testhrc, $tpy);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeGetHTMLListMarkerTypeWithDom(array $dom, int $key, bool $ordered): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLListMarkerType($this->testhrc, $key, $ordered);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposePushHTMLListWithDom(array $dom, int $key, bool $ordered): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;
        $this->pushHTMLList($this->testhrc, $key, $ordered);
    }

    public function exposePopHTMLList(): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->popHTMLList($this->testhrc);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeGetHTMLListItemCounterWithDom(array $dom, int $key): int
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLListItemCounter($this->testhrc, $key);
    }

    public function exposeGetCurrentHTMLListMarkerType(): string
    {
        $this->initExposeRenderContextIfNeeded();

        return $this->getCurrentHTMLListMarkerType($this->testhrc);
    }

    public function exposeEstimateHTMLTableHeadHeight(string $thead): float
    {
        $this->initExposeRenderContextIfNeeded();

        return $this->estimateHTMLTableHeadHeight($this->testhrc, $thead);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeEstimateHTMLTableRowHeightWithDom(array $dom, int $trkey): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->estimateHTMLTableRowHeight($this->testhrc, $trkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeFindHTMLClosingTagIndex(array $dom, int $startkey): int
    {
        return $this->findHTMLClosingTagIndex($dom, $startkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeEstimateHTMLTextHeightWithDom(array $dom, int $key, string $text, float $width): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->estimateHTMLTextHeight($this->testhrc, $key, $text, $width);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeEstimateHTMLNobrHeightWithDom(array $dom, int $startkey, float $width): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->estimateHTMLNobrHeight($this->testhrc, $startkey, $width);
    }

    public function exposeSetHTMLPrelevel(int $prelevel): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['prelevel'] = $prelevel;
    }

    public function exposeGetHTMLBaseFontName(): string
    {
        return $this->getHTMLBaseFontName();
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<string, mixed>
     */
    public function exposeGetHTMLFontMetricWithDom(array $dom, int $key): array
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLFontMetric($this->testhrc, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeGetHTMLTextPrefixWithDom(array $dom, int $key): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLTextPrefix($this->testhrc, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeGetHTMLLineAdvanceWithDom(array $dom, int $key): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLLineAdvance($this->testhrc, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeGetCurrentHTMLLineAdvanceWithDom(array $dom, int $key): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getCurrentHTMLLineAdvance($this->testhrc, $key);
    }

    public function exposeUpdateHTMLLineAdvance(float $lineadvance): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->updateHTMLLineAdvance($this->testhrc, $lineadvance);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array{width: float, spaces: int, wrapped: bool}
     */
    public function exposeMeasureHTMLInlineLineMetricsWithDom(
        array $dom,
        int $startkey,
        float $maxwidth,
        float $wordspacing = 0.0,
    ): array {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->measureHTMLInlineLineMetrics($this->testhrc, $startkey, $maxwidth, $wordspacing);
    }

    public function exposeGetHTMLTextFirstLineSpaces(string $text, string $forcedir, float $maxwidth): int
    {
        return $this->getHTMLTextFirstLineSpaces($text, $forcedir, $maxwidth);
    }

    public function exposeGetHTMLTextFirstLineSpacesWithMode(
        string $text,
        string $mode,
        string $forcedir,
        float $maxwidth,
    ): int {
        $this->initExposeRenderContextIfNeeded();

        $root = $this->getHTMLRootProperties();
        $root['white-space'] = $mode;
        $this->testhrc['dom'] = [$root];

        return $this->getHTMLTextFirstLineSpaces($text, $forcedir, $maxwidth);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeMeasureHTMLInlineRunMaxAscentWithDom(array $dom, int $startkey): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->measureHTMLInlineRunMaxAscent($this->testhrc, $startkey);
    }

    public function exposeHasHTMLTextBreakOpportunity(string $text): bool
    {
        $this->initExposeRenderContextIfNeeded();
        return $this->hasHTMLTextBreakOpportunity($this->testhrc, 0, $text);
    }

    public function exposeHasHTMLTextBreakOpportunityWithMode(string $text, string $mode): bool
    {
        $this->initExposeRenderContextIfNeeded();

        $root = $this->getHTMLRootProperties();
        $root['white-space'] = $mode;
        $this->testhrc['dom'] = [$root];

        return $this->hasHTMLTextBreakOpportunity($this->testhrc, 0, $text);
    }

    public function exposeNormalizeHTMLTextWithMode(string $text, string $mode): string
    {
        $this->initExposeRenderContextIfNeeded();

        $root = $this->getHTMLRootProperties();
        $root['white-space'] = $mode;
        $this->testhrc['dom'] = [$root];

        return $this->normalizeHTMLText($this->testhrc, $text, 0);
    }

    /**
     * @return array<string, string>
     */
    public function exposeParseHTMLStyleDeclarationMap(string $style): array
    {
        return $this->parseHTMLStyleDeclarationMap($style);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleAttributesWithDom(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleAttributes($dom, $key, $parentkey);
    }
}
