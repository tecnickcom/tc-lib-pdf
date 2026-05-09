<?php

/**
 * ImportanceNormalizerTest.php
 *
 * @since       2026-05-08
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

use PHPUnit\Framework\Attributes\DataProvider;
use Com\Tecnick\Pdf\CSS\ImportanceNormalizer;

/**
 * Test ImportanceNormalizer CSS declaration normalization
 */
class ImportanceNormalizerTest extends TestUtil
{
    public function testGetAffectedLonghandsExpandsNestedBorderAliases(): void
    {
        $affected = ImportanceNormalizer::getAffectedLonghands('border');

        $this->assertContains('border-width', $affected);
        $this->assertContains('border-style', $affected);
        $this->assertContains('border-color', $affected);
        $this->assertContains('border-top-width', $affected);
        $this->assertContains('border-right-style', $affected);
        $this->assertContains('border-bottom-color', $affected);
        $this->assertContains('border-left-width', $affected);
    }

    public function testNormalizePreservesRegularDeclarations(): void
    {
        $input = 'color: red; font-size: 12px;';
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringContainsString('color:red;', \str_replace(' ', '', $result));
        $this->assertStringContainsString('font-size:12px;', \str_replace(' ', '', $result));
        $this->assertStringNotContainsString('!important', $result);
    }

    public function testNormalizePreservesImportantFlag(): void
    {
        $input = 'color: red !important; font-size: 12px;';
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringContainsString('color:red!important;', \str_replace(' ', '', $result));
        $this->assertStringNotContainsString('font-size:12px!important;', \str_replace(' ', '', $result));
    }

    public function testNormalizeBorderShorthandWithoutImportant(): void
    {
        $input = 'border: 1px solid red;';
        $result = ImportanceNormalizer::normalize($input);

        // Should contain the shorthand property
        $this->assertStringContainsString('border:', $result);
        $this->assertStringNotContainsString('!important', $result);
    }

    public function testNormalizeBorderShorthandWithImportant(): void
    {
        $input = 'border: 1px solid red !important;';
        $result = ImportanceNormalizer::normalize($input);

        // The shorthand should have !important
        $this->assertStringContainsString('border:', $result);
        $this->assertStringContainsString('!important', $result);
    }

    public function testNormalizeMixedShorthandAndLonghands(): void
    {
        $input = 'margin: 10px !important; margin-top: 20px;';
        $result = ImportanceNormalizer::normalize($input);

        // Should contain both margin shorthand and the longhand
        $this->assertStringContainsString('margin:', $result);
        $this->assertStringContainsString('margin-top:', $result);
        // Shorthand !important should be preserved
        $this->assertStringContainsString('!important', $result);
    }

    public function testNormalizePaddingShorthandWithImportant(): void
    {
        $input = 'padding: 5px 10px !important;';
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringContainsString('padding:', $result);
        $this->assertStringContainsString('!important', $result);
    }

    public function testNormalizeEmptyString(): void
    {
        $result = ImportanceNormalizer::normalize('');
        $this->assertSame('', $result);
    }

    public function testNormalizeHandlesWhitespaceAroundImportant(): void
    {
        $input = 'color: blue !  important;';
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringContainsString('!important', $result);
    }

    public function testNormalizeMultipleImportantDeclarations(): void
    {
        $input = 'color: red !important; background: blue !important; font-size: 14px;';
        $result = ImportanceNormalizer::normalize($input);

        $normalized = \str_replace(' ', '', $result);
        $this->assertStringContainsString('color:red!important;', $normalized);
        $this->assertStringContainsString('background:blue!important;', $normalized);
        $this->assertStringNotContainsString('font-size:14px!important;', $normalized);
    }

    public function testNormalizeHandlesTrailingSemicolon(): void
    {
        $input = 'color: red;';
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringEndsWith(';', $result);
    }

    public function testNormalizeCaseInsensitive(): void
    {
        $input = 'COLOR: red !IMPORTANT;';
        $result = ImportanceNormalizer::normalize($input);

        // Property should be lowercased
        $this->assertStringContainsString('color:', $result);
        // !important flag should be present (case-insensitive)
        $this->assertStringContainsString('!important', $result);
    }

    #[DataProvider('realWorldDeclarationsProvider')]
    public function testNormalizeRealWorldDeclarations(string $input, string $expectedProperty): void
    {
        $result = ImportanceNormalizer::normalize($input);

        $this->assertStringContainsString($expectedProperty, $result);
    }

    /** @return array<string, array{0: string, 1: string}> */
    public static function realWorldDeclarationsProvider(): array
    {
        return [
            'button_styling' => [
                'padding: 10px 20px !important; color: white; background: blue;',
                'padding:',
            ],
            'text_emphasis' => [
                'font-weight: bold !important; font-size: 14px;',
                'font-weight:',
            ],
            'border_reset' => [
                'border: none !important; margin: 0;',
                'border:',
            ],
        ];
    }
}
