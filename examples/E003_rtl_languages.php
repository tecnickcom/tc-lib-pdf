<?php

/**
 * E003_persian_arabic.php
 *
 * @since       2026-04-19
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
require __DIR__ . '/../vendor/autoload.php';

// define fonts directory
\define('K_PATH_FONTS', \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    unit: 'mm',
    isunicode: true,
    subsetfont: false,
    compress: false,
    mode: '',
    objEncrypt: null,
);

// ----------

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 003');
$pdf->setTitle('Persian and Arabic RTL Text Rendering');
$pdf->setKeywords('TCPDF tc-lib-pdf persian arabic rtl bidi unicode shaping');
$pdf->setPDFFilename('003_persian_arabic.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent(false);

$bfont = $pdf->font->insert($pdf->pon, 'dejavusans', '', 12);

// ----------

$txtEngPersian = 'PERSIAN: "The process of printing and correctly displaying Persian texts in digital systems requires full support for the Unicode standard and right-to-left layout. In this test text, all letters of the Persian alphabet, including specific letters like "Pe", "Che", "Zhe", and "Gaf", are included to verify their correct connectivity. Additionally, the use of Persian numbers (1234567890) and punctuation marks like commas (,) and semicolons (;) is essential for accurately measuring the alignment of lines and margins on multi-line pages."';

$txtPersian = 'فرآیند چاپ و نمایش صحیح متون فارسی در سیستم‌های دیجیتال نیازمند پشتیبانی کامل از استاندارد یونیکد و چیدمان راست‌به‌چپ است. در این متن آزمایشی، تمامی حروف الفبای فارسی از جمله حروف خاص مانند «پ»، «چ»، «ژ» و «گ» گنجانده شده‌اند تا صحت اتصال آن‌ها بررسی شود. همچنین استفاده از اعداد فارسی (۱۲۳۴۵۶۷۸۹۰) و علائم نگارشی مانند ویرگول (،) و نقطه‌ویرگول (؛) برای سنجش دقیق ترازبندی خطوط و حاشیه‌های صفحات چندخطی الزامی است.';

$htmlPersian = '<p style="direction:rtl;text-align:right;color:#003366;" lang="fa">فرآیند چاپ و نمایش صحیح متون فارسی در سیستم‌های دیجیتال نیازمند پشتیبانی کامل از استاندارد یونیکد و چیدمان راست‌به‌چپ است. در این متن آزمایشی، تمامی حروف الفبای فارسی از جمله حروف خاص مانند <span style="color:#660000;">«پ»، «چ»، «ژ» و «گ»</span> گنجانده شده‌اند تا صحت اتصال آن‌ها بررسی شود. همچنین استفاده از اعداد فارسی <span style="color:#006600;">(۱۲۳۴۵۶۷۸۹۰)</span> و علائم نگارشی مانند ویرگول (،) و نقطه‌ویرگول (؛) برای سنجش دقیق ترازبندی خطوط و حاشیه‌های صفحات چندخطی الزامی است.</p>';

$txtEngArabic = 'ARABIC: "The process of printing and correctly displaying Arabic texts on digital screens requires full support for the Unicode system and right-to-left formatting. This test text contains various letters, including elongated letters and different Hamzas (أ، إ، آ، ء، ئ، ؤ), to ensure the accuracy of letter connectivity. The text also includes Arabic numerals (1234567890) and punctuation marks such as the comma (،) and semicolon (؛) to verify the integrity of line alignment and multi-page margins."';

$txtArabic = 'إن عملية طباعة النصوص العربية وعرضها بشكل صحيح على الشاشات الرقمية تتطلب دعماً كاملاً لنظام اليونيكود والتنسيق من اليمين إلى اليسار. يحتوي هذا النص التجريبي على حروف متنوعة تشمل الحروف الممدودة والهمزات المختلفة مثل (أ، إ، آ، ء، ئ، ؤ) لضمان دقة اتصال الحروف ببعضها. كما يتضمن النص الأرقام العربية (١٢٣٤٥٦٧٨٩٠) وعلامات الترقيم كالفاصلة (،) والفاصلة المنقوطة (؛) للتحقق من سلامة محاذاة الأسطر وهوامش الصفحات المتعددة.';

$htmlArabic = '<p style="direction:rtl;text-align:right;color:#003366;" lang="ar">إن عملية طباعة النصوص العربية وعرضها بشكل صحيح على الشاشات الرقمية تتطلب دعماً كاملاً لنظام اليونيكود والتنسيق من اليمين إلى اليسار. يحتوي هذا النص التجريبي على حروف متنوعة تشمل الحروف الممدودة والهمزات المختلفة مثل <span style="color:#660000;">(أ، إ، آ، ء، ئ، ؤ)</span> لضمان دقة اتصال الحروف ببعضها. كما يتضمن النص الأرقام العربية <span style="color:#006600;">(١٢٣٤٥٦٧٨٩٠)</span> وعلامات الترقيم كالفاصلة (،) والفاصلة المنقوطة (؛) للتحقق من سلامة محاذاة الأسطر وهوامش الصفحات المتعددة.</p>';

$txtEngHebrew = 'HEBREW: "The process of printing and correctly displaying text in Hebrew on digital systems requires full support for the Unicode standard and right-to-left formatting. In this experimental text, all letters of the alphabet are included, including the final letters (ך, ם, ן, ף, ץ) to verify their correct display. Furthermore, the text integrates numbers (1234567890) and various punctuation marks like a comma (,) and semicolon (;) in order to examine line alignment and page margins in long paragraphs."';

$txtHebrew = 'תהליך ההדפסה וההצגה התקינה של טקסט בעברית במערכות דיגיטליות דורש תמיכה מלאה בתקן יוניקוד ובתצורת כתיבה מימין לשמאל. בטקסט ניסיוני זה נכללו כל אותיות האלפבית, כולל האותיות הסופיות (ך, ם, ן, ף, ץ) כדי לבדוק את תקינות התצוגה שלהן. כמו כן, הטקסט משלב מספרים (1234567890) וסימני פיסוק שונים כמו פסיק (,) ונקודה ופסיק (;) על מנת לבחון את יישור השורות ואת שולי העמודים בפסקאות ארוכות.';

$htmlHebrew = '<p style="direction:rtl;text-align:right;color:#003366;" lang="he">תהליך ההדפסה וההצגה התקינה של טקסט בעברית במערכות דיגיטליות דורש תמיכה מלאה בתקן יוניקוד ובתצורת כתיבה מימין לשמאל. בטקסט ניסיוני זה נכללו כל אותיות האלפבית, כולל האותיות הסופיות <span style="color:#660000;">(ך, ם, ן, ף, ץ)</span> כדי לבדוק את תקינות התצוגה שלהן. כמו כן, הטקסט משלב מספרים <span style="color:#006600;">(1234567890)</span> וסימני פיסוק שונים כמו פסיק (,) ונקודה ופסיק (;) על מנת לבחון את יישור השורות ואת שולי העמודים בפסקאות ארוכות.</p>';

// ----------

$psx = 15;
$lnw = 180;

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->setRTL(false);
$pdf->addHTMLCell(
    html: '<h1>RTL (Right-To-Left) Example</h1><h2>1. getTextCell()</h2>',
    posx: $psx,
    posy: 15,
    width: $lnw,
);

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getTextCell(txt: $txtEngPersian, posx: $psx, posy: 40, width: $lnw, halign: 'L'));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getTextCell(txt: $txtPersian, posx: $psx, posy: 80, width: $lnw, halign: 'R'));

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getTextCell(txt: $txtEngArabic, posx: $psx, posy: 120, width: $lnw, halign: 'L'));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getTextCell(txt: $txtArabic, posx: $psx, posy: 160, width: $lnw, halign: 'R'));

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getTextCell(txt: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw, halign: 'L'));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getTextCell(txt: $txtHebrew, posx: $psx, posy: 240, width: $lnw, halign: 'R'));

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: '<h2>2. addTextCell()</h2>', posx: $psx, posy: 15, width: $lnw);

$pdf->setRTL(false);
$pdf->addTextCell(txt: $txtEngPersian, posx: $psx, posy: 40, width: $lnw, halign: 'L', drawcell: false);
$pdf->setRTL(true);
$pdf->addTextCell(txt: $txtPersian, posx: $psx, posy: 80, width: $lnw, halign: 'R', drawcell: false);

$pdf->setRTL(false);
$pdf->addTextCell(txt: $txtEngArabic, posx: $psx, posy: 120, width: $lnw, halign: 'L', drawcell: false);
$pdf->setRTL(true);
$pdf->addTextCell(txt: $txtArabic, posx: $psx, posy: 160, width: $lnw, halign: 'R', drawcell: false);

$pdf->setRTL(false);
$pdf->addTextCell(txt: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw, halign: 'L', drawcell: false);
$pdf->setRTL(true);
$pdf->addTextCell(txt: $txtHebrew, posx: $psx, posy: 240, width: $lnw, halign: 'R', drawcell: false);

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: '<h2>3. getHTMLCell() - simple text</h2>', posx: $psx, posy: 15, width: $lnw);

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngPersian, posx: $psx, posy: 40, width: $lnw));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtPersian, posx: $psx, posy: 80, width: $lnw));

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngArabic, posx: $psx, posy: 120, width: $lnw));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtArabic, posx: $psx, posy: 160, width: $lnw));

$pdf->setRTL(false);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw));
$pdf->setRTL(true);
$pdf->page->addContent($pdf->getHTMLCell(html: $txtHebrew, posx: $psx, posy: 240, width: $lnw));

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: '<h2>4. addtHTMLCell() - simple text</h2>', posx: $psx, posy: 15, width: $lnw);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: $txtEngPersian, posx: $psx, posy: 40, width: $lnw);
$pdf->setRTL(true);
$pdf->addHTMLCell(html: $txtPersian, posx: $psx, posy: 80, width: $lnw);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: $txtEngArabic, posx: $psx, posy: 120, width: $lnw);
$pdf->setRTL(true);
$pdf->addHTMLCell(html: $txtArabic, posx: $psx, posy: 160, width: $lnw);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw);
$pdf->setRTL(true);
$pdf->addHTMLCell(html: $txtHebrew, posx: $psx, posy: 240, width: $lnw);

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->setRTL(false);
$pdf->addHTMLCell(html: '<h2>5. getHTMLCell() - HTML fragments</h2>', posx: $psx, posy: 15, width: $lnw);

$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngPersian, posx: $psx, posy: 40, width: $lnw));
$pdf->page->addContent($pdf->getHTMLCell(html: $htmlPersian, posx: $psx, posy: 80, width: $lnw));

$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngArabic, posx: $psx, posy: 120, width: $lnw));
$pdf->page->addContent($pdf->getHTMLCell(html: $htmlArabic, posx: $psx, posy: 160, width: $lnw));

$pdf->page->addContent($pdf->getHTMLCell(html: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw));
$pdf->page->addContent($pdf->getHTMLCell(html: $htmlHebrew, posx: $psx, posy: 240, width: $lnw));

// ----------

$page = $pdf->addPage();

$pdf->page->addContent($bfont['out']);

$pdf->addHTMLCell(html: '<h2>6. addtHTMLCell() - HTML fragments</h2>', posx: $psx, posy: 15, width: $lnw);

$pdf->addHTMLCell(html: $txtEngPersian, posx: $psx, posy: 40, width: $lnw);
$pdf->addHTMLCell(html: $htmlPersian, posx: $psx, posy: 80, width: $lnw);

$pdf->addHTMLCell(html: $txtEngArabic, posx: $psx, posy: 120, width: $lnw);
$pdf->addHTMLCell(html: $htmlArabic, posx: $psx, posy: 160, width: $lnw);

$pdf->addHTMLCell(html: $txtEngHebrew, posx: $psx, posy: 200, width: $lnw);
$pdf->addHTMLCell(html: $htmlHebrew, posx: $psx, posy: 240, width: $lnw);

// =============================================================

$rawpdf = $pdf->getOutPDFString();

$pdf->renderPDF(rawpdf: $rawpdf);
