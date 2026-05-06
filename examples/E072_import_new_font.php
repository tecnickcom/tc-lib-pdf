<?php
/**
 * E072_import_new_font.php
 *
 * Demonstrates a direct custom-font workflow:
 * 1. Download Noto Sans Regular.
 * 2. Convert it with tc-lib-pdf-font.
 * 3. Start a PDF page and render text using that imported font.
 *
 * @since       2026-05-06
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

require(__DIR__ . '/../vendor/autoload.php');

$defaultFontsDir = (string) \realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts');
$sourceFontsDir = __DIR__ . '/../target/fonts/source';
$customFontsDir = __DIR__ . '/../target/fonts/custom';
$notoFileName = 'NotoSans-Regular.ttf';
$notoUrl = 'https://github.com/notofonts/noto-fonts/raw/main/hinted/ttf/NotoSans/NotoSans-Regular.ttf';
$notoSourcePath = $sourceFontsDir . '/' . $notoFileName;
$fontFamily = 'notosans';
$fontSetupError = '';
$customFontsReal = '';

/**
 * Download a file from URL to local destination.
 *
 * @throws RuntimeException in case of download failure
 */
function downloadFile(string $url, string $destination, int $timeout = 30): void
{
    $ctx = \stream_context_create([
        'http' => [
            'follow_location' => 1,
            'max_redirects' => 5,
            'timeout' => $timeout,
        ],
    ]);

    $data = @\file_get_contents($url, false, $ctx);
    if ($data === false) {
        throw new \RuntimeException('Unable to download font from: ' . $url);
    }

    if (@\file_put_contents($destination, $data) === false) {
        throw new \RuntimeException('Unable to save downloaded font to: ' . $destination);
    }
}

try {
    if (!\is_dir($sourceFontsDir)) {
        if (!@\mkdir($sourceFontsDir, 0755, true) && !\is_dir($sourceFontsDir)) {
            throw new \RuntimeException('Unable to create directory: ' . $sourceFontsDir);
        }
    }

    if (!\is_dir($customFontsDir)) {
        if (!@\mkdir($customFontsDir, 0755, true) && !\is_dir($customFontsDir)) {
            throw new \RuntimeException('Unable to create directory: ' . $customFontsDir);
        }
    }

    $customFontsReal = (string) \realpath($customFontsDir);
    if (empty($customFontsReal)) {
        throw new \RuntimeException('Unable to resolve custom fonts directory path.');
    }

    $convertedFontPath = $customFontsReal . '/' . $fontFamily . '.json';

    // 1) Download Noto Sans Regular only when needed.
    if (!\is_file($convertedFontPath) && !\is_file($notoSourcePath)) {
        downloadFile($notoUrl, $notoSourcePath, 60);
    }

    // 2) Convert it to tc-lib-pdf-font format when not already converted.
    if (!\is_file($convertedFontPath)) {
        $sourceFontPath = (string) \realpath($notoSourcePath);
        if (empty($sourceFontPath)) {
            throw new \RuntimeException('Unable to resolve downloaded font path.');
        }

        try {
            $import = new \Com\Tecnick\Pdf\Font\Import(
                $sourceFontPath,
                $customFontsReal . '/',
                'TrueTypeUnicode',
                '',
                32,
                3,
                1,
                false,
            );
            $fontFamily = $import->getFontName();
            $convertedFontPath = $customFontsReal . '/' . $fontFamily . '.json';
        } catch (\Throwable $e) {
            $alreadyImported = (\strpos($e->getMessage(), 'already imported:') !== false);
            if ($alreadyImported && \preg_match('/([a-z0-9_\-]+)\.json$/i', $e->getMessage(), $match) === 1) {
                $fontFamily = \strtolower((string) $match[1]);
                $convertedFontPath = $customFontsReal . '/' . $fontFamily . '.json';
            } else {
                throw $e;
            }
        }
    }

    if (!\is_file($convertedFontPath)) {
        throw new \RuntimeException('Converted font definition file not found: ' . $convertedFontPath);
    }
} catch (\Throwable $e) {
    $fontSetupError = $e->getMessage();
}

if (empty($fontSetupError) && !empty($customFontsReal)) {
    // The HTML <pre> tag defaults to courier in this renderer.
    // Copy core courier metrics into the custom font directory when missing.
    $defaultCourier = $defaultFontsDir . '/core/courier.json';
    $customCourier = $customFontsReal . '/courier.json';
    if (\is_file($defaultCourier) && !\is_file($customCourier)) {
        @\copy($defaultCourier, $customCourier);
    }
}

$fontsRoot = (!empty($fontSetupError) || empty($customFontsReal)) ? $defaultFontsDir : $customFontsReal;
\define('K_PATH_FONTS', $fontsRoot);

$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm',
    true,
    true,
    true,
    '',
    null,
);

$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('Nicola Asuni');
$pdf->setSubject('tc-lib-pdf example: 072');
$pdf->setTitle('Import and Use a New Font');
$pdf->setKeywords('TCPDF tc-lib-pdf font import custom ttf otf noto sans');
$pdf->setPDFFilename('072_import_new_font.pdf');

$manualFontFamily = !empty($fontSetupError) ? 'helvetica' : $fontFamily;

$manualInstructions = '<p style="font-family: ' . \htmlspecialchars($manualFontFamily, ENT_QUOTES) . ';">Manual conversion and import commands:</p>'
    . '<p style="font-family: ' . \htmlspecialchars($manualFontFamily, ENT_QUOTES) . ';">Run these commands from the project root:</p>'
    . '<pre style="color:darkgreen;font-family: ' . \htmlspecialchars($manualFontFamily, ENT_QUOTES) . '; font-size: 9pt; line-height: 1.35;">'
    . 'mkdir -p target/fonts/source target/fonts/custom' . "\n\n"
    . 'curl -fL --retry 3 -o target/fonts/source/'
    . \htmlspecialchars($notoFileName, ENT_QUOTES) . ' ' . \htmlspecialchars($notoUrl, ENT_QUOTES) . "\n\n"
    . 'php vendor/tecnickcom/tc-lib-pdf-font/util/convert.php \\' . "\n"
    . '  --outpath=target/fonts/custom \\' . "\n"
    . '  --type=TrueTypeUnicode \\' . "\n"
    . '  --flags=32 \\' . "\n"
    . '  --encoding_id=1 \\' . "\n"
    . '  --fonts=target/fonts/source/' . \htmlspecialchars($notoFileName, ENT_QUOTES)
    . '</pre>'
    . '<p style="font-family: ' . \htmlspecialchars($manualFontFamily, ENT_QUOTES) . ';">Then run this example again; it will load the generated '
    . 'target/fonts/custom/' . \htmlspecialchars($fontFamily, ENT_QUOTES) . '.json.</p>';

if (!empty($fontSetupError)) {
    $baseFont = $pdf->font->insert($pdf->pon, 'helvetica', '', 10);

    $pdf->addPage(['format' => 'A4']);
    $pdf->page->addContent($baseFont['out']);

    $html = '<h1 style="font-family: helvetica;">Font Setup Failed</h1>'
        . '<p style="font-family: helvetica;">The example could not download or convert Noto Sans automatically.</p>'
        . '<p style="font-family: helvetica;"><b>Error:</b> ' . \htmlspecialchars($fontSetupError, ENT_QUOTES) . '</p>'
        . '<p style="font-family: helvetica;">If the environment is offline, run once with internet access or pre-populate:</p>'
        . '<p style="font-family: helvetica;">target/fonts/source/' . \htmlspecialchars($notoFileName, ENT_QUOTES)
        . ' and target/fonts/custom/' . \htmlspecialchars($fontFamily, ENT_QUOTES) . '.json.</p>'
        . '<hr/>'
        . $manualInstructions;

    $pdf->addHTMLCell($html, 15, 20, 180);

    $rawpdf = $pdf->getOutPDFString();
    $pdf->renderPDF($rawpdf);
    return;
}

$notoFont = $pdf->font->insert($pdf->pon, $fontFamily, '', 10);

// 3) Start the PDF page and render content using the imported font.
$pdf->addPage(['format' => 'A4']);
$pdf->page->addContent($notoFont['out']);

$html = '<h1 style="font-family: ' . \htmlspecialchars($fontFamily, ENT_QUOTES) . ';">Custom Font Imported Automatically</h1>'
    . '<p style="font-family: ' . \htmlspecialchars($fontFamily, ENT_QUOTES) . ';">Downloaded from: ' . \htmlspecialchars($notoUrl, ENT_QUOTES) . '</p>'
    . '<p style="font-family: ' . \htmlspecialchars($fontFamily, ENT_QUOTES) . ';">Converted source: target/fonts/source/' . \htmlspecialchars($notoFileName, ENT_QUOTES) . '</p>'
    . '<p style="font-family: ' . \htmlspecialchars($fontFamily, ENT_QUOTES) . ';">Imported family: <b>' . \htmlspecialchars($fontFamily, ENT_QUOTES) . '</b></p>'
    . '<hr/>'
    . '<p style="font-family: ' . \htmlspecialchars($fontFamily, ENT_QUOTES) . '; font-size: 16pt;">'
    . 'The quick brown fox jumps over the lazy dog. 0123456789'
    . '</p>'
    . '<hr/>'
    . $manualInstructions;

$pdf->addHTMLCell($html, 15, 20, 180);

$rawpdf = $pdf->getOutPDFString();
$pdf->renderPDF($rawpdf);
