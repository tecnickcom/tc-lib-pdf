<?php

/**
 * TestableHTMLCSSBuildProbe.php
 *
 * @since       2026-10-11
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

/**
 * Counts the final-tree CSS passes of getHTMLDOM(), compares each opening child
 * lookup made with the child index of getHTMLDOM() with the lookup made by scanning
 * the DOM, and can check every stylesheet rule for every element instead of the
 * candidates of the rule index.
 *
 * @phpstan-import-type THTMLDOMNode from \Com\Tecnick\Pdf\HTML
 */
class TestableHTMLCSSBuildProbe extends TestableHTML
{
    public bool $fullRuleScan = false;

    private int $finalTreePasses = 0;

    private int $indexedLookups = 0;

    /** @var list<array{parent: int, indexed: array<int>, scanned: array<int>}> */
    private array $mismatches = [];

    public function exposeFinalTreePasses(): int
    {
        return $this->finalTreePasses;
    }

    public function exposeIndexedLookups(): int
    {
        return $this->indexedLookups;
    }

    /** @return list<array{parent: int, indexed: array<int>, scanned: array<int>}> */
    public function exposeChildKeyMismatches(): array
    {
        return $this->mismatches;
    }

    /**
     * @param array<int, THTMLDOMNode> $dom
     *
     * @return array<int>
     */
    protected function getHTMLOpeningChildKeys(array &$dom, int $parent): array
    {
        $index = $this->htmlDOMChildKeys;
        $indexed = parent::getHTMLOpeningChildKeys($dom, $parent);
        if ($index === null) {
            return $indexed;
        }

        ++$this->indexedLookups;
        $this->htmlDOMChildKeys = null;
        $scanned = parent::getHTMLOpeningChildKeys($dom, $parent);
        $this->htmlDOMChildKeys = $index;
        if ($indexed !== $scanned) {
            $this->mismatches[] = ['parent' => $parent, 'indexed' => $indexed, 'scanned' => $scanned];
        }

        return $indexed;
    }

    /**
     * @param array<int, THTMLDOMNode> $dom
     * @param array<string, string> $css
     */
    protected function recomputeHTMLDOMCSSAgainstFinalTree(array &$dom, array $css): void
    {
        ++$this->finalTreePasses;
        parent::recomputeHTMLDOMCSSAgainstFinalTree($dom, $css);
    }

    /**
     * @param array<int, THTMLDOMNode> $dom
     * @param array<string, list<int>> $buckets
     *
     * @return list<int>
     */
    protected function getHTMLCSSCandidateRules(array &$dom, int $key, array $buckets): array
    {
        if (!$this->fullRuleScan) {
            return parent::getHTMLCSSCandidateRules($dom, $key, $buckets);
        }

        $all = [];
        foreach ($buckets as $list) {
            foreach ($list as $ruleidx) {
                $all[$ruleidx] = true;
            }
        }

        \ksort($all);

        return \array_keys($all);
    }
}
