<?php
/**
 * E046_pangrams.php
 *
 * @since       2026-05-01
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

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    '', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 046');
$pdf->setTitle('Pangrams');
$pdf->setKeywords('TCPDF tc-lib-pdf example pangrams unicode multilingual');
$pdf->setPDFFilename('046_pangrams.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

// unifont covers the full Unicode BMP including Arabic, Hebrew, Thai,
// Devanagari, Hangul, and CJK Unified Ideographs.
$bfont = $pdf->font->insert($pdf->pon, 'unifont', '', 14);

$page = $pdf->addPage(['format' => 'A4']);

$pdf->page->addContent($bfont['out']);

$html = '<h1>Pangrams</h1>';

// English
$html .= '<h2>English</h2>';
$html .= '<p>The quick brown fox jumps over the lazy dog.</p>';

// Arabic (RTL)
$html .= '<h2>Arabic (العربية)</h2>';
$html .= '<div style="text-align:right">'
    . 'صِف خَلقَ خَودِكِ كَمِثلِ الشَّمسِ إِذ بَزَغَت'
    . ' — يَحظى الضَّجيعُ بِها نَجلاءَ مِعطارِ'
    . '</div>';

// Hebrew (RTL)
$html .= '<h2>Hebrew (עברית)</h2>';
$html .= '<div style="text-align:right">'
    . 'דג סקרן שט בים מאוכזב ולפתע מצא חברה'
    . '</div>';

// Thai
$html .= '<h2>Thai (ภาษาไทย)</h2>';
$html .= '<p>'
    . 'เป็นมนุษย์สุดประเสริฐเลิศคุณค่า'
    . ' กว่าบรรดาฝูงสัตว์เดรัจฉาน'
    . ' จงฝ่าฟันพัฒนาวิชาการ'
    . ' อย่าล้างผลาญฤๅเข่นฆ่าบีฑาใคร'
    . '</p>';

// Devanagari (Hindi)
$html .= '<h2>Devanagari (देवनागरी)</h2>';
$html .= '<p>'
    . 'ऋषि सुयश ने प्रख्यात ग्रंथ की रचना करके हमें ज्ञान दिया।'
    . '</p>';

// Hangul (Korean)
$html .= '<h2>Hangul (한글)</h2>';
$html .= '<p>'
    . '키스의 고유조건은 입술끼리 만나야 하고'
    . ' 특별한 기술은 필요치 않다'
    . '</p>';

// Greek
$html .= '<h2>Greek (Ελληνικά)</h2>';
$html .= '<p>'
    . 'Ξεσκεπάζω την ψυχοφθόρα βδελυγμία.'
    . '</p>';

// Japanese
$html .= '<h2>Japanese (日本語)</h2>';
$html .= '<p>'
    . 'いろはにほへと ちりぬるを わかよたれそ つねならむ'
    . ' うゐのおくやま けふこえて あさきゆめみし ゑひもせす'
    . '</p>';

// Russian
$html .= '<h2>Russian (Русский)</h2>';
$html .= '<p>'
    . 'Съешь же ещё этих мягких французских булок, да выпей чаю.'
    . '</p>';

// Armenian
$html .= '<h2>Armenian (Հայերեն)</h2>';
$html .= '<p>'
    . 'Բոլոր մարդիկ ծնվում են ազատ և հավասար իրենց արժանապատ­վությամբ և իրա­վուն­քնե­րով։'
    . '</p>';

// Georgian
$html .= '<h2>Georgian (ქართული)</h2>';
$html .= '<p>'
    . 'გთხოვთ ახლავე გაიაროთ რეგისტრაცია უფასო პროგრამაზე.'
    . '</p>';

// Tamil
$html .= '<h2>Tamil (தமிழ்)</h2>';
$html .= '<p>'
    . 'யாமறிந்த மொழிகளிலே தமிழ்மொழி போல் இனிதாவது எங்கும் காணோம்.'
    . '</p>';

// Bengali
$html .= '<h2>Bengali (বাংলা)</h2>';
$html .= '<p>'
    . 'সুপ্রিয় বন্ধুগণ, আমরা বাংলা ভাষায় কথা বলি।'
    . '</p>';

// Amharic
$html .= '<h2>Amharic (አማርኛ)</h2>';
$html .= '<p>'
    . 'ሰው ናቸው ወይስ ሃሳቤን ሳልጨርስ ትዕዛዙን ሰጡ?'
    . '</p>';

// Khmer
$html .= '<h2>Khmer (ភាសាខ្មែរ)</h2>';
$html .= '<p>'
    . 'ខ្ញុំអាចញ៉ាំកញ្ចក់បានដោយគ្មានបញ្ហា។'
    . '</p>';

// Tibetan
$html .= '<h2>Tibetan (བོད་སྐད།)</h2>';
$html .= '<p>'
    . 'ང་གཤེགས་ཀྱི་ཡིག་གུ་ཟ་ཐུབ་ཀྱི་ཡོད། དེས་ང་ལ་གནོད་པ་མི་བྱེད།'
    . '</p>';

// Chinese (Traditional — Thousand Character Classic)
$html .= '<h2>Chinese (中文)</h2>';
$html .= '<p>'
    . '天地玄黄，宇宙洪荒。日月盈昃，辰宿列张。'
    . '寒来暑往，秋收冬藏。闰余成岁，律吕调阳。'
    . '</p>';

$pdf->addHTMLCell($html, 10, 10, 190);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF($rawpdf);
