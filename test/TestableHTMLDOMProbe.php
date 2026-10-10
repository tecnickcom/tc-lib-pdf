<?php

/**
 * TestableHTMLDOMProbe.php
 *
 * @since       2026-10-10
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
 * Records the inherited properties of the opening DOM nodes before and after each
 * HTML rendering.
 *
 * @phpstan-import-type THTMLDOMNode from \Com\Tecnick\Pdf\HTML
 */
class TestableHTMLDOMProbe extends TestableHTML
{
    /** @var array<int, array{before: array<int, array<array-key, mixed>>, after: array<int, array<array-key, mixed>>}> */
    private array $inheritedSnapshots = [];

    /** @return array<int, array{before: array<int, array<array-key, mixed>>, after: array<int, array<array-key, mixed>>}> */
    public function exposeInheritedSnapshots(): array
    {
        return $this->inheritedSnapshots;
    }

    protected function renderHTMLCellFragments(
        array &$hrc,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        callable $appendFragment,
    ): void {
        $before = $this->getInheritedSnapshot($hrc['dom']);
        parent::renderHTMLCellFragments($hrc, $tpx, $tpy, $tpw, $tph, $appendFragment);
        $this->inheritedSnapshots[] = ['before' => $before, 'after' => $this->getInheritedSnapshot($hrc['dom'])];
    }

    /**
     * Returns the inherited properties of each opening node.
     *
     * @param array<int, THTMLDOMNode> $dom
     *
     * @return array<int, array<array-key, mixed>>
     */
    private function getInheritedSnapshot(array $dom): array
    {
        $keys = \array_fill_keys($this->exposeGetHTMLInheritedProperties(), true);
        $snapshot = [];
        foreach ($dom as $key => $node) {
            if (!$node['tag'] || !$node['opening']) {
                continue;
            }

            $snapshot[$key] = \array_intersect_key($node, $keys);
        }

        return $snapshot;
    }
}
