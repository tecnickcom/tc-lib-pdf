<?php

/**
 * SVGTest.php
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

    public function exposeGetSVGExtGState(?float $strokingAlpha = null, ?float $nonstrokingAlpha = null, string $blendMode = 'Normal'): string
    {
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
    public function exposeParseSVGStyleClip(array $svgstyle, float $posx, float $posy, float $width, float $height): array
    {
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
        $out = $this->parseSVGStyleFill($soid, $svgstyle, $gradients, $posx, $posy, $width, $height, $clip_fnc, $clip_par);
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
        /** @var array{defsmode: bool, clipmode: bool, clipid: int, tagdepth: int, x0: float, y0: float, x: float, y: float, refunitval: TRefUnitValues, gradientid: string, gradients: array<string, TSVGGradient>, clippaths: array<string, TSVGAttribs>, defs: array<string, TSVGAttribs>, cliptm: array<float>, styles: array<int, TSVGStyle>, child: array<string, TSVGAttribChild>, textmode: TSVGTextMode, text: string, dir: string, out: string} $svgobj */
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
            'cliptm' => [],
            'styles' => [self::DEFSVGSTYLE],
            'child' => [],
            'textmode' => [
                'rtl' => false,
                'invisible' => false,
                'stroke' => 0,
                'text-anchor' => 'start',
            ],
            'text' => '',
            'dir' => '',
            'out' => '',
        ];
        $this->svgobjs[$soid] = $svgobj;
    }

    /** @phpstan-param array<string, mixed> $patch */
    public function patchSvgObj(int $soid, array $patch): void
    {
        /** @var array{defsmode: bool, clipmode: bool, clipid: int, tagdepth: int, x0: float, y0: float, x: float, y: float, refunitval: TRefUnitValues, gradientid: string, gradients: array<string, TSVGGradient>, clippaths: array<string, TSVGAttribs>, defs: array<string, TSVGAttribs>, cliptm: array<float>, styles: array<int, TSVGStyle>, child: array<string, TSVGAttribChild>, textmode: TSVGTextMode, text: string, dir: string, out: string} $svgobj */
        $svgobj = \array_replace_recursive($this->svgobjs[$soid], $patch);
        $this->svgobjs[$soid] = $svgobj;
    }

    /** @phpstan-return TSVGObj */
    public function getSvgObj(int $soid): array
    {
        return $this->svgobjs[$soid];
    }

    public function exposeHandlerSVGCharacter(\XMLParser $parser, string $data): void
    {
        $this->handlerSVGCharacter($parser, $data);
    }

    public function exposeHandleSVGTagEnd(\XMLParser $parser, string $name): void
    {
        $this->handleSVGTagEnd($parser, $name);
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
    ): string {
        return $this->parseSVGTagSTARTpolygon($parser, $soid, $attr, $svgstyle, $prev_svgstyle);
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

    /** @phpstan-param TSVGAttributes $attr */
    public function exposeParseSVGTagSTARTuse(\XMLParser $parser, int $soid, array $attr): string
    {
        return $this->parseSVGTagSTARTuse($parser, $soid, $attr);
    }
}

class SVGTest extends TestUtil
{
    public static function setUpBeforeClass(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    protected function getTestObject(): \Com\Tecnick\Pdf\Tcpdf
    {
        return new \Com\Tecnick\Pdf\Tcpdf();
    }

    protected function getInternalTestObject(): TestableSVG
    {
        return new TestableSVG();
    }

    private function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($obj);
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    /** @return array{pid: int, height: float} */
    private function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
        /** @var array{pid: int, height: float} $page */
        $page = $obj->addPage();
        return $page;
    }

    public function testAddSVGStoresInlineSvgObject(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="100" height="50">'
            . '<rect x="0" y="0" width="100" height="50" fill="#ff0000"/>'
            . '</svg>';

        $soid = $obj->addSVG($svg, 10, 20, 40, 20, $page['height']);

        $this->assertSame(1, $soid);
        /** @var array<int, array<string, mixed>> $svgobjs */
        $svgobjs = $this->getObjectProperty($obj, 'svgobjs');
        $this->assertArrayHasKey($soid, $svgobjs);
        $this->assertNotSame('', $svgobjs[$soid]['out']);
    }

    public function testAddSVGRejectsInvalidSvg(): void
    {
        $obj = $this->getTestObject();
        $this->initFontAndPage($obj);
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->addSVG('@<svg xmlns="http://www.w3.org/2000/svg"></svg>');
    }

    public function testGetSetSVGReturnsRenderableOutput(): void
    {
        $obj = $this->getTestObject();
        $page = $this->initFontAndPage($obj);
        $svg = '@<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20">'
            . '<circle cx="10" cy="10" r="8" fill="#00ff00"/>'
            . '</svg>';

        $soid = $obj->addSVG($svg, 5, 6, 12, 12, $page['height']);
        $out = $obj->getSetSVG($soid);

        $this->assertNotSame('', $out);
        $this->assertStringStartsWith("q\n", $out);
        $this->assertStringContainsString(" cm\n", $out);
        $this->assertStringEndsWith("Q\nQ\n", $out);
    }

    public function testGetSetSVGThrowsForUnknownId(): void
    {
        $obj = $this->getTestObject();
        $this->bcExpectException(\Com\Tecnick\Pdf\Exception::class);

        $obj->getSetSVG(999);
    }

    public function testParseSVGTMtranslateWithTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMtranslate('10.5, 20.3');

        $this->assertSame([1.0, 0.0, 0.0, 1.0, 10.5, 20.3], $result);
    }

    public function testParseSVGTMtranslateWithOneValue(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMtranslate('5.5');

        $this->assertSame([1.0, 0.0, 0.0, 1.0, 5.5, 0.0], $result);
    }

    public function testParseSVGTMscaleWithTwoValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMscale('2.0, 3.0');

        $this->assertSame([2.0, 0.0, 0.0, 3.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMscaleWithOneValue(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMscale('1.5');

        $this->assertSame([1.5, 0.0, 0.0, 1.5, 0.0, 0.0], $result);
    }

    public function testParseSVGTMrotateWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMrotate('45');

        $this->assertCount(6, $result);
        // Rotation matrix has cos and sin components that can be floating point
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMskewXWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMskewX('30');

        $this->assertCount(6, $result);
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMskewYWithDegrees(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMskewY('30');

        $this->assertCount(6, $result);
        $this->assertNotSame([1.0, 0.0, 0.0, 1.0, 0.0, 0.0], $result);
    }

    public function testParseSVGTMmatrixWithSixValues(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMmatrix('1 2 3 4 5 6');

        $this->assertSame([1.0, 2.0, 3.0, 4.0, 5.0, 6.0], $result);
    }

    public function testParseSVGTMmatrixWithCSVFormat(): void
    {
        $obj = $this->getInternalTestObject();

        $result = $obj->exposeParseSVGTMmatrix('1.5, 2.5, 3.5, 4.5, 5.5, 6.5');

        $this->assertSame([1.5, 2.5, 3.5, 4.5, 5.5, 6.5], $result);
    }

    public function testSvgUnitConvertersAndTransformHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setSvgRefUnit(1);

        $this->assertSame(72.0, $obj->exposeSvgUnitToPoints('1in', 1));
        $this->assertEqualsWithDelta(25.4, $obj->exposeSvgUnitToUnit('25.4mm', 1), 0.0001);

        $transformMatrix = $obj->exposeGetSVGTransformMatrix('translate(10,20) scale(2) rotate(0)');
        $this->assertCount(6, $transformMatrix);

        $converted = $obj->exposeConvertSVGMatrix([1, 0, 0, 1, 10, 20], 1);
        $this->assertCount(6, $converted);
        $this->assertSame(7.5, $converted[4]);
        $this->assertSame(-15.0, $converted[5]);

        $out = $obj->exposeGetOutSVGTransformation([1, 0, 0, 1, 10, 20], 1);
        $this->assertStringContainsString(' cm', $out);
    }

    public function testSvgTagPathAndCssAttributeHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->setSvgRefUnit(2);

        $this->assertSame('rect', $obj->exposeRemoveTagNamespace('svg:rect'));
        $this->assertSame('', $obj->exposeGetSVGPath(2, 'M 0 0 L 10 10 Z', ''));

        $path = $obj->exposeGetSVGPath(2, 'M 0 0 L 10 10 H 5 V 3 Z', 'S');
        $this->assertStringContainsString('m', $path);
        $this->assertStringContainsString('l', $path);
        $this->assertStringContainsString("h\n", $path);

        $tag = 'font-size: 12px; font-weight: bold;';
        $this->assertSame('12px', $obj->exposeParseCSSAttrib($tag, 'font-size', '10px'));
        $this->assertSame('fallback', $obj->exposeParseCSSAttrib($tag, 'line-height', 'fallback'));
    }

    public function testSvgTextAttributeAndBlendAlphaHelpers(): void
    {
        $obj = $this->getInternalTestObject();

        $this->assertSame(0.0, $obj->exposeGetTALetterSpacing('normal'));
        $this->assertSame(1.25, $obj->exposeGetTALetterSpacing('inherit', 1.25));
        $this->assertGreaterThan(0, $obj->exposeGetTALetterSpacing('2px'));

        $this->assertSame(70.0, $obj->exposeGetTAFontStretching('condensed'));
        $this->assertSame(120.0, $obj->exposeGetTAFontStretching('wider', 110));
        $this->assertSame(95.0, $obj->exposeGetTAFontStretching('narrower', 105));
        $this->assertSame(80.0, $obj->exposeGetTAFontStretching('80%'));

        $this->assertSame('B', $obj->exposeGetTAFontWeight('bold'));
        $this->assertSame('', $obj->exposeGetTAFontWeight('normal'));
        $this->assertSame('I', $obj->exposeGetTAFontStyle('italic'));
        $this->assertSame('', $obj->exposeGetTAFontStyle('normal'));
        $this->assertSame('U', $obj->exposeGetTAFontDecoration('underline'));
        $this->assertSame('O', $obj->exposeGetTAFontDecoration('overline'));
        $this->assertSame('D', $obj->exposeGetTAFontDecoration('line-through'));
        $this->assertSame('', $obj->exposeGetTAFontDecoration('none'));

        $this->assertSame('Multiply', $obj->exposeNormalizeSVGBlendMode('multiply'));
        $this->assertSame('Normal', $obj->exposeNormalizeSVGBlendMode('no-such-mode'));
        $this->assertSame(1.0, $obj->exposeNormalizeSVGAlphaValue(1.5));
        $this->assertSame(0.0, $obj->exposeNormalizeSVGAlphaValue(-0.4));
        $this->assertSame(0.5, $obj->exposeNormalizeSVGAlphaValue('0.5'));

        $this->assertSame('', $obj->exposeGetSVGExtGState(null, null, ''));
        $gstate = $obj->exposeGetSVGExtGState(0.8, 0.5, 'Multiply');
        $this->assertStringContainsString('gs', $gstate);
    }

    public function testSvgPathCommandHelpersProducePathOperations(): void
    {
        $obj = $this->getInternalTestObject();

        $coord = $obj->getPathCoordDefaults();
        [$mout, $coord] = $obj->exposeSvgPathCmdM([1, 2, 3, 4], $coord);
        $this->assertStringContainsString('m', $mout);

        [$lout, $coord] = $obj->exposeSvgPathCmdL([5, 6, 7, 8], $coord);
        $this->assertStringContainsString('l', $lout);

        [$hout, $coord] = $obj->exposeSvgPathCmdH([9, 10], $coord);
        $this->assertStringContainsString('l', $hout);

        [$vout, $coord] = $obj->exposeSvgPathCmdV([11, 12], $coord);
        $this->assertStringContainsString('l', $vout);

        [$cout, $coord] = $obj->exposeSvgPathCmdC([13, 14, 15, 16, 17, 18], $coord);
        $this->assertStringContainsString('c', $cout);

        [$qout, $coord] = $obj->exposeSvgPathCmdQ([19, 20, 21, 22], $coord);
        $this->assertStringContainsString('c', $qout);

        $spaths = [['0', 'C'], ['1', 'S']];
        [$sout, $coord] = $obj->exposeSvgPathCmdS([23, 24, 25, 26], $coord, $spaths, 1);
        $this->assertStringContainsString('c', $sout);

        $tpaths = [['0', 'Q'], ['1', 'T']];
        [$tout, $coord] = $obj->exposeSvgPathCmdT([27, 28], $coord, $tpaths, 1);
        $this->assertStringContainsString('c', $tout);

        $arcPaths = [['0', 'A'], ['1', 'z']];
        [$aout, $coord] = $obj->exposeSvgPathCmdA([5, 5, 0, 0, 1, 30, 35], $coord, $arcPaths, 0, ['5', '5', '0', '0', '1', '30', '35']);
        $this->assertNotSame('', $aout);

        [$zout, $coord] = $obj->exposeSvgPathCmdZ($coord);
        $this->assertSame("h\n", $zout);
        $this->assertSame($coord['xinit'], $coord['x']);
        $this->assertSame($coord['yinit'], $coord['y']);
    }

    public function testSvgStyleHelperMethodsCoverCorePaths(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->setSvgObjMeta(3);

        $base = $obj->exposeDefaultSVGStyle();

        [$fontOut, $fontStyle] = $obj->exposeParseSVGStyleFont($base, $base);
        $this->assertNotSame('', $fontOut);
        $this->assertArrayHasKey('font-size-val', $fontStyle);

        $strokeStyle = $base;
        $strokeStyle['stroke'] = '#112233';
        $strokeStyle['stroke-width'] = 1.0;
        $strokeStyle['stroke-linecap'] = 'butt';
        $strokeStyle['stroke-linejoin'] = 'miter';
        $strokeStyle['stroke-opacity'] = 1.0;
        $strokeStyle['opacity'] = 1.0;
        $strokeStyle['mix-blend-mode'] = 'normal';
        $strokeStyle['objstyle'] = '';
        [$strokeOut, $strokeStyle] = $obj->exposeParseSVGStyleStroke(3, $strokeStyle);
        $this->assertNotSame('', $strokeOut);
        $this->assertStringContainsString('D', $strokeStyle['objstyle']);

        $colorStyle = $base;
        $colorStyle['opacity'] = 0.5;
        $colorStyle['mix-blend-mode'] = 'multiply';
        $colorStyle['color'] = '#ff0000';
        $colorStyle['text-color'] = '#00ff00';
        [$colorOut] = $obj->exposeParseSVGStyleColor($colorStyle);
        $this->assertNotSame('', $colorOut);

        $clipStyle = $base;
        $clipStyle['clip'] = 'rect(1 2 3 4)';
        $clipStyle['clip-rule'] = 'evenodd';
        [$clipOut] = $obj->exposeParseSVGStyleClip($clipStyle, 10, 20, 100, 50);
        $this->assertNotSame('', $clipOut);

        $this->assertSame('', $obj->exposeParseSVGStyleGradient(3, [], 'missing', 0, 0, 10, 10));

        $fillNone = $base;
        $fillNone['fill'] = 'none';
        [$fillNoneOut] = $obj->exposeParseSVGStyleFill(3, $fillNone, [], 0, 0, 10, 10);
        $this->assertSame('', $fillNoneOut);

        $fillUrl = $base;
        $fillUrl['fill'] = 'url(#missing)';
        $fillUrl['opacity'] = 1.0;
        $fillUrl['fill-opacity'] = 1.0;
        [$fillUrlOut] = $obj->exposeParseSVGStyleFill(3, $fillUrl, [], 0, 0, 10, 10);
        $this->assertSame('', $fillUrlOut);

        $parser = \xml_parser_create();
        try {
            $obj->exposeParseSVGStyleClipPath($parser, 3, []);

            $earlyStyle = $base;
            $earlyStyle['opacity'] = 0;
            [$styleOut, $objstyle] = $obj->exposeParseSVGStyle($parser, 3, $earlyStyle, $base);
            $this->assertSame('', $styleOut);
            $this->assertSame('', $objstyle);
        } finally {
            // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.xml_parser_freeDeprecated
            \xml_parser_free($parser);
        }
    }

    public function testSvgRawSizeAndPrescanHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $obj->initSvgObjForHandlers(40);

        $inline = '<svg width="10" height="20"></svg>';
        $this->assertSame($inline, $obj->exposeGetRawSVGData('@' . $inline));
        $this->assertSame('', $obj->exposeGetRawSVGData('@'));

        $tmp = \tempnam(\sys_get_temp_dir(), 'tc-svg-');
        $this->assertNotFalse($tmp);
        \file_put_contents($tmp, $inline);
        try {
            $this->assertSame($inline, $obj->exposeGetRawSVGData($tmp));
        } finally {
            @\unlink($tmp);
        }

        $size = $obj->exposeGetSVGSize('<svg x="1" y="2" width="100" height="50" viewBox="0 0 100 50" preserveAspectRatio="xMinYMin meet"></svg>');
        $this->assertGreaterThan(0, $size['width']);
        $this->assertGreaterThan(0, $size['height']);
        $this->assertSame('xMinYMin', $size['ar_align']);

        $attr = $obj->exposeGetSVGPrescanAttributes(['id' => 'g1', 'stop-color' => '#fff', 'junk' => 'x']);
        $this->assertArrayHasKey('id', $attr);
        $this->assertArrayHasKey('stop-color', $attr);
        $this->assertArrayNotHasKey('junk', $attr);

        $stopStyle = $obj->exposeGetSVGPrescanStopStyle(['style' => 'stop-color: #123456; stop-opacity: 0.25']);
        $this->assertSame('#123456', $stopStyle['stop-color']);
        $this->assertSame(0.25, $stopStyle['stop-opacity']);

        $obj->exposePrescanSVGGradients(
            '<svg><defs><linearGradient id="lg"><stop offset="0%" stop-color="#000"/><stop offset="100%" style="stop-color:#fff;stop-opacity:0.4"/></linearGradient></defs></svg>',
            40,
        );
        $svgobj = $obj->getSvgObj(40);
        $this->assertArrayHasKey('lg', $svgobj['gradients']);
        $this->assertGreaterThanOrEqual(1, \count($svgobj['gradients']['lg']['stops']));
    }

    public function testSvgTagHandlersAndEndStartHelpers(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(41);

        $parser = \xml_parser_create('UTF-8');
        try {
            $obj->exposeHandlerSVGCharacter($parser, 'Hello');
            $svgobj = $obj->getSvgObj(41);
            $this->assertSame('Hello', $svgobj['text']);

            $obj->exposeParseSVGTagSTARTdefs(41);
            $this->assertTrue($obj->getSvgObj(41)['defsmode']);
            $obj->exposeParseSVGTagENDdefs(41);
            $this->assertFalse($obj->getSvgObj(41)['defsmode']);

            $obj->exposeParseSVGTagSTARTclipPath(41, [1, 0, 0, 1, 0, 0]);
            $this->assertTrue($obj->getSvgObj(41)['clipmode']);
            $obj->exposeParseSVGTagENDclipPath(41);
            $this->assertFalse($obj->getSvgObj(41)['clipmode']);

            $obj->patchSvgObj(41, ['styles' => [$obj->exposeDefaultSVGStyle(), $obj->exposeDefaultSVGStyle()]]);
            $this->assertSame('', $obj->exposeParseSVGTagENDg(41));
            $this->assertCount(1, $obj->getSvgObj(41)['styles']);

            $obj->patchSvgObj(41, ['textmode' => ['invisible' => true]]);
            $this->assertSame('', $obj->exposeParseSVGTagENDtext(41));
            $this->assertSame('', $obj->exposeParseSVGTagENDtspan(41));

            $obj->patchSvgObj(41, ['tagdepth' => 1]);
            $this->assertSame('', $obj->exposeParseSVGTagENDsvg(41));

            $obj->patchSvgObj(41, ['clipmode' => true, 'cliptm' => [1, 0, 0, 1, 0, 0]]);
            $obj->exposeHandleSVGTagStart($parser, 'rect', ['x' => '1', 'y' => '2'], 41, false, [1, 0, 0, 1, 0, 0]);
            $this->assertNotEmpty($obj->getSvgObj(41)['clippaths']);

            $obj->patchSvgObj(41, ['defsmode' => false]);
            $obj->exposeHandleSVGTagEnd($parser, 'defs');
            $this->assertFalse($obj->getSvgObj(41)['defsmode']);
        } finally {
            // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.xml_parser_freeDeprecated
            \xml_parser_free($parser);
        }
    }

    public function testSvgRemainingStartTagMethodsCoveredViaGuardBranches(): void
    {
        $obj = $this->getInternalTestObject();
        $this->initFontAndPage($obj);
        $obj->initSvgObjForHandlers(42);
        $base = $obj->exposeDefaultSVGStyle();

        $parser = \xml_parser_create('UTF-8');
        try {
            // Root svg branch exits immediately.
            $obj->patchSvgObj(42, ['tagdepth' => 0]);
            $this->assertSame('', $obj->exposeParseSVGTagSTARTsvg($parser, 42, [], $base, $base));

            // Group start path should return a transform block.
            $gOut = $obj->exposeParseSVGTagSTARTg($parser, 42, [], $base, $base);
            $this->assertNotSame('', $gOut);

            // Gradient starts and stop insertion.
            $this->assertSame('', $obj->exposeParseSVGTagSTARTlinearGradient(42, ['id' => 'lg2']));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTradialGradient(42, ['id' => 'rg2']));
            $obj->patchSvgObj(42, ['gradientid' => 'lg2']);
            $this->assertSame('', $obj->exposeParseSVGTagSTARTstop(42, ['offset' => '50%'], $base));
            $this->assertNotEmpty($obj->getSvgObj(42)['gradients']['lg2']['stops']);

            // Invisibility/clip guards for shape and text tags.
            $obj->patchSvgObj(42, ['textmode' => ['invisible' => true], 'clipmode' => false]);
            $this->assertSame('', $obj->exposeParseSVGTagSTARTpath($parser, 42, ['d' => 'M 0 0 L 1 1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTrect($parser, 42, ['width' => '1', 'height' => '1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTcircle($parser, 42, ['r' => '1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTellipse($parser, 42, ['rx' => '1', 'ry' => '1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTline($parser, 42, ['x1' => '0', 'y1' => '0', 'x2' => '1', 'y2' => '1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTpolygon($parser, 42, ['points' => '0,0 1,1'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTimage($parser, 42, ['xlink:href' => 'x.png'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTtext($parser, 42, ['x' => '0', 'y' => '0'], $base, $base));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTtspan($parser, 42, ['dx' => '1'], $base, $base));

            // use tag guards when href is missing or unknown.
            $this->assertSame('', $obj->exposeParseSVGTagSTARTuse($parser, 42, []));
            $this->assertSame('', $obj->exposeParseSVGTagSTARTuse($parser, 42, ['xlink:href' => '#missing']));
        } finally {
            // phpcs:ignore PHPCompatibility.FunctionUse.RemovedFunctions.xml_parser_freeDeprecated
            \xml_parser_free($parser);
        }
    }
}
