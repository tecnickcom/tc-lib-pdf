<?php

/**
 * CascadeContextTest.php
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

use Com\Tecnick\Pdf\CSS\CascadeContext;

class CascadeContextTest extends TestUtil
{
    public function testInitialStateHasZeroSourceOrder(): void
    {
        $ctx = new CascadeContext();

        $this->assertSame(0, $ctx->getTotalRulesProcessed());
    }

    public function testGetNextNormalSourceOrderIncrementsCounter(): void
    {
        $ctx = new CascadeContext();

        $order1 = $ctx->getNextNormalSourceOrder();
        $order2 = $ctx->getNextNormalSourceOrder();
        $order3 = $ctx->getNextNormalSourceOrder();

        $this->assertSame(1, $order1);
        $this->assertSame(2, $order2);
        $this->assertSame(3, $order3);
        $this->assertSame(3, $ctx->getTotalRulesProcessed());
    }

    public function testGetNextImportantSourceOrderStartsAboveNormalRange(): void
    {
        $ctx = new CascadeContext();

        // Normal rules
        $ctx->getNextNormalSourceOrder();
        $ctx->getNextNormalSourceOrder();

        // First important rule should start at MIN_IMPORTANT_SOURCE_ORDER
        $importantOrder = $ctx->getNextImportantSourceOrder();
        $this->assertSame(CascadeContext::MIN_IMPORTANT_SOURCE_ORDER + 1, $importantOrder);
        $this->assertGreaterThan(CascadeContext::INLINE_STYLE_SOURCE_ORDER, $importantOrder);
    }

    public function testImportantAndNormalCountersAreIndependent(): void
    {
        $ctx = new CascadeContext();

        $normal1 = $ctx->getNextNormalSourceOrder();
        $important1 = $ctx->getNextImportantSourceOrder();
        $normal2 = $ctx->getNextNormalSourceOrder();
        $important2 = $ctx->getNextImportantSourceOrder();

        $this->assertSame(1, $normal1);
        $this->assertSame(CascadeContext::MIN_IMPORTANT_SOURCE_ORDER + 1, $important1);
        $this->assertSame(2, $normal2);
        $this->assertSame(CascadeContext::MIN_IMPORTANT_SOURCE_ORDER + 2, $important2);
    }

    public function testInlineStyleSourceOrderIsMaximumNormalValue(): void
    {
        $inlineOrder = CascadeContext::getInlineStyleSourceOrder();

        $this->assertSame(10000000, $inlineOrder);
        $this->assertGreaterThan(CascadeContext::MAX_NORMAL_SOURCE_ORDER, $inlineOrder);
        $this->assertLessThan(CascadeContext::MIN_IMPORTANT_SOURCE_ORDER, $inlineOrder);
    }

    public function testSetCurrentSourceTypeStoresAndRetrievesValue(): void
    {
        $ctx = new CascadeContext();

        $this->assertSame('embedded', $ctx->getCurrentSourceType());

        $ctx->setCurrentSourceType('external');
        $this->assertSame('external', $ctx->getCurrentSourceType());

        $ctx->setCurrentSourceType('inline');
        $this->assertSame('inline', $ctx->getCurrentSourceType());
    }

    public function testResetClearsAllCountersAndState(): void
    {
        $ctx = new CascadeContext();

        // Add some rules
        $ctx->setCurrentSourceType('external');
        $ctx->getNextNormalSourceOrder();
        $ctx->getNextNormalSourceOrder();
        $ctx->getNextImportantSourceOrder();

        $this->assertGreaterThan(0, $ctx->getTotalRulesProcessed());
        $this->assertSame('external', $ctx->getCurrentSourceType());

        // Reset
        $ctx->reset();

        $this->assertSame(0, $ctx->getTotalRulesProcessed());
        $this->assertSame('embedded', $ctx->getCurrentSourceType());
    }

    public function testMultipleSourcesCanBeProcessedSequentially(): void
    {
        $ctx = new CascadeContext();

        // External stylesheet: 3 rules
        $ctx->setCurrentSourceType('external');
        $ctx->getNextNormalSourceOrder();
        $ctx->getNextNormalSourceOrder();
        $ext3 = $ctx->getNextNormalSourceOrder();

        // Embedded style: 2 rules
        $ctx->setCurrentSourceType('embedded');
        $emb1 = $ctx->getNextNormalSourceOrder();
        $emb2 = $ctx->getNextNormalSourceOrder();

        // Inline style: 1 rule
        $ctx->setCurrentSourceType('inline');
        $inl1 = $ctx->getNextNormalSourceOrder();

        // Verify ordering: external < embedded < inline
        $this->assertLessThan($emb1, $ext3);
        $this->assertLessThan($inl1, $emb2);
        $this->assertSame(6, $ctx->getTotalRulesProcessed());
    }

    public function testSourceOrderValuesRemainBelowImportantRange(): void
    {
        $ctx = new CascadeContext();

        // Generate many normal rules
        for ($i = 0; $i < 100; ++$i) {
            $order = $ctx->getNextNormalSourceOrder();
            $this->assertLessThanOrEqual(
                CascadeContext::MAX_NORMAL_SOURCE_ORDER,
                $order,
                "Normal source order exceeded max at iteration {$i}",
            );
        }

        // Important rules should still be higher
        $importantOrder = $ctx->getNextImportantSourceOrder();
        $this->assertGreaterThan(CascadeContext::MAX_NORMAL_SOURCE_ORDER, $importantOrder);
    }

    public function testCascadePrecedenceForDeterminism(): void
    {
        // This test verifies the precedence model:
        // normal rules < inline styles < !important rules

        $normalMax = CascadeContext::MAX_NORMAL_SOURCE_ORDER;
        $inlineOrder = CascadeContext::getInlineStyleSourceOrder();
        $importantMin = CascadeContext::MIN_IMPORTANT_SOURCE_ORDER;

        // Verify cascade ordering: normal < inline < important
        $this->assertLessThan($inlineOrder, $normalMax);
        $this->assertLessThan($importantMin, $inlineOrder);
    }
}
