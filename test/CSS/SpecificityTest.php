<?php

/**
 * SpecificityTest.php
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

use Com\Tecnick\Pdf\CSS\Specificity;
use PHPUnit\Framework\Attributes\DataProvider;

class SpecificityTest extends TestUtil
{
    public function testConstructorInitializesValues(): void
    {
        $spec = new Specificity(1, 2, 3);
        $this->assertSame(1, $spec->idCount);
        $this->assertSame(2, $spec->classCount);
        $this->assertSame(3, $spec->typeCount);
    }

    public function testConstructorNormalizesNegativeValues(): void
    {
        $spec = new Specificity(-1, -2, -3);
        $this->assertSame(0, $spec->idCount);
        $this->assertSame(0, $spec->classCount);
        $this->assertSame(0, $spec->typeCount);
    }

    #[DataProvider('selectorSpecificityProvider')]
    public function testFromSelectorCalculatesCorrectSpecificity(
        string $selector,
        int $expectedA,
        int $expectedB,
        int $expectedC,
    ): void {
        $spec = Specificity::fromSelector($selector);
        $this->assertSame($expectedA, $spec->idCount, "ID count mismatch for: {$selector}");
        $this->assertSame(
            $expectedB,
            $spec->classCount,
            "Class/attribute/pseudo-class count mismatch for: {$selector}",
        );
        $this->assertSame($expectedC, $spec->typeCount, "Type/pseudo-element count mismatch for: {$selector}");
    }

    /** @return array<string, array{0: string, 1: int, 2: int, 3: int}> */
    public static function selectorSpecificityProvider(): array
    {
        return [
            'universal selector' => ['*', 0, 0, 0],
            'single type' => ['div', 0, 0, 1],
            'single class' => ['.highlight', 0, 1, 0],
            'single id' => ['#main', 1, 0, 0],
            'type with class' => ['div.highlight', 0, 1, 1],
            'type with id' => ['div#main', 1, 0, 1],
            'type with attribute' => ['div[role="button"]', 0, 1, 1],
            'type with pseudo-class' => ['div:hover', 0, 1, 1],
            'descendant combinator' => ['div p', 0, 0, 2],
            'child combinator' => ['div > p', 0, 0, 2],
            'adjacent sibling' => ['h1 + p', 0, 0, 2],
            'general sibling' => ['h1 ~ p', 0, 0, 2],
            'multiple ids' => ['#main #article', 2, 0, 0],
            'multiple classes' => ['.header.highlight.active', 0, 3, 0],
            'complex selector 1' => ['.header nav li a:hover', 0, 2, 3],
            'complex selector 2' => ['#nav .menu li a:visited', 1, 2, 2],
            'multiple pseudo-classes' => ['a:link:visited:hover', 0, 3, 1],
            'pseudo-element before' => ['p::before', 0, 0, 2],
            'pseudo-element after' => ['p::after', 0, 0, 2],
            'type and pseudo-element' => ['div::before', 0, 0, 2],
            'multiple attributes' => ['div[data-foo][data-bar]', 0, 2, 1],
        ];
    }

    #[DataProvider('specificityComparisonProvider')]
    public function testComparisonMethods(
        int $idCount1,
        int $classCount1,
        int $typeCount1,
        int $idCount2,
        int $classCount2,
        int $typeCount2,
        int $expectedComparison,
    ): void {
        $spec1 = new Specificity($idCount1, $classCount1, $typeCount1);
        $spec2 = new Specificity($idCount2, $classCount2, $typeCount2);

        $result = $spec1->compareTo($spec2);
        $this->assertSame($expectedComparison, $result);

        if ($expectedComparison < 0) {
            $this->assertTrue($spec1->isLessThan($spec2));
            $this->assertFalse($spec1->isGreaterThan($spec2));
            $this->assertFalse($spec1->equals($spec2));
        } elseif ($expectedComparison > 0) {
            $this->assertTrue($spec1->isGreaterThan($spec2));
            $this->assertFalse($spec1->isLessThan($spec2));
            $this->assertFalse($spec1->equals($spec2));
        } else {
            $this->assertTrue($spec1->equals($spec2));
            $this->assertFalse($spec1->isLessThan($spec2));
            $this->assertFalse($spec1->isGreaterThan($spec2));
        }
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: int, 3: int, 4: int, 5: int, 6: int}>
     */
    public static function specificityComparisonProvider(): array
    {
        return [
            'equal' => [0, 0, 0, 0, 0, 0, 0],
            'a determines precedence' => [1, 0, 0, 0, 9, 9, 1],
            'b determines when a equal' => [0, 1, 0, 0, 0, 9, 1],
            'c determines when a and b equal' => [0, 0, 1, 0, 0, 0, 1],
            'less: all components' => [0, 0, 0, 1, 1, 1, -1],
            'greater: all components' => [1, 1, 1, 0, 0, 0, 1],
            'edge case: high b vs low a' => [0, 99, 99, 1, 0, 0, -1],
            'edge case: high c vs low a' => [0, 0, 99, 1, 0, 0, -1],
            'edge case: all high vs single a' => [0, 99, 99, 1, 0, 0, -1],
        ];
    }

    public function testSortKeyFormatting(): void
    {
        $spec = new Specificity(1, 2, 3);
        $key = $spec->toSortKey(0);
        $this->assertStringMatchesFormat('%s_%s', $key);
        $this->assertStringContainsString('0001', $key);
        $this->assertStringContainsString('0002', $key);
        $this->assertStringContainsString('0003', $key);
        $this->assertStringContainsString('000000', $key);
    }

    public function testSortKeyWithSourceOrder(): void
    {
        $spec = new Specificity(1, 2, 3);
        $key1 = $spec->toSortKey(0);
        $key2 = $spec->toSortKey(1);

        // Same specificity, different source order - should be ordered by index
        $this->assertLessThan(0, \strcmp($key1, $key2));
    }

    public function testSortKeyOrderingBySpecificity(): void
    {
        $low = new Specificity(0, 0, 1);
        $medium = new Specificity(0, 1, 0);
        $high = new Specificity(1, 0, 0);

        $keys = [
            $high->toSortKey(0),
            $low->toSortKey(0),
            $medium->toSortKey(0),
        ];

        $sorted = $keys;
        \sort($sorted);

        // After string sort, low < medium < high
        $this->assertSame($low->toSortKey(0), $sorted[0]);
        $this->assertSame($medium->toSortKey(0), $sorted[1]);
        $this->assertSame($high->toSortKey(0), $sorted[2]);
    }

    public function testToStringRepresentation(): void
    {
        $spec = new Specificity(1, 2, 3);
        $str = $spec->toString();
        $this->assertSame('(1,2,3)', $str);
    }

    public function testLegacyStringConversion(): void
    {
        $spec = new Specificity(1, 2, 3);
        $legacy = $spec->toLegacyString(0);
        $this->assertSame('0123', $legacy);

        $legacyInline = $spec->toLegacyString(1);
        $this->assertSame('1123', $legacyInline);
    }

    public function testLegacyStringParsing(): void
    {
        $spec = Specificity::fromLegacyString('0123');
        $this->assertSame(1, $spec->idCount);
        $this->assertSame(2, $spec->classCount);
        $this->assertSame(3, $spec->typeCount);
    }

    public function testLegacyStringRoundTrip(): void
    {
        $original = new Specificity(2, 3, 4);
        $legacy = $original->toLegacyString(0);
        $restored = Specificity::fromLegacyString($legacy);

        $this->assertTrue($original->equals($restored));
    }

    public function testRealWorldSelectorComparisons(): void
    {
        // From CSS spec examples
        $headingOne = Specificity::fromSelector('h1');
        $h1_foo = Specificity::fromSelector('h1.foo');
        $h1_foo_bar = Specificity::fromSelector('h1.foo.bar');
        $div_p_a = Specificity::fromSelector('div p a');
        $foo_bar_baz = Specificity::fromSelector('.foo .bar .baz');
        $id_selector = Specificity::fromSelector('#main');

        // h1 (0,0,1) < h1.foo (0,1,1)
        $this->assertTrue($headingOne->isLessThan($h1_foo));

        // h1.foo (0,1,1) < h1.foo.bar (0,2,1)
        $this->assertTrue($h1_foo->isLessThan($h1_foo_bar));

        // div p a (0,0,3) < .foo .bar .baz (0,3,0)
        $this->assertTrue($div_p_a->isLessThan($foo_bar_baz));

        // All < #main (1,0,0)
        $this->assertTrue($foo_bar_baz->isLessThan($id_selector));
        $this->assertTrue($h1_foo_bar->isLessThan($id_selector));
    }
}
