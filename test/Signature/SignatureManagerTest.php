<?php

/**
 * SignatureManagerTest.php
 *
 * @since       2025-01-01
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Test\Signature;

use PHPUnit\Framework\TestCase;
use Com\Tecnick\Pdf\Signature\SignatureManager;
use Com\Tecnick\Pdf\Signature\CmsBuilder;
use Com\Tecnick\Pdf\Signature\PdfParser;
use Com\Tecnick\Pdf\Signature\PhpseclibSigner;

/**
 * SignatureManager Test
 *
 * @since       2025-01-01
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class SignatureManagerTest extends TestCase
{
    /**
     * Test certificate and key directory
     */
    protected string $certDir;

    /**
     * Sample PDF content
     */
    protected string $samplePdf;

    protected function setUp(): void
    {
        $this->certDir = __DIR__ . '/../data/cert/';

        // Create a minimal valid PDF for testing
        $this->samplePdf = $this->createMinimalPdf();
    }

    /**
     * Create a minimal valid PDF for testing
     */
    protected function createMinimalPdf(): string
    {
        $pdf = "%PDF-1.7\n";

        // Object 1: Catalog
        $pdf .= "1 0 obj\n";
        $pdf .= "<< /Type /Catalog /Pages 2 0 R >>\n";
        $pdf .= "endobj\n";

        // Object 2: Pages
        $pdf .= "2 0 obj\n";
        $pdf .= "<< /Type /Pages /Kids [3 0 R] /Count 1 >>\n";
        $pdf .= "endobj\n";

        // Object 3: Page
        $pdf .= "3 0 obj\n";
        $pdf .= "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Resources << >> >>\n";
        $pdf .= "endobj\n";

        // Xref
        $xrefPos = strlen($pdf);
        $pdf .= "xref\n";
        $pdf .= "0 4\n";
        $pdf .= "0000000000 65535 f \n";
        $pdf .= "0000000009 00000 n \n";
        $pdf .= "0000000058 00000 n \n";
        $pdf .= "0000000115 00000 n \n";

        // Trailer
        $pdf .= "trailer\n";
        $pdf .= "<< /Size 4 /Root 1 0 R >>\n";
        $pdf .= "startxref\n";
        $pdf .= $xrefPos . "\n";
        $pdf .= "%%EOF\n";

        return $pdf;
    }

    public function testSignatureManagerConstruction(): void
    {
        $manager = new SignatureManager('mm');
        $this->assertInstanceOf(SignatureManager::class, $manager);
    }

    public function testLoadPdf(): void
    {
        $manager = new SignatureManager('mm');
        $manager->loadPdf($this->samplePdf);

        $this->assertNotEmpty($manager->getPdfContent());
        $this->assertEquals($this->samplePdf, $manager->getPdfContent());
    }

    public function testGetSignatureFieldsEmpty(): void
    {
        $manager = new SignatureManager('mm');
        $manager->loadPdf($this->samplePdf);

        $fields = $manager->getSignatureFields();
        $this->assertIsArray($fields);
        $this->assertEmpty($fields);
    }

    public function testPdfParserConstruction(): void
    {
        $parser = new PdfParser($this->samplePdf);

        $this->assertEquals('1.7', $parser->getVersion());
        $this->assertNotEmpty($parser->getXref());
        $this->assertNotEmpty($parser->getTrailer());
    }

    public function testPdfParserPages(): void
    {
        $parser = new PdfParser($this->samplePdf);
        $pages = $parser->getPages();

        $this->assertIsArray($pages);
        $this->assertCount(1, $pages);
    }

    public function testAddSignatureField(): void
    {
        $manager = new SignatureManager('mm');
        $manager->loadPdf($this->samplePdf);

        $updatedPdf = $manager->addSignatureField(
            'TestSignature',
            1,
            10,
            10,
            50,
            20
        );

        $this->assertNotEmpty($updatedPdf);
        $this->assertStringContainsString('/FT /Sig', $updatedPdf);
        $this->assertStringContainsString('TestSignature', $updatedPdf);
    }
}
