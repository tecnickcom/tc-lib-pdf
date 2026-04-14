<?php

/**
 * HTML.php
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\HTML
 *
 * HTML PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type BorderStyle from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCSSData from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Cell
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-type THTMLTableCell array{cellx: float, cellw: float, contenth: float, bstyles: array<int|string, BorderStyle>, fillstyle: ?BorderStyle, buffer: string}
 * @phpstan-type THTMLTableRowspanCell array{cellx: float, cellw: float, rowtop: float, rowsremaining: int, usedheight: float, contenth: float, bstyles: array<int|string, BorderStyle>, fillstyle: ?BorderStyle, buffer: string}
 * @phpstan-type THTMLTableState array{originx: float, originy: float, width: float, cols: int, colwidth: float, colwidths: array<int, float>, cellspacing: float, cellpadding: float, rowtop: float, rowheight: float, colindex: int, cells: array<int, THTMLTableCell>, occupied: array<int, int>, rowspans: array<int, THTMLTableRowspanCell>}
 * @phpstan-type THTMLTableCellContext array{originx: float, originy: float, maxwidth: float, maxheight: float, rowtop: float, cellx: float, cellw: float, bstyles: array<int|string, BorderStyle>, fillstyle: ?BorderStyle, rowspan: int, buffer: string}
 *
 * @phpstan-type THTMLAttrib array{
 *     'align': string,
 *     'attribute': array<string, string>,
 *     'bgcolor': string,
 *     'block': bool,
 *     'border': array<string, BorderStyle>,
 *     'clip': bool,
 *     'cols': int,
 *     'content': string,
 *     'cssdata': array<string, TCSSData>,
 *     'csssel': array<string>,
 *     'dir': string,
 *     'elkey': int,
 *     'fgcolor': string,
 *     'fill': bool,
 *     'font-stretch': float,
 *     'fontname': string,
 *     'fontsize': float,
 *     'fontstyle': string,
 *     'height': float,
 *     'hide': bool,
 *     'letter-spacing': float,
 *     'line-height': float,
 *     'listtype': string,
 *     'margin': TCellBound,
 *     'opening': bool,
 *     'padding': TCellBound,
 *     'parent': int,
 *     'rows': int,
 *     'self': bool,
 *     'stroke': float,
 *     'strokecolor': string,
 *     'style': array<string, string>,
 *     'tag': bool,
 *     'text-indent': float,
 *     'text-transform': string,
 *     'thead': string,
 *     'trids': array<int>,
 *     'value': string,
 *     'width': float,
 *     'x': float,
 *     'y': float,
 * }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class HTML extends \Com\Tecnick\Pdf\JavaScript
{
    /**
     * Valid bullet types for list-items
     *
     * @var array<string>
     */
    protected const LIST_SYMBOL = [
        '!',
        '#',
        '1',
        'A',
        'I',
        'a',
        'circle',
        'decimal',
        'decimal-leading-zero',
        'disc',
        'i',
        'lower-alpha',
        'lower-greek',
        'lower-latin',
        'lower-roman',
        'square',
        'upper-alpha',
        'upper-latin',
        'upper-roman',
    ];

    /**
     * Default list types for unordered lists.
     *
     * @var array<string>
     */
    protected const LIST_DEF_ULTYPE = [
        'disc',
        'circle',
        'square',
    ];

    /**
     * HTML block tags.
     *
     * @var array<string>
     */
    protected const HTML_BLOCK_TAGS = [
        'blockquote',
        'br',
        'dd',
        'div',
        'dl',
        'dt',
        'h1',
        'h2',
        'h3',
        'h4',
        'h5',
        'h6',
        'hr',
        'li',
        'ol',
        'p',
        'pre',
        'table',
        'tcpdf',
        'td',
        'tr',
        'ul',
    ];

    /**
     * HTML self-closing tags.
     *
     * @var array<string>
     */
    protected const HTML_SELF_CLOSING_TAGS = [
        'area',
        'base',
        'basefont',
        'br',
        'hr',
        'img',
        'input',
        'link',
        'meta',
    ];

    /**
     * HTML character replacements.
     *
     * @var array<string, string>
     */
    protected const HTML_REPLACEMENT_CHARS = [
        "\t" => ' ',
        "\0" => ' ',
        "\x0B" => ' ',
        "\\" => "\\\\",
    ];

    /**
     * HTML text transform cases.
     *
     * @var array<string, int>
     */
    protected const HTML_TEXT_TRANSFORM = [
        'capitalize' => \MB_CASE_TITLE,
        'uppercase' => \MB_CASE_UPPER,
        'lowercase' => \MB_CASE_LOWER,
    ];

    /**
     * HTML valid (supported) tags.
     *
     * @var string
     */
    protected const HTML_VALID_TAGS = '<marker/><a><b><blockquote><body><br><br/><dd><del><div><dl><dt><em><font><form><h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label><li><ol><option><p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead><tcpdf><td><textarea><th><thead><tr><tt><u><ul>';

    /**
     * HTML generic regexp tag pattern.
     *
     * @var string
     */
    protected const HTML_TAG_PATTERN = '/(<[^>]+>)/';

    /**
     * List of HTML inheritable properties.
     *
     * @var array<string>
     */
    protected const HTML_INHPROP = [
        'align',
        //'azimuth',//
        'bgcolor',
        'block',
        //'border-collapse',//
        //'border-spacing',//
        'border',
        //'caption-side',//
        'clip',
        //'color',//
        //'cursor',//
        'dir',
        //'direction',//
        //'empty-cells',//
        'fgcolor',
        'fill',
        //'font-family',//
        //'font-size-adjust',//
        //'font-size',//
        'font-stretch',
        //'font-stretch',//
        //'font-style',//
        //'font-variant',//
        //'font-weight',//
        //'font',//
        'fontname',
        'fontsize',
        'fontstyle',
        'hide',
        'letter-spacing',
        'line-height',
        //'list-style-image',//
        //'list-style-position',//
        //'list-style-type',//
        //'list-style',//
        'listtype',
        //'orphans',//
        //'page-break-inside',//
        //'page',//
        'parent',
        //'quotes',//
        //'speak-header',//
        //'speak',//
        'stroke',
        'strokecolor',
        'tag',
        //'text-align',//
        'text-indent',
        'text-transform',
        'value',
        //'volume',//
        //'white-space',//
        //'widows',//
        //'word-spacing',//
    ];

    /**
     * Typoe of symbol used for HTML unordered list items.
     *
     * @var string
     */
    protected string $ullidot = '!';

    /**
     * Temporary HTML cell rendering context.
     *
     * @var array{originx: float, originy: float, maxwidth: float, maxheight: float, basefont: string}
     */
    protected array $htmlcellctx = [
        'originx' => 0.0,
        'originy' => 0.0,
        'maxwidth' => 0.0,
        'maxheight' => 0.0,
        'basefont' => 'helvetica',
    ];

    /**
     * Per-cell cache for resolved font metrics keyed by DOM font tuple.
     *
     * @var array<string, array<string, mixed>>
     */
    protected array $htmlfontcache = [];

    /**
     * Per-cell list stack used to render nested UL/OL items.
     *
     * @var array<int, array{ordered: bool, type: string, count: int}>
     */
    protected array $htmlliststack = [];

    /**
     * Per-cell table stack used to render nested HTML tables.
     *
    * @var array<int, THTMLTableState>
     */
    protected array $htmltablestack = [];

    /**
     * Temporary stack to restore outer HTML cell context when leaving table cells.
     *
    * @var array<int, THTMLTableCellContext>
     */
    protected array $htblcellctx = [];

    /**
     * Per-cell stack of active anchor links.
     *
     * @var array<int, string>
     */
    protected array $htmllinkstack = [];

    /**
     * Stack used to save/restore originx and maxwidth when entering/leaving list items,
     * so that line-breaks (<br />) inside a <li> reset to the item's indented origin.
     *
     * @var array<int, array{originx: float, maxwidth: float}>
     */
    protected array $htmllistack = [];

    /**
     * Nesting level of preformatted blocks.
     */
    protected int $htmlprelevel = 0;

    /**
     * Per-column content widths pre-computed for the next table to be opened.
     *
     * @var array<int, float>
     */
    protected array $htmlpendingcolwidths = [];

    /**
     * Horizontal cellspacing pre-computed for the next table to be opened.
     */
    protected float $htmlpndcellspacing = 0.0;

    /**
     * Default cell padding pre-computed for the next table to be opened.
     */
    protected float $htmlpndcellpadding = 0.0;

    /**
     * Custom vertical spacing overrides for HTML block tags.
     * Structure: [ tagname => [ 0 => ['h' => float, 'n' => int], 1 => ['h' => float, 'n' => int] ] ]
     * Index 0 = before-open spacing, index 1 = after-close spacing.
     *
     * @var array<string, array<int, array{h?: float|int, n?: int}>>
     */
    protected array $tagvspaces = [];

    /**
     * Cleanup HTML code (requires HTML Tidy library).
     *
     * @param string $html htmlcode to fix.
     * @param string $defcss CSS to add.
     *
     * @return string XHTML code cleaned up.
     */
    public function tidyHTML(
        string $html,
        string $defcss,
    ): string {
        $tidyopts = [
            'clean' => 1,
            'drop-empty-paras' => 0,
            'drop-proprietary-attributes' => 1,
            'fix-backslash' => 1,
            'hide-comments' => 1,
            'join-styles' => 1,
            'lower-literals' => 1,
            'merge-divs' => 1,
            'merge-spans' => 1,
            'output-xhtml' => 1,
            'word-2000' => 1,
            'wrap' => 0,
            'output-bom' => 0,
        ];
        // clean up the HTML code
        $tidy = \tidy_parse_string($html, $tidyopts);
        if ($tidy === false) {
            throw new PdfException('Unable to tidy the HTML');
        }
        // fix the HTML
        $tidy->cleanRepair();
        // get the CSS part
        $headnode = \tidy_get_head($tidy);
        $css = empty($headnode) ? '' : $headnode->value;
        $css = \preg_replace('/<style([^>]+)>/ims', '<style>', $css) ?? '';
        $css = \preg_replace('/<\/style>(.*)<style>/ims', "\n", $css) ?? '';
        $css = \str_replace('/*<![CDATA[*/', '', $css);
        $css = \str_replace('/*]]>*/', '', $css);
        if (\preg_match('/<style>(.*)<\/style>/ims', $css, $matches) > 0) {
            $css = empty($matches[1]) ? '' : \strtolower($matches[1]);
        } else {
            $css = '';
        }
        // get the body part
        $bodynode = \tidy_get_body($tidy);
        $body = empty($bodynode) ? '' : $bodynode->value;
        // fix some self-closing tags
        $body = \str_replace('<br>', '<br />', $body);
        // remove some empty tag blocks
        $body = \preg_replace('/<div([^\>]*)><\/div>/', '', $body) ?? '';
        $body = \preg_replace('/<p([^\>]*)><\/p>/', '', $body) ?? '';
        // return the cleaned XHTML code with CSS
        return '<style>' . $defcss . $css . '</style>' . $body;
    }

    /**
     * Left trim the input string.
     *
     * @param string $str string to trim.
     * @param string $replace string that replace spaces.
     *
     * @return string left trimmed string.
     */
    public function strTrimLeft($str, $replace = ''): string
    {
        return \preg_replace(
            '/^' . $this->spaceregexp['p'] . '+/' . $this->spaceregexp['m'],
            $replace,
            $str,
        ) ?? '';
    }

    /**
     * Right trim the input string.
     *
     * @param string $str string to trim.
     * @param string $replace string that replace spaces.
     *
     * @return string right trimmed string.
     */
    public function strTrimRight($str, $replace = ''): string
    {
        return \preg_replace(
            '/' . $this->spaceregexp['p'] . '+$/' . $this->spaceregexp['m'],
            $replace,
            $str,
        ) ?? '';
    }

    /**
     * Trim the input string.
     *
     * @param string $str string to trim.
     * @param string $replace string that replace spaces.
     *
     * @return string trimmed string.
     */
    public function strTrim($str, $replace = '')
    {
        $str = $this->strTrimLeft($str, $replace);
        $str = $this->strTrimRight($str, $replace);
        return $str;
    }

    /**
     * Sanitize the HTML code for getHTMLDOM().
     *
     * @param string $html HTML code to parse.
     *
     * @return string
     */
    protected function sanitizeHTML(string $html): string
    {
        // remove head and style blocks
        $html = \preg_replace('/<head([^\>]*?)>(.*?)<\/head>/is', '', $html) ?? '';
        $html = \preg_replace('/<style([^\>]*?)>([^\<]*?)<\/style>/is', '', $html) ?? '';
        // remove all unsupported
        $html = \strip_tags($html, self::HTML_VALID_TAGS);
        // preserve pre tag
        $html = \preg_replace('/<pre/', '<xre', $html) ?? '';
        //replace some blank characters
        $regexp_block_tags = \implode('|', self::HTML_BLOCK_TAGS);
        $html = \preg_replace(
            '/<(' . $regexp_block_tags . ')([^\>]*)>[\n\r\t]+/',
            '<\\1\\2>',
            $html,
        ) ?? '';
        // newlines
        $html = \preg_replace('@(\r\n|\r)@', "\n", $html) ?? '';
        // special chars
        $html = \strtr($html, self::HTML_REPLACEMENT_CHARS);

        // tag: pre
        $offset = 0;
        while (
            ($offset < \strlen($html))
            && (($pos = \strpos($html, '</pre>', $offset)) !== false)
        ) {
            $html_a = \substr($html, 0, $offset);
            $html_b = \substr($html, $offset, ($pos - $offset + 6));
            while (
                \preg_match(
                    "'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si",
                    $html_b,
                ) > 0
            ) {
                // preserve newlines on <pre> tag
                $html_b = \preg_replace(
                    "'<xre([^\>]*)>(.*?)\n(.*?)</pre>'si",
                    "<xre\\1>\\2<br />\\3</pre>",
                    $html_b,
                ) ?? '';
            }
            while (
                \preg_match(
                    "'<xre([^\>]*)>(.*?)" . $this->spaceregexp['p'] . "(.*?)</pre>'" . $this->spaceregexp['m'],
                    $html_b,
                ) > 0
            ) {
                // preserve spaces on <pre> tag
                $html_b = \preg_replace(
                    "'<xre([^\>]*)>(.*?)" . $this->spaceregexp['p'] . "(.*?)</pre>'" . $this->spaceregexp['m'],
                    "<xre\\1>\\2&nbsp;\\3</pre>",
                    $html_b,
                ) ?? '';
            }
            $html = $html_a . $html_b . \substr($html, $pos + 6);
            $offset = \strlen($html_a . $html_b);
        }

        // tag: textarea
        $offset = 0;
        while (
            ($offset < \strlen($html))
            && (($pos = \strpos($html, '</textarea>', $offset)) !== false)
        ) {
            $html_a = \substr($html, 0, $offset);
            $html_b = \substr($html, $offset, ($pos - $offset + 11));
            while (
                \preg_match(
                    "'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si",
                    $html_b,
                ) > 0
            ) {
                // preserve newlines on <textarea> tag
                $html_b = \preg_replace(
                    "'<textarea([^\>]*)>(.*?)\n(.*?)</textarea>'si",
                    "<textarea\\1>\\2<TBR>\\3</textarea>",
                    $html_b,
                ) ?? '';
                $html_b = \preg_replace(
                    "'<textarea([^\>]*)>(.*?)[\"](.*?)</textarea>'si",
                    "<textarea\\1>\\2''\\3</textarea>",
                    $html_b,
                ) ?? '';
            }
            $html = $html_a . $html_b . \substr($html, $pos + 11);
            $offset = \strlen($html_a . $html_b);
        }

        // tag: option
        $html = \preg_replace('/([\s]*)<option/si', '<option', $html) ?? '';
        $html = \preg_replace('/<\/option>([\s]*)/si', '</option>', $html) ?? '';
        $offset = 0;
        while (
            ($offset < \strlen($html))
            && (($pos = \strpos($html, '</option>', $offset)) !== false)
        ) {
            $html_a = \substr($html, 0, $offset);
            $html_b = \substr($html, $offset, ($pos - $offset + 9));
            while (\preg_match("'<option([^\>]*)>(.*?)</option>'si", $html_b) > 0) {
                $html_b = \preg_replace_callback(
                    "'<option([^\>]*)>(.*?)</option>'si",
                    static function (array $optm): string {
                        $attrs = $optm[1];
                        $label = $optm[2];

                        $value = '';
                        if (\preg_match('/[\s]+value[\s]*=[\s]*"([^"]*)"/si', $attrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        } elseif (\preg_match('/[\s]+value[\s]*=[\s]*\'([^\']*)\'/si', $attrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        } elseif (\preg_match('/[\s]+value[\s]*=[\s]*([^\s>]+)/si', $attrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        }

                        $selected = (\preg_match('/(^|[\s])selected([\s]*=[\s]*("[^"]*"|\'[^\']*\'|[^\s>]+))?([\s]|$)/si', $attrs) > 0);
                        $prefix = $selected ? '#!SeL!#' : '';

                        if ($value !== '') {
                            return $prefix . $value . '#!TaB!#' . $label . '#!NwL!#';
                        }

                        return $prefix . $label . '#!NwL!#';
                    },
                    $html_b,
                ) ?? '';
            }
            $html = $html_a . $html_b . \substr($html, $pos + 9);
            $offset = \strlen($html_a . $html_b);
        }

        // tag: select
        if (\preg_match("'</select'si", $html) > 0) {
            $html = \preg_replace("'<select([^\>]*)>'si", "<select\\1 opt=\"", $html) ?? '';
            $html = \preg_replace("'#!NwL!#</select>'si", "\" />", $html) ?? '';
        }

        // newlines
        $html = \str_replace("\n", ' ', $html);
        // restore textarea newlines
        $html = \str_replace('<TBR>', "\n", $html);

        // remove extra spaces
        $html = \preg_replace(
            '/[\s]+<\/(table|tr|ul|ol|dl)>/',
            '</\\1>',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/' . $this->spaceregexp['p'] . '+<\/(td|th|li|dt|dd)>/' . $this->spaceregexp['m'],
            '</\\1>',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/[\s]+<(tr|td|th|li|dt|dd)/',
            '<\\1',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/' . $this->spaceregexp['p'] . '+<(ul|ol|dl|br)/' . $this->spaceregexp['m'],
            '<\\1',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</',
            '</\\1><',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<\/(td|th)>/',
            '<marker style="font-size:0"/></\\1>',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<\/table>([\s]*)<marker style="font-size:0"\/>/',
            '</table>',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/' . $this->spaceregexp['p'] . '+<img/' . $this->spaceregexp['m'],
            chr(32) . '<img',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<img([^\>]*)>[\s]+([^\<])/xi',
            '<img\\1>&nbsp;\\2',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<img([^\>]*)>/xi',
            '<img\\1><span><marker style="font-size:0"/></span>',
            $html,
        ) ?? '';
        // restore pre tag
        $html = \preg_replace(
            '/<xre/',
            '<pre',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<textarea([^\>]*)>([^\<]*)<\/textarea>/xi',
            '<textarea\\1 value="\\2" />',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<li([^\>]*)><\/li>/',
            '<li\\1>&nbsp;</li>',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<li([^\>]*)>' . $this->spaceregexp['p'] . '*<img/' . $this->spaceregexp['m'],
            '<li\\1><font size="1">&nbsp;</font><img',
            $html,
        ) ?? '';
        // preserve some spaces
        $html = \preg_replace(
            '/<([^\>\/]*)>[\s]/',
            '<\\1>&nbsp;',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/[\s]<\/([^\>]*)>/',
            '&nbsp;</\\1>',
            $html,
        ) ?? '';
        // fix sub/sup alignment
        $html = \preg_replace(
            '/<su([bp])/',
            '<zws/><su\\1',
            $html,
        ) ?? '';
        $html = \preg_replace(
            '/<\/su([bp])>/',
            '</su\\1><zws/>',
            $html,
        ) ?? '';
        // replace multiple spaces with a single space
        $html = \preg_replace(
            '/' . $this->spaceregexp['p'] . '+/' . $this->spaceregexp['m'],
            chr(32),
            $html,
        ) ?? '';

        // trim string
        $html = $this->strTrim($html);

        // fix br tag after li
        $html = \preg_replace('/<li><br([^\>]*)>/', '<li> <br\\1>', $html) ?? '';

        // fix first image tag alignment
        $html = \preg_replace('/^<img/', '<span style="font-size:0"><br /></span> <img', $html, 1) ?? '';

        return $html;
    }

    /**
     * Returns the default properties for the root HTML element.
     *
     * @return THTMLAttrib
     */
    protected function getHTMLRootProperties(): array
    {
        $font = $this->font->getCurrentFont();
        $fontname = $this->font->getFontFamilyName((string) $font['key']);
        if ($fontname === '') {
            $fontname = (string) $font['key'];
        }
        return [
            'align' => '',
            'attribute' => [],
            //'azimuth' => '',//
            'bgcolor' => '',
            'block' => false,
            'border' => [],
            //'border-collapse' => '',//
            //'border-spacing' => '',//
            //'caption-side' => '',//
            'clip' => false,
            //'color' => '',//
            'cols' => 0,
            'content' => '',
            'cssdata' => [],
            'csssel' => [],
            //'cursor' => '',//
            'dir' => $this->rtl ? 'rtl' : 'ltr',
            //'direction' => '',//
            'elkey' => 0,
            //'empty-cells' => '',//
            'fgcolor' => 'black',
            'fill' => true,
            //'font' => '',//
            //'font-family' => '',//
            //'font-size' => $font['size'],//
            //'font-size-adjust' => '',//
            'font-stretch' => $font['stretching'],
            //'font-style' => $font['style'],//
            //'font-variant' => '',//
            //'font-weight' => '',//
            'fontname' => $fontname,
            'fontsize' => $font['size'],
            'fontstyle' => $font['style'],
            'height' => 0.0,
            'hide' => false,
            'letter-spacing' => $font['spacing'],
            'line-height' => 1.0,
            //'list-style' => '',//
            //'list-style-image' => '',//
            //'list-style-position' => '',//
            //'list-style-type' => '',//
            'listtype' => '',
            'margin' => self::ZEROCELLBOUND,
            'opening' => false,
            //'orphans' => '',//
            'padding' => self::ZEROCELLBOUND,
            //'page' => '',//
            //'page-break-inside' => '',//
            'parent' => 0,
            //'quotes' => '',//
            'rows' => 0,
            'self' => false,
            //'speak' => '',//
            //'speak-header' => '',//
            'stroke' => 0.0,
            'strokecolor' => 'black',
            'style' => [],
            'tag' => false,
            //'text-align' => '',//
            'text-indent' => 0.0,
            'text-transform' => '',
            'thead' => '',
            'trids' => [],
            'value' => '',
            //'volume' => '',//
            //'white-space' => '',//
            //'widows' => '',//
            'width' => 0.0,
            //'word-spacing' => 0.0,//
            'x' => 0.0,
            'y' => 0.0,

        ];
    }

    /**
     * Parse and returs the HTML DOM array,
     *
     * @param string $html HTML code to parse.
     *
     * @return array<int, THTMLAttrib> HTML DOM Array
     */
    protected function getHTMLDOM(string $html): array
    {
        $css = $this->getCSSArrayFromHTML($html);
        // create a custom tag to contain the encoded CSS data array (used for table content).
        $jcss = \json_encode($css);
        $cssarray = '';
        if ($jcss !== false) {
            $cssarray = '<cssarray>' . \htmlentities($jcss) . '</cssarray>';
        }

        $html = $this->sanitizeHTML($html);

        /** @var array<int, THTMLAttrib> $dom */
        $dom = [0 => $this->getHTMLRootProperties()];

        /** @var array<int> $level */
        $level = [0];

        $elm = \preg_split(self::HTML_TAG_PATTERN, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($elm === false) {
            return $dom;
        }

        $maxel = \count($elm);
        $elkey = 0;
        $key = 1;
        $inthead = false;

        while ($elkey < $maxel) {
            $element = $elm[$elkey];
            $parent = \intval(\end($level));

            // init new DOM element
            $dom[$key] = $dom[0];
            $dom[$key]['dir'] = $dom[$parent]['dir'];
            $dom[$key]['elkey'] = $elkey;
            $dom[$key]['opening'] = false;
            $dom[$key]['parent'] = $parent;

            if (\preg_match(self::HTML_TAG_PATTERN, $element) > 0) {
                $element = \substr($element, 1, -1);
                if (empty(\preg_match('/[\/]?([a-zA-Z0-9]*)/', $element, $tag))) {
                    continue;
                }
                $tagname = \strtolower($tag[1]);
                if ($tagname == 'thead') {
                    $inthead = ($element[0] !== '/');
                    ++$elkey;
                    continue;
                }
                $dom[$key]['tag'] = true;
                $dom[$key]['value'] = $tagname;
                $dom[$key]['block'] = \in_array($dom[$key]['value'], self::HTML_BLOCK_TAGS);
                /** @var array<int, THTMLAttrib> $dom */
                if ($element[0] == '/') { // closing tag
                    array_pop($level);
                    $this->processHTMLDOMClosingTag(
                        $dom,
                        $elm,
                        $key,
                        $parent,
                        $cssarray,
                    );
                } else { // opening or self-closing html tag
                    $this->processHTMLDOMOpeningTag(
                        $dom,
                        $css,
                        $level,
                        $element,
                        $key,
                        $inthead,
                    );
                }
            } else {
                /** @var array<int, THTMLAttrib> $dom */
                // content between tags (TEXT)
                $this->processHTMLDOMText(
                    $dom,
                    $element,
                    $key,
                    $parent,
                );
            }

            ++$elkey;
            ++$key;
        }
        /** @var array<int, THTMLAttrib> $dom */
        return $dom;
    }

    /**
     * Process the content between tags (text).
     *
    * @param array<int, THTMLAttrib> $dom DOM array.
     * @param string $element Element data.
     * @param int $key Current element ID.
     * @param int $parent ID of the parent element.
     *
     * @return void
     */
    protected function processHTMLDOMText(array &$dom, string $element, int $key, int $parent): void
    {
        $this->inheritHTMLProperties($dom, $key, $parent);
        $transform = (
            isset($dom[$parent]['text-transform'])
            && \is_string($dom[$parent]['text-transform'])
        ) ? $dom[$parent]['text-transform'] : '';

        if ($transform !== '') {
            if (!empty(self::HTML_TEXT_TRANSFORM[$transform])) {
                $element = \mb_convert_case(
                    $element,
                    self::HTML_TEXT_TRANSFORM[$transform],
                    $this->encoding,
                );
            }
            $element = \preg_replace("/&NBSP;/i", "&nbsp;", $element) ?? '';
        }
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['value'] = \stripslashes($this->unhtmlentities($element));
    }

    /**
     * Inherit HTML properties from a parent element.
     *
    * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key ID of the current HTML element.
     * @param int $parent ID of the parent element from which to inherit properties.
     *
     * @return void
     */
    protected function inheritHTMLProperties(array &$dom, int $key, int $parent): void
    {
        $defaults = $dom[0] ?? [];
        foreach (
            [
            'align',
            'bgcolor',
            'border',
            'clip',
            'dir',
            'fgcolor',
            'fill',
            'font-stretch',
            'fontname',
            'fontsize',
            'fontstyle',
            'hide',
            'letter-spacing',
            'line-height',
            'listtype',
            'stroke',
            'strokecolor',
            'text-indent',
            'text-transform',
            ] as $prop
        ) {
            if (!isset($dom[$parent][$prop]) || !isset($defaults[$prop])) {
                continue;
            }

            if (!isset($dom[$key][$prop]) || $dom[$key][$prop] === $defaults[$prop]) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key][$prop] = $dom[$parent][$prop];
            }
        }
    }

    /**
     * Process the HTML DOM closing tag.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param array<int, string> $elm Current element.
     * @param int $key Current element ID.
     * @param int $parent ID of the parent element.
     * @param string $cssarray.
     *
     * @return void
     */
    protected function processHTMLDOMClosingTag(
        array &$dom,
        array $elm,
        int $key,
        int $parent,
        string $cssarray,
    ): void {
        $granparent = $dom[$parent]['parent'];
        // @phpstan-ignore-next-line parameterByRef.type
        $this->inheritHTMLProperties($dom, $key, $granparent);

        // Carry margin and padding from the opening tag so that closeHTMLBlock
        // can correctly apply bottom spacing (e.g. CSS margin-bottom, heading defaults).
        if (!empty($dom[$parent]['margin']) && \is_array($dom[$parent]['margin'])) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['margin'] = $dom[$parent]['margin'];
        }
        if (!empty($dom[$parent]['padding']) && \is_array($dom[$parent]['padding'])) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['padding'] = $dom[$parent]['padding'];
        }
        /** @var array<int, THTMLAttrib> $dom */

        // set the number of columns in table tag
        if (
            ($dom[$key]['value'] == 'tr')
            && (!empty($dom[$parent]['cols']))
            && (empty($dom[$granparent]['cols']))
        ) {
            // @phpstan-ignore parameterByRef.type
            $dom[$granparent]['cols'] = $dom[$parent]['cols'];
        }
        /** @var array<int, THTMLAttrib> $dom */
        $content = '';
        if (($dom[$key]['value'] == 'td') || ($dom[$key]['value'] == 'th')) {
            $content = $cssarray;
            for ($idx = ($parent + 1); $idx < $key; ++$idx) {
                $content .= \stripslashes($elm[$dom[$idx]['elkey']]);
            }
            $key = $idx;
            // mark nested tables
            $content = \str_replace('<table', '<table nested="true"', $content);
            // remove thead sections from nested tables
            $content = \str_replace('<thead>', '', $content);
            $content = \str_replace('</thead>', '', $content);
        }
        // @phpstan-ignore parameterByRef.type
        $dom[$parent]['content'] = $content;
        /** @var array<int, THTMLAttrib> $dom */
        // store header rows on a new table
        if (
            ($dom[$key]['value'] == 'tr')
            && !empty($dom[$parent]['thead'])
        ) {
            if (empty($dom[$granparent]['thead'])) {
                // @phpstan-ignore parameterByRef.type
                $dom[$granparent]['thead'] = $cssarray . $elm[$dom[$granparent]['elkey']];
            }
            for ($idx = $parent; $idx <= $key; ++$idx) {
                /** @var array<int, THTMLAttrib> $dom */
                // @phpstan-ignore parameterByRef.type
                $dom[$granparent]['thead'] .= $elm[$dom[$idx]['elkey']];
            }
            /** @var array<int, THTMLAttrib> $dom */
            // header elements must be always contained in a single page
            // @phpstan-ignore parameterByRef.type
            $dom[$parent]['attribute']['nobr'] = 'true';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'table')
            && (!empty($dom[$parent]['thead']))
        ) {
            // remove the nobr attributes from the table header
            $dom[$parent]['thead'] = \str_replace(' nobr="true"', '', $dom[$parent]['thead']);
            $dom[$parent]['thead'] .= '</tablehead>';
        }
    }

    /**
     * Process HTML DOM Opening Tag.
     *
    * @param array<int, THTMLAttrib> $dom
     * @param array<string, string> $css
    * @param array<int> $level
     * @param string $element
     * @param int $key
     * @param bool $thead
     *
     * @return void
     */
    protected function processHTMLDOMOpeningTag(
        array &$dom,
        array $css,
        array &$level,
        string $element,
        int $key,
        bool $thead,
    ): void {
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['opening'] = true;
        /** @var array<int, THTMLAttrib> $dom */
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['self'] = ((\substr($element, -1, 1) == '/')
            || (\in_array($dom[$key]['value'], self::HTML_SELF_CLOSING_TAGS)));
        if (!$dom[$key]['self']) {
            \array_push($level, $key);
        }
        $parentkey = 0;
        if ($key > 0) {
            $parentkey = (int) $dom[$key]['parent'];
            // @phpstan-ignore-next-line parameterByRef.type
            $this->inheritHTMLProperties($dom, $key, $parentkey);
        }

        // get attributes
        if (
            \preg_match_all(
                '/([^=\s]*)[\s]*=[\s]*"([^"]*)"/',
                $element,
                $attr_array,
                PREG_PATTERN_ORDER,
            ) > 0
        ) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['attribute'] = []; // reset attribute array
            foreach ($attr_array[1] as $id => $name) {
                /** @var array<int, THTMLAttrib> $dom */
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['attribute'][\strtolower($name)] = (string) $attr_array[2][$id];
            }
        }
        if (!empty($css)) {
            /** @var array<int, THTMLAttrib> $dom */
            // merge CSS style to current style
            $this->getHTMLDOMCSSData($dom, $css, $key);
            if (!empty($dom[$key]['cssdata'])) {
                $dom[$key]['attribute']['style'] = $this->implodeCSSData($dom[$key]['cssdata']);
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        $this->parseHTMLStyleAttributes($dom, $key, $parentkey);
        $this->parseHTMLAttributes($dom, $key, $thead);
    }

    /**
     * Returns the styles array that apply for the selected HTML tag.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param array<string, string> $css
     * @param int $key Key of the current HTML tag.
     */
    public function getHTMLDOMCSSData(array &$dom, array $css, int $key): void
    {
        /** @var array<TCSSData> $ret */
        $ret = [];
        // get parent CSS selectors
        /** @var array<string> $selectors */
        $selectors = [];
        $parent = $dom[$key]['parent'];
        if (!empty($dom[$parent]['csssel'])) {
            $selectors = $dom[$parent]['csssel'];
        }
        // get all styles that apply
        foreach ($css as $selector => $style) {
            $pos = \strpos($selector, ' ');
            if ($pos === false) {
                continue;
            }
            // get specificity
            $specificity = \substr($selector, 0, $pos);
            // remove specificity
            $selector = \substr($selector, $pos);
            // check if this selector apply to current tag
            if ($this->isValidCSSSelectorForTag($dom, $key, $selector)) {
                if (!\in_array($selector, $selectors)) {
                    // add style if not already added on parent selector
                    $ret[] = [
                        'k' => $selector,
                        's' => $specificity,
                        'c' => $style,
                    ];
                    $selectors[] = $selector;
                }
            }
        }
        if (
            !empty($dom[$key]['attribute'])
            && !empty($dom[$key]['attribute']['style'])
        ) {
            // attach inline style (latest properties have high priority)
            $ret[] = [
                'k' => '',
                's' => '1000',
                'c' => $dom[$key]['attribute']['style'],
            ];
        }
        // order the css array to account for specificity
        /** @var array<string, TCSSData> $cssordered */
        $cssordered = [];
        /** @var TCSSData $val */
        foreach ($ret as $key => $val) {
            $skey = \sprintf('%s_%04d', $val['s'], $key);
            $cssordered[$skey] = $val;
        }
        if (!empty($selectors)) {
            /** @var array<int, THTMLAttrib> $dom */
            /** @var array<string> $selectors */
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['csssel'] = $selectors;
        }
        if (!empty($cssordered)) {
            // sort selectors alphabetically to account for specificity
            \ksort($cssordered, SORT_STRING);
            /** @var array<int, THTMLAttrib> $dom */
            /** @var array<string, TCSSData> $cssordered */
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['cssdata'] = $cssordered;
        }
    }

    /**
     * Returns true if the CSS selector is valid for the selected HTML tag.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param int $key key of the current HTML tag
     * @param string $selector CSS selector string
     *
     * @return bool True if the selector is valid, false otherwise
     */
    public function isValidCSSSelectorForTag(array $dom, int $key, string $selector): bool
    {
        $ret = false;
        $tag = $dom[$key]['value'];
        $class = [];
        if (!empty($dom[$key]['attribute']['class'])) {
            $class = \explode(' ', \strtolower($dom[$key]['attribute']['class']));
        }
        $idx = '';
        if (!empty($dom[$key]['attribute']['id'])) {
            $idx = \strtolower($dom[$key]['attribute']['id']);
        }
        $selector = \preg_replace(
            '/([\>\+\~\s]{1})([\.]{1})([^\>\+\~\s]*)/si',
            '\\1*.\\3',
            $selector,
        ) ?? '';
        $matches = [];
        if (
            empty(\preg_match_all(
                '/([\>\+\~\s]{1})([a-zA-Z0-9\*]+)([^\>\+\~\s]*)/si',
                $selector,
                $matches,
                PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE,
            ))
        ) {
            return $ret;
        }
        $parentop = \array_pop($matches[1]);
        $operator = $parentop[0] ?? '';
        $offset = $parentop[1] ?? 0;
        $lasttag = \array_pop($matches[2]);
        $lasttag = \strtolower(\trim($lasttag[0] ?? ''));
        if (($lasttag !== '*') && ($lasttag !== $tag)) {
            return $ret;
        }
        // the last element on selector is our tag or 'any tag'
        $attrib = \array_pop($matches[3]);
        $attrib = \strtolower(\trim($attrib[0] ?? ''));
        if (empty($attrib)) {
            $ret = true;
        } else {
            // check if matches class, id, attribute, pseudo-class or pseudo-element
            switch ($attrib[0]) {
                case '.': // class
                    $ret =  (\in_array(\substr($attrib, 1), $class));
                    break;
                case '#': // ID
                    $ret = (\substr($attrib, 1) == $idx);
                    break;
                case '[': // attribute
                    $attrmatch = [];
                    if (
                        empty(\preg_match(
                            '/\[([a-zA-Z0-9]*)[\s]*([\~\^\$\*\|\=]*)[\s]*["]?([^"\]]*)["]?\]/i',
                            $attrib,
                            $attrmatch,
                        ))
                    ) {
                        break;
                    }
                    $att = \strtolower($attrmatch[1]);
                    $val = $attrmatch[3];
                    if (!empty($dom[$key]['attribute'][$att])) {
                        switch ($attrmatch[2]) {
                            case '=':
                                $ret = ($dom[$key]['attribute'][$att] == $val);
                                break;
                            case '~=':
                                $ret = (\in_array($val, \explode(' ', $dom[$key]['attribute'][$att])));
                                break;
                            case '^=':
                                $ret = ($val == \substr($dom[$key]['attribute'][$att], 0, \strlen($val)));
                                break;
                            case '$=':
                                $ret = ($val == \substr($dom[$key]['attribute'][$att], -\strlen($val)));
                                break;
                            case '*=':
                                $ret = (\strpos($dom[$key]['attribute'][$att], $val) !== false);
                                break;
                            case '|=':
                                $ret = (($dom[$key]['attribute'][$att] == $val)
                                || (\preg_match('/' . $val . '[\-]{1}/i', $dom[$key]['attribute'][$att]) > 0));
                                break;
                            default: {
                                $ret = true;
                            }
                        }
                    }
                    break;
                case ':': // pseudo-class or pseudo-element
                    if ($attrib[1] == ':') { // pseudo-element
                        // @TODO: pseudo-elements are not supported!
                        // (::first-line, ::first-letter, ::before, ::after)
                    } else { // pseudo-class
                        // @TODO: pseudo-classes are not supported!
                        // (:root, :nth-child(n), :nth-last-child(n),
                        // :nth-of-type(n), :nth-last-of-type(n), :first-child,
                        // :last-child, :first-of-type, :last-of-type, :only-child,
                        // :only-of-type, :empty, :link, :visited, :active, :hover,
                        // :focus, :target, :lang(fr), :enabled, :disabled, :checked)
                    }
                    break;
            } // end of switch
        }

        if (
            $ret
            && ($offset > 0)
            && \is_int($dom[$key]['parent'])
        ) {
            $ret = false;
            // check remaining selector part
            $selector = \substr($selector, 0, $offset);
            switch ($operator) {
                case ' ': // descendant of an element
                    while (
                        \is_int($dom[$key]['parent'])
                        && ($dom[$key]['parent'] > 0)
                    ) {
                        if ($this->isValidCSSSelectorForTag($dom, $dom[$key]['parent'], $selector)) {
                            $ret = true;
                            break;
                        } else {
                            $key = $dom[$key]['parent'];
                        }
                    }
                    break;
                case '>': // child of an element
                    $ret = $this->isValidCSSSelectorForTag($dom, $dom[$key]['parent'], $selector);
                    break;
                case '+': // immediately preceded by an element
                    for ($idx = ($key - 1); $idx > $dom[$key]['parent']; --$idx) {
                        if ($dom[$idx]['tag'] and $dom[$idx]['opening']) {
                            $ret = $this->isValidCSSSelectorForTag($dom, $idx, $selector);
                            break;
                        }
                    }
                    break;
                case '~': // preceded by an element
                    for ($idx = ($key - 1); $idx > $dom[$key]['parent']; --$idx) {
                        if (
                            $dom[$idx]['tag']
                            && $dom[$idx]['opening']
                            && $this->isValidCSSSelectorForTag($dom, $idx, $selector)
                        ) {
                            break;
                        }
                    }
                    break;
            }
        }

        return $ret;
    }

    /**
     * Parse HTML DOM Style attributes.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param int $key key of the current HTML tag.
     * @param int $parentkey Key of the parent element.
     */
    public function parseHTMLStyleAttributes(array &$dom, int $key, int $parentkey): void
    {
        if (
            empty($dom[$key]['attribute']['style'])
            || empty(\preg_match_all(
                '/([^;:\s]*):([^;]*)/',
                $dom[$key]['attribute']['style'],
                $style_array,
                PREG_PATTERN_ORDER,
            ))
        ) {
            return;
        }

        $dom[$key]['style'] = []; // reset style attribute array
        foreach ($style_array[1] as $id => $name) {
            /** @var array<int, THTMLAttrib> $dom */
            // in case of duplicate attribute the last replace the previous
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['style'][\strtolower($name)] = \trim($style_array[2][$id]);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // --- get some style attributes ---
        // text direction
        if (!empty($dom[$key]['style']['direction'])) {
            $dom[$key]['dir'] = $dom[$key]['style']['direction'];
        }
        /** @var array<int, THTMLAttrib> $dom */
        // display
        if (!empty($dom[$key]['style']['display'])) {
            $dom[$key]['hide'] = (\trim(\strtolower($dom[$key]['style']['display'])) == 'none');
        }
        /** @var array<int, THTMLAttrib> $dom */
        // font family
        if (!empty($dom[$key]['style']['font-family'])) {
            $dom[$key]['fontname'] = $this->font->getFontFamilyName($dom[$key]['style']['font-family']);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // list-style-type
        if (!empty($dom[$key]['style']['list-style-type'])) {
            $dom[$key]['listtype'] = \trim(\strtolower($dom[$key]['style']['list-style-type']));
            if ($dom[$key]['listtype'] == 'inherit') {
                $dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // text-indent
        if (!empty($dom[$key]['style']['text-indent'])) {
            if ($dom[$key]['style']['text-indent'] == 'inherit') {
                $dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
            } else {
                $dom[$key]['text-indent'] = $this->toUnit(
                    $this->getUnitValuePoints($dom[$key]['style']['text-indent']),
                );
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // text-transform
        if (!empty($dom[$key]['style']['text-transform'])) {
            $dom[$key]['text-transform'] = $dom[$key]['style']['text-transform'];
        }
        /** @var array<int, THTMLAttrib> $dom */
        // font size
        if (!empty($dom[$key]['style']['font-size'])) {
            $fsize = \trim($dom[$key]['style']['font-size']);
            $ref = self::REFUNITVAL;
            if (\is_numeric($dom[$parentkey]['fontsize'])) {
                $ref['parent'] = \floatval($dom[$parentkey]['fontsize']);
            }
            $dom[$key]['fontsize'] = $this->getFontValuePoints($fsize, $ref, 'pt');
        }
        /** @var array<int, THTMLAttrib> $dom */
        // font-stretch
        if (
            !empty($dom[$key]['style']['font-stretch'])
            && \is_numeric($dom[$parentkey]['font-stretch'])
        ) {
            $dom[$key]['font-stretch'] = $this->getTAFontStretching(
                $dom[$key]['style']['font-stretch'],
                \floatval($dom[$parentkey]['font-stretch']),
            );
        }
        /** @var array<int, THTMLAttrib> $dom */
        // letter-spacing
        if (
            !empty($dom[$key]['style']['letter-spacing'])
            && \is_numeric($dom[$parentkey]['letter-spacing'])
        ) {
            $dom[$key]['letter-spacing'] = $this->getTALetterSpacing(
                $dom[$key]['style']['letter-spacing'],
                \floatval($dom[$parentkey]['letter-spacing']),
            );
        }
        /** @var array<int, THTMLAttrib> $dom */
        // line-height (internally is the cell height ratio)
        if (!empty($dom[$key]['style']['line-height'])) {
            $lineheight = \trim($dom[$key]['style']['line-height']);
            switch ($lineheight) {
                // A normal line height. This is default
                case 'normal':
                    $dom[$key]['line-height'] = $dom[0]['line-height'];
                    break;
                case 'inherit':
                    $dom[$key]['line-height'] = $dom[$parentkey]['line-height'];
                    break;
                default:
                    if (\is_numeric($lineheight)) {
                        // convert to percentage of font height
                        $lineheight = ($lineheight * 100) . '%';
                    }
                    $dom[$key]['line-height'] = $this->toUnit(
                        $this->getUnitValuePoints($lineheight, defunit: '%')
                    );
                    /** @var array<int, THTMLAttrib> $dom */
                    if (\substr($lineheight, -1) !== '%') {
                        if ($dom[$key]['fontsize'] <= 0) {
                            $dom[$key]['line-height'] = 1.0;
                        } elseif (\is_numeric($dom[$key]['fontsize'])) {
                            $dom[$key]['line-height'] = $dom[$key]['line-height'] / floatval($dom[$key]['fontsize']);
                        }
                    }
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // font style
        if (
            !empty($dom[$key]['style']['font-weight'])
            && \is_string($dom[$key]['fontstyle'])
        ) {
            if (\is_numeric($dom[$key]['style']['font-weight'])) {
                if ((int) $dom[$key]['style']['font-weight'] >= 600) {
                    $dom[$key]['fontstyle'] .= 'B';
                } else {
                    $dom[$key]['fontstyle'] = \str_replace('B', '', $dom[$key]['fontstyle']);
                }
            } elseif (\strtolower($dom[$key]['style']['font-weight'][0]) == 'n') {
                if (\strpos($dom[$key]['fontstyle'], 'B') !== false) {
                    $dom[$key]['fontstyle'] = \str_replace('B', '', $dom[$key]['fontstyle']);
                }
            } elseif (\strtolower($dom[$key]['style']['font-weight'][0]) == 'b') {
                $dom[$key]['fontstyle'] .= 'B';
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            !empty($dom[$key]['style']['font-style'])
            && \is_string($dom[$key]['fontstyle'])
            && (\strtolower($dom[$key]['style']['font-style'][0]) == 'i')
        ) {
            $dom[$key]['fontstyle'] .= 'I';
        }
        /** @var array<int, THTMLAttrib> $dom */
        // font color
        if ((!empty($dom[$key]['style']['color']))) {
            $dom[$key]['fgcolor'] = $this->getCSSColor($dom[$key]['style']['color']);
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fgcolor'] = 'blue';
        }
        /** @var array<int, THTMLAttrib> $dom */
        // background color
        if ((!empty($dom[$key]['style']['background-color']))) {
            $dom[$key]['bgcolor'] = $this->getCSSColor($dom[$key]['style']['background-color']);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // text-decoration
        if (
            !empty($dom[$key]['style']['text-decoration'])
            && \is_string($dom[$key]['fontstyle'])
        ) {
            $decors = \explode(' ', \strtolower($dom[$key]['style']['text-decoration']));
            foreach ($decors as $dec) {
                $dec = \trim($dec);
                if (!empty($dec)) {
                    $dom[$key]['fontstyle'] .= match ($dec[0]) {
                        'u' => 'U',
                        'l' => 'D',
                        'o' => 'O',
                        default => '',
                    };
                }
            }
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fontstyle'] .= 'U';
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check width attribute
        if (!empty($dom[$key]['style']['width'])) {
            $dom[$key]['width'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['style']['width']));
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check height attribute
        if (!empty($dom[$key]['style']['height'])) {
            $dom[$key]['height'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['style']['height']));
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check text alignment
        if (!empty($dom[$key]['style']['text-align'])) {
            $dom[$key]['align'] = \strtoupper((string) $dom[$key]['style']['text-align'][0]);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for CSS padding properties
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['padding'] = empty($dom[$key]['style']['padding']) ?
            self::ZEROCELLBOUND : $this->getCSSPadding($dom[$key]['style']['padding']);

        // apply individual padding-* overrides
        foreach (['T' => 'padding-top', 'R' => 'padding-right', 'B' => 'padding-bottom', 'L' => 'padding-left'] as $side => $prop) {
            if (!empty($dom[$key]['style'][$prop]) && \strtolower(\trim($dom[$key]['style'][$prop])) !== 'auto') {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['padding'][$side] = $this->toUnit($this->getUnitValuePoints($dom[$key]['style'][$prop]));
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for CSS margin properties
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['margin'] = empty($dom[$key]['style']['margin']) ?
            self::ZEROCELLBOUND : $this->getCSSMargin($dom[$key]['style']['margin']);
        // apply individual margin-* overrides
        foreach (['T' => 'margin-top', 'R' => 'margin-right', 'B' => 'margin-bottom', 'L' => 'margin-left'] as $side => $prop) {
            if (!empty($dom[$key]['style'][$prop]) && \strtolower(\trim($dom[$key]['style'][$prop])) !== 'auto') {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['margin'][$side] = $this->toUnit($this->getUnitValuePoints($dom[$key]['style'][$prop]));
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check CSS border properties
        if (!empty($dom[$key]['style']['border'])) {
            $dom[$key]['border']['LTRB'] = $this->getCSSBorderStyle($dom[$key]['style']['border']);
        }
        /** @var  array<string, BorderStyle> $brdr */
        $brdr = [
            'L' => $this->getCSSDefaultBorderStyle(),
            'R' => $this->getCSSDefaultBorderStyle(),
            'T' => $this->getCSSDefaultBorderStyle(),
            'B' => $this->getCSSDefaultBorderStyle(),
        ];
        if (!empty($dom[$key]['style']['border-color'])) {
            $brd_colors = \preg_split('/[\s]+/', \trim($dom[$key]['style']['border-color']));
            if ($brd_colors !== false) {
                if (!empty($brd_colors[3])) {
                    $brdr['L']['lineColor'] = $this->getCSSColor($brd_colors[3]);
                }
                if (!empty($brd_colors[1])) {
                    $brdr['R']['lineColor'] = $this->getCSSColor($brd_colors[1]);
                }
                if (!empty($brd_colors[0])) {
                    $brdr['T']['lineColor'] = $this->getCSSColor($brd_colors[0]);
                }
                if (!empty($brd_colors[2])) {
                    $brdr['B']['lineColor'] = $this->getCSSColor($brd_colors[2]);
                }
            }
        }
        if (!empty($dom[$key]['style']['border-width'])) {
            $brd_widths = \preg_split('/[\s]+/', \trim($dom[$key]['style']['border-width']));
            if ($brd_widths !== false) {
                if (isset($brd_widths[3])) {
                    $brdr['L']['lineWidth'] = $this->getCSSBorderWidth($brd_widths[3]);
                }
                if (isset($brd_widths[1])) {
                    $brdr['R']['lineWidth'] = $this->getCSSBorderWidth($brd_widths[1]);
                }
                if (isset($brd_widths[0])) {
                    $brdr['T']['lineWidth'] = $this->getCSSBorderWidth($brd_widths[0]);
                }
                if (isset($brd_widths[2])) {
                    $brdr['B']['lineWidth'] = $this->getCSSBorderWidth($brd_widths[2]);
                }
            }
        }
        if (!empty($dom[$key]['style']['border-style'])) {
            $brd_styles = \preg_split('/[\s]+/', \trim($dom[$key]['style']['border-style']));
            if ($brd_styles !== false) {
                if (isset($brd_styles[3]) && ($brd_styles[3] != 'none')) {
                    $brdr['L']['lineCap'] = 'square';
                    $brdr['L']['lineJoin'] = 'miter';
                    $brdr['L']['dashPhase'] = $this->getCSSBorderDashStyle($brd_styles[3]);
                    if ($brdr['L']['dashPhase'] < 0) {
                        $brdr['L'] = [];
                    }
                }
                if (isset($brd_styles[1])) {
                    $brdr['R']['lineCap'] = 'square';
                    $brdr['R']['lineJoin'] = 'miter';
                    $brdr['R']['dashPhase'] = $this->getCSSBorderDashStyle($brd_styles[1]);
                    if ($brdr['R']['dashPhase'] < 0) {
                        $brdr['R'] = [];
                    }
                }
                if (isset($brd_styles[0])) {
                    $brdr['T']['lineCap'] = 'square';
                    $brdr['T']['lineJoin'] = 'miter';
                    $brdr['T']['dashPhase'] = $this->getCSSBorderDashStyle($brd_styles[0]);
                    if ($brdr['T']['dashPhase'] < 0) {
                        $brdr['T'] = [];
                    }
                }
                if (isset($brd_styles[2])) {
                    $brdr['B']['lineCap'] = 'square';
                    $brdr['B']['lineJoin'] = 'miter';
                    $brdr['B']['dashPhase'] = $this->getCSSBorderDashStyle($brd_styles[2]);
                    if ($brdr['B']['dashPhase'] < 0) {
                        $brdr['B'] = [];
                    }
                }
            }
        }
        $cellside = [
            'L' => 'left',
            'R' => 'right',
            'T' => 'top',
            'B' => 'bottom',
        ];
        $ref = self::REFUNITVAL;
        $ref['parent'] = 0.0;
        foreach ($cellside as $bsk => $bsv) {
            $brdr[$bsk] = $this->getCSSDefaultBorderStyle();
            if (!empty($dom[$key]['style']['border-' . $bsv])) {
                $brdr[$bsk] = $this->getCSSBorderStyle($dom[$key]['style']['border-' . $bsv]);
            }
            if (!empty($dom[$key]['style']['border-' . $bsv . '-color'])) {
                $brdr[$bsk]['lineColor'] = $this->getCSSColor(
                    $dom[$key]['style']['border-' . $bsv . '-color']
                );
            }
            if (!empty($dom[$key]['style']['border-' . $bsv . '-width'])) {
                $brdr[$bsk]['lineWidth'] = $this->getCSSBorderWidth(
                    $dom[$key]['style']['border-' . $bsv . '-width']
                );
            }
            if (!empty($dom[$key]['style']['border-' . $bsv . '-style'])) {
                $brdr[$bsk]['dashPhase'] = $this->getCSSBorderDashStyle(
                    $dom[$key]['style']['border-' . $bsv . '-style']
                );
                if ($brdr[$bsk]['dashPhase'] < 0) {
                    $brdr[$bsk] = [];
                }
            }
            /** @var  array<string, BorderStyle> $brdr */
            if ($brdr[$bsk]['lineWidth'] > 0) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['border'][$bsk] = $brdr[$bsk];
            }
            /** @var array<int, THTMLAttrib> $dom */
            if (!empty($dom[$key]['style']['padding-' . $bsv])) {
                $dom[$key]['padding'][$bsk] = $this->toUnit(
                    $this->getUnitValuePoints($dom[$key]['style']['padding-' . $bsv], $ref)
                );
            }
            /** @var array<int, THTMLAttrib> $dom */
            if (!empty($dom[$key]['style']['margin-' . $bsv])) {
                $dom[$key]['margin'][$bsk] = $this->toUnit($this->getUnitValuePoints(
                    \str_replace('auto', '0', $dom[$key]['style']['margin-' . $bsv]),
                    $ref
                ));
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for CSS border-spacing properties
        if (!empty($dom[$key]['style']['border-spacing'])) {
            $dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // page-break-inside
        if (
            !empty($dom[$key]['style']['page-break-inside'])
            && ($dom[$key]['style']['page-break-inside'] == 'avoid')
        ) {
            $dom[$key]['attribute']['nobr'] = 'true';
        }
        /** @var array<int, THTMLAttrib> $dom */
        // page-break-before
        if (!empty($dom[$key]['style']['page-break-before'])) {
            $dom[$key]['attribute']['pagebreak'] = match ($dom[$key]['style']['page-break-before']) {
                'always' => 'true',
                'left' => 'left',
                'right' => 'right',
                default => '',
            };
        }
        /** @var array<int, THTMLAttrib> $dom */
        // page-break-after
        if (!empty($dom[$key]['style']['page-break-after'])) {
            $dom[$key]['attribute']['pagebreakafter'] = match ($dom[$key]['style']['page-break-after']) {
                'always' => 'true',
                'left' => 'left',
                'right' => 'right',
                default => '',
            };
        }
    }

    /**
     * Parst HTML DOM attributes.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param int $key key of the current HTML tag.
     * @param bool $thead
     */
    public function parseHTMLAttributes(array &$dom, int $key, bool $thead): void
    {
        if (!empty($dom[$key]['attribute']['display'])) {
            $dom[$key]['hide'] = (\trim(\strtolower($dom[$key]['attribute']['display'])) == 'none');
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (!empty($dom[$key]['attribute']['border'])) {
            $borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'] . ' solid black');
            if (!empty($borderstyle)) {
                $dom[$key]['border']['LTRB'] = $borderstyle;
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for font tag
        if ($dom[$key]['value'] == 'font') {
            // font family
            if (!empty($dom[$key]['attribute']['face'])) {
                $dom[$key]['fontname'] = $this->font->getFontFamilyName($dom[$key]['attribute']['face']);
            }
            /** @var array<int, THTMLAttrib> $dom */
            $parent = $dom[$key]['parent'];
            // font size
            if (!empty($dom[$key]['attribute']['size'])) {
                if (
                    ($key > 0)
                    && !empty($dom[$parent]['fontsize'])
                    && \is_numeric($dom[$parent]['fontsize'])
                ) {
                    $dom[$key]['fontsize'] = match ($dom[$key]['attribute']['size'][0]) {
                        '+' => $dom[$parent]['fontsize']
                            + \floatval(\substr($dom[$key]['attribute']['size'], 1)),
                        '-' => $dom[$parent]['fontsize']
                            - \floatval(\substr($dom[$key]['attribute']['size'], 1)),
                        default => \floatval($dom[$key]['attribute']['size']),
                    };
                } elseif (\is_numeric($dom[$key]['attribute']['size'])) {
                    $dom[$key]['fontsize'] = \floatval($dom[$key]['attribute']['size']);
                }
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // force natural alignment for lists
        if (
            (($dom[$key]['value'] == 'ul')
            || ($dom[$key]['value'] == 'ol')
            || ($dom[$key]['value'] == 'dl'))
            && (!isset($dom[$key]['align'])
            || empty($dom[$key]['align'])
            || ($dom[$key]['align'] != 'J'))
        ) {
            $dom[$key]['align'] = ($this->rtl) ? 'R' : 'L';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'small')
            || ($dom[$key]['value'] == 'sup')
            || ($dom[$key]['value'] == 'sub')
        ) {
            if (
                !isset($dom[$key]['attribute']['size'])
                && empty($dom[$key]['style']['font-size'])
                && \is_numeric($dom[$key]['fontsize'])
            ) {
                $dom[$key]['fontsize'] = \floatval($dom[$key]['fontsize']) * self::FONT_SMALL_RATIO;
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (empty($dom[$key]['fontstyle']) || !\is_string($dom[$key]['fontstyle'])) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['fontstyle'] = '';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'strong')
            || ($dom[$key]['value'] == 'b')
        ) {
            $dom[$key]['fontstyle'] .= 'B';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'em')
            || ($dom[$key]['value'] == 'i')
        ) {
            $dom[$key]['fontstyle'] .= 'I';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if ($dom[$key]['value'] == 'u') {
            $dom[$key]['fontstyle'] .= 'U';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'del')
            || ($dom[$key]['value'] == 's')
            || ($dom[$key]['value'] == 'strike')
        ) {
            $dom[$key]['fontstyle'] .= 'D';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            empty($dom[$key]['style']['text-decoration'])
            && ($dom[$key]['value'] == 'a')
        ) {
            $dom[$key]['fontstyle'] .= 'U';
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'pre')
            || ($dom[$key]['value'] == 'tt')
        ) {
            $dom[$key]['fontname'] = self::FONT_MONO;
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            !empty($dom[$key]['value'])
            && ($dom[$key]['value'][0] == 'h')
            && \is_numeric($dom[$key]['value'][1])
            && (\intval($dom[$key]['value'][1]) > 0)
            && (\intval($dom[$key]['value'][1]) < 7)
        ) {
            // headings h1, h2, h3, h4, h5, h6
            if (
                !isset($dom[$key]['attribute']['size'])
                && empty($dom[$key]['style']['font-size'])
                && \is_numeric($dom[$key]['value'][1])
            ) {
                $headsize = (4 - \intval($dom[$key]['value'][1])) * 2;
                $dom[$key]['fontsize'] = $dom[0]['fontsize'] + $headsize;
            }
            if (empty($dom[$key]['style']['font-weight'])) {
                $dom[$key]['fontstyle'] .= 'B';
            }
            // apply default proportional top/bottom margin unless overridden by CSS
            if (empty($dom[$key]['style']['margin']) && empty($dom[$key]['style']['margin-top'])) {
                $dom[$key]['margin']['T'] = $this->toUnit((float) $dom[$key]['fontsize'] * 0.67);
            }
            if (empty($dom[$key]['style']['margin']) && empty($dom[$key]['style']['margin-bottom'])) {
                $dom[$key]['margin']['B'] = $this->toUnit((float) $dom[$key]['fontsize'] * 0.67);
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (($dom[$key]['value'] == 'table')) {
            $dom[$key]['rows'] = 0; // number of rows
            $dom[$key]['trids'] = []; // IDs of TR elements
            $dom[$key]['thead'] = ''; // table header rows
        }
        /** @var array<int, THTMLAttrib> $dom */
        if ($dom[$key]['value'] == 'tr') {
            $dom[$key]['cols'] = 0;
            /** @var array<int, THTMLAttrib> $dom */
            if ($thead) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['thead'] = 'true';
                // rows on thead block are printed as a separate table
            } else {
                $parent = \is_int($dom[$key]['parent']) ? $dom[$key]['parent'] : 0;
                if (
                    !isset($dom[$parent]['rows'])
                    || !\is_int($dom[$parent]['rows'])
                ) {
                    $dom[$parent]['rows'] = 0;
                }
                /** @var array<int, THTMLAttrib> $dom */
                // store the number of rows on table element
                // @phpstan-ignore parameterByRef.type
                ++$dom[$parent]['rows'];
                /** @var array<int, THTMLAttrib> $dom */
                if (
                    !isset($dom[$parent]['trids'])
                    || !\is_array($dom[$parent]['trids'])
                ) {
                    $dom[$parent]['trids'] = [];
                }
                /** @var array<int, THTMLAttrib> $dom */
                // store the TR elements IDs on table element
                // @phpstan-ignore parameterByRef.type
                \array_push($dom[$parent]['trids'], $key);
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (
            ($dom[$key]['value'] == 'th')
            || ($dom[$key]['value'] == 'td')
        ) {
            $colspan = (isset($dom[$key]['attribute']['colspan'])
                && \is_numeric($dom[$key]['attribute']['colspan']))
                ? $dom[$key]['attribute']['colspan'] : '1';
            $rowspan = (isset($dom[$key]['attribute']['rowspan'])
                && \is_numeric($dom[$key]['attribute']['rowspan']))
                ? $dom[$key]['attribute']['rowspan'] : '1';
            $dom[$key]['attribute']['colspan'] = $colspan;
            $dom[$key]['attribute']['rowspan'] = $rowspan;
            $parent = \is_int($dom[$key]['parent']) ? $dom[$key]['parent'] : 0;
            if (
                isset($dom[($parent)]['cols'])
                && \is_numeric($dom[($parent)]['cols'])
            ) {
                $dom[$parent]['cols'] += \intval($colspan);
            }
        }
        /** @var array<int, THTMLAttrib> $dom */
        // text direction
        if (!empty($dom[$key]['attribute']['dir'])) {
            $dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
        }
        /** @var array<int, THTMLAttrib> $dom */
        // set foreground color attribute
        if (!empty($dom[$key]['attribute']['color'])) {
            $dom[$key]['fgcolor'] = $this->getCSSColor($dom[$key]['attribute']['color']);
        } elseif (
            empty($dom[$key]['style']['color'])
            && ($dom[$key]['value'] == 'a')
        ) {
            $dom[$key]['fgcolor'] = 'blue';
        }
        /** @var array<int, THTMLAttrib> $dom */
        // set background color attribute
        if (!empty($dom[$key]['attribute']['bgcolor'])) {
            $dom[$key]['bgcolor'] = $this->getCSSColor($dom[$key]['attribute']['bgcolor']);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // set stroke color attribute
        if (!empty($dom[$key]['attribute']['strokecolor'])) {
            $dom[$key]['strokecolor'] = $this->getCSSColor($dom[$key]['attribute']['strokecolor']);
        }
        /** @var array<int, THTMLAttrib> $dom */
        /** @var array<int, THTMLAttrib> $dom */
        // check for width attribute
        if (isset($dom[$key]['attribute']['width'])) {
            $dom[$key]['width'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['attribute']['width']));
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for height attribute
        if (isset($dom[$key]['attribute']['height'])) {
            $dom[$key]['height'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['attribute']['height']));
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for text alignment
        if (
            (!empty($dom[$key]['attribute']['align']))
            && ($dom[$key]['value'] !== 'img')
        ) {
            $dom[$key]['align'] = \strtoupper($dom[$key]['attribute']['align'][0]);
        }
        /** @var array<int, THTMLAttrib> $dom */
        // check for text rendering mode (the following attributes do not exist in HTML)
        if (
            !empty($dom[$key]['attribute']['stroke'])
            && \is_numeric($dom[$key]['attribute']['stroke'])
            && \is_numeric($dom[$key]['fontsize'])
        ) {
            $ref = self::REFUNITVAL;
            $ref['parent'] = \floatval($dom[$key]['fontsize']);
            // font stroke width
            $dom[$key]['stroke'] = $this->toUnit(
                $this->getUnitValuePoints($dom[$key]['attribute']['stroke'], $ref)
            );
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (isset($dom[$key]['attribute']['fill'])) {
            // font fill
            $dom[$key]['fill'] = ($dom[$key]['attribute']['fill'] == 'true');
        }
        /** @var array<int, THTMLAttrib> $dom */
        if (isset($dom[$key]['attribute']['clip'])) {
            // clipping mode
            $dom[$key]['clip'] = ($dom[$key]['attribute']['clip'] == 'true');
        }
    }

    /**
     * Sets the default symbol to be used as unordered-list (UL) list-item (LI) bullet.
     *
     * @param string $sym This can be one of the values in self::LIST_SYMBOL
     *                       or an image specified as:'img|type|width|height|image.ext').
     */
    public function setULLIDot($sym = '!'): void
    {
        if (\substr($sym, 0, 4) == 'img|') {
            // image type
            $this->ullidot = $sym;
            return;
        }

        $sym = \strtolower($sym);
        $this->ullidot = (\in_array($sym, self::LIST_SYMBOL)) ? $sym : '!';
    }

    /**
     * Returns the PDF code for an HTML list bullet or ordered list item symbol.
     *
     * @param int    $depth  List nesting level.
     * @param int    $count  List entry position, starting form 1.
     * @param float  $posx   Abscissa of upper-left corner.
     * @param float  $posy   Ordinate of upper-left corner.
     * @param string $type   Type of list.
     *
     * @return string
     */
    protected function getHTMLliBullet(
        int $depth,
        int $count,
        float $posx = 0,
        float $posy = 0,
        string $type = '',
    ): string {
        switch ($type) {
            case '^': // special symbol used for avoid justification of rect bullet
                return '';
            case '!': // default list type for unordered list
                $type = self::LIST_DEF_ULTYPE[($depth - 1) % 3];
                break;
            case '#': // default list type for ordered list
                $type = 'decimal';
                break;
            default:
                if (\substr($type, 0, 4) === 'img|') {
                    // custom image type ('img|type|width|height|image.ext')
                    $img = \explode('|', $type);
                    $type = 'img';
                }
        }

        $font = $this->font->getCurrentFont();
        $size = $font['usize'];
        $lspace = $this->getStringWidth(' '); // width of one space in document units
        $txti = '';
        $img = ['', '', '0', '0', ''];

        switch ($type) {
            // unordered types
            case 'none':
                break;
            case 'disc':
                if ($this->isunicode) {
                    $txti = "\u{2022}";
                    break;
                }
                $rad = $size / 4;
                $lspace += (2 * $rad);
                $posx += $this->rtl ? $lspace : -$lspace;
                $style = [
                    'lineWidth' => 0,
                    'lineCap'   => 'butt',
                    'lineJoin'  => 'miter',
                    'miterLimit' => 0,
                    'dashArray' => [],
                    'dashPhase' => 0,
                    'lineColor' => (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                return $this->graph->getStartTransform()
                . $this->graph->getCircle(
                    $posx,
                    $posy + $this->toUnit($font['midpoint']),
                    $rad,
                    0,
                    360,
                    'F',
                    $style,
                    8,
                ) . $this->graph->getStopTransform();
            case 'circle':
                if ($this->isunicode) {
                    $txti = "\u{25E6}";
                    break;
                }
                $rad = $size / 4;
                $lspace += (2 * $rad);
                $posx += $this->rtl ? $lspace : -$lspace;
                $style = [
                    'lineWidth' => ($rad / 3),
                    'lineCap'   => 'butt',
                    'lineJoin'  => 'miter',
                    'miterLimit' => 0,
                    'dashArray' => [],
                    'dashPhase' => 0,
                    'lineColor' => (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                return $this->graph->getStartTransform()
                . $this->graph->getCircle(
                    $posx,
                    $posy + $this->toUnit($font['midpoint']),
                    $rad,
                    0,
                    360,
                    'D',
                    $style,
                    8,
                ) . $this->graph->getStopTransform();
            case 'square':
                if ($this->isunicode) {
                    $txti = "\u{25AA}";
                    break;
                }
                $len = $size / 2;
                $lspace += $len;
                $posx += $this->rtl ? $lspace : -$lspace;
                $style = [
                    'lineWidth' => 0,
                    'lineCap'   => 'butt',
                    'lineJoin'  => 'miter',
                    'miterLimit' => 0,
                    'dashArray' => [],
                    'dashPhase' => 0,
                    'lineColor' => (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                return $this->graph->getStartTransform()
                . $this->graph->getBasicRect(
                    $posx,
                    $posy + (($this->toUnit($font['height']) - $len) / 2),
                    $len,
                    $len,
                    'F',
                    $style,
                ) . $this->graph->getStopTransform();
            case 'img':
                // 1=>type, 2=>width, 3=>height, 4=>image.ext
                $lspace += \floatval($img[2]);
                $posx += $this->rtl ? $lspace : -$lspace;
                $imgtype = strtolower($img[1]);
                $imgwidth = \floatval($img[2]);
                $imgheight = \floatval($img[3]);
                $pageheight = $this->page->getPage()['height'];
                switch ($imgtype) {
                    case 'svg':
                        $svgid = $this->addSVG(
                            $img[4],
                            $posx,
                            $posy + (($this->toUnit($font['height']) - $imgheight) / 2),
                            $imgwidth,
                            $imgheight,
                            $pageheight,
                        );
                        return $this->getSetSVG($svgid);
                    default:
                        $imgid = $this->image->add($img[4]);
                        return $this->image->getSetImage(
                            $imgid,
                            $posx,
                            $posy + (($this->toUnit($font['height']) - $imgheight) / 2),
                            $imgwidth,
                            $imgheight,
                            $pageheight,
                        );
                }
            // ordered types
            case '1':
            case 'decimal':
                $txti = \strval($count);
                break;
            case 'decimal-leading-zero':
                $txti = \sprintf('%02d', $count);
                break;
            case 'i':
            case 'lower-roman':
                $txti = \strtolower($this->intToRoman($count));
                break;
            case 'I':
            case 'upper-roman':
                $txti = $this->intToRoman($count);
                break;
            case 'a':
            case 'lower-alpha':
            case 'lower-latin':
                $txti = \chr((97 + $count - 1) & 0xFF);
                break;
            case 'A':
            case 'upper-alpha':
            case 'upper-latin':
                $txti = \chr((65 + $count - 1) & 0xFF);
                break;
            case 'lower-greek':
                $txti = $this->uniconv->chr(945 + $count - 1);
                break;
            case 'hebrew':
                $txti = $this->uniconv->chr(1488 + $count - 1);
                break;
            case 'armenian':
                $txti = $this->uniconv->chr(1377 + $count - 1);
                break;
            case 'georgian':
                $txti = $this->uniconv->chr(4304 + $count - 1);
                break;
            case 'cjk-ideographic':
                $txti = $this->uniconv->chr(19968 + $count - 1);
                break;
            case 'hiragana':
                $txti = $this->uniconv->chr(12354 + $count - 1);
                break;
            case 'hiragana-iroha':
                $txti = $this->uniconv->chr(12356 + $count - 1);
                break;
            case 'katakana':
                $txti = $this->uniconv->chr(12450 + $count - 1);
                break;
            case 'katakana-iroha':
                $txti = $this->uniconv->chr(12452 + $count - 1);
                break;
            default:
                $txti = \strval($count);
                break;
        }

        if (empty($txti)) {
            return '';
        }

        // append dot separator for ordered list types only
        $unorderedTypes = ['disc', 'circle', 'square'];
        if (!\in_array($type, $unorderedTypes, true)) {
            $txti = $this->rtl ? '.' . $txti : $txti . '.';
        }

        $lspace += $this->getStringWidth($txti);
        $posx += $this->rtl ? $lspace : -$lspace;
        return $this->getTextLine($txti, $posx, $posy);
    }

    /**
     * Move to the next page region and returns the page ID.
     */
    protected function pageBreak(): int
    {
        $pid = $this->page->getPageId();
        $this->page->getNextRegion($pid);
        $cpid = $this->page->getPageId();
        if ($cpid > $pid) {
            $pid = $cpid;
            $this->setPageContext($pid);
        }
        return $pid;
    }

    /**
     * Initialize the temporary HTML cell rendering context.
     */
    protected function initHTMLCellContext(float $posx, float $posy, float $width, float $height): void
    {
        $basefont = $this->getHTMLBaseFontName();
        $this->htmlcellctx = [
            'originx' => $posx,
            'originy' => $posy,
            'maxwidth' => $width,
            'maxheight' => $height,
            'basefont' => $basefont,
        ];
        $this->htmlfontcache = [];
        $this->htmlliststack = [];
        $this->htmllistack = [];
        $this->htmltablestack = [];
        $this->htblcellctx = [];
        $this->htmllinkstack = [];
        $this->htmlprelevel = 0;
    }

    /**
     * Reset the temporary HTML cell rendering context.
     */
    protected function clearHTMLCellContext(): void
    {
        $this->htmlcellctx = [
            'originx' => 0.0,
            'originy' => 0.0,
            'maxwidth' => 0.0,
            'maxheight' => 0.0,
            'basefont' => 'helvetica',
        ];
        $this->htmlfontcache = [];
        $this->htmlliststack = [];
        $this->htmllistack = [];
        $this->htmltablestack = [];
        $this->htblcellctx = [];
        $this->htmllinkstack = [];
        $this->htmlprelevel = 0;
    }

    /**
     * Estimate the total rendered height for rows inside a table-header fragment.
     */
    protected function estimateHTMLTableHeadHeight(string $thead): float
    {
        if ($thead === '') {
            return 0.0;
        }

        $dom = $this->getHTMLDOM($thead);
        $height = 0.0;
        $rowheight = 0.0;
        $inrow = false;

        foreach ($dom as $elm) {
            if (empty($elm['tag']) || empty($elm['value']) || !\is_string($elm['value'])) {
                continue;
            }

            if ($elm['opening'] && ($elm['value'] === 'tr')) {
                $inrow = true;
                $rowheight = 0.0;
                continue;
            }

            if ($inrow && $elm['opening'] && (($elm['value'] === 'td') || ($elm['value'] === 'th'))) {
                $cellh = $this->getHTMLLineAdvance($elm)
                    + (float) $elm['padding']['T']
                    + (float) $elm['padding']['B']
                    + (float) $elm['margin']['T']
                    + (float) $elm['margin']['B'];
                if (!empty($elm['height']) && \is_numeric($elm['height'])) {
                    $cellh = \max($cellh, (float) $elm['height']);
                }
                $rowheight = \max($rowheight, $cellh);
                continue;
            }

            if (!$elm['opening'] && ($elm['value'] === 'tr') && $inrow) {
                if ($rowheight <= 0.0) {
                    $curfont = $this->font->getCurrentFont();
                    $rowheight = $this->toUnit((float) $curfont['height']);
                }
                $height += $rowheight;
                $rowheight = 0.0;
                $inrow = false;
            }
        }

        return $height;
    }

    /**
     * Replay stored table-header HTML at the current row position.
     */
    protected function replayHTMLTableHead(
        string $thead,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        if ($thead === '') {
            return '';
        }

        $out = $this->getHTMLCell($thead, $tpx, $tpy, $tpw, $tph);
        $theadh = $this->estimateHTMLTableHeadHeight($thead);
        if ($theadh > 0.0) {
            $tpy += $theadh;
            $this->resetHTMLLineCursor($tpx, $tpw);
        }

        return $out;
    }

    /**
     * Estimate the total rendered height for a table row starting at the given TR node.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function estimateHTMLTableRowHeight(array $dom, int $trkey): float
    {
        if (empty($dom[$trkey]) || empty($dom[$trkey]['tag']) || empty($dom[$trkey]['opening'])) {
            return 0.0;
        }

        $rowheight = 0.0;
        $depth = 0;
        $numel = \count($dom);
        for ($key = $trkey + 1; $key < $numel; ++$key) {
            $elm = $dom[$key];
            if (empty($elm['tag']) || empty($elm['value']) || !\is_string($elm['value'])) {
                continue;
            }

            if ($elm['opening'] && ($elm['value'] === 'tr')) {
                ++$depth;
                continue;
            }

            if (!$elm['opening'] && ($elm['value'] === 'tr')) {
                if ($depth === 0) {
                    break;
                }

                --$depth;
                continue;
            }

            if ($depth > 0) {
                continue;
            }

            if (!$elm['opening'] || (($elm['value'] !== 'td') && ($elm['value'] !== 'th'))) {
                continue;
            }

            $cellh = $this->getHTMLLineAdvance($elm)
                + (float) $elm['padding']['T']
                + (float) $elm['padding']['B']
                + (float) $elm['margin']['T']
                + (float) $elm['margin']['B'];
            if (!empty($elm['height']) && \is_numeric($elm['height'])) {
                $cellh = \max($cellh, (float) $elm['height']);
            }

            $rowheight = \max($rowheight, $cellh);
        }

        if ($rowheight <= 0.0) {
            $curfont = $this->font->getCurrentFont();
            $rowheight = $this->toUnit((float) $curfont['height']);
        }

        return $rowheight;
    }

    /**
     * Find the matching closing tag index for an opening DOM tag.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function findHTMLClosingTagIndex(array $dom, int $startkey): int
    {
        if (empty($dom[$startkey]['tag']) || empty($dom[$startkey]['opening']) || empty($dom[$startkey]['value'])) {
            return $startkey;
        }

        $tag = (string) $dom[$startkey]['value'];
        $depth = 0;
        $numel = \count($dom);
        for ($key = ($startkey + 1); $key < $numel; ++$key) {
            $elm = $dom[$key];
            if (empty($elm['tag']) || empty($elm['value']) || !\is_string($elm['value']) || ($elm['value'] !== $tag)) {
                continue;
            }

            if (!empty($elm['opening'])) {
                ++$depth;
                continue;
            }

            if ($depth === 0) {
                return $key;
            }

            --$depth;
        }

        return $startkey;
    }

    /**
     * Estimate the rendered height of plain HTML text for the available width.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function estimateHTMLTextHeight(string $text, array $elm, float $width): float
    {
        $text = $this->normalizeHTMLText($text);
        if ($text === '') {
            return 0.0;
        }

        $forcedir = ($elm['dir'] === 'rtl') ? 'R' : '';
        $this->getHTMLFontMetric($elm);
        $ordarr = [];
        $dim = self::DIM_DEFAULT;
        $this->prepareText($text, $ordarr, $dim, $forcedir);

        $lineadvance = $this->getHTMLLineAdvance($elm);
        if (($width <= 0.0) || ($ordarr === [])) {
            return $lineadvance;
        }

        $lines = $this->splitLines($ordarr, $dim, $this->toPoints($width));
        return \max($lineadvance, \count($lines) * $lineadvance);
    }

    /**
     * Estimate the height of a nobr subtree so it can be moved intact to a new region.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function estimateHTMLNobrHeight(array $dom, int $startkey, float $width): float
    {
        if (empty($dom[$startkey]) || empty($dom[$startkey]['tag']) || empty($dom[$startkey]['opening'])) {
            return 0.0;
        }

        $starttag = (string) $dom[$startkey]['value'];
        if ($starttag === 'tr') {
            return $this->estimateHTMLTableRowHeight($dom, $startkey);
        }

        $endkey = $this->findHTMLClosingTagIndex($dom, $startkey);
        if ($endkey <= $startkey) {
            return 0.0;
        }

        $height = 0.0;
        for ($key = ($startkey + 1); $key < $endkey; ++$key) {
            $elm = $dom[$key];

            if (empty($elm['tag'])) {
                $height += $this->estimateHTMLTextHeight((string) $elm['value'], $elm, $width);
                continue;
            }

            if (!empty($elm['opening'])) {
                if ($elm['value'] === 'br') {
                    $height += $this->getHTMLLineAdvance($elm);
                    continue;
                }

                if ($elm['value'] === 'img') {
                    $height += (!empty($elm['height']) && \is_numeric($elm['height']))
                        ? (float) $elm['height']
                        : $this->getHTMLLineAdvance($elm);
                    continue;
                }

                if (($elm['value'] === 'input') || ($elm['value'] === 'output')) {
                    $height += $this->estimateHTMLTextHeight($this->getHTMLInputDisplayValue($elm), $elm, $width);
                    continue;
                }

                if ($elm['value'] === 'select') {
                    $height += $this->estimateHTMLTextHeight($this->getHTMLSelectDisplayValue($elm), $elm, $width);
                    continue;
                }

                if ($elm['value'] === 'textarea') {
                    $value = (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value']))
                        ? $elm['attribute']['value']
                        : '';
                    $height += $this->estimateHTMLTextHeight($value, $elm, $width);
                    continue;
                }

                if (($elm['value'] === 'table') || ($elm['value'] === 'tablehead') || ($elm['value'] === 'thead')) {
                    $subheight = 0.0;
                    $tableend = $this->findHTMLClosingTagIndex($dom, $key);
                    for ($idx = $key; $idx <= $tableend; ++$idx) {
                        if (!empty($dom[$idx]['tag']) && !empty($dom[$idx]['opening']) && (($dom[$idx]['value'] ?? '') === 'tr')) {
                            $subheight += $this->estimateHTMLTableRowHeight($dom, $idx);
                        }
                    }
                    $height += $subheight;
                    $key = $tableend;
                    continue;
                }
            }

            if (
                !empty($elm['tag'])
                && \is_string($elm['value'])
                && \in_array($elm['value'], self::HTML_BLOCK_TAGS, true)
            ) {
                if (!empty($elm['opening'])) {
                    $height += (float) $elm['margin']['T'] + (float) $elm['padding']['T'];
                } else {
                    $height += (float) $elm['margin']['B'] + (float) $elm['padding']['B'];
                }
            }
        }

        if ($height <= 0.0) {
            $height = $this->getHTMLLineAdvance($dom[$startkey]);
        }

        return $height;
    }

    /**
     * Return the remaining vertical space in the current region or explicit cell box.
     */
    protected function getHTMLRemainingHeight(float $tpy): float
    {
        $region = $this->page->getRegion();
        $remaining = ((float) $region['RY'] + (float) $region['RH']) - $tpy;

        if ($this->htmlcellctx['maxheight'] > 0.0) {
            $remaining = \min(
                $remaining,
                ($this->htmlcellctx['originy'] + $this->htmlcellctx['maxheight']) - $tpy,
            );
        }

        return \max(0.0, $remaining);
    }

    /**
     * Break to the next page region when the required height does not fit.
     */
    protected function breakHTMLIfNeeded(
        float $requiredh,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        string $thead = '',
    ): string {
        if ($requiredh <= 0.0) {
            return '';
        }

        $region = $this->page->getRegion();
        $regiontop = (float) $region['RY'];
        $remaining = $this->getHTMLRemainingHeight($tpy);
        if (($requiredh <= ($remaining + 0.001)) || ($tpy <= ($regiontop + 0.001))) {
            return '';
        }

        $this->pageBreak();
        $region = $this->page->getRegion();
        $this->htmlcellctx['originy'] = (float) $region['RY'];
        $tpy = $this->htmlcellctx['originy'];
        $this->resetHTMLLineCursor($tpx, $tpw);

        if ($thead === '') {
            return '';
        }

        return $this->replayHTMLTableHead($thead, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Push a new active HTML link.
     */
    protected function pushHTMLLink(string $href): void
    {
        $this->htmllinkstack[] = $href;
    }

    /**
     * Pop the current active HTML link.
     */
    protected function popHTMLLink(): void
    {
        if ($this->htmllinkstack === []) {
            return;
        }

        \array_pop($this->htmllinkstack);
    }

    /**
     * Get the current active HTML link.
     */
    protected function getCurrentHTMLLink(): string
    {
        if ($this->htmllinkstack === []) {
            return '';
        }

        $href = $this->htmllinkstack[\count($this->htmllinkstack) - 1];
        return \is_string($href) ? $href : '';
    }

    /**
     * Returns the indentation width used by HTML lists.
     */
    protected function getHTMLListIndentWidth(): float
    {
        return $this->getStringWidth('000000');
    }

    /**
     * Resolve the list marker style for UL/OL elements.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLListMarkerType(array $elm, bool $ordered): string
    {
        $default = $ordered ? '#' : $this->ullidot;

        if (!empty($elm['attribute']['type']) && \is_string($elm['attribute']['type'])) {
            $type = \trim(\strtolower($elm['attribute']['type']));
            return ($type === '') ? $default : $type;
        }

        if (!empty($elm['listtype']) && \is_string($elm['listtype'])) {
            return $elm['listtype'];
        }

        return $default;
    }

    /**
     * Push a new list level onto the rendering stack.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function pushHTMLList(array $elm, bool $ordered): void
    {
        $start = 0;
        if (
            $ordered
            && !empty($elm['attribute']['start'])
            && \is_numeric($elm['attribute']['start'])
        ) {
            $start = ((int) $elm['attribute']['start']) - 1;
        }

        $this->htmlliststack[] = [
            'ordered' => $ordered,
            'type' => $this->getHTMLListMarkerType($elm, $ordered),
            'count' => $start,
        ];
    }

    /**
     * Remove the current list level from the rendering stack.
     */
    protected function popHTMLList(): void
    {
        if (!empty($this->htmlliststack)) {
            \array_pop($this->htmlliststack);
        }
    }

    /**
     * Returns the current list depth.
     */
    protected function getHTMLListDepth(): int
    {
        return \count($this->htmlliststack);
    }

    /**
     * Returns the next list marker counter for the current list level.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLListItemCounter(array $elm): int
    {
        $depth = $this->getHTMLListDepth();
        if ($depth < 1) {
            return 1;
        }

        $idx = $depth - 1;
        if (!$this->htmlliststack[$idx]['ordered']) {
            return 1;
        }

        ++$this->htmlliststack[$idx]['count'];
        if (
            !empty($elm['attribute']['value'])
            && \is_numeric($elm['attribute']['value'])
        ) {
            $this->htmlliststack[$idx]['count'] = (int) $elm['attribute']['value'];
        }

        return $this->htmlliststack[$idx]['count'];
    }

    /**
     * Returns the marker type for the current list level.
     */
    protected function getCurrentHTMLListMarkerType(): string
    {
        $depth = $this->getHTMLListDepth();
        if ($depth < 1) {
            return '#';
        }

        return $this->htmlliststack[$depth - 1]['type'];
    }

    /**
     * Return a stable base font family name for HTML rendering.
     */
    protected function getHTMLBaseFontName(): string
    {
        $curfont = $this->font->getCurrentFont();
        $fontname = (string) ($curfont['key'] ?? '');
        $fontname = \preg_replace('/[biudo]+$/i', '', $fontname) ?? $fontname;
        if ($fontname === '') {
            return 'helvetica';
        }

        $family = $this->font->getFontFamilyName($fontname);
        if (($family !== '') && !\preg_match('/[biudo]+$/i', $family)) {
            return $family;
        }

        return $fontname;
    }

    /**
     * Return the metric for the specified HTML node font.
     *
     * @param THTMLAttrib $elm DOM array element.
     *
    * @return array<string, mixed>
     */
    protected function getHTMLFontMetric(array $elm): array
    {
        $curfont = $this->font->getCurrentFont();
        $fontname = empty($elm['fontname'])
            ? (string) ($this->htmlcellctx['basefont'] ?? $this->getHTMLBaseFontName())
            : (string) $elm['fontname'];

        $stripped = \preg_replace('/[biudo]+$/i', '', $fontname) ?? '';
        if (($stripped !== '') && ($stripped !== $fontname)) {
            $strippedFamily = $this->font->getFontFamilyName($stripped);
            if ($strippedFamily !== '') {
                $fontname = $strippedFamily;
            }
        }

        $family = $this->font->getFontFamilyName($fontname);
        if (($family !== '') && !\preg_match('/[biudo]+$/i', $family)) {
            $fontname = $family;
        } elseif (!empty($this->htmlcellctx['basefont']) && \is_string($this->htmlcellctx['basefont'])) {
            $fontname = $this->htmlcellctx['basefont'];
        }
        $fontsize = (!empty($elm['fontsize']) && \is_numeric($elm['fontsize']))
            ? (int) \round((float) $elm['fontsize'])
            : (int) \round((float) $curfont['size']);
        $fontstyle = '';
        if (!empty($elm['fontstyle']) && \is_string($elm['fontstyle'])) {
            foreach (['B', 'I'] as $style) {
                if (\str_contains($elm['fontstyle'], $style)) {
                    $fontstyle .= $style;
                }
            }
        }

        $cachekey = $fontname . '|' . $fontstyle . '|' . (string) $fontsize;
        if (isset($this->htmlfontcache[$cachekey])) {
            // Re-insert if the current font pointer doesn't match the desired one,
            // so that getCurrentFont() returns the correct metrics for subsequent operations
            // (e.g. getTextCell width/height calculations).
            if ($this->font->getCurrentFontKey() !== $this->htmlfontcache[$cachekey]['key']) {
                $this->font->insert($this->pon, $fontname, $fontstyle, $fontsize);
            }

            return $this->htmlfontcache[$cachekey];
        }

        $metric = $this->font->insert($this->pon, $fontname, $fontstyle, $fontsize);
        $this->htmlfontcache[$cachekey] = $metric;

        return $metric;
    }

    /**
     * Build the font and color prefix for a text fragment.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLTextPrefix(array $elm): string
    {
        $font = $this->getHTMLFontMetric($elm);
        $color = empty($elm['fgcolor']) ? 'black' : (string) $elm['fgcolor'];
        $fontout = (isset($font['out']) && \is_string($font['out'])) ? $font['out'] : '';

        return $fontout . $this->color->getPdfColor($color);
    }

    /**
     * Return the current HTML text advance for line-based layout.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLLineAdvance(array $elm): float
    {
        $font = $this->getHTMLFontMetric($elm);
        $ratio = (!empty($elm['line-height']) && \is_numeric($elm['line-height']))
            ? (float) $elm['line-height']
            : 1.0;
        $fontheight = (isset($font['height']) && \is_numeric($font['height'])) ? (float) $font['height'] : 0.0;

        return $this->toUnit($fontheight * $ratio);
    }

    /**
     * Normalize plain HTML text before rendering it.
     */
    protected function normalizeHTMLText(string $text): string
    {
        if ($this->htmlprelevel > 0) {
            return \str_replace("\u{00A0}", ' ', $text);
        }

        $text = \preg_replace('/\s+/u', ' ', $text) ?? '';
        return $text === '' ? '' : (\trim($text) === '' ? ' ' : $text);
    }

    /**
     * Reset the cursor to the current HTML block origin.
     */
    protected function resetHTMLLineCursor(float &$tpx, float &$tpw): void
    {
        $tpx = $this->htmlcellctx['originx'];
        $tpw = $this->htmlcellctx['maxwidth'];
    }

    /**
     * Move the HTML cursor to the next line.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function moveHTMLToNextLine(array $elm, float &$tpx, float &$tpy, float &$tpw, float $extra = 0): void
    {
        $this->resetHTMLLineCursor($tpx, $tpw);
        $tpy += $this->getHTMLLineAdvance($elm) + $extra;
    }

    /**
     * Open a simple block-level HTML element.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function openHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        $hasinlinecontent = ($tpx > ($this->htmlcellctx['originx'] + 0.001));
        $lineadvance = $hasinlinecontent ? $this->getHTMLLineAdvance($elm) : 0.0;

        if ($hasinlinecontent || ($tpy > $this->htmlcellctx['originy'])) {
            $tpy += $lineadvance + (float) $elm['margin']['T'] + $this->getHTMLTagVSpace($elm, 0);
        }

        $tpx = $this->htmlcellctx['originx'] + (float) $elm['margin']['L'] + (float) $elm['padding']['L'];
        $tpw = $this->htmlcellctx['maxwidth'];
        if ($tpw > 0) {
            $tpw = \max(
                0.0,
                $tpw
                - (float) $elm['margin']['L']
                - (float) $elm['margin']['R']
                - (float) $elm['padding']['L']
                - (float) $elm['padding']['R']
            );
        }

        return '';
    }

    /**
     * Close a simple block-level HTML element.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function closeHTMLBlock(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        // When a block closes on the same line where inline text was rendered,
        // advance by one line height before applying bottom spacing.
        $hasinlinecontent = ($tpx > ($this->htmlcellctx['originx'] + 0.001));
        $lineadvance = $hasinlinecontent ? $this->getHTMLLineAdvance($elm) : 0.0;

        $this->resetHTMLLineCursor($tpx, $tpw);
        $tpy += $lineadvance + (float) $elm['margin']['B'] + (float) $elm['padding']['B'] + $this->getHTMLTagVSpace($elm, 1);

        return '';
    }

    /**
     * Shift the HTML cursor vertically for sub/sup blocks.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function shiftHTMLVerticalPosition(array $elm, float &$tpy, float $ratio): string
    {
        if (empty($elm['fontsize']) || !\is_numeric($elm['fontsize'])) {
            return '';
        }

        $tpy += ((float) $elm['fontsize'] * $ratio);
        return '';
    }

    /**
     * Render raw literal text using the current HTML style context.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function renderHTMLLiteralText(
        string $text,
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        if ($text === '') {
            return '';
        }

        $txtelm = $elm;
        $txtelm['tag'] = false;
        $txtelm['opening'] = false;
        $txtelm['self'] = false;
        $txtelm['value'] = $text;

        return $this->parseHTMLText($txtelm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Resolve display text for HTML input controls.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLInputDisplayValue(array $elm): string
    {
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $type = '';
        if (isset($attr['type']) && \is_string($attr['type'])) {
            $type = \strtolower(\trim($attr['type']));
        }

        if ($type === 'hidden') {
            return '';
        }

        if (($type === 'checkbox') || ($type === 'radio')) {
            return isset($attr['checked']) ? '[x]' : '[ ]';
        }

        if ($type === 'password') {
            $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
            return ($value === '') ? '' : \str_repeat('*', \mb_strlen($value, $this->encoding));
        }

        if (($type === 'submit') || ($type === 'button') || ($type === 'reset')) {
            if (isset($attr['value']) && \is_string($attr['value'])) {
                return $attr['value'];
            }

            return $type;
        }

        if (isset($attr['value']) && \is_string($attr['value'])) {
            return $attr['value'];
        }

        if (isset($attr['placeholder']) && \is_string($attr['placeholder'])) {
            return $attr['placeholder'];
        }

        return '';
    }

    /**
     * Resolve display text for HTML select controls.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function getHTMLSelectDisplayValue(array $elm): string
    {
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr) || empty($attr['opt']) || !\is_string($attr['opt'])) {
            return '';
        }

        $selected = (isset($attr['value']) && \is_string($attr['value']))
            ? $attr['value']
            : '';

        $entries = \array_filter(\explode('#!NwL!#', $attr['opt']), static fn ($opt): bool => ($opt !== ''));
        if ($entries === []) {
            return '';
        }

        $selectedvals = [];
        if ($selected !== '') {
            foreach (\explode(',', $selected) as $sel) {
                $sel = \trim($sel);
                if ($sel !== '') {
                    $selectedvals[] = $sel;
                }
            }
        }

        $fallback = '';
        $labels = [];
        $selectedlabels = [];
        foreach ($entries as $entry) {
            $isselected = false;
            if (\str_starts_with($entry, '#!SeL!#')) {
                $isselected = true;
                $entry = \substr($entry, 7);
            }

            if (\str_contains($entry, '#!TaB!#')) {
                $parts = \explode('#!TaB!#', $entry, 2);
                $value = $parts[0] ?? '';
                $label = $parts[1] ?? '';
            } else {
                $value = $entry;
                $label = $entry;
            }

            if (($fallback === '') && ($label !== '')) {
                $fallback = $label;
            }

            if (($value !== '') && ($label !== '')) {
                $labels[$value] = $label;
            }

            if ($isselected && ($label !== '')) {
                $selectedlabels[] = $label;
            }
        }

        if ($selectedvals !== []) {
            $out = [];
            foreach ($selectedvals as $val) {
                if (!isset($labels[$val])) {
                    continue;
                }

                $out[] = $labels[$val];
            }

            if ($out !== []) {
                return \implode(', ', $out);
            }
        }

        if ($selectedlabels !== []) {
            return \implode(', ', $selectedlabels);
        }

        return $fallback;
    }

    /**
     * Set custom vertical spacing for HTML block tags.
     *
     * Each tag entry has two positions:
     *   [0] = space added before the opening tag (top margin)
     *   [1] = space added after the closing tag (bottom margin)
     *
     * Each position is an array with optional keys:
     *   'h' => float (fixed height in user units)
     *   'n' => int   (number of line-heights to add)
     *
     * Example:
     *   $pdf->setHtmlVSpace(['p' => [['h' => 0, 'n' => 1], ['h' => 0, 'n' => 0]]]);
     *
     * @param array<string, array<int, array{h?: float|int, n?: int}>> $tagvs
     */
    public function setHtmlVSpace(array $tagvs): void
    {
        $this->tagvspaces = $tagvs;
    }

    /**
     * Return the extra vertical space (in user units) for the given tag at a specific position.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param int $position 0 = before open, 1 = after close.
     */
    protected function getHTMLTagVSpace(array $elm, int $position): float
    {
        $tag = (isset($elm['value']) && \is_string($elm['value'])) ? $elm['value'] : '';
        if (empty($this->tagvspaces[$tag][$position])) {
            return 0.0;
        }

        $tvs = $this->tagvspaces[$tag][$position];
        $lineheight = $this->getHTMLLineAdvance($elm);
        $height = (isset($tvs['h']) && \is_numeric($tvs['h'])) ? (float) $tvs['h'] : 0.0;
        $lines = (isset($tvs['n']) && \is_numeric($tvs['n'])) ? (int) $tvs['n'] : 0;
        return $height + $lineheight * $lines;
    }

    /**
     * Render an HTML image and advance the inline cursor.
     *
     * @param THTMLAttrib $elm DOM array element.
     */
    protected function renderHTMLImage(array $elm, float &$tpx, float &$tpy, float &$tpw): string
    {
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $alt = (isset($attr['alt']) && \is_string($attr['alt'])) ? $attr['alt'] : '[img]';
        if (empty($attr['src']) || !\is_string($attr['src'])) {
            $lineheight = $this->getHTMLLineAdvance($elm);
            return $this->renderHTMLLiteralText($alt, $elm, $tpx, $tpy, $tpw, $lineheight);
        }

        $src = $attr['src'];

        // Support base64 data URIs: data:<mime>;base64,<data>
        if (\preg_match('/^data:[^;]+;base64,(.+)$/s', $src, $matches)) {
            $decoded = \base64_decode($matches[1], true);
            if ($decoded !== false) {
                $src = '@' . $decoded;
            }
        }

        $lineheight = $this->getHTMLLineAdvance($elm);
        $width = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $lineheight;
        $height = (!empty($elm['height']) && \is_numeric($elm['height']))
            ? (float) $elm['height']
            : $lineheight;

        if (($width <= 0) || ($height <= 0)) {
            return '';
        }

        // Vertical alignment: top/middle/bottom (default)
        $align = (isset($attr['align']) && \is_string($attr['align']))
            ? \strtolower(\trim($attr['align']))
            : 'bottom';
        $imagey = $tpy;
        if ($height < $lineheight) {
            $imagey = match ($align) {
                'top'    => $tpy,
                'middle' => $tpy + ($lineheight - $height) / 2.0,
                default  => $tpy + $lineheight - $height,
            };
        }

        $out = '';
        try {
            $pageheight = $this->page->getPage()['height'];
            if (\str_ends_with(\strtolower($src), '.svg')) {
                $svgid = $this->addSVG($src, $tpx, $imagey, $width, $height, $pageheight);
                $out = $this->getSetSVG($svgid);
            } else {
                $imgid = $this->image->add($src);
                $out = $this->image->getSetImage($imgid, $tpx, $imagey, $width, $height, $pageheight);
            }
        } catch (\Throwable) {
            return $this->renderHTMLLiteralText($alt, $elm, $tpx, $tpy, $tpw, $height);
        }

        $tpx += $width;
        if ($this->htmlcellctx['maxwidth'] > 0) {
            $tpw = \max(0.0, $this->htmlcellctx['maxwidth'] - ($tpx - $this->htmlcellctx['originx']));
        }

        return $out;
    }

    /**
     * Build border styles for HTML table cells.
     *
     * @param THTMLAttrib $elm DOM array element.
     *
     * @return array<int|string, BorderStyle>
     */
    protected function getHTMLTableCellBorderStyles(array $elm): array
    {
        if (!isset($elm['border']) || !\is_array($elm['border']) || $elm['border'] === []) {
            return [];
        }

        /** @var array<string, BorderStyle> $border */
        $border = $elm['border'];
        $styles = [];

        if (!empty($border['LTRB'])) {
            $styles['all'] = $border['LTRB'];
            return $styles;
        }

        if (!empty($border['T'])) {
            $styles[0] = $border['T'];
        }
        if (!empty($border['R'])) {
            $styles[1] = $border['R'];
        }
        if (!empty($border['B'])) {
            $styles[2] = $border['B'];
        }
        if (!empty($border['L'])) {
            $styles[3] = $border['L'];
        }

        return $styles;
    }

    /**
     * Build fill style for HTML table cells.
     *
     * @param THTMLAttrib $elm DOM array element.
     *
     * @return ?BorderStyle
     */
    protected function getHTMLTableCellFillStyle(array $elm): ?array
    {
        if (empty($elm['bgcolor']) || !\is_string($elm['bgcolor'])) {
            return null;
        }

        return [
            'lineWidth' => 0,
            'lineCap' => 'butt',
            'lineJoin' => 'miter',
            'miterLimit' => 10,
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => '',
            'fillColor' => $elm['bgcolor'],
        ];
    }

    /**
     * Append a rendered HTML fragment to the active table-cell buffer when needed.
     */
    protected function captureHTMLTableCellBuffer(string $fragment): bool
    {
        if (($fragment === '') || empty($this->htblcellctx)) {
            return false;
        }

        $cellidx = \count($this->htblcellctx) - 1;
        $this->htblcellctx[$cellidx]['buffer'] .= $fragment;

        return true;
    }

    /**
     * Advance to the next free table column, skipping active row spans.
        *
        * @param THTMLTableState $table
     */
    protected function getHTMLTableNextFreeColumn(array $table): int
    {
        $colindex = $table['colindex'];
        while (($colindex < $table['cols']) && !empty($table['occupied'][$colindex])) {
            ++$colindex;
        }

        return $colindex;
    }

    /**
     * Compute the x-position of a given column in a table, accounting for
     * per-column widths and horizontal cellspacing.
     *
     * @param THTMLTableState $table
     */
    protected function getHTMLTableColX(array $table, int $colindex): float
    {
        $tox = $table['originx'];
        $cellspacing = $table['cellspacing'];
        for ($i = 0; $i < $colindex; ++$i) {
            $tox += ($table['colwidths'][$i] ?? $table['colwidth']) + $cellspacing;
        }

        return $tox;
    }

    /**
     * Compute the rendered width of a (possibly spanning) table cell,
     * accounting for per-column widths and horizontal cellspacing between spanned columns.
     *
     * @param THTMLTableState $table
     */
    protected function getHTMLTableColSpanWidth(array $table, int $colindex, int $colspan): float
    {
        $tcw = 0.0;
        $cellspacing = $table['cellspacing'];
        for ($i = 0; $i < $colspan; ++$i) {
            if ($i > 0) {
                $tcw += $cellspacing;
            }

            $tcw += ($table['colwidths'][$colindex + $i] ?? $table['colwidth']);
        }

        return $tcw;
    }

    /**
     * Pre-compute per-column content widths from the first body row of a table.
     *
     * Scans forward in the DOM from the table opening tag to find the first
     * non-thead TR row, then collects explicit `width` attributes from its
     * TD/TH cells. Columns without an explicit width retain their equal default.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param int   $tablekey       DOM index of the opening <table> tag.
     * @param int   $cols           Total number of columns.
     * @param float $availableWidth Width available for column content (table width minus inter-column cellspacing).
     *
     * @return array<int, float>
     */
    protected function computeHTMLTableColWidths(
        array $dom,
        int $tablekey,
        int $cols,
        float $availableWidth,
    ): array {
        $defaultWidth = ($cols > 0) ? ($availableWidth / $cols) : $availableWidth;
        $colwidths = \array_fill(0, $cols, $defaultWidth);

        $numel = \count($dom);
        $depth = 0;      // nesting depth for sub-tables
        $inFirstRow = false;
        $colid = 0;

        for ($key = $tablekey + 1; $key < $numel; ++$key) {
            $elm = $dom[$key];
            if (empty($elm['tag']) || !\is_string($elm['value'])) {
                continue;
            }

            $val = $elm['value'];

            // Track nested table depth so we don't accidentally read inner cells.
            if (!empty($elm['opening']) && $val === 'table') {
                ++$depth;
                continue;
            }

            if (empty($elm['opening']) && $val === 'table') {
                if ($depth > 0) {
                    --$depth;
                    continue;
                }

                break; // end of the table we're scanning
            }

            if ($depth > 0) {
                continue;
            }

            // Find the first body TR (skip thead rows marked with 'thead' property).
            if (!empty($elm['opening']) && $val === 'tr') {
                if (!empty($elm['thead'])) {
                    continue; // skip header row
                }

                if (!$inFirstRow) {
                    $inFirstRow = true;
                    $colid = 0;
                }

                continue;
            }

            if (empty($elm['opening']) && $val === 'tr' && $inFirstRow) {
                break; // done collecting from first body row
            }

            if (!$inFirstRow) {
                continue;
            }

            // Collect TD/TH explicit widths in the first body row.
            if (!empty($elm['opening']) && ($val === 'td' || $val === 'th')) {
                $colspan = 1;
                if (!empty($elm['attribute']['colspan']) && \is_numeric($elm['attribute']['colspan'])) {
                    $colspan = \max(1, (int) $elm['attribute']['colspan']);
                }

                $colspan = \min($colspan, $cols - $colid);

                if (!empty($elm['width']) && \is_numeric($elm['width']) && (float) $elm['width'] > 0) {
                    $cellw = (float) $elm['width'];
                    $percw = $cellw / $colspan;
                    for ($i = 0; $i < $colspan; ++$i) {
                        if ($colid + $i < $cols) {
                            $colwidths[$colid + $i] = $percw;
                        }
                    }
                }

                $colid += $colspan;
            }
        }

        return $colwidths;
    }

    /**
     * Render a resolved table cell using the final computed height.
     *
     * @param array<int|string, BorderStyle> $styles
    * @param ?BorderStyle $fillstyle
     */
    protected function renderHTMLTableCell(
        float $cellx,
        float $rowtop,
        float $cellw,
        float $cellh,
        array $styles,
        ?array $fillstyle,
        string $buffer,
    ): string {
        $out = '';

        if ($fillstyle !== null) {
            $out .= $this->graph->getStartTransform()
                . $this->graph->getBasicRect(
                    $cellx,
                    $rowtop,
                    $cellw,
                    $cellh,
                    'f',
                    $fillstyle,
                )
                . $this->graph->getStopTransform();
        }

        $out .= $buffer;

        if ($styles === []) {
            return $out;
        }

        $out .= $this->graph->getStartTransform();
        if (!empty($styles['all'])) {
            $out .= $this->graph->getBasicRect(
                $cellx,
                $rowtop,
                $cellw,
                $cellh,
                's',
                $styles['all'],
            );
        } else {
            $out .= $this->graph->getRect(
                $cellx,
                $rowtop,
                $cellw,
                $cellh,
                's',
                $styles,
            );
        }

        return $out . $this->graph->getStopTransform();
    }

    /**
     * Render HTML fragments for a cell and dispatch each fragment to a consumer.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param callable(string):void    $appendFragment
     */
    protected function renderHTMLCellFragments(
        array $dom,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        callable $appendFragment,
    ): void {
        $numel = \count($dom);
        $key = 0;
        $tabletheadmap = [];
        $nobrstack = [];

        foreach ($dom as $dnode) {
            if (
                empty($dnode['tag'])
                || !empty($dnode['opening'])
                || empty($dnode['value'])
                || ($dnode['value'] !== 'table')
                || empty($dnode['parent'])
                || !\is_int($dnode['parent'])
                || empty($dnode['thead'])
                || !\is_string($dnode['thead'])
            ) {
                continue;
            }

            $tabletheadmap[$dnode['parent']] = $dnode['thead'];
        }

        while ($key < $numel) {
            $elm = $dom[$key];

            if ($elm['tag']) { // HTML TAG
                if ($elm['opening']) { // opening tag
                    $didpagebreak = false;
                    if ($elm['hide']) {
                        $hidden_node_key = $key;
                        if ($elm['self']) {
                            ++$key; // skip just this self-closing tag
                        } else {
                            // skip this and all children tags
                            while (
                                ($key < $numel) && (
                                    !$dom[$key]['tag']
                                    || $dom[$key]['opening']
                                    || ($dom[$key]['parent'] != $hidden_node_key)
                                )
                            ) {
                                ++$key; // skip hidden objects
                            }
                            ++$key;
                        }
                        if ($key >= $numel) {
                            break;
                        }
                        $elm = $dom[$key];
                    }

                    if (!empty($elm['attribute']['pagebreak'])) {
                        $pid = $this->pageBreak();
                        $didpagebreak = true;
                        if ($elm['attribute']['pagebreak'] != 'true') {
                            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
                            if (
                                (($elm['attribute']['pagebreak'] == 'left') && $leftmode)
                                || (($elm['attribute']['pagebreak'] == 'right') && !$leftmode)
                            ) {
                                $this->pageBreak();
                                $didpagebreak = true;
                            }
                        }
                    }

                    if ($didpagebreak && ($elm['value'] === 'tr')) {
                        $parent = \is_int($elm['parent']) ? $elm['parent'] : 0;
                        $theadhtml = '';
                        if (
                            isset($dom[$parent])
                            && !empty($dom[$parent]['thead'])
                            && \is_string($dom[$parent]['thead'])
                        ) {
                            $theadhtml = $dom[$parent]['thead'];
                        } elseif (!empty($tabletheadmap[$parent]) && \is_string($tabletheadmap[$parent])) {
                            $theadhtml = $tabletheadmap[$parent];
                        }

                        if ($theadhtml !== '') {
                            $appendFragment(
                                $this->replayHTMLTableHead(
                                    $theadhtml,
                                    $tpx,
                                    $tpy,
                                    $tpw,
                                    $tph,
                                )
                            );
                        }
                    }

                    if ($elm['value'] === 'tr') {
                        $parent = \is_int($elm['parent']) ? $elm['parent'] : 0;
                        $theadhtml = '';
                        if (
                            isset($dom[$parent])
                            && !empty($dom[$parent]['thead'])
                            && \is_string($dom[$parent]['thead'])
                        ) {
                            $theadhtml = $dom[$parent]['thead'];
                        } elseif (!empty($tabletheadmap[$parent]) && \is_string($tabletheadmap[$parent])) {
                            $theadhtml = $tabletheadmap[$parent];
                        }

                        $appendFragment(
                            $this->breakHTMLIfNeeded(
                                $this->estimateHTMLTableRowHeight($dom, $key),
                                $tpx,
                                $tpy,
                                $tpw,
                                $tph,
                                $theadhtml,
                            )
                        );
                    }

                    if (!empty($elm['attribute']['id']) && \is_string($elm['attribute']['id'])) {
                        $name = \trim($elm['attribute']['id']);
                        if ($name !== '') {
                            $this->setNamedDestination($name, -1, $tpx, $tpy);
                        }
                    }

                    if (!empty($elm['attribute']['nobr']) && ($elm['attribute']['nobr'] === 'true')) {
                        if (!empty($nobrstack)) {
                            $elm['attribute']['nobr'] = '';
                        } elseif (!$elm['self']) {
                            if ($elm['value'] !== 'tr') {
                                $appendFragment(
                                    $this->breakHTMLIfNeeded(
                                        $this->estimateHTMLNobrHeight(
                                            $dom,
                                            $key,
                                            ($tpw > 0.0) ? $tpw : $this->htmlcellctx['maxwidth'],
                                        ),
                                        $tpx,
                                        $tpy,
                                        $tpw,
                                        $tph,
                                    )
                                );
                            }
                            $nobrstack[] = $elm['value'];
                        }
                    }

                    // Pre-compute per-column widths and spacing for table tags
                    // so that parseHTMLTagOPENtable can use them from $this->htmlpending*.
                    if (($elm['value'] === 'table') || ($elm['value'] === 'tablehead')) {
                        $tableCols = (!empty($elm['cols']) && \is_numeric($elm['cols']))
                            ? \max(1, (int) $elm['cols']) : 1;
                        $tableWidth = ($tpw > 0) ? $tpw : $this->htmlcellctx['maxwidth'];
                        $this->htmlpndcellspacing = (!empty($elm['attribute']['cellspacing'])
                            && \is_numeric($elm['attribute']['cellspacing']))
                            ? $this->toUnit($this->getUnitValuePoints($elm['attribute']['cellspacing']))
                            : 0.0;
                        $this->htmlpndcellpadding = (!empty($elm['attribute']['cellpadding'])
                            && \is_numeric($elm['attribute']['cellpadding']))
                            ? $this->toUnit($this->getUnitValuePoints($elm['attribute']['cellpadding']))
                            : 0.0;
                        $availableForCols = \max(
                            0.0,
                            $tableWidth - $this->htmlpndcellspacing * \max(0, $tableCols - 1)
                        );
                        $this->htmlpendingcolwidths = $this->computeHTMLTableColWidths(
                            $dom,
                            $key,
                            $tableCols,
                            $availableForCols,
                        );
                    }

                    $fragment = match ($elm['value']) {
                        'a'          => $this->parseHTMLTagOPENa($elm, $tpx, $tpy, $tpw, $tph),
                        'b'          => $this->parseHTMLTagOPENb($elm, $tpx, $tpy, $tpw, $tph),
                        'blockquote' => $this->parseHTMLTagOPENblockquote($elm, $tpx, $tpy, $tpw, $tph),
                        'body'       => $this->parseHTMLTagOPENbody($elm, $tpx, $tpy, $tpw, $tph),
                        'br'         => $this->parseHTMLTagOPENbr($elm, $tpx, $tpy, $tpw, $tph),
                        'dd'         => $this->parseHTMLTagOPENdd($elm, $tpx, $tpy, $tpw, $tph),
                        'del'        => $this->parseHTMLTagOPENdel($elm, $tpx, $tpy, $tpw, $tph),
                        'div'        => $this->parseHTMLTagOPENdiv($elm, $tpx, $tpy, $tpw, $tph),
                        'dl'         => $this->parseHTMLTagOPENdl($elm, $tpx, $tpy, $tpw, $tph),
                        'dt'         => $this->parseHTMLTagOPENdt($elm, $tpx, $tpy, $tpw, $tph),
                        'em'         => $this->parseHTMLTagOPENem($elm, $tpx, $tpy, $tpw, $tph),
                        'font'       => $this->parseHTMLTagOPENfont($elm, $tpx, $tpy, $tpw, $tph),
                        'form'       => $this->parseHTMLTagOPENform($elm, $tpx, $tpy, $tpw, $tph),
                        'h1'         => $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph),
                        'h2'         => $this->parseHTMLTagOPENh2($elm, $tpx, $tpy, $tpw, $tph),
                        'h3'         => $this->parseHTMLTagOPENh3($elm, $tpx, $tpy, $tpw, $tph),
                        'h4'         => $this->parseHTMLTagOPENh4($elm, $tpx, $tpy, $tpw, $tph),
                        'h5'         => $this->parseHTMLTagOPENh5($elm, $tpx, $tpy, $tpw, $tph),
                        'h6'         => $this->parseHTMLTagOPENh6($elm, $tpx, $tpy, $tpw, $tph),
                        'hr'         => $this->parseHTMLTagOPENhr($elm, $tpx, $tpy, $tpw, $tph),
                        'i'          => $this->parseHTMLTagOPENi($elm, $tpx, $tpy, $tpw, $tph),
                        'img'        => $this->parseHTMLTagOPENimg($elm, $tpx, $tpy, $tpw, $tph),
                        'input'      => $this->parseHTMLTagOPENinput($elm, $tpx, $tpy, $tpw, $tph),
                        'label'      => $this->parseHTMLTagOPENlabel($elm, $tpx, $tpy, $tpw, $tph),
                        'li'         => $this->parseHTMLTagOPENli($elm, $tpx, $tpy, $tpw, $tph),
                        'marker'     => $this->parseHTMLTagOPENmarker($elm, $tpx, $tpy, $tpw, $tph),
                        'ol'         => $this->parseHTMLTagOPENol($elm, $tpx, $tpy, $tpw, $tph),
                        'option'     => $this->parseHTMLTagOPENoption($elm, $tpx, $tpy, $tpw, $tph),
                        'output'     => $this->parseHTMLTagOPENoutput($elm, $tpx, $tpy, $tpw, $tph),
                        'p'          => $this->parseHTMLTagOPENp($elm, $tpx, $tpy, $tpw, $tph),
                        'pre'        => $this->parseHTMLTagOPENpre($elm, $tpx, $tpy, $tpw, $tph),
                        's'          => $this->parseHTMLTagOPENs($elm, $tpx, $tpy, $tpw, $tph),
                        'select'     => $this->parseHTMLTagOPENselect($elm, $tpx, $tpy, $tpw, $tph),
                        'small'      => $this->parseHTMLTagOPENsmall($elm, $tpx, $tpy, $tpw, $tph),
                        'span'       => $this->parseHTMLTagOPENspan($elm, $tpx, $tpy, $tpw, $tph),
                        'strike'     => $this->parseHTMLTagOPENstrike($elm, $tpx, $tpy, $tpw, $tph),
                        'strong'     => $this->parseHTMLTagOPENstrong($elm, $tpx, $tpy, $tpw, $tph),
                        'sub'        => $this->parseHTMLTagOPENsub($elm, $tpx, $tpy, $tpw, $tph),
                        'sup'        => $this->parseHTMLTagOPENsup($elm, $tpx, $tpy, $tpw, $tph),
                        'table'      => $this->parseHTMLTagOPENtable($elm, $tpx, $tpy, $tpw, $tph),
                        'tablehead'  => $this->parseHTMLTagOPENtablehead($elm, $tpx, $tpy, $tpw, $tph),
                        'tcpdf'      => $this->parseHTMLTagOPENtcpdf($elm, $tpx, $tpy, $tpw, $tph),
                        'td'         => $this->parseHTMLTagOPENtd($elm, $tpx, $tpy, $tpw, $tph),
                        'textarea'   => $this->parseHTMLTagOPENtextarea($elm, $tpx, $tpy, $tpw, $tph),
                        'th'         => $this->parseHTMLTagOPENth($elm, $tpx, $tpy, $tpw, $tph),
                        'thead'      => $this->parseHTMLTagOPENthead($elm, $tpx, $tpy, $tpw, $tph),
                        'tr'         => $this->parseHTMLTagOPENtr($elm, $tpx, $tpy, $tpw, $tph),
                        'tt'         => $this->parseHTMLTagOPENtt($elm, $tpx, $tpy, $tpw, $tph),
                        'u'          => $this->parseHTMLTagOPENu($elm, $tpx, $tpy, $tpw, $tph),
                        'ul'         => $this->parseHTMLTagOPENul($elm, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                    if (!$this->captureHTMLTableCellBuffer($fragment)) {
                        $appendFragment($fragment);
                    }

                    if ($elm['self'] && !empty($elm['attribute']['pagebreakafter'])) {
                        $pid = $this->pageBreak();
                        if ($elm['attribute']['pagebreakafter'] != 'true') {
                            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
                            if (
                                (($elm['attribute']['pagebreakafter'] == 'left') && $leftmode)
                                || (($elm['attribute']['pagebreakafter'] == 'right') && !$leftmode)
                            ) {
                                $this->pageBreak();
                            }
                        }
                    }
                } else { // closing tag
                    if (
                        !empty($nobrstack) && (
                        $nobrstack[\count($nobrstack) - 1] === $elm['value']
                        )
                    ) {
                        \array_pop($nobrstack);
                    }

                    $fragment = match ($elm['value']) {
                        'a'          => $this->parseHTMLTagCLOSEa($elm, $tpx, $tpy, $tpw, $tph),
                        'b'          => $this->parseHTMLTagCLOSEb($elm, $tpx, $tpy, $tpw, $tph),
                        'blockquote' => $this->parseHTMLTagCLOSEblockquote($elm, $tpx, $tpy, $tpw, $tph),
                        'body'       => $this->parseHTMLTagCLOSEbody($elm, $tpx, $tpy, $tpw, $tph),
                        'br'         => $this->parseHTMLTagCLOSEbr($elm, $tpx, $tpy, $tpw, $tph),
                        'dd'         => $this->parseHTMLTagCLOSEdd($elm, $tpx, $tpy, $tpw, $tph),
                        'del'        => $this->parseHTMLTagCLOSEdel($elm, $tpx, $tpy, $tpw, $tph),
                        'div'        => $this->parseHTMLTagCLOSEdiv($elm, $tpx, $tpy, $tpw, $tph),
                        'dl'         => $this->parseHTMLTagCLOSEdl($elm, $tpx, $tpy, $tpw, $tph),
                        'dt'         => $this->parseHTMLTagCLOSEdt($elm, $tpx, $tpy, $tpw, $tph),
                        'em'         => $this->parseHTMLTagCLOSEem($elm, $tpx, $tpy, $tpw, $tph),
                        'font'       => $this->parseHTMLTagCLOSEfont($elm, $tpx, $tpy, $tpw, $tph),
                        'form'       => $this->parseHTMLTagCLOSEform($elm, $tpx, $tpy, $tpw, $tph),
                        'h1'         => $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph),
                        'h2'         => $this->parseHTMLTagCLOSEh2($elm, $tpx, $tpy, $tpw, $tph),
                        'h3'         => $this->parseHTMLTagCLOSEh3($elm, $tpx, $tpy, $tpw, $tph),
                        'h4'         => $this->parseHTMLTagCLOSEh4($elm, $tpx, $tpy, $tpw, $tph),
                        'h5'         => $this->parseHTMLTagCLOSEh5($elm, $tpx, $tpy, $tpw, $tph),
                        'h6'         => $this->parseHTMLTagCLOSEh6($elm, $tpx, $tpy, $tpw, $tph),
                        'hr'         => $this->parseHTMLTagCLOSEhr($elm, $tpx, $tpy, $tpw, $tph),
                        'i'          => $this->parseHTMLTagCLOSEi($elm, $tpx, $tpy, $tpw, $tph),
                        'img'        => $this->parseHTMLTagCLOSEimg($elm, $tpx, $tpy, $tpw, $tph),
                        'input'      => $this->parseHTMLTagCLOSEinput($elm, $tpx, $tpy, $tpw, $tph),
                        'label'      => $this->parseHTMLTagCLOSElabel($elm, $tpx, $tpy, $tpw, $tph),
                        'li'         => $this->parseHTMLTagCLOSEli($elm, $tpx, $tpy, $tpw, $tph),
                        'marker'     => $this->parseHTMLTagCLOSEmarker($elm, $tpx, $tpy, $tpw, $tph),
                        'ol'         => $this->parseHTMLTagCLOSEol($elm, $tpx, $tpy, $tpw, $tph),
                        'option'     => $this->parseHTMLTagCLOSEoption($elm, $tpx, $tpy, $tpw, $tph),
                        'output'     => $this->parseHTMLTagCLOSEoutput($elm, $tpx, $tpy, $tpw, $tph),
                        'p'          => $this->parseHTMLTagCLOSEp($elm, $tpx, $tpy, $tpw, $tph),
                        'pre'        => $this->parseHTMLTagCLOSEpre($elm, $tpx, $tpy, $tpw, $tph),
                        's'          => $this->parseHTMLTagCLOSEs($elm, $tpx, $tpy, $tpw, $tph),
                        'select'     => $this->parseHTMLTagCLOSEselect($elm, $tpx, $tpy, $tpw, $tph),
                        'small'      => $this->parseHTMLTagCLOSEsmall($elm, $tpx, $tpy, $tpw, $tph),
                        'span'       => $this->parseHTMLTagCLOSEspan($elm, $tpx, $tpy, $tpw, $tph),
                        'strike'     => $this->parseHTMLTagCLOSEstrike($elm, $tpx, $tpy, $tpw, $tph),
                        'strong'     => $this->parseHTMLTagCLOSEstrong($elm, $tpx, $tpy, $tpw, $tph),
                        'sub'        => $this->parseHTMLTagCLOSEsub($elm, $tpx, $tpy, $tpw, $tph),
                        'sup'        => $this->parseHTMLTagCLOSEsup($elm, $tpx, $tpy, $tpw, $tph),
                        'table'      => $this->parseHTMLTagCLOSEtable($elm, $tpx, $tpy, $tpw, $tph),
                        'tablehead'  => $this->parseHTMLTagCLOSEtablehead($elm, $tpx, $tpy, $tpw, $tph),
                        'tcpdf'      => $this->parseHTMLTagCLOSEtcpdf($elm, $tpx, $tpy, $tpw, $tph),
                        'td'         => $this->parseHTMLTagCLOSEtd($elm, $tpx, $tpy, $tpw, $tph),
                        'textarea'   => $this->parseHTMLTagCLOSEtextarea($elm, $tpx, $tpy, $tpw, $tph),
                        'th'         => $this->parseHTMLTagCLOSEth($elm, $tpx, $tpy, $tpw, $tph),
                        'thead'      => $this->parseHTMLTagCLOSEthead($elm, $tpx, $tpy, $tpw, $tph),
                        'tr'         => $this->parseHTMLTagCLOSEtr($elm, $tpx, $tpy, $tpw, $tph),
                        'tt'         => $this->parseHTMLTagCLOSEtt($elm, $tpx, $tpy, $tpw, $tph),
                        'u'          => $this->parseHTMLTagCLOSEu($elm, $tpx, $tpy, $tpw, $tph),
                        'ul'         => $this->parseHTMLTagCLOSEul($elm, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                    if (!$this->captureHTMLTableCellBuffer($fragment)) {
                        $appendFragment($fragment);
                    }

                    if (!empty($elm['attribute']['pagebreakafter'])) {
                        $pid = $this->pageBreak();
                        if ($elm['attribute']['pagebreakafter'] != 'true') {
                            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
                            if (
                                (($elm['attribute']['pagebreakafter'] == 'left') && $leftmode)
                                || (($elm['attribute']['pagebreakafter'] == 'right') && !$leftmode)
                            ) {
                                $this->pageBreak();
                            }
                        }
                    }
                }
            } else { // Text Content
                $fragment = $this->parseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
                if (!$this->captureHTMLTableCellBuffer($fragment)) {
                    $appendFragment($fragment);
                }
            }

            ++$key;
        }
    }

    /**
     * Returns the PDF code to render an HTML block inside a rectangular cell.
     *
     * @param string      $html        HTML code to be processed.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param ?TCellDef   $cell        Optional to overwrite cell parameters for padding, margin etc.
     * @param array<int|string, BorderStyle> $styles Cell border styles (see: getCurrentStyleArray).
     *
     * @return string
     */
    public function getHTMLCell(
        string $html,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        ?array $cell = null,
        array $styles = [],
    ): string {
        $out = '';
        $dom = $this->getHTMLDOM($html);

        $drawcell = ($styles !== []);
        $cellctx = $this->adjustMinCellPadding($styles, $cell);

        $cellwidth = $width;
        if ($cellwidth <= 0.0) {
            $cellwidth = $this->toUnit(
                $this->cellMaxWidth(
                    $this->toPoints($posx),
                    $cellctx,
                )
            );
        }

        $offsetx = $this->toUnit((float) $cellctx['margin']['L'] + (float) $cellctx['padding']['L']);
        $offsety = $this->toUnit((float) $cellctx['margin']['T'] + (float) $cellctx['padding']['T']);
        $offsetw = $this->toUnit(
            (float) $cellctx['margin']['L']
            + (float) $cellctx['margin']['R']
            + (float) $cellctx['padding']['L']
            + (float) $cellctx['padding']['R']
        );
        $offseth = $this->toUnit(
            (float) $cellctx['margin']['T']
            + (float) $cellctx['margin']['B']
            + (float) $cellctx['padding']['T']
            + (float) $cellctx['padding']['B']
        );

        $contentx = $posx + $offsetx;
        $contenty = $posy + $offsety;
        $contentw = \max(0.0, $cellwidth - $offsetw);
        $contenth = ($height > 0) ? \max(0.0, $height - $offseth) : 0.0;

        $tpx = $contentx;
        $tpy = $contenty;
        $tpw = $contentw;
        $tph = $contenth;

        $this->initHTMLCellContext($contentx, $contenty, $contentw, $contenth);
        $this->renderHTMLCellFragments(
            $dom,
            $tpx,
            $tpy,
            $tpw,
            $tph,
            function (string $fragment) use (&$out): void {
                $out .= $fragment;
            },
        );

        if ($drawcell) {
            $boxheight = $height;
            if ($boxheight <= 0) {
                $curfont = $this->font->getCurrentFont();
                $lineh = $this->toUnit((float) $curfont['height']);
                $boxheight = \max($lineh, ($tpy - $contenty) + $lineh + $offseth);
            }

            $out = $this->drawCell(
                $this->toPoints($posx),
                $this->toYPoints($posy),
                $this->toPoints($cellwidth),
                $this->toPoints($boxheight),
                $styles,
                $cellctx,
            ) . $out;
        }

        $this->clearHTMLCellContext();

        return $out;
    }

    /**
     * Adds an HTML block inside a rectangular cell.
     * Accounts for automatic page and region breaks while appending
     * page-specific content directly to each affected page stream.
     *
     * @param string      $html        HTML code to be processed.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param ?TCellDef   $cell        Optional to overwrite cell parameters for padding, margin etc.
     * @param array<int|string, BorderStyle> $styles Cell border styles (see: getCurrentStyleArray).
     */
    public function addHTMLCell(
        string $html,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        ?array $cell = null,
        array $styles = [],
    ): void {
        $dom = $this->getHTMLDOM($html);
        $outbypage = [];
        $startpid = $this->page->getPageId();

        $appendFragment = function (string $fragment) use (&$outbypage): void {
            if ($fragment === '') {
                return;
            }

            $pid = $this->page->getPageId();
            if (!isset($outbypage[$pid])) {
                $outbypage[$pid] = '';
            }

            $outbypage[$pid] .= $fragment;
        };

        $drawcell = ($styles !== []);
        $cellctx = $this->adjustMinCellPadding($styles, $cell);

        $cellwidth = $width;
        if ($cellwidth <= 0.0) {
            $cellwidth = $this->toUnit(
                $this->cellMaxWidth(
                    $this->toPoints($posx),
                    $cellctx,
                )
            );
        }

        $offsetx = $this->toUnit((float) $cellctx['margin']['L'] + (float) $cellctx['padding']['L']);
        $offsety = $this->toUnit((float) $cellctx['margin']['T'] + (float) $cellctx['padding']['T']);
        $offsetw = $this->toUnit(
            (float) $cellctx['margin']['L']
            + (float) $cellctx['margin']['R']
            + (float) $cellctx['padding']['L']
            + (float) $cellctx['padding']['R']
        );
        $offseth = $this->toUnit(
            (float) $cellctx['margin']['T']
            + (float) $cellctx['margin']['B']
            + (float) $cellctx['padding']['T']
            + (float) $cellctx['padding']['B']
        );

        $contentx = $posx + $offsetx;
        $contenty = $posy + $offsety;
        $contentw = \max(0.0, $cellwidth - $offsetw);
        $contenth = ($height > 0) ? \max(0.0, $height - $offseth) : 0.0;

        $tpx = $contentx;
        $tpy = $contenty;
        $tpw = $contentw;
        $tph = $contenth;

        $this->initHTMLCellContext($contentx, $contenty, $contentw, $contenth);
        $this->renderHTMLCellFragments(
            $dom,
            $tpx,
            $tpy,
            $tpw,
            $tph,
            $appendFragment,
        );

        $multipage = (\count($outbypage) > 1);
        if ($drawcell && !($multipage && ($height <= 0))) {
            $boxheight = $height;
            if ($boxheight <= 0) {
                $curfont = $this->font->getCurrentFont();
                $lineh = $this->toUnit((float) $curfont['height']);
                $boxheight = \max($lineh, ($tpy - $contenty) + $lineh + $offseth);
            }

            $cellout = $this->drawCell(
                $this->toPoints($posx),
                $this->toYPoints($posy),
                $this->toPoints($cellwidth),
                $this->toPoints($boxheight),
                $styles,
                $cellctx,
            );

            if (!isset($outbypage[$startpid])) {
                $outbypage[$startpid] = '';
            }
            $outbypage[$startpid] = $cellout . $outbypage[$startpid];
        }

        $this->clearHTMLCellContext();

        foreach ($outbypage as $pid => $pageout) {
            if ($pageout === '') {
                continue;
            }

            $this->page->addContent($pageout, (int) $pid);
        }
    }

    /**
     * Process HTML Text (content between tags).
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx  Abscissa of upper-left corner.
     * @param float  $tpy  Ordinate of upper-left corner.
     * @param float  $tpw  Width.
     * @param float  $tph  Height.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLText(
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $text = $this->normalizeHTMLText($elm['value']);
        if ($text === '') {
            return '';
        }

        $style = empty($elm['fontstyle']) || !\is_string($elm['fontstyle']) ? '' : $elm['fontstyle'];
        $forcedir = ($elm['dir'] === 'rtl') ? 'R' : '';
        $halign = empty($elm['align']) ? ($this->rtl ? 'R' : 'L') : (string) $elm['align'];
        $availableWidth = ($tpw > 0) ? $tpw : 0.0;

        $out = $this->getHTMLTextPrefix($elm);
        $out .= $this->getTextCell(
            $text,
            $tpx,
            $tpy,
            $availableWidth,
            0,
            0,
            0,
            'T',
            $halign,
            static::ZEROCELL, // @phpstan-ignore argument.type
            [],
            (float) $elm['stroke'],
            0,
            0,
            0,
            true,
            (bool) $elm['fill'],
            ((float) $elm['stroke'] > 0),
            \str_contains($style, 'U'),
            \str_contains($style, 'D'),
            \str_contains($style, 'O'),
            (bool) $elm['clip'],
            false,
            $forcedir,
        );

        $bbox = $this->getLastBBox();
        $link = $this->getCurrentHTMLLink();
        if (($link !== '') && ($bbox['w'] > 0.0) && ($bbox['h'] > 0.0)) {
            $lnkid = $this->setLink($bbox['x'], $bbox['y'], $bbox['w'], $bbox['h'], $link);
            $this->page->addAnnotRef($lnkid, $this->page->getPageID());
        }

        $lineAdvance = $this->getHTMLLineAdvance($elm);
        $wrapped = ($bbox['h'] > ($lineAdvance + 0.001));
        if ($wrapped) {
            $this->resetHTMLLineCursor($tpx, $tpw);
            $tpy = $bbox['y'] + $bbox['h'];
            return $out;
        }

        $tpx = $bbox['x'] + $bbox['w'];
        if ($this->htmlcellctx['maxwidth'] > 0) {
            $tpw = \max(0.0, $this->htmlcellctx['maxwidth'] - ($tpx - $this->htmlcellctx['originx']));
        }
        $tpy = $bbox['y'];

        return $out;
    }

    // FUNCTIONS TO PROCESS HTML OPENING TAGS

    /**
     * Placeholder for opening-tag handlers.
     */

    /**
     * Process HTML opening tag <a>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENa(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $href = (!empty($elm['attribute']['href']) && \is_string($elm['attribute']['href']))
            ? $elm['attribute']['href']
            : '';
        $this->pushHTMLLink($href);

        return '';
    }

    /**
     * Process HTML opening tag <b>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENb(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <blockquote>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENblockquote(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <body>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENbody(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <br>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENbr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $this->moveHTMLToNextLine($elm, $tpx, $tpy, $tpw);
        return '';
    }

    /**
     * Process HTML opening tag <dd>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdd(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $indent = $this->getHTMLListIndentWidth();
        $tpx += $indent;
        if ($tpw > 0) {
            $tpw = \max(0.0, $tpw - $indent);
        }

        return $out;
    }

    /**
     * Process HTML opening tag <del>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdel(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <div>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdiv(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <dl>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdl(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <dt>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdt(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <em>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENem(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <font>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENfont(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <form>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENform(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <h1>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh1(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <h2>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh2(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h3>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh3(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h4>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh4(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h5>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh5(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h6>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh6(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <hr>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENhr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $availableWidth = ($tpw > 0) ? $tpw : $this->htmlcellctx['maxwidth'];
        $width = $availableWidth;
        if (!empty($elm['width']) && \is_numeric($elm['width']) && (float) $elm['width'] > 0) {
            $width = \min((float) $elm['width'], $availableWidth);
        }

        $strokeWidth = ((float) $elm['stroke'] > 0) ? (float) $elm['stroke'] : 0.2;
        if (!empty($elm['height']) && \is_numeric($elm['height']) && (float) $elm['height'] > 0) {
            $strokeWidth = (float) $elm['height'];
        }

        $lineY = $tpy + ($this->getHTMLLineAdvance($elm) / 2);
        $out .= $this->graph->getLine(
            $tpx,
            $lineY,
            $tpx + $width,
            $lineY,
            [
                'lineWidth' => $strokeWidth,
                'lineCap' => 'butt',
                'lineJoin' => 'miter',
                'dashArray' => [],
                'dashPhase' => 0,
                'lineColor' => empty($elm['fgcolor']) ? 'black' : (string) $elm['fgcolor'],
            ],
        );
        $this->moveHTMLToNextLine($elm, $tpx, $tpy, $tpw);

        return $out;
    }

    /**
     * Process HTML opening tag <i>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENi(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <img>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENimg(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->renderHTMLImage($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <input>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENinput(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $type = '';
        if (isset($attr['type']) && \is_string($attr['type'])) {
            $type = \strtolower(\trim($attr['type']));
        }

        if ($type === 'hidden') {
            return '';
        }

        $name = (isset($attr['name']) && \is_string($attr['name'])) ? $attr['name'] : ('input_' . \count($this->tagvspaces));
        $lineheight = $this->getHTMLLineAdvance($elm);
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $lineheight * 5;

        if ($type === 'checkbox') {
            $onvalue = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : 'Yes';
            $checked = isset($attr['checked']);
            $this->addFFCheckBox($name, $tpx, $tpy, $lineheight, $onvalue, $checked);
            $tpx += $lineheight;
        } elseif ($type === 'radio') {
            $onvalue = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : 'On';
            $checked = isset($attr['checked']);
            $this->addFFRadioButton($name, $tpx, $tpy, $lineheight, $onvalue, $checked);
            $tpx += $lineheight;
        } elseif ($type === 'submit' || $type === 'button' || $type === 'reset') {
            $caption = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : $type;
            $action = (isset($attr['onclick']) && \is_string($attr['onclick'])) ? $attr['onclick'] : '';
            $this->addFFButton($name, $tpx, $tpy, $fieldwidth, $lineheight, $caption, $action);
            $tpx += $fieldwidth;
        } else {
            // text, password, email, url, number, etc.
            $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
            if ($value === '' && isset($attr['placeholder']) && \is_string($attr['placeholder'])) {
                $value = $attr['placeholder'];
            }

            $opt = ['v' => $value];
            $jsp = ($type === 'password') ? ['password' => 'true'] : [];
            $this->addFFText($name, $tpx, $tpy, $fieldwidth, $lineheight, $opt, $jsp);
            $tpx += $fieldwidth;
        }

        if ($this->htmlcellctx['maxwidth'] > 0) {
            $tpw = \max(0.0, $this->htmlcellctx['maxwidth'] - $tpx + $this->htmlcellctx['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <label>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENlabel(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <li>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENli(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $depth = $this->getHTMLListDepth();
        if ($depth < 1) {
            return $out;
        }

        $font = $this->getHTMLFontMetric($elm);
        $indent = $this->getHTMLListIndentWidth();
        $counter = $this->getHTMLListItemCounter($elm);
        $markerType = $this->getCurrentHTMLListMarkerType();
        $baseline = $tpy + $this->toUnit((isset($font['ascent']) && \is_numeric($font['ascent'])) ? (float) $font['ascent'] : 0.0);
        $bulletx = $tpx + $indent;

        $out .= $this->getHTMLTextPrefix($elm);
        $out .= $this->getHTMLliBullet($depth, $counter, $bulletx, $baseline, $markerType);

        $tpx += $indent;
        if ($tpw > 0) {
            $tpw = \max(0.0, $tpw - $indent);
        }

        $this->htmllistack[] = [
            'originx' => $this->htmlcellctx['originx'],
            'maxwidth' => $this->htmlcellctx['maxwidth'],
        ];
        $this->htmlcellctx['originx'] = $tpx;
        $this->htmlcellctx['maxwidth'] = $tpw;

        return $out;
    }

    /**
     * Process HTML opening tag <marker>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENmarker(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <ol>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENol(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $this->pushHTMLList($elm, true);

        return $out;
    }

    /**
     * Process HTML opening tag <option>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENoption(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $label = '';
        if (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value'])) {
            $label = $elm['attribute']['value'];
        }

        return $this->renderHTMLLiteralText($label, $elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <output>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENoutput(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $label = '';
        if (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value'])) {
            $label = $elm['attribute']['value'];
        }

        return $this->renderHTMLLiteralText($label, $elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <p>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENp(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <pre>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENpre(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        ++$this->htmlprelevel;
        return $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <s>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENs(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <select>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENselect(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $name = (isset($attr['name']) && \is_string($attr['name'])) ? $attr['name'] : ('select_' . \count($this->tagvspaces));
        $lineheight = $this->getHTMLLineAdvance($elm);
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $lineheight * 5;

        // Parse packed option string into [value, label] pairs; track initial selected value.
        $values = [];
        $selectedValue = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
        if (!empty($attr['opt']) && \is_string($attr['opt'])) {
            $entries = \array_filter(\explode('#!NwL!#', $attr['opt']), static fn ($ent): bool => $ent !== '');
            foreach ($entries as $entry) {
                $isSelected = \str_starts_with($entry, '#!SeL!#');
                if ($isSelected) {
                    $entry = \substr($entry, 7);
                }

                if (\str_contains($entry, '#!TaB!#')) {
                    $parts = \explode('#!TaB!#', $entry, 2);
                    $values[] = [$parts[0], $parts[1]];
                    if ($isSelected && $selectedValue === '') {
                        $selectedValue = $parts[0];
                    }
                } else {
                    $values[] = [$entry, $entry];
                    if ($isSelected && $selectedValue === '') {
                        $selectedValue = $entry;
                    }
                }
            }
        }

        $this->addFFComboBox($name, $tpx, $tpy, $fieldwidth, $lineheight, $values, ['v' => $selectedValue]);
        $tpx += $fieldwidth;

        if ($this->htmlcellctx['maxwidth'] > 0) {
            $tpw = \max(0.0, $this->htmlcellctx['maxwidth'] - $tpx + $this->htmlcellctx['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <small>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsmall(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <span>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENspan(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <strike>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENstrike(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <strong>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENstrong(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <sub>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsub(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($elm, $tpy, 0.3);
    }

    /**
     * Process HTML opening tag <sup>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsup(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($elm, $tpy, -0.7);
    }

    /**
     * Process HTML opening tag <table>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtable(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);

        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $width = ($tpw > 0) ? $tpw : $this->htmlcellctx['maxwidth'];
        $cols = (!empty($elm['cols']) && \is_numeric($elm['cols'])) ? \max(1, (int) $elm['cols']) : 1;
        $colwidth = ($cols > 0) ? ($width / $cols) : $width;

        // Consume pre-computed column widths and spacing set by renderHTMLCellFragments.
        $colwidths = $this->htmlpendingcolwidths;
        $cellspacing = $this->htmlpndcellspacing;
        $cellpadding = $this->htmlpndcellpadding;
        $this->htmlpendingcolwidths = [];
        $this->htmlpndcellspacing = 0.0;
        $this->htmlpndcellpadding = 0.0;

        if (empty($colwidths)) {
            $colwidths = \array_fill(0, $cols, $colwidth);
        }

        $this->htmltablestack[] = [
            'originx' => $tpx,
            'originy' => $tpy,
            'width' => $width,
            'cols' => $cols,
            'colwidth' => $colwidth,
            'colwidths' => $colwidths,
            'cellspacing' => $cellspacing,
            'cellpadding' => $cellpadding,
            'rowtop' => $tpy,
            'rowheight' => 0.0,
            'colindex' => 0,
            'cells' => [],
            'occupied' => \array_fill(0, $cols, 0),
            'rowspans' => [],
        ];

        $tpw = $width;

        return $out;
    }

    /**
     * Process HTML opening tag <tablehead>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtablehead(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENtable($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Parse serialized TCPDF tag data payload.
     *
     * Supported format: <hlen>+<hash>+<urlencoded-json>
     * where JSON decodes to: {'m': string, 'p': array}
     *
     * @return ?array{m: string, p: array<int, mixed>}
     */
    protected function parseHTMLTcpdfSerializedData(string $data): ?array
    {
        $hpos = \strpos($data, '+');
        if (($hpos === false) || ($hpos <= 0)) {
            return null;
        }

        $hlen = (int) \substr($data, 0, $hpos);
        if ($hlen <= 0) {
            return null;
        }

        $encoded = \substr($data, $hpos + 2 + $hlen);
        if ($encoded === '') {
            return null;
        }

        $decoded = \json_decode(\urldecode($encoded), true);
        if (!\is_array($decoded) || empty($decoded['m']) || !\is_string($decoded['m'])) {
            return null;
        }

        $params = [];
        if (isset($decoded['p']) && \is_array($decoded['p'])) {
            $params = \array_values($decoded['p']);
        }

        return [
            'm' => $decoded['m'],
            'p' => $params,
        ];
    }

    /**
     * Check if a TCPDF HTML callback method is allowed.
     */
    protected function isAllowedHTMLTcpdfMethod(string $method): bool
    {
        if (\defined('K_ALLOWED_TCPDF_TAGS')) {
            $allowedtags = \constant('K_ALLOWED_TCPDF_TAGS');
            if (\is_string($allowedtags)) {
                return (\strpos($allowedtags, '|' . $method . '|') !== false);
            }
        }

        return \in_array(\strtolower($method), ['pagebreak', 'addpage'], true);
    }

    /**
     * Execute page-break style tcpdf callback and normalize cursor.
     */
    protected function executeHTMLTcpdfPageBreak(string $mode, float &$tpx, float &$tpw): void
    {
        $pid = $this->pageBreak();
        if ($mode !== 'true') {
            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
            if ((($mode == 'left') && $leftmode) || (($mode == 'right') && !$leftmode)) {
                $this->pageBreak();
            }
        }

        $this->resetHTMLLineCursor($tpx, $tpw);
    }

    /**
     * Process HTML opening tag <tcpdf>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtcpdf(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tpy, $tph);

        if (!empty($elm['attribute']['data']) && \is_string($elm['attribute']['data'])) {
            $tagdata = $this->parseHTMLTcpdfSerializedData($elm['attribute']['data']);
            if (($tagdata !== null) && $this->isAllowedHTMLTcpdfMethod($tagdata['m'])) {
                $method = \strtolower($tagdata['m']);
                if (($method === 'pagebreak') || ($method === 'addpage')) {
                    $mode = 'true';
                    if (!empty($elm['attribute']['pagebreak']) && \is_string($elm['attribute']['pagebreak'])) {
                        $mode = \strtolower(\trim($elm['attribute']['pagebreak']));
                    }
                    $this->executeHTMLTcpdfPageBreak($mode, $tpx, $tpw);
                    return '';
                }

                try {
                    $this->{$tagdata['m']}(...$tagdata['p']);
                } catch (\Throwable) {
                    return '';
                }

                $this->resetHTMLLineCursor($tpx, $tpw);
            }

            return '';
        }

        $method = '';
        if (!empty($elm['attribute']['method']) && \is_string($elm['attribute']['method'])) {
            $method = \strtolower(\trim($elm['attribute']['method']));
        }

        if (($method !== 'pagebreak') && ($method !== 'addpage')) {
            return '';
        }

        $mode = 'true';
        if (!empty($elm['attribute']['pagebreak']) && \is_string($elm['attribute']['pagebreak'])) {
            $mode = \strtolower(\trim($elm['attribute']['pagebreak']));
        }

        $this->executeHTMLTcpdfPageBreak($mode, $tpx, $tpw);

        return '';
    }

    /**
     * Process HTML opening tag <td>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtd(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);

        $tableidx = \count($this->htmltablestack) - 1;
        if ($tableidx < 0) {
            return '';
        }

        /** @var THTMLTableState $table */
        $table = $this->htmltablestack[$tableidx];

        $colindex = $this->getHTMLTableNextFreeColumn($table);
        $table['colindex'] = $colindex;
        $cols = $table['cols'];
        $remaining = \max(1, $cols - $colindex);
        $colspan = 1;
        $rowspan = 1;
        if (!empty($elm['attribute']['colspan']) && \is_numeric($elm['attribute']['colspan'])) {
            $colspan = (int) $elm['attribute']['colspan'];
        }
        if (!empty($elm['attribute']['rowspan']) && \is_numeric($elm['attribute']['rowspan'])) {
            $rowspan = (int) $elm['attribute']['rowspan'];
        }
        $colspan = \max(1, \min($remaining, $colspan));
        $rowspan = \max(1, $rowspan);

        $cellx = $this->getHTMLTableColX($table, $colindex);
        $cellw = $this->getHTMLTableColSpanWidth($table, $colindex, $colspan);
        $rowtop = $table['rowtop'];

        // Apply table cellpadding as default when the cell has no CSS padding.
        $cellpadding = $table['cellpadding'];
        if (
            $cellpadding > 0.0
            && (float) $elm['padding']['T'] === 0.0
            && (float) $elm['padding']['R'] === 0.0
            && (float) $elm['padding']['B'] === 0.0
            && (float) $elm['padding']['L'] === 0.0
        ) {
            $elm['padding'] = [
                'T' => $cellpadding,
                'R' => $cellpadding,
                'B' => $cellpadding,
                'L' => $cellpadding,
            ];
        }

        $originx = $cellx + (float) $elm['margin']['L'] + (float) $elm['padding']['L'];
        $originy = $rowtop + (float) $elm['margin']['T'] + (float) $elm['padding']['T'];
        $maxwidth = \max(
            0.0,
            $cellw
            - (float) $elm['margin']['L']
            - (float) $elm['margin']['R']
            - (float) $elm['padding']['L']
            - (float) $elm['padding']['R']
        );

        $this->htblcellctx[] = [
            'originx' => $this->htmlcellctx['originx'],
            'originy' => $this->htmlcellctx['originy'],
            'maxwidth' => $this->htmlcellctx['maxwidth'],
            'maxheight' => $this->htmlcellctx['maxheight'],
            'rowtop' => $rowtop,
            'cellx' => $cellx,
            'cellw' => $cellw,
            'bstyles' => $this->getHTMLTableCellBorderStyles($elm),
            'fillstyle' => $this->getHTMLTableCellFillStyle($elm),
            'rowspan' => $rowspan,
            'buffer' => '',
        ];

        $this->htmlcellctx['originx'] = $originx;
        $this->htmlcellctx['originy'] = $originy;
        $this->htmlcellctx['maxwidth'] = $maxwidth;

        $tpx = $originx;
        $tpy = $originy;
        $tpw = $maxwidth;

        $table['colindex'] += $colspan;

        if ($rowspan > 1) {
            for ($idx = $colindex; $idx < ($colindex + $colspan); ++$idx) {
                $table['occupied'][$idx] = \max(
                    $table['occupied'][$idx] ?? 0,
                    $rowspan - 1,
                );
            }
        }

        $this->htmltablestack[$tableidx] = $table;

        return '';
    }

    /**
     * Process HTML opening tag <textarea>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtextarea(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $name = (isset($attr['name']) && \is_string($attr['name'])) ? $attr['name'] : ('textarea_' . \count($this->tagvspaces));
        $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
        $lineheight = $this->getHTMLLineAdvance($elm);
        $rows = (isset($attr['rows']) && \is_numeric($attr['rows'])) ? \max(1, (int) $attr['rows']) : 3;
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : (($tpw > 0) ? $tpw : $this->htmlcellctx['maxwidth']);
        $fieldheight = $lineheight * $rows;

        $this->addFFText($name, $tpx, $tpy, $fieldwidth, $fieldheight, ['v' => $value], ['multiline' => 'true']);
        $tpx += $fieldwidth;

        if ($this->htmlcellctx['maxwidth'] > 0) {
            $tpw = \max(0.0, $this->htmlcellctx['maxwidth'] - $tpx + $this->htmlcellctx['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <th>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENth(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENtd($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <thead>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENthead(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagOPENtablehead($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <tr>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tph);

        $tableidx = \count($this->htmltablestack) - 1;
        if ($tableidx < 0) {
            return '';
        }

        $table = $this->htmltablestack[$tableidx];
        $table['rowtop'] = $tpy;
        $table['rowheight'] = 0.0;
        $table['colindex'] = 0;
        $table['cells'] = [];
        $this->htmltablestack[$tableidx] = $table;

        $tpx = $table['originx'];
        $tpw = $table['width'];

        return '';
    }

    /**
     * Process HTML opening tag <tt>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtt(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <u>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENu(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <ul>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENul(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->openHTMLBlock($elm, $tpx, $tpy, $tpw);
        $this->pushHTMLList($elm, false);

        return $out;
    }

    // FUNCTIONS TO PROCESS HTML CLOSING TAGS

    /**
     * Placeholder for closing-tag handlers.
     */
    /**
     * Process HTML closing tag </a>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEa(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        $value = '';
        if (!empty($elm['value']) && \is_string($elm['value'])) {
            $value = \strtolower($elm['value']);
        }

        if ($value === 'a') {
            $this->popHTMLLink();
        }

        return '';
    }

    /**
     * Process HTML closing tag </b>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEb(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </blockquote>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEblockquote(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </body>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEbody(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </br>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEbr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </dd>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdd(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </del>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdel(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </div>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdiv(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </dl>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdl(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </dt>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdt(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </em>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEem(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </font>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEfont(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </form>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEform(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </h1>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh1(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </h2>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh2(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h3>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh3(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h4>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh4(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h5>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh5(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h6>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh6(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </hr>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEhr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </i>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEi(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </img>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEimg(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </input>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEinput(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </label>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSElabel(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </li>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEli(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $out = $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
        $saved = \array_pop($this->htmllistack);
        if ($saved !== null) {
            $this->htmlcellctx['originx'] = $saved['originx'];
            $this->htmlcellctx['maxwidth'] = $saved['maxwidth'];
            $tpx = $saved['originx'];
            $tpw = $saved['maxwidth'];
        }

        return $out;
    }

    /**
     * Process HTML closing tag </marker>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEmarker(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </ol>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEol(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $this->popHTMLList();

        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </option>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEoption(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </output>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEoutput(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </p>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEp(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </pre>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEpre(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $this->htmlprelevel = \max(0, $this->htmlprelevel - 1);
        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </s>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEs(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </select>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEselect(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </small>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsmall(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </span>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEspan(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </strike>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEstrike(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </strong>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEstrong(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </sub>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsub(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($elm, $tpy, -0.3);
    }

    /**
     * Process HTML closing tag </sup>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsup(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($elm, $tpy, 0.7);
    }

    /**
     * Process HTML closing tag </table>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtable(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);

        if (empty($this->htmltablestack)) {
            return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
        }

        $table = \array_pop($this->htmltablestack);
        if ($table === null) {
            return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
        }

        $tpx = $table['originx'];
        $tpw = $table['width'];

        $out = '';
        foreach ($table['rowspans'] as $cell) {
            $height = ($cell['usedheight'] > 0.0) ? $cell['usedheight'] : $cell['contenth'];
            $out .= $this->renderHTMLTableCell(
                $cell['cellx'],
                $cell['rowtop'],
                $cell['cellw'],
                $height,
                $cell['bstyles'],
                $cell['fillstyle'],
                $cell['buffer'],
            );
        }

        return $out . $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </tablehead>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtablehead(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEtable($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </tcpdf>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtcpdf(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </td>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtd(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);

        $tableidx = \count($this->htmltablestack) - 1;
        $cellctx = \array_pop($this->htblcellctx);
        if (($tableidx < 0) || ($cellctx === null)) {
            return '';
        }

        $lineAdvance = $this->getHTMLLineAdvance($elm);
        $cellbottom = $tpy
            + $lineAdvance
            + (float) $elm['padding']['B']
            + (float) $elm['margin']['B'];
        $rowheight = \max(0.0, $cellbottom - $cellctx['rowtop']);
        if (!empty($elm['height']) && \is_numeric($elm['height'])) {
            $rowheight = \max($rowheight, (float) $elm['height']);
        }
        $table = $this->htmltablestack[$tableidx];
        if ($cellctx['rowspan'] > 1) {
            $table['rowspans'][] = [
                'cellx' => $cellctx['cellx'],
                'cellw' => $cellctx['cellw'],
                'rowtop' => $cellctx['rowtop'],
                'rowsremaining' => $cellctx['rowspan'],
                'usedheight' => 0.0,
                'contenth' => $rowheight,
                'bstyles' => $cellctx['bstyles'],
                'fillstyle' => $cellctx['fillstyle'],
                'buffer' => $cellctx['buffer'],
            ];
        } else {
            $table['cells'][] = [
                'cellx' => $cellctx['cellx'],
                'cellw' => $cellctx['cellw'],
                'contenth' => $rowheight,
                'bstyles' => $cellctx['bstyles'],
                'fillstyle' => $cellctx['fillstyle'],
                'buffer' => $cellctx['buffer'],
            ];
            $table['rowheight'] = \max(
                $table['rowheight'],
                $rowheight,
            );
        }
        $this->htmltablestack[$tableidx] = $table;

        $this->htmlcellctx['originx'] = $cellctx['originx'];
        $this->htmlcellctx['originy'] = $cellctx['originy'];
        $this->htmlcellctx['maxwidth'] = $cellctx['maxwidth'];
        $this->htmlcellctx['maxheight'] = $cellctx['maxheight'];

        $tpy = $cellctx['rowtop'];
        $tpx = $this->getHTMLTableColX($table, $table['colindex']);
        $tpw = \max(0.0, $table['width'] - ($tpx - $table['originx']));

        return '';
    }

    /**
     * Process HTML closing tag </textarea>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtextarea(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </th>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEth(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEtd($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </thead>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEthead(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        return $this->parseHTMLTagCLOSEtablehead($elm, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </tr>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtr(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tph);

        $tableidx = \count($this->htmltablestack) - 1;
        if ($tableidx < 0) {
            return '';
        }

        $table = $this->htmltablestack[$tableidx];
        $rowheight = $table['rowheight'];
        foreach ($table['rowspans'] as $cell) {
            $remainingHeight = \max(0.0, $cell['contenth'] - $cell['usedheight']);
            $rowsremaining = \max(1, $cell['rowsremaining']);
            $rowheight = \max($rowheight, ($remainingHeight / $rowsremaining));
        }
        if ($rowheight <= 0) {
            $curfont = $this->font->getCurrentFont();
            $rowheight = $this->toUnit((float) $curfont['height']);
        }

        $tpy = $table['rowtop'] + $rowheight + $table['cellspacing'];

        $out = '';
        if (!empty($table['cells'])) {
            foreach ($table['cells'] as $cell) {
                $out .= $this->renderHTMLTableCell(
                    $cell['cellx'],
                    $table['rowtop'],
                    $cell['cellw'],
                    $rowheight,
                    $cell['bstyles'],
                    $cell['fillstyle'],
                    $cell['buffer'],
                );
            }
        }

        $rowspans = [];
        foreach ($table['rowspans'] as $cell) {
            $cell['usedheight'] += $rowheight;
            --$cell['rowsremaining'];
            if ($cell['rowsremaining'] <= 0) {
                $out .= $this->renderHTMLTableCell(
                    $cell['cellx'],
                    $cell['rowtop'],
                    $cell['cellw'],
                    $cell['usedheight'],
                    $cell['bstyles'],
                    $cell['fillstyle'],
                    $cell['buffer'],
                );
                continue;
            }

            $rowspans[] = $cell;
        }

        foreach ($table['occupied'] as $idx => $remaining) {
            if ($remaining > 0) {
                $table['occupied'][$idx] = $remaining - 1;
            }
        }

        $table['rowtop'] = $tpy;
        $table['rowheight'] = 0.0;
        $table['colindex'] = 0;
        $table['cells'] = [];
        $table['rowspans'] = $rowspans;
        $this->htmltablestack[$tableidx] = $table;
        $tpx = $table['originx'];
        $tpw = $table['width'];

        return $out;
    }

    /**
     * Process HTML closing tag </tt>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtt(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </u>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEu(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($elm, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </ul>.
     *
     * @param THTMLAttrib $elm DOM array element.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEul(array $elm, float &$tpx, float &$tpy, float &$tpw, float &$tph): string
    {
        unset($tph);
        $this->popHTMLList();

        return $this->closeHTMLBlock($elm, $tpx, $tpy, $tpw);
    }
}
