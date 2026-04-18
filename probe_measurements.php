<?php
/**
 * Probe to measure exact word widths and test wrapping logic.
 * This is a minimal implementation that mirrors the example structure.
 */

require(__DIR__ . '/vendor/autoload.php');

\define('K_PATH_FONTS', \realpath(__DIR__ . '/vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

class ProbeHTML extends \Com\Tecnick\Pdf\Tcpdf
{
    public function probe()
    {
        // Initialize PDF
        $this->setCreator('Probe');
        $this->setPDFFilename('probe_measurements.pdf');
        $this->enableDefaultPageContent();

        // Add page and set font (must insert font first via font->insert)
        $this->font->insert($this->pon, 'helvetica', '', 10);
        $page = $this->addPage();
        $this->setFont('helvetica', '', 10);

        $page_width = $this->getPageWidth();
        $margin_left = $this->getMarginLeft();
        $margin_right = $this->getMarginRight();
        $available_width = $page_width - $margin_left - $margin_right;

        echo "\n╔════════════════════════════════════════════════════════╗\n";
        echo "║  Width Probe - Kilo Wrapping Analysis                   ║\n";
        echo "╚════════════════════════════════════════════════════════╝\n\n";

        printf("Page width:         %.6f mm\n", $page_width);
        printf("Margin left:        %.6f mm\n", $margin_left);
        printf("Margin right:       %.6f mm\n", $margin_right);
        printf("Available width:    %.6f mm\n\n", $available_width);

        // Test with normal font and italic
        $words = [
            'RIGHT:' => 'normal',
            ' ' => 'space',
            'Alfa' => 'normal',
            ' ' => 'space',
            'Bravo' => 'italic',
            ' ' => 'space',
            'Charlie' => 'normal',
            ' ' => 'space',
            'Delta' => 'italic',
            ' ' => 'space',
            'Echo' => 'normal',
            ' ' => 'space',
            'Foxtrot' => 'italic',
            ' ' => 'space',
            'Golf' => 'normal',
            ' ' => 'space',
            'Hotel' => 'italic',
            ' ' => 'space',
            'India' => 'normal',
            ' ' => 'space',
            'Juliett' => 'italic',
            ' ' => 'space',
            'Kilo' => 'normal',
            ' ' => 'space',
            'Lima' => 'italic',
            ' ' => 'space',
            'Mike' => 'normal',
            ' ' => 'space',
            'November' => 'italic',
        ];

        echo "Word Widths:\n";
        echo str_pad("Word", 15) . str_pad("Width (mm)", 15) . str_pad("Cumulative", 15) . str_pad("Excess", 15) . "\n";
        echo str_repeat("-", 60) . "\n";

        $cumulative = 0;
        $kilo_line_index = -1;
        $kilo_exceeded_at = 0;

        foreach ($words as $word => $style) {
            $html_word = ($style === 'italic') ? '<i>' . $word . '</i>' : $word;
            $width = $this->getStringWidth($html_word);
            $cumulative += $width;
            $excess = $cumulative - $available_width;

            $marker = '';
            if ($word === 'Kilo') {
                $kilo_line_index = count($words);
                if ($excess > 0.01) {
                    $marker = ' ⚠ EXCEEDS + 0.01';
                    $kilo_exceeded_at = $excess;
                } elseif ($excess > 0.001) {
                    $marker = ' ⚠ EXCEEDS + 0.001';
                    $kilo_exceeded_at = $excess;
                }
            }

            $style_label = ($style === 'space') ? 'sp' : $style;
            printf(
                "%-15s  %.6f  %.6f  %+.6f%s\n",
                '"' . $word . '"(' . $style_label . ')',
                $width,
                $cumulative,
                $excess,
                $marker
            );
        }

        echo str_repeat("-", 60) . "\n";
        printf("Final cumulative: %.6f mm\n", $cumulative);
        printf("Final excess:     %+.6f mm\n", $cumulative - $available_width);

        if ($kilo_exceeded_at !== 0) {
            echo "\n⚠ KILO WRAPS with excess: " . $kilo_exceeded_at . " mm\n";
            echo "WIDTH_TOLERANCE = 0.01 mm\n";
            if ($kilo_exceeded_at > 0.01) {
                echo "Verdict: Kilo SHOULD wrap (exceeds tolerance)\n";
            } else {
                echo "Verdict: Kilo should NOT wrap (within tolerance)\n";
            }
        } else {
            echo "\n✓ Kilo fits within tolerance\n";
        }
    }
}

$pdf = new ProbeHTML();
$pdf->probe();

$pdf->output(__DIR__ . '/probe_measurements.pdf', 'F');
echo "\n✓ PDF saved to probe_measurements.pdf\n";
