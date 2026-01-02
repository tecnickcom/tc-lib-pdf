<?php

/**
 * RichText.php
 *
 * Rich Text support for PDF annotations and form fields.
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

/**
 * Rich Text helper class
 *
 * Provides methods for creating rich text content (XHTML subset)
 * for use in PDF annotations and form fields.
 *
 * PDF Rich Text uses a subset of XHTML with the following allowed elements:
 * - p, span, br, b, i, font
 * - Supported CSS properties: color, font-size, font-family, font-weight, font-style
 *
 * @since     2025-01-02
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-type TRichTextStyle array{
 *     'font-family'?: string,
 *     'font-size'?: string,
 *     'font-weight'?: string,
 *     'font-style'?: string,
 *     'color'?: string,
 *     'text-decoration'?: string,
 *     'text-align'?: string,
 * }
 */
class RichText
{
    /**
     * Default font family
     */
    protected string $fontFamily = 'Helvetica';

    /**
     * Default font size (in points)
     */
    protected float $fontSize = 12;

    /**
     * Default text color (hex)
     */
    protected string $textColor = '#000000';

    /**
     * Build rich text content parts
     *
     * @var array<string>
     */
    protected array $parts = [];

    /**
     * Constructor
     *
     * @param string $fontFamily Default font family
     * @param float $fontSize Default font size in points
     * @param string $textColor Default text color (hex format)
     */
    public function __construct(
        string $fontFamily = 'Helvetica',
        float $fontSize = 12,
        string $textColor = '#000000'
    ) {
        $this->fontFamily = $fontFamily;
        $this->fontSize = $fontSize;
        $this->textColor = $textColor;
    }

    /**
     * Add plain text
     *
     * @param string $text Text content
     * @return self
     */
    public function addText(string $text): self
    {
        $this->parts[] = $this->escapeXml($text);
        return $this;
    }

    /**
     * Add bold text
     *
     * @param string $text Text content
     * @return self
     */
    public function addBold(string $text): self
    {
        $this->parts[] = '<b>' . $this->escapeXml($text) . '</b>';
        return $this;
    }

    /**
     * Add italic text
     *
     * @param string $text Text content
     * @return self
     */
    public function addItalic(string $text): self
    {
        $this->parts[] = '<i>' . $this->escapeXml($text) . '</i>';
        return $this;
    }

    /**
     * Add bold italic text
     *
     * @param string $text Text content
     * @return self
     */
    public function addBoldItalic(string $text): self
    {
        $this->parts[] = '<b><i>' . $this->escapeXml($text) . '</i></b>';
        return $this;
    }

    /**
     * Add underlined text
     *
     * @param string $text Text content
     * @return self
     */
    public function addUnderline(string $text): self
    {
        $this->parts[] = '<span style="text-decoration:underline">' . $this->escapeXml($text) . '</span>';
        return $this;
    }

    /**
     * Add styled text with custom formatting
     *
     * @param string $text Text content
     * @param TRichTextStyle $style Style options
     * @return self
     */
    public function addStyled(string $text, array $style): self
    {
        $cssStyle = $this->buildCssStyle($style);
        if ($cssStyle !== '') {
            $this->parts[] = '<span style="' . $cssStyle . '">' . $this->escapeXml($text) . '</span>';
        } else {
            $this->parts[] = $this->escapeXml($text);
        }
        return $this;
    }

    /**
     * Add colored text
     *
     * @param string $text Text content
     * @param string $color Color in hex format (#RRGGBB) or rgb format
     * @return self
     */
    public function addColored(string $text, string $color): self
    {
        $rgbColor = $this->hexToRgb($color);
        $this->parts[] = '<span style="color:' . $rgbColor . '">' . $this->escapeXml($text) . '</span>';
        return $this;
    }

    /**
     * Add text with custom font size
     *
     * @param string $text Text content
     * @param float $size Font size in points
     * @return self
     */
    public function addSized(string $text, float $size): self
    {
        $this->parts[] = '<span style="font-size:' . $size . 'pt">' . $this->escapeXml($text) . '</span>';
        return $this;
    }

    /**
     * Add a line break
     *
     * @return self
     */
    public function addLineBreak(): self
    {
        $this->parts[] = '<br/>';
        return $this;
    }

    /**
     * Add a paragraph
     *
     * @param string $text Paragraph text
     * @param string $align Alignment (left, center, right, justify)
     * @return self
     */
    public function addParagraph(string $text, string $align = 'left'): self
    {
        $style = 'text-align:' . $align;
        $this->parts[] = '<p style="' . $style . '">' . $this->escapeXml($text) . '</p>';
        return $this;
    }

    /**
     * Start a paragraph block
     *
     * @param string $align Alignment (left, center, right, justify)
     * @return self
     */
    public function startParagraph(string $align = 'left'): self
    {
        $style = 'text-align:' . $align;
        $this->parts[] = '<p style="' . $style . '">';
        return $this;
    }

    /**
     * End a paragraph block
     *
     * @return self
     */
    public function endParagraph(): self
    {
        $this->parts[] = '</p>';
        return $this;
    }

    /**
     * Get the rich text content as XHTML string
     *
     * @param bool $wrapBody Whether to wrap in body/html tags
     * @return string Rich text XHTML content
     */
    public function getContent(bool $wrapBody = true): string
    {
        $content = implode('', $this->parts);

        if ($wrapBody) {
            $defaultStyle = sprintf(
                'font-family:%s;font-size:%spt;color:%s',
                $this->fontFamily,
                $this->fontSize,
                $this->hexToRgb($this->textColor)
            );

            return '<?xml version="1.0"?>'
                . '<body xmlns="http://www.w3.org/1999/xhtml" '
                . 'xmlns:xfa="http://www.xfa.org/schema/xfa-data/1.0/" '
                . 'xfa:contentType="text/html" '
                . 'xfa:APIVersion="Acrobat:23.0.0" '
                . 'style="' . $defaultStyle . '">'
                . $content
                . '</body>';
        }

        return $content;
    }

    /**
     * Get content and reset the builder
     *
     * @param bool $wrapBody Whether to wrap in body/html tags
     * @return string Rich text XHTML content
     */
    public function build(bool $wrapBody = true): string
    {
        $content = $this->getContent($wrapBody);
        $this->clear();
        return $content;
    }

    /**
     * Clear all parts
     *
     * @return self
     */
    public function clear(): self
    {
        $this->parts = [];
        return $this;
    }

    /**
     * Create a rich text string from simple HTML-like markup
     *
     * Supported tags: <b>, <i>, <u>, <br>, <p>, <span style="...">
     *
     * @param string $markup Simple markup text
     * @return string Rich text XHTML content
     */
    public function fromMarkup(string $markup): string
    {
        // Convert simple markup to proper XHTML
        $content = $markup;

        // Ensure self-closing tags are valid XML
        $content = preg_replace('/<br\s*\/?>/', '<br/>', $content) ?? $content;

        // Wrap content
        $defaultStyle = sprintf(
            'font-family:%s;font-size:%spt;color:%s',
            $this->fontFamily,
            $this->fontSize,
            $this->hexToRgb($this->textColor)
        );

        return '<?xml version="1.0"?>'
            . '<body xmlns="http://www.w3.org/1999/xhtml" '
            . 'xmlns:xfa="http://www.xfa.org/schema/xfa-data/1.0/" '
            . 'xfa:contentType="text/html" '
            . 'xfa:APIVersion="Acrobat:23.0.0" '
            . 'style="' . $defaultStyle . '">'
            . $content
            . '</body>';
    }

    /**
     * Escape XML special characters
     *
     * @param string $text Text to escape
     * @return string Escaped text
     */
    protected function escapeXml(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /**
     * Build CSS style string from style array
     *
     * @param TRichTextStyle $style Style options
     * @return string CSS style string
     */
    protected function buildCssStyle(array $style): string
    {
        $css = [];

        if (isset($style['font-family'])) {
            $css[] = 'font-family:' . $style['font-family'];
        }

        if (isset($style['font-size'])) {
            $css[] = 'font-size:' . $style['font-size'];
        }

        if (isset($style['font-weight'])) {
            $css[] = 'font-weight:' . $style['font-weight'];
        }

        if (isset($style['font-style'])) {
            $css[] = 'font-style:' . $style['font-style'];
        }

        if (isset($style['color'])) {
            $css[] = 'color:' . $this->hexToRgb($style['color']);
        }

        if (isset($style['text-decoration'])) {
            $css[] = 'text-decoration:' . $style['text-decoration'];
        }

        if (isset($style['text-align'])) {
            $css[] = 'text-align:' . $style['text-align'];
        }

        return implode(';', $css);
    }

    /**
     * Convert hex color to rgb() format for CSS
     *
     * @param string $color Color in hex format (#RRGGBB or #RGB)
     * @return string Color in rgb(r,g,b) format
     */
    protected function hexToRgb(string $color): string
    {
        // Already in rgb format
        if (str_starts_with($color, 'rgb')) {
            return $color;
        }

        // Remove # prefix
        $hex = ltrim($color, '#');

        // Handle 3-digit hex
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        // Parse hex values
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return sprintf('rgb(%d,%d,%d)', $r, $g, $b);
    }

    /**
     * Create a Default Appearance string (DA) for PDF annotations
     *
     * @param string $fontKey Font key (e.g., 'F1', 'Helv')
     * @param float $fontSize Font size in points
     * @param string $color Text color (hex format)
     * @return string DA string
     */
    public static function createDA(
        string $fontKey,
        float $fontSize = 12,
        string $color = '#000000'
    ): string {
        // Parse color
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2)) / 255;
        $g = hexdec(substr($hex, 2, 2)) / 255;
        $b = hexdec(substr($hex, 4, 2)) / 255;

        // DA format: "/FontKey fontSize Tf r g b rg"
        return sprintf(
            '/%s %.1f Tf %.3f %.3f %.3f rg',
            $fontKey,
            $fontSize,
            $r,
            $g,
            $b
        );
    }

    /**
     * Create a Default Style string (DS) for PDF annotations
     *
     * @param string $fontFamily Font family name
     * @param float $fontSize Font size in points
     * @param string $color Text color (hex format)
     * @param string $align Text alignment (left, center, right)
     * @return string DS string
     */
    public static function createDS(
        string $fontFamily = 'Helvetica',
        float $fontSize = 12,
        string $color = '#000000',
        string $align = 'left'
    ): string {
        // Parse color
        $hex = ltrim($color, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return sprintf(
            'font: %spt %s; color:rgb(%d,%d,%d); text-align:%s',
            $fontSize,
            $fontFamily,
            $r,
            $g,
            $b,
            $align
        );
    }
}
