<?php

/**
 * TestUtil.php
 *
 * @since     2020-12-19
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-file software library.
 */

namespace Test;

use PHPUnit\Framework\TestCase;

/**
 * Test Util
 *
 * @since     2020-12-19
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2015-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class TestUtil extends TestCase
{
    public static function setUpFontsPath(): void
    {
        if (!\defined('K_PATH_FONTS')) {
            $fonts = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
            \define('K_PATH_FONTS', $fonts);
        }
    }

    public function bcAssertEqualsWithDelta(
        mixed $expected,
        mixed $actual,
        float $delta = 0.01,
        string $message = ''
    ): void {
        parent::assertEqualsWithDelta($expected, $actual, $delta, $message);
    }

    /**
     * @param class-string<\Throwable> $exception
     */
    public function bcExpectException($exception): void
    {
        parent::expectException($exception);
    }

    public function bcAssertIsResource(mixed $res): void
    {
        parent::assertIsResource($res);
    }

    public function bcAssertMatchesRegularExpression(string $pattern, string $string, string $message = ''): void
    {
        parent::assertMatchesRegularExpression($pattern, $string, $message);
    }

    protected function getObjectProperty(object $obj, string $name): mixed
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                return $prop->getValue($obj);
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    protected function setObjectProperty(object $obj, string $name, mixed $value): void
    {
        $ref = new \ReflectionClass($obj);
        while ($ref !== false) {
            if ($ref->hasProperty($name)) {
                $prop = $ref->getProperty($name);
                $prop->setAccessible(true);
                $prop->setValue($obj, $value);
                return;
            }
            $ref = $ref->getParentClass();
        }

        $this->fail('Property not found: ' . $name);
    }

    protected function initFont(\Com\Tecnick\Pdf\Tcpdf $obj): void
    {
        self::setUpFontsPath();
        /** @var \Com\Tecnick\Pdf\Font\Stack $font */
        $font = $this->getObjectProperty($obj, 'font');
        /** @var int $pon */
        $pon = $this->getObjectProperty($obj, 'pon');
        $fontfile = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/core/helvetica.json');
        $font->insert($pon, 'helvetica', '', 10, null, null, $fontfile);
    }

    /**
     * @phpstan-return array{pid: int, height: float}
     */
    protected function initFontAndPage(\Com\Tecnick\Pdf\Tcpdf $obj): array
    {
        $this->initFont($obj);
        /** @var array{pid: int, height: float} $page */
        $page = $obj->addPage();
        return $page;
    }
}
