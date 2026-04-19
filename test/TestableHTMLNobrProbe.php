<?php

/**
 * TestableHTMLNobrProbe.php
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

class TestableHTMLNobrProbe extends TestableHTML
{
    /** @var array<int, string> */
    private array $nobrOpenStates = [];

    /** @return array<int, string> */
    public function exposeNobrOpenStates(): array
    {
        return $this->nobrOpenStates;
    }

    protected function parseHTMLTagOPENdiv(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = $hrc['dom'][$key];
        $state = '';
        if (!empty($elm['attribute']['nobr']) && \is_string($elm['attribute']['nobr'])) {
            $state = $elm['attribute']['nobr'];
        }
        $this->nobrOpenStates[] = $state;

        return parent::parseHTMLTagOPENdiv($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }
}
