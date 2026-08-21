<?php

/**
 * EncryptedImportTest.php
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

namespace Test\Import;

use Com\Tecnick\Pdf\Encrypt\Decrypt;
use Com\Tecnick\Pdf\Encrypt\Encrypt;
use Com\Tecnick\Pdf\Tcpdf;
use PHPUnit\Framework\TestCase;

/**
 * Imported objects must follow the encryption of the destination document.
 */
class EncryptedImportTest extends TestCase
{
    /** Content stream of the source page, stored unfiltered. */
    private const SOURCE_CONTENT = 'BT /F1 12 Tf 20 160 Td (SOURCE_PAGE_TEXT) Tj ET';

    protected function setUp(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            \define(
                'K_PATH_FONTS',
                (string) \realpath(__DIR__ . '/../../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'),
            );
        }
    }

    /**
     * Build a one-page source document with an unfiltered content stream, a font
     * object carrying literal strings and an inline colour space name.
     */
    private function buildSourcePdf(): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 =>
                '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200]'
                    . ' /Resources << /ColorSpace << /CS0 /DeviceRGB >> /Font << /F1 5 0 R >> >>'
                    . ' /Contents 4 0 R >>',
            4 =>
                '<< /Length '
                    . \strlen(self::SOURCE_CONTENT)
                    . ' >>'
                    . "\nstream\n"
                    . self::SOURCE_CONTENT
                    . "\nendstream",
            5 =>
                '<< /Type /Font /Subtype /Type0 /BaseFont /Helvetica'
                    . ' /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >> >>',
        ];

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $num => $body) {
            $offsets[$num] = \strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $body . "\nendobj\n";
        }

        $xref = \strlen($pdf);
        $pdf .= 'xref' . "\n" . '0 ' . (\count($objects) + 1) . "\n" . '0000000000 65535 f ' . "\n";
        foreach (\array_keys($objects) as $num) {
            $pdf .= \sprintf('%010d 00000 n ' . "\n", $offsets[$num]);
        }

        return (
            $pdf
            . 'trailer'
            . "\n"
            . '<< /Size '
            . (\count($objects) + 1)
            . ' /Root 1 0 R >>'
            . "\n"
            . 'startxref'
            . "\n"
            . $xref
            . "\n"
            . '%%EOF'
            . "\n"
        );
    }

    /**
     * Import the source page into a destination document and return its bytes.
     *
     * @throws \Throwable
     */
    private function importInto(?Encrypt $encrypt): string
    {
        $pdf = new Tcpdf('mm', true, false, true, '', $encrypt);
        $pdf->font->insert($pdf->pon, 'helvetica', '', 12);
        $sourceId = $pdf->setImportSourceData($this->buildSourcePdf());
        $template = $pdf->importPage($sourceId, 1);
        $pdf->addPage();
        $pdf->useImportedPage(
            $template,
            0.0,
            0.0,
            $pdf->toUnit($template->getWidth()),
            $pdf->toUnit($template->getHeight()),
        );

        return $pdf->getOutPDFString();
    }

    /**
     * Return the object number and the raw stream bytes of the imported Form XObject.
     *
     * @return array{0: int, 1: string}
     */
    private function findFormXObject(string $pdf): array
    {
        $matches = [];
        $found = \preg_match(
            '/(\d+) 0 obj\n<< \/Type \/XObject \/Subtype \/Form.*?\nstream\n(.*?)\nendstream/s',
            $pdf,
            $matches,
        );
        $this->assertSame(1, $found, 'imported Form XObject not found');

        return [(int) ($matches[1] ?? 0), $matches[2] ?? ''];
    }

    /** @throws \Throwable */
    public function testImportedFormXObjectStreamIsEncrypted(): void
    {
        $encrypt = new Encrypt(enabled: true, mode: 3, permissions: ['print', 'copy']);
        $out = $this->importInto($encrypt);
        [$objNum, $stream] = $this->findFormXObject($out);

        $this->assertStringNotContainsString(self::SOURCE_CONTENT, $out);

        $decrypt = new Decrypt($encrypt->getEncryptionData());
        $this->assertTrue($decrypt->authenticate(''));
        $this->assertSame(self::SOURCE_CONTENT, \rtrim($decrypt->decryptString($stream, $objNum), "\n"));
    }

    /** @throws \Throwable */
    public function testClonedObjectStringsAreEncrypted(): void
    {
        $encrypt = new Encrypt(enabled: true, mode: 3, permissions: ['print', 'copy']);
        $out = $this->importInto($encrypt);

        $this->assertStringNotContainsString('(Adobe)', $out);
        $this->assertStringNotContainsString('(Identity)', $out);

        $matches = [];
        $found = \preg_match('/(\d+) 0 obj\n<<[^>]*\/Registry <([0-9a-f]*)>/', $out, $matches);
        $this->assertSame(1, $found, 'cloned font object not found');

        $decrypt = new Decrypt($encrypt->getEncryptionData());
        $this->assertTrue($decrypt->authenticate(''));
        $this->assertSame('Adobe', $decrypt->decryptString(
            (string) \hex2bin($matches[2] ?? ''),
            (int) ($matches[1] ?? 0),
        ));
    }

    /** @throws \Throwable */
    public function testImportedObjectsAreNotAlteredWithoutEncryption(): void
    {
        $out = $this->importInto(null);
        [, $stream] = $this->findFormXObject($out);

        $this->assertSame(self::SOURCE_CONTENT, \rtrim($stream, "\n"));
        $this->assertStringContainsString('(Adobe)', $out);
    }

    /** @throws \Throwable */
    public function testInlineResourceNamesKeepTheirPrefix(): void
    {
        $out = $this->importInto(null);
        $this->assertStringContainsString('/CS0 /DeviceRGB', $out);
    }

    /** @throws \Throwable */
    public function testMetadataStreamFollowsTheEncryptMetadataFlag(): void
    {
        $encrypted = $this->importInto(new Encrypt(
            enabled: true,
            mode: 3,
            permissions: ['print'],
            encryptMetadata: true,
        ));
        $this->assertStringNotContainsString('<?xpacket', $encrypted);

        $cleartext = $this->importInto(new Encrypt(
            enabled: true,
            mode: 3,
            permissions: ['print'],
            encryptMetadata: false,
        ));
        $this->assertStringContainsString('<?xpacket', $cleartext);
    }
}
