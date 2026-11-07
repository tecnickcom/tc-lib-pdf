<?php

/**
 * TestableHTMLTcpdfTag.php
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

namespace Test;

/**
 * Implements the TCPDF tag hooks for the tests.
 *
 * The 'box' method draws a filled rectangle object: params [width, height, display, valign, halign].
 * The 'fail' method throws from the object hook.
 * The 'mark' method is a call that returns a PDF comment with its first param.
 *
 * @phpstan-import-type THTMLTcpdfObject from \Com\Tecnick\Pdf\HTML
 */
class TestableHTMLTcpdfTag extends TestableHTML
{
    /** Number of objects created by the object hook. */
    public int $objectCalls = 0;

    /** @var list<array{string, float, float}> Calls run by the call hook: label and position. */
    public array $markCalls = [];

    protected function isAllowedHTMLTcpdfMethod(string $method): bool
    {
        return (
            \in_array($method, ['box', 'fail', 'mark', 'setNamedDestination'], true)
            || parent::isAllowedHTMLTcpdfMethod($method)
        );
    }

    /**
     * @param array<int, mixed> $params
     *
     * @return ?THTMLTcpdfObject
     *
     * @throws \Com\Tecnick\Pdf\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function getHTMLTcpdfTagObject(string $method, array $params): ?array
    {
        if ($method === 'fail') {
            throw new \Com\Tecnick\Pdf\Exception('object hook failure');
        }

        if ($method !== 'box') {
            return parent::getHTMLTcpdfTagObject($method, $params);
        }

        ++$this->objectCalls;
        $width = \is_numeric($params[0] ?? null) ? \floatval($params[0]) : 0.0;
        $height = \is_numeric($params[1] ?? null) ? \floatval($params[1]) : 0.0;
        $tid = $this->newXObjectTemplate($width, $height);
        $this->addXObjectContent($tid, $this->graph->getBasicRect(0, 0, $width, $height, 'F', [
            'fillColor' => 'red',
        ]));
        $this->exitXObjectTemplate();

        return [
            'tid' => $tid,
            'width' => $width,
            'height' => $height,
            'display' => \is_string($params[2] ?? null) ? $params[2] : 'block',
            'valign' => \is_string($params[3] ?? null) ? $params[3] : 'bottom',
            'halign' => \is_string($params[4] ?? null) ? $params[4] : '',
            'alt' => 'box',
        ];
    }

    /**
     * @param array<int, mixed> $params
     */
    protected function runHTMLTcpdfTagCall(string $method, array $params, float $posx, float $posy): string
    {
        if ($method !== 'mark') {
            return parent::runHTMLTcpdfTagCall($method, $params, $posx, $posy);
        }

        $label = \is_string($params[0] ?? null) ? $params[0] : '';
        $this->markCalls[] = [$label, $posx, $posy];

        return '% mark ' . $label . "\n";
    }
}
