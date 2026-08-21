<?php

/**
 * PdfaCoreFontGlyphNameTest.php
 *
 * @since       2026-08-21
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

use Com\Tecnick\Unicode\Data\Encoding;

/**
 * Regression tests for the glyph names of the PDF/A core font substitutes.
 *
 * The PDF/A substitute fonts are Type1 programs embedded verbatim, and their glyphs are selected
 * by name. A code whose declared encoding names a glyph the embedded program does not define is
 * drawn as .notdef, while its width and its ToUnicode mapping stay correct.
 * See: https://github.com/tecnickcom/tc-lib-pdf/issues/268
 *
 * @since       2026-08-21
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TFontData from \Com\Tecnick\Pdf\Font\Load
 */
class PdfaCoreFontGlyphNameTest extends TestUtil
{
    /**
     * Glyph names that WinAnsiEncoding assigns to the codes the PDF/A substitutes used to name
     * with their pre-Adobe-Glyph-List equivalents.
     */
    private const AGL_NAME = [
        181 => 'mu',
        183 => 'periodcentered',
        223 => 'germandbls',
    ];

    /**
     * The pre-Adobe-Glyph-List names of the same glyphs.
     */
    private const LEGACY_NAME = ['micro', 'middot', 'ssharp'];

    /**
     * The families and styles that are substituted by an embedded Type1 program.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function textFontProvider(): array
    {
        $fonts = [];
        foreach (['helvetica', 'times', 'courier'] as $family) {
            foreach (['', 'B', 'I', 'BI'] as $style) {
                $fonts[$family . ($style === '' ? '' : '-' . $style)] = [$family, $style];
            }
        }

        return $fonts;
    }

    /**
     * Every substituted family, including the two symbolic ones.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function pdfaFontProvider(): array
    {
        return self::textFontProvider()
        + [
            'symbol' => ['symbol', ''],
            'zapfdingbats' => ['zapfdingbats', ''],
        ];
    }

    /**
     * Load a family in PDF/A mode and return its font data.
     *
     * @return TFontData
     *
     * @throws \Throwable
     */
    private function loadPdfaFont(string $family, string $style): array
    {
        self::setUpFontsPath();
        $pdf = new \Com\Tecnick\Pdf\Tcpdf(mode: 'pdfa3a');
        $pdf->addPage();
        $font = $pdf->font->insert($pdf->pon, $family, $style, 12);
        $data = $pdf->font->getFont($font['key']);

        $this->assertSame('Type1', $data['type'], 'The PDF/A substitute must be an embedded Type1 font');
        $this->assertStringStartsWith('PDFA', $data['name'], 'The PDF/A substitute font must be used');

        return $data;
    }

    /**
     * Decrypt the eexec section of a Type1 font program.
     */
    private static function eexecDecrypt(string $data): string
    {
        $rnd = 55665;
        $out = '';
        $len = \strlen($data);
        for ($idx = 0; $idx < $len; ++$idx) {
            $byte = \ord($data[$idx]);
            $out .= \chr($byte ^ ($rnd >> 8));
            $rnd = ((($byte + $rnd) * 52845) + 22719) & 0xFFFF;
        }

        // the first four plaintext bytes are the random lead of the eexec section
        return \substr($out, 4);
    }

    /**
     * Read the embedded font program and return its cleartext and decrypted private sections.
     *
     * @param TFontData $data Font data.
     *
     * @return array{0: string, 1: string}
     *
     * @throws \Throwable
     */
    private function readFontProgram(array $data): array
    {
        $path = \dirname($data['ifile']) . '/' . $data['file'];
        $this->assertFileExists($path, 'The embedded font program must be available');

        $raw = \gzuncompress((string) \file_get_contents($path));
        $this->assertIsString($raw, 'The embedded font program must be a valid zlib stream');

        return [
            \substr($raw, 0, $data['size1']),
            self::eexecDecrypt(\substr($raw, $data['size1'], $data['size2'])),
        ];
    }

    /**
     * Return the glyph names defined by the CharStrings dictionary of a font program.
     *
     * @return array<string, true>
     */
    private static function charstringNames(string $private): array
    {
        $names = [];
        $matches = [];
        \preg_match_all('~/([\w.]+)\s+\d+\s+(?:RD|-\|)\s~', $private, $matches);
        foreach ($matches[1] ?? [] as $name) {
            $names[$name] = true;
        }

        return $names;
    }

    /**
     * Return the built-in encoding vector of a font program.
     *
     * @return array<int, string>
     */
    private static function builtinEncoding(string $cleartext): array
    {
        $encoding = [];
        $matches = [];
        \preg_match_all('~dup\s+(\d+)\s*/([\w.]+)\s+put~', $cleartext, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $code = $match[1] ?? '';
            $name = $match[2] ?? '';
            if ($code === '' || $name === '') {
                continue;
            }

            $encoding[(int) $code] = $name;
        }

        return $encoding;
    }

    /**
     * Return the encoding that selects the glyphs of the emitted font.
     *
     * A font with an empty encoding is emitted without an /Encoding entry, so its built-in vector
     * is in force. Any other font is emitted as WinAnsiEncoding, overlaid with the /Differences
     * array when it carries one.
     *
     * @param TFontData          $data    Font data.
     * @param array<int, string> $builtin Built-in encoding vector of the font program.
     *
     * @return array<int, string>
     */
    private static function declaredEncoding(array $data, array $builtin): array
    {
        if ($data['enc'] === '') {
            return $builtin;
        }

        $encoding = Encoding::MAP['cp1252'] ?? [];
        $code = 0;
        $tokens = [];
        \preg_match_all('/\S+/', $data['diff'], $tokens);
        foreach ($tokens[0] ?? [] as $token) {
            if (\str_starts_with($token, '/')) {
                $encoding[$code] = \substr($token, 1);
                ++$code;
                continue;
            }

            $code = (int) $token;
        }

        return $encoding;
    }

    /**
     * Every code the font declares a width for must resolve, through the encoding the font
     * dictionary declares, to a glyph the embedded program defines.
     *
     * @throws \Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('pdfaFontProvider')]
    public function testEveryDefinedCodeResolvesToAnEmbeddedGlyph(string $family, string $style): void
    {
        $data = $this->loadPdfaFont($family, $style);
        [$cleartext, $private] = $this->readFontProgram($data);
        $glyphs = self::charstringNames($private);
        $encoding = self::declaredEncoding($data, self::builtinEncoding($cleartext));

        $missing = [];
        foreach (\array_keys($data['cw']) as $code) {
            $name = $encoding[$code] ?? '.notdef';
            if ($name === '.notdef' || isset($glyphs[$name])) {
                continue;
            }

            $missing[] = $code . ' /' . $name;
        }

        $this->assertSame(
            [],
            $missing,
            $data['name'] . ': the embedded program defines no glyph for ' . \implode(', ', $missing),
        );
    }

    /**
     * The three codes of the reported defect must resolve to their Adobe Glyph List names.
     *
     * @throws \Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('textFontProvider')]
    public function testLatinSupplementGlyphsUseTheAdobeGlyphListNames(string $family, string $style): void
    {
        $data = $this->loadPdfaFont($family, $style);
        [$cleartext, $private] = $this->readFontProgram($data);
        $glyphs = self::charstringNames($private);
        $builtin = self::builtinEncoding($cleartext);

        foreach (self::AGL_NAME as $code => $name) {
            $this->assertArrayHasKey(
                $name,
                $glyphs,
                $data['name'] . ': the CharStrings dictionary defines no /' . $name . ' for code ' . $code,
            );
            $this->assertSame(
                $name,
                $builtin[$code] ?? '',
                $data['name'] . ': the built-in encoding must name code ' . $code . ' /' . $name,
            );
            $this->assertArrayHasKey($code, $data['cw'], $data['name'] . ': code ' . $code . ' must have a width');
        }
    }

    /**
     * No pre-Adobe-Glyph-List name may survive in an embedded program that is emitted with the
     * WinAnsiEncoding, since that encoding cannot address them.
     *
     * @throws \Throwable
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('textFontProvider')]
    public function testEmbeddedProgramCarriesNoLegacyGlyphName(string $family, string $style): void
    {
        $data = $this->loadPdfaFont($family, $style);
        [$cleartext, $private] = $this->readFontProgram($data);
        $glyphs = self::charstringNames($private);

        foreach (self::LEGACY_NAME as $name) {
            $this->assertArrayNotHasKey(
                $name,
                $glyphs,
                $data['name'] . ': the CharStrings dictionary still defines the pre-AGL name /' . $name,
            );
        }
    }

    /**
     * ZapfDingbats declares no encoding, so it is emitted without an /Encoding entry and its
     * built-in vector selects the glyphs. Declaring one would make every code unreachable, since
     * none of its glyph names belongs to the WinAnsiEncoding.
     *
     * @throws \Throwable
     */
    public function testZapfDingbatsIsEmittedWithoutAnEncodingEntry(): void
    {
        $data = $this->loadPdfaFont('zapfdingbats', '');

        $this->assertSame('', $data['enc'], 'ZapfDingbats must declare no encoding');
        $this->assertSame('', $data['diff'], 'ZapfDingbats must carry no /Differences array');
    }
}
