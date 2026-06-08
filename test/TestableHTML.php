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
            'floatrowleftw' => 0.0,
            'floatrowrightw' => 0.0,
            'floatrowtop' => 0.0,
            'floatrowbottom' => 0.0,
            'lineadvance' => 0.0,
            'linebottom' => 0.0,
            'lineascent' => 0.0,
            'linewordspacing' => 0.0,
            'linewrapped' => false,
            'textindentapplied' => false,
            'pendingblockmarginb' => 0.0,
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
        'quotelevel' => 0,
        'dom' => [],
    ];

    public function exposeSanitizeHTML(string $html): string
    {
        return $this->sanitizeHTML($html);
    }

    /**
     * Render context defaults are pre-initialized in `$testhrc` for probe-style tests.
     * @throws \Throwable
     */
    private function initExposeRenderContextIfNeeded(): void
    {
        // Intentionally kept as a compatibility hook for existing expose* methods.
    }

    /** @phpstan-return THTMLRenderContext */
    public function exposeGetHTMLRenderContext(): array
    {
        return $this->testhrc;
    }

    private function stringifyInvokeResult(mixed $value): string
    {
        return \is_string($value) ? $value : '';
    }

    /** @return array<int, string> */
    public function exposeParseHTMLTagMethods(): array
    {
        $ref = new \ReflectionClass(\Com\Tecnick\Pdf\HTML::class);
        $names = [];
        foreach ($ref->getMethods(\ReflectionMethod::IS_PROTECTED) as $method) {
            $name = $method->getName();
            if (\str_starts_with($name, 'parseHTMLTagOPEN') || \str_starts_with($name, 'parseHTMLTagCLOSE')) {
                $names[] = $name;
            }
        }
        \sort($names);

        return $names;
    }

    /**
     * @phpstan-param THTMLAttrib $elm
     * @throws \Throwable
     */
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

        $refm = new \ReflectionMethod($this, $method);
        $args = [&$this->testhrc, 0, &$tpx, &$tpy, &$tpw, &$tph];

        return $this->stringifyInvokeResult($refm->invokeArgs($this, $args));
    }

    /** @phpstan-return THTMLAttrib */
    public function exposeGetHTMLRootProperties(): array
    {
        return $this->getHTMLRootProperties();
    }

    /**
     * @phpstan-return array<int, THTMLAttrib>
     * @throws \Throwable
     */
    public function exposeGetHTMLDOM(string $html): array
    {
        return $this->getHTMLDOM($html);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<int, float>
     */
    public function exposeComputeHTMLTableColWidths(array $dom, int $tablekey, int $cols, float $availableWidth): array
    {
        return $this->computeHTMLTableColWidths($dom, $tablekey, $cols, $availableWidth);
    }

    /**
     * @phpstan-param array<string, mixed> $markerStyles
     * @throws \Throwable
     */
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

    /** @throws \Throwable */
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
     * @throws \Throwable
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

    /**
     * @phpstan-param THTMLAttrib $elm
     * @throws \Throwable
     */
    public function exposeParseHTMLText(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        return $this->parseHTMLText($this->testhrc, 0, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
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

    /** @throws \Throwable */
    public function exposeInitHTMLCellContext(float $originx, float $originy, float $maxwidth, float $maxheight): void
    {
        $this->initHTMLCellContext($this->testhrc, $originx, $originy, $maxwidth, $maxheight);
    }

    /** @throws \Throwable */
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

    /**
     * @phpstan-param THTMLAttrib $elm
     * @throws \Throwable
     */
    public function exposeOpenHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = [$elm];

        return $this->openHTMLBlock($this->testhrc, 0, $tpx, $tpy, $tpw);
    }

    /**
     * @phpstan-param THTMLAttrib $elm
     * @throws \Throwable
     */
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
     * @throws \Throwable
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

    /** @return ?array{width: int, height: int} */
    public function exposeGetHTMLRasterImportDimensions(string $src, float $width, float $height): ?array
    {
        return $this->getHTMLRasterImportDimensions($src, $width, $height);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<int|string, array<string, mixed>>
     * @throws \Throwable
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
     * @throws \Throwable
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

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeHasBlockLvBgAncestorWithDom(array $dom, int $key): bool
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->hasBlockLvBgAncestor($this->testhrc, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
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
     * @throws \Throwable
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

    /** @throws \Throwable */
    public function exposeExecuteHTMLTcpdfPageBreak(string $mode, float &$tpx, float &$tpw): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->executeHTMLTcpdfPageBreak($this->testhrc, $mode, $tpx, $tpw);
    }

    /**
     * @param array<int, array<string, mixed>> $tablestack
     * @phpstan-param array<int, THTMLTableState> $tablestack
     * @throws \Throwable
     */
    public function exposeSetHTMLTableStack(array $tablestack): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['tablestack'] = $tablestack;
    }

    /** @phpstan-param BorderStyle $style */
    public function exposeGetHTMLCollapsedBorderStyleName(array $style): string
    {
        return $this->getHTMLCollapsedBorderStyleName($style);
    }

    /** @throws \Throwable */
    public function exposeResetHTMLTableStackOnPageBreak(float $tpy): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->resetHTMLTableStackOnPageBreak($this->testhrc, $tpy);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetHTMLListMarkerTypeWithDom(array $dom, int $key, bool $ordered): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLListMarkerType($this->testhrc, $key, $ordered);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposePushHTMLListWithDom(array $dom, int $key, bool $ordered): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;
        $this->pushHTMLList($this->testhrc, $key, $ordered);
    }

    /** @throws \Throwable */
    public function exposePopHTMLList(): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->popHTMLList($this->testhrc);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetHTMLListItemCounterWithDom(array $dom, int $key): int
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLListItemCounter($this->testhrc, $key);
    }

    /** @throws \Throwable */
    public function exposeGetCurrentHTMLListMarkerType(): string
    {
        $this->initExposeRenderContextIfNeeded();

        return $this->getCurrentHTMLListMarkerType($this->testhrc);
    }

    /** @throws \Throwable */
    public function exposeEstimateHTMLTableHeadHeight(string $thead): float
    {
        $this->initExposeRenderContextIfNeeded();

        return $this->estimateHTMLTableHeadHeight($this->testhrc, $thead);
    }

    /**
     * @phpstan-param array<string, mixed>|null $cell
     * @phpstan-param array<int|string, array<array-key, array<array-key, int>|float|int|string>|float|string> $styles
     * @throws \Throwable
     */
    public function exposeMeasureHTMLCellRenderedHeight(
        string $html,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        ?array $cell = null,
        array $styles = [],
    ): float {
        return $this->measureHTMLCellRenderedHeight($html, $posx, $posy, $width, $height, $cell, $styles);
    }

    /** @throws \Throwable */
    public function exposeReplayHTMLTableHead(string $thead, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $this->initExposeRenderContextIfNeeded();

        return $this->replayHTMLTableHead($this->testhrc, $thead, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
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

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeEstimateHTMLTextHeightWithDom(array $dom, int $key, string $text, float $width): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->estimateHTMLTextHeight($this->testhrc, $key, $text, $width);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeEstimateHTMLNobrHeightWithDom(array $dom, int $startkey, float $width): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->estimateHTMLNobrHeight($this->testhrc, $startkey, $width);
    }

    /** @throws \Throwable */
    public function exposeSetHTMLPrelevel(int $prelevel): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['prelevel'] = $prelevel;
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeSetHTMLDom(array $dom): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;
    }

    /** @throws \Throwable */
    public function exposeUpdateHTMLParentBlockBottom(int $openkey, float $bottom): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->updateHTMLParentBlockBottom($this->testhrc, $openkey, $bottom);
    }

    /** @throws \Throwable */
    public function exposeGetHTMLBaseFontName(): string
    {
        return $this->getHTMLBaseFontName();
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeHasHTMLBooleanAttribute(array &$dom, int $key, string $name): bool
    {
        return $this->hasHTMLBooleanAttribute($dom, $key, $name);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeGetHTMLEffectiveLang(array &$dom, int $key): string
    {
        return $this->getHTMLEffectiveLang($dom, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeMatchesHTMLPseudoLang(array &$dom, int $key, string $arg): bool
    {
        return $this->matchesHTMLPseudoLang($dom, $key, $arg);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<int> $siblings
     * @return array<int>
     */
    public function exposeGetHTMLSiblingKeysByTagName(array &$dom, array $siblings, int $key): array
    {
        return $this->getHTMLSiblingKeysByTagName($dom, $siblings, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<int> $siblings
     */
    public function exposeMatchesHTMLPseudoLastOfType(array &$dom, array $siblings, int $key): bool
    {
        return $this->matchesHTMLPseudoLastOfType($dom, $siblings, $key);
    }

    /**
     * @phpstan-param array<int> $siblings
     */
    public function exposeMatchesHTMLPseudoNthChild(array $siblings, int $key, string $arg): bool
    {
        return $this->matchesHTMLPseudoNthChild($siblings, $key, $arg);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeMatchesHTMLPseudoEmpty(array &$dom, int $key): bool
    {
        return $this->matchesHTMLPseudoEmpty($dom, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     */
    public function exposeMatchesHTMLSelectorAttribute(
        array &$dom,
        int $key,
        #[\SensitiveParameter]
        string $token,
    ): bool {
        return $this->matchesHTMLSelectorAttribute($dom, $key, $token);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @return array<int>
     */
    public function exposeGetHTMLOpeningSiblingKeys(array &$dom, int $key): array
    {
        return $this->getHTMLOpeningSiblingKeys($dom, $key);
    }

    /** @throws \Throwable */
    public function exposeGetHTMLStyleLengthValue(string $value): ?float
    {
        return $this->getHTMLStyleLengthValue($value);
    }

    /**
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    public function exposeGetHTMLListImageMarkerType(string $listImage): string
    {
        return $this->getHTMLListImageMarkerType($listImage);
    }

    public function exposeGetHTMLBackgroundShorthandColor(string $background): string
    {
        return $this->getHTMLBackgroundShorthandColor($background);
    }

    /**
     * @phpstan-param THTMLAttrib $elm
     * @throws \Com\Tecnick\Pdf\Font\Exception
     */
    public function exposeResolveHTMLFontSizeAdjust(
        array $elm,
        string $fontname,
        string $fontstyle,
        float $fontsize,
    ): float {
        return $this->resolveHTMLFontSizeAdjust($elm, $fontname, $fontstyle, $fontsize);
    }

    /**
     * @phpstan-param THTMLRenderContext $hrc
     * @phpstan-param THTMLAttrib $elm
     */
    public function exposeApplyHTMLNamedPageSemantics(
        array &$hrc,
        array $elm,
        bool $hasExplicitBreakBefore,
        float $tpx,
        float $tpy,
    ): bool {
        return $this->applyHTMLNamedPageSemantics($hrc, $elm, $hasExplicitBreakBefore, $tpx, $tpy);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array<string, mixed>
     * @throws \Throwable
     */
    public function exposeGetHTMLFontMetricWithDom(array $dom, int $key): array
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLFontMetric($this->testhrc, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetHTMLTextPrefixWithDom(array $dom, int $key): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLTextPrefix($this->testhrc, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetHTMLLineAdvanceWithDom(array $dom, int $key): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getHTMLLineAdvance($this->testhrc, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetCurrentHTMLLineAdvanceWithDom(array $dom, int $key): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->getCurrentHTMLLineAdvance($this->testhrc, $key);
    }

    /** @throws \Throwable */
    public function exposeGetUnitValuePoints(string $value): float
    {
        return $this->getUnitValuePoints($value);
    }

    public function exposeToUnitPoints(float $points): float
    {
        return $this->toUnit($points);
    }

    /** @throws \Throwable */
    public function exposeUpdateHTMLLineAdvance(float $lineadvance): void
    {
        $this->initExposeRenderContextIfNeeded();
        $this->updateHTMLLineAdvance($this->testhrc, $lineadvance);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     *
     * @return array{width: float, spaces: int, wrapped: bool}
     * @throws \Throwable
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

    /** @throws \Throwable */
    public function exposeGetHTMLTextFirstLineSpaces(string $text, string $forcedir, float $maxwidth): int
    {
        return $this->getHTMLTextFirstLineSpaces($text, $forcedir, $maxwidth);
    }

    /** @throws \Throwable */
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

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeMeasureHTMLInlineRunMaxAscentWithDom(array $dom, int $startkey): float
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->measureHTMLInlineRunMaxAscent($this->testhrc, $startkey);
    }

    /** @throws \Throwable */
    public function exposeHasHTMLTextBreakOpportunity(string $text): bool
    {
        $this->initExposeRenderContextIfNeeded();
        return $this->hasHTMLTextBreakOpportunity($this->testhrc, 0, $text);
    }

    /** @throws \Throwable */
    public function exposeHasHTMLTextBreakOpportunityWithMode(string $text, string $mode): bool
    {
        $this->initExposeRenderContextIfNeeded();

        $root = $this->getHTMLRootProperties();
        $root['white-space'] = $mode;
        $this->testhrc['dom'] = [$root];

        return $this->hasHTMLTextBreakOpportunity($this->testhrc, 0, $text);
    }

    /** @throws \Throwable */
    public function exposeNormalizeHTMLTextWithMode(string $text, string $mode): string
    {
        $this->initExposeRenderContextIfNeeded();

        $root = $this->getHTMLRootProperties();
        $root['white-space'] = $mode;
        $this->testhrc['dom'] = [$root];

        return $this->normalizeHTMLText($this->testhrc, $text, 0);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeApplyHTMLFontVariantWithDom(array $dom, int $key, string $text): string
    {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;

        return $this->applyHTMLFontVariant($this->testhrc, $text, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeGetHTMLPseudoTextContentWithDom(
        array $dom,
        int $key,
        string $style,
        int $quotelevel = 0,
    ): string {
        $this->initExposeRenderContextIfNeeded();
        $this->testhrc['dom'] = $dom;
        $this->testhrc['quotelevel'] = $quotelevel;

        return $this->getHTMLPseudoTextContent($this->testhrc, $key, $style);
    }

    /**
     * @return array<string, string>
     */
    public function exposeParseHTMLStyleDeclarationMap(string $style): array
    {
        return $this->parseHTMLStyleDeclarationMap($style);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @phpstan-param array<int, string> $properties
     */
    public function exposeIsLastHTMLStyleDeclarationProperty(
        array &$dom,
        int $key,
        string $property,
        array $properties,
    ): bool {
        return $this->isLastHTMLStyleDeclarationProperty($dom, $key, $property, $properties);
    }

    /**
     * @phpstan-param THTMLRenderContext $hrc
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function exposeGetCurrentHTMLListIndentWidthWithContext(array &$hrc): float
    {
        return $this->getCurrentHTMLListIndentWidth($hrc);
    }

    /**
     * @param array<string, mixed> $elm
     */
    public function exposeGetPdfUaListNumbering(array $elm): string
    {
        return $this->getPdfUaListNumbering($elm);
    }

    /**
     * @return array{T: string, R: string, B: string, L: string}|array{}
     */
    public function exposeExpandHTMLBorderQuadValues(string $value): array
    {
        return $this->expandHTMLBorderQuadValues($value);
    }

    /**
     * @phpstan-param THTMLTableState $table
     * @phpstan-param THTMLAttrib $elm
     * @param array{buffer: string} $cellctx
     */
    public function exposeShouldHideHTMLEmptyTableCell(array $table, array $elm, array $cellctx): bool
    {
        $fullCellCtx = [
            'bstyles' => [],
            'buffer' => $cellctx['buffer'],
            'cellw' => 0.0,
            'cellx' => 0.0,
            'colindex' => 0,
            'colspan' => 1,
            'fillstyle' => null,
            'lineadvance' => 0.0,
            'lineascent' => 0.0,
            'linebottom' => 0.0,
            'linewordspacing' => 0.0,
            'linewrapped' => false,
            'maxheight' => 0.0,
            'maxwidth' => 0.0,
            'originx' => 0.0,
            'originy' => 0.0,
            'rowspan' => 1,
            'rowtop' => 0.0,
            'valign' => 'top',
        ];

        return $this->shouldHideHTMLEmptyTableCell($table, $elm, $fullCellCtx);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\Pdf\Exception
     */
    public function exposeParseHTMLStyleBorderSpacingProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBorderSpacingProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStylePageBreakInsideProperty(array &$dom, int $key): void
    {
        $this->parseHTMLStylePageBreakInsideProperty($dom, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleBreakInsideAliasProperty(array &$dom, int $key): void
    {
        $this->parseHTMLStyleBreakInsideAliasProperty($dom, $key);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Com\Tecnick\Color\Exception
     */
    public function exposeParseHTMLStyleBackgroundProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBackgroundProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleTableLayoutProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleTableLayoutProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleCaptionSideProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleCaptionSideProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleEmptyCellsProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleEmptyCellsProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleFontShorthandProperty(array &$dom, int $key): void
    {
        $this->parseHTMLStyleFontShorthandProperty($dom, $key);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleFontFamilyProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleFontFamilyProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleListStyleShorthandProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleListStyleShorthandProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleListStyleTypeProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleListStyleTypeProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleListStylePositionProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleListStylePositionProperty($dom, $key, $parentkey);
    }

    /** @phpstan-param array<int, THTMLAttrib> $dom */
    public function exposeParseHTMLStyleListStyleImageProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleListStyleImageProperty($dom, $key, $parentkey);
    }

    /**
     * @phpstan-param array<int, THTMLAttrib> $dom
     * @throws \Throwable
     */
    public function exposeParseHTMLStyleAttributesWithDom(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleAttributes($dom, $key, $parentkey);
    }

    /**
     * @param array<string, mixed> $attr
     * @param array<string, mixed> $elm
     *
     * @return array<string, mixed>
     */
    public function exposeGetHTMLFormFieldJSProperties(array $attr, string $fieldkind, array $elm = []): array
    {
        return $this->getHTMLFormFieldJSProperties($attr, $fieldkind, $elm);
    }
}
