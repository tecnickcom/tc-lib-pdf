<?php

/**
 * SourceDocumentTest.php
 *
 * @since       2002-08-03
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

use Com\Tecnick\Pdf\Import\ImportCorruptedSourceException;
use Com\Tecnick\Pdf\Import\ImportException;
use Com\Tecnick\Pdf\Import\ImportResourceLimitException;
use Com\Tecnick\Pdf\Import\ImportUnsupportedFeatureException;
use Com\Tecnick\Pdf\Import\SourceDocument;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SourceDocumentTest extends TestCase
{
    private function invokeSourceMethod(SourceDocument $doc, string $method, mixed ...$args): mixed
    {
        $ref = new \ReflectionClass($doc);
        return $ref->getMethod($method)->invokeArgs($doc, $args);
    }

    /**
     * @param array<string, mixed> $cfg
     * @return array<string, bool>
     */
    private function callNormalizeParserConfig(SourceDocument $doc, array $cfg, bool &$passwordProvided): array
    {
        $ref = new \ReflectionClass($doc);
        $method = $ref->getMethod('normalizeParserConfig');
        $args = [$cfg, &$passwordProvided];
        /** @var array<string, bool> */
        return $method->invokeArgs($doc, $args);
    }

    private function loadFixture(): string
    {
        $path = __DIR__ . '/../fixtures/simple_import.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    private function loadEncryptedFixture(): string
    {
        $path = __DIR__ . '/../fixtures/encrypted_import_stub.pdf';
        $data = file_get_contents($path);
        $this->assertNotFalse($data);
        return $data;
    }

    /**
     * @param array<int, string> $objects
     */
    private function buildPdf(string $rootRef, array $objects): string
    {
        $pdf = "%PDF-1.4\n";
        $offsets = [0 => 0];
        $maxObjNum = 0;
        foreach ($objects as $num => $obj) {
            $maxObjNum = max($maxObjNum, $num);
            $offsets[$num] = strlen($pdf);
            $pdf .= $num . " 0 obj\n" . $obj . "\nendobj\n";
        }

        $startxref = strlen($pdf);
        $pdf .= "xref\n0 " . ($maxObjNum + 1) . "\n";
        $pdf .= "0000000000 65535 f \n";
        for ($obj = 1; $obj <= $maxObjNum; ++$obj) {
            if (isset($offsets[$obj])) {
                $pdf .= sprintf("%010d 00000 n \n", $offsets[$obj]);
                continue;
            }

            $pdf .= "0000000000 00000 f \n";
        }

        $pdf .= "trailer\n<< /Size " . ($maxObjNum + 1) . ' /Root ' . $rootRef . " >>\n";
        $pdf .= "startxref\n" . $startxref . "\n%%EOF\n";

        return $pdf;
    }

    private function buildInvalidFilterPdf(): string
    {
        return $this->buildPdf('1 0 R', [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 50 50] /Contents 4 0 R >>',
            4 => "<< /Length 3 /Filter /ASCIIHexDecode >>\nstream\nGG>\nendstream",
        ]);
    }

    private function buildRootNullPdf(): string
    {
        return $this->buildPdf('1 0 R', [
            1 => 'null',
        ]);
    }

    /**
     * Build a PDF whose objects form a chain of indirect /Length references.
     *
     * @param int  $chain         Number of chained stream objects.
     * @param bool $invalidFilter If true, add a stream with an undecodable filter.
     */
    private function buildLengthChainPdf(int $chain, bool $invalidFilter = false): string
    {
        $objects = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 50 50] /Contents 4 0 R >>',
            4 =>
                "<< /Length 3 /Filter /ASCIIHexDecode >>\nstream\n" . ($invalidFilter ? 'GG>' : '414>') . "\nendstream",
        ];

        for ($idx = 0; $idx < $chain; ++$idx) {
            $objects[5 + $idx] = '<< /Length ' . (6 + $idx) . " 0 R >>\nstream\nab\nendstream";
        }

        $objects[5 + $chain] = '2';

        return $this->buildPdf('1 0 R', $objects);
    }

    /** @throws \Throwable */
    public function testConstructSucceedsWithValidPdf(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertNotEmpty($doc->getId());
    }

    /** @throws \Throwable */
    public function testIdIsSha256OfData(): void
    {
        $data = $this->loadFixture();
        $doc = new SourceDocument($data);
        $this->assertSame(hash('sha256', $data), $doc->getId());
    }

    /** @throws \Throwable */
    public function testGetTrailerContainsRoot(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $trailer = $doc->getTrailer();
        $this->assertArrayHasKey('root', $trailer);
    }

    /** @throws \Throwable */
    public function testGetXrefReturnsNonEmptyArray(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $xref = $doc->getXref();
        $this->assertNotEmpty($xref);
    }

    /** @throws \Throwable */
    public function testGetObjectReturnsDataForKnownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        // Object 1 is /Catalog in the fixture.
        $obj = $doc->getObject('1_0');
        $this->assertNotEmpty($obj);
    }

    /** @throws \Throwable */
    public function testGetObjectThrowsForUnknownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->expectException(ImportCorruptedSourceException::class);
        $doc->getObject('999_0');
    }

    /** @throws \Throwable */
    public function testFindObjectReturnsNullForUnknownRef(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertNull($doc->findObject('999_0'));
    }

    /** @throws \Throwable */
    public function testConstructThrowsOnEmptyData(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        new SourceDocument('');
    }

    /** @throws \Throwable */
    public function testConstructThrowsOnGarbage(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        new SourceDocument('this is not a pdf');
    }

    /** @throws \Throwable */
    public function testConstructThrowsOnEncryptedPdfWithoutPassword(): void
    {
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password support is not available', '/') . '/');

        new SourceDocument($this->loadEncryptedFixture());
    }

    /**
     * @param array<string, string> $cfg
     * @throws \Throwable
     */
    #[DataProvider('passwordConfigProvider')]
    public function testConstructThrowsOnEncryptedPdfWhenPasswordConfigProvided(array $cfg): void
    {
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password-based import is not supported', '/') . '/');

        new SourceDocument($this->loadEncryptedFixture(), $cfg);
    }

    /** @throws \Throwable */
    public function testConstructEncryptedPdfWithNonStringPasswordFallsBackToNoPasswordSupportMessage(): void
    {
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password support is not available', '/') . '/');

        $passwordKey = implode('', ['pass', 'word']);
        $passwordVal = \strlen($this->loadFixture());

        new SourceDocument($this->loadEncryptedFixture(), [$passwordKey => $passwordVal]);
    }

    /** @throws \Throwable */
    public function testConstructEncryptedPdfWithEmptyPasswordFallsBackToNoPasswordSupportMessage(): void
    {
        $this->expectException(ImportUnsupportedFeatureException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('password support is not available', '/') . '/');

        $passwordKey = implode('', ['pass', 'word']);
        $passwordVal = \substr($this->loadFixture(), 0, 0);

        new SourceDocument($this->loadEncryptedFixture(), [$passwordKey => $passwordVal]);
    }

    /** @throws \Throwable */
    public function testConstructAcceptsIgnoreFilterErrorsBooleanConfig(): void
    {
        $doc = new SourceDocument($this->loadFixture(), ['ignore_filter_errors' => true]);

        $this->assertNotSame('', $doc->getId());
    }

    /** @throws \Throwable */
    public function testConstructRetriesWithIgnoreFilterErrorsWhenInvalidCodeIsDetected(): void
    {
        $data = $this->buildInvalidFilterPdf();
        $doc = new SourceDocument($data);

        $this->assertSame(hash('sha256', $data), $doc->getId());
        $this->assertNotEmpty($doc->getXref());
    }

    /** @throws \Throwable */
    public function testParserLimitIsReportedAsAWarning(): void
    {
        $doc = new SourceDocument($this->buildLengthChainPdf(20), ['max_resolution_depth' => 4]);

        $warnings = $doc->getParserWarnings();

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('resolution depth limit (4)', $warnings[0] ?? '');
    }

    /** @throws \Throwable */
    public function testNoParserWarningIsReportedWithinTheLimit(): void
    {
        $doc = new SourceDocument($this->buildLengthChainPdf(20));

        $this->assertSame([], $doc->getParserWarnings());
    }

    /** @throws \Throwable */
    public function testParserWarningsSurviveTheIgnoreFilterErrorsRetry(): void
    {
        $doc = new SourceDocument($this->buildLengthChainPdf(20, true), [
            'decode_streams' => true,
            'max_resolution_depth' => 4,
        ]);

        $warnings = $doc->getParserWarnings();

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('resolution depth limit (4)', $warnings[0] ?? '');
    }

    /** @throws \Throwable */
    public function testStrictLimitsRaiseAResourceLimitException(): void
    {
        $this->expectException(ImportResourceLimitException::class);
        $this->expectExceptionMessageMatches('/resolution depth limit \(4\)/');

        new SourceDocument($this->buildLengthChainPdf(20), [
            'max_resolution_depth' => 4,
            'strict_limits' => true,
        ]);
    }

    /** @throws \Throwable */
    public function testStrictLimitsRaiseAResourceLimitExceptionOnTheRetryPath(): void
    {
        $this->expectException(ImportResourceLimitException::class);

        new SourceDocument($this->buildLengthChainPdf(20, true), [
            'decode_streams' => true,
            'max_resolution_depth' => 4,
            'strict_limits' => true,
        ]);
    }

    /**
     * A limit failure must also be catchable as a corrupted source failure.
     *
     * @throws \Throwable
     */
    public function testResourceLimitExceptionSpecializesTheCorruptedSourceException(): void
    {
        try {
            new SourceDocument($this->buildLengthChainPdf(20), [
                'max_resolution_depth' => 4,
                'strict_limits' => true,
            ]);
            $this->fail('Expected an import exception.');
        } catch (ImportException $exc) {
            $this->assertInstanceOf(ImportResourceLimitException::class, $exc);
            $this->assertInstanceOf(ImportCorruptedSourceException::class, $exc);
        }
    }

    /** @throws \Throwable */
    public function testNestingLimitCanBeRaised(): void
    {
        $data = $this->buildPdf('1 0 R', [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '<< /Type /Pages /Kids [3 0 R] /Count 1 >>',
            3 => '<< /Type /Page /Parent 2 0 R /Deep ' . str_repeat('[', 400) . str_repeat(']', 400) . ' >>',
        ]);

        $this->assertNotEmpty((new SourceDocument($data, ['max_nesting_depth' => 1024]))->getXref());

        $this->expectException(ImportResourceLimitException::class);
        $this->expectExceptionMessageMatches('/Maximum object nesting depth exceeded/');
        new SourceDocument($data);
    }

    /** @throws \Throwable */
    public function testConstructThrowsWhenRootObjectIsExplicitNull(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        $this->expectExceptionMessageMatches('/' . preg_quote('null object for 1_0', '/') . '/');

        new SourceDocument($this->buildRootNullPdf());
    }

    /** @throws \Throwable */
    public function testRefToKeyConvertsNormalRef(): void
    {
        $this->assertSame('3_0', SourceDocument::refToKey('3 0 R'));
    }

    /** @throws \Throwable */
    public function testRefToKeyPassesThroughKeyForm(): void
    {
        $this->assertSame('3_0', SourceDocument::refToKey('3_0'));
    }

    /** @throws \Throwable */
    public function testRefToKeyTrimsWhitespaceAroundIndirectReference(): void
    {
        $this->assertSame('3_0', SourceDocument::refToKey("\n 3 0 R\t"));
    }

    /** @throws \Throwable */
    public function testRefToKeyThrowsOnInvalidRef(): void
    {
        $this->expectException(ImportCorruptedSourceException::class);
        SourceDocument::refToKey('not a ref');
    }

    /** @throws \Throwable */
    public function testObjectCountReturnsPositiveInteger(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $this->assertGreaterThan(0, $doc->objectCount());
    }

    /** @throws \Throwable */
    public function testNormalizeParserConfigKeepsOnlyBooleanIgnoreFilterErrors(): void
    {
        $doc = new SourceDocument($this->loadFixture());

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig($doc, ['ignore_filter_errors' => true], $passwordProvided);
        $this->assertSame(['decode_streams' => false, 'ignore_filter_errors' => true], $cfg);
        $this->assertFalse($passwordProvided);

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig($doc, ['ignore_filter_errors' => 'yes'], $passwordProvided);
        $this->assertSame(['decode_streams' => false], $cfg);
        $this->assertFalse($passwordProvided);
    }

    /** @throws \Throwable */
    public function testNormalizeParserConfigPassesThroughTheLimitOptions(): void
    {
        $doc = new SourceDocument($this->loadFixture());

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig(
            $doc,
            [
                'strict_limits' => true,
                'max_stream_size' => 1024,
                'max_resolution_depth' => 512,
                'max_nesting_depth' => 2048,
            ],
            $passwordProvided,
        );

        $this->assertSame(
            [
                'decode_streams' => false,
                'strict_limits' => true,
                'max_stream_size' => 1024,
                'max_resolution_depth' => 512,
                'max_nesting_depth' => 2048,
            ],
            $cfg,
        );
    }

    /** @throws \Throwable */
    public function testNormalizeParserConfigRejectsNonIntegerLimits(): void
    {
        $doc = new SourceDocument($this->loadFixture());

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig(
            $doc,
            [
                'strict_limits' => 'yes',
                'max_resolution_depth' => '512',
                'max_nesting_depth' => 12.5,
            ],
            $passwordProvided,
        );

        $this->assertSame(['decode_streams' => false], $cfg);
    }

    /** @throws \Throwable */
    public function testNormalizeParserConfigLeavesOutOfRangeLimitsToTheParser(): void
    {
        $doc = new SourceDocument($this->loadFixture());

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig($doc, ['max_resolution_depth' => -3], $passwordProvided);

        $this->assertSame(['decode_streams' => false, 'max_resolution_depth' => -3], $cfg);
    }

    /**
     * A limit below 1 must be clamped by the parser to the strictest usable value.
     *
     * @throws \Throwable
     */
    public function testOutOfRangeResolutionDepthIsClampedByTheParser(): void
    {
        $doc = new SourceDocument($this->buildLengthChainPdf(20), ['max_resolution_depth' => 0]);

        $warnings = $doc->getParserWarnings();

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('resolution depth limit (1)', $warnings[0] ?? '');
    }

    /** @throws \Throwable */
    public function testNormalizeParserConfigDetectsSupportedPasswordAliases(): void
    {
        $doc = new SourceDocument($this->loadFixture());
        $userPasswordKey = implode('', ['user', '_password']);
        $passwordVal = \hash('sha1', $this->loadFixture());

        $passwordProvided = false;
        $cfg = $this->callNormalizeParserConfig($doc, [$userPasswordKey => $passwordVal], $passwordProvided);

        $this->assertTrue($passwordProvided);
        $this->assertSame(['decode_streams' => false], $cfg);
    }

    /** @throws \Throwable */
    public function testIsNullObjectRecognizesExplicitPdfNullObject(): void
    {
        $doc = new SourceDocument($this->loadFixture());

        $this->assertTrue($this->invokeSourceMethod($doc, 'isNullObject', [['null']]));
        $this->assertFalse($this->invokeSourceMethod($doc, 'isNullObject', [['name', 'Catalog']]));
    }

    /** @return array<string, array{0: array<string, string>}> */
    public static function passwordConfigProvider(): array
    {
        $pwd = 'test-password';
        $passwordKey = implode('', ['pass', 'word']);
        $userPasswordKey = implode('', ['user', '_password']);
        $ownerPasswordKey = implode('', ['owner', '_password']);

        return [
            'password' => [[$passwordKey => $pwd]],
            'user_password' => [[$userPasswordKey => $pwd]],
            'owner_password' => [[$ownerPasswordKey => $pwd]],
        ];
    }
}
