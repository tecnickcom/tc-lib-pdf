<?php

/**
 * TestUtil.php
 *
 * @since       2020-12-19
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-file software library.
 */

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * Test Util
 *
 * @since      2020-12-19
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2015-2023 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 */
class TestUtil extends TestCase
{
    public function bcAssertEqualsWithDelta($expected, $actual, $delta = 0.01, $message = '')
    {
        if (\is_callable([self::class, 'assertEqualsWithDelta'])) {
            parent::assertEqualsWithDelta($expected, $actual, $delta, $message);
            return;
        }
        /* @phpstan-ignore-next-line */
        $this->assertEquals($expected, $actual, $message, $delta);
    }

    public function bcExpectException($exception)
    {
        if (\is_callable([self::class, 'expectException'])) {
            parent::expectException($exception);
            return;
        }
        /* @phpstan-ignore-next-line */
        parent::setExpectedException($exception);
    }

    public function bcAssertIsResource($res)
    {
        if (\is_callable([self::class, 'assertIsResource'])) {
            parent::assertIsResource($res);
            return;
        }
        /* @phpstan-ignore-next-line */
        parent::assertInternalType('resource', $res);
    }

    public function bcAssertMatchesRegularExpression($pattern, $string, $message = '')
    {
        if (\is_callable([self::class, 'assertMatchesRegularExpression'])) {
            parent::assertMatchesRegularExpression($pattern, $string, $message);
            return;
        }
        /* @phpstan-ignore-next-line */
        parent::assertRegExp($pattern, $string, $message);
    }
}
