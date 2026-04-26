<?php

/**
 * TestableSVG.php
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
 * @phpstan-import-type TTMatrix from \Com\Tecnick\Pdf\Graph\Base
 * @phpstan-import-type TRefUnitValues from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TSVGSize from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSCGCoord from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGGradient from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGStyle from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGTextMode from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGAttributes from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGAttribChild from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGAttribs from \Com\Tecnick\Pdf\SVG
 * @phpstan-import-type TSVGObj from \Com\Tecnick\Pdf\SVG
 */
class TestableSVG extends \Com\Tecnick\Pdf\Tcpdf
{
    /** @phpstan-return array<float> */
    public function exposeParseSVGTMtranslate(string $val): array
    {
        return $this->parseSVGTMtranslate($val);
    }

    /** @phpstan-return array<float> */
    public function exposeParseSVGTMscale(string $val): array
    {
        return $this->parseSVGTMscale($val);
    }

    /** @phpstan-return array<float> */
    public function exposeParseSVGTMrotate(string $val): array
    {
        return $this->parseSVGTMrotate($val);
    }

    /** @phpstan-return array<float> */
    public function exposeParseSVGTMskewX(string $val): array
    {
        return $this->parseSVGTMskewX($val);
    }

    /** @phpstan-return array<float> */
    public function exposeParseSVGTMskewY(string $val): array
    {
        return $this->parseSVGTMskewY($val);
    }

    /** @phpstan-return array<float> */
    public function exposeParseSVGTMmatrix(string $val): array
    {
        return $this->parseSVGTMmatrix($val);
    }

    /** @phpstan-param TRefUnitValues|null $ref */
    public function setSvgRefUnit(int $soid, ?array $ref = null): void
    {
        if (!isset($this->svgobjs[$soid])) {
            $this->initSvgObjForHandlers($soid);
        }
        $this->patchSvgObj($soid, ['refunitval' => $ref ?? self::REFUNITVAL]);
    }

    /** @phpstan-param TRefUnitValues|null $ref */
    public function exposeSvgUnitToPoints(string|float|int $val, int $soid = -1, ?array $ref = null): float
    {
        return $this->svgUnitToPoints($val, $soid, $ref);
    }

    /** @phpstan-param TRefUnitValues|null $ref */
    public function exposeSvgUnitToUnit(string|float|int $val, int $soid = -1, ?array $ref = null): float
    {
        return $this->svgUnitToUnit($val, $soid, $ref);
    }

    /** @phpstan-return TTMatrix */
    public function exposeGetSVGTransformMatrix(string $attr): array
    {
        return $this->getSVGTransformMatrix($attr);
    }

    /**
     * @phpstan-param TTMatrix $trm
     * @phpstan-return TTMatrix
     */
    public function exposeConvertSVGMatrix(array $trm, int $soid = 0): array
    {
        return $this->convertSVGMatrix($trm, $soid);
    }

    /** @phpstan-param TTMatrix $trm */
    public function exposeGetOutSVGTransformation(array $trm, int $soid = 0): string
    {
        return $this->getOutSVGTransformation($trm, $soid);
    }

    public function exposeRemoveTagNamespace(string $name): string
    {
        return $this->removeTagNamespace($name);
    }

    public function exposeGetSVGPath(int $soid, string $attrd, string $mode = ''): string
    {
        return $this->getSVGPath($soid, $attrd, $mode);
    }

    public function exposeGetTALetterSpacing(string $spacing, float $parent = 0.0): float
    {
        return $this->getTALetterSpacing($spacing, $parent);
    }

    public function exposeGetTAFontStretching(string $stretch, float $parent = 100): float
    {
        return $this->getTAFontStretching($stretch, $parent);
    }

    public function exposeGetTAFontWeight(string $weight): string
    {
        return $this->getTAFontWeight($weight);
    }

    public function exposeGetTAFontStyle(string $style): string
    {
        return $this->getTAFontStyle($style);
    }

    public function exposeGetTAFontDecoration(string $decoration): string
    {
        return $this->getTAFontDecoration($decoration);
    }

    public function exposeNormalizeSVGBlendMode(string $mode): string
    {
        return $this->normalizeSVGBlendMode($mode);
    }

    public function exposeNormalizeSVGAlphaValue(string|float|int $alpha): float
    {
        return $this->normalizeSVGAlphaValue($alpha);
    }

    public function exposeGetSVGExtGState(
        ?float $strokingAlpha = null,
        ?float $nonstrokingAlpha = null,
        string $blendMode = 'Normal',
    ): string {
        return $this->getSVGExtGState($strokingAlpha, $nonstrokingAlpha, $blendMode);
    }

    public function exposeParseCSSAttrib(string $tag, string $attr, string $default = ''): string
    {
        return $this->parseCSSAttrib($tag, $attr, $default);
    }

    /** @phpstan-return TSCGCoord */
    public function getPathCoordDefaults(): array
    {
        return [
            'x' => 0.0,
            'y' => 0.0,
            'x0' => 0.0,
            'y0' => 0.0,
            'xmin' => 1000000000.0,
            'xmax' => 0.0,
            'ymin' => 1000000000.0,
            'ymax' => 0.0,
            'xinit' => 0.0,
            'yinit' => 0.0,
            'xoffset' => 0.0,
            'yoffset' => 0.0,
            'relcoord' => false,
            'firstcmd' => true,
        ];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-param array<array<string>> $paths
     * @phpstan-param array<string> $rawparams
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdA(array $prm, array $crd, array $paths, int $key, array $rawparams): array
    {
        $out = $this->svgPathCmdA($prm, $crd, $paths, $key, $rawparams);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdC(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdC($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdH(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdH($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdL(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdL($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdM(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdM($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdQ(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdQ($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-param array<array<string>> $paths
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdS(array $prm, array $crd, array $paths, int $key): array
    {
        $out = $this->svgPathCmdS($prm, $crd, $paths, $key);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-param array<array<string>> $paths
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdT(array $prm, array $crd, array $paths, int $key): array
    {
        $out = $this->svgPathCmdT($prm, $crd, $paths, $key);
        return [$out, $crd];
    }

    /**
     * @phpstan-param array<float> $prm
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdV(array $prm, array $crd): array
    {
        $out = $this->svgPathCmdV($prm, $crd);
        return [$out, $crd];
    }

    /**
     * @phpstan-param TSCGCoord $crd
     * @phpstan-return array{0: string, 1: TSCGCoord}
     */
    public function exposeSvgPathCmdZ(array $crd): array
    {
        $out = $this->svgPathCmdZ($crd);
        return [$out, $crd];
    }

    /** @phpstan-return TSVGStyle */
    public function exposeDefaultSVGStyle(): array
    {
        return self::DEFSVGSTYLE;
    }

    /**
     * @phpstan-param array<string, TSVGGradient> $gradients
     * @phpstan-param array<string, TSVGAttribs> $clippaths
     */
    public function setSvgObjMeta(int $soid, array $gradients = [], array $clippaths = []): void
    {
        if (!isset($this->svgobjs[$soid])) {
            $this->initSvgObjForHandlers($soid);
        }
        $this->patchSvgObj($soid, [
            'refunitval' => self::REFUNITVAL,
            'gradients' => $gradients,
            'clippaths' => $clippaths,
        ]);
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $parent
     * @phpstan-return array{0: string, 1: TSVGStyle}
     */
    public function exposeParseSVGStyleFont(array $svgstyle, array $parent): array
    {
        $out = $this->parseSVGStyleFont($svgstyle, $parent);
        return [$out, $svgstyle];
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-return array{0: string, 1: TSVGStyle}
     */
    public function exposeParseSVGStyleStroke(int $soid, array $svgstyle): array
    {
        $out = $this->parseSVGStyleStroke($soid, $svgstyle);
        return [$out, $svgstyle];
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-return array{0: string, 1: TSVGStyle}
     */
    public function exposeParseSVGStyleColor(array $svgstyle): array
    {
        $out = $this->parseSVGStyleColor($svgstyle);
        return [$out, $svgstyle];
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-return array{0: string, 1: TSVGStyle}
     */
    public function exposeParseSVGStyleClip(
        array $svgstyle,
        float $posx,
        float $posy,
        float $width,
        float $height,
    ): array {
        $out = $this->parseSVGStyleClip($svgstyle, $posx, $posy, $width, $height);
        return [$out, $svgstyle];
    }

    /**
     * @phpstan-param array<string, TSVGGradient> $gradients
     * @phpstan-param array<float> $clip_par
     */
    public function exposeParseSVGStyleGradient(
        int $soid,
        array $gradients,
        string $xref,
        float $grx,
        float $gry,
        float $grw,
        float $grh,
        string $clip_fnc = '',
        array $clip_par = [],
    ): string {
        return $this->parseSVGStyleGradient($soid, $gradients, $xref, $grx, $gry, $grw, $grh, $clip_fnc, $clip_par);
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param array<string, TSVGGradient> $gradients
     * @phpstan-param array<float> $clip_par
     * @phpstan-return array{0: string, 1: TSVGStyle}
     */
    public function exposeParseSVGStyleFill(
        int $soid,
        array $svgstyle,
        array $gradients,
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $clip_fnc = '',
        array $clip_par = [],
    ): array {
        $out = $this->parseSVGStyleFill(
            $soid,
            $svgstyle,
            $gradients,
            $posx,
            $posy,
            $width,
            $height,
            $clip_fnc,
            $clip_par
        );
        return [$out, $svgstyle];
    }

    /** @phpstan-param array<string, TSVGAttribs> $clippaths */
    public function exposeParseSVGStyleClipPath(\XMLParser $parser, int $soid, array $clippaths = []): void
    {
        $this->parseSVGStyleClipPath($parser, $soid, $clippaths);
    }

    /**
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     * @phpstan-param array<float> $clip_par
     * @phpstan-return array{0: string, 1: string}
     */
    public function exposeParseSVGStyle(
        \XMLParser $parser,
        int $soid,
        array $svgstyle,
        array $prev_svgstyle,
        float $posx = 0,
        float $posy = 0,
        float $width = 1,
        float $height = 1,
        string $objstyle = '',
        string $clip_fnc = '',
        array $clip_par = [],
    ): array {
        $out = $this->parseSVGStyle(
            $parser,
            $soid,
            $svgstyle,
            $prev_svgstyle,
            $posx,
            $posy,
            $width,
            $height,
            $objstyle,
            $clip_fnc,
            $clip_par,
        );
        return [$out, $objstyle];
    }

    public function initSvgObjForHandlers(int $soid): void
    {
        $svgobj = [
            'defsmode' => false,
            'clipmode' => false,
            'clipid' => 0,
            'tagdepth' => 1,
            'x0' => 0.0,
            'y0' => 0.0,
            'x' => 0.0,
            'y' => 0.0,
            'refunitval' => self::REFUNITVAL,
            'gradientid' => '',
            'gradients' => [],
            'clippaths' => [],
            'defs' => [],
            'cliptm' => self::TMXID,
            'styles' => [self::DEFSVGSTYLE],
            'child' => [],
            'xmldepth' => 0,
            'switchstack' => [],
            'markermode' => 0,
            'patternmode' => 0,
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
                'vertical' => false,
                'linkhref' => '',
                'linkx' => 0.0,
                'linky' => 0.0,
            ],
            'charskip' => 0,
            'text' => '',
            'dir' => '',
            'out' => '',
        ];
        $this->svgobjs[$soid] = $svgobj;
    }

    /** @phpstan-param array<string, mixed> $patch */
    public function patchSvgObj(int $soid, array $patch): void
    {
        /** @var TSVGObj $svgobj */
        $svgobj = \array_replace_recursive($this->svgobjs[$soid], $patch);
        // @phpstan-ignore-next-line assign.propertyType
        $this->svgobjs[$soid] = $svgobj;
    }

    /** @phpstan-return TSVGObj */
    public function getSvgObj(int $soid): array
    {
        return $this->svgobjs[$soid];
    }

    /** @return array<string, mixed> */
    public function exposeGetCurrentPageData(): array
    {
        return $this->page->getPage();
    }

    public function exposeHandlerSVGCharacter(\XMLParser $parser, string $data): void
    {
        $this->handlerSVGCharacter($parser, $data);
    }

    public function exposeHandleSVGTagEnd(\XMLParser $parser, string $name): void
    {
        $this->handleSVGTagEnd($parser, $name);
    }

    public function exposeHandleSVGCharacter(\XMLParser $parser, string $data): void
    {
        $this->handlerSVGCharacter($parser, $data);
    }

    public function exposeParseSVGTagENDdefs(int $soid): string
    {
        return $this->parseSVGTagENDdefs($soid);
    }

    public function exposeParseSVGTagENDclipPath(int $soid): string
    {
        return $this->parseSVGTagENDclipPath($soid);
    }

    public function exposeParseSVGTagENDsvg(int $soid): string
    {
        return $this->parseSVGTagENDsvg($soid);
    }

    public function exposeParseSVGTagENDg(int $soid): string
    {
        return $this->parseSVGTagENDg($soid);
    }

    public function exposeParseSVGTagENDtspan(int $soid): string
    {
        return $this->parseSVGTagENDtspan($soid);
    }

    public function exposeParseSVGTagENDtext(int $soid): string
    {
        return $this->parseSVGTagENDtext($soid);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TTMatrix $ctm
     */
    public function exposeHandleSVGTagStart(
        \XMLParser $parser,
        string $name,
        array $attr,
        int $soid = -1,
        bool $clipmode = false,
        array $ctm = self::TMXID,
    ): void {
        $this->handleSVGTagStart($parser, $name, $attr, $soid, $clipmode, $ctm);
    }

    public function exposeParseSVGTagSTARTdefs(int $soid): string
    {
        return $this->parseSVGTagSTARTdefs($soid);
    }

    /** @phpstan-param TTMatrix $tmx */
    public function exposeParseSVGTagSTARTclipPath(int $soid, array $tmx = self::TMXID): string
    {
        return $this->parseSVGTagSTARTclipPath($soid, $tmx);
    }

    public function exposeGetRawSVGData(string $img): string
    {
        return $this->getRawSVGData($img);
    }

    /** @phpstan-return TSVGSize */
    public function exposeGetSVGSize(string $data): array
    {
        return $this->getSVGSize($data);
    }

    public function exposePrescanSVGGradients(string $data, int $soid): void
    {
        $this->prescanSVGGradients($data, $soid);
    }

    /**
     * @phpstan-param TSVGAttributes $xmlAttr
     * @phpstan-return TSVGAttributes
     */
    public function exposeGetSVGPrescanAttributes(array $xmlAttr): array
    {
        return $this->getSVGPrescanAttributes($xmlAttr);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-return TSVGStyle
     */
    public function exposeGetSVGPrescanStopStyle(array $attr): array
    {
        return $this->getSVGPrescanStopStyle($attr);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTsvg(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTsvg($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTg(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTg($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTlinearGradient(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTlinearGradient($soid, $attr);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTradialGradient(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTradialGradient($soid, $attr);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     */
    public function exposeParseSVGTagSTARTstop(int $soid, array $attr, array $svgstyle): string
    {
        return $this->parseSVGTagSTARTstop($soid, $attr, $svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTpath(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTpath($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTrect(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTrect($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTcircle(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTcircle($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTellipse(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTellipse($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTline(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTline($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTpolygon(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
        bool $isPolyline = false,
    ): string {
        return $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle, $isPolyline);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTimage(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTimage($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTtext(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
        bool $is_tspan = false,
    ): string {
        return $this->parseSVGTagSTARTtext($parser, $soid, $attr, $svgstyle, $prev_svgstyle, $is_tspan);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTtspan(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTtspan($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    /**
     * @phpstan-param TSVGAttributes $attr
     * @phpstan-param TSVGStyle $svgstyle
     * @phpstan-param TSVGStyle $prev_svgstyle
     */
    public function exposeParseSVGTagSTARTtextPath(
        \XMLParser $parser,
        int $soid,
        array $attr,
        array $svgstyle,
        array $prev_svgstyle,
    ): string {
        return $this->parseSVGTagSTARTtextPath($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
    }

    public function exposeParseSVGTagENDtextPath(int $soid): string
    {
        return $this->parseSVGTagENDtextPath($soid);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTuse(\XMLParser $parser, int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTuse($parser, $soid, $attr);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTsymbol(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTsymbol($soid, $attr);
    }

    public function exposeParseSVGTagENDsymbol(int $soid): string
    {
        return $this->parseSVGTagENDsymbol($soid);
    }

    public function exposeParseSVGTagENDmarker(int $soid): string
    {
        return $this->parseSVGTagENDmarker($soid);
    }

    public function exposeParseSVGTagENDpattern(int $soid): string
    {
        return $this->parseSVGTagENDpattern($soid);
    }

    public function exposeParseSVGTagENDmask(int $soid): string
    {
        return $this->parseSVGTagENDmask($soid);
    }

    /** @phpstan-return ?TSVGAttribs */
    public function exposeResolveSVGPatternDef(int $soid, string $patternId): ?array
    {
        return $this->resolveSVGPatternDef($soid, $patternId);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTa(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTa($soid, $attr);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTmarker(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTmarker($soid, $attr);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTpattern(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTpattern($soid, $attr);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTmask(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTmask($soid, $attr);
    }

    public function exposeParseSVGTagENDa(int $soid): string
    {
        return $this->parseSVGTagENDa($soid);
    }

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTswitch(int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTswitch($soid, $attr);
    }

    public function exposeParseSVGTagENDswitch(int $soid): string
    {
        return $this->parseSVGTagENDswitch($soid);
    }
}
