<?php
/**
 * E028_text_hyphenation.php
 *
 * @since       2026-04-26
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license     https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    false,
    true,
    '',
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 028');
$pdf->setTitle('Text hyphenation with soft hyphens');
$pdf->setKeywords('TCPDF tc-lib-pdf example text hyphenation soft hyphen shy html');
$pdf->setPDFFilename('028_text_hyphenation.pdf');
$pdf->setViewerPreferences(['DisplayDocTitle' => true]);
$pdf->enableDefaultPageContent();

$setFont = static function (
    \Com\Tecnick\Pdf\Tcpdf $pdf,
    string $family,
    string $style,
    int $size,
): void {
    $font = $pdf->font->insert($pdf->pon, $family, $style, $size, 0.0, 1.0);
    $pdf->page->addContent($font['out']);
};

// Insert one neutral font before addPage() so page context has a valid current font.
$pdf->font->insert($pdf->pon, 'helvetica', '', 10, 0.0, 1.0);

$pdf->addPage();

$setFont($pdf, 'helvetica', 'B', 20);
$pdf->page->addContent(
    $pdf->getTextCell(
        'Example of Text Hyphenation',
        15.0,
        22.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$setFont($pdf, 'helvetica', '', 10);
$pdf->page->addContent(
    $pdf->getTextCell(
        'The text below uses explicit soft hyphens (&shy;) inside words and is rendered in a narrow left-aligned column.',
        15.0,
        34.0,
        180.0,
        0.0,
        drawcell: false,
        valign: 'T',
        halign: 'L',
    )
);

$setFont($pdf, 'times', '', 10);

$html = '<div style="color:#003f7f;text-align:justify">'
    . 'On the other hand, we de&shy;nounce with righ&shy;teous in&shy;dig&shy;na&shy;tion '
    . 'and dis&shy;like men who are so be&shy;guiled and de&shy;mo&shy;r&shy;al&shy;ized by the charms '
    . 'of plea&shy;sure of the mo&shy;ment, so blind&shy;ed by de&shy;sire, that they can&shy;not fore&shy;see '
    . 'the pain and trou&shy;ble that are bound to en&shy;sue; and equal blame be&shy;longs to '
    . 'those who fail in their du&shy;ty through weak&shy;ness of will, which is the same as '
    . 'say&shy;ing through shrink&shy;ing from toil and pain. Th&shy;ese cas&shy;es are per&shy;fect&shy;ly '
    . 'sim&shy;ple and easy to distin&shy;guish. In a free hour, when our pow&shy;er of choice is '
    . 'un&shy;tram&shy;melled and when noth&shy;ing pre&shy;vents our be&shy;ing able to do what we like '
    . 'best, ev&shy;ery plea&shy;sure is to be wel&shy;comed and ev&shy;ery pain avoid&shy;ed. But in '
    . 'cer&shy;tain cir&shy;cum&shy;s&shy;tances and ow&shy;ing to the claims of du&shy;ty or the obli&shy;ga&shy;tions '
    . 'of busi&shy;ness it will fre&shy;quent&shy;ly oc&shy;cur that plea&shy;sures have to be '
    . 're&shy;pu&shy;di&shy;at&shy;ed and an&shy;noy&shy;ances ac&shy;cept&shy;ed. The wise man there&shy;fore al&shy;ways holds '
    . 'in th&shy;ese mat&shy;ters to this prin&shy;ci&shy;ple of se&shy;lec&shy;tion: he re&shy;jects plea&shy;sures to '
    . 'se&shy;cure other greater plea&shy;sures, or else he en&shy;dures pains to avoid worse pains.'
    . '</div>';

$cellBorderStyles = [
    'all' => [
        'lineWidth' => 0.2,
        'lineCap' => 'butt',
        'lineJoin' => 'miter',
        'dashArray' => [],
        'dashPhase' => 0,
        'lineColor' => 'red',
        'fillColor' => '',
    ],
];

$pdf->addHTMLCell(
    $html,
    15.0,
    45.0,
    42.0,
    0.0,
    null,
    $cellBorderStyles,
);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
