<?php

/**
 * ConformanceMatrixTest.php
 *
 * @since       2026-08-31
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

use Com\Tecnick\Pdf\Encrypt\Encrypt as ObjEncrypt;
use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Structural invariants of the whole document for each conformance mode.
 *
 * These assert on the complete getOutPDFString() output rather than on a single
 * emitter, which is the only level at which requirements such as font embedding
 * or the presence of a structure tree are observable.
 *
 * @since       2026-08-31
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TAnnotOpts from \Com\Tecnick\Pdf\Base
 */
class ConformanceMatrixTest extends TestUtil
{
    /**
     * @throws \Throwable
     */
    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    /**
     * Build a minimal one page document in the given conformance mode.
     *
     * @throws \Throwable
     */
    private function buildDocument(string $mode, ?ObjEncrypt $encrypt = null): string
    {
        $pdf = new Tcpdf('mm', true, false, true, $mode, $encrypt);
        $pdf->setTitle('Conformance matrix document');
        $pdf->setAuthor('Test Author');
        $pdf->setSubject('Test Subject');
        $pdf->setKeywords('test keywords');
        $pdf->setCreator('Test Creator');
        $pdf->setLanguage('en-US');

        if (\in_array($mode, ['pdfx4', 'pdfx5'], true)) {
            // These parts require an embedded destination profile.
            $pdf->setSRGB(true);
        }

        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<h1>Heading</h1><p>Body text.</p>', posx: 15, posy: 20, width: 180);

        return $pdf->getOutPDFString();
    }

    /**
     * Extract the XMP packet from a rendered document.
     */
    private function extractXmp(string $pdf): string
    {
        $matches = [];
        if (\preg_match('#<\?xpacket begin=.*?<\?xpacket end="w"\?>#s', $pdf, $matches) !== 1) {
            return '';
        }

        return $matches[0] ?? '';
    }

    /**
     * Every conformance mode that requires embedded fonts must embed the core font.
     *
     * @throws \Throwable
     */
    #[DataProvider('embeddedFontModeProvider')]
    public function testConformanceModesEmbedFontPrograms(string $mode): void
    {
        $out = $this->buildDocument($mode);

        $this->assertMatchesRegularExpression('#/FontFile\d?\s#', $out);
        $this->assertStringNotContainsString('/BaseFont /Helvetica ', $out);
    }

    /** @return array<string, array{0: string}> */
    public static function embeddedFontModeProvider(): array
    {
        return [
            'pdfa1b' => ['pdfa1b'],
            'pdfa2b' => ['pdfa2b'],
            'pdfa3b' => ['pdfa3b'],
            'pdfx1a' => ['pdfx1a'],
            'pdfx3' => ['pdfx3'],
            'pdfx4' => ['pdfx4'],
            'pdfua1' => ['pdfua1'],
            'pdfua2' => ['pdfua2'],
        ];
    }

    /**
     * Tagged modes (PDF/UA and PDF/A conformance level A) must carry a structure tree.
     *
     * @throws \Throwable
     */
    #[DataProvider('taggedModeProvider')]
    public function testTaggedModesEmitStructureTreeAndMarkInfo(string $mode): void
    {
        $out = $this->buildDocument($mode);

        $this->assertStringContainsString('/StructTreeRoot ', $out);
        $this->assertStringContainsString('/MarkInfo << /Marked true >>', $out);
        $this->assertStringContainsString('/Lang ', $out);
    }

    /** @return array<string, array{0: string}> */
    public static function taggedModeProvider(): array
    {
        return [
            'pdfa1a' => ['pdfa1a'],
            'pdfa2a' => ['pdfa2a'],
            'pdfa3a' => ['pdfa3a'],
            'pdfua1' => ['pdfua1'],
            'pdfua2' => ['pdfua2'],
        ];
    }

    /**
     * A page-spanning table replays its header on each continuation page and
     * measures fragments into a discarded buffer. Both paths suspend tagging, and
     * they nest, so the suspension has to unwind exactly for the structure tree to
     * survive in every tagged mode.
     *
     * @throws \Throwable
     */
    #[DataProvider('taggedModeProvider')]
    public function testTaggedModesKeepStructureAcrossPageSpanningTable(string $mode): void
    {
        $rows = '';
        for ($idx = 1; $idx <= 400; ++$idx) {
            $rows .= '<tr><td>Row ' . $idx . '</td><td>Value ' . $idx . '</td></tr>';
        }

        $pdf = new Tcpdf('mm', true, false, true, $mode);
        $pdf->setTitle('Page spanning table');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(
            html: '<table border="1"><thead><tr><th>A</th><th>B</th></tr></thead><tbody>' . $rows . '</tbody></table>',
            posx: 15,
            posy: 20,
            width: 180,
        );

        $out = $pdf->getOutPDFString();

        $this->assertGreaterThan(1, \count($pdf->page->getPages()));
        $this->assertStringContainsString('/StructTreeRoot ', $out);
        // The table is one logical element however many pages it covers, and the
        // headers replayed on each continuation page are artifacts rather than extra
        // structure elements: one TH per column, not one per column and page.
        $this->assertSame(1, \substr_count($out, '/S /Table'));
        $this->assertSame(2, \substr_count($out, '/S /TH'));
        $this->assertSame(401, \substr_count($out, '/S /TR'));
    }

    /**
     * Conformance levels that are not tagged must not claim a structure tree.
     *
     * @throws \Throwable
     */
    #[DataProvider('untaggedModeProvider')]
    public function testUntaggedModesOmitStructureTree(string $mode): void
    {
        $out = $this->buildDocument($mode);

        $this->assertStringNotContainsString('/StructTreeRoot ', $out);
        $this->assertStringNotContainsString('/MarkInfo', $out);
    }

    /** @return array<string, array{0: string}> */
    public static function untaggedModeProvider(): array
    {
        return [
            'none' => [''],
            'pdfa1b' => ['pdfa1b'],
            'pdfa2b' => ['pdfa2b'],
            'pdfa2u' => ['pdfa2u'],
            'pdfa3b' => ['pdfa3b'],
            'pdfx1a' => ['pdfx1a'],
            'pdfx4' => ['pdfx4'],
        ];
    }

    /**
     * Each conformance mode is defined against one exact PDF version.
     *
     * @throws \Throwable
     */
    #[DataProvider('headerVersionProvider')]
    public function testConformanceModesPinThePdfHeaderVersion(string $mode, string $expected): void
    {
        $this->assertStringStartsWith('%PDF-' . $expected, $this->buildDocument($mode));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function headerVersionProvider(): array
    {
        return [
            'pdfa1b' => ['pdfa1b', '1.4'],
            'pdfa2b' => ['pdfa2b', '1.7'],
            'pdfa3b' => ['pdfa3b', '1.7'],
            // ISO 15930-4 and ISO 15930-6 define a conforming file as a PDF 1.4 file.
            'pdfx1a' => ['pdfx1a', '1.4'],
            'pdfx3' => ['pdfx3', '1.4'],
            // ISO 15930-7 and ISO 15930-8 are based on PDF 1.6.
            'pdfx4' => ['pdfx4', '1.6'],
            'pdfx5' => ['pdfx5', '1.6'],
            'pdfua1' => ['pdfua1', '1.7'],
            'pdfua2' => ['pdfua2', '2.0'],
        ];
    }

    /**
     * ISO 19005 and ISO 15930 forbid encryption, so an injected encryption object
     * must be dropped rather than producing ciphertext with no /Encrypt dictionary.
     *
     * @throws \Throwable
     */
    #[DataProvider('encryptionForbiddenModeProvider')]
    public function testModesForbiddingEncryptionEmitReadableContent(string $mode): void
    {
        $encrypt = new ObjEncrypt(true, '', 2, ['print'], 'user-pass', 'owner-pass');

        \set_error_handler(
            static fn(int $errno, string $errstr): bool => $errno === E_USER_WARNING
            && \str_contains($errstr, 'Encryption is not allowed'),
        );

        try {
            $out = $this->buildDocument($mode, $encrypt);
        } finally {
            \restore_error_handler();
        }

        $this->assertStringNotContainsString(' /Encrypt ', $out);
        // The metadata must stay readable: ciphertext here would mean the document
        // was encrypted while the decryption key was omitted from the trailer.
        $this->assertStringContainsString('Conformance matrix document', $out);
    }

    /** @return array<string, array{0: string}> */
    public static function encryptionForbiddenModeProvider(): array
    {
        return [
            'pdfa1b' => ['pdfa1b'],
            'pdfa3b' => ['pdfa3b'],
            'pdfx1a' => ['pdfx1a'],
            'pdfx4' => ['pdfx4'],
        ];
    }

    /**
     * ISO 19005 requires each Info dictionary entry with an analogous predefined XMP
     * property to be mirrored in the metadata stream with an equivalent value.
     *
     * @throws \Throwable
     */
    public function testInfoDictionaryEntriesAreMirroredInXmp(): void
    {
        $out = $this->buildDocument('pdfa3b');
        $xmp = $this->extractXmp($out);

        $this->assertNotSame('', $xmp);

        $expected = [
            '<dc:title>' => '/Title',
            '<dc:creator>' => '/Author',
            '<dc:description>' => '/Subject',
            '<pdf:Keywords>' => '/Keywords',
            '<xmp:CreatorTool>' => '/Creator',
            '<pdf:Producer>' => '/Producer',
            '<xmp:CreateDate>' => '/CreationDate',
            '<xmp:ModifyDate>' => '/ModDate',
            '<pdf:Trapped>' => '/Trapped',
        ];

        foreach ($expected as $xmpTag => $infoKey) {
            $this->assertStringContainsString($xmpTag, $xmp, $infoKey . ' has no XMP counterpart');
            $this->assertStringContainsString($infoKey, $out);
        }

        $this->assertStringContainsString('<pdf:Trapped>False</pdf:Trapped>', $xmp);
        $this->assertStringContainsString('/Trapped /False', $out);
    }

    /**
     * The trapping status must stay identical in both places.
     *
     * @throws \Throwable
     */
    public function testSetTrappedDrivesBothInfoDictionaryAndXmp(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle('Trapped document');
        $pdf->setTrapped('unknown');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/Trapped /Unknown', $out);
        $this->assertStringContainsString('<pdf:Trapped>Unknown</pdf:Trapped>', $out);
    }

    /**
     * The PDF/X output intent must identify a real printing condition.
     *
     * @throws \Throwable
     */
    public function testPdfxOutputIntentUsesRegisteredConditionAndProfile(): void
    {
        $out = $this->buildDocument('pdfx4');

        // The PDF/X part names are not registered characterization names.
        $this->assertStringContainsString('/OutputConditionIdentifier (OFCOM_PO_P1_F60_95)', $out);
        $this->assertStringContainsString('/DestOutputProfile ', $out);
        $this->assertStringNotContainsString('PDF/X-4)', $out);
    }

    /**
     * ISO 15930-7 and ISO 15930-8 make the destination profile mandatory, so its
     * absence has to be reported rather than silently emitted.
     *
     * @throws \Throwable
     */
    public function testPdfx4WarnsWhenNoDestinationProfileIsAvailable(): void
    {
        $warned = false;
        \set_error_handler(static function (int $errno, string $errstr) use (&$warned): bool {
            if ($errno !== E_USER_WARNING || !\str_contains($errstr, 'output intent ICC profile')) {
                return false;
            }

            $warned = true;
            return true;
        });

        try {
            $pdf = new Tcpdf('mm', true, false, true, 'pdfx4');
            $pdf->setTitle('No profile');
            $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
            $pdf->addPage();
            $pdf->page->addContent($font['out']);
            $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);
            $pdf->getOutPDFString();
        } finally {
            \restore_error_handler();
        }

        $this->assertTrue($warned);
    }

    /**
     * Explicit document dates make the emitted metadata reproducible.
     *
     * @throws \Throwable
     */
    public function testExplicitDocumentDatesDriveInfoDictionaryAndXmp(): void
    {
        $created = new \DateTimeImmutable('2020-03-04T05:06:07+00:00');
        $modified = new \DateTimeImmutable('2021-07-08T09:10:11+00:00');

        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle('Dated document');
        $pdf->setDocCreationDate($created);
        $pdf->setDocModificationDate($modified);
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('D:' . \date('YmdHis', $created->getTimestamp()), $out);
        $this->assertStringContainsString('D:' . \date('YmdHis', $modified->getTimestamp()), $out);
        $this->assertStringContainsString(
            '<xmp:CreateDate>' . \date('Y-m-d\TH:i:sp', $created->getTimestamp()) . '</xmp:CreateDate>',
            $out,
        );
        // The metadata date tracks the modification, never the creation.
        $this->assertStringContainsString(
            '<xmp:MetadataDate>' . \date('Y-m-d\TH:i:sp', $modified->getTimestamp()) . '</xmp:MetadataDate>',
            $out,
        );
    }

    /**
     * dc:subject is a set of individual keywords, not the raw Info string.
     *
     * @throws \Throwable
     */
    public function testKeywordsAreSplitIntoIndividualSubjectItems(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle('Keyword document');
        $pdf->setKeywords('alpha beta  gamma');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('<rdf:li>alpha</rdf:li>', $out);
        $this->assertStringContainsString('<rdf:li>beta</rdf:li>', $out);
        $this->assertStringContainsString('<rdf:li>gamma</rdf:li>', $out);
        // pdf:Keywords keeps mirroring the Info dictionary string verbatim.
        $this->assertStringContainsString('<pdf:Keywords>alpha beta  gamma</pdf:Keywords>', $out);
    }

    /**
     * The XMP packet must stay well formed whatever the caller puts in the metadata.
     *
     * @throws \Throwable
     */
    #[DataProvider('hostileMetadataProvider')]
    public function testXmpStaysWellFormedWithHostileMetadata(string $title): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle($title);
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);

        $xmp = $this->extractXmp($pdf->getOutPDFString());
        $this->assertNotSame('', $xmp);

        $body = \preg_replace(['#^<\?xpacket[^>]*\?>\s*#s', '#\s*<\?xpacket end="w"\?>$#s'], '', $xmp);

        $previous = \libxml_use_internal_errors(true);

        try {
            $parsed = \simplexml_load_string((string) $body);
        } finally {
            \libxml_clear_errors();
            \libxml_use_internal_errors($previous);
        }

        $this->assertNotFalse($parsed);
    }

    /** @return array<string, array{0: string}> */
    public static function hostileMetadataProvider(): array
    {
        return [
            'markup' => ['Title & <tag> "quoted"'],
            'nul_byte' => ["Title\x00truncated"],
            'form_feed' => ["Title\x0Cbroken"],
            'control_range' => ["Title\x01\x02\x08\x0B\x1F"],
            'invalid_utf8' => ["Caf\xE9 Latin1"],
            'lone_high_byte' => ["Title\xFF\xFEmixed"],
            'delete_char' => ["Title\x7Fdelete"],
        ];
    }

    /**
     * ISO 19005 and ISO 15930 both forbid /Alternates in image XObjects.
     *
     * @throws \Throwable
     */
    #[DataProvider('imageAlternatesModeProvider')]
    public function testModesForbiddingImageAlternatesOmitThem(string $mode, bool $forbidden): void
    {
        $image = (string) \realpath(__DIR__ . '/../examples/images/tcpdf_logo.jpg');
        $alternate = (string) \realpath(__DIR__ . '/../examples/images/tcpdf_cell.png');

        $pdf = new Tcpdf('mm', true, false, true, $mode);
        $pdf->setTitle('Alternates document');
        $pdf->setLanguage('en-US');

        if ($mode === 'pdfx4') {
            $pdf->setSRGB(true);
        }

        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        $altid = $pdf->image->add($alternate);
        $imgid = $pdf->image->add($image, null, null, false, 100, false, [$altid]);
        $pdf->page->addContent($pdf->image->getSetImage($imgid, 20, 20, 40, 20, 297));

        $out = $pdf->getOutPDFString();

        if ($forbidden) {
            $this->assertStringNotContainsString('/Alternates', $out);
            return;
        }

        $this->assertStringContainsString('/Alternates', $out);
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function imageAlternatesModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfua1' => ['pdfua1', false],
            'pdfa2b' => ['pdfa2b', true],
            'pdfx' => ['pdfx', true],
            'pdfx4' => ['pdfx4', true],
        ];
    }

    /**
     * The producer suffix must reach both emitters, keeping the Info dictionary and
     * the XMP packet equivalent, and must never displace the library attribution.
     *
     * @throws \Throwable
     */
    public function testProducerSuffixDrivesBothInfoDictionaryAndXmp(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle('Producer document');
        $pdf->setProducerSuffix("My\x00 Application 1.2");
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Body.</p>', posx: 15, posy: 20, width: 180);

        $out = $pdf->getOutPDFString();
        $xmp = $this->extractXmp($out);

        $matches = [];
        $this->assertSame(1, \preg_match('#<pdf:Producer>(.*?)</pdf:Producer>#s', $xmp, $matches));
        $this->assertSame(
            'TCPDF ' . $pdf->getVersion() . ' (https://tcpdf.org) - My Application 1.2',
            $matches[1] ?? '',
        );
    }

    /**
     * ISO 15930 requires a page to carry a trim box or an art box, not both.
     *
     * @throws \Throwable
     */
    #[DataProvider('artBoxModeProvider')]
    public function testPdfxPagesCarryATrimBoxWithoutAnArtBox(string $mode, bool $omitted): void
    {
        $out = $this->buildDocument($mode);

        $this->assertStringContainsString('/MediaBox', $out);
        $this->assertStringContainsString('/TrimBox', $out);

        if ($omitted) {
            $this->assertStringNotContainsString('/ArtBox', $out);
            return;
        }

        $this->assertStringContainsString('/ArtBox', $out);
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function artBoxModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa2b' => ['pdfa2b', false],
            'pdfua1' => ['pdfua1', false],
            'pdfx' => ['pdfx', true],
            'pdfx1a' => ['pdfx1a', true],
            'pdfx4' => ['pdfx4', true],
        ];
    }

    /**
     * Shadings are legal in every conformance mode; only transparency is restricted,
     * and only by ISO 19005-1, ISO 15930-4 and ISO 15930-6.
     *
     * @throws \Throwable
     */
    #[DataProvider('shadingModeProvider')]
    public function testShadingsSurviveInEveryModeWhileTransparencyFollowsTheMode(
        string $mode,
        bool $transparency,
    ): void {
        $pdf = new Tcpdf('mm', true, false, true, $mode);
        $pdf->setTitle('Gradient document');
        $pdf->setLanguage('en-US');

        if (\in_array($mode, ['pdfx4', 'pdfx5'], true)) {
            $pdf->setSRGB(true);
        }

        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->page->addContent($pdf->graph->getLinearGradient(20, 20, 60, 40, '#ff0000', '#0000ff'));
        $pdf->addHTMLCell(
            html: '<p style="background-color:rgba(255,0,0,0.5)">Half transparent.</p>',
            posx: 15,
            posy: 120,
            width: 180,
        );

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/Shading', $out);
        $this->assertStringContainsString('/Pattern', $out);

        if ($transparency) {
            $this->assertStringContainsString('/ExtGState', $out);
            return;
        }

        $this->assertStringNotContainsString('/ExtGState', $out);
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function shadingModeProvider(): array
    {
        return [
            'none' => ['', true],
            'pdfa1b' => ['pdfa1b', false],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3b' => ['pdfa3b', true],
            'pdfx1a' => ['pdfx1a', false],
            'pdfx3' => ['pdfx3', false],
            'pdfx4' => ['pdfx4', true],
            'pdfua1' => ['pdfua1', true],
        ];
    }

    /**
     * Build a one page document carrying one annotation of the given subtype.
     *
     * @throws \Throwable
     */
    private function buildAnnotatedDocument(string $mode, string $subtype): Tcpdf
    {
        $pdf = new Tcpdf('mm', true, false, true, $mode);
        $pdf->setTitle('Annotated document');
        $pdf->setLanguage('en-US');

        if (\in_array($mode, ['pdfx4', 'pdfx5'], true)) {
            // These parts require an embedded destination profile.
            $pdf->setSRGB(true);
        }

        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<h1>Heading</h1><p>Body text.</p>', posx: 15, posy: 20, width: 180);

        /** @var TAnnotOpts $opt */
        $opt = [
            'subtype' => $subtype,
        ];
        $aoid = $pdf->setAnnotation(150.0, 20.0, 20.0, 10.0, 'Annotation body', $opt);
        if ($aoid > 0) {
            $pdf->page->addAnnotRef($aoid);
        }

        return $pdf;
    }

    /**
     * The annotation subtype is written as the ISO 32000-1 name whatever case the
     * caller uses.
     *
     * @throws \Throwable
     */
    #[DataProvider('annotationSubtypeProvider')]
    public function testAnnotationSubtypeIsNormalizedToTheIsoName(string $given, string $expected): void
    {
        $out = $this->buildAnnotatedDocument('', $given)->getOutPDFString();

        $this->assertStringContainsString('/Subtype /' . $expected . ' ', $out);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function annotationSubtypeProvider(): array
    {
        return [
            'text' => ['text', 'Text'],
            'Text' => ['Text', 'Text'],
            'freetext' => ['freetext', 'FreeText'],
            'strikeout' => ['strikeout', 'StrikeOut'],
            'polyline' => ['polyline', 'PolyLine'],
            'fileattachment' => ['fileattachment', 'FileAttachment'],
        ];
    }

    /**
     * An options array that omits the subtype falls back to the documented default
     * instead of raising a TypeError.
     *
     * @throws \Throwable
     */
    public function testAnnotationWithoutSubtypeOptionUsesTheDefault(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, '');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        // The subtype key is required by the option type but may be missing at runtime.
        /** @var TAnnotOpts $opt */
        $opt = [
            't' => 'title only',
        ];
        $aoid = $pdf->setAnnotation(20.0, 20.0, 30.0, 10.0, 'No subtype given', $opt);
        $pdf->page->addAnnotRef($aoid);

        $this->assertStringContainsString('/Subtype /Text ', $pdf->getOutPDFString());
    }

    /**
     * ISO 19005 and ISO 15930 require an appearance stream on every annotation but
     * links and popups; the other modes leave the annotation as the caller built it.
     *
     * @throws \Throwable
     */
    #[DataProvider('appearanceModeProvider')]
    public function testAnnotationsCarryAnAppearanceStreamWhenTheModeRequiresIt(string $mode, bool $required): void
    {
        $out = $this->buildAnnotatedDocument($mode, 'text')->getOutPDFString();

        $this->assertStringContainsString('/Subtype /Text ', $out);
        $this->assertSame($required, \str_contains($out, '/AP <<'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function appearanceModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfua1' => ['pdfua1', false],
            'pdfa1b' => ['pdfa1b', true],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3b' => ['pdfa3b', true],
            'pdfx1a' => ['pdfx1a', true],
            'pdfx4' => ['pdfx4', true],
        ];
    }

    /**
     * PDF/A and PDF/X force the Print flag and clear the Hidden and NoView flags.
     *
     * @throws \Throwable
     */
    public function testConformingModesClearTheHiddenAnnotationFlag(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa2b');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        // Hidden and NoView.
        $aoid = $pdf->setAnnotation(20.0, 20.0, 30.0, 10.0, 'Hidden note', [
            'subtype' => 'text',
            'f' => 2 | 32,
        ]);
        $pdf->page->addAnnotRef($aoid);

        $this->assertStringContainsString('/F 4 ', $pdf->getOutPDFString());
    }

    /**
     * ISO 14289-1 requires every annotation but Link, Widget, PrinterMark and Popup
     * to be nested in an Annot structure element.
     *
     * @throws \Throwable
     */
    public function testTaggedModeNestsAnnotationsInAnAnnotStructureElement(): void
    {
        $out = $this->buildAnnotatedDocument('pdfua1', 'text')->getOutPDFString();

        $this->assertStringContainsString('/S /Annot', $out);
        $this->assertStringContainsString('/Type /OBJR', $out);
        $this->assertStringContainsString('/StructParent ', $out);
    }

    /**
     * ISO 14289-1 requires a Widget annotation to be nested in a Form structure
     * element and the field to carry a description.
     *
     * @throws \Throwable
     */
    public function testTaggedModeNestsFormFieldsInAFormStructureElementWithADescription(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfua1');
        $pdf->setTitle('Form document');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<h1>Form</h1><p>One text field.</p>', posx: 15, posy: 20, width: 180);

        $aoid = $pdf->addFFText('field1', 20.0, 60.0, 60.0, 8.0);
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/S /Form', $out);
        $this->assertStringContainsString('/TU ', $out);
    }

    /**
     * ISO 19005 and ISO 15930 each permit their own set of annotation subtypes; the
     * ones outside it are dropped rather than written.
     *
     * @throws \Throwable
     */
    #[DataProvider('blockedAnnotationSubtypeProvider')]
    public function testForbiddenAnnotationSubtypesAreDropped(string $mode, string $subtype, bool $blocked): void
    {
        $out = $this->buildAnnotatedDocument($mode, $subtype)->getOutPDFString();

        $this->assertSame(!$blocked, \str_contains($out, '/Subtype /' . $subtype . ' '));
    }

    /** @return array<string, array{0: string, 1: string, 2: bool}> */
    public static function blockedAnnotationSubtypeProvider(): array
    {
        return [
            // ISO 19005-1 clause 6.5.2 lists a shorter set than the later parts.
            'pdfa1b Caret' => ['pdfa1b', 'Caret', true],
            'pdfa1b Polygon' => ['pdfa1b', 'Polygon', true],
            'pdfa1b Watermark' => ['pdfa1b', 'Watermark', true],
            'pdfa1b Square' => ['pdfa1b', 'Square', false],
            'pdfa2b Caret' => ['pdfa2b', 'Caret', false],
            'pdfa2b Polygon' => ['pdfa2b', 'Polygon', false],
            'pdfa2b Movie' => ['pdfa2b', 'Movie', true],
            'pdfa2b Screen' => ['pdfa2b', 'Screen', true],
            'pdfa3b Sound' => ['pdfa3b', 'Sound', true],
            'pdfx1a Screen' => ['pdfx1a', 'Screen', true],
            'pdfx1a Square' => ['pdfx1a', 'Square', false],
            'none Movie' => ['', 'Movie', false],
        ];
    }

    /**
     * A Popup annotation needs no appearance stream but still needs an Annot
     * structure element; a PrinterMark needs neither.
     *
     * @throws \Throwable
     */
    public function testExemptAnnotationSubtypesAreHandledSeparately(): void
    {
        $popup = $this->buildAnnotatedDocument('pdfua1', 'popup')->getOutPDFString();
        $printermark = $this->buildAnnotatedDocument('pdfua1', 'printermark')->getOutPDFString();

        $this->assertStringContainsString('/S /Annot', $popup);
        $this->assertStringNotContainsString('/S /Annot', $printermark);
        $this->assertStringNotContainsString(
            '/AP <<',
            $this->buildAnnotatedDocument('pdfa2b', 'popup')->getOutPDFString(),
        );
    }

    /**
     * An empty appearance option is not an appearance: it would emit an /AP dictionary
     * with no N entry, which ISO 19005 forbids.
     *
     * @throws \Throwable
     */
    public function testEmptyAppearanceOptionDoesNotEmitAnEmptyAppearanceDictionary(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa2b');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        /** @var TAnnotOpts $opt */
        $opt = [
            'subtype' => 'text',
            'ap' => [],
        ];
        $aoid = $pdf->setAnnotation(20.0, 20.0, 30.0, 10.0, 'Empty appearance', $opt);
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertStringNotContainsString('/AP << >>', $out);
        $this->assertStringContainsString('/AP << /N ', $out);
    }

    /**
     * ISO 19005 requires the normal appearance of a button field to be a subdictionary
     * of states, which a push button built by addFFButton() does not use on its own.
     *
     * @throws \Throwable
     */
    public function testButtonFieldAppearanceIsAStateSubdictionaryInConformingModes(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa2b');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        $aoid = $pdf->addFFButton('b1', 20.0, 40.0, 30.0, 10.0, 'Push', '');
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/AS /Off', $out);
        $this->assertStringContainsString('/AP << /N << /Off ', $out);
    }

    /**
     * ISO 15930 requires annotations to sit outside the bleed box, which no validator
     * available here can check, so the library reports the overlap as a warning.
     *
     * @throws \Throwable
     */
    public function testPdfxReportsAnnotationsOverlappingTheBleedBox(): void
    {
        $pdf = $this->buildAnnotatedDocument('pdfx1a', 'text');
        $pdf->getOutPDFString();

        $this->assertCount(1, $pdf->getWarnings());
        $this->assertStringContainsString('overlaps the BleedBox', $pdf->getWarnings()[0] ?? '');
    }

    /**
     * Build a one page document in the given conformance mode, ready for content.
     *
     * @param string $mode     Conformance mode.
     * @param bool   $compress Compress the page content streams.
     *
     * @throws \Throwable
     */
    private function buildBareDocument(string $mode, bool $compress = true): Tcpdf
    {
        $pdf = new Tcpdf('mm', true, false, $compress, $mode);
        $pdf->setTitle('Conformance document');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        return $pdf;
    }

    /**
     * ISO 19005-1 clause 6.6.1 forbids a form field from performing an action of any
     * type, and ISO 19005-2 and ISO 19005-3 clause 6.4.1 forbid the /A and /AA keys of
     * a widget annotation, whatever the action type.
     *
     * @throws \Throwable
     */
    #[DataProvider('widgetActionModeProvider')]
    public function testPdfaDropsEveryActionOfAWidgetAnnotation(string $mode, bool $forbidden): void
    {
        $pdf = $this->buildBareDocument($mode);

        /** @var TAnnotOpts $opt */
        $opt = [
            'subtype' => 'Widget',
            'ft' => 'Tx',
            't' => 'field1',
            'a' => '/S /URI /URI (https://example.test)',
            'aa' => '/E << /S /GoTo /D [0 /Fit] >>',
        ];
        $aoid = $pdf->setAnnotation(20.0, 40.0, 30.0, 10.0, 'field1', $opt);
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertSame(!$forbidden, \str_contains($out, '/A << /S /URI'));
        $this->assertSame(!$forbidden, \str_contains($out, '/AA << /E <<'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function widgetActionModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfua1' => ['pdfua1', false],
            'pdfa1b' => ['pdfa1b', true],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3b' => ['pdfa3b', true],
        ];
    }

    /**
     * The same rule covers the form action a button field sets on its own: SubmitForm
     * is a permitted action type but not on a widget, while ResetForm and ImportData
     * are forbidden types on top of that.
     *
     * @throws \Throwable
     */
    #[DataProvider('buttonFormActionModeProvider')]
    public function testPdfaDropsTheFormActionOfAButtonField(string $mode, bool $forbidden): void
    {
        $pdf = $this->buildBareDocument($mode);
        $aoid = $pdf->addFFButton('b1', 20.0, 40.0, 30.0, 10.0, 'Reset', ['S' => 'ResetForm']);
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertSame(!$forbidden, \str_contains($out, '/S /ResetForm'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function buttonFormActionModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa1b' => ['pdfa1b', true],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3b' => ['pdfa3b', true],
        ];
    }

    /**
     * A link to an embedded generic file is served by a JavaScript action, which
     * ISO 19005-3 clause 6.5.1 forbids in the one PDF/A part that allows the
     * embedded file itself.
     *
     * @throws \Throwable
     */
    #[DataProvider('embeddedFileLinkModeProvider')]
    public function testPdfaDropsTheJavaScriptActionOfAnEmbeddedFileLink(string $mode, bool $forbidden): void
    {
        $pdf = $this->buildBareDocument($mode);
        $pdf->addContentAsEmbeddedFile('probe.txt', 'probe content', 'text/plain');
        $aoid = $pdf->setLink(20.0, 40.0, 30.0, 10.0, '*probe.txt');
        $pdf->page->addAnnotRef($aoid);

        $out = $pdf->getOutPDFString();

        $this->assertSame(!$forbidden, \str_contains($out, '/S /JavaScript'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function embeddedFileLinkModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa3b' => ['pdfa3b', true],
            'pdfa3a' => ['pdfa3a', true],
        ];
    }

    /**
     * ISO 19005-1 clause 6.4 forbids the /SMask key of an image XObject, as do
     * ISO 15930-4 and ISO 15930-6: the flattened image is emitted alone and the
     * loss of the alpha channel is reported.
     *
     * @throws \Throwable
     */
    #[DataProvider('imageSoftMaskModeProvider')]
    public function testModesForbiddingTransparencyDropTheImageSoftMask(string $mode, bool $forbidden): void
    {
        $pdf = $this->buildBareDocument($mode);
        $imgid = $pdf->image->add((string) \realpath(__DIR__ . '/../examples/images/200x100_RGBALPHA.png'));
        $pdf->page->addContent($pdf->image->getSetImage($imgid, 20, 20, 40, 20, 297));

        $out = $pdf->getOutPDFString();
        $warnings = \implode("\n", $pdf->getWarnings());

        $this->assertSame(!$forbidden, \str_contains($out, '/SMask'));
        $this->assertSame($forbidden, \str_contains($warnings, 'the soft mask of an image was dropped'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function imageSoftMaskModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa2b' => ['pdfa2b', false],
            'pdfa1b' => ['pdfa1b', true],
            'pdfx1a' => ['pdfx1a', true],
            'pdfx3' => ['pdfx3', true],
        ];
    }

    /**
     * ISO 19005 allows a device colour space only when the output intent defines the
     * same space; the default intent is sRGB, so a DeviceCMYK image or colour is
     * reported rather than converted.
     *
     * @throws \Throwable
     */
    #[DataProvider('deviceCmykModeProvider')]
    public function testPdfaReportsDeviceCmykAgainstAnRgbOutputIntent(string $mode, bool $reported): void
    {
        $pdf = $this->buildBareDocument($mode);
        $imgid = $pdf->image->add((string) \realpath(__DIR__ . '/../examples/images/200x100_CMYK.jpg'));
        $pdf->page->addContent($pdf->image->getSetImage($imgid, 20, 20, 40, 20, 297));
        $pdf->page->addContent($pdf->color->getPdfColor('cmyk(0,100,100,0)'));

        $pdf->getOutPDFString();
        $warnings = \implode("\n", $pdf->getWarnings());

        $this->assertSame($reported, \str_contains($warnings, 'a DeviceCMYK image is emitted'));
        $this->assertSame($reported, \str_contains($warnings, 'a DeviceCMYK colour is emitted'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function deviceCmykModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfua1' => ['pdfua1', false],
            'pdfa1b' => ['pdfa1b', true],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3a' => ['pdfa3a', true],
        ];
    }

    /**
     * The same content with the same pinned dates and file ID renders the same bytes.
     *
     * @throws \Throwable
     */
    public function testPinnedFileIdMakesTheOutputReproducible(): void
    {
        $this->assertSame($this->buildPinnedDocument(), $this->buildPinnedDocument());
    }

    /**
     * Build a document whose dates and file ID are pinned.
     *
     * @throws \Throwable
     */
    private function buildPinnedDocument(): string
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa3b');
        $pdf->setTitle('Reproducible document');
        $pdf->setLanguage('en-US');
        $pdf->setDocCreationDate(1600000000);
        $pdf->setDocModificationDate(1600000000);
        $pdf->setFileId('reproducible-probe');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        $pdf->addHTMLCell(html: '<p>Same bytes every run.</p>', posx: 15, posy: 20, width: 180);

        return $pdf->getOutPDFString();
    }

    /**
     * The encryption key is derived from the file identifier chosen at construction
     * time, so pinning it afterwards would invalidate the document.
     *
     * @throws \Throwable
     */
    public function testFileIdCannotBeChangedOnAnEncryptedDocument(): void
    {
        $encrypt = new ObjEncrypt(true, '', 2, ['print'], 'user-pass', 'owner-pass');
        $pdf = new Tcpdf('mm', true, false, true, '', $encrypt);

        $this->expectException(\Com\Tecnick\Pdf\Exception::class);
        $pdf->setFileId('deadbeef');
    }

    /**
     * XMP distinguishes the identifier of the document from the identifier of the
     * saved instance, so the two are never equal.
     *
     * @throws \Throwable
     */
    public function testDocumentIdAndInstanceIdDiffer(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa2b');
        $pdf->setTitle('Identifier document');
        $pdf->setLanguage('en-US');
        $pdf->setFileId('11111111111111111111111111111111');
        $pdf->setDocumentId('22222222222222222222222222222222');
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        $xmp = $this->extractXmp($pdf->getOutPDFString());

        $this->assertStringContainsString('<xmpMM:DocumentID>uuid:22222222-2222-2222-2222-222222222222<', $xmp);
        $this->assertStringContainsString('<xmpMM:InstanceID>uuid:11111111-1111-1111-1111-111111111111<', $xmp);
    }

    /**
     * The XMP packet padding is what makes the packet writable, so dropping it also
     * declares the packet read-only.
     *
     * @throws \Throwable
     */
    public function testXmpPaddingCanBeDisabled(): void
    {
        $pdf = new Tcpdf('mm', true, false, true, 'pdfa2b');
        $pdf->setTitle('Unpadded document');
        $pdf->setLanguage('en-US');
        $pdf->setXMPPaddingLines(0);
        $font = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('<?xpacket end="r"?>', $out);
        $this->assertStringNotContainsString('<?xpacket end="w"?>', $out);
        $this->assertStringNotContainsString(\str_repeat(' ', 99), $out);
    }

    /**
     * ISO 19005-2 and ISO 19005-3 clause 6.9, ISO 14289-1 clause 7.10 and ISO 14289-2
     * clause 8.7 forbid the /AS key of an optional content configuration dictionary.
     *
     * @throws \Throwable
     */
    #[DataProvider('optionalContentUsageModeProvider')]
    public function testConformingModesOmitTheOptionalContentUsageApplication(string $mode, bool $omitted): void
    {
        $pdf = $this->buildBareDocument($mode);
        if ($mode === 'pdfx4') {
            // ISO 15930-7 requires an embedded destination profile.
            $pdf->setSRGB(true);
            $pdf->setOutputIntent(
                identifier: 'sRGB IEC61966-2.1',
                info: 'sRGB IEC61966-2.1',
                condition: 'sRGB display condition',
            );
        }

        $pdf->page->addContent($pdf->newLayer(name: 'probe'));
        $pdf->page->addContent($pdf->getTextCell('Layer content', 20.0, 20.0, 60.0, 8.0));
        $pdf->page->addContent($pdf->closeLayer());

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString('/OCProperties << /OCGs [', $out);
        $this->assertSame(!$omitted, \str_contains($out, '/AS [ << /Event /Print'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function optionalContentUsageModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa2b' => ['pdfa2b', true],
            'pdfa3b' => ['pdfa3b', true],
            'pdfua1' => ['pdfua1', true],
            'pdfx4' => ['pdfx4', true],
        ];
    }

    /**
     * ISO 19005-1 is based on PDF 1.4, which has no optional content: a layer cannot
     * be emitted, and the marked content operators newLayer() returns would reference
     * a missing property, so the call is refused.
     *
     * @throws \Throwable
     */
    public function testPdfa1RefusesOptionalContent(): void
    {
        $pdf = $this->buildBareDocument('pdfa1b');

        $this->expectException(\Com\Tecnick\Pdf\Exception::class);
        $this->expectExceptionMessageMatches('/' . \preg_quote('Optional content (layers) is not allowed', '/') . '/');
        $pdf->newLayer(name: 'probe');
    }

    /**
     * A Separation with a DeviceCMYK alternate space hits the same output intent rule
     * as a device colour, while a Lab alternate does not.
     *
     * @throws \Throwable
     */
    public function testPdfaReportsACmykSpotColourAgainstAnRgbOutputIntent(): void
    {
        $pdf = $this->buildBareDocument('pdfa2b');
        $pdf->color->addSpotColor('ProbeSpot', new \Com\Tecnick\Color\Model\Cmyk([
            'cyan' => 0.9,
            'magenta' => 0.2,
            'yellow' => 0.0,
            'key' => 0.1,
        ]));
        $pdf->color->addSpotLabColor('ProbeLab', 55.0, 20.0, -30.0);
        $pdf->page->addContent($pdf->color->getPdfColor('ProbeSpot'));
        $pdf->page->addContent($pdf->color->getPdfColor('ProbeLab'));

        $pdf->getOutPDFString();
        $warnings = \implode("\n", $pdf->getWarnings());

        $this->assertStringContainsString('the spot colour "ProbeSpot" is emitted as a Separation', $warnings);
        $this->assertStringNotContainsString('ProbeLab', $warnings);
    }

    /**
     * An imported page carries the fonts of its source document, so a font the source
     * does not embed cannot be embedded by the destination document either.
     *
     * @throws \Throwable
     */
    public function testImportedPageReportsANonEmbeddedFont(): void
    {
        $source = new Tcpdf('mm', true, false, true, '');
        $sourceFont = $source->font->insert($source->pon, 'helvetica', 'B', 14);
        $source->addPage();
        $source->page->addContent($sourceFont['out']);
        $source->page->addContent($source->getTextCell('Source text', 15.0, 15.0, 100.0, 8.0));
        $sourceRaw = $source->getOutPDFString();

        $pdf = $this->buildBareDocument('pdfa2b');
        $tpl = $pdf->importPage($pdf->setImportSourceData($sourceRaw), 1);
        $pdf->useImportedPage($tpl, 20.0, 20.0, 100.0);

        $pdf->getOutPDFString();
        $warnings = \implode("\n", $pdf->getWarnings());

        $this->assertStringContainsString('the imported page 1 uses the font Helvetica-Bold', $warnings);
    }

    /**
     * The same import outside a conforming mode raises nothing.
     *
     * @throws \Throwable
     */
    public function testImportedPageFontsAreNotReportedOutsideAConformingMode(): void
    {
        $source = new Tcpdf('mm', true, false, true, '');
        $sourceFont = $source->font->insert($source->pon, 'helvetica', 'B', 14);
        $source->addPage();
        $source->page->addContent($sourceFont['out']);
        $sourceRaw = $source->getOutPDFString();

        $pdf = $this->buildBareDocument('');
        $tpl = $pdf->importPage($pdf->setImportSourceData($sourceRaw), 1);
        $pdf->useImportedPage($tpl, 20.0, 20.0, 100.0);

        $pdf->getOutPDFString();

        $this->assertSame([], $pdf->getWarnings());
    }

    /**
     * A signature field placed with no appearance stream leaves an empty appearance
     * array, which must not become an empty /AP dictionary: ISO 19005-1 clause 6.5.3
     * and ISO 19005-2 and ISO 19005-3 clause 6.3.3 require a widget with a visible
     * rectangle to carry an appearance dictionary holding only /N.
     *
     * @throws \Throwable
     */
    public function testSignatureFieldWithoutAnAppearanceGetsAConformingOne(): void
    {
        $pdf = $this->buildSignedDocument('pdfa2b');

        $out = $pdf->getOutPDFString();

        $this->assertStringNotContainsString('/AP << >>', $out);
        $this->assertSame(2, \substr_count($out, '/AP << /N '));
    }

    /**
     * Outside a mode requiring an appearance, an unset one emits no /AP at all.
     *
     * @throws \Throwable
     */
    public function testSignatureFieldWithoutAnAppearanceEmitsNoAppearanceDictionary(): void
    {
        $pdf = $this->buildSignedDocument('');

        $out = $pdf->getOutPDFString();

        $this->assertStringNotContainsString('/AP <<', $out);
    }

    /**
     * ISO 14289-1 clause 7.18.4 requires a widget annotation to be nested in a Form
     * structure element and clause 7.18.1 requires the field to carry a /TU, which
     * ISO 14289-2 clause 8.10.2.3 completes with a /Contents description.
     *
     * @throws \Throwable
     */
    public function testTaggedModeNestsSignatureWidgetsInAFormStructureElement(): void
    {
        $pdf = $this->buildSignedDocument('pdfua1');

        $out = $pdf->getOutPDFString();

        $this->assertSame(2, \substr_count($out, '/StructElem /S /Form'));
        $this->assertStringContainsString('/StructParent ', $out);
        $this->assertStringContainsString('/TU ', $out);
        $this->assertStringContainsString('/Contents ', $out);
    }

    /**
     * ISO 14289-1 clause 7.1 requires every piece of content to be tagged or marked as
     * an artifact: the table of contents entries are a TOC of TOCI elements and its
     * decorations are artifacts.
     *
     * @throws \Throwable
     */
    public function testTaggedModeTagsTheTableOfContents(): void
    {
        $pdf = $this->buildTocDocument('pdfua1', false);

        $out = $pdf->getOutPDFString();

        $this->assertSame(1, \substr_count($out, '/StructElem /S /TOC '));
        $this->assertSame(2, \substr_count($out, '/StructElem /S /TOCI '));
        $this->assertSame(2, \substr_count($out, '/StructElem /S /Reference '));
        $this->assertStringContainsString('/Artifact BMC', $out);
    }

    /**
     * Outside a tagged mode the table of contents keeps its plain content stream.
     *
     * @throws \Throwable
     */
    public function testTableOfContentsIsNotTaggedOutsideATaggedMode(): void
    {
        $pdf = $this->buildTocDocument('', false);

        $out = $pdf->getOutPDFString();

        $this->assertStringNotContainsString('/StructElem /S /TOC ', $out);
        $this->assertStringNotContainsString('/Artifact BMC', $out);
    }

    /**
     * ISO 14289-2 clause 8.2.5.8 requires a table of contents item to name the target
     * of its reference through the /Ref entry.
     *
     * @throws \Throwable
     */
    public function testTableOfContentsItemsReferenceTheirTarget(): void
    {
        $pdf = $this->buildTocDocument('pdfua2');

        $out = $pdf->getOutPDFString();

        $this->assertSame(2, \substr_count($out, '/Ref ['));
    }

    /**
     * ISO 14289-2 clause 8.8 requires an in-document destination to point at a
     * structure element; the other modes keep the page destination.
     *
     * @throws \Throwable
     */
    #[DataProvider('structureDestinationModeProvider')]
    public function testInDocumentDestinationsFollowTheMode(string $mode, string $expected): void
    {
        $pdf = $this->buildTocDocument($mode);

        $out = $pdf->getOutPDFString();

        $this->assertSame($expected, $this->getDestinationTargetType($out));
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function structureDestinationModeProvider(): array
    {
        return [
            'none' => ['', '/Type /Page'],
            'pdfa2a' => ['pdfa2a', '/Type /Page'],
            'pdfua1' => ['pdfua1', '/Type /Page'],
            'pdfua2' => ['pdfua2', '/Type /StructElem'],
        ];
    }

    /**
     * Return the object type the first explicit destination of the document targets.
     */
    private function getDestinationTargetType(string $pdf): string
    {
        $matches = [];
        if (\preg_match('#/Dest \[(\d+) 0 R#', $pdf, $matches) !== 1) {
            return '';
        }

        $target = [];
        if (\preg_match('#(?:^|\n)' . ($matches[1] ?? '') . ' 0 obj\n<<\s*(/Type /\w+)#', $pdf, $target) !== 1) {
            return '';
        }

        return $target[1] ?? '';
    }

    /**
     * Build a two page document with a bookmark per page and a table of contents.
     *
     * @param string $mode     Conformance mode.
     * @param bool   $compress Compress the page content streams.
     *
     * @throws \Throwable
     */
    private function buildTocDocument(string $mode, bool $compress = true): Tcpdf
    {
        $pdf = $this->buildBareDocument($mode, $compress);
        $pdf->addHTMLCell(html: '<h1>First chapter</h1><p>Body text.</p>', posx: 15, posy: 20, width: 180);
        $pdf->setBookmark('First chapter', '', 0, -1, 0.0, 20.0);
        $pdf->addPage();
        $pdf->addHTMLCell(html: '<h1>Second chapter</h1><p>Body text.</p>', posx: 15, posy: 20, width: 180);
        $pdf->setBookmark('Second chapter', '', 0, -1, 0.0, 20.0);
        $pdf->addPage();
        $pdf->addTOC(page: -1, posx: 15, posy: 20, width: 180);

        return $pdf;
    }

    /**
     * The description given by the caller replaces the field name in /TU and /Contents.
     *
     * @throws \Throwable
     */
    public function testSignatureFieldDescriptionReplacesTheFieldName(): void
    {
        $pdf = $this->buildSignedDocument('pdfua1', 'Signed by the approving officer', 'Reserved for the reviewer');

        $out = $pdf->getOutPDFString();

        $this->assertStringContainsString($this->textString('Signed by the approving officer'), $out);
        $this->assertStringContainsString($this->textString('Reserved for the reviewer'), $out);
        $this->assertStringNotContainsString('/TU ' . $this->textString('PrimarySignature'), $out);
    }

    /**
     * Encode an ASCII string the way getOutTextString() writes it in an unencrypted
     * document: a literal UTF-16BE string with the byte order mark.
     */
    private function textString(string $txt): string
    {
        $out = '(' . "\xFE\xFF";
        foreach (\str_split($txt) as $chr) {
            $out .= "\x00" . $chr;
        }

        return $out . ')';
    }

    /**
     * Build a one page document carrying a signed field and an empty approval field.
     *
     * @param string $mode             Conformance mode.
     * @param string $description      Description of the signed field.
     * @param string $emptyDescription Description of the empty approval field.
     *
     * @throws \Throwable
     */
    private function buildSignedDocument(string $mode, string $description = '', string $emptyDescription = ''): Tcpdf
    {
        $certPath = (string) \realpath(__DIR__ . '/../examples/data/cert/tcpdf.crt');
        $cert = 'file://' . $certPath;

        $pdf = $this->buildBareDocument($mode);
        $pdf->signature()->configure([
            'appearance' => [
                'empty' => [],
                'name' => '',
                'page' => 0,
                'rect' => '',
            ],
            'approval' => '',
            'cert_type' => 2,
            'extracerts' => '',
            'info' => [
                'ContactInfo' => '',
                'Location' => 'Test',
                'Name' => 'test',
                'Reason' => 'test',
            ],
            'password' => '',
            'privkey' => $cert,
            'signcert' => $cert,
        ]);
        $pdf->signature()->appearance()->place(15.0, 60.0, 75.0, 20.0, -1, 'PrimarySignature');
        if ($description !== '') {
            $pdf->signature()->appearance()->description($description);
        }

        $pdf->signature()->emptyField(15.0, 90.0, 75.0, 20.0, -1, 'ApprovalSignature', $emptyDescription);

        return $pdf;
    }

    /**
     * ISO 19005-1 clause 6.3.8, ISO 19005-2 and ISO 19005-3 clauses 6.2.11.7.2 and
     * 6.2.11.8 and ISO 14289-1 clauses 7.21.7 and 7.21.8 forbid a glyph that no
     * Unicode value maps to, which the .notdef glyph of an uncovered codepoint is.
     * The B levels accept it, so the text is left as it stands there.
     *
     * @throws \Throwable
     */
    #[DataProvider('unmappedGlyphModeProvider')]
    public function testUncoveredCodepointsAreDroppedWhenTheModeForbidsNotdef(string $mode, bool $dropped): void
    {
        $pdf = new Tcpdf('mm', true, false, true, $mode);
        $pdf->setTitle('Uncovered codepoints');
        $pdf->setLanguage('en-US');
        $font = $pdf->font->insert($pdf->pon, 'freesans', '', 12);
        $pdf->addPage();
        $pdf->page->addContent($font['out']);
        // FreeSans covers neither of these two ideographs.
        $pdf->addHTMLCell(html: '<p>Latin and ' . "\u{6F22}\u{5B57}" . ' kanji.</p>', posx: 15, posy: 20, width: 180);

        $pdf->getOutPDFString();
        $warnings = \implode("\n", $pdf->getWarnings());

        $this->assertSame($dropped, \str_contains($warnings, 'U+6F22, U+5B57'));
    }

    /** @return array<string, array{0: string, 1: bool}> */
    public static function unmappedGlyphModeProvider(): array
    {
        return [
            'none' => ['', false],
            'pdfa1b' => ['pdfa1b', false],
            'pdfa1a' => ['pdfa1a', true],
            'pdfa2u' => ['pdfa2u', true],
            'pdfua1' => ['pdfua1', true],
        ];
    }
}
