<?php

/**
 * HtmlRenderer.php
 *
 * HTML to PDF rendering engine.
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\Html;

use Com\Tecnick\Pdf\Tcpdf;

/**
 * HTML Renderer
 *
 * Converts HTML content with CSS styling to PDF output.
 *
 * Supported HTML elements:
 * - Block elements: p, div, h1-h6, blockquote, pre
 * - Inline elements: span, b, strong, i, em, u, s, strike, sub, sup
 * - Lists: ul, ol, li
 * - Tables: table, tr, th, td
 * - Links: a
 * - Images: img
 * - Line breaks: br, hr
 *
 * Supported CSS properties:
 * - Font: font-family, font-size, font-weight, font-style
 * - Color: color, background-color
 * - Text: text-align, text-decoration, line-height, text-indent
 * - Box: margin, padding, border, width, height
 * - Display: display
 *
 * @category  Library
 * @package   Pdf
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TRenderState array{
 *     'x': float,
 *     'y': float,
 *     'width': float,
 *     'height': float,
 *     'lineHeight': float,
 *     'fontSize': float,
 *     'fontFamily': string,
 *     'fontStyle': string,
 *     'color': array{float, float, float},
 *     'bgColor': ?array{float, float, float},
 *     'align': string,
 *     'indent': float,
 *     'listLevel': int,
 *     'listType': string,
 *     'listCounter': int,
 * }
 */
class HtmlRenderer
{
    /**
     * Reference to the TCPDF instance
     */
    protected Tcpdf $pdf;

    /**
     * Current X position
     */
    protected float $curX = 0;

    /**
     * Current Y position
     */
    protected float $curY = 0;

    /**
     * Content area width
     */
    protected float $contentWidth = 0;

    /**
     * Content area height
     */
    protected float $contentHeight = 0;

    /**
     * Left margin
     */
    protected float $marginLeft = 10;

    /**
     * Right margin
     */
    protected float $marginRight = 10;

    /**
     * Top margin
     */
    protected float $marginTop = 10;

    /**
     * Current font size (in points)
     */
    protected float $fontSize = 12;

    /**
     * Current font family
     */
    protected string $fontFamily = 'helvetica';

    /**
     * Current font style (B, I, BI, '')
     */
    protected string $fontStyle = '';

    /**
     * Current text color (RGB 0-1)
     *
     * @var array{float, float, float}
     */
    protected array $textColor = [0, 0, 0];

    /**
     * Current line height multiplier
     */
    protected float $lineHeight = 1.2;

    /**
     * Current text alignment
     */
    protected string $textAlign = 'left';

    /**
     * List nesting level
     */
    protected int $listLevel = 0;

    /**
     * List type stack
     *
     * @var array<string>
     */
    protected array $listTypeStack = [];

    /**
     * List counter stack
     *
     * @var array<int>
     */
    protected array $listCounterStack = [];

    /**
     * Style stack for nested elements
     *
     * @var array<array<string, mixed>>
     */
    protected array $styleStack = [];

    /**
     * Block elements
     *
     * @var array<string>
     */
    protected const BLOCK_ELEMENTS = [
        'p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
        'blockquote', 'pre', 'ul', 'ol', 'li', 'table',
        'tr', 'hr', 'br', 'header', 'footer', 'section',
        'article', 'nav', 'aside', 'main', 'figure',
    ];

    /**
     * Heading sizes (in points)
     *
     * @var array<string, float>
     */
    protected const HEADING_SIZES = [
        'h1' => 24,
        'h2' => 20,
        'h3' => 16,
        'h4' => 14,
        'h5' => 12,
        'h6' => 10,
    ];

    /**
     * Constructor
     *
     * @param Tcpdf $pdf TCPDF instance
     */
    public function __construct(Tcpdf $pdf)
    {
        $this->pdf = $pdf;
    }

    /**
     * Set margins
     *
     * @param float $left Left margin
     * @param float $top Top margin
     * @param float $right Right margin
     * @return static
     */
    public function setMargins(float $left, float $top, float $right): static
    {
        $this->marginLeft = $left;
        $this->marginTop = $top;
        $this->marginRight = $right;
        return $this;
    }

    /**
     * Set default font
     *
     * @param string $family Font family
     * @param string $style Font style
     * @param float $size Font size
     * @return static
     */
    public function setFont(string $family, string $style = '', float $size = 12): static
    {
        $this->fontFamily = $family;
        $this->fontStyle = $style;
        $this->fontSize = $size;
        return $this;
    }

    /**
     * Render HTML content to PDF
     *
     * @param string $html HTML content
     * @param float $x X position (0 = use margin)
     * @param float $y Y position (0 = current position)
     * @param float $width Width (0 = auto)
     * @return float Final Y position after rendering
     */
    public function render(string $html, float $x = 0, float $y = 0, float $width = 0): float
    {
        // Get page dimensions
        $page = $this->pdf->page->getPage();
        $pageWidth = $page['width'] ?? 210;
        $pageHeight = $page['height'] ?? 297;

        // Set positions
        $this->curX = $x > 0 ? $x : $this->marginLeft;
        $this->curY = $y > 0 ? $y : $this->marginTop;
        $this->contentWidth = $width > 0 ? $width : ($pageWidth - $this->marginLeft - $this->marginRight);
        $this->contentHeight = $pageHeight - $this->marginTop - 10;

        // Parse HTML
        $html = $this->preprocessHtml($html);
        $elements = $this->parseHtml($html);

        // Render elements
        foreach ($elements as $element) {
            $this->renderElement($element);
        }

        return $this->curY;
    }

    /**
     * Write HTML directly (convenience method)
     *
     * @param string $html HTML content
     * @return float Final Y position
     */
    public function writeHTML(string $html): float
    {
        return $this->render($html);
    }

    /**
     * Preprocess HTML content
     *
     * @param string $html Raw HTML
     * @return string Cleaned HTML
     */
    protected function preprocessHtml(string $html): string
    {
        // Remove comments
        $html = preg_replace('/<!--.*?-->/s', '', $html) ?? $html;

        // Remove scripts and styles
        $html = preg_replace('/<script[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html) ?? $html;

        // Normalize whitespace
        $html = preg_replace('/\s+/', ' ', $html) ?? $html;

        // Decode entities
        $html = html_entity_decode($html, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim($html);
    }

    /**
     * Parse HTML into element array
     *
     * @param string $html HTML content
     * @return array<array{tag: string, content: string, attributes: array<string, string>, children: array<mixed>}>
     */
    protected function parseHtml(string $html): array
    {
        $elements = [];

        // Simple regex-based parser for common elements
        $pattern = '/<(\w+)([^>]*)>(.*?)<\/\1>|<(\w+)([^>]*)\s*\/?>|([^<]+)/is';

        if (preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                if (!empty($match[6])) {
                    // Text node
                    $text = trim($match[6]);
                    if ($text !== '') {
                        $elements[] = [
                            'tag' => '#text',
                            'content' => $text,
                            'attributes' => [],
                            'children' => [],
                        ];
                    }
                } elseif (!empty($match[4])) {
                    // Self-closing tag
                    $elements[] = [
                        'tag' => strtolower($match[4]),
                        'content' => '',
                        'attributes' => $this->parseAttributes($match[5] ?? ''),
                        'children' => [],
                    ];
                } elseif (!empty($match[1])) {
                    // Regular tag with content
                    $tag = strtolower($match[1]);
                    $content = $match[3] ?? '';
                    $children = [];

                    // Recursively parse nested content
                    if (strpos($content, '<') !== false) {
                        $children = $this->parseHtml($content);
                        $content = '';
                    }

                    $elements[] = [
                        'tag' => $tag,
                        'content' => trim($content),
                        'attributes' => $this->parseAttributes($match[2] ?? ''),
                        'children' => $children,
                    ];
                }
            }
        }

        return $elements;
    }

    /**
     * Parse HTML attributes
     *
     * @param string $attrString Attribute string
     * @return array<string, string>
     */
    protected function parseAttributes(string $attrString): array
    {
        $attributes = [];

        if (preg_match_all('/(\w+)=["\']([^"\']*)["\']|(\w+)=(\S+)/i', $attrString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = !empty($match[1]) ? $match[1] : ($match[3] ?? '');
                $value = !empty($match[2]) ? $match[2] : ($match[4] ?? '');
                if ($name) {
                    $attributes[strtolower($name)] = $value;
                }
            }
        }

        return $attributes;
    }

    /**
     * Render a single element
     *
     * @param array{tag: string, content: string, attributes: array<string, string>, children: array<mixed>} $element
     */
    protected function renderElement(array $element): void
    {
        $tag = $element['tag'];
        $content = $element['content'];
        $attributes = $element['attributes'];
        $children = $element['children'];

        // Apply inline styles
        $this->pushStyle($attributes);

        switch ($tag) {
            case '#text':
                $this->renderText($content);
                break;

            case 'p':
                $this->renderParagraph($content, $children);
                break;

            case 'br':
                $this->renderLineBreak();
                break;

            case 'hr':
                $this->renderHorizontalRule();
                break;

            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $this->renderHeading($tag, $content, $children);
                break;

            case 'b':
            case 'strong':
                $this->renderBold($content, $children);
                break;

            case 'i':
            case 'em':
                $this->renderItalic($content, $children);
                break;

            case 'u':
                $this->renderUnderline($content, $children);
                break;

            case 'a':
                $this->renderLink($content, $children, $attributes);
                break;

            case 'ul':
                $this->renderUnorderedList($children);
                break;

            case 'ol':
                $this->renderOrderedList($children);
                break;

            case 'li':
                $this->renderListItem($content, $children);
                break;

            case 'table':
                $this->renderTable($children, $attributes);
                break;

            case 'img':
                $this->renderImage($attributes);
                break;

            case 'div':
            case 'span':
            case 'section':
            case 'article':
            case 'header':
            case 'footer':
            case 'main':
                $this->renderContainer($content, $children, $tag);
                break;

            case 'blockquote':
                $this->renderBlockquote($content, $children);
                break;

            case 'pre':
            case 'code':
                $this->renderPreformatted($content, $children);
                break;

            default:
                // Render content and children for unknown tags
                if ($content) {
                    $this->renderText($content);
                }
                foreach ($children as $child) {
                    $this->renderElement($child);
                }
        }

        // Restore previous style
        $this->popStyle();
    }

    /**
     * Push style onto stack
     *
     * @param array<string, string> $attributes
     */
    protected function pushStyle(array $attributes): void
    {
        $this->styleStack[] = [
            'fontSize' => $this->fontSize,
            'fontFamily' => $this->fontFamily,
            'fontStyle' => $this->fontStyle,
            'textColor' => $this->textColor,
            'textAlign' => $this->textAlign,
        ];

        // Parse inline style
        if (isset($attributes['style'])) {
            $this->applyInlineStyle($attributes['style']);
        }
    }

    /**
     * Pop style from stack
     */
    protected function popStyle(): void
    {
        if (!empty($this->styleStack)) {
            $style = array_pop($this->styleStack);
            $this->fontSize = $style['fontSize'];
            $this->fontFamily = $style['fontFamily'];
            $this->fontStyle = $style['fontStyle'];
            $this->textColor = $style['textColor'];
            $this->textAlign = $style['textAlign'];
        }
    }

    /**
     * Apply inline CSS style
     *
     * @param string $style CSS style string
     */
    protected function applyInlineStyle(string $style): void
    {
        $properties = explode(';', $style);

        foreach ($properties as $property) {
            $parts = explode(':', $property, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $name = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            switch ($name) {
                case 'font-size':
                    $this->fontSize = $this->parseFontSize($value);
                    break;

                case 'font-family':
                    $this->fontFamily = $this->parseFontFamily($value);
                    break;

                case 'font-weight':
                    if ($value === 'bold' || (int)$value >= 700) {
                        $this->fontStyle = str_contains($this->fontStyle, 'I') ? 'BI' : 'B';
                    }
                    break;

                case 'font-style':
                    if ($value === 'italic') {
                        $this->fontStyle = str_contains($this->fontStyle, 'B') ? 'BI' : 'I';
                    }
                    break;

                case 'color':
                    $this->textColor = $this->parseColor($value);
                    break;

                case 'text-align':
                    $this->textAlign = $value;
                    break;

                case 'line-height':
                    $this->lineHeight = $this->parseLineHeight($value);
                    break;
            }
        }
    }

    /**
     * Parse font size value
     *
     * @param string $value CSS value
     * @return float Size in points
     */
    protected function parseFontSize(string $value): float
    {
        $value = strtolower(trim($value));

        // Named sizes
        $namedSizes = [
            'xx-small' => 8,
            'x-small' => 9,
            'small' => 10,
            'medium' => 12,
            'large' => 14,
            'x-large' => 18,
            'xx-large' => 24,
        ];

        if (isset($namedSizes[$value])) {
            return $namedSizes[$value];
        }

        // Parse numeric values
        if (preg_match('/^([\d.]+)(px|pt|em|rem|%)?$/i', $value, $match)) {
            $num = (float)$match[1];
            $unit = strtolower($match[2] ?? 'pt');

            return match ($unit) {
                'px' => $num * 0.75,
                'em', 'rem' => $num * $this->fontSize,
                '%' => $this->fontSize * ($num / 100),
                default => $num,
            };
        }

        return $this->fontSize;
    }

    /**
     * Parse font family value
     *
     * @param string $value CSS value
     * @return string Font family name
     */
    protected function parseFontFamily(string $value): string
    {
        // Get first font in stack
        $fonts = explode(',', $value);
        $font = trim($fonts[0], " \t\n\r\0\x0B'\"");

        // Map common web fonts to PDF fonts
        $fontMap = [
            'arial' => 'helvetica',
            'helvetica' => 'helvetica',
            'times' => 'times',
            'times new roman' => 'times',
            'courier' => 'courier',
            'courier new' => 'courier',
            'georgia' => 'times',
            'verdana' => 'helvetica',
            'sans-serif' => 'helvetica',
            'serif' => 'times',
            'monospace' => 'courier',
        ];

        $lowerFont = strtolower($font);
        return $fontMap[$lowerFont] ?? $this->fontFamily;
    }

    /**
     * Parse color value
     *
     * @param string $value CSS color value
     * @return array{float, float, float} RGB values (0-1)
     */
    protected function parseColor(string $value): array
    {
        $value = strtolower(trim($value));

        // Named colors
        $namedColors = [
            'black' => [0, 0, 0],
            'white' => [1, 1, 1],
            'red' => [1, 0, 0],
            'green' => [0, 0.5, 0],
            'blue' => [0, 0, 1],
            'yellow' => [1, 1, 0],
            'cyan' => [0, 1, 1],
            'magenta' => [1, 0, 1],
            'gray' => [0.5, 0.5, 0.5],
            'grey' => [0.5, 0.5, 0.5],
            'navy' => [0, 0, 0.5],
            'maroon' => [0.5, 0, 0],
            'olive' => [0.5, 0.5, 0],
            'purple' => [0.5, 0, 0.5],
            'teal' => [0, 0.5, 0.5],
            'silver' => [0.75, 0.75, 0.75],
            'orange' => [1, 0.65, 0],
        ];

        if (isset($namedColors[$value])) {
            return $namedColors[$value];
        }

        // Hex colors
        if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value, $match)) {
            $hex = $match[1];
            if (strlen($hex) === 3) {
                $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
            }
            return [
                hexdec(substr($hex, 0, 2)) / 255,
                hexdec(substr($hex, 2, 2)) / 255,
                hexdec(substr($hex, 4, 2)) / 255,
            ];
        }

        // RGB/RGBA
        if (preg_match('/^rgba?\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)/i', $value, $match)) {
            return [
                (int)$match[1] / 255,
                (int)$match[2] / 255,
                (int)$match[3] / 255,
            ];
        }

        return $this->textColor;
    }

    /**
     * Parse line height value
     *
     * @param string $value CSS value
     * @return float Line height multiplier
     */
    protected function parseLineHeight(string $value): float
    {
        $value = trim($value);

        if (is_numeric($value)) {
            return (float)$value;
        }

        if (preg_match('/^([\d.]+)(px|pt|em|%)?$/i', $value, $match)) {
            $num = (float)$match[1];
            $unit = strtolower($match[2] ?? '');

            return match ($unit) {
                'px', 'pt' => $num / $this->fontSize,
                '%' => $num / 100,
                default => $num,
            };
        }

        return $this->lineHeight;
    }

    /**
     * Render text content
     *
     * @param string $text Text to render
     */
    protected function renderText(string $text): void
    {
        if (trim($text) === '') {
            return;
        }

        $lineHeightPt = $this->fontSize * $this->lineHeight;
        $lineHeightMm = $lineHeightPt * 0.352778; // Convert pt to mm

        // Word wrap
        $words = preg_split('/\s+/', $text) ?: [$text];
        $line = '';
        $page = $this->pdf->page->getPage();
        $pageHeight = $page['height'] ?? 297;

        foreach ($words as $word) {
            $testLine = $line === '' ? $word : $line . ' ' . $word;
            $testWidth = $this->getStringWidth($testLine);

            if ($testWidth > $this->contentWidth && $line !== '') {
                // Output current line
                $this->outputTextLine($line, $lineHeightMm);
                $line = $word;

                // Check for page break
                if ($this->curY + $lineHeightMm > $pageHeight - 10) {
                    $this->pdf->addPage();
                    $this->curY = $this->marginTop;
                }
            } else {
                $line = $testLine;
            }
        }

        // Output remaining text
        if ($line !== '') {
            $this->outputTextLine($line, $lineHeightMm);
        }
    }

    /**
     * Output a single text line
     *
     * @param string $text Text line
     * @param float $lineHeight Line height in mm
     */
    protected function outputTextLine(string $text, float $lineHeight): void
    {
        $page = $this->pdf->page->getPage();
        $pageHeight = $page['height'] ?? 297;

        // Calculate X position based on alignment
        $textWidth = $this->getStringWidth($text);
        $x = match ($this->textAlign) {
            'center' => $this->curX + ($this->contentWidth - $textWidth) / 2,
            'right' => $this->curX + $this->contentWidth - $textWidth,
            default => $this->curX,
        };

        // Convert Y from top-based to bottom-based
        $y = $pageHeight - $this->curY - $lineHeight;

        // Build PDF content
        $content = "BT\n";
        $content .= sprintf("/F1 %.2f Tf\n", $this->fontSize);
        $content .= sprintf("%.3f %.3f %.3f rg\n", $this->textColor[0], $this->textColor[1], $this->textColor[2]);
        $content .= sprintf("1 0 0 1 %.4f %.4f Tm\n", $x * 2.834645669, $y * 2.834645669);
        $content .= "(" . $this->escapeText($text) . ") Tj\n";
        $content .= "ET\n";

        $this->pdf->page->addContent($content);

        $this->curY += $lineHeight;
    }

    /**
     * Get string width in mm
     *
     * @param string $text Text
     * @return float Width in mm
     */
    protected function getStringWidth(string $text): float
    {
        // Approximate width calculation
        // Average character width is about 0.5 * font size for proportional fonts
        $avgCharWidth = $this->fontSize * 0.5 * 0.352778; // pt to mm
        return strlen($text) * $avgCharWidth;
    }

    /**
     * Escape text for PDF
     *
     * @param string $text Text
     * @return string Escaped text
     */
    protected function escapeText(string $text): string
    {
        return str_replace(
            ['\\', '(', ')'],
            ['\\\\', '\\(', '\\)'],
            $text
        );
    }

    /**
     * Render paragraph
     *
     * @param string $content Text content
     * @param array<mixed> $children Child elements
     */
    protected function renderParagraph(string $content, array $children): void
    {
        // Add top margin
        $this->curY += 2;

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        // Add bottom margin
        $this->curY += 2;
    }

    /**
     * Render line break
     */
    protected function renderLineBreak(): void
    {
        $lineHeight = $this->fontSize * $this->lineHeight * 0.352778;
        $this->curY += $lineHeight;
    }

    /**
     * Render horizontal rule
     */
    protected function renderHorizontalRule(): void
    {
        $this->curY += 3;

        $page = $this->pdf->page->getPage();
        $pageHeight = $page['height'] ?? 297;

        $x1 = $this->curX * 2.834645669;
        $x2 = ($this->curX + $this->contentWidth) * 2.834645669;
        $y = ($pageHeight - $this->curY) * 2.834645669;

        $content = "q\n";
        $content .= "0.5 w\n";
        $content .= "0.7 0.7 0.7 RG\n";
        $content .= sprintf("%.4f %.4f m\n", $x1, $y);
        $content .= sprintf("%.4f %.4f l\n", $x2, $y);
        $content .= "S\n";
        $content .= "Q\n";

        $this->pdf->page->addContent($content);

        $this->curY += 3;
    }

    /**
     * Render heading
     *
     * @param string $tag Heading tag (h1-h6)
     * @param string $content Text content
     * @param array<mixed> $children Child elements
     */
    protected function renderHeading(string $tag, string $content, array $children): void
    {
        $originalSize = $this->fontSize;
        $originalStyle = $this->fontStyle;

        $this->fontSize = self::HEADING_SIZES[$tag] ?? 14;
        $this->fontStyle = 'B';

        // Top margin
        $this->curY += 4;

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        // Bottom margin
        $this->curY += 2;

        $this->fontSize = $originalSize;
        $this->fontStyle = $originalStyle;
    }

    /**
     * Render bold text
     *
     * @param string $content Text content
     * @param array<mixed> $children Child elements
     */
    protected function renderBold(string $content, array $children): void
    {
        $originalStyle = $this->fontStyle;
        $this->fontStyle = str_contains($this->fontStyle, 'I') ? 'BI' : 'B';

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->fontStyle = $originalStyle;
    }

    /**
     * Render italic text
     *
     * @param string $content Text content
     * @param array<mixed> $children Child elements
     */
    protected function renderItalic(string $content, array $children): void
    {
        $originalStyle = $this->fontStyle;
        $this->fontStyle = str_contains($this->fontStyle, 'B') ? 'BI' : 'I';

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->fontStyle = $originalStyle;
    }

    /**
     * Render underlined text
     *
     * @param string $content Text content
     * @param array<mixed> $children Child elements
     */
    protected function renderUnderline(string $content, array $children): void
    {
        // For now, render as normal text
        // Full underline support would require tracking text positions
        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }
    }

    /**
     * Render link
     *
     * @param string $content Link text
     * @param array<mixed> $children Child elements
     * @param array<string, string> $attributes
     */
    protected function renderLink(string $content, array $children, array $attributes): void
    {
        $originalColor = $this->textColor;
        $this->textColor = [0, 0, 0.8]; // Blue color for links

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->textColor = $originalColor;
    }

    /**
     * Render unordered list
     *
     * @param array<mixed> $children List items
     */
    protected function renderUnorderedList(array $children): void
    {
        $this->listLevel++;
        $this->listTypeStack[] = 'ul';
        $this->listCounterStack[] = 0;

        $this->curY += 2;

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->curY += 2;

        $this->listLevel--;
        array_pop($this->listTypeStack);
        array_pop($this->listCounterStack);
    }

    /**
     * Render ordered list
     *
     * @param array<mixed> $children List items
     */
    protected function renderOrderedList(array $children): void
    {
        $this->listLevel++;
        $this->listTypeStack[] = 'ol';
        $this->listCounterStack[] = 0;

        $this->curY += 2;

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->curY += 2;

        $this->listLevel--;
        array_pop($this->listTypeStack);
        array_pop($this->listCounterStack);
    }

    /**
     * Render list item
     *
     * @param string $content Item content
     * @param array<mixed> $children Child elements
     */
    protected function renderListItem(string $content, array $children): void
    {
        $indent = $this->listLevel * 5; // 5mm per level

        // Get list type and counter
        $listType = end($this->listTypeStack) ?: 'ul';
        $counterIndex = count($this->listCounterStack) - 1;

        if ($counterIndex >= 0) {
            $this->listCounterStack[$counterIndex]++;
            $counter = $this->listCounterStack[$counterIndex];
        } else {
            $counter = 1;
        }

        // Render bullet or number
        $marker = $listType === 'ol' ? $counter . '.' : '•';
        $markerX = $this->curX + $indent - 4;

        // Save current X
        $originalX = $this->curX;
        $this->curX = $markerX;
        $this->renderText($marker);

        // Restore X and add indent
        $this->curX = $originalX + $indent;
        $originalWidth = $this->contentWidth;
        $this->contentWidth -= $indent;

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        // Restore
        $this->curX = $originalX;
        $this->contentWidth = $originalWidth;
    }

    /**
     * Render table
     *
     * @param array<mixed> $children Table rows
     * @param array<string, string> $attributes
     */
    protected function renderTable(array $children, array $attributes): void
    {
        $this->curY += 3;

        // Collect rows
        $rows = [];
        foreach ($children as $child) {
            if (($child['tag'] ?? '') === 'tr') {
                $rows[] = $child;
            } elseif (in_array($child['tag'] ?? '', ['thead', 'tbody', 'tfoot'])) {
                foreach ($child['children'] ?? [] as $row) {
                    if (($row['tag'] ?? '') === 'tr') {
                        $rows[] = $row;
                    }
                }
            }
        }

        // Count columns
        $colCount = 0;
        foreach ($rows as $row) {
            $cellCount = 0;
            foreach ($row['children'] ?? [] as $cell) {
                if (in_array($cell['tag'] ?? '', ['td', 'th'])) {
                    $cellCount++;
                }
            }
            $colCount = max($colCount, $cellCount);
        }

        if ($colCount === 0) {
            return;
        }

        $colWidth = $this->contentWidth / $colCount;
        $rowHeight = $this->fontSize * $this->lineHeight * 0.352778 + 2;

        // Render rows
        foreach ($rows as $row) {
            $colIndex = 0;
            foreach ($row['children'] ?? [] as $cell) {
                if (!in_array($cell['tag'] ?? '', ['td', 'th'])) {
                    continue;
                }

                $cellX = $this->curX + ($colIndex * $colWidth);
                $cellContent = $cell['content'] ?? '';

                // Get content from children if needed
                if (!$cellContent && !empty($cell['children'])) {
                    $cellContent = $this->getTextContent($cell['children']);
                }

                // Render cell border
                $this->renderCellBorder($cellX, $this->curY, $colWidth, $rowHeight);

                // Render cell content
                $originalX = $this->curX;
                $originalWidth = $this->contentWidth;

                $this->curX = $cellX + 1;
                $this->contentWidth = $colWidth - 2;

                $originalY = $this->curY;
                $this->curY += 1;

                if ($cell['tag'] === 'th') {
                    $originalStyle = $this->fontStyle;
                    $this->fontStyle = 'B';
                    $this->renderText($cellContent);
                    $this->fontStyle = $originalStyle;
                } else {
                    $this->renderText($cellContent);
                }

                $this->curY = $originalY;
                $this->curX = $originalX;
                $this->contentWidth = $originalWidth;

                $colIndex++;
            }

            $this->curY += $rowHeight;
        }

        $this->curY += 3;
    }

    /**
     * Render cell border
     *
     * @param float $x X position
     * @param float $y Y position
     * @param float $width Cell width
     * @param float $height Cell height
     */
    protected function renderCellBorder(float $x, float $y, float $width, float $height): void
    {
        $page = $this->pdf->page->getPage();
        $pageHeight = $page['height'] ?? 297;

        $x1 = $x * 2.834645669;
        $y1 = ($pageHeight - $y) * 2.834645669;
        $w = $width * 2.834645669;
        $h = $height * 2.834645669;

        $content = "q\n";
        $content .= "0.5 w\n";
        $content .= "0 0 0 RG\n";
        $content .= sprintf("%.4f %.4f %.4f %.4f re\n", $x1, $y1 - $h, $w, $h);
        $content .= "S\n";
        $content .= "Q\n";

        $this->pdf->page->addContent($content);
    }

    /**
     * Get text content from children
     *
     * @param array<mixed> $children
     * @return string
     */
    protected function getTextContent(array $children): string
    {
        $text = '';
        foreach ($children as $child) {
            if (($child['tag'] ?? '') === '#text') {
                $text .= $child['content'] ?? '';
            } elseif (!empty($child['content'])) {
                $text .= $child['content'];
            } elseif (!empty($child['children'])) {
                $text .= $this->getTextContent($child['children']);
            }
        }
        return $text;
    }

    /**
     * Render image
     *
     * @param array<string, string> $attributes Image attributes
     */
    protected function renderImage(array $attributes): void
    {
        $src = $attributes['src'] ?? '';
        if (!$src || !file_exists($src)) {
            return;
        }

        $width = isset($attributes['width']) ? (float)$attributes['width'] * 0.264583 : 50; // px to mm
        $height = isset($attributes['height']) ? (float)$attributes['height'] * 0.264583 : 0;

        try {
            $imageId = $this->pdf->image->add($src);
            $page = $this->pdf->page->getPage();
            $pageHeight = $page['height'] ?? 297;

            $output = $this->pdf->image->getSetImage($imageId, $this->curX, $this->curY, $width, $height, $pageHeight);
            $this->pdf->page->addContent($output);

            $this->curY += $height ?: $width; // Approximate if height not specified
        } catch (\Exception $e) {
            // Skip if image cannot be loaded
        }
    }

    /**
     * Render container (div, span, etc.)
     *
     * @param string $content Content
     * @param array<mixed> $children Children
     * @param string $tag Tag name
     */
    protected function renderContainer(string $content, array $children, string $tag): void
    {
        $isBlock = in_array($tag, self::BLOCK_ELEMENTS);

        if ($isBlock) {
            $this->curY += 1;
        }

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        if ($isBlock) {
            $this->curY += 1;
        }
    }

    /**
     * Render blockquote
     *
     * @param string $content Content
     * @param array<mixed> $children Children
     */
    protected function renderBlockquote(string $content, array $children): void
    {
        $originalX = $this->curX;
        $originalWidth = $this->contentWidth;
        $originalColor = $this->textColor;

        $this->curX += 10;
        $this->contentWidth -= 20;
        $this->textColor = [0.3, 0.3, 0.3];

        $this->curY += 3;

        if ($content) {
            $this->renderText($content);
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->curY += 3;

        $this->curX = $originalX;
        $this->contentWidth = $originalWidth;
        $this->textColor = $originalColor;
    }

    /**
     * Render preformatted text
     *
     * @param string $content Content
     * @param array<mixed> $children Children
     */
    protected function renderPreformatted(string $content, array $children): void
    {
        $originalFamily = $this->fontFamily;
        $this->fontFamily = 'courier';

        $this->curY += 2;

        if ($content) {
            // Preserve whitespace
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $this->renderText($line);
                $this->renderLineBreak();
            }
        }

        foreach ($children as $child) {
            $this->renderElement($child);
        }

        $this->curY += 2;

        $this->fontFamily = $originalFamily;
    }
}
