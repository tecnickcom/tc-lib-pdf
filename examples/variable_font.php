<?php
/**
 * variable_font.php
 *
 * Example demonstrating OpenType Variable Font analysis.
 *
 * @since       2025-01-02
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');

use Com\Tecnick\Pdf\Font\VariableFont;

echo "OpenType Variable Font Analysis Example\n";
echo "=======================================\n\n";

// =========================================================================
// Example 1: Variable Font Constants
// =========================================================================

echo "1. Standard Variation Axes\n";
echo "--------------------------\n";
echo "Axis tags:\n";
echo "  - wght (Weight): " . VariableFont::AXIS_WEIGHT . "\n";
echo "  - wdth (Width): " . VariableFont::AXIS_WIDTH . "\n";
echo "  - slnt (Slant): " . VariableFont::AXIS_SLANT . "\n";
echo "  - ital (Italic): " . VariableFont::AXIS_ITALIC . "\n";
echo "  - opsz (Optical Size): " . VariableFont::AXIS_OPTICAL_SIZE . "\n\n";

echo "Standard weight values:\n";
echo "  - Thin: " . VariableFont::WEIGHT_THIN . "\n";
echo "  - Light: " . VariableFont::WEIGHT_LIGHT . "\n";
echo "  - Regular: " . VariableFont::WEIGHT_REGULAR . "\n";
echo "  - Medium: " . VariableFont::WEIGHT_MEDIUM . "\n";
echo "  - Semi-Bold: " . VariableFont::WEIGHT_SEMI_BOLD . "\n";
echo "  - Bold: " . VariableFont::WEIGHT_BOLD . "\n";
echo "  - Black: " . VariableFont::WEIGHT_BLACK . "\n\n";

echo "Standard width values:\n";
echo "  - Condensed: " . VariableFont::WIDTH_CONDENSED . "\n";
echo "  - Normal: " . VariableFont::WIDTH_NORMAL . "\n";
echo "  - Expanded: " . VariableFont::WIDTH_EXPANDED . "\n\n";

// =========================================================================
// Example 2: Analyze a Font File
// =========================================================================

echo "2. Analyzing Font Files\n";
echo "-----------------------\n";

// Try to find a variable font in the system
$fontPaths = [
    '/System/Library/Fonts/SFNS.ttf',                    // macOS
    '/System/Library/Fonts/SFNSText.ttf',                // macOS
    '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',   // Linux
    'C:/Windows/Fonts/segoeui.ttf',                      // Windows
    __DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts/helvetica.afm', // Example fonts
];

$fontPath = null;
foreach ($fontPaths as $path) {
    if (file_exists($path)) {
        $fontPath = $path;
        break;
    }
}

if ($fontPath !== null) {
    echo "Found font: $fontPath\n";

    $vf = new VariableFont($fontPath);

    if ($vf->isVariableFont()) {
        echo "This IS a variable font!\n\n";

        $axes = $vf->getAxes();
        echo "Variation axes:\n";
        foreach ($axes as $tag => $axis) {
            echo sprintf(
                "  %s (%s): %.1f - %.1f (default: %.1f)\n",
                $axis['name'],
                $tag,
                $axis['minValue'],
                $axis['maxValue'],
                $axis['defaultValue']
            );
        }

        $instances = $vf->getInstances();
        if (!empty($instances)) {
            echo "\nNamed instances:\n";
            foreach ($instances as $name => $instance) {
                echo "  - $name: " . json_encode($instance['coordinates']) . "\n";
            }
        }
    } else {
        echo "This is NOT a variable font (no fvar table).\n";
        echo "Variable fonts contain multiple styles in one file.\n";
    }
} else {
    echo "No test font file found. Demonstrating API usage...\n";
}

echo "\n";

// =========================================================================
// Example 3: Setting Axis Values
// =========================================================================

echo "3. Setting Axis Values (API Demo)\n";
echo "---------------------------------\n";

$vf = new VariableFont();

// Demonstrate the fluent API
echo "Setting variation values:\n";
echo "  \$vf->setWeight(600);   // Semi-Bold\n";
echo "  \$vf->setWidth(75);     // Condensed\n";
echo "  \$vf->setSlant(-12);    // 12 degree oblique\n\n";

echo "Or set multiple values at once:\n";
echo "  \$vf->setAxisValues([\n";
echo "      'wght' => 700,\n";
echo "      'wdth' => 100,\n";
echo "      'slnt' => 0,\n";
echo "  ]);\n\n";

// =========================================================================
// Example 4: CSS Integration
// =========================================================================

echo "4. CSS font-variation-settings\n";
echo "------------------------------\n";
echo "For web/CSS integration, get the variation settings string:\n";
echo "  \$settings = \$vf->getVariationSettings();\n";
echo "  // Returns: 'wght' 600, 'wdth' 75, 'slnt' -12\n\n";

echo "Use in CSS:\n";
echo "  font-variation-settings: 'wght' 600, 'wdth' 75;\n\n";

// =========================================================================
// Example 5: Using with TCPDF
// =========================================================================

echo "5. Integration with TCPDF\n";
echo "-------------------------\n";
echo "Usage in TCPDF:\n\n";

echo "  \$pdf = new Tcpdf('mm');\n";
echo "  \n";
echo "  // Analyze a variable font\n";
echo "  \$vf = \$pdf->analyzeVariableFont('/path/to/font.ttf');\n";
echo "  \n";
echo "  if (\$vf->isVariableFont()) {\n";
echo "      // Get font info\n";
echo "      \$info = \$pdf->getVariableFontInfo('/path/to/font.ttf');\n";
echo "      \n";
echo "      // List axes\n";
echo "      foreach (\$info['axes'] as \$tag => \$axis) {\n";
echo "          echo \"{$axis['name']}: {\$axis['minValue']} - {\$axis['maxValue']}\\n\";\n";
echo "      }\n";
echo "  }\n\n";

// =========================================================================
// Important Notes
// =========================================================================

echo "Important Notes\n";
echo "---------------\n";
echo "1. Variable font RENDERING in PDFs requires PDF 2.0 support\n";
echo "2. Most PDF viewers only support static font instances\n";
echo "3. For maximum compatibility, use static fonts or named instances\n";
echo "4. The VariableFont class is primarily for font ANALYSIS\n";
echo "5. Full variable font embedding requires tc-lib-pdf-font updates\n\n";

echo "Supported Standard Axes:\n";
echo "  - wght (Weight): Control stroke thickness\n";
echo "  - wdth (Width): Control character width\n";
echo "  - slnt (Slant): Control oblique angle\n";
echo "  - ital (Italic): Switch between upright and italic\n";
echo "  - opsz (Optical Size): Optimize for display size\n\n";

echo "Popular Variable Fonts:\n";
echo "  - Inter (wght)\n";
echo "  - Roboto Flex (wght, wdth, slnt, opsz)\n";
echo "  - Source Sans Variable (wght)\n";
echo "  - IBM Plex Sans Var (wght, wdth)\n";
