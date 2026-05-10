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
 * @phpstan-import-type BorderStyleOpt from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCSSBorderSpacing from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCSSData from \Com\Tecnick\Pdf\CSS
 * @phpstan-import-type TCellDef from \Com\Tecnick\Pdf\Cell
 * @phpstan-import-type TCellBound from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TTextDims from \Com\Tecnick\Pdf\Font\Stack
 * @phpstan-import-type TAnnotOpts from \Com\Tecnick\Pdf\Output
 * @phpstan-type THTMLTableCell array{
 *     cellx: float,
 *     cellw: float,
 *     colindex: int,
 *     colspan: int,
 *     contenth: float,
 *     valign: string,
 *     bstyles: array<int|string, BorderStyle>,
 *     fillstyle: ?BorderStyle,
 *     buffer: string
 * }
 * @phpstan-type THTMLTableRowspanCell array{
 *     cellx: float,
 *     cellw: float,
 *     colindex: int,
 *     colspan: int,
 *     rowtop: float,
 *     rowsremaining: int,
 *     usedheight: float,
 *     contenth: float,
 *     valign: string,
 *     bstyles: array<int|string, BorderStyle>,
 *     fillstyle: ?BorderStyle,
 *     buffer: string
 * }
 * @phpstan-type THTMLTableState array{
 *     originx: float,
 *     originy: float,
 *     width: float,
 *     dir: string,
 *     cols: int,
 *     colwidth: float,
 *     colwidths: array<int, float>,
 *     cellspacingh: float,
 *     cellspacingv: float,
 *     cellpadding: float,
 *     collapse: bool,
 *     hascellborders: bool,
 *     prevrowbottom: array<int, BorderStyle>,
 *     rowtop: float,
 *     rowheight: float,
 *     colindex: int,
 *     cells: array<int, THTMLTableCell>,
 *     occupied: array<int, int>,
 *     rowspans: array<int, THTMLTableRowspanCell>,
 *     regionoffset: float
 * }
 * @phpstan-type THTMLTableCellContext array{
 *     originx: float,
 *     originy: float,
 *     maxwidth: float,
 *     maxheight: float,
 *     lineadvance: float,
 *     linebottom: float,
 *     lineascent: float,
 *     linewordspacing: float,
 *     linewrapped: bool,
 *     rowtop: float,
 *     cellx: float,
 *     cellw: float,
 *     colindex: int,
 *     colspan: int,
 *     bstyles: array<int|string, BorderStyle>,
 *     fillstyle: ?BorderStyle,
 *     rowspan: int,
 *     valign: string,
 *     buffer: string
 * }
 *
 * @phpstan-type THTMLAttrib array{
 *     'align': bool|float|string,
 *     'attribute': array<string, string>,
 *     'caption-top-html'?: string,
 *     'caption-bottom-html'?: string,
 *     'caption-side': string,
 *     'bgcolor': bool|float|string,
 *     'block': bool,
 *     'border-collapse': string,
 *     'border-spacing'?: TCSSBorderSpacing,
 *     'border': array<string, BorderStyle>,
 *     'clip': bool,
 *     'cols': int,
 *     'content': string,
 *     'cssdata': array<string, TCSSData>,
 *     'csssel': array<string>,
 *     'clear': string,
 *     'dir': string,
 *     'display': string,
 *     'empty-cells': string,
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
 *     'list-style-position': string,
 *     'listtype': string,
 *     'float': string,
 *     'margin': array<string, float>,
 *     'opening': bool,
 *     'overflow-wrap'?: string,
 *     'padding': array<string, float>,
 *     'parent': int,
 *     'pendingcellpadding'?: float,
 *     'pendingcellspacingh'?: float,
 *     'pendingcellspacingv'?: float,
 *     'pendingcolwidths'?: array<int, float>,
 *     'position': string,
 *     'rows': int,
 *     'self': bool,
 *     'stroke': float,
 *     'strokecolor': string,
 *     'table-layout': string,
 *     'style': array<string, string>,
 *     'list-style-image'?: string,
 *     'tag': bool,
 *     'text-indent': float,
 *     'text-transform': string,
 *     'thead': string,
 *     'trids': array<int>,
 *     'valign': string,
 *     'value': string,
 *     'white-space': string,
 *     'word-break'?: string,
 *     'word-spacing': float,
 *     'width': float,
 *     'x': float,
 *     'y': float,
 * }
 *
 * @phpstan-type THTMLBlockBuf array{
 *     openkey: int,
 *     bx: float,
 *     by: float,
 *     bw: float,
 *     buffer: string
 * }
 *
 * @phpstan-type THTMLRenderContext array{
 *     'cellctx': array{
 *         originx: float,
 *         originy: float,
 *         lineoriginx: float,
 *         maxwidth: float,
 *         maxheight: float,
 *         floatrowleftw: float,
 *         floatrowrightw: float,
 *         floatrowtop: float,
 *         floatrowbottom: float,
 *         lineadvance: float,
 *         linebottom: float,
 *         lineascent: float,
 *         linewordspacing: float,
 *         linewrapped: bool,
 *         textindentapplied: bool,
 *         pendingblockmarginb: float,
 *         regionoffset?: float,
 *         basefont: string
 *     },
 *     'currentkey'?: int,
 *     'fontcache': array<string, array<string, mixed>>,
 *     'liststack': array<int, array{
 *         ordered: bool,
 *         type: string,
 *         count: int,
 *         indent: float
 *     }>,
 *     'tablestack': array<int, THTMLTableState>,
 *     'bcellctx': array<int, THTMLTableCellContext>,
 *     'blockbuf': array<int, THTMLBlockBuf>,
 *     'linkstack': array<int, string>,
 *     'listack': array<int, array{
 *         originx: float,
 *         maxwidth: float
 *     }>,
 *     'prelevel': int,
 *     'dom': array<int, THTMLAttrib>,
 * }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class HTML extends \Com\Tecnick\Pdf\JavaScript
{
    /**
     * Preserve SHY during HTML text rendering only.
     */
    protected bool $htmlRenderSoftHyphen = false;

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
        'caption',
        'dd',
        'div',
        'dl',
        'figure',
        'figcaption',
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
        'th',
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
        'col',
        'hr',
        'img',
        'input',
        'link',
        'meta',
    ];

    /**
     * Supported CSS display values recognized by the HTML style parser.
     *
     * @var array<string>
     */
    protected const HTML_SUPPORTED_DISPLAY_VALUES = [
        'none',
        'inline',
        'block',
        'inline-block',
        'list-item',
        'table',
        'inline-table',
        'table-row-group',
        'table-header-group',
        'table-footer-group',
        'table-row',
        'table-column-group',
        'table-column',
        'table-cell',
        'table-caption',
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
     * DOM properties that are inherited from parent elements.
     *
     * @var array<string>
     */
    protected const HTML_INHERITED_PROPERTIES = [
        'align',
        'bgcolor',
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
        'list-style-image',
        'list-style-position',
        'listtype',
        'overflow-wrap',
        'stroke',
        'strokecolor',
        'text-indent',
        'text-transform',
        'white-space',
        'word-break',
        'word-spacing',
    ];

    /**
     * HTML valid (supported) tags.
     *
     * @var string
     */
    protected const HTML_VALID_TAGS = '<marker/><a><b><blockquote><body><br><br/><code><dd><del><div><dl><dt><em>'
        . '<caption><col><colgroup><figure><figcaption><font><form>'
        . '<h1><h2><h3><h4><h5><h6><hr><hr/><i><img><input><label>'
        . '<li><ol><optgroup><option>'
        . '<p><pre><s><select><small><span><strike><strong><sub><sup><table><tablehead>'
        . '<tcpdf><td><textarea><tfoot><th><thead><tr><tt><u><ul>';

    /**
     * HTML generic regexp tag pattern.
     *
     * @var string
     */
    protected const HTML_TAG_PATTERN = '/(<[^>]+>)/';

    /**
     * Verical shift ratio for HTML sub tag.
     *
     * @var float
     */
    protected const VERT_SHIFT_SUB = 0.1;

    /**
     * Verical shift ratio for HTML sup tag.
     *
     * @var float
     */
    protected const VERT_SHIFT_SUP = 0.3;

    /**
     * Width wrapping tolerance in user units (mm).
     * Accounts for floating-point rounding errors in width calculations.
     *
     * @var float
     */
    protected const WIDTH_TOLERANCE = 0.01;

    /**
     * Minimum word length (in characters) to apply break-point insertion for word-break/overflow-wrap.
     * Words shorter than this are left unbroken to preserve readability.
     *
     * @var int
     */
    protected const MIN_WORD_LENGTH_FOR_BREAKS = 20;

    /**
     * Character interval for inserting break-point opportunities (zero-width spaces) in long words.
     * A break point is inserted every N characters when word-break or overflow-wrap rules are active.
     *
     * @var int
     */
    protected const BREAK_INTERVAL = 2;

    /**
     * Engine default values used to resolve CSS `initial` in inline style parsing.
     *
     * Values are expressed as CSS declaration strings so existing property parsers
     * can process them without special-case branching.
     *
     * @var array<string, string>
     */
    protected const HTML_STYLE_INITIAL_VALUE_MAP = [
        'direction' => 'ltr',
        'display' => 'inline',
        'font-family' => 'helvetica',
        'list-style' => 'none',
        'list-style-type' => 'disc',
        'list-style-position' => 'outside',
        'list-style-image' => 'none',
        'text-indent' => '0',
        'text-transform' => 'none',
        'white-space' => 'normal',
        'word-break' => 'normal',
        'overflow-wrap' => 'normal',
        'font-size' => '10pt',
        'font-stretch' => 'normal',
        'letter-spacing' => 'normal',
        'word-spacing' => 'normal',
        'line-height' => 'normal',
        'font-weight' => 'normal',
        'font-style' => 'normal',
        'color' => 'black',
        'background-color' => 'transparent',
        'background' => 'transparent',
        'text-decoration' => 'none',
        'width' => '0',
        'height' => '0',
        'text-align' => 'left',
        'vertical-align' => 'baseline',
        'padding' => '0',
        'padding-top' => '0',
        'padding-right' => '0',
        'padding-bottom' => '0',
        'padding-left' => '0',
        'margin' => '0',
        'margin-top' => '0',
        'margin-right' => '0',
        'margin-bottom' => '0',
        'margin-left' => '0',
        'border' => 'none',
        'border-top' => 'none',
        'border-right' => 'none',
        'border-bottom' => 'none',
        'border-left' => 'none',
        'border-width' => '0',
        'border-style' => 'none',
        'border-color' => 'black',
        'border-collapse' => 'separate',
        'border-spacing' => '0',
        'overflow' => 'visible',
        'page-break-inside' => 'auto',
        'page-break-before' => 'auto',
        'page-break-after' => 'auto',
        'break-inside' => 'auto',
        'break-before' => 'auto',
        'break-after' => 'auto',
    ];


    /**
     * Typoe of symbol used for HTML unordered list items.
     *
     * @var string
     */
    protected string $ullidot = '!';

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

        // tags: select / optgroup / option
        $html = \preg_replace_callback(
            "'<select([^\>]*)>(.*?)</select>'si",
            static function (array $selm): string {
                $selattrs = (string) $selm[1];
                $inner = (string) $selm[2];
                $packed = '';
                $groupLabel = '';
                $tokenPattern =
                    '/<optgroup([^\>]*)>|<\/optgroup>|<option([^\>]*)>(.*?)<\/option>/si';

                if (\preg_match_all($tokenPattern, $inner, $tokens, PREG_SET_ORDER) > 0) {
                    foreach ($tokens as $tok) {
                        $toktxt = (string) $tok[0];
                        if ($toktxt === '') {
                            continue;
                        }

                        if (\str_starts_with(\strtolower($toktxt), '</optgroup')) {
                            $groupLabel = '';
                            continue;
                        }

                        if (\str_starts_with(\strtolower($toktxt), '<optgroup')) {
                            $gattrs = (isset($tok[1]) && \is_string($tok[1])) ? $tok[1] : '';
                            $groupLabel = '';
                            if (\preg_match('/[\s]+label[\s]*=[\s]*"([^"]*)"/si', $gattrs, $gmatch) > 0) {
                                $groupLabel = $gmatch[1];
                            } elseif (\preg_match('/[\s]+label[\s]*=[\s]*\'([^\']*)\'/si', $gattrs, $gmatch) > 0) {
                                $groupLabel = $gmatch[1];
                            } elseif (\preg_match('/[\s]+label[\s]*=[\s]*([^\s>]+)/si', $gattrs, $gmatch) > 0) {
                                $groupLabel = $gmatch[1];
                            }
                            continue;
                        }

                        $oattrs = (isset($tok[2]) && \is_string($tok[2])) ? $tok[2] : '';
                        $label = (isset($tok[3]) && \is_string($tok[3])) ? $tok[3] : '';
                        if ($groupLabel !== '') {
                            $label = $groupLabel . ' - ' . $label;
                        }

                        $value = '';
                        if (\preg_match('/[\s]+value[\s]*=[\s]*"([^"]*)"/si', $oattrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        } elseif (\preg_match('/[\s]+value[\s]*=[\s]*\'([^\']*)\'/si', $oattrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        } elseif (\preg_match('/[\s]+value[\s]*=[\s]*([^\s>]+)/si', $oattrs, $valmatch) > 0) {
                            $value = $valmatch[1];
                        }

                        $selPattern = '/(^|[\s])selected([\s]*=[\s]*("[^"]*"|\'[^\']*\'|[^\s>]+))?([\s]|$)/si';
                        $selected = (\preg_match($selPattern, $oattrs) > 0);
                        $prefix = $selected ? '#!SeL!#' : '';

                        if ($value !== '') {
                            $packed .= $prefix . $value . '#!TaB!#' . $label . '#!NwL!#';
                        } else {
                            $packed .= $prefix . $label . '#!NwL!#';
                        }
                    }
                }

                if (\str_ends_with($packed, '#!NwL!#')) {
                    $packed = \substr($packed, 0, -7);
                }

                return '<select' . $selattrs . ' opt="' . $packed . '" />';
            },
            $html,
        ) ?? '';

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
            'caption-side' => 'top',
            'border-collapse' => 'separate',
            'border-spacing' => ['H' => 0.0, 'V' => 0.0],
            'border' => [],
            //'border-collapse' => '',//
            //'border-spacing' => '',//
            'clip' => false,
            'clear' => 'none',
            //'color' => '',//
            'cols' => 0,
            'content' => '',
            'cssdata' => [],
            'csssel' => [],
            //'cursor' => '',//
            'dir' => $this->rtl ? 'rtl' : 'ltr',
            //'direction' => '',//
            'display' => 'inline',
            'elkey' => 0,
            'empty-cells' => 'show',
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
            'list-style-image' => '',
            'list-style-position' => 'outside',
            //'list-style-type' => '',//
            'listtype' => '',
            'float' => 'none',
            'margin' => self::ZEROCELLBOUND,
            'opening' => false,
            //'orphans' => '',//
            'padding' => self::ZEROCELLBOUND,
            //'page' => '',//
            //'page-break-inside' => '',//
            'parent' => 0,
            'position' => 'static',
            //'quotes' => '',//
            'rows' => 0,
            'self' => false,
            //'speak' => '',//
            //'speak-header' => '',//
            'stroke' => 0.0,
            'strokecolor' => 'black',
            'table-layout' => 'auto',
            'style' => [],
            'tag' => false,
            //'text-align' => '',//
            'text-indent' => 0.0,
            'text-transform' => '',
            'thead' => '',
            'trids' => [],
            'valign' => 'top',
            'value' => '',
            //'volume' => '',//
            'white-space' => 'normal',
            //'widows' => '',//
            'word-spacing' => 0.0,
            'width' => 0.0,
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
                $dom[$key]['display'] = $dom[$key]['block'] ? 'block' : 'inline';
                /** @var array<int, THTMLAttrib> $dom */
                if ($element[0] == '/') { // closing tag
                    array_pop($level);
                    // @phpstan-ignore-next-line parameterByRef.type
                    $this->processHTMLDOMClosingTag(
                        $dom,
                        $elm,
                        $key,
                        $parent,
                        $cssarray,
                    );
                } else { // opening or self-closing html tag
                    // @phpstan-ignore-next-line parameterByRef.type
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

        if (!empty($css)) {
            // @phpstan-ignore-next-line parameterByRef.type
            $this->recomputeHTMLDOMCSSAgainstFinalTree($dom, $css);
        }

        /** @var array<int, THTMLAttrib> $dom */
        return $dom;
    }

    /**
     * Recompute selector matching against the fully built DOM tree.
     *
     * Structural pseudo-classes (for example :last-child) require complete
     * sibling sets and can be mis-evaluated during streaming DOM construction.
     *
    * @param array<int, THTMLAttrib> $dom
     * @param array<string, string> $css
     */
    protected function recomputeHTMLDOMCSSAgainstFinalTree(array &$dom, array $css): void
    {
        if (!isset($dom[0]) || !\is_array($dom[0])) {
            return;
        }

        /** @var THTMLAttrib $defaults */
        $defaults = $dom[0];

        /** @var array<int, THTMLAttrib> $dom */

        foreach ($dom as $key => $node) {
            if ($key === 0) {
                continue;
            }

            $parentkey = (isset($node['parent']) && \is_int($node['parent'])) ? (int) $node['parent'] : 0;

            foreach (self::HTML_INHERITED_PROPERTIES as $prop) {
                if (\array_key_exists($prop, $defaults)) {
                    // @phpstan-ignore-next-line parameterByRef.type
                    $dom[$key][$prop] = $defaults[$prop];
                }
            }

            // Border is not an inherited CSS property; reset per-node default
            // to avoid accidental cross-node mutation/leakage during parsing.
            if (isset($defaults['border']) && \is_array($defaults['border'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['border'] = $defaults['border'];
            }

            /** @var array<int, THTMLAttrib> $dom */
            $this->inheritHTMLProperties($dom, $key, $parentkey);

            if (empty($node['tag']) || empty($node['opening'])) {
                continue;
            }

            if (!empty($dom[$key]['cssdata'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['cssdata'] = [];
            }
            if (!empty($dom[$key]['csssel'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['csssel'] = [];
            }

            $attributes = $dom[$key]['attribute'] ?? [];
            if (!\is_array($attributes)) {
                $attributes = [];
            }

            if (!empty($attributes)) {
                unset($attributes['pseudo-before-style']);
                unset($attributes['pseudo-after-style']);
                unset($attributes['pseudo-marker-style']);

                $rawInlineStyle = (isset($attributes['data-tcpdf-inline-style'])
                    && \is_string($attributes['data-tcpdf-inline-style']))
                    ? (string) $attributes['data-tcpdf-inline-style']
                    : '';

                if ($rawInlineStyle !== '') {
                    $attributes['style'] = $rawInlineStyle;
                } else {
                    unset($attributes['style']);
                }
            }

            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['attribute'] = $attributes;

            /** @var array<int, THTMLAttrib> $dom */
            $this->getHTMLDOMCSSData($dom, $css, $key);

            if (!empty($dom[$key]['cssdata'])) {
                $dom[$key]['attribute']['style'] = $this->implodeCSSData($dom[$key]['cssdata']);
            }

            $this->parseHTMLStyleAttributes($dom, $key, $parentkey);
            $this->parseHTMLAttributes($dom, $key, $this->isHTMLNodeInsideThead($dom, $key));
        }
    }

    /**
     * Returns true when the specified node is inside a THEAD section.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function isHTMLNodeInsideThead(array &$dom, int $key): bool
    {
        $parent = $dom[$key]['parent'] ?? null;
        while (\is_int($parent) && ($parent > 0) && isset($dom[$parent])) {
            if (
                !empty($dom[$parent]['tag'])
                && !empty($dom[$parent]['opening'])
                && isset($dom[$parent]['value'])
                && ($dom[$parent]['value'] === 'thead')
            ) {
                return true;
            }

            $next = $dom[$parent]['parent'] ?? null;
            if (!\is_int($next) || ($next === $parent)) {
                break;
            }

            $parent = $next;
        }

        return false;
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
    protected function processHTMLDOMText(
        array &$dom,
        string $element,
        int $key,
        int $parent,
    ): void {
        $this->inheritHTMLProperties($dom, $key, $parent);
        $transform = (
            isset($dom[$parent]['text-transform'])
            && \is_string($dom[$parent]['text-transform'])
        ) ? $dom[$parent]['text-transform'] : '';

        if ($transform !== '') {
            if (\array_key_exists($transform, self::HTML_TEXT_TRANSFORM)) {
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
        foreach (self::HTML_INHERITED_PROPERTIES as $prop) {
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
        $tableparent = $granparent;
        while (
            isset($dom[$tableparent]['value'])
            && !\in_array($dom[$tableparent]['value'], ['table', 'tablehead'], true)
            && isset($dom[$tableparent]['parent'])
            && \is_int($dom[$tableparent]['parent'])
            && ($dom[$tableparent]['parent'] !== $tableparent)
        ) {
            $tableparent = $dom[$tableparent]['parent'];
        }
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
            && (empty($dom[$tableparent]['cols']))
        ) {
            // @phpstan-ignore parameterByRef.type
            $dom[$tableparent]['cols'] = $dom[$parent]['cols'];
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
            if (empty($dom[$tableparent]['thead'])) {
                // @phpstan-ignore parameterByRef.type
                $dom[$tableparent]['thead'] = $cssarray . $elm[$dom[$tableparent]['elkey']];
            }
            for ($idx = $parent; $idx <= $key; ++$idx) {
                /** @var array<int, THTMLAttrib> $dom */
                // @phpstan-ignore parameterByRef.type
                $dom[$tableparent]['thead'] .= $elm[$dom[$idx]['elkey']];
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
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$parent]['thead'] = \str_replace(' nobr="true"', '', $dom[$parent]['thead']);
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$parent]['thead'] .= '</tablehead>';
        }

        if (($dom[$key]['value'] === 'caption') && ($tableparent > 0)) {
            $captionSide = (!empty($dom[$parent]['caption-side']) && \is_string($dom[$parent]['caption-side']))
                ? \strtolower(\trim((string) $dom[$parent]['caption-side'])) : 'top';
            $captionhtml = $cssarray;
            for ($idx = $parent; $idx <= $key; ++$idx) {
                // Suppress in-flow rendering of the full caption subtree; it
                // will be replayed deterministically at table open/close.
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$idx]['hide'] = true;

                $elkey = $dom[$idx]['elkey'] ?? null;
                if (
                    empty($elkey)
                    || (!\is_int($elkey) && !\is_string($elkey))
                    || !isset($elm[$elkey])
                ) {
                    continue;
                }

                $captionhtml .= $elm[$elkey];
            }

            if ($captionSide === 'bottom') {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$tableparent]['caption-bottom-html'] = $captionhtml;
            } else {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$tableparent]['caption-top-html'] = $captionhtml;
            }
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

        // Parse attributes allowing quoted/unquoted values and valueless boolean attributes.
        // Boolean attributes (e.g. readonly, required, disabled) are normalized to "true".
        /** @var array<int, THTMLAttrib> $dom */
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['attribute'] = [];
        $tagname = (isset($dom[$key]['value']) && \is_string($dom[$key]['value'])) ? $dom[$key]['value'] : '';
        if (
            \preg_match_all(
                '/([a-zA-Z_:][a-zA-Z0-9_:\-\.]*)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|([^\s"\'`=<>]+)))?/',
                $element,
                $attr_array,
                PREG_SET_ORDER,
            ) > 0
        ) {
            foreach ($attr_array as $attrm) {
                $name = \strtolower((string) $attrm[1]);
                if (($name === '') || (($tagname !== '') && ($name === $tagname))) {
                    continue;
                }

                $value = 'true';
                if (isset($attrm[2]) && \is_string($attrm[2]) && ($attrm[2] !== '')) {
                    $value = $attrm[2];
                } elseif (isset($attrm[3]) && \is_string($attrm[3]) && ($attrm[3] !== '')) {
                    $value = $attrm[3];
                } elseif (isset($attrm[4]) && \is_string($attrm[4])) {
                    $value = $attrm[4];
                }

                /** @var array<int, THTMLAttrib> $dom */
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['attribute'][$name] = $value;
            }
        }

        // Keep the author-provided inline style separate from merged CSS so
        // selector recomputation against the final DOM can remain idempotent.
        if (!isset($dom[$key]['attribute']['data-tcpdf-inline-style'])) {
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['attribute']['data-tcpdf-inline-style'] =
                (isset($dom[$key]['attribute']['style']) && \is_string($dom[$key]['attribute']['style']))
                ? $dom[$key]['attribute']['style']
                : '';
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
        /** @var array<TCSSData> $pseudobefore */
        $pseudobefore = [];
        /** @var array<TCSSData> $pseudoafter */
        $pseudoafter = [];
        /** @var array<TCSSData> $pseudomarker */
        $pseudomarker = [];
        /** @var array<string> $selectors */
        $selectors = [];
        /** @var array<string> $inheritedSelectors */
        $inheritedSelectors = [];

        $parentkey = -1;
        if (isset($dom[$key]['parent']) && \is_numeric($dom[$key]['parent'])) {
            $parentkey = (int) $dom[$key]['parent'];
        }

        if (
            ($parentkey >= 0)
            && isset($dom[$parentkey]['csssel'])
            && \is_array($dom[$parentkey]['csssel'])
        ) {
            foreach ($dom[$parentkey]['csssel'] as $parentsel) {
                if (\is_string($parentsel) && ($parentsel !== '')) {
                    $inheritedSelectors[] = $parentsel;
                }
            }
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
            $pseudomatch = [];
            if (\preg_match('/^(.*)::(before|after|marker)\s*$/i', $selector, $pseudomatch) > 0) {
                $baseselector = \trim($pseudomatch[1]);
                if (($baseselector !== '') && $this->isValidCSSSelectorForTag($dom, $key, $baseselector)) {
                    $entry = [
                        'k' => $selector,
                        's' => $specificity,
                        'c' => $style,
                    ];
                    $pseudotype = \strtolower($pseudomatch[2]);
                    if ($pseudotype === 'before') {
                        $pseudobefore[] = $entry;
                    } elseif ($pseudotype === 'after') {
                        $pseudoafter[] = $entry;
                    } else { // marker
                        // Only apply marker styles to li elements
                        if (($dom[$key]['value'] ?? '') === 'li') {
                            $pseudomarker[] = $entry;
                        }
                    }
                }

                continue;
            }
            // check if this selector apply to current tag
            if ($this->isValidCSSSelectorForTag($dom, $key, $selector)) {
                if (!empty($inheritedSelectors) && \in_array($selector, $inheritedSelectors, true)) {
                    continue;
                }

                $ret[] = [
                    'k' => $selector,
                    's' => $specificity,
                    'c' => $style,
                ];
                $selectors[] = $selector;
            }
        }

        $inlineStyle = '';
        if (
            !empty($dom[$key]['attribute'])
            && isset($dom[$key]['attribute']['data-tcpdf-inline-style'])
            && \is_string($dom[$key]['attribute']['data-tcpdf-inline-style'])
        ) {
            $inlineStyle = (string) $dom[$key]['attribute']['data-tcpdf-inline-style'];
        } elseif (
            !empty($dom[$key]['attribute'])
            && isset($dom[$key]['attribute']['style'])
            && \is_string($dom[$key]['attribute']['style'])
        ) {
            $inlineStyle = (string) $dom[$key]['attribute']['style'];
        }

        if ($inlineStyle !== '') {
            // attach inline style (latest properties have high priority)
            // Use maximum source order for inline styles to ensure they override stylesheet rules
            $ret[] = [
                'k' => '',
                's' => (string) \Com\Tecnick\Pdf\CSS\CascadeContext::getInlineStyleSourceOrder(),
                'c' => $inlineStyle,
            ];
        }
        // order the css array to account for specificity
        /** @var array<string, TCSSData> $cssordered */
        $cssordered = [];
        /** @var TCSSData $val */
        foreach ($ret as $idx => $val) {
            // If specificity already contains source order (new format with underscore),
            // use it directly; otherwise append the per-element index for backward compatibility
            if (\strpos($val['s'], '_') !== false) {
                // New format: specificity is already a sort key
                $skey = $val['s'];
            } else {
                // Backward compatibility: append per-element index
                $skey = \sprintf('%s_%04d', $val['s'], $idx);
            }
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

        if (!empty($pseudobefore)) {
            if (!isset($dom[$key]['attribute']) || !\is_array($dom[$key]['attribute'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['attribute'] = [];
            }

            $beforeordered = [];
            foreach ($pseudobefore as $idx => $val) {
                $beforeordered[\sprintf('%s_%04d', $val['s'], $idx)] = $val;
            }
            \ksort($beforeordered, SORT_STRING);
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['attribute']['pseudo-before-style'] = $this->implodeCSSData($beforeordered);
        }

        if (!empty($pseudoafter)) {
            if (!isset($dom[$key]['attribute']) || !\is_array($dom[$key]['attribute'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['attribute'] = [];
            }

            $afterordered = [];
            foreach ($pseudoafter as $idx => $val) {
                if (!\is_array($val) || !isset($val['s']) || !\is_string($val['s'])) {
                    continue;
                }
                $afterordered[\sprintf('%s_%04d', $val['s'], $idx)] = $val;
            }
            \ksort($afterordered, SORT_STRING);
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['attribute']['pseudo-after-style'] = $this->implodeCSSData($afterordered);
        }

        if (!empty($pseudomarker)) {
            if (!isset($dom[$key]['attribute']) || !\is_array($dom[$key]['attribute'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['attribute'] = [];
            }

            $markerordered = [];
            foreach ($pseudomarker as $idx => $val) {
                if (!\is_array($val) || !isset($val['s']) || !\is_string($val['s'])) {
                    continue;
                }
                $markerordered[\sprintf('%s_%04d', $val['s'], $idx)] = $val;
            }
            \ksort($markerordered, SORT_STRING);
            // Filter marker styles to only supported properties
            $mergedMarkerstyles = $this->implodeCSSData($markerordered);
            $parsedMarkerstyles = $this->parseHTMLStyleDeclarationMap($mergedMarkerstyles);
            $filtered = $this->filterHTMLMarkerStyles($parsedMarkerstyles);
            if (!empty($filtered)) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['attribute']['pseudo-marker-style'] = $filtered;
            }
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
    public function isValidCSSSelectorForTag(array &$dom, int $key, string $selector): bool
    {
        $ret = false;
        $selector = \trim($selector);
        if ($selector === '') {
            return false;
        }

        $selector = \preg_replace('/\\\\(?:\r\n|\r|\n|\f)/', '', $selector) ?? $selector;
        $selector = $this->escapeHTMLSelectorFunctionalOperators($selector);
        $selector = \preg_replace('/\s*([>+~])\s*/', '\\1', $selector) ?? '';
        $selector = ' ' . \ltrim($selector);
        $tag = \strtolower((string) ($dom[$key]['value'] ?? ''));
        $class = [];
        if (!empty($dom[$key]['attribute']['class'])) {
            $class = \explode(' ', \strtolower($dom[$key]['attribute']['class']));
        }
        $idattr = '';
        if (!empty($dom[$key]['attribute']['id'])) {
            $idattr = \strtolower($dom[$key]['attribute']['id']);
        }
        $selector = \preg_replace(
            '/([\>\+\~\s]{1})([\.]{1})([^\>\+\~\s]*)/si',
            '\\1*.\\3',
            $selector,
        ) ?? '';
        $selector = \preg_replace(
            '/([\>\+\~\s]{1})([\#]{1})([^\>\+\~\s]*)/si',
            '\\1*#\\3',
            $selector,
        ) ?? '';
        $selector = \preg_replace(
            '/([\>\+\~\s]{1})(\[)/si',
            '\\1*\\2',
            $selector,
        ) ?? '';
        $matches = [];
        if (
            empty(\preg_match_all(
                '/([\>\+\~\s]{1})(\*|(?:\\\\.|[\p{L}\p{N}_-])+)([^\>\+\~\s]*)/siu',
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
        $lasttag = \strtolower($this->decodeHTMLCSSIdentifier(\trim($lasttag[0] ?? '')));
        if (($lasttag !== '*') && ($lasttag !== $tag)) {
            return $ret;
        }
        // the last element on selector is our tag or 'any tag'
        $attrib = \array_pop($matches[3]);
        $attrib = \strtolower(\trim($attrib[0] ?? ''));
        $attrib = $this->unescapeHTMLSelectorFunctionalOperators($attrib);
        if (empty($attrib)) {
            $ret = true;
        } else {
            $ret = $this->matchesHTMLSelectorSuffix(
                $dom,
                $key,
                $attrib,
                $class,
                $idattr,
            );
        }

        if (
            $ret
            && ($offset > 0)
            && \is_int($dom[$key]['parent'])
        ) {
            $ret = false;
            // check remaining selector part
            $selector = \substr($selector, 0, $offset);
            $selector = $this->unescapeHTMLSelectorFunctionalOperators($selector);
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
                    $sibling = $this->getHTMLPreviousOpeningSibling($dom, $key);
                    if ($sibling !== null) {
                        $ret = $this->isValidCSSSelectorForTag($dom, $sibling, $selector);
                    }
                    break;
                case '~': // preceded by an element
                    $ret = false;
                    foreach ($this->getHTMLOpeningSiblingKeys($dom, $key) as $sibling) {
                        if ($sibling >= $key) {
                            continue;
                        }

                        if ($this->isValidCSSSelectorForTag($dom, $sibling, $selector)) {
                            $ret = true;
                            break;
                        }
                    }
                    break;
            }
        }

        return $ret;
    }

    /**
     * Match selector suffix (.class, #id, [attr], pseudo-class) for the current tag.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param array<string> $class
     */
    protected function matchesHTMLSelectorSuffix(
        array &$dom,
        int $key,
        string $attrib,
        array $class,
        string $idattr,
    ): bool {
        $tokens = [];
        $selectorTokenPattern = '/(\.(?:\\\\.|[\p{L}\p{N}_-])+|#(?:\\\\.|[\p{L}\p{N}_-])+|'
            . '\[[^\]]+\]|:{1,2}[a-zA-Z-]+(?:\([^\)]*\))?)/u';
        if (
            empty(\preg_match_all(
                $selectorTokenPattern,
                $attrib,
                $tokens,
            ))
        ) {
            return false;
        }

        $suffix = \implode('', $tokens[0]);
        if ($suffix !== $attrib) {
            // The suffix contains unsupported or malformed fragments.
            return false;
        }

        foreach ($tokens[0] as $token) {
            if ($token[0] === '.') {
                $classname = \strtolower($this->decodeHTMLCSSIdentifier(\substr($token, 1)));
                if (!\in_array($classname, $class, true)) {
                    return false;
                }

                continue;
            }

            if ($token[0] === '#') {
                $selectorid = \strtolower($this->decodeHTMLCSSIdentifier(\substr($token, 1)));
                if ($selectorid !== $idattr) {
                    return false;
                }

                continue;
            }

            if ($token[0] === '[') {
                if (!$this->matchesHTMLSelectorAttribute($dom, $key, $token)) {
                    return false;
                }

                continue;
            }

            if ($token[0] === ':') {
                if (!$this->matchesHTMLPseudoClass($dom, $key, $token)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Match attribute selector token against current tag attributes.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function matchesHTMLSelectorAttribute(array &$dom, int $key, string $token): bool
    {
        $attrmatch = [];
        if (
            empty(\preg_match(
                '/\[([^\s\~\^\$\*\|\]=]+)[\s]*([\~\^\$\*\|\=]*)[\s]*(?:"([^"]*)"|\'([^\']*)\'|([^\]]*))?\]/i',
                $token,
                $attrmatch,
            ))
        ) {
            return false;
        }

        $att = \strtolower($this->decodeHTMLCSSIdentifier($attrmatch[1]));
        $val = '';
        if (isset($attrmatch[3]) && ($attrmatch[3] !== '')) {
            $val = $attrmatch[3];
        } elseif (isset($attrmatch[4]) && ($attrmatch[4] !== '')) {
            $val = $attrmatch[4];
        } elseif (isset($attrmatch[5])) {
            $val = \trim($attrmatch[5]);
        }
        $val = $this->decodeHTMLCSSIdentifier($val);
        if (empty($dom[$key]['attribute'][$att])) {
            return false;
        }

        $current = (string) $dom[$key]['attribute'][$att];
        $qval = \preg_quote($val, '/');

        return match ($attrmatch[2]) {
            '=' => ($current == $val),
            '~=' => \in_array($val, \explode(' ', $current), true),
            '^=' => ($val == \substr($current, 0, \strlen($val))),
            '$=' => ($val == \substr($current, -\strlen($val))),
            '*=' => (\strpos($current, $val) !== false),
            '|=' => (($current == $val) || (\preg_match('/^' . $qval . '-/i', $current) > 0)),
            default => true,
        };
    }

    /**
     * Decode CSS escaped sequences inside selector identifiers.
     */
    protected function decodeHTMLCSSIdentifier(string $value): string
    {
        if ($value === '' || !\str_contains($value, '\\')) {
            return $value;
        }

        $value = \preg_replace('/\\\\(?:\r\n|\r|\n|\f)/', '', $value) ?? $value;

        return (string) (\preg_replace_callback(
            '/\\\\([0-9a-fA-F]{1,6}\s?|.)/u',
            static function (array $match): string {
                $esc = $match[1];
                if (\preg_match('/^[0-9a-fA-F]{1,6}\s?$/', $esc) === 1) {
                    $hex = \trim($esc);
                    $codepoint = (int) \hexdec($hex);
                    if ($codepoint <= 0 || $codepoint > 0x10FFFF) {
                        return '';
                    }

                    $chr = \mb_chr($codepoint, 'UTF-8');
                    return ($chr === false) ? '' : $chr;
                }

                return $esc;
            },
            $value,
        ) ?? $value);
    }

    /**
     * Match pseudo-class token against current element.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function matchesHTMLPseudoClass(array &$dom, int $key, string $token): bool
    {
        if (\str_starts_with($token, '::')) {
            return false;
        }

        if (!\preg_match('/^:([a-z-]+)(?:\(([^\)]*)\))?$/i', $token, $pseudo)) {
            return false;
        }

        $name = \strtolower($pseudo[1]);
        $arg = isset($pseudo[2]) ? \trim($pseudo[2]) : '';
        $parent = $dom[$key]['parent'];
        $siblings = [];
        if (\is_int($parent)) {
            $siblings = $this->getHTMLOpeningChildKeys($dom, $parent);
        }

        return match ($name) {
            'first-child' => (!empty($siblings) && ($siblings[0] === $key)),
            'last-child' => (!empty($siblings) && ($siblings[\count($siblings) - 1] === $key)),
            'nth-child' => $this->matchesHTMLPseudoNthChild($siblings, $key, $arg),
            'nth-last-child' => $this->matchesHTMLPseudoNthLastChild($siblings, $key, $arg),
            'only-child' => ((\count($siblings) === 1) && ($siblings[0] === $key)),
            'first-of-type' => $this->matchesHTMLPseudoFirstOfType($dom, $siblings, $key),
            'last-of-type' => $this->matchesHTMLPseudoLastOfType($dom, $siblings, $key),
            'nth-of-type' => $this->matchesHTMLPseudoNthOfType($dom, $siblings, $key, $arg),
            'empty' => $this->matchesHTMLPseudoEmpty($dom, $key),
            'lang' => $this->matchesHTMLPseudoLang($dom, $key, $arg),
            'link' => (
                ($dom[$key]['value'] === 'a')
                && !empty($dom[$key]['attribute']['href'])
            ),
            default => false,
        };
    }

    /**
     * Resolve the effective language for an element by traversing ancestors.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function getHTMLEffectiveLang(array &$dom, int $key): string
    {
        $current = $key;
        while (isset($dom[$current])) {
            $attr = $dom[$current]['attribute'] ?? null;
            if (\is_array($attr)) {
                $lang = (string) ($attr['lang'] ?? $attr['xml:lang'] ?? '');
                $lang = \strtolower(\trim($lang));
                if ($lang !== '') {
                    return $lang;
                }
            }
            $parent = $dom[$current]['parent'] ?? null;
            if (!\is_int($parent) || ($parent === $current)) {
                break;
            }
            $current = $parent;
        }

        return '';
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     */
    protected function matchesHTMLPseudoLang(array &$dom, int $key, string $arg): bool
    {
        $arg = \strtolower(\trim($arg, " \t\n\r\0\x0B\"'"));
        if ($arg === '') {
            return false;
        }

        $lang = $this->getHTMLEffectiveLang($dom, $key);
        if ($lang === '') {
            return false;
        }

        return ($lang === $arg) || \str_starts_with($lang, $arg . '-');
    }

    /**
     * @param array<int> $siblings
     */
    protected function matchesHTMLPseudoNthLastChild(array $siblings, int $key, string $arg): bool
    {
        $reversed = \array_reverse($siblings);
        return $this->matchesHTMLPseudoNthChild($reversed, $key, $arg);
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     * @param array<int> $siblings
        *
        * @return array<int>
     */
    protected function getHTMLSiblingKeysByTagName(array &$dom, array $siblings, int $key): array
    {
        $tag = (string) ($dom[$key]['value'] ?? '');
        if ($tag === '') {
            return [];
        }

        $matched = [];
        foreach ($siblings as $sibling) {
            if (($dom[$sibling]['value'] ?? '') === $tag) {
                $matched[] = $sibling;
            }
        }

        return $matched;
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     * @param array<int> $siblings
     */
    protected function matchesHTMLPseudoFirstOfType(array &$dom, array $siblings, int $key): bool
    {
        $typed = $this->getHTMLSiblingKeysByTagName($dom, $siblings, $key);
        return (!empty($typed) && ($typed[0] === $key));
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     * @param array<int> $siblings
     */
    protected function matchesHTMLPseudoLastOfType(array &$dom, array $siblings, int $key): bool
    {
        $typed = $this->getHTMLSiblingKeysByTagName($dom, $siblings, $key);
        return (!empty($typed) && ($typed[\count($typed) - 1] === $key));
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     * @param array<int> $siblings
     */
    protected function matchesHTMLPseudoNthOfType(array &$dom, array $siblings, int $key, string $arg): bool
    {
        $typed = $this->getHTMLSiblingKeysByTagName($dom, $siblings, $key);
        return $this->matchesHTMLPseudoNthChild($typed, $key, $arg);
    }

    /**
     * @param array<int> $siblings
     */
    protected function matchesHTMLPseudoNthChild(array $siblings, int $key, string $arg): bool
    {
        if ($arg === '') {
            return false;
        }

        $arg = \strtolower(\str_replace(' ', '', $arg));
        if ($arg === '') {
            return false;
        }

        $index = \array_search($key, $siblings, true);
        if (($index === false) || !\is_int($index)) {
            return false;
        }

        $position = $index + 1;
        if ($position < 1) {
            return false;
        }

        if ($arg === 'odd') {
            return (($position % 2) === 1);
        }

        if ($arg === 'even') {
            return (($position % 2) === 0);
        }

        if (\preg_match('/^\d+$/', $arg)) {
            $nth = (int) $arg;
            if ($nth < 1) {
                return false;
            }

            return ($position === $nth);
        }

        if (!\preg_match('/^([+\-]?\d*)n([+\-]\d+)?$/', $arg, $matches)) {
            return false;
        }

        $acoef = $matches[1];
        $boffset = $matches[2] ?? '0';

        if (($acoef === '') || ($acoef === '+')) {
            $factor = 1;
        } elseif ($acoef === '-') {
            $factor = -1;
        } else {
            $factor = (int) $acoef;
        }

        $offset = (int) $boffset;
        $delta = $position - $offset;

        if ($factor === 0) {
            return ($position === $offset);
        }

        if ($factor > 0) {
            return ($delta >= 0) && (($delta % $factor) === 0);
        }

        $absFactor = \abs($factor);
        return ($delta <= 0) && (((-$delta) % $absFactor) === 0);
    }

    /**
     * Escape combinator characters inside pseudo-function arguments and
     * attribute selector values/operators.
     */
    protected function escapeHTMLSelectorFunctionalOperators(string $selector): string
    {
        $out = '';
        $parenDepth = 0;
        $attrDepth = 0;
        $len = \strlen($selector);

        for ($idx = 0; $idx < $len; ++$idx) {
            $char = $selector[$idx];

            if ($char === '[') {
                ++$attrDepth;
                $out .= $char;
                continue;
            }

            if (($char === ']') && ($attrDepth > 0)) {
                --$attrDepth;
                $out .= $char;
                continue;
            }

            if ($char === '(') {
                ++$parenDepth;
                $out .= $char;
                continue;
            }

            if (($char === ')') && ($parenDepth > 0)) {
                --$parenDepth;
                $out .= $char;
                continue;
            }

            if (($parenDepth > 0) || ($attrDepth > 0)) {
                $out .= match ($char) {
                    '+' => "\x1D",
                    '>' => "\x1E",
                    '~' => "\x1F",
                    default => $char,
                };
                continue;
            }

            $out .= $char;
        }

        return $out;
    }

    /**
     * Restore escaped combinator characters in pseudo-function arguments.
     */
    protected function unescapeHTMLSelectorFunctionalOperators(string $selector): string
    {
        return \strtr(
            $selector,
            [
                "\x1D" => '+',
                "\x1E" => '>',
                "\x1F" => '~',
            ],
        );
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     */
    protected function matchesHTMLPseudoEmpty(array &$dom, int $key): bool
    {
        foreach ($dom as $idx => $node) {
            if ($idx === $key) {
                continue;
            }

            if (!isset($node['parent']) || ($node['parent'] !== $key)) {
                continue;
            }

            if (!empty($node['tag']) && !empty($node['opening'])) {
                return false;
            }

            if (
                empty($node['tag'])
                && isset($node['value'])
                && (\trim((string) $node['value']) !== '')
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     *
     * @return array<int>
     */
    protected function getHTMLOpeningChildKeys(array &$dom, int $parent): array
    {
        $children = [];
        foreach ($dom as $idx => $node) {
            if (
                isset($node['parent'])
                && ($node['parent'] === $parent)
                && !empty($node['tag'])
                && !empty($node['opening'])
            ) {
                $children[] = $idx;
            }
        }

        return $children;
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     *
     * @return array<int>
     */
    protected function getHTMLOpeningSiblingKeys(array &$dom, int $key): array
    {
        $parent = $dom[$key]['parent'] ?? null;
        if (!\is_int($parent)) {
            return [];
        }

        return $this->getHTMLOpeningChildKeys($dom, $parent);
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     */
    protected function getHTMLPreviousOpeningSibling(array &$dom, int $key): ?int
    {
        $siblings = $this->getHTMLOpeningSiblingKeys($dom, $key);
        $prev = null;
        foreach ($siblings as $sibling) {
            if ($sibling >= $key) {
                break;
            }

            $prev = $sibling;
        }

        return $prev;
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
        if (empty($dom[$key]['attribute']['style']) || !\is_string($dom[$key]['attribute']['style'])) {
            return;
        }

        $styles = $this->parseHTMLStyleDeclarationMap($dom[$key]['attribute']['style']);
        $styles = $this->resolveHTMLStyleInitialValues($styles);
        if ($styles === []) {
            return;
        }

        /** @var array<int, THTMLAttrib> $dom */
        // @phpstan-ignore parameterByRef.type
        $dom[$key]['style'] = $styles;

        // Layout and list properties.
        $this->parseHTMLStyleDirectionProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleDisplayProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFloatProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleClearProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePositionProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFontFamilyProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleListStyleShorthandProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleListStyleTypeProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleListStylePositionProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleListStyleImageProperty($dom, $key, $parentkey);

        // Text flow and font metrics.
        $this->parseHTMLStyleTextIndentProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleTextTransformProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleWhiteSpaceProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleOverflowWrapProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleWordBreakProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFontSizeProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFontStretchProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleLetterSpacingProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleWordSpacingProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleLineHeightProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFontWeightProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleFontStyleProperty($dom, $key, $parentkey);

        // Colors and text decoration.
        $this->parseHTMLStyleColorProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleBackgroundProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleTextDecorationProperty($dom, $key, $parentkey);

        // Dimensions and alignment.
        $this->parseHTMLStyleWidthProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMinMaxWidthProperties($dom, $key);
        $this->parseHTMLStyleHeightProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMinMaxHeightProperties($dom, $key);
        $this->parseHTMLStyleOverflowProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleTextAlignProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleVerticalAlignProperty($dom, $key, $parentkey);

        // Box model.
        $this->parseHTMLStylePaddingProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePaddingTopProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePaddingRightProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePaddingBottomProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePaddingLeftProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMarginProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMarginTopProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMarginRightProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMarginBottomProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleMarginLeftProperty($dom, $key, $parentkey);
        $this->parseHTMLStylePositionOffsetProperties($dom, $key, $parentkey);

        // Borders and table border model.
        $this->parseHTMLStyleBorderProperties($dom, $key, $parentkey);
        $this->parseHTMLStyleCaptionSideProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleTableLayoutProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleEmptyCellsProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleBorderCollapseProperty($dom, $key, $parentkey);
        $this->parseHTMLStyleBorderSpacingProperty($dom, $key, $parentkey);

        // Page break properties and modern break aliases.
        $this->parseHTMLStylePageBreakInsideProperty($dom, $key);
        $this->parseHTMLStyleBreakInsideAliasProperty($dom, $key);
        $this->parseHTMLStylePageBreakBeforeProperty($dom, $key);
        $this->parseHTMLStyleBreakBeforeAliasProperty($dom, $key);
        $this->parseHTMLStylePageBreakAfterProperty($dom, $key);
        $this->parseHTMLStyleBreakAfterAliasProperty($dom, $key);
    }

    /**
     * Resolve CSS `initial` keyword values using an explicit property metadata table.
     *
     * @param array<string, string> $styles
     *
     * @return array<string, string>
     */
    protected function resolveHTMLStyleInitialValues(array $styles): array
    {
        if ($styles === []) {
            return [];
        }

        $resolved = $styles;
        if (
            isset($resolved['all'])
            && (\trim(\strtolower((string) $resolved['all'])) === 'initial')
        ) {
            foreach (self::HTML_STYLE_INITIAL_VALUE_MAP as $property => $default) {
                if (!isset($resolved[$property])) {
                    $resolved[$property] = $default;
                }
            }
            unset($resolved['all']);
        }

        foreach ($resolved as $property => $value) {
            if (\trim(\strtolower((string) $value)) !== 'initial') {
                continue;
            }

            if (isset(self::HTML_STYLE_INITIAL_VALUE_MAP[$property])) {
                $resolved[$property] = self::HTML_STYLE_INITIAL_VALUE_MAP[$property];
            }
        }

        return $resolved;
    }

    /**
     * Parse direction style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleDirectionProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['direction'])) {
            return;
        }

        $direction = \strtolower(\trim((string) $dom[$key]['style']['direction']));
        if ($direction === 'inherit') {
            $dom[$key]['dir'] = (string) $dom[$parentkey]['dir'];
        } elseif (\in_array($direction, ['ltr', 'rtl'], true)) {
            $dom[$key]['dir'] = $direction;
        }
    }

    /**
     * Parse display style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleDisplayProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['display'])) {
            return;
        }

        $display = \strtolower(\trim((string) $dom[$key]['style']['display']));
        if ($display === 'inherit') {
            $dom[$key]['hide'] = (bool) $dom[$parentkey]['hide'];
            if (!empty($dom[$parentkey]['display']) && \is_string($dom[$parentkey]['display'])) {
                $parentDisplay = \strtolower(\trim((string) $dom[$parentkey]['display']));
                if ($this->isSupportedHTMLDisplayValue($parentDisplay)) {
                    $dom[$key]['display'] = $parentDisplay;
                    $dom[$key]['block'] = $this->isHTMLDisplayBlockLike($parentDisplay);
                }
            }
            return;
        }

        if (!$this->isSupportedHTMLDisplayValue($display)) {
            return;
        }

        $dom[$key]['display'] = $display;
        if ($display === 'none') {
            $dom[$key]['hide'] = true;
        } else {
            $dom[$key]['hide'] = false;
            $dom[$key]['block'] = $this->isHTMLDisplayBlockLike($display);
        }
    }

    protected function isSupportedHTMLDisplayValue(string $display): bool
    {
        return \in_array($display, self::HTML_SUPPORTED_DISPLAY_VALUES, true);
    }

    protected function isHTMLDisplayBlockLike(string $display): bool
    {
        return !\in_array($display, ['inline', 'none'], true);
    }

    /**
     * Parse float style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFloatProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['float'])) {
            return;
        }

        $float =
            \strtolower(\trim((string) $dom[$key]['style']['float']));
        if ($float === 'inherit') {
            if (!empty($dom[$parentkey]['float']) && \is_string($dom[$parentkey]['float'])) {
                $dom[$key]['float'] = (string) $dom[$parentkey]['float'];
            } elseif (!empty($dom[$parentkey]['style']['float']) && \is_string($dom[$parentkey]['style']['float'])) {
                $parentFloat = \strtolower(\trim((string) $dom[$parentkey]['style']['float']));
                if (\in_array($parentFloat, ['left', 'right', 'none'], true)) {
                    $dom[$key]['float'] = $parentFloat;
                }
            }

            return;
        }

        if (\in_array($float, ['left', 'right', 'none'], true)) {
            $dom[$key]['float'] = $float;
        }
    }

    /**
     * Parse clear style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleClearProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['clear'])) {
            return;
        }

        $clear = \strtolower(\trim((string) $dom[$key]['style']['clear']));
        if ($clear === 'inherit') {
            if (!empty($dom[$parentkey]['clear']) && \is_string($dom[$parentkey]['clear'])) {
                $dom[$key]['clear'] = (string) $dom[$parentkey]['clear'];
            } elseif (!empty($dom[$parentkey]['style']['clear']) && \is_string($dom[$parentkey]['style']['clear'])) {
                $parentClear = \strtolower(\trim((string) $dom[$parentkey]['style']['clear']));
                if (\in_array($parentClear, ['left', 'right', 'both', 'none'], true)) {
                    $dom[$key]['clear'] = $parentClear;
                }
            }

            return;
        }

        if (\in_array($clear, ['left', 'right', 'both', 'none'], true)) {
            $dom[$key]['clear'] = $clear;
        }
    }

    /**
     * Parse position style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePositionProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['position'])) {
            return;
        }

        $position = \strtolower(\trim((string) $dom[$key]['style']['position']));
        if ($position === 'inherit') {
            if (!empty($dom[$parentkey]['position']) && \is_string($dom[$parentkey]['position'])) {
                $dom[$key]['position'] = (string) $dom[$parentkey]['position'];
            } elseif (
                !empty($dom[$parentkey]['style']['position'])
                && \is_string($dom[$parentkey]['style']['position'])
            ) {
                $parentPosition = \strtolower(\trim((string) $dom[$parentkey]['style']['position']));
                if (\in_array($parentPosition, ['static', 'relative', 'absolute', 'fixed'], true)) {
                    $dom[$key]['position'] = $parentPosition;
                }
            }
            return;
        }

        if (\in_array($position, ['static', 'relative', 'absolute', 'fixed'], true)) {
            $dom[$key]['position'] = $position;
        }
    }

    /**
     * Parse top/left offsets for positioned elements using conservative flow emulation.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    // @phpstan-ignore parameterByRef.type
    protected function parseHTMLStylePositionOffsetProperties(array &$dom, int $key, int $parentkey): void
    {
        $position = (!empty($dom[$key]['position']) && \is_string($dom[$key]['position']))
            ? \strtolower(\trim((string) $dom[$key]['position']))
            : 'static';
        if (!\in_array($position, ['relative', 'absolute', 'fixed'], true)) {
            return;
        }

        $offsetL = $this->getHTMLStylePositionOffsetValue($dom, $key, $parentkey, 'left');
        $offsetT = $this->getHTMLStylePositionOffsetValue($dom, $key, $parentkey, 'top');

        if (!isset($dom[$key]['margin']) || !\is_array($dom[$key]['margin'])) {
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['margin'] = [];
        }

        if ($offsetL !== 0.0) {
            // @phpstan-ignore parameterByRef.type
            if (!isset($dom[$key]['margin']['L']) || !\is_numeric($dom[$key]['margin']['L'])) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['margin']['L'] = 0.0;
            }
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['margin']['L'] += $offsetL;
        }

        if ($offsetT !== 0.0) {
            // @phpstan-ignore parameterByRef.type
            if (!isset($dom[$key]['margin']['T']) || !\is_numeric($dom[$key]['margin']['T'])) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['margin']['T'] = 0.0;
            }
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['margin']['T'] += $offsetT;
        }
    }

    /**
     * Resolve a position offset style value in user units.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function getHTMLStylePositionOffsetValue(array &$dom, int $key, int $parentkey, string $prop): float
    {
        if (!empty($dom[$key]['style'][$prop]) && \is_string($dom[$key]['style'][$prop])) {
            $raw = \strtolower(\trim((string) $dom[$key]['style'][$prop]));
            if (($raw === '') || ($raw === 'auto')) {
                return 0.0;
            }

            if ($raw === 'inherit') {
                if (!empty($dom[$parentkey]['style'][$prop]) && \is_string($dom[$parentkey]['style'][$prop])) {
                    $parentRaw = \strtolower(\trim((string) $dom[$parentkey]['style'][$prop]));
                    if (($parentRaw === '') || ($parentRaw === 'auto') || ($parentRaw === 'inherit')) {
                        return 0.0;
                    }

                    return $this->toUnit($this->getUnitValuePoints($parentRaw));
                }

                return 0.0;
            }

            return $this->toUnit($this->getUnitValuePoints($raw));
        }

        return 0.0;
    }

    /**
     * Parse font-family style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFontFamilyProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['font-family'])) {
            return;
        }

        $fontFamily = \trim((string) $dom[$key]['style']['font-family']);
        if (\strtolower($fontFamily) === 'inherit') {
            $dom[$key]['fontname'] = (string) $dom[$parentkey]['fontname'];
        } else {
            // Keep the raw CSS family list and defer font resolution to insert().
            // Resolving here against the current buffer can incorrectly collapse
            // unresolved families to the currently active font.
            $dom[$key]['fontname'] = $fontFamily;
        }
    }

    /**
     * Parse list-style shorthand property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleListStyleShorthandProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['list-style'])) {
            return;
        }

        $listStyle = \strtolower(\trim((string) $dom[$key]['style']['list-style']));
        if ($listStyle === 'inherit') {
            $parentListType = '';
            if (!empty($dom[$parentkey]['listtype']) && \is_string($dom[$parentkey]['listtype'])) {
                $parentListType = \trim(\strtolower((string) $dom[$parentkey]['listtype']));
            } elseif (
                !empty($dom[$parentkey]['style']['list-style-type'])
                && \is_string($dom[$parentkey]['style']['list-style-type'])
            ) {
                $styleListType = \trim(\strtolower((string) $dom[$parentkey]['style']['list-style-type']));
                if ($styleListType !== 'inherit') {
                    $parentListType = $styleListType;
                }
            }
            if ($parentListType !== '') {
                $dom[$key]['listtype'] = $parentListType;
            }

            $parentListPosition = '';
            if (
                !empty($dom[$parentkey]['list-style-position'])
                && \is_string($dom[$parentkey]['list-style-position'])
            ) {
                $parentListPosition = \trim(\strtolower((string) $dom[$parentkey]['list-style-position']));
            } elseif (
                !empty($dom[$parentkey]['style']['list-style-position'])
                && \is_string($dom[$parentkey]['style']['list-style-position'])
            ) {
                $styleListPosition = \trim(\strtolower((string) $dom[$parentkey]['style']['list-style-position']));
                if (\in_array($styleListPosition, ['inside', 'outside'], true)) {
                    $parentListPosition = $styleListPosition;
                }
            }
            if ($parentListPosition !== '') {
                $dom[$key]['list-style-position'] = $parentListPosition;
            }

            $parentImage = '';
            if (
                !empty($dom[$parentkey]['style']['list-style-image'])
                && \is_string($dom[$parentkey]['style']['list-style-image'])
            ) {
                $parentImage = \trim((string) $dom[$parentkey]['style']['list-style-image']);
            } elseif (
                !empty($dom[$parentkey]['list-style-image'])
                && \is_string($dom[$parentkey]['list-style-image'])
            ) {
                $parentImage = \trim((string) $dom[$parentkey]['list-style-image']);
            }

            if ($parentImage !== '') {
                $dom[$key]['list-style-image'] = $parentImage;
                $dom[$key]['style']['list-style-image'] = $parentImage;
            }
        }
    }

    /**
     * Parse list-style-type property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleListStyleTypeProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['list-style-type'])) {
            return;
        }

        $dom[$key]['listtype'] = \trim(\strtolower($dom[$key]['style']['list-style-type']));
        if ($dom[$key]['listtype'] == 'inherit') {
            if (!empty($dom[$parentkey]['listtype']) && \is_string($dom[$parentkey]['listtype'])) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['listtype'] = \trim(\strtolower((string) $dom[$parentkey]['listtype']));
            } elseif (
                !empty($dom[$parentkey]['style']['list-style-type'])
                && \is_string($dom[$parentkey]['style']['list-style-type'])
            ) {
                $parentListType = \trim(\strtolower((string) $dom[$parentkey]['style']['list-style-type']));
                if ($parentListType !== 'inherit') {
                    // @phpstan-ignore-next-line parameterByRef.type
                    $dom[$key]['listtype'] = $parentListType;
                }
            }
        }
    }

    /**
     * Parse list-style-position property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleListStylePositionProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['list-style-position'])) {
            return;
        }

        $position = \trim(\strtolower($dom[$key]['style']['list-style-position']));
        if ($position === 'inherit') {
            if (
                !empty($dom[$parentkey]['list-style-position'])
                && \is_string($dom[$parentkey]['list-style-position'])
            ) {
                $dom[$key]['list-style-position'] = \trim(\strtolower(
                    (string) $dom[$parentkey]['list-style-position'],
                ));
            } elseif (
                !empty($dom[$parentkey]['style']['list-style-position'])
                && \is_string($dom[$parentkey]['style']['list-style-position'])
            ) {
                $parentPosition = \trim(\strtolower((string) $dom[$parentkey]['style']['list-style-position']));
                if (\in_array($parentPosition, ['inside', 'outside'], true)) {
                    $dom[$key]['list-style-position'] = $parentPosition;
                }
            }
        } elseif (\in_array($position, ['inside', 'outside'], true)) {
            $dom[$key]['list-style-position'] = $position;
        }
    }

    /**
     * Parse list-style-image property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleListStyleImageProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['list-style-image'])) {
            return;
        }

        $listImage = \trim((string) $dom[$key]['style']['list-style-image']);
        if (\strtolower($listImage) === 'inherit') {
            $parentImage = '';
            if (
                !empty($dom[$parentkey]['style']['list-style-image'])
                && \is_string($dom[$parentkey]['style']['list-style-image'])
            ) {
                $parentImage = \trim((string) $dom[$parentkey]['style']['list-style-image']);
            } elseif (
                !empty($dom[$parentkey]['list-style-image'])
                && \is_string($dom[$parentkey]['list-style-image'])
            ) {
                $parentImage = \trim((string) $dom[$parentkey]['list-style-image']);
            }

            $dom[$key]['list-style-image'] = $parentImage;
            if ($parentImage === '') {
                // @phpstan-ignore-next-line parameterByRef.type
                unset($dom[$key]['style']['list-style-image']);
            } else {
                $dom[$key]['style']['list-style-image'] = $parentImage;
            }
        } else {
            $dom[$key]['list-style-image'] = $listImage;
        }
    }

    /**
     * Parse text-indent style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleTextIndentProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['text-indent'])) {
            return;
        }

        $textIndent = \strtolower(\trim((string) $dom[$key]['style']['text-indent']));
        if ($textIndent === 'inherit') {
            if (isset($dom[$parentkey]['text-indent']) && \is_numeric($dom[$parentkey]['text-indent'])) {
                $dom[$key]['text-indent'] = (float) $dom[$parentkey]['text-indent'];
            } elseif (
                !empty($dom[$parentkey]['style']['text-indent'])
                && \is_string($dom[$parentkey]['style']['text-indent'])
            ) {
                $parentTextIndent = \strtolower(\trim((string) $dom[$parentkey]['style']['text-indent']));
                if ($parentTextIndent !== 'inherit') {
                    $dom[$key]['text-indent'] = $this->toUnit(
                        $this->getUnitValuePoints($parentTextIndent),
                    );
                }
            }
        } else {
            $dom[$key]['text-indent'] = $this->toUnit(
                $this->getUnitValuePoints($textIndent),
            );
        }
    }

    /**
     * Parse text-transform style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleTextTransformProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['text-transform'])) {
            return;
        }

        $transform = \strtolower(\trim((string) $dom[$key]['style']['text-transform']));
        if ($transform === 'inherit') {
            if (
                !empty($dom[$parentkey]['text-transform'])
                && \is_string($dom[$parentkey]['text-transform'])
            ) {
                $dom[$key]['text-transform'] = \strtolower(\trim((string) $dom[$parentkey]['text-transform']));
            } elseif (
                !empty($dom[$parentkey]['style']['text-transform'])
                && \is_string($dom[$parentkey]['style']['text-transform'])
            ) {
                $parentTransform = \strtolower(\trim((string) $dom[$parentkey]['style']['text-transform']));
                if (\in_array($parentTransform, ['uppercase', 'lowercase', 'capitalize'], true)) {
                    $dom[$key]['text-transform'] = $parentTransform;
                } elseif ($parentTransform === 'none') {
                    $dom[$key]['text-transform'] = '';
                }
            }
        } elseif ($transform === 'none') {
            $dom[$key]['text-transform'] = '';
        } elseif (\in_array($transform, ['uppercase', 'lowercase', 'capitalize'], true)) {
            $dom[$key]['text-transform'] = $transform;
        }
    }

    /**
     * Parse white-space style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleWhiteSpaceProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['white-space'])) {
            return;
        }

        $whitespace = \strtolower(\trim($dom[$key]['style']['white-space']));
        if ($whitespace === 'inherit') {
            if (
                !empty($dom[$parentkey]['white-space'])
                && \is_string($dom[$parentkey]['white-space'])
            ) {
                $dom[$key]['white-space'] = \strtolower(\trim((string) $dom[$parentkey]['white-space']));
            } elseif (
                !empty($dom[$parentkey]['style']['white-space'])
                && \is_string($dom[$parentkey]['style']['white-space'])
            ) {
                $parentWhitespace = \strtolower(\trim((string) $dom[$parentkey]['style']['white-space']));
                if (\in_array($parentWhitespace, ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line'], true)) {
                    $dom[$key]['white-space'] = $parentWhitespace;
                }
            }
        } elseif (\in_array($whitespace, ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line'], true)) {
            $dom[$key]['white-space'] = $whitespace;
        }
    }

    /**
     * Parse overflow-wrap style property.
     * Allows breaking long words to fit container width.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleOverflowWrapProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['overflow-wrap'])) {
            return;
        }

        $overflow = \strtolower(\trim($dom[$key]['style']['overflow-wrap']));
        if ($overflow === 'inherit') {
            if (
                !empty($dom[$parentkey]['overflow-wrap'])
                && \is_string($dom[$parentkey]['overflow-wrap'])
            ) {
                $parentOverflowWrap = $dom[$parentkey]['overflow-wrap'];
                $dom[$key]['overflow-wrap'] = \strtolower(\trim($parentOverflowWrap));
            }
        } elseif (\in_array($overflow, ['normal', 'break-word'], true)) {
            $dom[$key]['overflow-wrap'] = $overflow;
        }
    }

    /**
     * Parse word-break style property.
     * Controls breaking behavior for words.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleWordBreakProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['word-break'])) {
            return;
        }

        $wordbreak = \strtolower(\trim($dom[$key]['style']['word-break']));
        if ($wordbreak === 'inherit') {
            if (
                !empty($dom[$parentkey]['word-break'])
                && \is_string($dom[$parentkey]['word-break'])
            ) {
                $parentWordBreak = $dom[$parentkey]['word-break'];
                $dom[$key]['word-break'] = \strtolower(\trim($parentWordBreak));
            }
        } elseif (\in_array($wordbreak, ['normal', 'break-all', 'break-word', 'keep-all'], true)) {
            $dom[$key]['word-break'] = $wordbreak;
        }
    }

    /**
     * Parse font-size style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFontSizeProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['font-size'])) {
            return;
        }

        $fsize = \strtolower(\trim((string) $dom[$key]['style']['font-size']));
        if ($fsize === 'inherit') {
            if (\is_numeric($dom[$parentkey]['fontsize'])) {
                $dom[$key]['fontsize'] = (float) $dom[$parentkey]['fontsize'];
            } elseif (
                \is_array($dom[$parentkey]['style'])
                &&
                !empty($dom[$parentkey]['style']['font-size'])
                && \is_string($dom[$parentkey]['style']['font-size'])
            ) {
                $parentFontSize = \strtolower(\trim($dom[$parentkey]['style']['font-size']));
                if ($parentFontSize !== 'inherit') {
                    $ref = self::REFUNITVAL;
                    if (\is_numeric($dom[0]['fontsize'])) {
                        $ref['parent'] = \floatval($dom[0]['fontsize']);
                    }
                    $dom[$key]['fontsize'] = $this->getFontValuePoints($parentFontSize, $ref, 'pt');
                }
            }
        } else {
            $ref = self::REFUNITVAL;
            if (\is_numeric($dom[$parentkey]['fontsize'])) {
                $ref['parent'] = \floatval($dom[$parentkey]['fontsize']);
            }
            $dom[$key]['fontsize'] = $this->getFontValuePoints($fsize, $ref, 'pt');
        }
    }

    /**
     * Parse font-stretch style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFontStretchProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['font-stretch'])) {
            return;
        }

        $fontStretch = \strtolower(\trim((string) $dom[$key]['style']['font-stretch']));
        $parentStretch = 100.0;
        if (\is_numeric($dom[$parentkey]['font-stretch'])) {
            $parentStretch = \floatval($dom[$parentkey]['font-stretch']);
        } elseif (
            \is_array($dom[$parentkey]['style'])
            &&
            !empty($dom[$parentkey]['style']['font-stretch'])
            && \is_string($dom[$parentkey]['style']['font-stretch'])
        ) {
            $parentStretchStyle = \strtolower(\trim($dom[$parentkey]['style']['font-stretch']));
            if ($parentStretchStyle !== 'inherit') {
                $parentStretch = $this->getTAFontStretching($parentStretchStyle, 100.0);
            }
        }
        $dom[$key]['font-stretch'] = $this->getTAFontStretching(
            $fontStretch,
            $parentStretch,
        );
    }

    /**
     * Parse letter-spacing style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleLetterSpacingProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['letter-spacing'])) {
            return;
        }

        $letterSpacing = \strtolower(\trim((string) $dom[$key]['style']['letter-spacing']));
        $parentLetterSpacing = 0.0;
        if (\is_numeric($dom[$parentkey]['letter-spacing'])) {
            $parentLetterSpacing = \floatval($dom[$parentkey]['letter-spacing']);
        } elseif (
            \is_array($dom[$parentkey]['style'])
            &&
            !empty($dom[$parentkey]['style']['letter-spacing'])
            && \is_string($dom[$parentkey]['style']['letter-spacing'])
        ) {
            $parentSpacingStyle = \strtolower(\trim($dom[$parentkey]['style']['letter-spacing']));
            if ($parentSpacingStyle !== 'inherit') {
                $parentLetterSpacing = $this->getTALetterSpacing($parentSpacingStyle, 0.0);
            }
        }
        $dom[$key]['letter-spacing'] = $this->getTALetterSpacing(
            $letterSpacing,
            $parentLetterSpacing,
        );
    }

    /**
     * Parse word-spacing style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleWordSpacingProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['word-spacing'])) {
            return;
        }

        $spacing = \strtolower(\trim((string) $dom[$key]['style']['word-spacing']));
        $parentWordSpacing = 0.0;
        if (\is_numeric($dom[$parentkey]['word-spacing'])) {
            $parentWordSpacing = (float) $dom[$parentkey]['word-spacing'];
        } elseif (
            \is_array($dom[$parentkey]['style'])
            &&
            !empty($dom[$parentkey]['style']['word-spacing'])
            && \is_string($dom[$parentkey]['style']['word-spacing'])
        ) {
            $parentSpacingStyle = \strtolower(\trim($dom[$parentkey]['style']['word-spacing']));
            if ($parentSpacingStyle !== 'inherit') {
                if ($parentSpacingStyle === 'normal') {
                    $parentWordSpacing = 0.0;
                } else {
                    $parentWordSpacing = $this->toUnit(
                        $this->getUnitValuePoints(
                            $parentSpacingStyle,
                            \array_merge(self::REFUNITVAL, ['parent' => 0.0]),
                        ),
                    );
                }
            }
        }
        if ($spacing === 'inherit') {
            $dom[$key]['word-spacing'] = $parentWordSpacing;
        } elseif ($spacing === 'normal') {
            $dom[$key]['word-spacing'] = 0.0;
        } else {
            $dom[$key]['word-spacing'] = $this->toUnit(
                $this->getUnitValuePoints(
                    $spacing,
                    \array_merge(
                        self::REFUNITVAL,
                        ['parent' => $parentWordSpacing],
                    ),
                ),
            );
        }
    }

    /**
     * Parse line-height style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleLineHeightProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['line-height'])) {
            return;
        }

        $lineheight = \strtolower(\trim((string) $dom[$key]['style']['line-height']));
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
                    // Unitless number: ratio used directly (e.g. 1.5 means 1.5× font height).
                    $dom[$key]['line-height'] = (float) $lineheight;
                    /** @var array<int, THTMLAttrib> $dom */
                } elseif (\substr($lineheight, -1) === '%') {
                    // Percentage: store as a dimensionless ratio (150% → 1.5).
                    $dom[$key]['line-height'] = (float) $lineheight / 100.0;
                    /** @var array<int, THTMLAttrib> $dom */
                } else {
                    // Absolute unit (pt, mm, …): convert to pts and divide by font size in pts.
                    $lhpts = $this->getUnitValuePoints($lineheight);
                    $fontsize = (!empty($dom[$key]['fontsize']) && \is_numeric($dom[$key]['fontsize']))
                        ? (float) $dom[$key]['fontsize']
                        : 0.0;
                    $dom[$key]['line-height'] = ($fontsize > 0) ? ($lhpts / $fontsize) : 1.0;
                    /** @var array<int, THTMLAttrib> $dom */
                }
        }
    }

    /**
     * Parse font-weight style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFontWeightProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['font-weight']) || !\is_string($dom[$key]['fontstyle'])) {
            return;
        }

        $fontWeight = \strtolower(\trim((string) $dom[$key]['style']['font-weight']));
        if ($fontWeight === 'inherit') {
            $parentBold = (
                \is_string($dom[$parentkey]['fontstyle'])
                && (\strpos($dom[$parentkey]['fontstyle'], 'B') !== false)
            );
            if (
                !$parentBold
                && !empty($dom[$parentkey]['style']['font-weight'])
                && \is_string($dom[$parentkey]['style']['font-weight'])
            ) {
                $parentWeight = \strtolower(\trim((string) $dom[$parentkey]['style']['font-weight']));
                $parentBold = (
                    (\is_numeric($parentWeight) && ((int) $parentWeight >= 600))
                    || (($parentWeight !== '') && ($parentWeight[0] === 'b'))
                );
            }
            if ($parentBold) {
                if (\strpos($dom[$key]['fontstyle'], 'B') === false) {
                    // @phpstan-ignore-next-line parameterByRef.type
                    $dom[$key]['fontstyle'] .= 'B';
                }
            } elseif (\strpos($dom[$key]['fontstyle'], 'B') !== false) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] = \str_replace('B', '', $dom[$key]['fontstyle']);
            }
        } elseif (\is_numeric($fontWeight)) {
            if ((int) $fontWeight >= 600) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] .= 'B';
            } else {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] = \str_replace('B', '', $dom[$key]['fontstyle']);
            }
        } elseif ($fontWeight !== '' && $fontWeight[0] == 'n') {
            if (\strpos($dom[$key]['fontstyle'], 'B') !== false) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] = \str_replace('B', '', $dom[$key]['fontstyle']);
            }
        } elseif ($fontWeight !== '' && $fontWeight[0] == 'b') {
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['fontstyle'] .= 'B';
        }
    }

    /**
     * Parse font-style style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleFontStyleProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['font-style']) || !\is_string($dom[$key]['fontstyle'])) {
            return;
        }

        $fontStyle = \strtolower(\trim((string) $dom[$key]['style']['font-style']));
        if ($fontStyle === 'inherit') {
            if (
                !empty($dom[$parentkey]['fontstyle'])
                && \is_string($dom[$parentkey]['fontstyle'])
            ) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] = (string) $dom[$parentkey]['fontstyle'];
            } elseif (
                !empty($dom[$parentkey]['style']['font-style'])
                && \is_string($dom[$parentkey]['style']['font-style'])
            ) {
                $parentFontStyle = \strtolower(\trim((string) $dom[$parentkey]['style']['font-style']));
                if ($parentFontStyle === 'normal') {
                    // @phpstan-ignore-next-line parameterByRef.type
                    $dom[$key]['fontstyle'] = \str_replace('I', '', $dom[$key]['fontstyle']);
                } elseif ($parentFontStyle !== '' && ($parentFontStyle[0] === 'i' || $parentFontStyle[0] === 'o')) {
                    if (\strpos($dom[$key]['fontstyle'], 'I') === false) {
                        // @phpstan-ignore-next-line parameterByRef.type
                        $dom[$key]['fontstyle'] .= 'I';
                    }
                }
            }
        } elseif ($fontStyle === 'normal') {
            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['fontstyle'] = \str_replace('I', '', $dom[$key]['fontstyle']);
        } elseif ($fontStyle !== '' && ($fontStyle[0] === 'i' || $fontStyle[0] === 'o')) {
            if (\strpos($dom[$key]['fontstyle'], 'I') === false) {
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontstyle'] .= 'I';
            }
        }
    }

    /**
     * Parse color style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleColorProperty(array &$dom, int $key, int $parentkey): void
    {
        if ((!empty($dom[$key]['style']['color']))) {
            $fontColor = \trim((string) $dom[$key]['style']['color']);
            if (\strtolower($fontColor) === 'inherit') {
                if (!empty($dom[$parentkey]['fgcolor']) && \is_string($dom[$parentkey]['fgcolor'])) {
                    $dom[$key]['fgcolor'] = (string) $dom[$parentkey]['fgcolor'];
                } elseif (
                    !empty($dom[$parentkey]['style']['color'])
                    && \is_string($dom[$parentkey]['style']['color'])
                ) {
                    $parentColor = \trim((string) $dom[$parentkey]['style']['color']);
                    if (\strtolower($parentColor) !== 'inherit') {
                        $dom[$key]['fgcolor'] = $this->getCSSColor($parentColor);
                    }
                }
            } else {
                $dom[$key]['fgcolor'] = $this->getCSSColor($fontColor);
            }
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fgcolor'] = 'blue';
        }
    }

    /**
     * Parse background and background-color style properties.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBackgroundProperty(array &$dom, int $key, int $parentkey): void
    {
        if ((!empty($dom[$key]['style']['background-color']))) {
            $backgroundColor = \trim((string) $dom[$key]['style']['background-color']);
            if (\strtolower($backgroundColor) === 'inherit') {
                if (!empty($dom[$parentkey]['bgcolor']) && \is_string($dom[$parentkey]['bgcolor'])) {
                    $dom[$key]['bgcolor'] = (string) $dom[$parentkey]['bgcolor'];
                } elseif (
                    !empty($dom[$parentkey]['style']['background-color'])
                    && \is_string($dom[$parentkey]['style']['background-color'])
                ) {
                    $parentBgColor = \trim((string) $dom[$parentkey]['style']['background-color']);
                    if (\strtolower($parentBgColor) !== 'inherit') {
                        $dom[$key]['bgcolor'] = $this->getCSSColor($parentBgColor);
                    }
                } elseif (
                    !empty($dom[$parentkey]['style']['background'])
                    && \is_string($dom[$parentkey]['style']['background'])
                ) {
                    $parentBackground = \trim((string) $dom[$parentkey]['style']['background']);
                    if (\strtolower($parentBackground) !== 'inherit') {
                        $dom[$key]['bgcolor'] = $this->getHTMLBackgroundShorthandColor($parentBackground);
                    }
                }
            } else {
                $dom[$key]['bgcolor'] = $this->getCSSColor($backgroundColor);
            }
        } elseif (!empty($dom[$key]['style']['background'])) {
            $background = \trim((string) $dom[$key]['style']['background']);
            if (\strtolower($background) === 'inherit') {
                if (!empty($dom[$parentkey]['bgcolor']) && \is_string($dom[$parentkey]['bgcolor'])) {
                    $dom[$key]['bgcolor'] = (string) $dom[$parentkey]['bgcolor'];
                } elseif (
                    !empty($dom[$parentkey]['style']['background-color'])
                    && \is_string($dom[$parentkey]['style']['background-color'])
                ) {
                    $parentBgColor = \trim((string) $dom[$parentkey]['style']['background-color']);
                    if (\strtolower($parentBgColor) !== 'inherit') {
                        $dom[$key]['bgcolor'] = $this->getCSSColor($parentBgColor);
                    }
                } elseif (
                    !empty($dom[$parentkey]['style']['background'])
                    && \is_string($dom[$parentkey]['style']['background'])
                ) {
                    $parentBackground = \trim((string) $dom[$parentkey]['style']['background']);
                    if (\strtolower($parentBackground) !== 'inherit') {
                        $dom[$key]['bgcolor'] = $this->getHTMLBackgroundShorthandColor($parentBackground);
                    }
                }
            } else {
                $dom[$key]['bgcolor'] = $this->getHTMLBackgroundShorthandColor($background);
            }
        }
    }

    /**
     * Parse text-decoration style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleTextDecorationProperty(array &$dom, int $key, int $parentkey): void
    {
        if (
            !empty($dom[$key]['style']['text-decoration'])
            && \is_string($dom[$key]['fontstyle'])
        ) {
            $fontStyleNoDecor = \str_replace(['U', 'D', 'O'], '', $dom[$key]['fontstyle']);
            $decorations = '';
            $textDecoration = \strtolower(\trim((string) $dom[$key]['style']['text-decoration']));

            if ($textDecoration === 'inherit') {
                $parentStyle = \is_string($dom[$parentkey]['fontstyle']) ? $dom[$parentkey]['fontstyle'] : '';
                foreach (['U', 'D', 'O'] as $flag) {
                    if (\strpos($parentStyle, $flag) !== false) {
                        $decorations .= $flag;
                    }
                }
            } elseif ($textDecoration !== 'none') {
                $decors = \explode(' ', $textDecoration);
                foreach ($decors as $dec) {
                    $dec = \trim($dec);
                    if ($dec === '') {
                        continue;
                    }

                    $flag = match ($dec[0]) {
                        'u' => 'U',
                        'l' => 'D',
                        'o' => 'O',
                        default => '',
                    };
                    if ($flag !== '' && \strpos($decorations, $flag) === false) {
                        $decorations .= $flag;
                    }
                }
            }

            // @phpstan-ignore-next-line parameterByRef.type
            $dom[$key]['fontstyle'] = $fontStyleNoDecor . $decorations;
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fontstyle'] .= 'U';
        }
    }

    /**
     * Parse width style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleWidthProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['width'])) {
            return;
        }

        $width = \trim((string) $dom[$key]['style']['width']);
        if (\strtolower($width) === 'inherit') {
            if (isset($dom[$parentkey]['width']) && \is_numeric($dom[$parentkey]['width'])) {
                $dom[$key]['width'] = (float) $dom[$parentkey]['width'];
            } elseif (
                !empty($dom[$parentkey]['style']['width'])
                && \is_string($dom[$parentkey]['style']['width'])
            ) {
                $parentWidth = \trim((string) $dom[$parentkey]['style']['width']);
                if (\strtolower($parentWidth) !== 'inherit') {
                    $dom[$key]['width'] = $this->toUnit($this->getUnitValuePoints($parentWidth));
                }
            }
        } else {
            $dom[$key]['width'] = $this->toUnit($this->getUnitValuePoints($width));
        }
    }

    /**
     * Parse min-width/max-width style properties.
     *
     * Conservative policy: clamp only when width is explicitly specified
     * (or inherited) in the same declaration set.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMinMaxWidthProperties(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['width']) || !\is_numeric($dom[$key]['width'])) {
            return;
        }

        $min = null;
        $max = null;

        if (!empty($dom[$key]['style']['min-width']) && \is_string($dom[$key]['style']['min-width'])) {
            $min = $this->getHTMLStyleLengthValue((string) $dom[$key]['style']['min-width']);
        }

        if (!empty($dom[$key]['style']['max-width']) && \is_string($dom[$key]['style']['max-width'])) {
            $maxRaw = \strtolower(\trim((string) $dom[$key]['style']['max-width']));
            if ($maxRaw !== 'none') {
                $max = $this->getHTMLStyleLengthValue((string) $dom[$key]['style']['max-width']);
            }
        }

        if ($min === null && $max === null) {
            return;
        }

        $width = (float) $dom[$key]['width'];
        if ($min !== null && $max !== null && $max < $min) {
            $max = $min;
        }
        if ($min !== null && $width < $min) {
            $width = $min;
        }
        if ($max !== null && $width > $max) {
            $width = $max;
        }

        // @phpstan-ignore-next-line parameterByRef.type
        $dom[$key]['width'] = $width;
    }

    /**
     * Parse height style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleHeightProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['height'])) {
            return;
        }

        $height = \trim((string) $dom[$key]['style']['height']);
        if (\strtolower($height) === 'inherit') {
            if (isset($dom[$parentkey]['height']) && \is_numeric($dom[$parentkey]['height'])) {
                $dom[$key]['height'] = (float) $dom[$parentkey]['height'];
            } elseif (
                !empty($dom[$parentkey]['style']['height'])
                && \is_string($dom[$parentkey]['style']['height'])
            ) {
                $parentHeight = \trim((string) $dom[$parentkey]['style']['height']);
                if (\strtolower($parentHeight) !== 'inherit') {
                    $dom[$key]['height'] = $this->toUnit($this->getUnitValuePoints($parentHeight));
                }
            }
        } else {
            $dom[$key]['height'] = $this->toUnit($this->getUnitValuePoints($height));
        }
    }

    /**
     * Parse min-height/max-height style properties.
     *
     * Conservative policy: clamp only when height is explicitly specified
     * (or inherited) in the same declaration set.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMinMaxHeightProperties(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['height']) || !\is_numeric($dom[$key]['height'])) {
            return;
        }

        $min = null;
        $max = null;

        if (!empty($dom[$key]['style']['min-height']) && \is_string($dom[$key]['style']['min-height'])) {
            $min = $this->getHTMLStyleLengthValue((string) $dom[$key]['style']['min-height']);
        }

        if (!empty($dom[$key]['style']['max-height']) && \is_string($dom[$key]['style']['max-height'])) {
            $maxRaw = \strtolower(\trim((string) $dom[$key]['style']['max-height']));
            if ($maxRaw !== 'none') {
                $max = $this->getHTMLStyleLengthValue((string) $dom[$key]['style']['max-height']);
            }
        }

        if ($min === null && $max === null) {
            return;
        }

        $height = (float) $dom[$key]['height'];
        if ($min !== null && $max !== null && $max < $min) {
            $max = $min;
        }
        if ($min !== null && $height < $min) {
            $height = $min;
        }
        if ($max !== null && $height > $max) {
            $height = $max;
        }

        // @phpstan-ignore-next-line parameterByRef.type
        $dom[$key]['height'] = $height;
    }

    /**
     * Parse overflow style properties and map to the engine clipping flag.
     *
     * Conservative policy: support visible/hidden/inherit using a single clip flag.
     * Axis-specific declarations override the shorthand when present.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleOverflowProperty(array &$dom, int $key, int $parentkey): void
    {
        $hasOverflow = !empty($dom[$key]['style']['overflow']) && \is_string($dom[$key]['style']['overflow']);
        $hasOverflowX = !empty($dom[$key]['style']['overflow-x']) && \is_string($dom[$key]['style']['overflow-x']);
        $hasOverflowY = !empty($dom[$key]['style']['overflow-y']) && \is_string($dom[$key]['style']['overflow-y']);
        if (!$hasOverflow && !$hasOverflowX && !$hasOverflowY) {
            return;
        }

        $clip = null;
        if ($hasOverflow) {
            $value = \strtolower(\trim((string) $dom[$key]['style']['overflow']));
            $clip = $this->resolveHTMLOverflowValueToClip($dom, $parentkey, $value);
        }

        foreach (['overflow-x', 'overflow-y'] as $axisProp) {
            if (empty($dom[$key]['style'][$axisProp]) || !\is_string($dom[$key]['style'][$axisProp])) {
                continue;
            }

            $axisValue = \strtolower(\trim((string) $dom[$key]['style'][$axisProp]));
            $axisClip = $this->resolveHTMLOverflowValueToClip($dom, $parentkey, $axisValue);
            if ($axisClip === null) {
                continue;
            }

            if ($axisClip) {
                $clip = true;
            } elseif ($clip === null) {
                $clip = false;
            }
        }

        if ($clip !== null) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['clip'] = $clip;
        }
    }

    /**
     * @param array<int, THTMLAttrib> $dom
     */
    protected function resolveHTMLOverflowValueToClip(array &$dom, int $parentkey, string $value): ?bool
    {
        return match ($value) {
            'hidden' => true,
            'visible' => false,
            'inherit' => (bool) ($dom[$parentkey]['clip'] ?? false),
            default => null,
        };
    }

    /**
     * Convert a CSS length-like token to user units for box constraints.
     */
    protected function getHTMLStyleLengthValue(string $value): ?float
    {
        $value = \trim($value);
        $normalized = \strtolower($value);
        if ($normalized === '' || $normalized === 'auto' || $normalized === 'none') {
            return null;
        }

        return $this->toUnit($this->getUnitValuePoints($value));
    }

    /**
     * Parse text-align style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleTextAlignProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['text-align'])) {
            return;
        }

        $textAlign = \strtolower(\trim((string) $dom[$key]['style']['text-align']));
        if ($textAlign === 'inherit') {
            if (!empty($dom[$parentkey]['align']) && \is_string($dom[$parentkey]['align'])) {
                $dom[$key]['align'] = \strtoupper(\trim((string) $dom[$parentkey]['align']));
            } elseif (
                !empty($dom[$parentkey]['style']['text-align'])
                && \is_string($dom[$parentkey]['style']['text-align'])
            ) {
                $parentTextAlign = \strtolower(\trim((string) $dom[$parentkey]['style']['text-align']));
                if ($parentTextAlign === 'left') {
                    $dom[$key]['align'] = 'L';
                } elseif ($parentTextAlign === 'right') {
                    $dom[$key]['align'] = 'R';
                } elseif ($parentTextAlign === 'center') {
                    $dom[$key]['align'] = 'C';
                } elseif ($parentTextAlign === 'justify') {
                    $dom[$key]['align'] = 'J';
                }
            }
        } elseif ($textAlign === 'left') {
            $dom[$key]['align'] = 'L';
        } elseif ($textAlign === 'right') {
            $dom[$key]['align'] = 'R';
        } elseif ($textAlign === 'center') {
            $dom[$key]['align'] = 'C';
        } elseif ($textAlign === 'justify') {
            $dom[$key]['align'] = 'J';
        }
    }

    /**
     * Parse vertical-align style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleVerticalAlignProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['vertical-align']) || !\is_string($dom[$key]['style']['vertical-align'])) {
            return;
        }

        $valign = \strtolower(\trim($dom[$key]['style']['vertical-align']));
        if ($valign === 'inherit') {
            if (!empty($dom[$parentkey]['valign']) && \is_string($dom[$parentkey]['valign'])) {
                $dom[$key]['valign'] = \strtolower(\trim((string) $dom[$parentkey]['valign']));
            } elseif (
                !empty($dom[$parentkey]['style']['vertical-align'])
                && \is_string($dom[$parentkey]['style']['vertical-align'])
            ) {
                $parentValign = \strtolower(\trim((string) $dom[$parentkey]['style']['vertical-align']));
                if (\in_array($parentValign, ['top', 'middle', 'bottom'], true)) {
                    $dom[$key]['valign'] = $parentValign;
                }
            }
        } elseif (\in_array($valign, ['top', 'middle', 'bottom'], true)) {
            $dom[$key]['valign'] = $valign;
        }
    }

    /**
     * Parse padding shorthand property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePaddingProperty(array &$dom, int $key, int $parentkey): void
    {
        if (!empty($dom[$key]['style']['padding']) && \is_string($dom[$key]['style']['padding'])) {
            $padding = \trim($dom[$key]['style']['padding']);
            if (\strtolower($padding) === 'inherit') {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['padding'] = \is_array($dom[$parentkey]['padding'])
                    ? $dom[$parentkey]['padding']
                    : self::ZEROCELLBOUND;
            } else {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['padding'] = $this->getCSSPadding($padding);
            }
        } else {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['padding'] = self::ZEROCELLBOUND;
        }
    }

    /**
     * Parse padding-top style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePaddingTopProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'padding', 'padding-top', 'T');
    }

    /**
     * Parse padding-right style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePaddingRightProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'padding', 'padding-right', 'R');
    }

    /**
     * Parse padding-bottom style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePaddingBottomProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'padding', 'padding-bottom', 'B');
    }

    /**
     * Parse padding-left style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePaddingLeftProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'padding', 'padding-left', 'L');
    }

    /**
     * Parse one padding/margin side override property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBoxSideOverrideProperty(
        array &$dom,
        int $key,
        int $parentkey,
        string $box,
        string $prop,
        string $side,
    ): void {
        if (empty($dom[$key]['style'][$prop]) || !\is_string($dom[$key]['style'][$prop])) {
            return;
        }

        $pvalue = \trim($dom[$key]['style'][$prop]);
        $pvalueLower = \strtolower($pvalue);
        if ($pvalueLower === 'inherit') {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            if (\is_array($dom[$parentkey][$box]) && \array_key_exists($side, $dom[$parentkey][$box])) {
                // @phpstan-ignore parameterByRef.type, offsetAccess.nonOffsetAccessible
                $dom[$key][$box][$side] = $dom[$parentkey][$box][$side];
            }

            return;
        }

        if ($pvalueLower === 'auto') {
            return;
        }

        // @phpstan-ignore parameterByRef.type, offsetAccess.nonOffsetAccessible
        $dom[$key][$box][$side] = $this->toUnit($this->getUnitValuePoints($pvalue));
    }

    /**
     * Parse margin shorthand property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMarginProperty(array &$dom, int $key, int $parentkey): void
    {
        if (!empty($dom[$key]['style']['margin']) && \is_string($dom[$key]['style']['margin'])) {
            $margin = \trim($dom[$key]['style']['margin']);
            if (\strtolower($margin) === 'inherit') {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['margin'] = \is_array($dom[$parentkey]['margin'])
                    ? $dom[$parentkey]['margin']
                    : self::ZEROCELLBOUND;
            } else {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['margin'] = $this->getCSSMargin($margin);
            }
        } else {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['margin'] = self::ZEROCELLBOUND;
        }
    }

    /**
     * Parse margin-top style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMarginTopProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'margin', 'margin-top', 'T');
    }

    /**
     * Parse margin-right style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMarginRightProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'margin', 'margin-right', 'R');
    }

    /**
     * Parse margin-bottom style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMarginBottomProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'margin', 'margin-bottom', 'B');
    }

    /**
     * Parse margin-left style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleMarginLeftProperty(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBoxSideOverrideProperty($dom, $key, $parentkey, 'margin', 'margin-left', 'L');
    }

    /**
     * Parse border style properties and side-level overrides.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBorderProperties(array &$dom, int $key, int $parentkey): void
    {
        $this->parseHTMLStyleBorderShorthandProperty($dom, $key, $parentkey);
        $ctx = $this->getHTMLBorderParentContext($dom, $parentkey);
        $brdr = $this->getHTMLDefaultBorderSides();
        $this->parseHTMLStyleBorderColorProperty($dom, $key, $brdr, $ctx['parentBorder'], $ctx['parentLTRB']);
        $this->parseHTMLStyleBorderWidthProperty($dom, $key, $brdr, $ctx['parentBorder'], $ctx['parentLTRB']);
        $this->parseHTMLStyleBorderStyleProperty($dom, $key, $brdr, $ctx['parentBorder'], $ctx['parentLTRB']);
        $cellside = [
            'L' => 'left',
            'R' => 'right',
            'T' => 'top',
            'B' => 'bottom',
        ];
        $ref = self::REFUNITVAL;
        $ref['parent'] = 0.0;
        $this->parseHTMLStyleBorderSideProperties(
            $dom,
            $key,
            $parentkey,
            $brdr,
            $ctx['parentBorder'],
            $ctx['parentLTRB'],
            $cellside,
            $ref,
        );
    }

    /**
     * Parse border shorthand style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBorderShorthandProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['border'])) {
            return;
        }

        $border = \trim((string) $dom[$key]['style']['border']);
        if (\strtolower($border) === 'inherit') {
            if (\is_array($dom[$parentkey]['border'])) {
                $dom[$key]['border'] = $dom[$parentkey]['border'];
            }
        } else {
            $dom[$key]['border']['LTRB'] = $this->getCSSBorderStyle($border);
        }
    }

    /**
     * Build parent border context used for inherit fallbacks.
     *
     * @param array<int, THTMLAttrib> $dom
     *
     * @return array{parentBorder: array<string, BorderStyleOpt>, parentLTRB: BorderStyleOpt}
     */
    protected function getHTMLBorderParentContext(array &$dom, int $parentkey): array
    {
        /** @var array<string, BorderStyleOpt> $parentBorder */
        $parentBorder = (
            isset($dom[$parentkey]['border'])
            && \is_array($dom[$parentkey]['border'])
        ) ? $dom[$parentkey]['border'] : [];
        /** @var BorderStyleOpt $parentLTRB */
        $parentLTRB = (
            isset($parentBorder['LTRB'])
            && \is_array($parentBorder['LTRB'])
        ) ? $parentBorder['LTRB'] : [];

        return [
            'parentBorder' => $parentBorder,
            'parentLTRB' => $parentLTRB,
        ];
    }

    /**
     * Get default border style map for all sides.
     *
     * @return array<string, BorderStyle>
     */
    protected function getHTMLDefaultBorderSides(): array
    {
        return [
            'L' => $this->getCSSDefaultBorderStyle(),
            'R' => $this->getCSSDefaultBorderStyle(),
            'T' => $this->getCSSDefaultBorderStyle(),
            'B' => $this->getCSSDefaultBorderStyle(),
        ];
    }

    /**
     * Parse border-color style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderColorProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
    ): void {
        if (empty($dom[$key]['style']['border-color'])) {
            return;
        }

        $borderColor = \trim((string) $dom[$key]['style']['border-color']);
        if (\strtolower($borderColor) === 'inherit') {
            foreach (['L', 'R', 'T', 'B'] as $bkey) {
                if (
                    isset($parentBorder[$bkey]['lineColor'])
                    && \is_string($parentBorder[$bkey]['lineColor'])
                ) {
                    $brdr[$bkey]['lineColor'] = $parentBorder[$bkey]['lineColor'];
                } elseif (isset($parentLTRB['lineColor']) && \is_string($parentLTRB['lineColor'])) {
                    $brdr[$bkey]['lineColor'] = $parentLTRB['lineColor'];
                }
            }
        } else {
            $brd_colors = \preg_split('/[\s]+/', $borderColor);
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
    }

    /**
     * Parse border-width style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderWidthProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
    ): void {
        if (empty($dom[$key]['style']['border-width'])) {
            return;
        }

        $borderWidth = \trim((string) $dom[$key]['style']['border-width']);
        if (\strtolower($borderWidth) === 'inherit') {
            foreach (['L', 'R', 'T', 'B'] as $bkey) {
                if (
                    isset($parentBorder[$bkey]['lineWidth'])
                    && \is_numeric($parentBorder[$bkey]['lineWidth'])
                ) {
                    $brdr[$bkey]['lineWidth'] = (float) $parentBorder[$bkey]['lineWidth'];
                } elseif (isset($parentLTRB['lineWidth']) && \is_numeric($parentLTRB['lineWidth'])) {
                    $brdr[$bkey]['lineWidth'] = (float) $parentLTRB['lineWidth'];
                }
            }
        } else {
            $brd_widths = \preg_split('/[\s]+/', $borderWidth);
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
    }

    /**
     * Parse border-style style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderStyleProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
    ): void {
        if (empty($dom[$key]['style']['border-style'])) {
            return;
        }

        $borderStyle = \trim((string) $dom[$key]['style']['border-style']);
        if (\strtolower($borderStyle) === 'inherit') {
            foreach (['L', 'R', 'T', 'B'] as $bkey) {
                if (
                    isset($parentBorder[$bkey]['cssBorderStyle'])
                    && \is_string($parentBorder[$bkey]['cssBorderStyle'])
                ) {
                    $brdr[$bkey] = $this->applyCSSBorderStyleKeyword(
                        $brdr[$bkey],
                        $parentBorder[$bkey]['cssBorderStyle']
                    );
                } elseif (isset($parentLTRB['cssBorderStyle']) && \is_string($parentLTRB['cssBorderStyle'])) {
                    $brdr[$bkey] = $this->applyCSSBorderStyleKeyword(
                        $brdr[$bkey],
                        $parentLTRB['cssBorderStyle']
                    );
                }
            }
        } else {
            $brd_styles = \preg_split('/[\s]+/', $borderStyle);
            if ($brd_styles !== false) {
                if (isset($brd_styles[3]) && ($brd_styles[3] != 'none')) {
                    $brdr['L'] = $this->applyCSSBorderStyleKeyword($brdr['L'], $brd_styles[3]);
                }
                if (isset($brd_styles[1])) {
                    $brdr['R'] = $this->applyCSSBorderStyleKeyword($brdr['R'], $brd_styles[1]);
                }
                if (isset($brd_styles[0])) {
                    $brdr['T'] = $this->applyCSSBorderStyleKeyword($brdr['T'], $brd_styles[0]);
                }
                if (isset($brd_styles[2])) {
                    $brdr['B'] = $this->applyCSSBorderStyleKeyword($brdr['B'], $brd_styles[2]);
                }
            }
        }
    }

    /**
     * Parse per-side border, padding and margin style overrides.
     *
     * @param array<int, THTMLAttrib> $dom
        * @param array<string, BorderStyleOpt> $brdr
        * @param array<string, BorderStyleOpt> $parentBorder
        * @param BorderStyleOpt $parentLTRB
     * @param array<string, string> $cellside
        * @param array{
        *     parent: float,
        *     font: array{rootsize: float, size: float, xheight: float, zerowidth: float},
        *     viewport: array{width: float, height: float},
        *     page: array{width: float, height: float}
        * } $ref
     */
    protected function parseHTMLStyleBorderSideProperties(
        array &$dom,
        int $key,
        int $parentkey,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
        array $cellside,
        array $ref,
    ): void {
        foreach ($cellside as $bsk => $bsv) {
            $this->parseHTMLStyleBorderSideShorthandProperty($dom, $key, $brdr, $parentBorder, $parentLTRB, $bsk, $bsv);
            $this->parseHTMLStyleBorderSideColorProperty($dom, $key, $brdr, $parentBorder, $parentLTRB, $bsk, $bsv);
            $this->parseHTMLStyleBorderSideWidthProperty($dom, $key, $brdr, $parentBorder, $parentLTRB, $bsk, $bsv);
            $this->parseHTMLStyleBorderSideStyleProperty($dom, $key, $brdr, $parentBorder, $parentLTRB, $bsk, $bsv);
            $this->commitHTMLRenderableBorderSide($dom, $key, $brdr, $bsk);
            $this->parseHTMLStylePaddingSideProperty($dom, $key, $parentkey, $ref, $bsk, $bsv);
            $this->parseHTMLStyleMarginSideProperty($dom, $key, $parentkey, $ref, $bsk, $bsv);
        }
    }

    /**
     * Parse border-<side> style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderSideShorthandProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['border-' . $bsv])) {
            return;
        }

        $sideBorder = \trim((string) $dom[$key]['style']['border-' . $bsv]);
        if (\strtolower($sideBorder) === 'inherit') {
            if (
                // @phpstan-ignore offsetAccess.notFound
                isset($parentBorder[$bsk])
                && \is_array($parentBorder[$bsk])
            ) {
                /** @var BorderStyleOpt $parentBorder */
                // @phpstan-ignore offsetAccess.notFound
                $parentSideBorder = $parentBorder[$bsk];
                // @phpstan-ignore parameterByRef.type
                $brdr[$bsk] = $parentSideBorder;
            } elseif ($parentLTRB !== []) {
                /** @var BorderStyleOpt $parentSideBorder */
                $parentSideBorder = $parentLTRB;
                // @phpstan-ignore parameterByRef.type
                $brdr[$bsk] = $parentSideBorder;
            }
        } else {
            $brdr[$bsk] = $this->getCSSBorderStyle($sideBorder);
        }
    }

    /**
     * Parse border-<side>-color style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderSideColorProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['border-' . $bsv . '-color'])) {
            return;
        }

        $sideColor = \trim((string) $dom[$key]['style']['border-' . $bsv . '-color']);
        if (\strtolower($sideColor) === 'inherit') {
            if (
                isset($parentBorder[$bsk]['lineColor'])
                && \is_string($parentBorder[$bsk]['lineColor'])
            ) {
                $brdr[$bsk]['lineColor'] = $parentBorder[$bsk]['lineColor'];
            } elseif (isset($parentLTRB['lineColor']) && \is_string($parentLTRB['lineColor'])) {
                $brdr[$bsk]['lineColor'] = $parentLTRB['lineColor'];
            }
        } else {
            $brdr[$bsk]['lineColor'] = $this->getCSSColor($sideColor);
        }
    }

    /**
     * Parse border-<side>-width style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderSideWidthProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['border-' . $bsv . '-width'])) {
            return;
        }

        $sideWidth = \trim((string) $dom[$key]['style']['border-' . $bsv . '-width']);
        if (\strtolower($sideWidth) === 'inherit') {
            if (
                isset($parentBorder[$bsk]['lineWidth'])
                && \is_numeric($parentBorder[$bsk]['lineWidth'])
            ) {
                $brdr[$bsk]['lineWidth'] = (float) $parentBorder[$bsk]['lineWidth'];
            } elseif (isset($parentLTRB['lineWidth']) && \is_numeric($parentLTRB['lineWidth'])) {
                $brdr[$bsk]['lineWidth'] = (float) $parentLTRB['lineWidth'];
            }
        } else {
            $brdr[$bsk]['lineWidth'] = $this->getCSSBorderWidth($sideWidth);
        }
    }

    /**
     * Parse border-<side>-style style property.
     *
     * @param array<int, THTMLAttrib> $dom
    * @param array<string, BorderStyleOpt> $brdr
    * @param array<string, BorderStyleOpt> $parentBorder
    * @param BorderStyleOpt $parentLTRB
     */
    protected function parseHTMLStyleBorderSideStyleProperty(
        array &$dom,
        int $key,
        array &$brdr,
        array $parentBorder,
        array $parentLTRB,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['border-' . $bsv . '-style'])) {
            return;
        }

        $sideStyle = \trim((string) $dom[$key]['style']['border-' . $bsv . '-style']);
        if (\strtolower($sideStyle) === 'inherit') {
            if (
                isset($parentBorder[$bsk]['cssBorderStyle'])
                && \is_string($parentBorder[$bsk]['cssBorderStyle'])
            ) {
                $brdr[$bsk] = $this->applyCSSBorderStyleKeyword(
                    $brdr[$bsk],
                    $parentBorder[$bsk]['cssBorderStyle']
                );
            } elseif (isset($parentLTRB['cssBorderStyle']) && \is_string($parentLTRB['cssBorderStyle'])) {
                $brdr[$bsk] = $this->applyCSSBorderStyleKeyword(
                    $brdr[$bsk],
                    $parentLTRB['cssBorderStyle']
                );
            }
        } else {
            $brdr[$bsk] = $this->applyCSSBorderStyleKeyword(
                $brdr[$bsk],
                $sideStyle
            );
        }
    }

    /**
     * Commit a border side when renderable.
     *
     * @param array<int, THTMLAttrib> $dom
     * @param array<string, BorderStyleOpt> $brdr
     */
    protected function commitHTMLRenderableBorderSide(array &$dom, int $key, array $brdr, string $bsk): void
    {
        /** @var array<string, BorderStyleOpt> $brdr */
        if ($this->isHTMLRenderableBorderStyle($brdr[$bsk])) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['border'][$bsk] = $brdr[$bsk];
        }
    }

    /**
     * Parse padding-<side> style property.
     *
     * @param array<int, THTMLAttrib> $dom
        * @param array{
        *     parent: float,
        *     font: array{rootsize: float, size: float, xheight: float, zerowidth: float},
        *     viewport: array{width: float, height: float},
        *     page: array{width: float, height: float}
        * } $ref
     */
    protected function parseHTMLStylePaddingSideProperty(
        array &$dom,
        int $key,
        int $parentkey,
        array $ref,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['padding-' . $bsv])) {
            return;
        }

        $paddingSide = \trim((string) $dom[$key]['style']['padding-' . $bsv]);
        $paddingSideLower = \strtolower($paddingSide);
        if ($paddingSideLower === 'inherit') {
            if (\is_array($dom[$parentkey]['padding']) && \array_key_exists($bsk, $dom[$parentkey]['padding'])) {
                $dom[$key]['padding'][$bsk] = $dom[$parentkey]['padding'][$bsk];
            }
        } elseif ($paddingSideLower !== 'auto') {
            $dom[$key]['padding'][$bsk] = $this->toUnit(
                $this->getUnitValuePoints($paddingSide, $ref)
            );
        }
    }

    /**
     * Parse margin-<side> style property.
     *
     * @param array<int, THTMLAttrib> $dom
        * @param array{
        *     parent: float,
        *     font: array{rootsize: float, size: float, xheight: float, zerowidth: float},
        *     viewport: array{width: float, height: float},
        *     page: array{width: float, height: float}
        * } $ref
     */
    protected function parseHTMLStyleMarginSideProperty(
        array &$dom,
        int $key,
        int $parentkey,
        array $ref,
        string $bsk,
        string $bsv,
    ): void {
        if (empty($dom[$key]['style']['margin-' . $bsv])) {
            return;
        }

        $marginSide = \trim((string) $dom[$key]['style']['margin-' . $bsv]);
        $marginSideLower = \strtolower($marginSide);
        if ($marginSideLower === 'inherit') {
            if (\is_array($dom[$parentkey]['margin']) && \array_key_exists($bsk, $dom[$parentkey]['margin'])) {
                $dom[$key]['margin'][$bsk] = $dom[$parentkey]['margin'][$bsk];
            }
        } else {
            $dom[$key]['margin'][$bsk] = $this->toUnit($this->getUnitValuePoints(
                \str_replace('auto', '0', $marginSide),
                $ref
            ));
        }
    }

    /**
     * Parse border-collapse style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBorderCollapseProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['border-collapse'])) {
            return;
        }

        $bordercollapse = \strtolower(\trim((string) $dom[$key]['style']['border-collapse']));
        if ($bordercollapse === 'inherit') {
            if (!empty($dom[$parentkey]['border-collapse']) && \is_string($dom[$parentkey]['border-collapse'])) {
                $dom[$key]['border-collapse'] = (string) $dom[$parentkey]['border-collapse'];
            } elseif (
                !empty($dom[$parentkey]['style']['border-collapse'])
                && \is_string($dom[$parentkey]['style']['border-collapse'])
            ) {
                $parentCollapse = \strtolower(\trim((string) $dom[$parentkey]['style']['border-collapse']));
                if (\in_array($parentCollapse, ['collapse', 'separate'], true)) {
                    $dom[$key]['border-collapse'] = $parentCollapse;
                }
            }
        } elseif (\in_array($bordercollapse, ['collapse', 'separate'], true)) {
            $dom[$key]['border-collapse'] = $bordercollapse;
        }
    }

    /**
     * Parse table-layout style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleTableLayoutProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['table-layout'])) {
            return;
        }

        $tableLayout = \strtolower(\trim((string) $dom[$key]['style']['table-layout']));
        if ($tableLayout === 'inherit') {
            if (!empty($dom[$parentkey]['table-layout']) && \is_string($dom[$parentkey]['table-layout'])) {
                $dom[$key]['table-layout'] = (string) $dom[$parentkey]['table-layout'];
            } elseif (
                !empty($dom[$parentkey]['style']['table-layout'])
                && \is_string($dom[$parentkey]['style']['table-layout'])
            ) {
                $parentLayout = \strtolower(\trim((string) $dom[$parentkey]['style']['table-layout']));
                if (\in_array($parentLayout, ['auto', 'fixed'], true)) {
                    $dom[$key]['table-layout'] = $parentLayout;
                }
            }
        } elseif (\in_array($tableLayout, ['auto', 'fixed'], true)) {
            $dom[$key]['table-layout'] = $tableLayout;
        }
    }

    /**
     * Parse caption-side style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleCaptionSideProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['caption-side'])) {
            return;
        }

        $captionSide = \strtolower(\trim((string) $dom[$key]['style']['caption-side']));
        if ($captionSide === 'inherit') {
            if (!empty($dom[$parentkey]['caption-side']) && \is_string($dom[$parentkey]['caption-side'])) {
                $dom[$key]['caption-side'] = (string) $dom[$parentkey]['caption-side'];
            } elseif (
                !empty($dom[$parentkey]['style']['caption-side'])
                && \is_string($dom[$parentkey]['style']['caption-side'])
            ) {
                $parentCaptionSide = \strtolower(\trim((string) $dom[$parentkey]['style']['caption-side']));
                if (\in_array($parentCaptionSide, ['top', 'bottom'], true)) {
                    $dom[$key]['caption-side'] = $parentCaptionSide;
                }
            }
        } elseif (\in_array($captionSide, ['top', 'bottom'], true)) {
            $dom[$key]['caption-side'] = $captionSide;
        }
    }

    /**
     * Parse empty-cells style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleEmptyCellsProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['empty-cells'])) {
            return;
        }

        $emptyCells = \strtolower(\trim((string) $dom[$key]['style']['empty-cells']));
        if ($emptyCells === 'inherit') {
            if (!empty($dom[$parentkey]['empty-cells']) && \is_string($dom[$parentkey]['empty-cells'])) {
                $dom[$key]['empty-cells'] = (string) $dom[$parentkey]['empty-cells'];
            } elseif (
                !empty($dom[$parentkey]['style']['empty-cells'])
                && \is_string($dom[$parentkey]['style']['empty-cells'])
            ) {
                $parentEmptyCells = \strtolower(\trim((string) $dom[$parentkey]['style']['empty-cells']));
                if (\in_array($parentEmptyCells, ['show', 'hide'], true)) {
                    $dom[$key]['empty-cells'] = $parentEmptyCells;
                }
            }
        } elseif (\in_array($emptyCells, ['show', 'hide'], true)) {
            $dom[$key]['empty-cells'] = $emptyCells;
        }
    }

    /**
     * Return true when empty-cells requires skipping cell paint operations.
     *
     * @param THTMLTableState $table
     * @param THTMLAttrib $elm
     * @param THTMLTableCellContext $cellctx
     */
    protected function shouldHideHTMLEmptyTableCell(array $table, array $elm, array $cellctx): bool
    {
        if (!empty($table['collapse'])) {
            return false;
        }

        $emptyCells = (!empty($elm['empty-cells']) && \is_string($elm['empty-cells']))
            ? \strtolower(\trim($elm['empty-cells'])) : 'show';
        if ($emptyCells !== 'hide') {
            return false;
        }

        if (!empty($cellctx['buffer']) && \is_string($cellctx['buffer'])) {
            return false;
        }

        return true;
    }

    /**
     * Parse border-spacing style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBorderSpacingProperty(array &$dom, int $key, int $parentkey): void
    {
        if (empty($dom[$key]['style']['border-spacing'])) {
            return;
        }

        $borderSpacing = \trim((string) $dom[$key]['style']['border-spacing']);
        if (\strtolower($borderSpacing) === 'inherit') {
            if (isset($dom[$parentkey]['border-spacing']) && \is_array($dom[$parentkey]['border-spacing'])) {
                $dom[$key]['border-spacing'] = $dom[$parentkey]['border-spacing'];
            } elseif (
                !empty($dom[$parentkey]['style']['border-spacing'])
                && \is_string($dom[$parentkey]['style']['border-spacing'])
            ) {
                $parentSpacing = \trim((string) $dom[$parentkey]['style']['border-spacing']);
                if (\strtolower($parentSpacing) !== 'inherit') {
                    $dom[$key]['border-spacing'] = $this->getCSSBorderMargin($parentSpacing);
                }
            }
        } else {
            $dom[$key]['border-spacing'] = $this->getCSSBorderMargin($borderSpacing);
        }
    }

    /**
     * Parse page-break-inside style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePageBreakInsideProperty(array &$dom, int $key): void
    {
        if (
            !empty($dom[$key]['style']['page-break-inside'])
            && (\strtolower(\trim((string) $dom[$key]['style']['page-break-inside'])) === 'avoid')
        ) {
            $dom[$key]['attribute']['nobr'] = 'true';
        }
    }

    /**
     * Parse break-inside alias style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBreakInsideAliasProperty(array &$dom, int $key): void
    {
        if (
            empty($dom[$key]['attribute']['nobr'])
            &&
            !empty($dom[$key]['style']['break-inside'])
            && (\strtolower(\trim((string) $dom[$key]['style']['break-inside'])) === 'avoid')
        ) {
            $dom[$key]['attribute']['nobr'] = 'true';
        }
    }

    /**
     * Parse page-break-before style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePageBreakBeforeProperty(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['page-break-before'])) {
            return;
        }

        $pageBreakBefore = \strtolower(\trim((string) $dom[$key]['style']['page-break-before']));
        $this->applyHTMLStyleBreakBeforeValue($dom, $key, $pageBreakBefore);
    }

    /**
     * Parse break-before alias style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBreakBeforeAliasProperty(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['page-break-before']) && !empty($dom[$key]['style']['break-before'])) {
            $breakBefore = \strtolower(\trim((string) $dom[$key]['style']['break-before']));
            $this->applyHTMLStyleBreakBeforeValue($dom, $key, $breakBefore);
        }
    }

    /**
     * Apply computed break-before value.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function applyHTMLStyleBreakBeforeValue(array &$dom, int $key, string $value): void
    {
        if ($value === '') {
            return;
        }

        /** @var THTMLAttrib $elm */
        $elm = $dom[$key];
        $elm['attribute']['pagebreak'] = match ($value) {
            'always' => 'true',
            'page' => 'true',
            'left' => 'left',
            'right' => 'right',
            default => '',
        };
        $dom[$key] = $elm;
    }

    /**
     * Parse page-break-after style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStylePageBreakAfterProperty(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['page-break-after'])) {
            return;
        }

        $pageBreakAfter = \strtolower(\trim((string) $dom[$key]['style']['page-break-after']));
        $this->applyHTMLStyleBreakAfterValue($dom, $key, $pageBreakAfter);
    }

    /**
     * Parse break-after alias style property.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function parseHTMLStyleBreakAfterAliasProperty(array &$dom, int $key): void
    {
        if (empty($dom[$key]['style']['page-break-after']) && !empty($dom[$key]['style']['break-after'])) {
            $breakAfter = \strtolower(\trim((string) $dom[$key]['style']['break-after']));
            $this->applyHTMLStyleBreakAfterValue($dom, $key, $breakAfter);
        }
    }

    /**
     * Apply computed break-after value.
     *
     * @param array<int, THTMLAttrib> $dom
     */
    protected function applyHTMLStyleBreakAfterValue(array &$dom, int $key, string $value): void
    {
        if ($value === '') {
            return;
        }

        /** @var THTMLAttrib $elm */
        $elm = $dom[$key];
        $elm['attribute']['pagebreakafter'] = match ($value) {
            'always' => 'true',
            'page' => 'true',
            'left' => 'left',
            'right' => 'right',
            default => '',
        };
        $dom[$key] = $elm;
    }

    /**
     * Parse a CSS declaration list into a property map.
     *
     * This parser keeps semicolons and colons inside quoted strings and
     * parenthesized expressions (for example `url(data:image/...;base64,...)`).
     *
     * @return array<string, string>
     */
    protected function parseHTMLStyleDeclarationMap(string $style): array
    {
        $out = [];
        $decl = '';
        $quote = '';
        $parenDepth = 0;
        $slen = \strlen($style);

        for ($idx = 0; $idx < $slen; ++$idx) {
            $chr = $style[$idx];

            if ($quote !== '') {
                $decl .= $chr;
                if ($chr === $quote && (($idx === 0) || ($style[$idx - 1] !== '\\'))) {
                    $quote = '';
                }

                continue;
            }

            if ($chr === '"' || $chr === "'") {
                $quote = $chr;
                $decl .= $chr;
                continue;
            }

            if ($chr === '(') {
                ++$parenDepth;
                $decl .= $chr;
                continue;
            }

            if ($chr === ')') {
                $parenDepth = \max(0, $parenDepth - 1);
                $decl .= $chr;
                continue;
            }

            if ($chr === ';' && ($parenDepth === 0)) {
                $this->addHTMLStyleDeclaration($out, $decl);
                $decl = '';
                continue;
            }

            $decl .= $chr;
        }

        $this->addHTMLStyleDeclaration($out, $decl);

        return $out;
    }

    /**
     * Add one CSS declaration to the parsed map if valid.
     *
     * @param array<string, string> $out
     */
    protected function addHTMLStyleDeclaration(array &$out, string $decl): void
    {
        $decl = \trim($decl);
        if ($decl === '') {
            return;
        }

        $quote = '';
        $parenDepth = 0;
        $dlen = \strlen($decl);
        $split = -1;

        for ($idx = 0; $idx < $dlen; ++$idx) {
            $chr = $decl[$idx];
            if ($quote !== '') {
                if ($chr === $quote && (($idx === 0) || ($decl[$idx - 1] !== '\\'))) {
                    $quote = '';
                }

                continue;
            }

            if ($chr === '"' || $chr === "'") {
                $quote = $chr;
                continue;
            }

            if ($chr === '(') {
                ++$parenDepth;
                continue;
            }

            if ($chr === ')') {
                $parenDepth = \max(0, $parenDepth - 1);
                continue;
            }

            if ($chr === ':' && ($parenDepth === 0)) {
                $split = $idx;
                break;
            }
        }

        if ($split < 1) {
            return;
        }

        $name = \strtolower(\trim(\substr($decl, 0, $split)));
        if ($name === '') {
            return;
        }

        $value = \trim(\substr($decl, $split + 1));
        // Keep the declaration value content while dropping CSS priority marker.
        // Priority is handled by the cascade layer before inline declaration parsing.
        $value = \trim((string) (\preg_replace('/\s*!important\s*$/i', '', $value) ?? $value));
        $out[$name] = $value;
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
        $this->parseHTMLAttributesDisplayProperty($dom, $key);
        $this->parseHTMLAttributesBorderProperty($dom, $key);
        $this->parseHTMLAttributesFontTag($dom, $key);
        $this->parseHTMLAttributesListAlignment($dom, $key);
        $this->parseHTMLAttributesSmallSupSub($dom, $key);
        $this->parseHTMLAttributesEnsureFontStyle($dom, $key);
        $this->parseHTMLAttributesStrongBold($dom, $key);
        $this->parseHTMLAttributesEmItalic($dom, $key);
        $this->parseHTMLAttributesUnderline($dom, $key);
        $this->parseHTMLAttributesStrikeThrough($dom, $key);
        $this->parseHTMLAttributesAnchorUnderline($dom, $key);
        $this->parseHTMLAttributesMonospace($dom, $key);
        $this->parseHTMLAttributesHeadingDefaults($dom, $key);
        $this->parseHTMLAttributesBlockMargins($dom, $key);
        $this->parseHTMLAttributesTableDefaults($dom, $key);
        $this->parseHTMLAttributesCaptionDefaults($dom, $key);
        $this->parseHTMLAttributesTableRowDefaults($dom, $key, $thead);
        $this->parseHTMLAttributesTableCellDefaults($dom, $key);
        $this->parseHTMLAttributesDirection($dom, $key);
        $this->parseHTMLAttributesForegroundColor($dom, $key);
        $this->parseHTMLAttributesBackgroundColor($dom, $key);
        $this->parseHTMLAttributesStrokeColor($dom, $key);
        $this->parseHTMLAttributesWidth($dom, $key);
        $this->parseHTMLAttributesHeight($dom, $key);
        $this->parseHTMLAttributesAlignment($dom, $key);
        $this->parseHTMLAttributesVerticalAlignment($dom, $key);
        $this->parseHTMLAttributesStroke($dom, $key);
        $this->parseHTMLAttributesFill($dom, $key);
        $this->parseHTMLAttributesClip($dom, $key);
    }

    /**
     * Applies display-related HTML attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesDisplayProperty(array &$dom, int $key): void
    {
        if (!empty($dom[$key]['attribute']['display'])) {
            $dom[$key]['hide'] = (\trim(\strtolower($dom[$key]['attribute']['display'])) == 'none');
        }
    }

    /**
     * Applies border-related HTML attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesBorderProperty(array &$dom, int $key): void
    {
        if (!empty($dom[$key]['attribute']['border'])) {
            $borderstyle = $this->getCSSBorderStyle($dom[$key]['attribute']['border'] . ' solid black');
            if (!empty($borderstyle)) {
                $dom[$key]['border']['LTRB'] = $borderstyle;
            }
        }
    }

    /**
     * Applies font-tag attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesFontTag(array &$dom, int $key): void
    {
        // check for font tag
        if ($dom[$key]['value'] == 'font') {
            // font family
            if (!empty($dom[$key]['attribute']['face'])) {
                // Keep the raw face value and defer font resolution to insert().
                $dom[$key]['fontname'] = \trim((string) $dom[$key]['attribute']['face']);
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
    }

    /**
     * Forces natural alignment for list elements.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesListAlignment(array &$dom, int $key): void
    {
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
    }

    /**
     * Applies default sizing for small/sup/sub elements.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesSmallSupSub(array &$dom, int $key): void
    {
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
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$key]['fontsize'] = \floatval($dom[$key]['fontsize']) * self::FONT_SMALL_RATIO;
            }
        }
    }

    /**
     * Ensures the font style string is initialized.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesEnsureFontStyle(array &$dom, int $key): void
    {
        if (empty($dom[$key]['fontstyle']) || !\is_string($dom[$key]['fontstyle'])) {
            // @phpstan-ignore parameterByRef.type
            $dom[$key]['fontstyle'] = '';
        }
    }

    /**
     * Applies bold defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesStrongBold(array &$dom, int $key): void
    {
        if (
            ($dom[$key]['value'] == 'strong')
            || ($dom[$key]['value'] == 'b')
        ) {
            $dom[$key]['fontstyle'] .= 'B';
        }
    }

    /**
     * Applies italic defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesEmItalic(array &$dom, int $key): void
    {
        if (
            ($dom[$key]['value'] == 'em')
            || ($dom[$key]['value'] == 'i')
        ) {
            $dom[$key]['fontstyle'] .= 'I';
        }
    }

    /**
     * Applies underline defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesUnderline(array &$dom, int $key): void
    {
        if ($dom[$key]['value'] == 'u') {
            $dom[$key]['fontstyle'] .= 'U';
        }
    }

    /**
     * Applies strike-through defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesStrikeThrough(array &$dom, int $key): void
    {
        if (
            ($dom[$key]['value'] == 'del')
            || ($dom[$key]['value'] == 's')
            || ($dom[$key]['value'] == 'strike')
        ) {
            $dom[$key]['fontstyle'] .= 'D';
        }
    }

    /**
     * Applies underline defaults for anchors when CSS does not override it.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesAnchorUnderline(array &$dom, int $key): void
    {
        if (
            empty($dom[$key]['style']['text-decoration'])
            && ($dom[$key]['value'] == 'a')
        ) {
            $dom[$key]['fontstyle'] .= 'U';
        }
    }

    /**
     * Applies monospace font defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesMonospace(array &$dom, int $key): void
    {
        if (
            ($dom[$key]['value'] == 'pre')
            || ($dom[$key]['value'] == 'tt')
            || ($dom[$key]['value'] == 'code')
        ) {
            $dom[$key]['fontname'] = self::FONT_MONO;
        }
    }

    /**
     * Applies heading defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesHeadingDefaults(array &$dom, int $key): void
    {
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
            $hasMarginShorthand = isset($dom[$key]['style']['margin'])
                && \trim((string) $dom[$key]['style']['margin']) !== '';
            $hasMarginTop = isset($dom[$key]['style']['margin-top'])
                && \trim((string) $dom[$key]['style']['margin-top']) !== '';
            $hasMarginBottom = isset($dom[$key]['style']['margin-bottom'])
                && \trim((string) $dom[$key]['style']['margin-bottom']) !== '';
            // apply default proportional top/bottom margin unless overridden by CSS
            if (!$hasMarginShorthand && !$hasMarginTop) {
                $dom[$key]['margin']['T'] = $this->toUnit((float) $dom[$key]['fontsize'] * 0.67);
            }
            if (!$hasMarginShorthand && !$hasMarginBottom) {
                $dom[$key]['margin']['B'] = $this->toUnit((float) $dom[$key]['fontsize'] * 0.67);
            }
        }
    }

    /**
     * Applies default block margins.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesBlockMargins(array &$dom, int $key): void
    {
        // apply default 1em top/bottom margin for block elements that have it in standard CSS
        // skip nested lists (ol/ul/dl inside li) as browsers do not apply margins to sublists
        if (\in_array($dom[$key]['value'], ['p', 'ol', 'ul', 'dl', 'blockquote', 'pre'], true)) {
            $hasMarginShorthand = isset($dom[$key]['style']['margin'])
                && \trim((string) $dom[$key]['style']['margin']) !== '';
            $hasMarginTop = isset($dom[$key]['style']['margin-top'])
                && \trim((string) $dom[$key]['style']['margin-top']) !== '';
            $hasMarginBottom = isset($dom[$key]['style']['margin-bottom'])
                && \trim((string) $dom[$key]['style']['margin-bottom']) !== '';
            $isSublist = \in_array($dom[$key]['value'], ['ol', 'ul', 'dl'], true)
                && \is_int($dom[$key]['parent'])
                && ($dom[$key]['parent'] > 0)
                && isset($dom[$dom[$key]['parent']]['value'])
                && ($dom[$dom[$key]['parent']]['value'] === 'li');
            if (!$isSublist) {
                if (!$hasMarginShorthand && !$hasMarginTop) {
                    $dom[$key]['margin']['T'] = $this->toUnit((float) $dom[$key]['fontsize']);
                }
                if (!$hasMarginShorthand && !$hasMarginBottom) {
                    $dom[$key]['margin']['B'] = $this->toUnit((float) $dom[$key]['fontsize']);
                }
            }
        }
    }

    /**
     * Applies table defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesTableDefaults(array &$dom, int $key): void
    {
        if (($dom[$key]['value'] == 'table')) {
            $dom[$key]['rows'] = 0; // number of rows
            $dom[$key]['trids'] = []; // IDs of TR elements
            $dom[$key]['thead'] = ''; // table header rows
        }
    }

    /**
     * Applies caption defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesCaptionDefaults(array &$dom, int $key): void
    {
        if (($dom[$key]['value'] == 'caption')) {
            $parent = \is_int($dom[$key]['parent']) ? $dom[$key]['parent'] : 0;
            if (
                empty($dom[$key]['style']['text-align'])
                && empty($dom[$key]['attribute']['align'])
                && empty($dom[$key]['align'])
            ) {
                // Match browser default caption behavior when author style is absent.
                $dom[$key]['align'] = 'C';
            }

            if (
                empty($dom[$key]['style']['caption-side'])
                && isset($dom[$parent]['caption-side'])
                && \is_string($dom[$parent]['caption-side'])
            ) {
                $dom[$key]['caption-side'] = $dom[$parent]['caption-side'];
            }

            if (
                isset($dom[$parent]['value'])
                && \is_string($dom[$parent]['value'])
                && \in_array($dom[$parent]['value'], ['table', 'tablehead'], true)
            ) {
                // Defer caption rendering to table open/close for deterministic placement.
                $dom[$key]['hide'] = true;
            }
        }
    }

    /**
     * Applies table row defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     * @param bool $thead Whether the row belongs to a table head block.
     */
    protected function parseHTMLAttributesTableRowDefaults(array &$dom, int $key, bool $thead): void
    {
        if ($dom[$key]['value'] == 'tr') {
            $dom[$key]['cols'] = 0;
            /** @var array<int, THTMLAttrib> $dom */
            if ($thead) {
                // @phpstan-ignore parameterByRef.type
                $dom[$key]['thead'] = 'true';
                // rows on thead block are printed as a separate table
            } else {
                $parent = \is_int($dom[$key]['parent']) ? $dom[$key]['parent'] : 0;
                while (
                    isset($dom[$parent]['value'])
                    && !\in_array($dom[$parent]['value'], ['table', 'tablehead'], true)
                    && isset($dom[$parent]['parent'])
                    && \is_int($dom[$parent]['parent'])
                    && ($dom[$parent]['parent'] !== $parent)
                ) {
                    $parent = $dom[$parent]['parent'];
                }
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
    }

    /**
     * Applies table cell defaults.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesTableCellDefaults(array &$dom, int $key): void
    {
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
                // @phpstan-ignore-next-line parameterByRef.type
                $dom[$parent]['cols'] += \intval($colspan);
            }

            // Keep legacy HTML table behavior: table border attribute propagates
            // to cells when no explicit cell border is provided.
            if (
                empty($dom[$key]['attribute']['border'])
                && !isset($dom[$key]['style']['border'])
                && !isset($dom[$key]['style']['border-top'])
                && !isset($dom[$key]['style']['border-right'])
                && !isset($dom[$key]['style']['border-bottom'])
                && !isset($dom[$key]['style']['border-left'])
            ) {
                $tableParent = $parent;
                while (
                    isset($dom[$tableParent]['value'])
                    && \is_scalar($dom[$tableParent]['value'])
                    && !\in_array((string) $dom[$tableParent]['value'], ['table', 'tablehead'], true)
                    && isset($dom[$tableParent]['parent'])
                    && \is_int($dom[$tableParent]['parent'])
                    && ($dom[$tableParent]['parent'] !== $tableParent)
                ) {
                    $tableParent = $dom[$tableParent]['parent'];
                }

                if (
                    isset($dom[$tableParent]['attribute']['border'])
                    && !empty($dom[$tableParent]['attribute']['border'])
                    && \is_string($dom[$tableParent]['attribute']['border'])
                ) {
                    $borderStyle = $this->getCSSBorderStyle($dom[$tableParent]['attribute']['border'] . ' solid black');
                    if (!empty($borderStyle)) {
                        if (!isset($dom[$key]['border']) || !\is_array($dom[$key]['border'])) {
                            // @phpstan-ignore-next-line parameterByRef.type
                            $dom[$key]['border'] = [];
                        }

                        // @phpstan-ignore-next-line parameterByRef.type
                        $dom[$key]['border']['LTRB'] = $borderStyle;
                    }
                }
            }
        }
    }

    /**
     * Applies text direction attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesDirection(array &$dom, int $key): void
    {
        // text direction
        if (!empty($dom[$key]['attribute']['dir'])) {
            $dom[$key]['dir'] = $dom[$key]['attribute']['dir'];
        }
    }

    /**
     * Applies foreground color attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesForegroundColor(array &$dom, int $key): void
    {
        // set foreground color attribute
        if (!empty($dom[$key]['attribute']['color'])) {
            $dom[$key]['fgcolor'] = $this->getCSSColor($dom[$key]['attribute']['color']);
        } elseif (
            empty($dom[$key]['style']['color'])
            && ($dom[$key]['value'] == 'a')
        ) {
            $dom[$key]['fgcolor'] = 'blue';
        }
    }

    /**
     * Applies background color attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesBackgroundColor(array &$dom, int $key): void
    {
        // set background color attribute
        if (!empty($dom[$key]['attribute']['bgcolor'])) {
            $dom[$key]['bgcolor'] = $this->getCSSColor($dom[$key]['attribute']['bgcolor']);
        }
    }

    /**
     * Applies stroke color attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesStrokeColor(array &$dom, int $key): void
    {
        // set stroke color attribute
        if (!empty($dom[$key]['attribute']['strokecolor'])) {
            $dom[$key]['strokecolor'] = $this->getCSSColor($dom[$key]['attribute']['strokecolor']);
        }
    }

    /**
     * Applies width attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesWidth(array &$dom, int $key): void
    {
        // check for width attribute
        if (isset($dom[$key]['attribute']['width'])) {
            $dom[$key]['width'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['attribute']['width']));
        }
    }

    /**
     * Applies height attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesHeight(array &$dom, int $key): void
    {
        // check for height attribute
        if (isset($dom[$key]['attribute']['height'])) {
            $dom[$key]['height'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['attribute']['height']));
        }
    }

    /**
     * Applies text alignment attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesAlignment(array &$dom, int $key): void
    {
        // check for text alignment
        if (
            (!empty($dom[$key]['attribute']['align']))
            && ($dom[$key]['value'] !== 'img')
        ) {
            $dom[$key]['align'] = \strtoupper($dom[$key]['attribute']['align'][0]);
        }
    }

    /**
     * Applies vertical alignment attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesVerticalAlignment(array &$dom, int $key): void
    {
        // check for vertical alignment
        $hasCssValign = false;
        if (!empty($dom[$key]['style']['vertical-align']) && \is_string($dom[$key]['style']['vertical-align'])) {
            $cssValign = \strtolower(\trim($dom[$key]['style']['vertical-align']));
            $hasCssValign = \in_array($cssValign, ['top', 'middle', 'bottom'], true);
        }
        if (
            !$hasCssValign
            && !empty($dom[$key]['attribute']['valign'])
            && \is_string($dom[$key]['attribute']['valign'])
        ) {
            $valign = \strtolower(\trim($dom[$key]['attribute']['valign']));
            if (\in_array($valign, ['top', 'middle', 'bottom'], true)) {
                $dom[$key]['valign'] = $valign;
            }
        }
    }

    /**
     * Applies stroke width attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesStroke(array &$dom, int $key): void
    {
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
    }

    /**
     * Applies fill attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesFill(array &$dom, int $key): void
    {
        if (isset($dom[$key]['attribute']['fill'])) {
            // font fill
            $dom[$key]['fill'] = ($dom[$key]['attribute']['fill'] == 'true');
        }
    }

    /**
     * Applies clip attributes.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     * @param int $key Current element ID.
     */
    protected function parseHTMLAttributesClip(array &$dom, int $key): void
    {
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
     * Whether list bullets should be emitted as Unicode text glyphs.
     */
    protected function canUseUnicodeListBulletGlyphs(): bool
    {
        return $this->isunicode && !$this->font->isCurrentByteFont();
    }

    /**
     * Returns the PDF code for an HTML list bullet or ordered list item symbol.
     *
     * @param int    $depth  List nesting level.
     * @param int    $count  List entry position, starting form 1.
     * @param float  $posx   Abscissa of upper-left corner.
     * @param float  $posy   Ordinate of upper-left corner.
     * @param string $type   Type of list.
    * @param array<string, mixed> $markerStyles Marker style declarations from li::marker.
     *
     * @return string
     */
    protected function getHTMLliBullet(
        int $depth,
        int $count,
        float $posx = 0,
        float $posy = 0,
        string $type = '',
        array $markerStyles = [],
    ): string {
        $markerState = [];
        $markerPrefix = '';
        $img = ['', '', '0', '0', ''];
        $markerColor = '';
        if (isset($markerStyles['color']) && \is_string($markerStyles['color'])) {
            $markerColor = \trim($markerStyles['color']);
        }
        if (!empty($markerStyles)) {
            $markerPrefix = $this->getStartMarkerStyle($markerStyles, $markerState);
        }

        switch ($type) {
            case '^': // special symbol used for avoid justification of rect bullet
                if (!empty($markerStyles)) {
                    $this->getStopMarkerStyle($markerState);
                }
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
        $fontheight = $this->toUnit((float) ($font['height'] ?? 0.0));
        $fontascent = $this->toUnit((float) ($font['ascent'] ?? 0.0));
        $fontTop = $posy - $fontascent;
        $txti = '';

        switch ($type) {
            // unordered types
            case 'none':
                break;
            case 'disc':
                if ($this->canUseUnicodeListBulletGlyphs()) {
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
                    'lineColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                $result = $this->graph->getStartTransform()
                . $this->graph->getCircle(
                    $posx,
                    $fontTop + ($fontheight / 2),
                    $rad,
                    0,
                    360,
                    'F',
                    $style,
                    8,
                ) . $this->graph->getStopTransform();
                if (!empty($markerStyles)) {
                    $result = $markerPrefix . $result . $this->getStopMarkerStyle($markerState);
                }
                return $result;
            case 'circle':
                if ($this->canUseUnicodeListBulletGlyphs()) {
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
                    'lineColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                $result = $this->graph->getStartTransform()
                . $this->graph->getCircle(
                    $posx,
                    $fontTop + ($fontheight / 2),
                    $rad,
                    0,
                    360,
                    'D',
                    $style,
                    8,
                ) . $this->graph->getStopTransform();
                if (!empty($markerStyles)) {
                    $result = $markerPrefix . $result . $this->getStopMarkerStyle($markerState);
                }
                return $result;
            case 'square':
                if ($this->canUseUnicodeListBulletGlyphs()) {
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
                    'lineColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('lineColor', 'black'),
                    'fillColor' => ($markerColor !== '')
                        ? $markerColor
                        : (string) $this->graph->getLastStyleProperty('fillColor', 'black'),
                ];
                $result = $this->graph->getStartTransform()
                . $this->graph->getBasicRect(
                    $posx,
                    $fontTop + (($fontheight - $len) / 2),
                    $len,
                    $len,
                    'F',
                    $style,
                ) . $this->graph->getStopTransform();
                if (!empty($markerStyles)) {
                    $result = $markerPrefix . $result . $this->getStopMarkerStyle($markerState);
                }
                return $result;
            case 'img':
                // 1=>type, 2=>width, 3=>height, 4=>image.ext
                $lspace += \floatval($img[2]);
                $posx += $this->rtl ? $lspace : -$lspace;
                $imgtype = strtolower($img[1]);
                $imgsrc = isset($img[4]) ? (string) $img[4] : '';
                if (
                    ($imgtype === 'svg+xml')
                    || \str_starts_with($imgsrc, '@<svg')
                    || \str_contains($imgsrc, '<svg')
                    || \str_starts_with($imgsrc, 'data:image/svg+xml')
                ) {
                    $imgtype = 'svg';
                    if (\str_starts_with($imgsrc, 'data:image/svg+xml')) {
                        if (\preg_match('/^data:image\/svg\+xml(?:;base64)?,(.*)$/i', $imgsrc, $svgdata)) {
                            $payload = (string) $svgdata[1];
                            $rawsvg = \rawurldecode($payload);
                            if (\str_contains($imgsrc, ';base64,')) {
                                $decoded = \base64_decode($payload, true);
                                if ($decoded !== false && $decoded !== '') {
                                    $rawsvg = $decoded;
                                }
                            }

                            if ($rawsvg !== '') {
                                $imgsrc = '@' . $rawsvg;
                            }
                        }
                    }
                }
                $imgwidth = \floatval($img[2]);
                $imgheight = \floatval($img[3]);
                $imgposy = ($posy - $fontascent) + (($fontheight - $imgheight) / 2);
                $pageheight = $this->page->getPage()['height'];
                $result = '';
                switch ($imgtype) {
                    case 'svg':
                        $svgid = $this->addSVG(
                            $imgsrc,
                            $posx,
                            $imgposy,
                            $imgwidth,
                            $imgheight,
                            $pageheight,
                        );
                        $result = $this->getSetSVG($svgid);
                        break;
                    default:
                        $imgid = $this->image->add($imgsrc);
                        $result = $this->image->getSetImage(
                            $imgid,
                            $posx,
                            $imgposy,
                            $imgwidth,
                            $imgheight,
                            $pageheight,
                        );
                        break;
                }
                if (!empty($markerStyles)) {
                    $result = $markerPrefix . $result . $this->getStopMarkerStyle($markerState);
                }
                return $result;
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
            case 'i':
            case 'lower-roman':
                $txti = \strtolower($this->intToRoman($count));
                break;
            case 'I':
            case 'upper-roman':
                $txti = $this->intToRoman($count);
                break;
            case 'decimal-leading-zero':
                $txti = \number_format($count, 0, '.', '');
                if ($count >= 0 && $count < 10) {
                    $txti = '0' . $txti;
                }
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
            if (!empty($markerStyles)) {
                $this->getStopMarkerStyle($markerState);
            }
            return '';
        }

        // append dot separator for ordered list types only
        $unorderedTypes = ['disc', 'circle', 'square'];
        if (!\in_array($type, $unorderedTypes, true)) {
            $txti = $this->rtl ? '.' . $txti : $txti . '.';
        }

        $lspace += $this->getStringWidth($txti);
        $posx += $this->rtl ? $lspace : -$lspace;

        $out = $this->getTextLine($txti, $posx, $posy);

        if (!empty($markerPrefix)) {
            $out = $markerPrefix . $out;
        }

        if (!empty($markerStyles)) {
            $out .= $this->getStopMarkerStyle($markerState);
        }

        return $out;
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
     *
     * @param THTMLRenderContext $hrc HTML render context
     */
    protected function initHTMLCellContext(
        array &$hrc,
        float $posx,
        float $posy,
        float $width,
        float $height,
    ): void {
        $basefont = $this->getHTMLBaseFontName();
        $cellctx = [
            'originx' => $posx,
            'originy' => $posy,
            'lineoriginx' => $posx,
            'maxwidth' => $width,
            'maxheight' => $height,
            'floatrowleftw' => 0.0,
            'floatrowrightw' => 0.0,
            'floatrowtop' => 0.0,
            'floatrowbottom' => 0.0,
            'lineadvance' => 0.0,
            'linebottom' => 0.0,
            'lineascent' => 0.0,
            'linewordspacing' => 0.0,
            'linewrapped' => false,
            'textindentapplied' => false,
            'pendingblockmarginb' => 0.0,
            'regionoffset' => 0.0,
            'basefont' => $basefont,
        ];
        $hrc['cellctx'] = $cellctx;
        $hrc['fontcache'] = [];
        $hrc['liststack'] = [];
        $hrc['listack'] = [];
        $hrc['tablestack'] = [];
        $hrc['bcellctx'] = [];
        $hrc['blockbuf'] = [];
        $hrc['linkstack'] = [];
        $hrc['prelevel'] = 0;
    }

    /**
     * Reset the temporary HTML cell rendering context.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function clearHTMLCellContext(array &$hrc): void
    {
        $cellctx = [
            'originx' => 0.0,
            'originy' => 0.0,
            'lineoriginx' => 0.0,
            'maxwidth' => 0.0,
            'maxheight' => 0.0,
            'floatrowleftw' => 0.0,
            'floatrowrightw' => 0.0,
            'floatrowtop' => 0.0,
            'floatrowbottom' => 0.0,
            'lineadvance' => 0.0,
            'linebottom' => 0.0,
            'lineascent' => 0.0,
            'linewordspacing' => 0.0,
            'linewrapped' => false,
            'textindentapplied' => false,
            'pendingblockmarginb' => 0.0,
            'regionoffset' => 0.0,
            'basefont' => 'helvetica',
        ];
        $hrc['cellctx'] = $cellctx;
        $hrc['fontcache'] = [];
        $hrc['liststack'] = [];
        $hrc['listack'] = [];
        $hrc['tablestack'] = [];
        $hrc['bcellctx'] = [];
        $hrc['blockbuf'] = [];
        $hrc['linkstack'] = [];
        $hrc['prelevel'] = 0;
    }

    /**
     * Estimate the total rendered height for rows inside a table-header fragment.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function estimateHTMLTableHeadHeight(array &$hrc, string $thead): float
    {
        if ($thead === '') {
            return 0.0;
        }

        $dom = $this->getHTMLDOM($thead);
        $height = 0.0;
        $rowheight = 0.0;
        $inrow = false;
        $rowcount = 0;

        // Resolve the table-level cellpadding/cellspacing defaults the renderer
        // would apply at runtime (parseHTMLTagOPENtable / parseHTMLTagOPENtd).
        // The standalone thead DOM does not run those handlers, so the cell
        // padding default from the <table cellpadding="..."> attribute would
        // otherwise be lost, causing the estimated header height to be smaller
        // than the actually rendered one.
        $tablecellpadding = 0.0;
        $tablecellspacingv = 0.0;
        foreach ($dom as $elm) {
            if (
                empty($elm['tag']) || empty($elm['opening']) || empty($elm['value'])
                || !\is_string($elm['value'])
                || (($elm['value'] !== 'table') && ($elm['value'] !== 'tablehead'))
            ) {
                continue;
            }
            $attr = (isset($elm['attribute']) && \is_array($elm['attribute'])) ? $elm['attribute'] : [];
            if (!empty($attr['cellpadding']) && \is_numeric($attr['cellpadding'])) {
                $tablecellpadding = $this->toUnit($this->getUnitValuePoints((string) $attr['cellpadding']));
            }
            if (!empty($attr['cellspacing']) && \is_numeric($attr['cellspacing'])) {
                $tablecellspacingv = $this->toUnit($this->getUnitValuePoints((string) $attr['cellspacing']));
            } elseif (!empty($elm['border-spacing']) && \is_array($elm['border-spacing'])) {
                $tablecellspacingv = (isset($elm['border-spacing']['V']) && \is_numeric($elm['border-spacing']['V']))
                    ? (float) $elm['border-spacing']['V'] : 0.0;
            }
            if ((($elm['border-collapse'] ?? 'separate') === 'collapse')) {
                $tablecellspacingv = 0.0;
            }
            break;
        }

        $savedDom = $hrc['dom'];
        $hrc['dom'] = $dom;

        foreach ($dom as $key => $elm) {
            if (empty($elm['tag']) || empty($elm['value']) || !\is_string($elm['value'])) {
                continue;
            }

            if ($elm['opening'] && ($elm['value'] === 'tr')) {
                $inrow = true;
                $rowheight = 0.0;
                continue;
            }

            if ($inrow && $elm['opening'] && (($elm['value'] === 'td') || ($elm['value'] === 'th'))) {
                $padT = (float) $elm['padding']['T'];
                $padR = (float) $elm['padding']['R'];
                $padB = (float) $elm['padding']['B'];
                $padL = (float) $elm['padding']['L'];
                if (
                    ($tablecellpadding > 0.0)
                    && ($padT === 0.0) && ($padR === 0.0)
                    && ($padB === 0.0) && ($padL === 0.0)
                ) {
                    $padT = $tablecellpadding;
                    $padB = $tablecellpadding;
                }
                $cellh = $this->getHTMLLineAdvance($hrc, $key)
                    + $padT
                    + $padB
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
                // Each closed row advances the cursor by rowheight + cellspacing
                // (parseHTMLTagCLOSEtr); the table opening also advances by one
                // cellspacing (parseHTMLTagOPENtable).
                $height += $rowheight + $tablecellspacingv;
                if ($rowcount === 0) {
                    $height += $tablecellspacingv;
                }
                ++$rowcount;
                $rowheight = 0.0;
                $inrow = false;
            }
        }

        $hrc['dom'] = $savedDom;

        return $height;
    }

    /**
     * Replay stored table-header HTML at the current row position.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function replayHTMLTableHead(
        array &$hrc,
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
        $theadh = $this->estimateHTMLTableHeadHeight($hrc, $thead);
        if ($theadh > 0.0) {
            $tpy += $theadh;
            $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
        }

        return $out;
    }

    /**
     * Inject explicit COL widths into a serialized table-header fragment so
     * replayed headers on continuation pages use the same column geometry
     * computed for the original table.
     *
     * @param array<int, float> $colwidths
     */
    protected function injectHTMLTableHeadColWidths(string $thead, array $colwidths): string
    {
        if (($thead === '') || ($colwidths === [])) {
            return $thead;
        }

        if (\strpos($thead, 'data-tcpdf-colwidths="1"') !== false) {
            return $thead;
        }

        $tablePos = \strpos($thead, '<table');
        if ($tablePos === false) {
            return $thead;
        }

        $tagEnd = \strpos($thead, '>', $tablePos);
        if ($tagEnd === false) {
            return $thead;
        }

        $unit = (\in_array($this->unit, self::VALIDUNITS, true)) ? $this->unit : 'mm';
        $colgroup = '<colgroup data-tcpdf-colwidths="1">';
        foreach ($colwidths as $width) {
            if (!\is_numeric($width) || ((float) $width <= 0.0)) {
                continue;
            }

            $colgroup .= '<col style="width:' . \sprintf('%.6F', (float) $width) . $unit . ';" />';
        }

        $colgroup .= '</colgroup>';

        return \substr($thead, 0, $tagEnd + 1)
            . $colgroup
            . \substr($thead, $tagEnd + 1);
    }

    /**
     * Estimate rendered height for a deferred table-caption fragment.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function estimateHTMLTableCaptionHeight(array &$hrc, string $caption): float
    {
        if ($caption === '') {
            return 0.0;
        }

        $dom = $this->getHTMLDOM($caption);
        $savedDom = $hrc['dom'];
        $hrc['dom'] = $dom;

        $height = 0.0;
        foreach ($dom as $key => $elm) {
            if (
                empty($elm['tag']) || empty($elm['opening']) || empty($elm['value'])
                || !\is_string($elm['value']) || ($elm['value'] !== 'caption')
            ) {
                continue;
            }

            $height = $this->getHTMLLineAdvance($hrc, $key)
                + (float) $elm['padding']['T']
                + (float) $elm['padding']['B']
                + (float) $elm['margin']['T']
                + (float) $elm['margin']['B'];
            break;
        }

        $hrc['dom'] = $savedDom;

        return $height;
    }

    /**
     * Replay deferred bottom caption HTML at the current table cursor.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function replayHTMLTableCaption(
        array &$hrc,
        string $caption,
        float &$tpx,
        float &$tpy,
        float &$tpw,
    ): string {
        if ($caption === '') {
            return '';
        }

        // Defer-caption replay can be invoked while a heading font is active.
        // Use a neutral non-bold font for DOM root defaults, then restore state.
        $callerfont = $this->captureHTMLCallerFontState();
        $basefont = '';
        if (isset($hrc['cellctx']['basefont']) && \is_string($hrc['cellctx']['basefont'])) {
            $basefont = (string) $hrc['cellctx']['basefont'];
        }
        if ($basefont === '') {
            $basefont = $callerfont['family'];
        }

        $this->font->insert($this->pon, $basefont, '', $callerfont['size']);

        $tmph = 0.0;
        $out = $this->getHTMLCell($caption, $tpx, $tpy, $tpw, $tmph);
        $this->font->insert($this->pon, $callerfont['family'], $callerfont['style'], $callerfont['size']);
        $captionh = $this->estimateHTMLTableCaptionHeight($hrc, $caption);
        if ($captionh > 0.0) {
            $tpy += $captionh;
            $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
        }

        return $out;
    }

    /**
     * Estimate the total rendered height for a table row starting at the given TR node.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function estimateHTMLTableRowHeight(array &$hrc, int $trkey): float
    {
        $callerfont = $this->captureHTMLCallerFontState();

        try {
        /** @var array<int, THTMLAttrib> $dom */
            $dom = &$hrc['dom'];

            if (empty($dom[$trkey]) || empty($dom[$trkey]['tag']) || empty($dom[$trkey]['opening'])) {
                return 0.0;
            }

        // Fetch parent-table per-column widths and cellpadding fallback so we can
        // measure the wrapped text height inside each cell, mirroring what the
        // renderer will actually produce. Without this the estimate uses a single
        // line advance and misses multi-line cell content, leading to late page
        // breaks where the last row spills below the page bottom (see example 018).
            $tableColWidths = [];
            $tableCellPad = 0.0;
            $parentTableKey = isset($dom[$trkey]['parent']) && \is_int($dom[$trkey]['parent'])
            ? $dom[$trkey]['parent'] : 0;
            if (
                $parentTableKey > 0
                && isset($dom[$parentTableKey])
                && !empty($dom[$parentTableKey]['tag'])
                && \is_string($dom[$parentTableKey]['value'])
                && \in_array($dom[$parentTableKey]['value'], ['table', 'tablehead'], true)
            ) {
                if (
                    isset($dom[$parentTableKey]['pendingcolwidths'])
                    && \is_array($dom[$parentTableKey]['pendingcolwidths'])
                ) {
                    $tableColWidths = $dom[$parentTableKey]['pendingcolwidths'];
                }
                if (
                    isset($dom[$parentTableKey]['pendingcellpadding'])
                    && \is_numeric($dom[$parentTableKey]['pendingcellpadding'])
                ) {
                    $tableCellPad = (float) $dom[$parentTableKey]['pendingcellpadding'];
                }
            }

            $rowheight = 0.0;
            $depth = 0;
            $colidx = -1;
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

                ++$colidx;

                // Determine the cell's content area width for line-wrap measurement.
                $padL = (float) $elm['padding']['L'];
                $padR = (float) $elm['padding']['R'];
                $padT = (float) $elm['padding']['T'];
                $padB = (float) $elm['padding']['B'];
                if (($padL <= 0.0) && ($padR <= 0.0) && ($padT <= 0.0) && ($padB <= 0.0)) {
                    // Apply the table's cellpadding HTML attribute fallback when no
                    // explicit CSS/per-cell padding was set, mirroring parseHTMLTagOPENtd.
                    $padL = $padR = $padT = $padB = $tableCellPad;
                }

                $colwidth = (isset($tableColWidths[$colidx]) && \is_numeric($tableColWidths[$colidx]))
                ? (float) $tableColWidths[$colidx] : 0.0;
                $contentWidth = \max(0.0, $colwidth - $padL - $padR);

                $cellInner = $this->estimateHTMLCellContentHeight($hrc, $key, $contentWidth);
                $cellh = $cellInner
                + $padT
                + $padB
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
        } finally {
            $this->restoreHTMLCallerFontState($callerfont);
        }
    }

    /**
     * Estimate the height of a single TD/TH cell's inner content for the given
     * content-area width (already net of padding). Walks inline text fragments
     * and common inline tags to measure wrapped line count.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function estimateHTMLCellContentHeight(array &$hrc, int $cellkey, float $width): float
    {
        $callerfont = $this->captureHTMLCallerFontState();

        try {
        /** @var array<int, THTMLAttrib> $dom */
            $dom = &$hrc['dom'];

            if (empty($dom[$cellkey]) || empty($dom[$cellkey]['tag']) || empty($dom[$cellkey]['opening'])) {
                return 0.0;
            }

            $endkey = $this->findHTMLClosingTagIndex($dom, $cellkey);
            if ($endkey <= $cellkey) {
                return $this->getHTMLLineAdvance($hrc, $cellkey);
            }

            $lineadvance = $this->getHTMLLineAdvance($hrc, $cellkey);
            /** @var float $lineadvance */
            $height = 0.0;
            /** @var float $height */
            $inlinewidth = 0.0;
            /** @var float $inlinewidth */
            $inlineadvance = 0.0;
            /** @var float $inlineadvance */
            $hasinlinecontent = false;
            /** @var bool $hasinlinecontent */

            for ($key = ($cellkey + 1); $key < $endkey; ++$key) {
                $elm = $dom[$key];

                if (empty($elm['tag'])) {
                    $text = (string) $elm['value'];
                    if ($text === '') {
                        continue;
                    }

                    $text = $this->normalizeHTMLText($hrc, $text, $key);
                    if ($text === '') {
                        continue;
                    }

                    $forcedir = ($elm['dir'] === 'rtl') ? 'R' : '';
                    $this->getHTMLFontMetric($hrc, $key);
                    $ordarr = [];
                    $dim = $this->getHTMLDefaultTextDims();
                    $this->prepareHTMLText($text, $ordarr, $dim, $forcedir);

                    $fragmentadvance = $this->getHTMLLineAdvance($hrc, $key);
                    if (($width <= 0.0) || ($ordarr === [])) {
                        $inlineadvance = \max($inlineadvance, $fragmentadvance);
                        $hasinlinecontent = true;
                        continue;
                    }

                    $lines = $this->splitLines(
                        $ordarr,
                        $dim,
                        $this->toPoints((float) $width),
                        $this->toPoints((float) $inlinewidth),
                    );
                    if ($lines === []) {
                        continue;
                    }

                    $inlineadvance = \max($inlineadvance, $fragmentadvance);
                    $hasinlinecontent = true;
                    if (\count($lines) === 1) {
                        $inlinewidth = (float) ($inlinewidth + $this->toUnit((float) $lines[0]['totwidth']));
                        continue;
                    }

                    $height = (float) ($height + $inlineadvance);

                    $lastline = \count($lines) - 1;
                    if ($lastline > 1) {
                        $height = (float) ($height + (($lastline - 1) * $fragmentadvance));
                    }

                    $inlinewidth = $this->toUnit((float) $lines[$lastline]['totwidth']);
                    $inlineadvance = $fragmentadvance;
                    $hasinlinecontent = true;
                    continue;
                }

                if (!empty($elm['opening'])) {
                    if ($elm['value'] === 'br') {
                        $state = $this->flushHTMLInlineLine(
                            $height,
                            $inlineadvance,
                            $hasinlinecontent,
                            $lineadvance,
                            true,
                        );
                        $height = $state['height'];
                        $inlinewidth = $state['inlinewidth'];
                        $inlineadvance = $state['inlineadvance'];
                        $hasinlinecontent = $state['hasinlinecontent'];
                        continue;
                    }

                    if ($elm['value'] === 'img') {
                        $state = $this->flushHTMLInlineLine(
                            $height,
                            $inlineadvance,
                            $hasinlinecontent,
                            $lineadvance,
                        );
                        $height = $state['height'];
                        $inlinewidth = $state['inlinewidth'];
                        $inlineadvance = $state['inlineadvance'];
                        $hasinlinecontent = $state['hasinlinecontent'];
                        $height = (float) ($height + ((!empty($elm['height']) && \is_numeric($elm['height']))
                            ? (float) $elm['height']
                            : $lineadvance));
                        continue;
                    }

                    if (\in_array($elm['value'], self::HTML_BLOCK_TAGS, true)) {
                        $state = $this->flushHTMLInlineLine(
                            $height,
                            $inlineadvance,
                            $hasinlinecontent,
                            $lineadvance,
                        );
                        $height = $state['height'];
                        $inlinewidth = $state['inlinewidth'];
                        $inlineadvance = $state['inlineadvance'];
                        $hasinlinecontent = $state['hasinlinecontent'];
                        $height = (float) ($height + ((float) $elm['margin']['T'] + (float) $elm['padding']['T']));
                    }

                    continue;
                }

                // Closing tag.
                if (\in_array($elm['value'], self::HTML_BLOCK_TAGS, true)) {
                    $state = $this->flushHTMLInlineLine(
                        $height,
                        $inlineadvance,
                        $hasinlinecontent,
                        $lineadvance,
                    );
                    $height = $state['height'];
                    $inlinewidth = $state['inlinewidth'];
                    $inlineadvance = $state['inlineadvance'];
                    $hasinlinecontent = $state['hasinlinecontent'];
                    $height = (float) ($height + ((float) $elm['margin']['B'] + (float) $elm['padding']['B']));
                }
            }

            $state = $this->flushHTMLInlineLine(
                $height,
                $inlineadvance,
                $hasinlinecontent,
                $lineadvance,
            );
            $height = $state['height'];
            $inlinewidth = $state['inlinewidth'];
            $inlineadvance = $state['inlineadvance'];
            $hasinlinecontent = $state['hasinlinecontent'];

            if ($height <= 0.0) {
                $height = $lineadvance;
            }

            return (float) $height;
        } finally {
            $this->restoreHTMLCallerFontState($callerfont);
        }
    }

    /**
     * Flush current inline fragment metrics into block height.
     *
     * @phpstan-return array{
     *     height: float,
     *     inlinewidth: float,
     *     inlineadvance: float,
     *     hasinlinecontent: bool
     * }
     */
    protected function flushHTMLInlineLine(
        float $height,
        float $inlineadvance,
        bool $hasinlinecontent,
        float $fallbackadvance,
        bool $forceempty = false,
    ): array {
        if ($hasinlinecontent) {
            $height += ($inlineadvance > 0.0) ? $inlineadvance : $fallbackadvance;
        } elseif ($forceempty) {
            $height += $fallbackadvance;
        }

        return [
            'height' => $height,
            'inlinewidth' => 0.0,
            'inlineadvance' => 0.0,
            'hasinlinecontent' => false,
        ];
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
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function estimateHTMLTextHeight(array &$hrc, int $key, string $text, float $width): float
    {
        $callerfont = $this->captureHTMLCallerFontState();

        try {
            if (!isset($hrc['dom'][$key])) {
                return 0.0;
            }

            $elm = $hrc['dom'][$key];
            $text = $this->normalizeHTMLText($hrc, $text, $key);
            if ($text === '') {
                return 0.0;
            }

            $forcedir = ($elm['dir'] === 'rtl') ? 'R' : '';
            $this->getHTMLFontMetric($hrc, $key);
            $ordarr = [];
            $dim = $this->getHTMLDefaultTextDims();
            $this->prepareHTMLText($text, $ordarr, $dim, $forcedir);

            $lineadvance = $this->getHTMLLineAdvance($hrc, $key);
            if (($width <= 0.0) || ($ordarr === [])) {
                return $lineadvance;
            }

            $lines = $this->splitLines($ordarr, $dim, $this->toPoints($width));
            return \max($lineadvance, \count($lines) * $lineadvance);
        } finally {
            $this->restoreHTMLCallerFontState($callerfont);
        }
    }

    /**
     * Estimate the height of a nobr subtree so it can be moved intact to a new region.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function estimateHTMLNobrHeight(array &$hrc, int $startkey, float $width): float
    {
        $callerfont = $this->captureHTMLCallerFontState();

        try {
        /** @var array<int, THTMLAttrib> $dom */
            $dom = &$hrc['dom'];

            if (empty($dom[$startkey]) || empty($dom[$startkey]['tag']) || empty($dom[$startkey]['opening'])) {
                return 0.0;
            }

            $starttag = (string) $dom[$startkey]['value'];
            if ($starttag === 'tr') {
                return $this->estimateHTMLTableRowHeight($hrc, $startkey);
            }

            $endkey = $this->findHTMLClosingTagIndex($dom, $startkey);
            if ($endkey <= $startkey) {
                return 0.0;
            }

            $height = 0.0;
            for ($key = ($startkey + 1); $key < $endkey; ++$key) {
                $elm = $dom[$key];

                if (empty($elm['tag'])) {
                    $height += $this->estimateHTMLTextHeight($hrc, $key, (string) $elm['value'], $width);
                    continue;
                }

                if (!empty($elm['opening'])) {
                    if ($elm['value'] === 'br') {
                        $height += $this->getHTMLLineAdvance($hrc, $key);
                        continue;
                    }

                    if ($elm['value'] === 'img') {
                        $height += (!empty($elm['height']) && \is_numeric($elm['height']))
                        ? (float) $elm['height']
                        : $this->getHTMLLineAdvance($hrc, $key);
                        continue;
                    }

                    if (($elm['value'] === 'input') || ($elm['value'] === 'output')) {
                        $height += $this->estimateHTMLTextHeight(
                            $hrc,
                            $key,
                            $this->getHTMLInputDisplayValue($elm),
                            $width,
                        );
                        continue;
                    }

                    if ($elm['value'] === 'select') {
                        $selectDisplay = $this->getHTMLSelectDisplayValue($elm);
                        $height += $this->estimateHTMLTextHeight($hrc, $key, $selectDisplay, $width);
                        continue;
                    }

                    if ($elm['value'] === 'textarea') {
                        $value = (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value']))
                        ? $elm['attribute']['value']
                        : '';
                        $height += $this->estimateHTMLTextHeight($hrc, $key, $value, $width);
                        continue;
                    }

                    if (($elm['value'] === 'table') || ($elm['value'] === 'tablehead') || ($elm['value'] === 'thead')) {
                        $subheight = 0.0;
                        $tableend = $this->findHTMLClosingTagIndex($dom, $key);
                        for ($idx = $key; $idx <= $tableend; ++$idx) {
                            $isOpenTr = !empty($dom[$idx]['tag']) && !empty($dom[$idx]['opening'])
                            && ($dom[$idx]['value'] === 'tr');
                            if ($isOpenTr) {
                                $subheight += $this->estimateHTMLTableRowHeight($hrc, $idx);
                            }
                        }
                        $height += $subheight;
                        $key = $tableend;
                        continue;
                    }
                }

                if (
                    !empty($elm['tag'])
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
                $height = $this->getHTMLLineAdvance($hrc, $startkey);
            }

            return $height;
        } finally {
            $this->restoreHTMLCallerFontState($callerfont);
        }
    }

    /**
     * Return the remaining vertical space in the current region or explicit cell box.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getHTMLRemainingHeight(array &$hrc, float $tpy): float
    {
        $region = $this->page->getRegion();
        $remaining = ((float) $region['RY'] + (float) $region['RH']) - $tpy;

        if ($hrc['cellctx']['maxheight'] > 0.0) {
            $remaining = \min(
                $remaining,
                ($hrc['cellctx']['originy'] + $hrc['cellctx']['maxheight']) - $tpy,
            );
        }

        return \max(0.0, $remaining);
    }

    /**
     * Break to the next page region when the required height does not fit.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function breakHTMLIfNeeded(
        array &$hrc,
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
        $remaining = $this->getHTMLRemainingHeight($hrc, $tpy);
        if (($requiredh <= ($remaining + self::WIDTH_TOLERANCE)) || ($tpy <= ($regiontop + self::WIDTH_TOLERANCE))) {
            return '';
        }

        $oldRX = (float) $region['RX'];
        $this->pageBreak();
        $region = $this->page->getRegion();
        $hrc['cellctx']['originy'] = (float) $region['RY'];
        $rxDelta = (float) $region['RX'] - $oldRX;
        $hrc['cellctx']['originx'] += $rxDelta;
        $hrc['cellctx']['regionoffset'] = ((float) ($hrc['cellctx']['regionoffset'] ?? 0.0)) + $rxDelta;
        $tpy = $hrc['cellctx']['originy'];
        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);

        return ($thead === '') ? '' : $this->replayHTMLTableHead($hrc, $thead, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Render the partial styled rectangles for all currently-open block-level
     * buffers up to the given vertical position, reset their accumulated
     * content, and return the concatenated PDF code so the caller can emit it
     * onto the current (about-to-end) page before triggering a page break.
     *
     * Buffers are rendered innermost-first so that nested blocks remain wrapped
     * inside their outer block's rectangle, mirroring the behavior of
     * closeHTMLBlock(). The caller is expected to update each remaining
     * buffer's `by` to the new region top after the page break.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function flushOpenBlockBuffers(array &$hrc, float $tpy): string
    {
        if (empty($hrc['blockbuf'])) {
            return '';
        }

        // Extend the partial rectangle bottom to the page region bottom so the
        // styled block (background/border) appears visually continuous across
        // the page break. After the break, each buffer's `by` is reset to the
        // new region top, so the next page's rectangle abuts this one with no
        // visible gap. Without this extension the rectangle would end at the
        // last rendered line and leave an empty strip down to the page margin.
        $pageBottom = $tpy + $this->getHTMLRemainingHeight($hrc, $tpy);

        $rendered = '';
        for ($i = \count($hrc['blockbuf']) - 1; $i >= 0; --$i) {
            /** @var THTMLBlockBuf $blk */
            $blk = $hrc['blockbuf'][$i];
            $openkey = (int) $blk['openkey'];
            // For <table> blocks the outer border must end at the last
            // rendered row's bottom, not the page region bottom: row borders
            // already draw horizontals and the next page replays the head
            // with its own top border, so extending the outer frame to the
            // page bottom would leave a tall empty bordered rectangle below
            // the last row on this page.
            $isTable = ($openkey >= 0)
                && isset($hrc['dom'][$openkey]['value'])
                && ($hrc['dom'][$openkey]['value'] === 'table');
            $blockBottom = $isTable ? $tpy : $pageBottom;
            $partialHeight = $blockBottom - (float) $blk['by'];
            $content = (string) $blk['buffer'] . $rendered;
            if (((float) $blk['bw'] > 0.0) && ($partialHeight > 0.0)) {
                $bstyles = ($openkey >= 0)
                    ? $this->getHTMLTableCellBorderStyles($hrc, $openkey)
                    : [];
                $fillstyle = ($openkey >= 0)
                    ? $this->getHTMLTableCellFillStyle($hrc, $openkey)
                    : null;
                if ($isTable) {
                    // Keep table border ownership in parseHTMLTagCLOSEtable();
                    // page-split block flush must paint only the background.
                    $bstyles = [];
                }
                $rendered = $this->renderHTMLTableCell(
                    (float) $blk['bx'],
                    (float) $blk['by'],
                    (float) $blk['bw'],
                    $partialHeight,
                    $partialHeight,
                    'top',
                    $bstyles,
                    $fillstyle,
                    $content,
                );
            } else {
                $rendered = $content;
            }

            $blk['buffer'] = '';
            $hrc['blockbuf'][$i] = $blk;
        }

        return $rendered;
    }

    /**
     * After a page break occurs while one or more HTML tables are open,
     * reset each open table's per-page top references (`originy`, `rowtop`)
     * to the new region top. Without this, the closing `</table>` would draw
     * the outer frame using the previous page's top, leaving a stale narrow
     * rectangle on the new page.
     *
     * Any in-progress rowspan cells reference the previous page's `rowtop`
     * and would render incorrectly across the break, so they are dropped.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function resetHTMLTableStackOnPageBreak(array &$hrc, float $tpy): void
    {
        if (empty($hrc['tablestack'])) {
            return;
        }

        // Account for region offset changes (e.g., multi-column layout shifts).
        $currentOffset = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);

        foreach ($hrc['tablestack'] as $tidx => $table) {
            $cellspacing = (float) $table['cellspacingv'];
            $table['originy'] = $tpy;
            $table['rowtop'] = $tpy + $cellspacing;
            $table['rowheight'] = 0.0;
            $table['cells'] = [];
            $table['rowspans'] = [];

            // Adjust table originx if region offset has changed since table opened.
            // This handles multi-column/multi-page layouts where content shifts horizontally.
            if (isset($table['regionoffset']) && \is_numeric($table['regionoffset'])) {
                $savedOffset = (float) $table['regionoffset'];
                $offsetDelta = $currentOffset - $savedOffset;
                if ($offsetDelta !== 0.0) {
                    $table['originx'] = ((float) $table['originx']) + $offsetDelta;
                    $table['regionoffset'] = $currentOffset;
                }
            }

            $hrc['tablestack'][$tidx] = $table;
        }
    }

    /**
     * Push a new active HTML link.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function pushHTMLLink(array &$hrc, string $href): void
    {
        $hrc['linkstack'][] = $href;
    }

    /**
     * Pop the current active HTML link.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function popHTMLLink(array &$hrc): void
    {
        if ($hrc['linkstack'] === []) {
            return;
        }

        \array_pop($hrc['linkstack']);
    }

    /**
     * Get the current active HTML link.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getCurrentHTMLLink(array &$hrc): string
    {
        if ($hrc['linkstack'] === []) {
            return '';
        }

        $href = $hrc['linkstack'][\count($hrc['linkstack']) - 1];
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
     * Returns true when the element explicitly defines list indentation via CSS.
     *
     * @param THTMLAttrib $elm
     */
    protected function hasHTMLListIndentOverride(array $elm): bool
    {
        if (empty($elm['style']) || !\is_array($elm['style'])) {
            return false;
        }

        foreach (['padding-left', 'margin-left', 'padding', 'margin'] as $prop) {
            if (!empty($elm['style'][$prop])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the CSS-driven list indentation value for one element.
     *
     * @param THTMLAttrib $elm
     */
    protected function getHTMLListIndentFromElement(array $elm): float
    {
        $padding = (isset($elm['padding']['L']) && \is_numeric($elm['padding']['L']))
            ? (float) $elm['padding']['L']
            : 0.0;
        $margin = (isset($elm['margin']['L']) && \is_numeric($elm['margin']['L']))
            ? (float) $elm['margin']['L']
            : 0.0;

        return \max(0.0, $padding + $margin);
    }

    /**
     * Returns the explicit CSS list indent override for a DOM node.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getHTMLListIndentOverrideByKey(array &$hrc, int $key): float
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return 0.0;
        }

        $elm = &$hrc['dom'][$key];
        if (!$this->hasHTMLListIndentOverride($elm)) {
            return 0.0;
        }

        return $this->getHTMLListIndentFromElement($elm);
    }

    /**
     * Returns the current list indentation width.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getCurrentHTMLListIndentWidth(array &$hrc): float
    {
        $depth = $this->getHTMLListDepth($hrc);
        if ($depth < 1) {
            return $this->getHTMLListIndentWidth();
        }

        $idx = $depth - 1;
        if (isset($hrc['liststack'][$idx]['indent']) && \is_numeric($hrc['liststack'][$idx]['indent'])) {
            $indent = (float) $hrc['liststack'][$idx]['indent'];
            if ($indent > 0) {
                return $indent;
            }
        }

        return $this->getHTMLListIndentWidth();
    }

    /**
     * Resolve a CSS url(...) list-style-image value to a list marker type.
     *
     * @param string $listImage CSS list-style-image value.
     */
    protected function getHTMLListImageMarkerType(string $listImage): string
    {
        if ($listImage === '') {
            return '';
        }

        if (!\preg_match('/^url\((.*)\)$/i', \trim($listImage), $match)) {
            return '';
        }

        $source = \trim($match[1]);
        $source = \trim($source, " \t\n\r\0\x0B\"'");
        if ($source === '') {
            return '';
        }

        $imgtype = '';
        if (\preg_match('/^data:image\/([^;,]+)(;base64)?,(.*)$/i', $source, $dataMatch)) {
            $imgtype = \strtolower((string) $dataMatch[1]);
            $isBase64 = !empty($dataMatch[2]);
            $payload = (string) $dataMatch[3];
            if ($isBase64) {
                $decoded = \base64_decode($payload, true);
                if ($decoded === false || $decoded === '') {
                    return '';
                }
                $source = $decoded;
            } else {
                $source = \rawurldecode($payload);
            }
            if ($imgtype === 'svg+xml') {
                $imgtype = 'svg';
            }
        }

        if ($imgtype === '') {
            if (\preg_match('/\.svg([?#].*)?$/i', $source)) {
                $imgtype = 'svg';
            } elseif (\preg_match('/\.([a-z0-9]+)([?#].*)?$/i', $source, $extMatch)) {
                $imgtype = \strtolower((string) $extMatch[1]);
            }
        }

        if ($imgtype === '') {
            $imgtype = 'png';
        }

        if ($imgtype === 'svg' && !\str_starts_with($source, '@')) {
            if (\str_contains($source, '<svg')) {
                $source = '@' . $source;
            }
        }

        $curfont = $this->font->getCurrentFont();
        $fontsize = (float) ($curfont['usize'] ?? 0.0);
        if ($fontsize <= 0.0) {
            $fontsize = $this->toUnit(8.0);
        }
        $imgsize = \max(1.0, \round($fontsize * 0.5, 3));

        return 'img|'
            . $imgtype
            . '|'
            . (string) $imgsize
            . '|'
            . (string) $imgsize
            . '|'
            . $source;
    }

    /**
     * Resolve the list marker style for UL/OL elements.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getHTMLListMarkerType(array &$hrc, int $key, bool $ordered): string
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return $ordered ? '#' : $this->ullidot;
        }

        $elm = &$hrc['dom'][$key];
        $default = $ordered ? '#' : $this->ullidot;

        if (!empty($elm['style']['list-style-image']) && \is_string($elm['style']['list-style-image'])) {
            $imagetype = \trim((string) $elm['style']['list-style-image']);
            if ($imagetype !== '' && \strtolower($imagetype) !== 'none') {
                $imgmarker = $this->getHTMLListImageMarkerType($imagetype);
                if ($imgmarker !== '') {
                    return $imgmarker;
                }
            }
        } elseif (!empty($elm['list-style-image']) && \is_string($elm['list-style-image'])) {
            $imagetype = \trim((string) $elm['list-style-image']);
            if ($imagetype !== '' && \strtolower($imagetype) !== 'none') {
                $imgmarker = $this->getHTMLListImageMarkerType($imagetype);
                if ($imgmarker !== '') {
                    return $imgmarker;
                }
            }
        }

        $inheritsListStyle = !empty($elm['style']['list-style'])
            && \is_string($elm['style']['list-style'])
            && (\strtolower(\trim((string) $elm['style']['list-style'])) === 'inherit');
        $inheritsLStyType = !empty($elm['style']['list-style-type'])
            && \is_string($elm['style']['list-style-type'])
            && (\strtolower(\trim((string) $elm['style']['list-style-type'])) === 'inherit');
        if (($inheritsListStyle || $inheritsLStyType) && !empty($hrc['liststack'])) {
            $parentidx = \count($hrc['liststack']) - 1;
            if (isset($hrc['liststack'][$parentidx]['type']) && \is_string($hrc['liststack'][$parentidx]['type'])) {
                $parenttype = $hrc['liststack'][$parentidx]['type'];
                if (!$ordered && ($parenttype === '!')) {
                    $parentDepth = \count($hrc['liststack']);
                    return self::LIST_DEF_ULTYPE[($parentDepth - 1) % 3];
                }
                if ($ordered && ($parenttype === '#')) {
                    return 'decimal';
                }

                return $parenttype;
            }
        }

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
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function pushHTMLList(array &$hrc, int $key, bool $ordered): void
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            $hrc['liststack'][] = [
                'ordered' => $ordered,
                'type' => $ordered ? '#' : $this->ullidot,
                'count' => 0,
                'indent' => 0.0,
            ];
            return;
        }

        $elm = &$hrc['dom'][$key];
        $start = 0;
        if (
            $ordered
            && !empty($elm['attribute']['start'])
            && \is_numeric($elm['attribute']['start'])
        ) {
            $start = ((int) $elm['attribute']['start']) - 1;
        }

        $indent = $this->getHTMLListIndentOverrideByKey($hrc, $key);

        $hrc['liststack'][] = [
            'ordered' => $ordered,
            'type' => $this->getHTMLListMarkerType($hrc, $key, $ordered),
            'count' => $start,
            'indent' => $indent,
        ];
    }

    /**
     * Remove the current list level from the rendering stack.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function popHTMLList(array &$hrc): void
    {
        if (!empty($hrc['liststack'])) {
            \array_pop($hrc['liststack']);
        }
    }

    /**
     * Returns the current list depth.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getHTMLListDepth(array &$hrc): int
    {
        return \count($hrc['liststack']);
    }

    /**
     * Returns the next list marker counter for the current list level.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getHTMLListItemCounter(array &$hrc, int $key): int
    {
        $depth = $this->getHTMLListDepth($hrc);
        if ($depth < 1) {
            return 1;
        }

        $idx = $depth - 1;
        if (!$hrc['liststack'][$idx]['ordered']) {
            return 1;
        }

        ++$hrc['liststack'][$idx]['count'];
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return $hrc['liststack'][$idx]['count'];
        }

        $elm = &$hrc['dom'][$key];
        if (
            !empty($elm['attribute']['value'])
            && \is_numeric($elm['attribute']['value'])
        ) {
            $hrc['liststack'][$idx]['count'] = (int) $elm['attribute']['value'];
        }

        return $hrc['liststack'][$idx]['count'];
    }

    /**
     * Returns the marker type for the current list level.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getCurrentHTMLListMarkerType(array &$hrc): string
    {
        $depth = $this->getHTMLListDepth($hrc);
        if ($depth < 1) {
            return '#';
        }

        return $hrc['liststack'][$depth - 1]['type'];
    }

    /**
     * Filter CSS data to only include supported li::marker properties.
     * Supported properties: color, font-weight, font-style, font-size.
     *
     * @param array<string, mixed> $cssdata CSS data array with property keys.
     *
     * @return array<string, mixed> Filtered CSS data containing only supported marker properties.
     */
    protected function filterHTMLMarkerStyles(array $cssdata): array
    {
        /** @var array<string, mixed> $filtered */
        $filtered = [];
        /** @var array<string> $supportedProperties */
        $supportedProperties = ['color', 'font-weight', 'font-style', 'font-size'];

        foreach ($supportedProperties as $prop) {
            if (isset($cssdata[$prop])) {
                $filtered[$prop] = $cssdata[$prop];
            }
        }

        return $filtered;
    }

    /**
     * Apply marker styles by pushing them to the style stack.
     *
     * @param array<string, mixed> $markerStyles Marker-specific CSS styles.
     * @param array<string, mixed> $markerState Previous style state captured before applying marker styles.
     *
     * @return string PDF prefix commands for marker style activation.
     */
    protected function getStartMarkerStyle(array $markerStyles, array &$markerState): string
    {
        $markerState['lineColor'] = (string) $this->graph->getLastStyleProperty('lineColor', 'black');
        $markerState['fillColor'] = (string) $this->graph->getLastStyleProperty('fillColor', 'black');
        $markerState['font'] = $this->captureHTMLCallerFontState();

        $out = '';

        if (isset($markerStyles['color']) && \is_string($markerStyles['color'])) {
            $color = \trim($markerStyles['color']);
            if ($color !== '') {
                $out .= $this->color->getPdfColor($color);
            }
        }

        $fontstate = $markerState['font'];
        if (
            isset($fontstate['family'], $fontstate['style'], $fontstate['size'])
            && \is_string($fontstate['family'])
            && \is_string($fontstate['style'])
            && \is_numeric($fontstate['size'])
        ) {
            $fontstyle = (string) $fontstate['style'];
            if (isset($markerStyles['font-weight']) && \is_string($markerStyles['font-weight'])) {
                $weight = \strtolower(\trim($markerStyles['font-weight']));
                if (\in_array($weight, ['bold', 'bolder', '600', '700', '800', '900'], true)) {
                    if (!\str_contains($fontstyle, 'B')) {
                        $fontstyle .= 'B';
                    }
                } elseif (\in_array($weight, ['normal', '100', '200', '300', '400', '500'], true)) {
                    $fontstyle = \str_replace('B', '', $fontstyle);
                }
            }

            if (isset($markerStyles['font-style']) && \is_string($markerStyles['font-style'])) {
                $style = \strtolower(\trim($markerStyles['font-style']));
                if (\in_array($style, ['italic', 'oblique'], true)) {
                    if (!\str_contains($fontstyle, 'I')) {
                        $fontstyle .= 'I';
                    }
                } elseif ($style === 'normal') {
                    $fontstyle = \str_replace('I', '', $fontstyle);
                }
            }

            $fontsize = (float) $fontstate['size'];
            if (isset($markerStyles['font-size']) && \is_string($markerStyles['font-size'])) {
                $fsize = \trim($markerStyles['font-size']);
                if ($fsize !== '') {
                    $ref = self::REFUNITVAL;
                    $ref['font-size'] = $fontsize;
                    $ref['parent'] = $fontsize;
                    $csssize = $this->getUnitValuePoints($fsize, $ref);
                    if ($csssize > 0.0) {
                        $fontsize = $csssize;
                    }
                }
            }

            $metric = $this->font->insert(
                $this->pon,
                (string) $fontstate['family'],
                $fontstyle,
                $fontsize,
            );
            if (!empty($metric['out']) && \is_string($metric['out'])) {
                $out .= $metric['out'];
            }
        }

        return $out;
    }

    /**
     * Restore marker styles by popping them from the style stack.
     *
     * @param array<string, mixed> $markerState Previous style state captured before marker styles.
     *
     * @return string PDF suffix commands for marker style restore.
     */
    protected function getStopMarkerStyle(array $markerState): string
    {
        $fillColor = 'black';
        if (!empty($markerState['fillColor']) && \is_string($markerState['fillColor'])) {
            $fillColor = $markerState['fillColor'];
        }

        $out = $this->color->getPdfColor($fillColor);
        $fontstate = $markerState['font'] ?? null;
        if (
            \is_array($fontstate)
            && isset($fontstate['family'], $fontstate['style'], $fontstate['size'])
            && \is_string($fontstate['family'])
            && \is_string($fontstate['style'])
            && \is_numeric($fontstate['size'])
        ) {
            $metric = $this->font->insert(
                $this->pon,
                (string) $fontstate['family'],
                (string) $fontstate['style'],
                (float) $fontstate['size'],
            );
            if (!empty($metric['out']) && \is_string($metric['out'])) {
                $out = $metric['out'] . $out;
            }
        }

        return $out;
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
     * Capture the active font state so HTML rendering can restore it afterwards.
     *
     * @return array{family: string, style: string, size: float}
     */
    protected function captureHTMLCallerFontState(): array
    {
        $curfont = $this->font->getCurrentFont();
        $fontkey = (string) ($curfont['key'] ?? '');
        $family = $this->font->getFontFamilyName($fontkey);
        // getFontFamilyName may return the full font key (e.g. "helveticab")
        // rather than the plain base family name ("helvetica"), because the bold
        // variant is itself a valid key in the buffer.  Always strip trailing
        // bold/italic/underline/strikeout/overline suffixes so that
        // restoreHTMLCallerFontState re-inserts (family, style) correctly and
        // does not accumulate extra "B"/"I" characters in the key.
        $family = \preg_replace('/[biudo]+$/i', '', $family) ?? $family;
        if ($family === '') {
            $family = 'helvetica';
        }

        $style = '';
        if (!empty($curfont['style']) && \is_string($curfont['style'])) {
            foreach (['B', 'I'] as $fontstyle) {
                if (\str_contains($curfont['style'], $fontstyle)) {
                    $style .= $fontstyle;
                }
            }
        }

        $size = (float) ($curfont['size'] ?? 10.0);
        return [
            'family' => $family,
            'style' => $style,
            'size' => $size,
        ];
    }

    /**
     * Restore the font state captured before HTML rendering started.
     *
     * @param array{family: string, style: string, size: float} $fontstate Captured font state.
     */
    protected function restoreHTMLCallerFontState(array $fontstate): string
    {
        $font = $this->font->insert(
            $this->pon,
            $fontstate['family'],
            $fontstate['style'],
            $fontstate['size'],
        );

        return (isset($font['out']) && \is_string($font['out'])) ? $font['out'] : '';
    }

    /**
     * Return the metric for the specified HTML node font.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     *
    * @return array<string, mixed>
     */
    protected function getHTMLFontMetric(array &$hrc, int $key): array
    {
        if (!isset($hrc['dom'][$key])) {
            return [];
        }

        $elm = $hrc['dom'][$key];
        $curfont = $this->font->getCurrentFont();
        $fontname = empty($elm['fontname'])
            ? (string) ($hrc['cellctx']['basefont'] ?? $this->getHTMLBaseFontName())
            : (string) $elm['fontname'];

        $stripped = \preg_replace('/[biudo]+$/i', '', $fontname) ?? '';
        if (($stripped !== '') && ($stripped !== $fontname)) {
            $fontname = $stripped;
        }
        $fontsize = (!empty($elm['fontsize']) && \is_numeric($elm['fontsize']))
            ? (float) $elm['fontsize']
            : (float) ($curfont['size'] ?? 0.0);
        $fontstyle = '';
        if (!empty($elm['fontstyle']) && \is_string($elm['fontstyle'])) {
            foreach (['B', 'I'] as $style) {
                if (\str_contains($elm['fontstyle'], $style)) {
                    $fontstyle .= $style;
                }
            }
        }

        $cachekey = $fontname . '|' . $fontstyle . '|' . (string) $fontsize;
        if (isset($hrc['fontcache'][$cachekey])) {
            // Re-insert when cached font differs from the active one.
            // Font key alone is not enough because different font sizes may share the same key.
            $curfont = $this->font->getCurrentFont();
            $cursize = (float) ($curfont['size'] ?? 0.0);
            $curkey = (string) $this->font->getCurrentFontKey();
            $cachefontkey = '';
            if (isset($hrc['fontcache'][$cachekey]['key']) && \is_string($hrc['fontcache'][$cachekey]['key'])) {
                $cachefontkey = $hrc['fontcache'][$cachekey]['key'];
            }
            if (($curkey !== $cachefontkey) || (\abs($cursize - $fontsize) > 0.0001)) {
                $this->font->insert($this->pon, $fontname, $fontstyle, $fontsize);
            }

            return $hrc['fontcache'][$cachekey];
        }

        $metric = $this->font->insert($this->pon, $fontname, $fontstyle, $fontsize);
        $hrc['fontcache'][$cachekey] = $metric;

        return $metric;
    }

    /**
     * Build the font and color prefix for a text fragment.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getHTMLTextPrefix(array &$hrc, int $key): string
    {
        if (!isset($hrc['dom'][$key])) {
            return '';
        }

        $elm = $hrc['dom'][$key];
        $font = $this->getHTMLFontMetric($hrc, $key);
        $color = empty($elm['fgcolor']) ? 'black' : (string) $elm['fgcolor'];
        $fontout = (isset($font['out']) && \is_string($font['out'])) ? $font['out'] : '';

        return $fontout . $this->color->getPdfColor($color);
    }

    /**
     * Return the current HTML text advance for line-based layout.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getHTMLLineAdvance(array &$hrc, int $key): float
    {
        if (!isset($hrc['dom'][$key])) {
            return 0.0;
        }

        $elm = $hrc['dom'][$key];
        $font = $this->getHTMLFontMetric($hrc, $key);
        $ratio = (!empty($elm['line-height']) && \is_numeric($elm['line-height']))
            ? (float) $elm['line-height']
            : 1.0;
        $fontheight = (isset($font['height']) && \is_numeric($font['height'])) ? (float) $font['height'] : 0.0;

        return $this->toUnit($fontheight * $ratio);
    }

    /**
     * Return the additional word spacing for the specified HTML node.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getHTMLWordSpacing(array &$hrc, int $key): float
    {
        if (!isset($hrc['dom'][$key]) || !\is_numeric($hrc['dom'][$key]['word-spacing'])) {
            return 0.0;
        }

        return \max(0.0, (float) $hrc['dom'][$key]['word-spacing']);
    }

    /**
     * Resolve the effective table cellspacing after CSS border-collapse rules.
     * Collapse mode suppresses inter-cell gutters in the current table engine.
     *
     * @param THTMLAttrib $elm Table element.
     */
    protected function getHTMLTableCellSpacingH(array $elm): float
    {
        $cellspacing = (isset($elm['pendingcellspacingh']) && \is_numeric($elm['pendingcellspacingh']))
            ? (float) $elm['pendingcellspacingh'] : 0.0;

        if (($elm['border-collapse'] ?? 'separate') === 'collapse') {
            return 0.0;
        }

        return $cellspacing;
    }

    /**
     * Resolve the effective vertical table spacing after CSS border-collapse rules.
     * Collapse mode suppresses inter-row gutters in the current table engine.
     *
     * @param THTMLAttrib $elm Table element.
     */
    protected function getHTMLTableCellSpacingV(array $elm): float
    {
        $cellspacing = (isset($elm['pendingcellspacingv']) && \is_numeric($elm['pendingcellspacingv']))
            ? (float) $elm['pendingcellspacingv'] : 0.0;

        if (($elm['border-collapse'] ?? 'separate') === 'collapse') {
            return 0.0;
        }

        return $cellspacing;
    }

    /**
     * Extract a background color token from the CSS background shorthand.
     * Unsupported image/position/repeat parts are ignored.
     */
    protected function getHTMLBackgroundShorthandColor(string $background): string
    {
        $background = \trim($background);
        if ($background === '') {
            return '';
        }

        $tokens = \preg_split('/\s+(?![^()]*\))/u', $background);
        if ($tokens === false) {
            return '';
        }

        foreach ($tokens as $token) {
            $token = \trim($token);
            if (($token === '') || \str_contains($token, '/')) {
                continue;
            }

            try {
                $color = $this->getCSSColor($token);
            } catch (\Throwable) {
                $color = '';
            }
            if ($color !== '') {
                return $color;
            }
        }

        return '';
    }

    /**
     * Reset HTML flow cursor after an explicit page break.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function resetHTMLCursorAfterPageBreak(array &$hrc, float &$tpx, float &$tpy, float &$tpw): void
    {
        $region = $this->page->getRegion();
        $regiontop = (float) $region['RY'];
        $tpy = \max($regiontop, (float) $hrc['cellctx']['originy']);
        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
        $hrc['cellctx']['floatrowleftw'] = 0.0;
        $hrc['cellctx']['floatrowrightw'] = 0.0;
        $hrc['cellctx']['floatrowtop'] = 0.0;
        $hrc['cellctx']['floatrowbottom'] = 0.0;
        $hrc['cellctx']['pendingblockmarginb'] = 0.0;
    }

    /**
     * Return the current HTML line advance, including previously rendered inline fragments.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function getCurrentHTMLLineAdvance(array &$hrc, int $key): float
    {
        $lineadvance = 0.0;
        if (!empty($hrc['cellctx']['lineadvance']) && \is_numeric($hrc['cellctx']['lineadvance'])) {
            $lineadvance = (float) $hrc['cellctx']['lineadvance'];
        }

        return \max($lineadvance, $this->getHTMLLineAdvance($hrc, $key));
    }

    /**
     * Track the tallest inline fragment rendered on the current HTML line.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function updateHTMLLineAdvance(array &$hrc, float $lineadvance): void
    {
        if ($lineadvance <= 0.0) {
            return;
        }

        if (
            empty($hrc['cellctx']['lineadvance'])
            || !\is_numeric($hrc['cellctx']['lineadvance'])
            || ($lineadvance > (float) $hrc['cellctx']['lineadvance'])
        ) {
            $hrc['cellctx']['lineadvance'] = $lineadvance;
        }
    }

    /**
     * Normalize plain HTML text before rendering it.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    /**
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getHTMLWhiteSpaceMode(array &$hrc, int $key = -1): string
    {
        if ($hrc['prelevel'] > 0) {
            return 'pre';
        }

        if (
            ($key >= 0)
            && isset($hrc['dom'][$key])
            && isset($hrc['dom'][$key]['white-space'])
            && \is_string($hrc['dom'][$key]['white-space'])
        ) {
            $mode = \strtolower(\trim($hrc['dom'][$key]['white-space']));
            if (\in_array($mode, ['normal', 'nowrap', 'pre', 'pre-wrap', 'pre-line'], true)) {
                return $mode;
            }
        }

        return 'normal';
    }

    /**
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function isHTMLPreLikeWhiteSpaceMode(array &$hrc, int $key = -1): bool
    {
        $mode = $this->getHTMLWhiteSpaceMode($hrc, $key);
        return (($mode === 'pre') || ($mode === 'pre-wrap'));
    }

    /**
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function normalizeHTMLText(array &$hrc, string $text, int $key = -1): string
    {
        $mode = $this->getHTMLWhiteSpaceMode($hrc, $key);
        if (($mode === 'pre') || ($mode === 'pre-wrap')) {
            return \str_replace("\u{00A0}", ' ', $text);
        }

        if ($mode === 'pre-line') {
            $text = \str_replace("\u{00A0}", ' ', $text);
            $text = \str_replace(["\r\n", "\r", "\f"], "\n", $text);
            $lines = \explode("\n", $text);
            $count = \count($lines);
            for ($idx = 0; $idx < $count; ++$idx) {
                $line = \preg_replace('/[^\S\n]+/u', ' ', $lines[$idx]) ?? '';
                $lines[$idx] = \trim($line, ' ');
            }

            return \implode("\n", $lines);
        }

        $text = \preg_replace('/\s+/u', ' ', $text) ?? '';
        if ($text === '') {
            return '';
        }

        if (\trim($text) === '') {
            return ' ';
        }

        if ($mode === 'nowrap') {
            // Keep collapsed spacing but remove wrap opportunities at spaces.
            return \str_replace(' ', "\u{00A0}", $text);
        }

        return $text;
    }

    /**
     * Apply word-break CSS rules to text by inserting soft-break opportunities.
     * Allows long unbreakable words to wrap when overflow-wrap or word-break CSS is set.
     *
     * @param THTMLRenderContext $hrc HTML render context
     * @param string $text Normalized text to process
     * @param int $key Element key in DOM
     * @return string Text with soft-break opportunities inserted where needed
     */
    protected function applyHTMLWordBreakRules(array &$hrc, string $text, int $key = -1): string
    {
        // Check if word-breaking is explicitly allowed by CSS properties
        $allowWordBreak = false;
        $checkKey = $key;

        // For text nodes and elements without direct CSS, check inherited properties from ancestors
        if (($checkKey >= 0) && isset($hrc['dom'][$checkKey])) {
            $elm = $hrc['dom'][$checkKey];

            // First check this element
            if (isset($elm['overflow-wrap']) && \is_string($elm['overflow-wrap'])) {
                $overflowWrap = \strtolower($elm['overflow-wrap']);
                if ($overflowWrap === 'break-word') {
                    $allowWordBreak = true;
                }
            }

            if (!$allowWordBreak && isset($elm['word-break']) && \is_string($elm['word-break'])) {
                $wordBreak = \strtolower($elm['word-break']);
                if (($wordBreak === 'break-all') || ($wordBreak === 'break-word')) {
                    $allowWordBreak = true;
                }
            }

            // If not found on this element, check ancestor elements (inheritance)
            if (!$allowWordBreak && isset($elm['parent']) && \is_int($elm['parent'])) {
                $parentKey = (int) $elm['parent'];

                while ($parentKey >= 0 && isset($hrc['dom'][$parentKey])) {
                    $parent = $hrc['dom'][$parentKey];

                    if (isset($parent['overflow-wrap']) && \is_string($parent['overflow-wrap'])) {
                        $overflowWrap = \strtolower($parent['overflow-wrap']);
                        if ($overflowWrap === 'break-word') {
                            $allowWordBreak = true;
                            break;
                        }
                    }

                    if (isset($parent['word-break']) && \is_string($parent['word-break'])) {
                        $wordBreak = \strtolower($parent['word-break']);
                        if (($wordBreak === 'break-all') || ($wordBreak === 'break-word')) {
                            $allowWordBreak = true;
                            break;
                        }
                    }

                    // Stop at root (key 0) or if no valid parent
                    if ($parentKey === 0) {
                        break;
                    }

                    $parentKey = isset($parent['parent']) && \is_int($parent['parent']) ? (int) $parent['parent'] : -1;
                }
            }
        }

        // If CSS doesn't enable word breaking, don't modify text
        if (!$allowWordBreak) {
            return $text;
        }

        // Even in pre-like modes, if word-breaking is explicitly enabled, apply it
        // This allows breaking long unbreakable identifiers in code/pre elements

        // Insert soft-break opportunities (zero-width space \u{200B}) into long words
        // This allows the text renderer to break long words at character boundaries when needed
        $result = '';
        $words = \explode(' ', $text);

        foreach ($words as $word) {
            if ($result !== '') {
                $result .= ' ';
            }

            // For long words, add zero-width spaces at regular intervals to allow breaking.
            // Since $word is a single word split by spaces, no spaces exist in it.
            if (\mb_strlen($word) > self::MIN_WORD_LENGTH_FOR_BREAKS) {
                $chars = \mb_str_split($word);
                $chunk = '';
                $count = 0;

                foreach ($chars as $char) {
                    $chunk .= $char;
                    ++$count;

                    // Insert soft-break opportunity at configured intervals
                    if ($count >= self::BREAK_INTERVAL) {
                        $chunk .= "\u{200B}"; // Zero-width space for line-breaking
                        $count = 0;
                    }
                }

                $result .= $chunk;
            } else {
                $result .= $word;
            }
        }

        return $result;
    }

    /**
     * Return a typed default text-dimension structure for HTML text helpers.
     *
     * @return TTextDims
     */
    protected function getHTMLDefaultTextDims(): array
    {
        return self::DIM_DEFAULT;
    }

    /**
     * Prepare HTML text preserving explicit soft hyphen characters.
     *
     * @param string $txt Input text to normalize and convert.
     * @param array<int, int> $ordarr Output array of UTF-8 code points.
     * @param TTextDims $dim Output measured text dimensions.
     * @param string $forcedir If 'R' forces RTL, if 'L' forces LTR.
     */
    protected function prepareHTMLText(
        string &$txt,
        array &$ordarr,
        array &$dim,
        string $forcedir = '',
    ): void {
        $prevSoftHyphen = $this->htmlRenderSoftHyphen;
        $this->htmlRenderSoftHyphen = true;
        try {
            $this->prepareText($txt, $ordarr, $dim, $forcedir);
        } finally {
            $this->htmlRenderSoftHyphen = $prevSoftHyphen;
        }
    }

    /**
     * HTML rendering can opt in to preserve SOFT HYPHEN for discretionary wraps.
     */
    protected function cleanupText(string $txt): string
    {
        if (!$this->htmlRenderSoftHyphen) {
            return parent::cleanupText($txt);
        }

        $txt = \str_replace("\r", ' ', $txt);
        $txt = \str_replace($this->uniconv->chr(self::ORD_NO_BREAK_SPACE), ' ', $txt);
        return $txt;
    }

    /**
     * Convert trailing SHY to visible hyphen only when HTML line wrapping breaks on SHY.
     *
     * @param array<int, int> $ordarr The array of Unicode code points.
     *
     * @return array<int, int> The filtered array.
     */
    protected function removeOrdArrSoftHyphens(array $ordarr): array
    {
        if (!$this->htmlRenderSoftHyphen) {
            return parent::removeOrdArrSoftHyphens($ordarr);
        }

        $keeplast = ((\count($ordarr) > 0) && ($ordarr[(\count($ordarr) - 1)] == self::ORD_SOFT_HYPHEN));
        $retarr = \array_filter(
            $ordarr,
            fn($ord) => (
                ($ord != self::ORD_SOFT_HYPHEN)
                && ($ord != self::ORD_ZERO_WIDTH_SPACE)
            )
        );
        if ($keeplast) {
            $retarr[] = self::ORD_HYPHEN;
        }
        return $retarr;
    }

    /**
     * Reset the cursor to the current HTML block origin.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function resetHTMLLineCursor(array &$hrc, float &$tpx, float &$tpw): void
    {
        $tpx = $hrc['cellctx']['originx'];
        $tpw = $hrc['cellctx']['maxwidth'];
        $hrc['cellctx']['lineoriginx'] = $hrc['cellctx']['originx'];
        $hrc['cellctx']['lineadvance'] = 0.0;
        $hrc['cellctx']['linebottom'] = 0.0;
        $hrc['cellctx']['lineascent'] = 0.0;
        $hrc['cellctx']['linewordspacing'] = 0.0;
        $hrc['cellctx']['linewrapped'] = false;
    }

    /**
     * Return true when there is an active float row intersecting the current cursor.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function hasActiveHTMLFloatRow(array &$hrc, float $tpy): bool
    {
        $bottom = isset($hrc['cellctx']['floatrowbottom']) && \is_numeric($hrc['cellctx']['floatrowbottom'])
            ? (float) $hrc['cellctx']['floatrowbottom']
            : 0.0;

        // Treat near-equal coordinates as active so the next block is placed
        // safely below float borders/strokes instead of touching/overlapping.
        return ($bottom > ($tpy - self::WIDTH_TOLERANCE));
    }

    /**
     * Move the cursor below the active float row and clear float occupancy state.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function flushHTMLFloatRow(array &$hrc, float &$tpx, float &$tpy, float &$tpw): void
    {
        $bottom = isset($hrc['cellctx']['floatrowbottom']) && \is_numeric($hrc['cellctx']['floatrowbottom'])
            ? (float) $hrc['cellctx']['floatrowbottom']
            : 0.0;
        if ($bottom > $tpy) {
            $tpy = $bottom;
        }

        $hrc['cellctx']['floatrowleftw'] = 0.0;
        $hrc['cellctx']['floatrowrightw'] = 0.0;
        $hrc['cellctx']['floatrowtop'] = 0.0;
        $hrc['cellctx']['floatrowbottom'] = 0.0;
        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
    }

    /**
     * Measure the width of the remaining inline run on the current HTML line.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function measureHTMLInlineRunWidth(array &$hrc, int $startkey): float
    {
        /** @var array<int, THTMLAttrib> $dom */
        $dom = &$hrc['dom'];
        $numel = \count($dom);
        $runwidth = 0.0;

        for ($key = $startkey; $key < $numel; ++$key) {
            $node = $dom[$key];

            if (!empty($node['tag'])) {
                if (($node['value'] === 'img') && !empty($node['opening'])) {
                    $lineheight = $this->getHTMLLineAdvance($hrc, $key);
                    $imgwidth = (!empty($node['width']) && \is_numeric($node['width']))
                        ? (float) $node['width']
                        : $lineheight;
                    if ($imgwidth > 0.0) {
                        $runwidth += $imgwidth;
                    }

                    continue;
                }

                if (($key > $startkey) && (!empty($node['block']) || ($node['value'] === 'br'))) {
                    break;
                }

                continue;
            }

            $text = $this->normalizeHTMLText($hrc, (string) $node['value'], $key);
            if ($text === '') {
                continue;
            }

            $this->getHTMLFontMetric($hrc, $key);
            $runwidth += $this->getStringWidth($text);
        }

        return $runwidth;
    }

    /**
     * Measure the width of the inline chunk that fits on the next visual line.
     *
     * Unlike measureHTMLInlineRunWidth(), this method stops at the first wrap
     * point and returns only the first-line width, even when the remaining run
     * spans multiple wrapped lines.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function measureHTMLInlineLineWidth(array &$hrc, int $startkey, float $maxwidth): float
    {
        return $this->measureHTMLInlineLineMetrics($hrc, $startkey, $maxwidth)['width'];
    }

    /**
     * Measure width and spacing metadata for the next visual inline line.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     *
     * @return array{width: float, spaces: int, wrapped: bool}
     */
    protected function measureHTMLInlineLineMetrics(
        array &$hrc,
        int $startkey,
        float $maxwidth,
        float $wordspacing = 0.0,
    ): array {
        if ($maxwidth <= 0.0) {
            return ['width' => 0.0, 'spaces' => 0, 'wrapped' => false];
        }

        /** @var array<int, THTMLAttrib> $dom */
        $dom = &$hrc['dom'];
        $numel = \count($dom);
        $linewidth = 0.0;
        $contentwidth = 0.0;
        $spaces = 0;
        $wrapped = false;
        $trailspaces = 0;
        $trailspacewidth = 0.0;
        $lastspaceonly = false;
        $wrapspaces = 0;

        for ($key = $startkey; $key < $numel; ++$key) {
            $node = $dom[$key];

            if (!empty($node['tag'])) {
                if (($node['value'] === 'img') && !empty($node['opening'])) {
                    $lineheight = $this->getHTMLLineAdvance($hrc, $key);
                    $imgwidth = (!empty($node['width']) && \is_numeric($node['width']))
                        ? (float) $node['width']
                        : $lineheight;

                    if ($imgwidth <= 0.0) {
                        continue;
                    }

                    if (($linewidth > 0.0) && (($linewidth + $imgwidth) > ($maxwidth + self::WIDTH_TOLERANCE))) {
                        $wrapped = true;
                        break;
                    }

                    $linewidth += $imgwidth;
                    $contentwidth = $linewidth;
                    continue;
                }

                if (($key > $startkey) && (!empty($node['block']) || ($node['value'] === 'br'))) {
                    break;
                }

                continue;
            }

            $text = $this->normalizeHTMLText($hrc, (string) $node['value'], $key);
            if ($text === '') {
                continue;
            }

            if (($key === $startkey) && !$this->isHTMLPreLikeWhiteSpaceMode($hrc, $key)) {
                $text = \ltrim($text);
                if ($text === '') {
                    continue;
                }
            }

            $spaceonly = (\trim($text) === '');
            $remaining = \max(0.0, $maxwidth - $linewidth - ($spaces * $wordspacing));
            if ($remaining <= 0.0) {
                $wrapped = true;
                break;
            }

            // Switch to the correct font for this node BEFORE measuring, so that
            // getStringWidth() and canHTMLTextKeepVisibleChunkOnCurrentLine() use
            // the fragment's own metrics rather than the previous node's font.
            $this->getHTMLFontMetric($hrc, $key);

            if (($linewidth > 0.0) && !$spaceonly) {
                $forcedir = ($node['dir'] === 'rtl') ? 'R' : '';
                $fragmentwidth = $this->getStringWidth($text);
                $keepchunkonline = $this->canHTMLTextKeepVisibleChunkOnCurrentLine(
                    $text,
                    $forcedir,
                    $remaining,
                );

                if (
                    ($fragmentwidth > ($remaining + self::WIDTH_TOLERANCE))
                    && (
                        !$this->hasHTMLTextBreakOpportunity($hrc, $key, $text)
                        || (
                            ($fragmentwidth <= ($maxwidth + self::WIDTH_TOLERANCE))
                            && !$keepchunkonline
                        )
                    )
                ) {
                    $wrapped = true;
                    break;
                }
            }
            $ordarr = [];
            $dim = $this->getHTMLDefaultTextDims();
            $forcedir = ($node['dir'] === 'rtl') ? 'R' : '';
            $this->prepareHTMLText($text, $ordarr, $dim, $forcedir);
            $lines = $this->splitLines($ordarr, $dim, $this->toPoints($remaining));
            if ($lines === []) {
                continue;
            }

            $firstline = $lines[0];
            if ((int) $firstline['chars'] <= 0) {
                $wrapped = true;
                break;
            }

            $chunkordarr = \array_slice($ordarr, 0, (int) $firstline['chars']);
            $chunktext = \implode('', $this->uniconv->ordArrToChrArr($chunkordarr));
            if (($linewidth > 0.0) && !$spaceonly && (\trim($chunktext) === '')) {
                $wrapped = true;
                break;
            }

            $chunkwidth = $this->toUnit((float) $firstline['totwidth']);
            $nextspaces = $spaces + (int) ($firstline['spaces'] ?? 0);
            $lineOverflows = ($linewidth > 0.0)
                && (($linewidth + $chunkwidth + ($nextspaces * $wordspacing)) > $maxwidth + self::WIDTH_TOLERANCE);
            if ($lineOverflows) {
                $wrapped = true;
                break;
            }

            $linewidth += $chunkwidth;
            $spaces += (int) ($firstline['spaces'] ?? 0);
            if (!$spaceonly) {
                $contentwidth = $linewidth;
            }

            $lastspaceonly = $spaceonly;
            $trailmatch = [];
            if (\preg_match('/ +$/u', $chunktext, $trailmatch) === 1) {
                $trailspaces = \strlen($trailmatch[0]);
                $trailspacewidth = ($trailspaces > 0)
                    ? $this->getStringWidth(\str_repeat(' ', $trailspaces))
                    : 0.0;
            } else {
                $trailspaces = 0;
                $trailspacewidth = 0.0;
            }

            if ((int) $firstline['chars'] < (int) $dim['chars']) {
                if ($spaceonly) {
                    $wrapspaces = (int) ($firstline['spaces'] ?? 0);
                }
                $wrapped = true;
                break;
            }
        }

        if (($wrapspaces > 0) && $lastspaceonly) {
            $spaces = \max(0, $spaces - $wrapspaces);
            if ($contentwidth <= 0.0) {
                $linewidth = \max(0.0, $linewidth - $trailspacewidth);
            }
        }

        if (($trailspaces > 0) && !$lastspaceonly) {
            $spaces = \max(0, $spaces - $trailspaces);
            if ($contentwidth > 0.0) {
                $contentwidth = \max(0.0, $contentwidth - $trailspacewidth);
            } else {
                $linewidth = \max(0.0, $linewidth - $trailspacewidth);
            }
        }

        return [
            'width' => ($contentwidth > 0.0) ? $contentwidth : $linewidth,
            'spaces' => $spaces,
            'wrapped' => $wrapped,
        ];
    }

    /**
     * Count the number of breakable spaces rendered on the first line of an inline fragment.
     */
    protected function getHTMLTextFirstLineSpaces(string $text, string $forcedir, float $maxwidth): int
    {
        if (($text === '') || ($maxwidth <= 0.0)) {
            return 0;
        }

        $ordarr = [];
        $dim = $this->getHTMLDefaultTextDims();
        $this->prepareHTMLText($text, $ordarr, $dim, $forcedir);
        if ($ordarr === [] || ((int) $dim['spaces'] <= 0)) {
            return 0;
        }

        $lines = $this->splitLines($ordarr, $dim, $this->toPoints($maxwidth));
        if ($lines === []) {
            return 0;
        }

        return (int) ($lines[0]['spaces'] ?? 0);
    }

    /**
     * Measure the maximum ascent among inline text fragments in the current run.
     *
     * The run stops at block boundaries and explicit BR tags.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function measureHTMLInlineRunMaxAscent(array &$hrc, int $startkey): float
    {
        /** @var array<int, THTMLAttrib> $dom */
        $dom = &$hrc['dom'];
        $numel = \count($dom);
        $maxascent = 0.0;

        for ($key = $startkey; $key < $numel; ++$key) {
            $node = $dom[$key];

            if (!empty($node['tag'])) {
                if (($node['value'] === 'img') && !empty($node['opening'])) {
                    $lineheight = $this->getHTMLLineAdvance($hrc, $key);
                    $imgheight = (!empty($node['height']) && \is_numeric($node['height']))
                        ? (float) $node['height']
                        : $lineheight;
                    if ($imgheight <= 0.0) {
                        continue;
                    }

                    $attr = $node['attribute'] ?? [];
                    $valign = (isset($attr['align']) && \is_string($attr['align']))
                        ? \strtolower(\trim($attr['align']))
                        : 'bottom';
                    $ascent = match ($valign) {
                        'top' => 0.0,
                        'middle' => $imgheight / 2.0,
                        default => $imgheight,
                    };
                    if ($ascent > $maxascent) {
                        $maxascent = $ascent;
                    }

                    continue;
                }

                if (($key > $startkey) && (!empty($node['block']) || ($node['value'] === 'br'))) {
                    break;
                }

                continue;
            }

            $text = $this->normalizeHTMLText($hrc, (string) $node['value'], $key);
            if ($text === '') {
                continue;
            }

            $font = $this->getHTMLFontMetric($hrc, $key);
            $ascent = (isset($font['ascent']) && \is_numeric($font['ascent']))
                ? $this->toUnit((float) $font['ascent'])
                : 0.0;
            if ($ascent > $maxascent) {
                $maxascent = $ascent;
            }
        }

        return $maxascent;
    }

    /**
     * Check whether the given inline node starts a new renderable run on the current line.
     *
     * This lets inline alignment logic work even when the line cursor is offset by
     * container padding/margins (for example, table cell paddings).
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function isHTMLInlineRunFirstRenderableNode(array &$hrc, int $key): bool
    {
        if ($key <= 0) {
            return true;
        }

        /** @var array<int, THTMLAttrib> $dom */
        $dom = &$hrc['dom'];

        for ($idx = $key - 1; $idx >= 0; --$idx) {
            $node = $dom[$idx] ?? null;
            if (!\is_array($node)) {
                continue;
            }

            if (!empty($node['tag'])) {
                $isOpening = !empty($node['opening']);
                $tagname = (isset($node['value']) && \is_string($node['value']))
                    ? $node['value']
                    : '';
                if ($isOpening && (($tagname === 'br') || !empty($node['block']))) {
                    return true;
                }

                if ($isOpening && ($tagname === 'img')) {
                    return false;
                }

                continue;
            }

            $text = $this->normalizeHTMLText($hrc, (string) ($node['value'] ?? ''), $idx);
            if ($text === '') {
                continue;
            }

            if ($this->strTrim($text) === '') {
                continue;
            }

            return false;
        }

        return true;
    }

    /**
     * Move the HTML cursor to the next line.
     *
     * @param THTMLRenderContext $hrc HTML render context
     * @param int    $key DOM array key.
     */
    protected function moveHTMLToNextLine(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float $extra = 0,
    ): void {
        $lineadvance = $this->getCurrentHTMLLineAdvance($hrc, $key) + $extra;
        $linebottom = (!empty($hrc['cellctx']['linebottom']) && \is_numeric($hrc['cellctx']['linebottom']))
            ? (float) $hrc['cellctx']['linebottom']
            : 0.0;
        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
        $tpy = \max($tpy + $lineadvance, $linebottom + $extra);
    }

    /**
     * Return true when a BR follows plain text that already wrapped to a new line.
     *
     * In that case the cursor is already at line start and advancing again would
     * introduce an unintended blank line.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function shouldSkipHTMLBrAdvance(array &$hrc, int $key, float $tpx): bool
    {
        unset($key);

        if (empty($hrc['cellctx']['linewrapped'])) {
            return false;
        }

        $originx = (float) ($hrc['cellctx']['originx'] ?? 0.0);
        return ($tpx <= ($originx + self::WIDTH_TOLERANCE));
    }

    /**
     * Return true when a text fragment contains break opportunities.
     */
    /**
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function hasHTMLTextBreakOpportunity(array &$hrc, int $key, string $text): bool
    {
        if ($this->getHTMLWhiteSpaceMode($hrc, $key) === 'nowrap') {
            return false;
        }

        if ((bool) \preg_match('/[\x{00AD}\x{200B}]/u', $text)) {
            return true;
        }

        $trimmed = \trim($text);
        if ($trimmed === '') {
            return false;
        }

        return (bool) \preg_match('/\s/u', $trimmed);
    }

    /**
     * Return true when a breakable fragment can keep a visible leading chunk on the current line.
     *
     * This is intentionally narrower than hasHTMLTextBreakOpportunity(): a fragment such as
     * " underline ..." may keep flowing after the previous text, while a fragment starting with
     * visible content should wrap from the line origin when it cannot fully fit in the remaining width.
     */
    protected function canHTMLTextKeepVisibleChunkOnCurrentLine(
        string $text,
        string $forcedir,
        float $remainingWidth,
    ): bool {
        if ($remainingWidth <= 0.0) {
            return false;
        }

        if (!(bool) \preg_match('/^\s/u', $text)) {
            // The text begins with non-whitespace content (typically punctuation
            // such as ")" or "." that is glued to the previous inline fragment
            // on the same line). Allow it to remain on the current line if its
            // leading non-space chunk fits within the remaining width, so that
            // a closing parenthesis after an inline element does not get force-
            // wrapped to a fresh line.
            $leadMatches = [];
            if (\preg_match('/^\S+/u', $text, $leadMatches) === 1) {
                $lead = $leadMatches[0];
                if ($this->getStringWidth($lead) <= ($remainingWidth + self::WIDTH_TOLERANCE)) {
                    return true;
                }
            }

            return false;
        }

        $ordarr = [];
        $dim = $this->getHTMLDefaultTextDims();
        $this->prepareHTMLText($text, $ordarr, $dim, $forcedir);
        // Give splitLines the same tolerance used by wrap guards so boundary fits
        // (for example: one more word after an italic fragment) are not rejected.
        $lines = $this->splitLines(
            $ordarr,
            $dim,
            $this->toPoints($remainingWidth + self::WIDTH_TOLERANCE),
        );
        if ($lines === []) {
            return false;
        }

        $firstline = $lines[0];
        if ((int) $firstline['chars'] <= 0) {
            return false;
        }

        $chunk = \mb_substr($text, 0, (int) $firstline['chars']);
        return (\trim($chunk) !== '');
    }

    /**
     * Open a simple block-level HTML element.
     *
     * @param THTMLRenderContext $hrc HTML render context
     * @param int    $key DOM array key.
     */
    protected function openHTMLBlock(array &$hrc, int $key, float &$tpx, float &$tpy, float &$tpw): string
    {
        $elm = &$hrc['dom'][$key];
        $hasFloatAncestor = false;
        $scanParentKey = (
            isset($elm['parent'])
            && \is_int($elm['parent'])
        ) ? $elm['parent'] : -1;
        $scanGuard = 0;
        while (($scanParentKey >= 0) && isset($hrc['dom'][$scanParentKey]) && ($scanGuard < 256)) {
            $parentFloat = (
                !empty($hrc['dom'][$scanParentKey]['float'])
                && \is_string($hrc['dom'][$scanParentKey]['float'])
            )
                ? \strtolower(\trim((string) $hrc['dom'][$scanParentKey]['float']))
                : 'none';
            if (\in_array($parentFloat, ['left', 'right'], true)) {
                $hasFloatAncestor = true;
                break;
            }

            $nextParentKey = (
                isset($hrc['dom'][$scanParentKey]['parent'])
                && \is_int($hrc['dom'][$scanParentKey]['parent'])
            )
                ? $hrc['dom'][$scanParentKey]['parent']
                : -1;
            if ($nextParentKey === $scanParentKey) {
                break;
            }

            $scanParentKey = $nextParentKey;
            ++$scanGuard;
        }

        $float = (!empty($elm['float']) && \is_string($elm['float']))
            ? \strtolower(\trim((string) $elm['float']))
            : 'none';
        $clear = (!empty($elm['clear']) && \is_string($elm['clear']))
            ? \strtolower(\trim((string) $elm['clear']))
            : 'none';
        if (\in_array($clear, ['left', 'right', 'both'], true) && $this->hasActiveHTMLFloatRow($hrc, $tpy)) {
            $this->flushHTMLFloatRow($hrc, $tpx, $tpy, $tpw);
        }

        if (($float === 'none') && !$hasFloatAncestor && $this->hasActiveHTMLFloatRow($hrc, $tpy)) {
            // Keep conservative flow deterministic: normal blocks start below active float rows.
            $this->flushHTMLFloatRow($hrc, $tpx, $tpy, $tpw);
        }

        if (
            \in_array($clear, ['left', 'right', 'both'], true)
            && ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE))
        ) {
            // Conservative clear policy: force a new line when the cursor is mid-line.
            $this->moveHTMLToNextLine($hrc, $key, $tpx, $tpy, $tpw);
        }

        $lineadvancectx = isset($hrc['cellctx']['lineadvance']) && \is_numeric($hrc['cellctx']['lineadvance'])
            ? (float) $hrc['cellctx']['lineadvance']
            : 0.0;
        $hasinlinecontent = (
            ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE))
            && ($lineadvancectx > self::WIDTH_TOLERANCE)
        );
        $isFloatInActiveRow =
            \in_array($float, ['left', 'right'], true)
            && $this->hasActiveHTMLFloatRow($hrc, $tpy);
        $lineadvance = $hasinlinecontent ? $this->getCurrentHTMLLineAdvance($hrc, $key) : 0.0;
        $marginTop = (float) $elm['margin']['T'];
        $collapsed = 0.0;
        if (!$hasinlinecontent && ($tpy > $hrc['cellctx']['originy'])) {
            $pendingBottom = isset($hrc['cellctx']['pendingblockmarginb'])
                ? (float) $hrc['cellctx']['pendingblockmarginb']
                : 0.0;
            if ($pendingBottom > 0.0 && $marginTop > 0.0) {
                // Deterministic PDF policy: adjacent positive vertical margins collapse to max().
                $collapsed = \round(\min($pendingBottom, $marginTop), 6);
            }
        }

        if (!$isFloatInActiveRow && ($hasinlinecontent || ($tpy > $hrc['cellctx']['originy']))) {
            $tpy += $lineadvance + $marginTop + $this->getHTMLTagVSpace($hrc, $key, 0) - $collapsed;
        }
        $hrc['cellctx']['pendingblockmarginb'] = 0.0;

        $marginLeft = $this->getHTMLResolvedMarginSide($elm, $hrc['cellctx']['maxwidth'], 'L');
        $marginRight = $this->getHTMLResolvedMarginSide($elm, $hrc['cellctx']['maxwidth'], 'R');
        $paddingLeft = (float) $elm['padding']['L'];
        $paddingRight = (float) $elm['padding']['R'];

        $baseBlockX = $hrc['cellctx']['originx'] + $marginLeft;
        $baseBlockWidth = $hrc['cellctx']['maxwidth'] > 0
            ? \max(0.0, $hrc['cellctx']['maxwidth'] - $marginLeft - $marginRight)
            : 0.0;
        $baseInnerWidth = \max(0.0, $baseBlockWidth - $paddingLeft - $paddingRight);
        $requestedInnerWidth = $this->getHTMLResolvedExplicitWidth($elm, $baseInnerWidth);
        $explicitInnerWidth = 0.0;
        if (
            ($float === 'none')
            && ($requestedInnerWidth > 0.0)
            && ($baseInnerWidth > 0.0)
        ) {
            $explicitInnerWidth = \min($requestedInnerWidth, $baseInnerWidth);
        }
        $floatInnerWidth = 0.0;
        $floatShift = 0.0;
        if (\in_array($float, ['left', 'right'], true)) {
            if (!$this->hasActiveHTMLFloatRow($hrc, $tpy)) {
                $hrc['cellctx']['floatrowleftw'] = 0.0;
                $hrc['cellctx']['floatrowrightw'] = 0.0;
                $hrc['cellctx']['floatrowtop'] = $tpy;
                $hrc['cellctx']['floatrowbottom'] = $tpy;
            }

            $rowLeft = (float) $hrc['cellctx']['floatrowleftw'];
            $rowRight = (float) $hrc['cellctx']['floatrowrightw'];
            $remainingInner = \max(0.0, $baseInnerWidth - $rowLeft - $rowRight);
            $floatOuterExtras = $paddingLeft + $paddingRight + $marginLeft + $marginRight;
            $maxFloatInner = \max(0.0, $remainingInner - $floatOuterExtras);

            if (
                ($requestedInnerWidth > 0.0)
                && ($maxFloatInner > 0.0)
            ) {
                $floatInnerWidth = \min($requestedInnerWidth, $maxFloatInner);
                $floatOuterWidth = $floatInnerWidth + $floatOuterExtras;
                if ($float === 'left') {
                    $floatShift = $rowLeft;
                    $hrc['cellctx']['floatrowleftw'] = $rowLeft + $floatOuterWidth;
                } else {
                    $floatShift = \max(0.0, $baseInnerWidth - $rowRight - $floatOuterWidth);
                    $hrc['cellctx']['floatrowrightw'] = $rowRight + $floatOuterWidth;
                }
            }
        }

        $blockX = $baseBlockX + $floatShift;
        $blockWidth = ($floatInnerWidth > 0.0)
            ? ($floatInnerWidth + (float) $elm['padding']['L'] + (float) $elm['padding']['R'])
            : (($explicitInnerWidth > 0.0)
                ? ($explicitInnerWidth + (float) $elm['padding']['L'] + (float) $elm['padding']['R'])
                : $baseBlockWidth);

        // If this block has its OWN border or background (not merely inherited),
        // start buffering content so that fill is painted before content and border after.
        $hasBorder = isset($elm['border']) && \is_array($elm['border']) && $elm['border'] !== [];
        $hasBgcolor = !empty($elm['bgcolor']) && \is_string($elm['bgcolor']);
        // Exclude inherited values: if the value matches the parent, it was inherited.
        $parentkey = isset($elm['parent']) && \is_int($elm['parent']) ? $elm['parent'] : -1;
        if ($parentkey >= 0 && isset($hrc['dom'][$parentkey])) {
            $pelm = $hrc['dom'][$parentkey];
            if ($hasBorder && isset($pelm['border']) && $pelm['border'] === $elm['border']) {
                $hasBorder = false;
            }
            if ($hasBgcolor && isset($pelm['bgcolor']) && $pelm['bgcolor'] === $elm['bgcolor']) {
                $hasBgcolor = false;
            }
        }
        if ((($elm['value'] ?? '') === 'table') || (($elm['value'] ?? '') === 'tablehead')) {
            // Keep table border rendering in parseHTMLTagCLOSEtable() to avoid
            // double outer-frame painting, but still allow table background to
            // be painted by block buffering before cell content.
            $hasBorder = false;
        }
        if ($hasBorder || $hasBgcolor) {
            $bstyles = $this->getHTMLTableCellBorderStyles($hrc, $key);
            $fillstyle = $this->getHTMLTableCellFillStyle($hrc, $key);
            if ($bstyles !== [] || $fillstyle !== null) {
                $hrc['blockbuf'][] = [
                    'openkey' => $key,
                    'bx' => $blockX,
                    'by' => $tpy,
                    'bw' => $blockWidth,
                    'buffer' => '',
                ];
            }
        }

        $tpy += (float) $elm['padding']['T'];

        $tpx = $hrc['cellctx']['originx']
            + $marginLeft
            + $paddingLeft
            + $floatShift;
        // Start wrapped inline lines at this block's content origin,
        // not at the outer cell origin.
        $hrc['cellctx']['lineoriginx'] = $tpx;
        $tpw = $hrc['cellctx']['maxwidth'];
        $hrc['cellctx']['textindentapplied'] = false;
        if ($tpw > 0) {
            $tpw = \max(
                0.0,
                $tpw
                - $marginLeft
                - $marginRight
                - $paddingLeft
                - $paddingRight
            );
        }

        if ($floatInnerWidth > 0.0) {
            $tpw = \min($tpw, $floatInnerWidth);
        } elseif ($explicitInnerWidth > 0.0) {
            $tpw = \min($tpw, $explicitInnerWidth);
        }

        // Scope line-flow metrics to explicit-width block content boxes only,
        // so wrapped inline text honors local width without affecting generic
        // list/float flow behavior.
        if (\in_array($float, ['left', 'right'], true)) {
            $elm['ctxoriginx'] = (float) $hrc['cellctx']['originx'];
            $elm['ctxmaxwidth'] = (float) $hrc['cellctx']['maxwidth'];
            $elm['ctxregionoffset'] = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);
            $hrc['cellctx']['originx'] = $tpx;
            $hrc['cellctx']['maxwidth'] = $tpw;
        } elseif (($float === 'none') && ($explicitInnerWidth > 0.0)) {
            $elm['ctxoriginx'] = (float) $hrc['cellctx']['originx'];
            $elm['ctxmaxwidth'] = (float) $hrc['cellctx']['maxwidth'];
            $elm['ctxregionoffset'] = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);
            $hrc['cellctx']['originx'] = $tpx;
            $hrc['cellctx']['maxwidth'] = $tpw;
        } elseif (($float === 'none') && !empty($elm['padding'])) {
            // For non-float, non-explicit-width blocks, adjust context for padding
            // so children render inside the padding box.
            $elm['ctxoriginx'] = (float) $hrc['cellctx']['originx'];
            $elm['ctxmaxwidth'] = (float) $hrc['cellctx']['maxwidth'];
            $elm['ctxregionoffset'] = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);
            $hrc['cellctx']['originx'] = $blockX + (float) $elm['padding']['L'];
            $hrc['cellctx']['maxwidth'] = $baseBlockWidth - (float) $elm['padding']['L'] - (float) $elm['padding']['R'];
        }

        $role = $this->getHTMLStructRole($elm);
        if ($role !== '') {
            if ($this->pdfuaMode !== '') {
                $role = $this->pdfuaClampHeadingRole($role);
            }
            $attr = [];
            if (($this->pdfuaMode === 'pdfua2') && ($role === 'L')) {
                $listNumbering = $this->getPdfUaListNumbering($elm);
                if ($listNumbering !== '') {
                    $attr = ['O' => 'List', 'ListNumbering' => $listNumbering];
                }
            }

            $this->beginStructElem($role, $this->page->getPageId(), null, $attr);
        }

        return '';
    }

    /**
     * Clamp a heading structure role (H1-H6) to prevent skipped levels in PDF/UA mode.
     * Going back to a higher level (e.g. H3 then H1) is always allowed.
                if ($table['collapse'] && $table['hascellborders']) {
                    $bstyles = [];
                }
                if (($bstyles !== []) || ($fillstyle !== null)) {
     * Non-heading roles are returned unchanged.
     */
    protected function pdfuaClampHeadingRole(string $role): string
    {
        if (\preg_match('/^H([1-6])$/', $role, $mtch) !== 1) {
            return $role;
        }
        $requested = (int) $mtch[1];
        $clamped = ($requested > $this->pdfuaHeadingLevel + 1) ? $this->pdfuaHeadingLevel + 1 : $requested;
        $this->pdfuaHeadingLevel = $clamped;
        return 'H' . $clamped;
    }

    /**
     * Map an HTML element to the corresponding PDF structure role, or return '' for non-semantic elements.
     *
     * @param array<string, mixed> $elm DOM element.
     */
    protected function getHTMLStructRole(array $elm): string
    {
        $tag = isset($elm['value']) && \is_string($elm['value']) ? $elm['value'] : '';
        return match ($tag) {
            'p'          => 'P',
            'h1'         => 'H1',
            'h2'         => 'H2',
            'h3'         => 'H3',
            'h4'         => 'H4',
            'h5'         => 'H5',
            'h6'         => 'H6',
            'ul', 'ol'   => 'L',
            'li'         => 'LI',
            'table'      => 'Table',
            'caption',
            'figcaption' => 'Caption',
            'figure'     => 'Figure',
            'blockquote' => 'BlockQuote',
            'pre'        => 'Code',
            default      => ''
        };
    }

    /**
     * Return the PDF/UA ListNumbering value for a UL/OL element.
     *
     * @param array<string, mixed> $elm DOM element.
     */
    protected function getPdfUaListNumbering(array $elm): string
    {
        $tag = isset($elm['value']) && \is_string($elm['value']) ? \strtolower($elm['value']) : '';
        $listtype = isset($elm['listtype']) && \is_string($elm['listtype'])
            ? \strtolower(\trim($elm['listtype']))
            : '';

        $map = [
            'disc' => 'Disc',
            'circle' => 'Circle',
            'square' => 'Square',
            'decimal' => 'Decimal',
            'decimal-leading-zero' => 'Decimal',
            '1' => 'Decimal',
            'upper-roman' => 'UpperRoman',
            'i' => 'LowerRoman',
            'lower-roman' => 'LowerRoman',
            'upper-alpha' => 'UpperAlpha',
            'upper-latin' => 'UpperAlpha',
            'a' => 'LowerAlpha',
            'lower-alpha' => 'LowerAlpha',
            'lower-latin' => 'LowerAlpha',
        ];

        if (($listtype !== '') && isset($map[$listtype])) {
            return $map[$listtype];
        }

        if ($tag === 'ul') {
            return 'Disc';
        }

        if ($tag === 'ol') {
            return 'Decimal';
        }

        return '';
    }

    /**
     * Close a simple block-level HTML element.
     *
     * @param THTMLRenderContext $hrc HTML render context
     * @param int    $key DOM array key.
     */
    protected function closeHTMLBlock(array &$hrc, int $key, float &$tpx, float &$tpy, float &$tpw): string
    {
        $elm = &$hrc['dom'][$key];
        $openkey = isset($elm['parent']) && \is_int($elm['parent']) ? $elm['parent'] : -1;
        /** @var THTMLAttrib $openelm */
        $openelm = (($openkey >= 0) && isset($hrc['dom'][$openkey])) ? $hrc['dom'][$openkey] : $elm;
        $float = (!empty($openelm['float']) && \is_string($openelm['float']))
            ? \strtolower(\trim((string) $openelm['float']))
            : 'none';
        // When a block closes on the same line where inline text was rendered,
        // advance by one line height before applying bottom spacing.
        $hasinlinecontent = ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE));
        $lineadvance = $hasinlinecontent ? $this->getCurrentHTMLLineAdvance($hrc, $key) : 0.0;

        $out = '';
        $restoreOriginX = (float) ($hrc['cellctx']['originx'] ?? 0.0);
        $restoreMaxWidth = (float) ($hrc['cellctx']['maxwidth'] ?? 0.0);
        if (isset($openelm['ctxoriginx']) && \is_numeric($openelm['ctxoriginx'])) {
            // Adjust the saved originx by any region-break RX shift that occurred
            // while this block was open, so closing the block does not revert to
            // the previous column's x position after a region/page break.
            $currentOffset = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);
            $savedOffset = (isset($openelm['ctxregionoffset']) && \is_numeric($openelm['ctxregionoffset']))
                ? (float) $openelm['ctxregionoffset']
                : $currentOffset;
            $restoreOriginX = (float) $openelm['ctxoriginx'] + ($currentOffset - $savedOffset);
        }
        if (isset($openelm['ctxmaxwidth']) && \is_numeric($openelm['ctxmaxwidth'])) {
            $restoreMaxWidth = (float) $openelm['ctxmaxwidth'];
        }

        if (!empty($hrc['blockbuf'])) {
            $idx = \count($hrc['blockbuf']) - 1;
            if ($hrc['blockbuf'][$idx]['openkey'] === $openkey) {
                $blk = $hrc['blockbuf'][$idx];
                \array_pop($hrc['blockbuf']);
                $blockHeight = ($tpy + $lineadvance + (float) $openelm['padding']['B']) - $blk['by'];
                if ($blk['bw'] > 0.0 && $blockHeight > 0.0) {
                    $bstyles = ($openkey >= 0)
                        ? $this->getHTMLTableCellBorderStyles($hrc, $openkey)
                        : [];
                    $fillstyle = ($openkey >= 0)
                        ? $this->getHTMLTableCellFillStyle($hrc, $openkey)
                        : null;
                    if (
                        ($openkey >= 0)
                        && isset($hrc['dom'][$openkey]['value'])
                        && (
                            ($hrc['dom'][$openkey]['value'] === 'table')
                            || ($hrc['dom'][$openkey]['value'] === 'tablehead')
                        )
                    ) {
                        // Table border is rendered by parseHTMLTagCLOSEtable().
                        // Block buffer is used only for pre-content background paint.
                        $bstyles = [];
                    }
                    $out .= $this->renderHTMLTableCell(
                        $blk['bx'],
                        $blk['by'],
                        $blk['bw'],
                        $blockHeight,
                        $blockHeight,
                        'top',
                        $bstyles,
                        $fillstyle,
                        $blk['buffer'],
                    );
                } else {
                    $out .= $blk['buffer'];
                }
            }
        }

        if (\in_array($float, ['left', 'right'], true)) {
            $floatBtmBdrOv = 0.0;
            if ($openkey >= 0) {
                $bstyles = $this->getHTMLTableCellBorderStyles($hrc, $openkey);
                if (
                    isset($bstyles[2]['width'])
                    && \is_numeric($bstyles[2]['width'])
                    && ((float) $bstyles[2]['width'] > 0.0)
                ) {
                    // Borders are centered on the rectangle edge, so reserve
                    // half of the bottom stroke below the content box.
                    $floatBtmBdrOv = ((float) $bstyles[2]['width']) / 2.0;
                }
            }

            $floatBottom = $tpy
                + $lineadvance
                + (float) $openelm['margin']['B']
                + (float) $openelm['padding']['B']
                + $this->getHTMLTagVSpace($hrc, $key, 1)
                + $floatBtmBdrOv;
            $currentBottom = (float) $hrc['cellctx']['floatrowbottom'];
            $hrc['cellctx']['floatrowbottom'] = \max($currentBottom, $floatBottom);
            $tpy = (float) $hrc['cellctx']['floatrowtop'];
            $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
            $hrc['cellctx']['originx'] = $restoreOriginX;
            $hrc['cellctx']['maxwidth'] = $restoreMaxWidth;
            $hrc['cellctx']['pendingblockmarginb'] = 0.0;

            $role = $this->getHTMLStructRole($elm);
            if ($role !== '') {
                $this->endStructElem();
            }

            return $out;
        }

        $hrc['cellctx']['originx'] = $restoreOriginX;
        $hrc['cellctx']['maxwidth'] = $restoreMaxWidth;
        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
        $tpy += $lineadvance + (float) $openelm['margin']['B'] + (float) $openelm['padding']['B']
            + $this->getHTMLTagVSpace($hrc, $key, 1);
        $hrc['cellctx']['pendingblockmarginb'] = (float) $openelm['margin']['B'];

        $role = $this->getHTMLStructRole($elm);
        if ($role !== '') {
            $this->endStructElem();
        }

        return $out;
    }

    /**
     * Shift the HTML cursor vertically for sub/sup blocks.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function shiftHTMLVerticalPosition(array &$hrc, int $key, float &$tpy, float $ratio): string
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return '';
        }

        $elm = &$hrc['dom'][$key];
        if (empty($elm['fontsize']) || !\is_numeric($elm['fontsize'])) {
            return '';
        }

        $tpy += ((float) $elm['fontsize'] * $ratio);
        return '';
    }

    /**
     * Render raw literal text using the current HTML style context.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function renderHTMLLiteralText(
        array &$hrc,
        int $key,
        string $text,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        if ($text === '') {
            return '';
        }

        $elm = &$hrc['dom'][$key];
        $txtelm = $elm;
        $txtelm['tag'] = false;
        $txtelm['opening'] = false;
        $txtelm['self'] = false;
        $txtelm['value'] = $text;

        $txtkey = \count($hrc['dom']);
        $hrc['dom'][$txtkey] = $txtelm;
        $out = $this->parseHTMLText($hrc, $txtkey, $tpx, $tpy, $tpw, $tph);
        unset($hrc['dom'][$txtkey]);

        return $out;
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
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     * @param int $position 0 = before open, 1 = after close.
     */
    protected function getHTMLTagVSpace(array &$hrc, int $key, int $position): float
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return 0.0;
        }

        $elm = &$hrc['dom'][$key];
        $tag = (isset($elm['value']) && \is_string($elm['value'])) ? $elm['value'] : '';
        if (empty($this->tagvspaces[$tag][$position])) {
            return 0.0;
        }

        $tvs = $this->tagvspaces[$tag][$position];
        $lineheight = $this->getHTMLLineAdvance($hrc, $key);
        $height = (isset($tvs['h']) && \is_numeric($tvs['h'])) ? (float) $tvs['h'] : 0.0;
        $lines = (isset($tvs['n']) && \is_numeric($tvs['n'])) ? (int) $tvs['n'] : 0;
        return $height + $lineheight * $lines;
    }

    /**
     * Render an HTML image and advance the inline cursor.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     */
    protected function renderHTMLImage(array &$hrc, int $key, float &$tpx, float &$tpy, float &$tpw): string
    {
        $elm = &$hrc['dom'][$key];
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $alt = (isset($attr['alt']) && \is_string($attr['alt'])) ? $attr['alt'] : '[img]';
        if (empty($attr['src']) || !\is_string($attr['src'])) {
            $lineheight = $this->getHTMLLineAdvance($hrc, $key);
            return $this->renderHTMLLiteralText($hrc, $key, $alt, $tpx, $tpy, $tpw, $lineheight);
        }

        $src = $attr['src'];

        // Support base64 data URIs: data:<mime>;base64,<data>
        if (\preg_match('/^data:[^;]+;base64,(.+)$/s', $src, $matches)) {
            $decoded = \base64_decode($matches[1], true);
            if ($decoded !== false) {
                $src = '@' . $decoded;
            }
        }

        $lineheight = $this->getHTMLLineAdvance($hrc, $key);
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
        $valign = (isset($attr['align']) && \is_string($attr['align']))
            ? \strtolower(\trim($attr['align']))
            : 'bottom';
        $font = $this->getHTMLFontMetric($hrc, $key);
        $curAscent = (isset($font['ascent']) && \is_numeric($font['ascent']))
            ? $this->toUnit((float) $font['ascent'])
            : 0.0;
        if (empty($hrc['cellctx']['lineascent']) || !\is_numeric($hrc['cellctx']['lineascent'])) {
            $lineascent = $this->measureHTMLInlineRunMaxAscent($hrc, $key);
            if ($lineascent <= 0.0) {
                $lineascent = $curAscent;
            }

            $hrc['cellctx']['lineascent'] = $lineascent;
        }

        $lineascent = (float) $hrc['cellctx']['lineascent'];
        if ($lineascent < $curAscent) {
            $lineascent = $curAscent;
            $hrc['cellctx']['lineascent'] = $lineascent;
        }

        $baseliney = $tpy + $lineascent;
        $imagey = match ($valign) {
            'top' => $tpy,
            'middle' => $tpy + (($lineheight - $height) / 2.0),
            default => $baseliney - $height,
        };

        // Horizontal alignment: inherit text-align for inline image runs.
        $lineOriginX = $hrc['cellctx']['lineoriginx'];
        $lineOffset = (float) ($tpx - $lineOriginX);
        $availableWidth = ($hrc['cellctx']['maxwidth'] > 0) ? $hrc['cellctx']['maxwidth'] : $tpw;
        $remainingWidth = ($hrc['cellctx']['maxwidth'] > 0)
            ? \max(0.0, $tpw)
            : (($tpw > 0) ? $tpw : $availableWidth);

        $halign = empty($elm['align']) ? ($this->rtl ? 'R' : 'L') : (string) $elm['align'];
        $imagex = $tpx;
        if (
            (($halign === 'C') || ($halign === 'R'))
            && ($availableWidth > 0.0)
            && (
                ($lineOffset <= self::WIDTH_TOLERANCE)
                || $this->isHTMLInlineRunFirstRenderableNode($hrc, $key)
            )
            && ($width <= ($remainingWidth + self::WIDTH_TOLERANCE))
        ) {
            $lineWidth = $this->measureHTMLInlineLineWidth($hrc, $key, $availableWidth);
            if (($lineWidth > 0.0) && ($lineWidth <= ($availableWidth + self::WIDTH_TOLERANCE))) {
                $imagex = $lineOriginX + match ($halign) {
                    'R' => \max(0.0, $availableWidth - $lineWidth),
                    default => \max(0.0, ($availableWidth - $lineWidth) / 2.0),
                };
            }
        }

        $out = '';
        try {
            $pageheight = $this->page->getPage()['height'];
            if (\str_ends_with(\strtolower($src), '.svg')) {
                $svgid = $this->addSVG($src, $imagex, $imagey, $width, $height, $pageheight);
                $out = $this->getSetSVG($svgid);
            } else {
                $imgid = $this->image->add($src);
                $out = $this->image->getSetImage($imgid, $imagex, $imagey, $width, $height, $pageheight);
            }
        } catch (\Throwable) {
            return $this->renderHTMLLiteralText($hrc, $key, $alt, $tpx, $tpy, $tpw, $height);
        }

        $tpx = $imagex + $width;
        $imagebottom = $imagey + $height;
        $this->updateHTMLLineAdvance($hrc, \max($lineheight, $imagebottom - $tpy));
        if (
            empty($hrc['cellctx']['linebottom'])
            || !\is_numeric($hrc['cellctx']['linebottom'])
            || ($imagebottom > (float) $hrc['cellctx']['linebottom'])
        ) {
            $hrc['cellctx']['linebottom'] = $imagebottom;
        }
        if ($hrc['cellctx']['maxwidth'] > 0) {
            $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
        }

        if ($this->pdfuaMode !== '') {
            $out = $this->tagPdfUaFigureContent($out, $this->page->getPageId(), $alt);
        }

        return $out;
    }

    /**
     * Build border styles for HTML table cells.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     *
     * @return array<int|string, BorderStyle>
     */
    protected function getHTMLTableCellBorderStyles(array &$hrc, int $key): array
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return [];
        }

        $elm = &$hrc['dom'][$key];
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
     * In collapsed tables, shared borders should be owned by a single cell.
     * Keep the leading top/left edges on the first row/column and let the
     * neighboring preceding cells provide shared edges elsewhere.
     *
     * @param array<int|string, BorderStyle> $styles
     * @return array<int|string, BorderStyle>
     */
    protected function getHTMLCollapsedTableCellBorderStyles(
        array $styles,
        bool $keepTop,
        bool $keepLeft,
    ): array {
        if ($styles === []) {
            return [];
        }

        if (isset($styles['all']) && \is_array($styles['all'])) {
            /** @var BorderStyle $allstyle */
            $allstyle = $styles['all'];
            $styles = [0 => $allstyle, 1 => $allstyle, 2 => $allstyle, 3 => $allstyle];
        }

        if (!$keepTop) {
            unset($styles[0]);
        }
        if (!$keepLeft) {
            unset($styles[3]);
        }

        return $styles;
    }

    /**
     * Determine the CSS border-style keyword used for collapsed-border precedence.
     *
    * @param BorderStyleOpt $style
     */
    protected function getHTMLCollapsedBorderStyleName(array $style): string
    {
        if (isset($style['cssBorderStyle']) && \is_string($style['cssBorderStyle'])) {
            return \strtolower($style['cssBorderStyle']);
        }

        if (!empty($style['dashArray']) && \is_array($style['dashArray'])) {
            $dash = (int) ($style['dashArray'][0] ?? 0);
            if ($dash === 1) {
                return 'dotted';
            }

            return 'dashed';
        }

        $width = (isset($style['lineWidth']) && \is_numeric($style['lineWidth']))
            ? (float) $style['lineWidth'] : 0.0;

        return ($width > 0.0) ? 'solid' : 'none';
    }

    /**
     * Rank CSS border styles for collapsed-border conflict resolution.
     */
    protected function getHTMLCollapsedBorderStyleRank(string $style): int
    {
        return match ($style) {
            'hidden' => 100,
            'double' => 8,
            'solid' => 7,
            'dashed' => 6,
            'dotted' => 5,
            'ridge' => 4,
            'outset' => 3,
            'groove' => 2,
            'inset' => 1,
            'none' => 0,
            default => 7,
        };
    }

    /**
     * Check whether a parsed border style should render.
     *
        * @param BorderStyleOpt $style
     */
    protected function isHTMLRenderableBorderStyle(array $style): bool
    {
        $styleName = $this->getHTMLCollapsedBorderStyleName($style);
        if (($styleName === 'none') || ($styleName === 'hidden')) {
            return false;
        }

        return isset($style['lineWidth'])
            && \is_numeric($style['lineWidth'])
            && ((float) $style['lineWidth'] > 0.0);
    }

    /**
     * Pick the preferred collapsed-border style between adjacent edges.
     * Prefer hidden, then greater width, then CSS style precedence.
     *
     * @param BorderStyle $leftStyle
     * @param BorderStyle $rightStyle
     *
     * @return BorderStyle
     */
    protected function getHTMLCollapsedPreferredBorderStyle(array $leftStyle, array $rightStyle): array
    {
        $leftKind = $this->getHTMLCollapsedBorderStyleName($leftStyle);
        $rightKind = $this->getHTMLCollapsedBorderStyleName($rightStyle);

        if ($leftKind === 'hidden') {
            return $leftStyle;
        }

        if ($rightKind === 'hidden') {
            return $rightStyle;
        }

        if ($leftKind === 'none') {
            return $rightStyle;
        }

        if ($rightKind === 'none') {
            return $leftStyle;
        }

        $leftWidth = (isset($leftStyle['lineWidth']) && \is_numeric($leftStyle['lineWidth']))
            ? (float) $leftStyle['lineWidth'] : 0.0;
        $rightWidth = (isset($rightStyle['lineWidth']) && \is_numeric($rightStyle['lineWidth']))
            ? (float) $rightStyle['lineWidth'] : 0.0;

        if ($rightWidth > $leftWidth) {
            return $rightStyle;
        }

        if ($leftWidth === $rightWidth) {
            $leftRank = $this->getHTMLCollapsedBorderStyleRank($leftKind);
            $rightRank = $this->getHTMLCollapsedBorderStyleRank($rightKind);

            if ($rightRank > $leftRank) {
                return $rightStyle;
            }
        }

        return $leftStyle;
    }

    /**
     * Pick the preferred collapsed shared vertical border style.
     * Equal-tie ownership follows table direction: left cell in LTR, right cell in RTL.
     *
     * @param BorderStyle $leftCellStyle
     * @param BorderStyle $rightCellStyle
     *
     * @return BorderStyle
     */
    protected function getHTMLCollapsedPreferredVerticalBorderStyle(
        array $leftCellStyle,
        array $rightCellStyle,
        string $dir = 'ltr',
    ): array {
        if (\strtolower(\trim($dir)) === 'rtl') {
            return $this->getHTMLCollapsedPreferredBorderStyle($rightCellStyle, $leftCellStyle);
        }

        return $this->getHTMLCollapsedPreferredBorderStyle($leftCellStyle, $rightCellStyle);
    }

    /**
     * Build fill style for HTML table cells.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     *
     * @return ?BorderStyle
     */
    protected function getHTMLTableCellFillStyle(array &$hrc, int $key): ?array
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return null;
        }

        $elm = &$hrc['dom'][$key];
        if (empty($elm['bgcolor']) || !\is_string($elm['bgcolor'])) {
            return null;
        }

        return $this->getHTMLFillStyle($elm['bgcolor']);
    }

    /**
     * Build fill style for HTML background painting.
     *
     * @return BorderStyle
     */
    protected function getHTMLFillStyle(string $fillcolor): array
    {
        return [
            'lineWidth' => 0,
            'lineCap' => 'butt',
            'lineJoin' => 'miter',
            'miterLimit' => 10,
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => '',
            'fillColor' => $fillcolor,
        ];
    }

    /**
     * Determine whether a text background color comes from a block-level ancestor.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function hasBlockLvBgAncestor(array &$hrc, int $key): bool
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return false;
        }

        $elm = &$hrc['dom'][$key];
        if (empty($elm['bgcolor']) || !\is_string($elm['bgcolor'])) {
            return false;
        }

        $dom = &$hrc['dom'];
        $parent = isset($elm['parent']) && \is_int($elm['parent']) ? $elm['parent'] : -1;

        while (($parent >= 0) && isset($dom[$parent])) {
            $ancestor = $dom[$parent];
            $parent = isset($ancestor['parent']) && \is_int($ancestor['parent']) ? $ancestor['parent'] : -1;

            if (empty($ancestor['tag']) || empty($ancestor['opening'])) {
                continue;
            }

            if (empty($ancestor['bgcolor']) || !\is_string($ancestor['bgcolor'])) {
                continue;
            }

            if ($ancestor['bgcolor'] !== $elm['bgcolor']) {
                return false;
            }

            return !empty($ancestor['block']);
        }

        return false;
    }

    /**
     * Find the nearest opening ancestor tag with the given name.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param string $tagname Lowercase HTML tag name.
     *
     * @return int DOM key or -1 when not found.
     */
    protected function findHTMLAncestorOpeningTag(array &$hrc, int $key, string $tagname): int
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return -1;
        }

        $parent = isset($hrc['dom'][$key]['parent']) && \is_int($hrc['dom'][$key]['parent'])
            ? $hrc['dom'][$key]['parent']
            : -1;
        $visited = [];

        while (($parent >= 0) && isset($hrc['dom'][$parent])) {
            if (isset($visited[$parent])) {
                return -1;
            }
            $visited[$parent] = true;

            $ancestor = $hrc['dom'][$parent];
            if (
                !empty($ancestor['tag'])
                && !empty($ancestor['opening'])
                && isset($ancestor['value'])
                && ($ancestor['value'] === $tagname)
            ) {
                return $parent;
            }

            $parent = isset($ancestor['parent']) && \is_int($ancestor['parent']) ? $ancestor['parent'] : -1;
        }

        return -1;
    }

    /**
     * Build the default action for HTML button-like input elements.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param array<string, string> $attr Input attributes.
     *
     * @return string|array<string, mixed>
     */
    protected function getHTMLInputButtonAction(array &$hrc, int $key, string $type, array $attr): string|array
    {
        if (isset($attr['onclick']) && \is_string($attr['onclick']) && ($attr['onclick'] !== '')) {
            return $attr['onclick'];
        }

        if ($type === 'reset') {
            return ['S' => 'ResetForm'];
        }

        if ($type !== 'submit') {
            return '';
        }

        $formkey = $this->findHTMLAncestorOpeningTag($hrc, $key, 'form');
        $formattr = [];
        $formAttr = ($formkey >= 0) && isset($hrc['dom'][$formkey]['attribute'])
            && \is_array($hrc['dom'][$formkey]['attribute']);
        if ($formAttr) {
            $formattr = $hrc['dom'][$formkey]['attribute'];
        }

        $action = ['S' => 'SubmitForm'];
        $submitUrl = (isset($attr['formaction']) && \is_string($attr['formaction']))
            ? \trim($attr['formaction'])
            : '';
        if (($submitUrl === '') && isset($formattr['action']) && \is_string($formattr['action'])) {
            $submitUrl = \trim($formattr['action']);
        }
        if ($submitUrl !== '') {
            $action['F'] = $submitUrl;
        }

        $method = (isset($attr['formmethod']) && \is_string($attr['formmethod']))
            ? \strtolower(\trim($attr['formmethod']))
            : '';
        if (($method === '') && isset($formattr['method']) && \is_string($formattr['method'])) {
            $method = \strtolower(\trim($formattr['method']));
        }

        $flags = ['ExportFormat'];
        if ($method === 'get') {
            $flags[] = 'GetMethod';
        }
        $action['Flags'] = $flags;

        return $action;
    }

    /**
     * Determine whether a boolean HTML attribute is enabled.
     *
     * @param array<string, mixed> $attr
     */
    protected function isHTMLBooleanAttributeEnabled(array $attr, string $name): bool
    {
        $name = \strtolower($name);
        $rawval = null;
        foreach ($attr as $akey => $aval) {
            if (!\is_string($akey)) {
                continue;
            }

            if (\strtolower($akey) !== $name) {
                continue;
            }

            $rawval = $aval;
            break;
        }

        if ($rawval === null) {
            return false;
        }

        if (\is_bool($rawval)) {
            return $rawval;
        }

        if (!\is_string($rawval)) {
            return true;
        }

        $value = \strtolower(\trim($rawval));
        if (($value === 'false') || ($value === '0') || ($value === 'off') || ($value === 'no')) {
            return false;
        }

        return true;
    }

    /**
     * Convert HTML form attributes to JavaScript annotation properties.
     *
        * @param array<string, mixed> $attr
     *
     * @return array<string, mixed>
     */
    protected function getHTMLFormFieldJSProperties(array $attr, string $fieldkind): array
    {
        $jsp = [];

        $readonly = $this->isHTMLBooleanAttributeEnabled($attr, 'readonly');
        $required = $this->isHTMLBooleanAttributeEnabled($attr, 'required');
        $disabled = $this->isHTMLBooleanAttributeEnabled($attr, 'disabled');
        if ($disabled) {
            // Disabled fields are represented conservatively as read-only widgets.
            $readonly = true;
            // HTML disabled controls do not participate in required validation.
            $required = false;
        }

        if ($readonly) {
            $jsp['readonly'] = 'true';
        }
        if ($required) {
            $jsp['required'] = 'true';
        }

        foreach ($attr as $akey => $aval) {
            if (!\is_string($akey) || (\strtolower($akey) !== 'maxlength') || !\is_numeric($aval)) {
                continue;
            }

            $maxlen = (int) $aval;
            if ($maxlen > 0) {
                $jsp['charLimit'] = $maxlen;
            }
            break;
        }

        // Conservative per-type defaults that improve generated AcroForm semantics.
        if (\in_array($fieldkind, ['email', 'url', 'tel', 'date', 'number'], true)) {
            $jsp['doNotSpellCheck'] = 'true';
        }
        if ($fieldkind === 'number') {
            $jsp['alignment'] = 'right';
        }

        return $jsp;
    }

    /**
     * Resolve the human-readable label text for an HTML form control.
     *
     * Priority:
     * 1) Explicit <label for="control-id"> association.
     * 2) Enclosing <label> ancestor around the control.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function getHTMLLabelTextForControl(array &$hrc, int $key): string
    {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return '';
        }

        $elm = $hrc['dom'][$key];
        $controlid = (isset($elm['attribute']['id']) && \is_string($elm['attribute']['id']))
            ? \trim($elm['attribute']['id'])
            : '';

        if ($controlid !== '') {
            foreach ($hrc['dom'] as $lkey => $node) {
                if (
                    empty($node['tag'])
                    || empty($node['opening'])
                    || empty($node['value'])
                    || ($node['value'] !== 'label')
                ) {
                    continue;
                }

                $target = (isset($node['attribute']['for']) && \is_string($node['attribute']['for']))
                    ? \trim($node['attribute']['for'])
                    : '';
                if ($target !== $controlid) {
                    continue;
                }

                $label = $this->getHTMLNodeTextContent($hrc['dom'], $lkey);
                if ($label !== '') {
                    return $label;
                }
            }
        }

        $labelkey = $this->findHTMLAncestorOpeningTag($hrc, $key, 'label');
        if ($labelkey < 0) {
            return '';
        }

        return $this->getHTMLNodeTextContent($hrc['dom'], $labelkey);
    }

    /**
     * Extract plain text content for a DOM node, including nested text children.
     *
     * @param array<int, THTMLAttrib> $dom DOM array.
     */
    protected function getHTMLNodeTextContent(array $dom, int $startkey): string
    {
        if (empty($dom[$startkey]['tag']) || empty($dom[$startkey]['opening'])) {
            return '';
        }

        $endkey = $this->findHTMLClosingTagIndex($dom, $startkey);
        if ($endkey <= $startkey) {
            return '';
        }

        $text = '';
        for ($idx = ($startkey + 1); $idx < $endkey; ++$idx) {
            $node = $dom[$idx] ?? [];
            if (!empty($node['tag']) || empty($node['value']) || !\is_string($node['value'])) {
                continue;
            }

            $text .= $node['value'];
        }

        $text = \preg_replace('/\s+/u', ' ', $text) ?? '';
        return \trim($text);
    }

    /**
     * Append a rendered HTML fragment to the active table-cell buffer when needed.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function captureHTMLTableCellBuffer(array &$hrc, string $fragment): bool
    {
        if (($fragment === '') || empty($hrc['bcellctx'])) {
            return false;
        }

        $cellidx = \count($hrc['bcellctx']) - 1;
        if (
            !isset($hrc['bcellctx'][$cellidx]['buffer'])
            || !\is_string($hrc['bcellctx'][$cellidx]['buffer'])
        ) {
            return false;
        }

        /** @var THTMLTableCellContext $cellctx */
        $cellctx = $hrc['bcellctx'][$cellidx];
        $cellctx['buffer'] .= $fragment;
        $hrc['bcellctx'][$cellidx] = $cellctx;

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
        $cellspacing = $table['cellspacingh'];
        $tox = $table['originx'] + $cellspacing;
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
        $cellspacing = $table['cellspacingh'];
        for ($i = 0; $i < $colspan; ++$i) {
            if ($i > 0) {
                $tcw += $cellspacing;
            }

            $tcw += ($table['colwidths'][$colindex + $i] ?? $table['colwidth']);
        }

        return $tcw;
    }

    /**
     * Resolve a table-cell explicit width using table-available user units.
     *
     * Percentage widths from HTML/CSS must be resolved against the table width,
     * not the generic default unit reference used during early DOM parsing.
     *
     * @param THTMLAttrib $elm
     */
    protected function getHTMLTableCellExplicitWidth(array $elm, float $availableWidth): float
    {
        if (($availableWidth <= 0.0) || !isset($elm['style']) || !\is_array($elm['style'])) {
            return (!empty($elm['width']) && \is_numeric($elm['width'])) ? (float) $elm['width'] : 0.0;
        }

        $rawWidth = '';
        if (!empty($elm['style']['width']) && \is_string($elm['style']['width'])) {
            $rawWidth = \trim($elm['style']['width']);
        } elseif (!empty($elm['attribute']['width']) && \is_string($elm['attribute']['width'])) {
            $rawWidth = \trim($elm['attribute']['width']);
        }

        if (($rawWidth !== '') && (\preg_match('/^([0-9.+\-]+)\s*%$/', $rawWidth, $match) === 1)) {
            return \max(0.0, ($availableWidth * (float) $match[1]) / 100.0);
        }

        return (!empty($elm['width']) && \is_numeric($elm['width'])) ? (float) $elm['width'] : 0.0;
    }

    /**
     * Resolve an explicit width for generic block layout against available inner width.
     *
     * Percentage widths are resolved against the current block container width.
     * Absolute lengths use the pre-parsed numeric width value.
     *
     * @param THTMLAttrib $elm
     */
    protected function getHTMLResolvedExplicitWidth(array $elm, float $availableWidth): float
    {
        if ($availableWidth <= 0.0) {
            return 0.0;
        }

        $rawWidth = '';
        if (!empty($elm['style']['width']) && \is_string($elm['style']['width'])) {
            $rawWidth = \trim($elm['style']['width']);
        } elseif (!empty($elm['attribute']['width']) && \is_string($elm['attribute']['width'])) {
            $rawWidth = \trim($elm['attribute']['width']);
        }

        if (($rawWidth !== '') && (\preg_match('/^([0-9.+\-]+)\s*%$/', $rawWidth, $match) === 1)) {
            return \max(0.0, ($availableWidth * (float) $match[1]) / 100.0);
        }

        return (!empty($elm['width']) && \is_numeric($elm['width'])) ? (float) $elm['width'] : 0.0;
    }

    /**
     * Resolve horizontal margin values against available width when defined as percentages.
     *
     * @param THTMLAttrib $elm
     */
    protected function getHTMLResolvedMarginSide(array $elm, float $availableWidth, string $side): float
    {
        $fallback = 0.0;
        if (($side === 'L') && !empty($elm['margin']['L']) && \is_numeric($elm['margin']['L'])) {
            $fallback = (float) $elm['margin']['L'];
        } elseif (($side === 'R') && !empty($elm['margin']['R']) && \is_numeric($elm['margin']['R'])) {
            $fallback = (float) $elm['margin']['R'];
        }

        if (($availableWidth <= 0.0) || empty($elm['style']) || !\is_array($elm['style'])) {
            return $fallback;
        }

        $styleKey = ($side === 'L') ? 'margin-left' : 'margin-right';
        if (empty($elm['style'][$styleKey]) || !\is_string($elm['style'][$styleKey])) {
            return $fallback;
        }

        $rawMargin = \trim($elm['style'][$styleKey]);
        if ((\preg_match('/^([0-9.+\-]+)\s*%$/', $rawMargin, $match) === 1)) {
            return \max(0.0, ($availableWidth * (float) $match[1]) / 100.0);
        }

        return $fallback;
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
        $tableLayout = (isset($dom[$tablekey]['table-layout']) && \is_string($dom[$tablekey]['table-layout']))
            ? \strtolower(\trim((string) $dom[$tablekey]['table-layout']))
            : 'auto';
        $contentWeights = \array_fill(0, $cols, 0.0);
        $hasExplicitHints = false;
        $hasContentHints = false;
        $hintcol = 0;
        $colgroupactive = false;
        $colgrouphascols = false;
        $colgroupstart = 0;
        $colgroupspan = 1;
        $colgroupwidth = 0.0;

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

            // Resolve COLGROUP/COL width hints before first-row TD/TH fallback.
            if (!$inFirstRow && !empty($elm['opening']) && ($val === 'colgroup')) {
                $colgroupactive = true;
                $colgrouphascols = false;
                $colgroupstart = $hintcol;
                $colgroupspan = (!empty($elm['attribute']['span']) && \is_numeric($elm['attribute']['span']))
                    ? \max(1, (int) $elm['attribute']['span']) : 1;
                $colgroupspan = \max(1, \min($colgroupspan, $cols - $colgroupstart));
                $colgroupwidth = $this->getHTMLTableCellExplicitWidth($elm, $availableWidth);
                continue;
            }

            if (!$inFirstRow && empty($elm['opening']) && ($val === 'colgroup') && $colgroupactive) {
                if (!$colgrouphascols && ($colgroupspan > 0)) {
                    if ($colgroupwidth > 0.0) {
                        $percol = $colgroupwidth / $colgroupspan;
                        for ($i = 0; $i < $colgroupspan; ++$i) {
                            $idx = $colgroupstart + $i;
                            if (($idx >= 0) && ($idx < $cols)) {
                                $colwidths[$idx] = $percol;
                                $hasExplicitHints = true;
                            }
                        }
                    }

                    $hintcol = \min($cols, $colgroupstart + $colgroupspan);
                }

                $colgroupactive = false;
                $colgrouphascols = false;
                $colgroupstart = $hintcol;
                $colgroupspan = 1;
                $colgroupwidth = 0.0;
                continue;
            }

            if (!$inFirstRow && !empty($elm['opening']) && ($val === 'col')) {
                if ($colgroupactive) {
                    $colgrouphascols = true;
                }

                $colspan = (!empty($elm['attribute']['span']) && \is_numeric($elm['attribute']['span']))
                    ? \max(1, (int) $elm['attribute']['span']) : 1;
                $colspan = \max(1, \min($colspan, $cols - $hintcol));
                $colw = $this->getHTMLTableCellExplicitWidth($elm, $availableWidth);
                if (($colw > 0.0) && ($colspan > 0)) {
                    $percol = $colw / $colspan;
                    for ($i = 0; $i < $colspan; ++$i) {
                        $idx = $hintcol + $i;
                        if (($idx >= 0) && ($idx < $cols)) {
                            $colwidths[$idx] = $percol;
                            $hasExplicitHints = true;
                        }
                    }
                }

                $hintcol = \min($cols, $hintcol + $colspan);
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

                $cellw = $this->getHTMLTableCellExplicitWidth($elm, $availableWidth);
                if ($cellw > 0.0) {
                    $percw = $cellw / $colspan;
                    for ($i = 0; $i < $colspan; ++$i) {
                        if ($colid + $i < $cols) {
                            $colwidths[$colid + $i] = $percw;
                            $hasExplicitHints = true;
                        }
                    }
                } elseif (($tableLayout === 'auto') && !$hasExplicitHints && !empty($elm['content'])) {
                    $plain = \trim((string) (\strip_tags((string) $elm['content'])));
                    if ($plain !== '') {
                        $plain = \preg_replace('/\s+/u', ' ', $plain) ?? $plain;
                        $weight = \mb_strlen($plain, $this->encoding);
                        if ($weight > 0) {
                            $percol = ((float) $weight) / $colspan;
                            for ($i = 0; $i < $colspan; ++$i) {
                                if (($colid + $i) < $cols) {
                                    $contentWeights[$colid + $i] += $percol;
                                    $hasContentHints = true;
                                }
                            }
                        }
                    }
                }

                $colid += $colspan;
            }
        }

        if (($tableLayout === 'auto') && !$hasExplicitHints && $hasContentHints) {
            $sum = \array_sum($contentWeights);
            if ($sum > 0.0) {
                for ($idx = 0; $idx < $cols; ++$idx) {
                    $weight = $contentWeights[$idx];
                    if ($weight <= 0.0) {
                        continue;
                    }

                    $colwidths[$idx] = $availableWidth * ($weight / $sum);
                }
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
        float $contenth,
        string $valign,
        array $styles,
        ?array $fillstyle,
        string $buffer,
    ): string {
        $out = '';
        $decor = '';

        if ($fillstyle !== null) {
            $decor .= $this->graph->getStartTransform()
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

        $contenth = \max(0.0, $contenth);
        $gap = \max(0.0, $cellh - $contenth);
        $offset = match (\strtolower(\trim($valign))) {
            'middle' => ($gap / 2.0),
            'bottom' => $gap,
            default => 0.0,
        };
        if (($offset > self::WIDTH_TOLERANCE) && ($buffer !== '')) {
            $buffer = $this->graph->getStartTransform()
                . \sprintf("1 0 0 1 0 %F cm\n", -$offset)
                . $buffer
                . $this->graph->getStopTransform();
        }

        if (($decor !== '') && ($this->pdfuaMode !== '')) {
            $out .= $this->tagPdfUaArtifactContent($decor);
        } else {
            $out .= $decor;
        }

        $out .= $buffer;

        if ($styles === []) {
            return $out;
        }

        $decor = $this->graph->getStartTransform();
        if (!empty($styles['all'])) {
            $decor .= $this->graph->getBasicRect(
                $cellx,
                $rowtop,
                $cellw,
                $cellh,
                's',
                $styles['all'],
            );
        } else {
            $decor .= $this->drawHTMLRectBorderSides(
                $cellx,
                $rowtop,
                $cellw,
                $cellh,
                $styles,
            );
        }

        $decor .= $this->graph->getStopTransform();
        if ($this->pdfuaMode !== '') {
            $out .= $this->tagPdfUaArtifactContent($decor);
            return $out;
        }

        return $out . $decor;
    }

    /**
     * Draw only the explicitly defined rectangle border sides (T,R,B,L).
     *
     * @param array<int|string, BorderStyle> $styles
     */
    protected function drawHTMLRectBorderSides(
        float $cellx,
        float $rowtop,
        float $cellw,
        float $cellh,
        array $styles,
    ): string {
        if ($styles === []) {
            return '';
        }

        $out = '';

        if (isset($styles[0]) && \is_array($styles[0])) {
            $out .= $this->graph->getLine($cellx, $rowtop, ($cellx + $cellw), $rowtop, $styles[0]);
        }

        if (isset($styles[1]) && \is_array($styles[1])) {
            $out .= $this->graph->getLine(
                ($cellx + $cellw),
                $rowtop,
                ($cellx + $cellw),
                ($rowtop + $cellh),
                $styles[1]
            );
        }

        if (isset($styles[2]) && \is_array($styles[2])) {
            $out .= $this->graph->getLine(
                ($cellx + $cellw),
                ($rowtop + $cellh),
                $cellx,
                ($rowtop + $cellh),
                $styles[2]
            );
        }

        if (isset($styles[3]) && \is_array($styles[3])) {
            $out .= $this->graph->getLine($cellx, ($rowtop + $cellh), $cellx, $rowtop, $styles[3]);
        }

        return $out;
    }

    /**
     * Decode a CSS quoted string payload used by pseudo-element content.
     */
    protected function decodeHTMLCSSString(string $text): string
    {
        return \preg_replace_callback(
            '/\\\\(.)/s',
            static function (array $match): string {
                return match ($match[1]) {
                    'n' => "\n",
                    'r' => "\r",
                    't' => "\t",
                    default => $match[1],
                };
            },
            $text,
        ) ?? '';
    }

    /**
     * Extract text-only content declaration from pseudo-element CSS style.
     */
    protected function getHTMLPseudoTextContent(string $style): string
    {
        $match = [];
        if (
            \preg_match(
                '/(?:^|;)\\s*content\\s*:\\s*("(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')\\s*(?:;|$)/i',
                $style,
                $match,
            ) === 1
        ) {
                $quoted = $match[1];
            if (\strlen($quoted) < 2) {
                return '';
            }

            return $this->decodeHTMLCSSString(\substr($quoted, 1, -1));
        }

        return '';
    }

    /**
     * Remove content declaration from pseudo-element CSS style.
     */
    protected function stripHTMLPseudoContentDeclaration(string $style): string
    {
        $withoutContent = \preg_replace(
            '/(?:^|;)\\s*content\\s*:\\s*(?:"(?:[^"\\\\]|\\\\.)*"|\'(?:[^\'\\\\]|\\\\.)*\')\\s*(?:;|$)/i',
            ';',
            $style,
        ) ?? '';

        $withoutContent = \preg_replace('/;+/', ';', $withoutContent) ?? '';
        return \trim($withoutContent, " \t\n\r\0\x0B;");
    }

    /**
     * Render pseudo-element generated content in text-only mode.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param string $stylekey Either pseudo-before-style or pseudo-after-style.
     * @param ?callable(string):void $appendFragment
     */
    protected function renderHTMLPseudoGeneratedText(
        array &$hrc,
        int $key,
        string $stylekey,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        ?callable $appendFragment = null,
    ): string {
        if (
            !isset($hrc['dom'][$key]['attribute'][$stylekey])
            || !\is_string($hrc['dom'][$key]['attribute'][$stylekey])
        ) {
            return '';
        }

        $style = \trim($hrc['dom'][$key]['attribute'][$stylekey]);
        if ($style === '') {
            return '';
        }

        $text = $this->getHTMLPseudoTextContent($style);
        if ($text === '') {
            return '';
        }

        $pseudostyle = $this->stripHTMLPseudoContentDeclaration($style);
        $tmpkey = \count($hrc['dom']);

        $pseudo = $hrc['dom'][$key];
        $pseudo['tag'] = false;
        $pseudo['opening'] = false;
        $pseudo['self'] = true;
        $pseudo['hide'] = false;
        $pseudo['value'] = $text;
        $pseudo['attribute'] = [];
        if ($pseudostyle !== '') {
            $pseudo['attribute']['style'] = $pseudostyle;
        }
        $pseudo['style'] = [];
        $pseudo['cssdata'] = [];
        $pseudo['csssel'] = [];

        // @phpstan-ignore-next-line parameterByRef.type
        $hrc['dom'][$tmpkey] = $pseudo;
        if ($pseudostyle !== '') {
            /** @var THTMLRenderContext $hrc */
            $this->parseHTMLStyleAttributes($hrc['dom'], $tmpkey, $key);
        }

        /** @var THTMLRenderContext $hrc */
        $fragment = $this->parseHTMLText($hrc, $tmpkey, $tpx, $tpy, $tpw, $tph, $appendFragment);
        unset($hrc['dom'][$tmpkey]);

        return $fragment;
    }

    /**
     * Render HTML fragments for a cell and dispatch each fragment to a consumer.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param callable(string):void    $appendFragment
     */
    protected function renderHTMLCellFragments(
        array &$hrc,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        callable $appendFragment,
    ): void {
        $dom = &$hrc['dom'];
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
                        $this->resetHTMLCursorAfterPageBreak($hrc, $tpx, $tpy, $tpw);
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
                                    $hrc,
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

                        $requiredh = $this->estimateHTMLTableRowHeight($hrc, $key);

                        // If a page break is about to happen for this row, flush
                        // any open block-level buffers (e.g. <table border>) onto
                        // the current page before the break, so previously
                        // rendered rows are committed to the right page rather
                        // than carried over via the buffer to the next page.
                        $region = $this->page->getRegion();
                        $regiontop = (float) $region['RY'];
                        $remaining = $this->getHTMLRemainingHeight($hrc, $tpy);
                        $willBreak = ($requiredh > 0.0)
                            && ($requiredh > ($remaining + self::WIDTH_TOLERANCE))
                            && ($tpy > ($regiontop + self::WIDTH_TOLERANCE));

                        if ($willBreak && !empty($hrc['blockbuf'])) {
                            $flush = $this->flushOpenBlockBuffers($hrc, $tpy);
                            if ($flush !== '') {
                                $appendFragment($flush);
                            }
                        }

                        $breakout = $this->breakHTMLIfNeeded(
                            $hrc,
                            $requiredh,
                            $tpx,
                            $tpy,
                            $tpw,
                            $tph,
                            $theadhtml,
                        );

                        if ($willBreak && !empty($hrc['blockbuf'])) {
                            foreach ($hrc['blockbuf'] as $bidx => $blkEntry) {
                                /** @var THTMLBlockBuf $blkEntry */
                                $blkEntry['by'] = $tpy;
                                $hrc['blockbuf'][$bidx] = $blkEntry;
                            }
                        }

                        if ($willBreak && !empty($hrc['tablestack'])) {
                            $this->resetHTMLTableStackOnPageBreak($hrc, $tpy);
                        }

                        $appendFragment($breakout);
                    }

                    if (
                        ($elm['value'] === 'li')
                        && empty($hrc['tablestack'])
                        && empty($hrc['bcellctx'])
                        && ((float) $hrc['cellctx']['maxheight'] <= 0.0)
                    ) {
                        $liLineAdvance = $this->getHTMLLineAdvance($hrc, $key);
                        if ($liLineAdvance > 0.0) {
                            $region = $this->page->getRegion();
                            $regiontop = (float) $region['RY'];
                            $remaining = $this->getHTMLRemainingHeight($hrc, $tpy);
                            $willBreak = ($liLineAdvance > ($remaining + self::WIDTH_TOLERANCE))
                                && ($tpy > ($regiontop + self::WIDTH_TOLERANCE));

                            if ($willBreak && !empty($hrc['blockbuf'])) {
                                $flush = $this->flushOpenBlockBuffers($hrc, $tpy);
                                if ($flush !== '') {
                                    $appendFragment($flush);
                                }
                            }

                            $breakout = $this->breakHTMLIfNeeded(
                                $hrc,
                                $liLineAdvance,
                                $tpx,
                                $tpy,
                                $tpw,
                                $tph,
                            );

                            if ($willBreak && !empty($hrc['blockbuf'])) {
                                foreach ($hrc['blockbuf'] as $bidx => $blkEntry) {
                                    /** @var THTMLBlockBuf $blkEntry */
                                    $blkEntry['by'] = $tpy;
                                    $hrc['blockbuf'][$bidx] = $blkEntry;
                                }
                            }

                            if ($willBreak && !empty($hrc['tablestack'])) {
                                $this->resetHTMLTableStackOnPageBreak($hrc, $tpy);
                            }

                            if ($breakout !== '') {
                                $appendFragment($breakout);
                            }
                        }
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
                                $requiredh = $this->estimateHTMLNobrHeight(
                                    $hrc,
                                    $key,
                                    ($tpw > 0.0) ? $tpw : $hrc['cellctx']['maxwidth'],
                                );
                                $appendFragment(
                                    $this->breakHTMLIfNeeded(
                                        $hrc,
                                        $requiredh,
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
                    // and store them directly on the DOM node for parseHTMLTagOPENtable.
                    if (($elm['value'] === 'table') || ($elm['value'] === 'tablehead')) {
                        $tableCols = (!empty($elm['cols']) && \is_numeric($elm['cols']))
                            ? \max(1, (int) $elm['cols']) : 1;
                        $tableWidth = ($tpw > 0) ? $tpw : $hrc['cellctx']['maxwidth'];
                        // Constrain $tableWidth to the table's own CSS width so that percentage-based
                        // TD widths resolve against the table width, not the full container width.
                        // Percentages are resolved against the current container; absolute values use elm['width'].
                        $rawTblWidth = '';
                        if (!empty($elm['style']['width']) && \is_string($elm['style']['width'])) {
                            $rawTblWidth = \trim($elm['style']['width']);
                        } elseif (!empty($elm['attribute']['width']) && \is_string($elm['attribute']['width'])) {
                            $rawTblWidth = \trim($elm['attribute']['width']);
                        }
                        if ($rawTblWidth !== '') {
                            if (\preg_match('/^([0-9.+\-]+)\s*%$/', $rawTblWidth, $pctM)) {
                                $tableWidth = \min($tableWidth, ($tableWidth * (float) $pctM[1]) / 100.0);
                            } elseif (
                                !empty($elm['width'])
                                && \is_numeric($elm['width'])
                                && ((float) $elm['width'] > 0.0)
                            ) {
                                $tableWidth = \min($tableWidth, (float) $elm['width']);
                            }
                        }
                        // @phpstan-ignore parameterByRef.type
                        $elm['pendingcellspacingh'] = (!empty($elm['attribute']['cellspacing'])
                            && \is_numeric($elm['attribute']['cellspacing']))
                            ? $this->toUnit($this->getUnitValuePoints($elm['attribute']['cellspacing']))
                            : ((!empty($elm['border-spacing']) && \is_array($elm['border-spacing'])
                                && isset($elm['border-spacing']['H']) && \is_numeric($elm['border-spacing']['H']))
                                ? (float) $elm['border-spacing']['H'] : 0.0);
                        // @phpstan-ignore parameterByRef.type
                        $elm['pendingcellspacingv'] = (!empty($elm['attribute']['cellspacing'])
                            && \is_numeric($elm['attribute']['cellspacing']))
                            ? $this->toUnit($this->getUnitValuePoints($elm['attribute']['cellspacing']))
                            : ((!empty($elm['border-spacing']) && \is_array($elm['border-spacing'])
                                && isset($elm['border-spacing']['V']) && \is_numeric($elm['border-spacing']['V']))
                                ? (float) $elm['border-spacing']['V'] : 0.0);
                        // @phpstan-ignore parameterByRef.type
                        $elm['pendingcellpadding'] = (!empty($elm['attribute']['cellpadding'])
                            && \is_numeric($elm['attribute']['cellpadding']))
                            ? $this->toUnit($this->getUnitValuePoints($elm['attribute']['cellpadding']))
                            : 0.0;
                        $effectiveCellSpacing = $this->getHTMLTableCellSpacingH($elm);
                        $availableForCols = \max(
                            0.0,
                            $tableWidth - $effectiveCellSpacing * \max(0, $tableCols + 1)
                        );
                        // @phpstan-ignore parameterByRef.type
                        $elm['pendingcolwidths'] = $this->computeHTMLTableColWidths(
                            $dom,
                            $key,
                            $tableCols,
                            $availableForCols,
                        );

                        // Ensure replayed THEAD fragments on page breaks keep
                        // the exact per-column widths computed for this table.
                        if (
                            ($elm['value'] === 'table')
                            && !empty($elm['thead'])
                            && \is_string($elm['thead'])
                            && \is_array($elm['pendingcolwidths'])
                        ) {
                            $elm['thead'] = $this->injectHTMLTableHeadColWidths(
                                $elm['thead'],
                                $elm['pendingcolwidths'],
                            );
                            $tabletheadmap[$key] = $elm['thead'];
                        }
                    }

                    // Keep the DOM node in sync with local preprocessing updates.
                    $dom[$key] = $elm;

                    $fragment = match ($elm['value']) {
                        'a'          => $this->parseHTMLTagOPENa($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'b'          => $this->parseHTMLTagOPENb($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'blockquote' => $this->parseHTMLTagOPENblockquote($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'body'       => $this->parseHTMLTagOPENbody($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'br'         => $this->parseHTMLTagOPENbr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'caption'    => $this->parseHTMLTagOPENcaption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'col'        => $this->parseHTMLTagOPENcol($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'colgroup'   => $this->parseHTMLTagOPENcolgroup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dd'         => $this->parseHTMLTagOPENdd($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'del'        => $this->parseHTMLTagOPENdel($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'div'        => $this->parseHTMLTagOPENdiv($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dl'         => $this->parseHTMLTagOPENdl($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'figure'     => $this->parseHTMLTagOPENfigure($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'figcaption' => $this->parseHTMLTagOPENfigcaption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dt'         => $this->parseHTMLTagOPENdt($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'em'         => $this->parseHTMLTagOPENem($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'font'       => $this->parseHTMLTagOPENfont($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'form'       => $this->parseHTMLTagOPENform($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h1'         => $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h2'         => $this->parseHTMLTagOPENh2($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h3'         => $this->parseHTMLTagOPENh3($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h4'         => $this->parseHTMLTagOPENh4($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h5'         => $this->parseHTMLTagOPENh5($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h6'         => $this->parseHTMLTagOPENh6($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'hr'         => $this->parseHTMLTagOPENhr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'i'          => $this->parseHTMLTagOPENi($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'img'        => $this->parseHTMLTagOPENimg($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'input'      => $this->parseHTMLTagOPENinput($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'label'      => $this->parseHTMLTagOPENlabel($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'li'         => $this->parseHTMLTagOPENli($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'marker'     => $this->parseHTMLTagOPENmarker($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'ol'         => $this->parseHTMLTagOPENol($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'optgroup'   => $this->parseHTMLTagOPENoptgroup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'option'     => $this->parseHTMLTagOPENoption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'output'     => $this->parseHTMLTagOPENoutput($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'p'          => $this->parseHTMLTagOPENp($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'code'       => $this->parseHTMLTagOPENcode($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'pre'        => $this->parseHTMLTagOPENpre($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        's'          => $this->parseHTMLTagOPENs($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'select'     => $this->parseHTMLTagOPENselect($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'small'      => $this->parseHTMLTagOPENsmall($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'span'       => $this->parseHTMLTagOPENspan($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'strike'     => $this->parseHTMLTagOPENstrike($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'strong'     => $this->parseHTMLTagOPENstrong($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'sub'        => $this->parseHTMLTagOPENsub($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'sup'        => $this->parseHTMLTagOPENsup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'table'      => $this->parseHTMLTagOPENtable($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tablehead'  => $this->parseHTMLTagOPENtablehead($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tcpdf'      => $this->parseHTMLTagOPENtcpdf($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'td'         => $this->parseHTMLTagOPENtd($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'textarea'   => $this->parseHTMLTagOPENtextarea($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tfoot'      => $this->parseHTMLTagOPENtfoot($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'th'         => $this->parseHTMLTagOPENth($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'thead'      => $this->parseHTMLTagOPENthead($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tr'         => $this->parseHTMLTagOPENtr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tt'         => $this->parseHTMLTagOPENtt($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'u'          => $this->parseHTMLTagOPENu($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'ul'         => $this->parseHTMLTagOPENul($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                    $capturedByTableCell = $this->captureHTMLTableCellBuffer($hrc, $fragment);
                    $capturedByBlock = false;
                    if (!$capturedByTableCell && ($fragment !== '') && !empty($hrc['blockbuf'])) {
                        $blockidx = \count($hrc['blockbuf']) - 1;
                        /** @var THTMLBlockBuf $blockbuf */
                        $blockbuf = $hrc['blockbuf'][$blockidx];
                        $blockbuf['buffer'] .= $fragment;
                        $hrc['blockbuf'][$blockidx] = $blockbuf;
                        $capturedByBlock = true;
                    }
                    if (!$capturedByTableCell && !$capturedByBlock) {
                        $appendFragment($fragment);
                    }

                    $beforefragment = $this->renderHTMLPseudoGeneratedText(
                        $hrc,
                        $key,
                        'pseudo-before-style',
                        $tpx,
                        $tpy,
                        $tpw,
                        $tph,
                        $appendFragment,
                    );
                    $capturedByTableCell = $this->captureHTMLTableCellBuffer($hrc, $beforefragment);
                    $capturedByBlock = false;
                    if (!$capturedByTableCell && ($beforefragment !== '') && !empty($hrc['blockbuf'])) {
                        $blockidx = \count($hrc['blockbuf']) - 1;
                        /** @var THTMLBlockBuf $blockbuf */
                        $blockbuf = $hrc['blockbuf'][$blockidx];
                        $blockbuf['buffer'] .= $beforefragment;
                        $hrc['blockbuf'][$blockidx] = $blockbuf;
                        $capturedByBlock = true;
                    }
                    if (!$capturedByTableCell && !$capturedByBlock) {
                        $appendFragment($beforefragment);
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
                        $this->resetHTMLCursorAfterPageBreak($hrc, $tpx, $tpy, $tpw);
                    }
                } else { // closing tag
                    if (
                        !empty($nobrstack) && (
                        $nobrstack[\count($nobrstack) - 1] === $elm['value']
                        )
                    ) {
                        \array_pop($nobrstack);
                    }

                    $pseudokey = (isset($elm['parent']) && \is_int($elm['parent'])) ? $elm['parent'] : $key;
                    $afterfragment = $this->renderHTMLPseudoGeneratedText(
                        $hrc,
                        $pseudokey,
                        'pseudo-after-style',
                        $tpx,
                        $tpy,
                        $tpw,
                        $tph,
                        $appendFragment,
                    );
                    $capturedByTableCell = $this->captureHTMLTableCellBuffer($hrc, $afterfragment);
                    $capturedByBlock = false;
                    if (!$capturedByTableCell && ($afterfragment !== '') && !empty($hrc['blockbuf'])) {
                        $blockidx = \count($hrc['blockbuf']) - 1;
                        /** @var THTMLBlockBuf $blockbuf */
                        $blockbuf = $hrc['blockbuf'][$blockidx];
                        $blockbuf['buffer'] .= $afterfragment;
                        $hrc['blockbuf'][$blockidx] = $blockbuf;
                        $capturedByBlock = true;
                    }
                    if (!$capturedByTableCell && !$capturedByBlock) {
                        $appendFragment($afterfragment);
                    }

                    $fragment = match ($elm['value']) {
                        'a'          => $this->parseHTMLTagCLOSEa($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'b'          => $this->parseHTMLTagCLOSEb($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'blockquote' => $this->parseHTMLTagCLOSEblockquote($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'body'       => $this->parseHTMLTagCLOSEbody($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'br'         => $this->parseHTMLTagCLOSEbr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'caption'    => $this->parseHTMLTagCLOSEcaption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'col'        => $this->parseHTMLTagCLOSEcol($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'colgroup'   => $this->parseHTMLTagCLOSEcolgroup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dd'         => $this->parseHTMLTagCLOSEdd($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'del'        => $this->parseHTMLTagCLOSEdel($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'div'        => $this->parseHTMLTagCLOSEdiv($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dl'         => $this->parseHTMLTagCLOSEdl($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'figure'     => $this->parseHTMLTagCLOSEfigure($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'figcaption' => $this->parseHTMLTagCLOSEfigcaption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'dt'         => $this->parseHTMLTagCLOSEdt($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'em'         => $this->parseHTMLTagCLOSEem($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'font'       => $this->parseHTMLTagCLOSEfont($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'form'       => $this->parseHTMLTagCLOSEform($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h1'         => $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h2'         => $this->parseHTMLTagCLOSEh2($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h3'         => $this->parseHTMLTagCLOSEh3($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h4'         => $this->parseHTMLTagCLOSEh4($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h5'         => $this->parseHTMLTagCLOSEh5($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'h6'         => $this->parseHTMLTagCLOSEh6($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'hr'         => $this->parseHTMLTagCLOSEhr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'i'          => $this->parseHTMLTagCLOSEi($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'img'        => $this->parseHTMLTagCLOSEimg($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'input'      => $this->parseHTMLTagCLOSEinput($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'label'      => $this->parseHTMLTagCLOSElabel($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'li'         => $this->parseHTMLTagCLOSEli($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'marker'     => $this->parseHTMLTagCLOSEmarker($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'ol'         => $this->parseHTMLTagCLOSEol($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'optgroup'   => $this->parseHTMLTagCLOSEoptgroup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'option'     => $this->parseHTMLTagCLOSEoption($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'output'     => $this->parseHTMLTagCLOSEoutput($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'p'          => $this->parseHTMLTagCLOSEp($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'code'       => $this->parseHTMLTagCLOSEcode($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'pre'        => $this->parseHTMLTagCLOSEpre($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        's'          => $this->parseHTMLTagCLOSEs($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'select'     => $this->parseHTMLTagCLOSEselect($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'small'      => $this->parseHTMLTagCLOSEsmall($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'span'       => $this->parseHTMLTagCLOSEspan($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'strike'     => $this->parseHTMLTagCLOSEstrike($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'strong'     => $this->parseHTMLTagCLOSEstrong($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'sub'        => $this->parseHTMLTagCLOSEsub($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'sup'        => $this->parseHTMLTagCLOSEsup($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'table'      => $this->parseHTMLTagCLOSEtable($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tablehead'  => $this->parseHTMLTagCLOSEtablehead($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tcpdf'      => $this->parseHTMLTagCLOSEtcpdf($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'td'         => $this->parseHTMLTagCLOSEtd($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'textarea'   => $this->parseHTMLTagCLOSEtextarea($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tfoot'      => $this->parseHTMLTagCLOSEtfoot($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'th'         => $this->parseHTMLTagCLOSEth($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'thead'      => $this->parseHTMLTagCLOSEthead($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tr'         => $this->parseHTMLTagCLOSEtr($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'tt'         => $this->parseHTMLTagCLOSEtt($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'u'          => $this->parseHTMLTagCLOSEu($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        'ul'         => $this->parseHTMLTagCLOSEul($hrc, $key, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                    $capturedByTableCell = $this->captureHTMLTableCellBuffer($hrc, $fragment);
                    $capturedByBlock = false;
                    if (!$capturedByTableCell && ($fragment !== '') && !empty($hrc['blockbuf'])) {
                        $blockidx = \count($hrc['blockbuf']) - 1;
                        /** @var THTMLBlockBuf $blockbuf */
                        $blockbuf = $hrc['blockbuf'][$blockidx];
                        $blockbuf['buffer'] .= $fragment;
                        $hrc['blockbuf'][$blockidx] = $blockbuf;
                        $capturedByBlock = true;
                    }
                    if (!$capturedByTableCell && !$capturedByBlock) {
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
                        $this->resetHTMLCursorAfterPageBreak($hrc, $tpx, $tpy, $tpw);
                    }
                }
            } else { // Text Content
                $hrc['currentkey'] = $key;
                $fragment = $this->parseHTMLText($hrc, $key, $tpx, $tpy, $tpw, $tph, $appendFragment);
                $capturedByTableCell = $this->captureHTMLTableCellBuffer($hrc, $fragment);
                $capturedByBlock = false;
                if (!$capturedByTableCell && ($fragment !== '') && !empty($hrc['blockbuf'])) {
                    $blockidx = \count($hrc['blockbuf']) - 1;
                    /** @var THTMLBlockBuf $blockbuf */
                    $blockbuf = $hrc['blockbuf'][$blockidx];
                    $blockbuf['buffer'] .= $fragment;
                    $hrc['blockbuf'][$blockidx] = $blockbuf;
                    $capturedByBlock = true;
                }
                if (!$capturedByTableCell && !$capturedByBlock) {
                    $appendFragment($fragment);
                }
            }

            ++$key;
        }

        if ($this->hasActiveHTMLFloatRow($hrc, $tpy)) {
            $this->flushHTMLFloatRow($hrc, $tpx, $tpy, $tpw);
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
        $callerfont = $this->captureHTMLCallerFontState();

        $dom = $this->getHTMLDOM($html);

        /** @var THTMLRenderContext $hrc */
        $hrc = [
            'cellctx' => [
                'originx' => 0.0,
                'originy' => 0.0,
                'maxwidth' => 0.0,
                'maxheight' => 0.0,
                'basefont' => '',
            ],
            'fontcache' => [],
            'liststack' => [],
            'tablestack' => [],
            'bcellctx' => [],
            'blockbuf' => [],
            'linkstack' => [],
            'listack' => [],
            'prelevel' => 0,
            'dom' => $dom,
        ];

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

        $this->initHTMLCellContext($hrc, $contentx, $contenty, $contentw, $contenth);
        $this->renderHTMLCellFragments(
            $hrc,
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

        $this->clearHTMLCellContext($hrc);
        $out .= $this->restoreHTMLCallerFontState($callerfont);

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
        $callerfont = $this->captureHTMLCallerFontState();
        $dom = $this->getHTMLDOM($html);
        /** @var THTMLRenderContext $hrc */
        $hrc = [
            'cellctx' => [
                'originx' => 0.0,
                'originy' => 0.0,
                'maxwidth' => 0.0,
                'maxheight' => 0.0,
                'basefont' => '',
            ],
            'fontcache' => [],
            'liststack' => [],
            'tablestack' => [],
            'bcellctx' => [],
            'blockbuf' => [],
            'linkstack' => [],
            'listack' => [],
            'prelevel' => 0,
            'dom' => $dom,
        ];
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

        $this->initHTMLCellContext($hrc, $contentx, $contenty, $contentw, $contenth);
        $this->renderHTMLCellFragments(
            $hrc,
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

        $this->clearHTMLCellContext($hrc);
        $restorefontout = $this->restoreHTMLCallerFontState($callerfont);
        if ($restorefontout !== '') {
            $endpid = $this->page->getPageId();
            if (!isset($outbypage[$endpid])) {
                $outbypage[$endpid] = '';
            }

            $outbypage[$endpid] .= $restorefontout;
        }

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
     * @param THTMLRenderContext $hrc HTML render context.
    * @param-out THTMLRenderContext $hrc HTML render context.
     * @param int $key DOM array key.
     * @param float  $tpx  Abscissa of upper-left corner.
     * @param float  $tpy  Ordinate of upper-left corner.
     * @param float  $tpw  Width.
     * @param float  $tph  Height.
     * @param ?callable(string):void $appendFragment Optional sink used to
     *        emit the partial flush of any open block-level buffers onto the
     *        current page right before a region/page break, so block-level
     *        backgrounds and borders continue across pages.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLText(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
        ?callable $appendFragment = null,
    ): string {
        if (($key < 0) || !isset($hrc['dom'][$key])) {
            return '';
        }

        $elm = $hrc['dom'][$key];
        $text = $this->normalizeHTMLText($hrc, $elm['value'], $key);
        if ($text === '') {
            return '';
        }

        // Apply word-break CSS rules to allow long words to wrap
        $text = $this->applyHTMLWordBreakRules($hrc, $text, $key);
        if ($text === '') {
            return '';
        }

        $whitespaceMode = $this->getHTMLWhiteSpaceMode($hrc, $key);
        if (
            (($whitespaceMode === 'pre-line') || $this->isHTMLPreLikeWhiteSpaceMode($hrc, $key))
            && \str_contains($text, "\n")
        ) {
            $origElm = $hrc['dom'][$key];
            $splitPos = \strpos($text, "\n");
            if ($splitPos !== false) {
                $head = (string) \substr($text, 0, $splitPos);
                $tail = (string) \substr($text, ($splitPos + 1));

                $headOut = '';
                if ($head !== '') {
                    $headElm = $origElm;
                    $headElm['value'] = $head;
                    $hrc['dom'][$key] = $headElm;
                    $headOut = $this->parseHTMLText(
                        $hrc,
                        $key,
                        $tpx,
                        $tpy,
                        $tpw,
                        $tph,
                        $appendFragment,
                    );
                }

                $linebottom = (!empty($hrc['cellctx']['linebottom']) && \is_numeric($hrc['cellctx']['linebottom']))
                    ? (float) $hrc['cellctx']['linebottom']
                    : 0.0;
                $tpy = \max(
                    $tpy + $this->getCurrentHTMLLineAdvance($hrc, $key),
                    $linebottom,
                );
                $this->resetHTMLLineCursor($hrc, $tpx, $tpw);

                $tailOut = '';
                if ($tail !== '') {
                    $tailElm = $origElm;
                    $tailElm['value'] = $tail;
                    $hrc['dom'][$key] = $tailElm;
                    $tailOut = $this->parseHTMLText(
                        $hrc,
                        $key,
                        $tpx,
                        $tpy,
                        $tpw,
                        $tph,
                        $appendFragment,
                    );
                }

                $hrc['dom'][$key] = $origElm;
                return $headOut . $tailOut;
            }
        }

        $style = empty($elm['fontstyle']) || !\is_string($elm['fontstyle']) ? '' : $elm['fontstyle'];
        $forcedir = ($elm['dir'] === 'rtl') ? 'R' : '';
        $halign = empty($elm['align']) ? ($this->rtl ? 'R' : 'L') : (string) $elm['align'];
        $blockOriginX = (float) $hrc['cellctx']['originx'];
        $lineOriginX = $hrc['cellctx']['lineoriginx'];
        if ($tpx <= ($blockOriginX + self::WIDTH_TOLERANCE)) {
            $lineOriginX = $blockOriginX;
            $hrc['cellctx']['lineoriginx'] = $lineOriginX;
        }
        $lineOffset = (float) ($tpx - $lineOriginX);
        $availableWidth = ($hrc['cellctx']['maxwidth'] > 0) ? $hrc['cellctx']['maxwidth'] : $tpw;
        $remainingWidth = ($hrc['cellctx']['maxwidth'] > 0)
            ? \max(0.0, $tpw)
            : (($tpw > 0) ? $tpw : $availableWidth);

        // Apply CSS text-indent once per block on the first visual text line.
        // Positive values create a first-line indent; negative values create a hanging indent.
        if (
            ($lineOffset <= self::WIDTH_TOLERANCE)
            && !empty($elm['text-indent'])
            && \is_numeric($elm['text-indent'])
            && empty($hrc['cellctx']['textindentapplied'])
            && ($availableWidth > 0.0)
        ) {
            $indent = (float) $elm['text-indent'];
            if ($forcedir === 'R') {
                $indent *= -1;
            }

            $rightBoundary = $hrc['cellctx']['originx'] + $availableWidth;
            $lineOriginX += $indent;
            $availableWidth = \max(0.0, $rightBoundary - $lineOriginX);
            $tpx = $lineOriginX;
            $tpw = $availableWidth;
            $lineOffset = 0.0;
            $remainingWidth = $availableWidth;
            $hrc['cellctx']['lineoriginx'] = $lineOriginX;
            $hrc['cellctx']['textindentapplied'] = true;
        }

        // In normal HTML flow, collapsible spaces at line start are ignored.
        // Keeping them would shift the first visible fragment and defeat
        // center/right alignment for wrapped inline runs.
        if (!$this->isHTMLPreLikeWhiteSpaceMode($hrc, $key) && ($lineOffset <= self::WIDTH_TOLERANCE)) {
            if (\trim($text) === '') {
                return '';
            }

            $text = \ltrim($text);
            if ($text === '') {
                return '';
            }
        }

        $currentkey = $key;
        $hrc['currentkey'] = $currentkey;

        $breakoutPrefix = '';
        $out = $this->getHTMLTextPrefix($hrc, $currentkey);

        $curfont = $this->font->getCurrentFont();
        $curAscent = (isset($curfont['ascent']) && \is_numeric($curfont['ascent']))
            ? $this->toUnit((float) $curfont['ascent'])
            : 0.0;
        $curHeight = (isset($curfont['height']) && \is_numeric($curfont['height']))
            ? $this->toUnit((float) $curfont['height'])
            : 0.0;
        $skipAscent = ($lineOffset <= self::WIDTH_TOLERANCE)
            || empty($hrc['cellctx']['lineascent'])
            || !\is_numeric($hrc['cellctx']['lineascent']);
        if ($skipAscent) {
            $lineascent = $this->measureHTMLInlineRunMaxAscent($hrc, $currentkey);
            if ($lineascent <= 0.0) {
                $lineascent = $curAscent;
            }

            $hrc['cellctx']['lineascent'] = $lineascent;
        }

        $lineascent = (float) $hrc['cellctx']['lineascent'];
        if ($lineascent < $curAscent) {
            $lineascent = $curAscent;
            $hrc['cellctx']['lineascent'] = $lineascent;
        }

        $lineAdvance = $this->getHTMLLineAdvance($hrc, $currentkey);

        // Generic page/region overflow guard for plain inline text flow.
        // Skipped while a table cell is active so that table row pagination
        // keeps working through its dedicated path. Also skipped when the
        // cell has an explicit max height: the caller bounded the HTML box
        // and content must stay within it.
        // Block-level buffers (background/border) are split across pages by
        // flushing the partial rectangle onto the current page before the
        // break and updating each buffer's origin to the new region top.
        if (
            empty($hrc['tablestack'])
            && empty($hrc['bcellctx'])
            && ((float) $hrc['cellctx']['maxheight'] <= 0.0)
            && ($lineAdvance > 0.0)
        ) {
            $region = $this->page->getRegion();
            $regiontop = (float) $region['RY'];
            $remaining = $this->getHTMLRemainingHeight($hrc, $tpy);
            $willBreak = ($lineAdvance > ($remaining + self::WIDTH_TOLERANCE))
                && ($tpy > ($regiontop + self::WIDTH_TOLERANCE));

            if ($willBreak && !empty($hrc['blockbuf']) && ($appendFragment !== null)) {
                $flush = $this->flushOpenBlockBuffers($hrc, $tpy);
                if ($flush !== '') {
                    $appendFragment($flush);
                }
            }

            $breakout = $this->breakHTMLIfNeeded($hrc, $lineAdvance, $tpx, $tpy, $tpw, $tph);

            if ($willBreak && !empty($hrc['blockbuf'])) {
                foreach ($hrc['blockbuf'] as $bidx => $blkEntry) {
                    /** @var THTMLBlockBuf $blkEntry */
                    $blkEntry['by'] = $tpy;
                    $hrc['blockbuf'][$bidx] = $blkEntry;
                }
            }

            if ($willBreak && !empty($hrc['tablestack'])) {
                $this->resetHTMLTableStackOnPageBreak($hrc, $tpy);
            }

            $breakoutPrefix = $breakout;
        }

        // Multi-line vertical fit guard: when a wrappable fragment will produce
        // more wrapped lines than fit in the remaining region height, render
        // only the lines that fit, page-break, then process the remainder
        // recursively on the new page region.
        if (
            empty($hrc['tablestack'])
            && empty($hrc['bcellctx'])
            && ((float) $hrc['cellctx']['maxheight'] <= 0.0)
            && ($lineAdvance > 0.0)
            && $this->hasHTMLTextBreakOpportunity($hrc, $key, $text)
        ) {
            $regionMV = $this->page->getRegion();
            $regiontopMV = (float) $regionMV['RY'];
            $remainingMV = $this->getHTMLRemainingHeight($hrc, $tpy);
            $maxFitLines = (int) \floor(($remainingMV + self::WIDTH_TOLERANCE) / $lineAdvance);
            if ($maxFitLines < 1) {
                $maxFitLines = 1;
            }

            $lineOffsetMV = (float) ($tpx - $hrc['cellctx']['originx']);
            if ($lineOffsetMV < 0.0) {
                $lineOffsetMV = 0.0;
            }

            $availableWidthMV = ($hrc['cellctx']['maxwidth'] > 0)
                ? (float) $hrc['cellctx']['maxwidth']
                : (float) $tpw;
            if ($availableWidthMV <= 0.0) {
                $availableWidthMV = (float) $tpw;
            }

            if ($availableWidthMV > 0.0) {
                $probeText = $text;
                $probeOrd = [];
                $probeDim = $this->getHTMLDefaultTextDims();
                $this->prepareHTMLText($probeText, $probeOrd, $probeDim, $forcedir);
                $probeLines = $this->splitLines(
                    $probeOrd,
                    $probeDim,
                    $this->toPoints($availableWidthMV),
                    $this->toPoints(\min($lineOffsetMV, $availableWidthMV)),
                );
                $probeCount = \count($probeLines);

                if (
                    ($probeCount > $maxFitLines)
                    && ($tpy > ($regiontopMV + self::WIDTH_TOLERANCE))
                ) {
                    $cut = 0;
                    for ($i = 0; $i < $maxFitLines; ++$i) {
                        $cut = (int) $probeLines[$i]['pos'] + (int) $probeLines[$i]['chars'];
                    }
                    $probeLen = \mb_strlen($probeText);
                    if (($cut > 0) && ($cut < $probeLen)) {
                        $head = \mb_substr($probeText, 0, $cut);
                        $tail = \mb_substr($probeText, $cut);
                        if (!$this->isHTMLPreLikeWhiteSpaceMode($hrc, $key)) {
                            $tail = \ltrim($tail);
                        }

                        if (($head !== '') && ($tail !== '')) {
                            /** @var THTMLAttrib $origElm */
                            $origElm = $hrc['dom'][$key];
                            $headElm = $origElm;
                            $headElm['value'] = $head;
                            $hrc['dom'][$key] = $headElm;
                            $headOut = $this->parseHTMLText(
                                $hrc,
                                $key,
                                $tpx,
                                $tpy,
                                $tpw,
                                $tph,
                                $appendFragment,
                            );

                            // The HEAD portion belongs to the current (about-to-end)
                            // page and must be dispatched before the page break, using
                            // the same routing the caller applies to fragments
                            // (table-cell capture, block-level buffer, or direct page
                            // append). Mirroring this dispatch here ensures the head
                            // bytes are emitted on the correct page and not carried
                            // over to the new page along with the tail.
                            $headDispatch = $breakoutPrefix . $headOut;
                            $breakoutPrefix = '';
                            if ($headDispatch !== '') {
                                if (!$this->captureHTMLTableCellBuffer($hrc, $headDispatch)) {
                                    if (!empty($hrc['blockbuf'])) {
                                        $blockidxMV = \count($hrc['blockbuf']) - 1;
                                        /** @var THTMLBlockBuf $blockbufMV */
                                        $blockbufMV = $hrc['blockbuf'][$blockidxMV];
                                        $blockbufMV['buffer'] .= $headDispatch;
                                        $hrc['blockbuf'][$blockidxMV] = $blockbufMV;
                                    } elseif ($appendFragment !== null) {
                                        $appendFragment($headDispatch);
                                    }
                                }
                            }

                            if (!empty($hrc['blockbuf']) && ($appendFragment !== null)) {
                                $flush = $this->flushOpenBlockBuffers($hrc, $tpy);
                                if ($flush !== '') {
                                    $appendFragment($flush);
                                }
                            }

                            $forceH = $this->getHTMLRemainingHeight($hrc, $tpy)
                                + $lineAdvance
                                + 1.0;
                            $brk = $this->breakHTMLIfNeeded(
                                $hrc,
                                $forceH,
                                $tpx,
                                $tpy,
                                $tpw,
                                $tph,
                            );

                            if (!empty($hrc['blockbuf'])) {
                                foreach ($hrc['blockbuf'] as $bidx2 => $blkEntry2) {
                                    /** @var THTMLBlockBuf $blkEntry2 */
                                    $blkEntry2['by'] = $tpy;
                                    $hrc['blockbuf'][$bidx2] = $blkEntry2;
                                }
                            }

                            if (!empty($hrc['tablestack'])) {
                                $this->resetHTMLTableStackOnPageBreak($hrc, $tpy);
                            }

                            $tailElm = $origElm;
                            $tailElm['value'] = $tail;
                            $hrc['dom'][$key] = $tailElm;
                            $tailOut = $this->parseHTMLText(
                                $hrc,
                                $key,
                                $tpx,
                                $tpy,
                                $tpw,
                                $tph,
                                $appendFragment,
                            );

                            $hrc['dom'][$key] = $origElm;

                            // HEAD has already been dispatched above onto the
                            // current (now previous) page; only return $brk and
                            // $tailOut, which belong to the new page.
                            return $brk . $tailOut;
                        }
                    }
                }
            }
        }

        $out = $breakoutPrefix . $this->getHTMLTextPrefix($hrc, $currentkey);

        $fragmentWidth = $this->getStringWidth($text);

        // When a continuation fragment's trailing collapsible whitespace is the
        // sole cause of overflow, strip it before the wrap check and rendering.
        // The cursor is advanced by the stripped width so inter-word spacing is
        // preserved for the next fragment.
        $trailSpaceAdvance = 0.0;
        if (
            ($lineOffset > self::WIDTH_TOLERANCE)
            && !$this->isHTMLPreLikeWhiteSpaceMode($hrc, $key)
            && ($fragmentWidth > ($remainingWidth + self::WIDTH_TOLERANCE))
        ) {
            $trailVisMatch = [];
            if (\preg_match('/\s+$/u', $text, $trailVisMatch) === 1) {
                $strippedText = \rtrim($text);
                if ($strippedText !== '') {
                    $visibleWidth = $this->getStringWidth($strippedText);
                    if ($visibleWidth <= ($remainingWidth + self::WIDTH_TOLERANCE)) {
                        $trailSpaceAdvance = $fragmentWidth - $visibleWidth;
                        $text = $strippedText;
                        $fragmentWidth = $visibleWidth;
                    }
                }
            }
        }

        $keepChunkOnLine = $this->canHTMLTextKeepVisibleChunkOnCurrentLine(
            $text,
            $forcedir,
            $remainingWidth,
        );
        $linebottom = (!empty($hrc['cellctx']['linebottom']) && \is_numeric($hrc['cellctx']['linebottom']))
            ? (float) $hrc['cellctx']['linebottom']
            : 0.0;
        $needDeepLinePrewrap = (
            $linebottom > ($tpy + $this->getCurrentHTMLLineAdvance($hrc, $currentkey) + self::WIDTH_TOLERANCE)
        );
        if (
            ($lineOffset > self::WIDTH_TOLERANCE)
            && (\trim($text) !== '')
            && ($fragmentWidth > ($remainingWidth + self::WIDTH_TOLERANCE))
            && (
                ($needDeepLinePrewrap && !$keepChunkOnLine)
                ||
                !$this->hasHTMLTextBreakOpportunity($hrc, $key, $text)
                || (
                    ($fragmentWidth <= ($availableWidth + self::WIDTH_TOLERANCE))
                    && !$keepChunkOnLine
                )
            )
        ) {
            $tpy = \max(
                $tpy + $this->getCurrentHTMLLineAdvance($hrc, $currentkey),
                $linebottom,
            );
            $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
            $lineOffset = 0.0;
            $remainingWidth = $tpw;
            $lineOriginX = $hrc['cellctx']['lineoriginx'];
            $availableWidth = ($tpw > 0)
                ? $tpw
                : (($hrc['cellctx']['maxwidth'] > 0) ? $hrc['cellctx']['maxwidth'] : 0.0);

            // Collapsible spaces must still be removed when we pre-wrap a fragment.
            // Otherwise the new line can start with an artificial indent.
            if (!$this->isHTMLPreLikeWhiteSpaceMode($hrc, $key) && (\preg_match('/^\s*\S+$/u', $text) !== 1)) {
                $text = \ltrim($text);
                if ($text === '') {
                    return '';
                }

                $fragmentWidth = $this->getStringWidth($text);
            }

            // Forced wraps reset the line cursor; recompute per-line metrics using
            // the fresh line state so subsequent wrap detection uses the new line.
            $lineascent = $this->measureHTMLInlineRunMaxAscent($hrc, $currentkey);
            if ($lineascent <= 0.0) {
                $lineascent = $curAscent;
            }

            if ($lineascent < $curAscent) {
                $lineascent = $curAscent;
            }

            $hrc['cellctx']['lineascent'] = $lineascent;
            $lineAdvance = $this->getHTMLLineAdvance($hrc, $currentkey);
        }

        $lineWordSpacing = 0.0;
        $customJustify = false;
        if ($halign === 'J') {
            $runWidth = $this->measureHTMLInlineRunWidth($hrc, $currentkey);
            $hasFollowingInline = ($runWidth > ($fragmentWidth + self::WIDTH_TOLERANCE));
            $hasLineWordSpacing = ((float) $hrc['cellctx']['linewordspacing'] > 0.0);
            $customJustify = ($hasFollowingInline || (($lineOffset > self::WIDTH_TOLERANCE) && $hasLineWordSpacing));

            if ($customJustify) {
                if ($lineOffset <= self::WIDTH_TOLERANCE) {
                    $lineMetrics = $this->measureHTMLInlineLineMetrics($hrc, $currentkey, $availableWidth);
                    if (
                        !empty($lineMetrics['wrapped'])
                        && ((int) ($lineMetrics['spaces'] ?? 0) > 0)
                        && ((float) ($lineMetrics['width'] ?? 0.0) < ($availableWidth - self::WIDTH_TOLERANCE))
                    ) {
                        $lineWordSpacing = ($availableWidth - (float) $lineMetrics['width'])
                            / (int) $lineMetrics['spaces'];

                        $lineMetrics = $this->measureHTMLInlineLineMetrics(
                            $hrc,
                            $currentkey,
                            $availableWidth,
                            $lineWordSpacing,
                        );
                        if (
                            !empty($lineMetrics['wrapped'])
                            && ((int) ($lineMetrics['spaces'] ?? 0) > 0)
                            && ((float) ($lineMetrics['width'] ?? 0.0) < ($availableWidth - self::WIDTH_TOLERANCE))
                        ) {
                            $lineWordSpacing = ($availableWidth - (float) $lineMetrics['width'])
                                / (int) $lineMetrics['spaces'];
                        }
                    }

                    $hrc['cellctx']['linewordspacing'] = $lineWordSpacing;
                } else {
                    $lineWordSpacing = (float) $hrc['cellctx']['linewordspacing'];
                }
            } else {
                $hrc['cellctx']['linewordspacing'] = 0.0;
            }
        }

        $nodeWordSpacing = $this->getHTMLWordSpacing($hrc, $currentkey);
        $effectiveWordSpacing = $customJustify ? $lineWordSpacing : $nodeWordSpacing;
        if (
            !$customJustify
            && ($halign !== 'J')
            && ($effectiveWordSpacing > 0.0)
            && ($fragmentWidth > ($remainingWidth + self::WIDTH_TOLERANCE))
            && ($this->getHTMLWhiteSpaceMode($hrc, $currentkey) !== 'nowrap')
            && $this->hasHTMLTextBreakOpportunity($hrc, $key, $text)
        ) {
            // Avoid wrapped non-justified lines being stretched as justified
            // when CSS word-spacing is present.
            $effectiveWordSpacing = 0.0;
        }

        $renderPosX = $lineOriginX;
        $renderWidth = $availableWidth;
        $renderOffset = $lineOffset;
        $renderAlign = $halign;
        if ($customJustify) {
            $renderAlign = 'L';
        }
        $deferWrapDetection = false;
        if ((($halign === 'C') || ($halign === 'R')) && ($availableWidth > 0.0)) {
            if ($lineOffset > self::WIDTH_TOLERANCE) {
                $renderPosX = $lineOriginX;
                $renderWidth = $availableWidth;
                $renderOffset = $lineOffset;
                // Keep the first continuation chunk adjacent to previous inline text,
                // while preserving center/right alignment on wrapped continuation lines.
                $renderAlign = ($halign === 'R') ? 'r' : 'c';
            } elseif ($fragmentWidth <= ($remainingWidth + self::WIDTH_TOLERANCE)) {
                $lineWidth = $this->measureHTMLInlineLineWidth($hrc, $currentkey, $availableWidth);
                $runWidth = $this->measureHTMLInlineRunWidth($hrc, $currentkey);
                $hasFollowingInline = ($runWidth > ($fragmentWidth + self::WIDTH_TOLERANCE));
                $isLeadingSmall = ($curAscent + self::WIDTH_TOLERANCE < $lineascent);
                $lineWidthCollapsed = (
                    $hasFollowingInline
                    && ($lineWidth <= ($fragmentWidth + self::WIDTH_TOLERANCE))
                );
                $deferWrapDetection = ($hasFollowingInline && $isLeadingSmall);
                if (
                    ($lineWidth > 0.0)
                    && ($lineWidth <= ($availableWidth + self::WIDTH_TOLERANCE))
                    && !$lineWidthCollapsed
                ) {
                    $renderPosX = $lineOriginX + match ($halign) {
                        'R' => \max(0.0, $availableWidth - $lineWidth),
                        default => \max(0.0, ($availableWidth - $lineWidth) / 2),
                    };
                    // Use the measured lineWidth for rendering to avoid rounding-induced wraps.
                    // The lineWidth has been verified to fit within availableWidth + self::WIDTH_TOLERANCE tolerance.
                    $renderWidth = \min($lineWidth, $availableWidth);
                    $renderOffset = 0.0;
                    $renderAlign = 'L';
                } else {
                    // If the full run does not fit, avoid centering only the first fragment.
                    $renderPosX = $lineOriginX;
                    $renderWidth = $remainingWidth;
                    $renderOffset = 0.0;
                    $renderAlign = 'L';
                }
            }
        }

        $trailjustifyadvance = 0.0;
        if ($customJustify && ($lineWordSpacing > 0.0)) {
            $leadmatch = [];
            if (\preg_match('/^ +/u', $text, $leadmatch) === 1) {
                $leadspaces = \strlen($leadmatch[0]);
                if ($leadspaces > 0) {
                    $leadadvance = $this->getStringWidth($leadmatch[0]) + ($lineWordSpacing * $leadspaces);
                    $text = (string) \substr($text, $leadspaces);
                    $renderOffset += $leadadvance;
                }
            }

            $trailmatch = [];
            if (\preg_match('/ +$/u', $text, $trailmatch) === 1) {
                $trailspaces = \strlen($trailmatch[0]);
                if (($trailspaces > 0) && (\trim($text) !== '')) {
                    $trailjustifyadvance = $this->getStringWidth($trailmatch[0]) + ($lineWordSpacing * $trailspaces);
                    $text = (string) \substr($text, 0, -$trailspaces);
                }
            }

            if ($text === '') {
                $tpx = $lineOriginX + $renderOffset + $trailjustifyadvance;
                if ($hrc['cellctx']['maxwidth'] > 0) {
                    $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
                }

                return $out;
            }
        }

        $renderStartX = $renderPosX + $renderOffset;
        $renderStartY = $tpy + ($lineascent - $curAscent);
        $lineSpace = ($lineAdvance > 0.0) ? ($lineAdvance - $curHeight) : 0.0;

        // When trailing collapsible space was stripped, widen the render box by the
        // stripped amount so that splitLines inside getTextCell does not squeeze the
        // visible text at the floating-point boundary.
        if ($trailSpaceAdvance > 0.0) {
            $renderWidth += $trailSpaceAdvance;
        }

        if ($this->getHTMLWhiteSpaceMode($hrc, $currentkey) === 'nowrap') {
            // Disable line wraps for nowrap runs even when they exceed available width.
            $nowrapWidth = $renderOffset + $this->getStringWidth($text) + self::WIDTH_TOLERANCE;
            if ($nowrapWidth > $renderWidth) {
                $renderWidth = $nowrapWidth;
            }
        }

        // Inline width probes may switch the active font while scanning following nodes.
        // Re-sync the active font metric for accurate glyph placement.
        $this->getHTMLFontMetric($hrc, $currentkey);

        $prevSoftHyphen = $this->htmlRenderSoftHyphen;
        $textout = '';
        $actualText = '';
        if ($this->pdfuaMode !== '') {
            $ordarr = [];
            $dim = self::DIM_DEFAULT;
            $this->prepareText($text, $ordarr, $dim, $forcedir);
            $actualText = $this->getActualTextForOrdarr($ordarr);
        }
        $this->htmlRenderSoftHyphen = true;
        try {
            $textout = $this->getTextCell(
                $text,
                $renderPosX,
                $renderStartY,
                $renderWidth,
                0,
                $renderOffset,
                $lineSpace,
                'T',
                $renderAlign,
                static::ZEROCELL, // @phpstan-ignore argument.type
                [],
                (float) $elm['stroke'],
                $effectiveWordSpacing,
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
        } finally {
            $this->htmlRenderSoftHyphen = $prevSoftHyphen;
        }

        if (($textout !== '') && ($this->pdfuaMode !== '')) {
            $textout = $this->tagPdfUaTextContent($textout, $this->page->getPageId(), $actualText);
        }

        $out .= $textout;

        $bbox = $this->getLastBBox();
        $wrapThreshold = \max(self::WIDTH_TOLERANCE, $lineAdvance - self::WIDTH_TOLERANCE);
        $wrapped = ($bbox['y'] - $renderStartY) >= $wrapThreshold;
        if (!$deferWrapDetection) {
            $wrapped = $wrapped || ($bbox['h'] > ($lineAdvance + self::WIDTH_TOLERANCE));
        }

        $decorBgcolor = '';
        $decorPadding = ['T' => 0.0, 'R' => 0.0, 'B' => 0.0, 'L' => 0.0];
        $decorBorder = [];
        $ibMinDecorW = 0.0;
        $inlineBlockEndX = 0.0;
        $isInlineBlockParent = false;
        if (
            empty($elm['tag'])
            && \is_int($elm['parent'])
            && isset($hrc['dom'][$elm['parent']])
        ) {
            $parentElm = $hrc['dom'][$elm['parent']];
            if (!empty($parentElm['tag']) && !empty($parentElm['opening'])) {
                $parentDisplay = \strtolower(\trim((string) $parentElm['display']));
                $isInlineBlockParent = ($parentDisplay === 'inline-block');

                $grandParentElm = null;
                if (isset($hrc['dom'][$parentElm['parent']])) {
                    $grandParentElm = $hrc['dom'][$parentElm['parent']];
                }

                $hasInlineDecoration = false;
                if (!empty($parentElm['bgcolor']) && \is_string($parentElm['bgcolor'])) {
                    $hasInlineDecoration = true;
                }
                if (!empty($parentElm['padding']) && \is_array($parentElm['padding'])) {
                    foreach (['T', 'R', 'B', 'L'] as $side) {
                        if (
                            isset($parentElm['padding'][$side])
                            && \is_numeric($parentElm['padding'][$side])
                            && ((float) $parentElm['padding'][$side] > 0.0)
                        ) {
                            $hasInlineDecoration = true;
                            break;
                        }
                    }
                }
                if (!empty($parentElm['border']) && \is_array($parentElm['border'])) {
                    $hasInlineDecoration = true;
                }

                $hasOwnBg = false;
                if (!empty($parentElm['bgcolor']) && \is_string($parentElm['bgcolor'])) {
                    $hasOwnBg = (
                        ($grandParentElm === null)
                        || empty($grandParentElm['bgcolor'])
                        || ($grandParentElm['bgcolor'] !== $parentElm['bgcolor'])
                    );
                }

                $hasOwnPadding = false;
                if (!empty($parentElm['padding']) && \is_array($parentElm['padding'])) {
                    foreach (['T', 'R', 'B', 'L'] as $side) {
                        $parentPad = (isset($parentElm['padding'][$side]) && \is_numeric($parentElm['padding'][$side]))
                            ? (float) $parentElm['padding'][$side]
                            : 0.0;
                        $grandPad = (
                            ($grandParentElm !== null)
                            && !empty($grandParentElm['padding'])
                            && \is_array($grandParentElm['padding'])
                            && isset($grandParentElm['padding'][$side])
                            && \is_numeric($grandParentElm['padding'][$side])
                        )
                            ? (float) $grandParentElm['padding'][$side]
                            : 0.0;

                        if ($parentPad > ($grandPad + self::WIDTH_TOLERANCE)) {
                            $hasOwnPadding = true;
                            break;
                        }
                    }
                }

                $hasOwnBorder = false;
                if (!empty($parentElm['border']) && \is_array($parentElm['border'])) {
                    $hasOwnBorder = (
                        ($grandParentElm === null)
                        || empty($grandParentElm['border'])
                        || !\is_array($grandParentElm['border'])
                        || ($grandParentElm['border'] !== $parentElm['border'])
                    );
                }

                // For pure inline elements, only apply decorations if they are not inherited
                if (
                    ($parentDisplay === 'inline')
                    && $hasInlineDecoration
                    && ($hasOwnBg || $hasOwnPadding || $hasOwnBorder)
                ) {
                    if (!empty($parentElm['bgcolor']) && \is_string($parentElm['bgcolor'])) {
                        $decorBgcolor = (string) $parentElm['bgcolor'];
                    }
                    if (!empty($parentElm['padding']) && \is_array($parentElm['padding'])) {
                        foreach (['T', 'R', 'B', 'L'] as $side) {
                            if (isset($parentElm['padding'][$side]) && \is_numeric($parentElm['padding'][$side])) {
                                $decorPadding[$side] = (float) $parentElm['padding'][$side];
                            }
                        }
                    }
                    if ($hasOwnBorder && !empty($parentElm['border']) && \is_array($parentElm['border'])) {
                        $decorBorder = $parentElm['border'];
                    }
                }

                // For inline-block elements, always apply decorations directly
                if ($isInlineBlockParent && $hasInlineDecoration) {
                    if (!empty($parentElm['bgcolor']) && \is_string($parentElm['bgcolor'])) {
                        $decorBgcolor = (string) $parentElm['bgcolor'];
                    }
                    if (!empty($parentElm['padding']) && \is_array($parentElm['padding'])) {
                        foreach (['T', 'R', 'B', 'L'] as $side) {
                            if (isset($parentElm['padding'][$side]) && \is_numeric($parentElm['padding'][$side])) {
                                $decorPadding[$side] = (float) $parentElm['padding'][$side];
                            }
                        }
                    }
                    if (
                        !empty($parentElm['border'])
                        && \is_array($parentElm['border'])
                        && !empty($parentElm['border']['LTRB'])
                    ) {
                        $decorBorder = $parentElm['border'];
                    }
                }

                if ($isInlineBlockParent && !empty($parentElm['width']) && \is_numeric($parentElm['width'])) {
                    $inlineWidth = (float) $parentElm['width'];
                    if ($inlineWidth > 0.0) {
                        $borderLeft = 0.0;
                        $borderRight = 0.0;
                        if (!empty($parentElm['border']) && \is_array($parentElm['border'])) {
                            if (!empty($parentElm['border']['L']) && \is_array($parentElm['border']['L'])) {
                                $borderLeft = (float) $parentElm['border']['L']['lineWidth'];
                            } elseif (!empty($parentElm['border']['LTRB']) && \is_array($parentElm['border']['LTRB'])) {
                                $borderLeft = (float) $parentElm['border']['LTRB']['lineWidth'];
                            }

                            if (!empty($parentElm['border']['R']) && \is_array($parentElm['border']['R'])) {
                                $borderRight = (float) $parentElm['border']['R']['lineWidth'];
                            } elseif (!empty($parentElm['border']['LTRB']) && \is_array($parentElm['border']['LTRB'])) {
                                $borderRight = (float) $parentElm['border']['LTRB']['lineWidth'];
                            }
                        }

                        $ibMinDecorW = $inlineWidth
                            + (float) $decorPadding['L']
                            + (float) $decorPadding['R']
                            + $borderLeft
                            + $borderRight;
                    }
                }
            }
        }

        $decorRectX = $bbox['x'] - (float) $decorPadding['L'];
        $decorRectY = $bbox['y'] - (float) $decorPadding['T'];
        $decorRectW = $bbox['w'] + (float) $decorPadding['L'] + (float) $decorPadding['R'];
        $decorRectH = $bbox['h'] + (float) $decorPadding['T'] + (float) $decorPadding['B'];
        if (($ibMinDecorW > 0.0) && ($decorRectW < $ibMinDecorW)) {
            $decorRectW = $ibMinDecorW;
        }
        if ($ibMinDecorW > 0.0) {
            $inlineBlockEndX = $decorRectX + $decorRectW;
        }

        $background = '';
        if (($decorBgcolor !== '') && ($decorRectW > 0.0) && ($decorRectH > 0.0)) {
            $bgx = $bbox['x'];
            $bgw = $bbox['w'];
            $bgy = $bbox['y'];
            $bgh = $bbox['h'];
            $fillstyle = $this->getHTMLFillStyle($decorBgcolor);
            $hasBlockBgAncestor = $this->hasBlockLvBgAncestor($hrc, $currentkey);

            if ($hasBlockBgAncestor) {
                // Block-level backgrounds (for example td/div) are line-wide.
                // Draw them once at line start, otherwise later inline fragments
                // repaint over already-rendered text on the same line.
                if ($lineOffset > self::WIDTH_TOLERANCE) {
                    $hasBlockBgAncestor = false;
                }
            }

            if ($hasBlockBgAncestor) {
                $bgx = $lineOriginX;
                $bgw = $availableWidth;
            }

            if ($wrapped && !$hasBlockBgAncestor) {
                $lineheight = \max($lineAdvance, self::WIDTH_TOLERANCE);
                $renderEndY = $bbox['y'] + $bbox['h'];
                $lineSpan = \max(0.0, $renderEndY - $renderStartY);
                $lineCount = \max(2, (int) \ceil(($lineSpan - self::WIDTH_TOLERANCE) / $lineheight));
                $firstWidth = \max(0.0, $renderWidth - $renderOffset);

                $segments = [];
                if (($firstWidth > 0.0) && ($lineSpan > 0.0)) {
                    $segments[] = [
                        'x' => $renderStartX,
                        'y' => $renderStartY,
                        'w' => $firstWidth,
                        'h' => \min($lineheight, $lineSpan),
                    ];
                }

                if ($lineCount > 2) {
                    $middleH = ($lineCount - 2) * $lineheight;
                    if (($middleH > 0.0) && ($availableWidth > 0.0)) {
                        $segments[] = [
                            'x' => $lineOriginX,
                            'y' => $renderStartY + $lineheight,
                            'w' => $availableWidth,
                            'h' => $middleH,
                        ];
                    }
                }

                $lastY = \max($bbox['y'], $renderStartY + (($lineCount - 1) * $lineheight));
                $lastH = $renderEndY - $lastY;
                if ($lastH <= 0.0) {
                    $lastY = $bbox['y'];
                    $lastH = $bbox['h'];
                }
                if (($bbox['w'] > 0.0) && ($lastH > 0.0)) {
                    $segments[] = [
                        'x' => $bbox['x'],
                        'y' => $lastY,
                        'w' => $bbox['w'],
                        'h' => $lastH,
                    ];
                }

                $bgout = '';
                foreach ($segments as $segment) {
                    $segx = (float) $segment['x'];
                    $segy = (float) $segment['y'];
                    $segw = (float) $segment['w'];
                    $segh = (float) $segment['h'];
                    if (($segw <= 0.0) || ($segh <= 0.0) || ($segx < 0.0)) {
                        continue;
                    }

                    $bgout .= $this->graph->getBasicRect(
                        $segx,
                        $segy,
                        $segw,
                        $segh,
                        'f',
                        $fillstyle,
                    );
                }

                if ($bgout !== '') {
                    $background = $this->graph->getStartTransform()
                        . $bgout
                        . $this->graph->getStopTransform();
                }
            }

            if (($background === '') && ($bgw > 0.0) && ($bgx >= 0.0)) {
                $background = $this->graph->getStartTransform()
                    . $this->graph->getBasicRect(
                        $bgx,
                        $bgy,
                        $bgw,
                        $bgh,
                        'f',
                        $fillstyle,
                    )
                    . $this->graph->getStopTransform();
            }
        }

        $inlineBorder = '';
        if (
            !empty($decorBorder['LTRB'])
            && \is_array($decorBorder['LTRB'])
            && ($decorRectW > 0.0)
            && ($decorRectH > 0.0)
        ) {
            // For inline-block elements, always render the border (don't check for block ancestor bg)
            // For pure inline elements, check if block ancestor has same bgcolor
            $shouldRenderBorder = $isInlineBlockParent || !$this->hasBlockLvBgAncestor($hrc, $currentkey);
            if ($shouldRenderBorder) {
                $inlineBorder = $this->graph->getStartTransform()
                    . $this->graph->getBasicRect(
                        $decorRectX,
                        $decorRectY,
                        $decorRectW,
                        $decorRectH,
                        'D',
                        $decorBorder['LTRB'],
                    )
                    . $this->graph->getStopTransform();
            }
        }

        $link = $this->getCurrentHTMLLink($hrc);
        if (($link !== '') && ($bbox['w'] > 0.0) && ($bbox['h'] > 0.0)) {
            $lnkid = $this->setLink($bbox['x'], $bbox['y'], $bbox['w'], $bbox['h'], $link);
            $this->page->addAnnotRef($lnkid, $this->page->getPageID());
            if (($this->pdfuaMode !== '') && ($lnkid > 0)) {
                $this->registerPdfUaAnnotation($lnkid, $this->page->getPageID());
            }
        }

        if ($wrapped) {
            // Re-anchor the line cursor to the last visual line produced by the
            // multi-line getTextCell render, so that immediately following inline
            // content (for example "(" + <em>...</em>) keeps flowing on the same
            // last line instead of being pushed to a fresh empty line.
            $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
            $tpy = (float) $bbox['y'];
            $tpx = (float) $bbox['x'] + (float) $bbox['w'] + $trailjustifyadvance + $trailSpaceAdvance;
            if ($inlineBlockEndX > 0.0) {
                $tpx = \max($tpx, $inlineBlockEndX);
            }
            if ($effectiveWordSpacing > 0.0) {
                $fragmentSpaces = $this->getHTMLTextFirstLineSpaces(
                    $text,
                    $forcedir,
                    \max(0.0, $renderWidth - $renderOffset),
                );
                if ($fragmentSpaces > 0) {
                    $tpx += $effectiveWordSpacing * $fragmentSpaces;
                }
            }
            $this->updateHTMLLineAdvance($hrc, $lineAdvance);
            $hrc['cellctx']['linebottom'] = (float) $bbox['y'] + (float) $bbox['h'];
            if ($hrc['cellctx']['maxwidth'] > 0) {
                $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
            }
            $hrc['cellctx']['linewrapped'] = true;

            return $background . $out . $inlineBorder;
        }

        $tpx = $bbox['x'] + $bbox['w'] + $trailjustifyadvance + $trailSpaceAdvance;
        if ($inlineBlockEndX > 0.0) {
            $tpx = \max($tpx, $inlineBlockEndX);
        }
        if ($effectiveWordSpacing > 0.0) {
            $fragmentSpaces = $this->getHTMLTextFirstLineSpaces(
                $text,
                $forcedir,
                \max(0.0, $renderWidth - $renderOffset),
            );
            if ($fragmentSpaces > 0) {
                $tpx += $effectiveWordSpacing * $fragmentSpaces;
            }
        }
        $this->updateHTMLLineAdvance($hrc, $lineAdvance);
        $linebottom = (float) $bbox['y'] + (float) $bbox['h'];
        if (
            empty($hrc['cellctx']['linebottom'])
            || !\is_numeric($hrc['cellctx']['linebottom'])
            || ($linebottom > (float) $hrc['cellctx']['linebottom'])
        ) {
            $hrc['cellctx']['linebottom'] = $linebottom;
        }

        if ($hrc['cellctx']['maxwidth'] > 0) {
            $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
        }
        $hrc['cellctx']['linewrapped'] = false;

        return $background . $out . $inlineBorder;
    }

    // FUNCTIONS TO PROCESS HTML OPENING TAGS

    /**
     * Placeholder for opening-tag handlers.
     */

    /**
     * Process HTML opening tag <a>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENa(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        $href = (!empty($elm['attribute']['href']) && \is_string($elm['attribute']['href']))
            ? $elm['attribute']['href']
            : '';

        if ($this->pdfuaMode !== '' && ($href !== '')) {
            $this->beginStructElem('Link', $this->page->getPageId());
        }

        $this->pushHTMLLink($hrc, $href);

        return '';
    }

    /**
     * Process HTML opening tag <b>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENb(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <blockquote>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENblockquote(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <body>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENbody(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <br>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENbr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        if ($this->shouldSkipHTMLBrAdvance($hrc, $key, $tpx)) {
            $hrc['cellctx']['linewrapped'] = false;
            return '';
        }

        $this->moveHTMLToNextLine($hrc, $key, $tpx, $tpy, $tpw);
        return '';
    }

    /**
     * Process HTML opening tag <caption>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENcaption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <col>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENcol(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <colgroup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENcolgroup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <dd>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdd(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
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
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdel(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <figure>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENfigure(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <figcaption>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENfigcaption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <div>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdiv(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <dl>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdl(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <dt>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENdt(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <em>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENem(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <font>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENfont(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <form>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENform(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <h1>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh1(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <h2>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh2(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h3>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh3(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h4>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh4(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h5>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh5(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <h6>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENh6(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <hr>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENhr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        unset($tph);
        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        $availableWidth = ($tpw > 0) ? $tpw : $hrc['cellctx']['maxwidth'];
        $width = $availableWidth;
        if (!empty($elm['width']) && \is_numeric($elm['width']) && (float) $elm['width'] > 0) {
            $width = \min((float) $elm['width'], $availableWidth);
        }

        $strokeWidth = ((float) $elm['stroke'] > 0) ? (float) $elm['stroke'] : 0.2;
        if (!empty($elm['height']) && \is_numeric($elm['height']) && (float) $elm['height'] > 0) {
            $strokeWidth = (float) $elm['height'];
        }

        $lineY = $tpy + ($this->getHTMLLineAdvance($hrc, $key) / 2);
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
        $this->moveHTMLToNextLine($hrc, $key, $tpx, $tpy, $tpw);

        return $out;
    }

    /**
     * Process HTML opening tag <i>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENi(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <img>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENimg(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->renderHTMLImage($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <input>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENinput(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
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

        $name = (isset($attr['name']) && \is_string($attr['name']))
            ? $attr['name'] : ('input_' . \count($this->tagvspaces));
        $lineheight = $this->getHTMLLineAdvance($hrc, $key);
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $lineheight * 5;
        $fieldlabel = $this->getHTMLLabelTextForControl($hrc, $key);
        $fieldjsp = $this->getHTMLFormFieldJSProperties($attr, $type);

        switch ($type) {
            case 'checkbox':
                $onvalue = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : 'Yes';
                $checked = isset($attr['checked']);
                $opt = ['subtype' => 'Widget'];
                if ($fieldlabel !== '') {
                    $opt['tu'] = $fieldlabel;
                }
                $objid = $this->addFFCheckBox($name, $tpx, $tpy, $lineheight, $onvalue, $checked, $opt, $fieldjsp);
                $this->page->addAnnotRef($objid, $this->page->getPageID());
                $tpx += $lineheight;
                break;

            case 'radio':
                $onvalue = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : 'On';
                $checked = isset($attr['checked']);
                $opt = ['subtype' => 'Widget'];
                if ($fieldlabel !== '') {
                    $opt['tu'] = $fieldlabel;
                }
                $objid = $this->addFFRadioButton($name, $tpx, $tpy, $lineheight, $onvalue, $checked, $opt, $fieldjsp);
                $this->page->addAnnotRef($objid, $this->page->getPageID());
                $tpx += $lineheight;
                break;

            case 'submit':
            case 'button':
            case 'reset':
                $caption = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : $type;
                $action = $this->getHTMLInputButtonAction($hrc, $key, $type, $attr);
                $opt = ['subtype' => 'Widget'];
                if ($fieldlabel !== '') {
                    $opt['tu'] = $fieldlabel;
                }
                $objid = $this->addFFButton(
                    $name,
                    $tpx,
                    $tpy,
                    $fieldwidth,
                    $lineheight,
                    $caption,
                    $action,
                    $opt,
                    $fieldjsp,
                );
                $this->page->addAnnotRef($objid, $this->page->getPageID());
                $tpx += $fieldwidth;
                break;

            case 'file':
                $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
                $opt = ['v' => $value];
                if ($fieldlabel !== '') {
                    $opt['tu'] = $fieldlabel;
                }
                $filejsp = \array_merge($fieldjsp, ['fileSelect' => 'true']);
                $objid = $this->addFFText($name, $tpx, $tpy, $fieldwidth, $lineheight, $opt, $filejsp);
                $this->page->addAnnotRef($objid, $this->page->getPageID());
                $tpx += $fieldwidth;
                break;

            default:
                // text, password, email, url, number, etc.
                $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
                if ($value === '' && isset($attr['placeholder']) && \is_string($attr['placeholder'])) {
                    $value = $attr['placeholder'];
                }

                $opt = ['v' => $value];
                if ($fieldlabel !== '') {
                    $opt['tu'] = $fieldlabel;
                }
                $jsp = $fieldjsp;
                if ($type === 'password') {
                    $jsp['password'] = 'true';
                }
                $objid = $this->addFFText($name, $tpx, $tpy, $fieldwidth, $lineheight, $opt, $jsp);
                $this->page->addAnnotRef($objid, $this->page->getPageID());
                $tpx += $fieldwidth;
                break;
        }

        if ($hrc['cellctx']['maxwidth'] > 0) {
            $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - $tpx + $hrc['cellctx']['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <label>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENlabel(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <li>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENli(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        $listItemOriginX = (isset($hrc['cellctx']['originx']) && \is_numeric($hrc['cellctx']['originx']))
            ? (float) $hrc['cellctx']['originx']
            : 0.0;
        $listItemMaxWidth = (isset($hrc['cellctx']['maxwidth']) && \is_numeric($hrc['cellctx']['maxwidth']))
            ? (float) $hrc['cellctx']['maxwidth']
            : 0.0;
        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        $depth = $this->getHTMLListDepth($hrc);
        if ($depth < 1) {
            if ($this->pdfuaMode !== '') {
                $this->beginStructElem('LBody', $this->page->getPageId());
            }

            return $out;
        }

        $font = $this->getHTMLFontMetric($hrc, $key);

        // When the parent <ul>/<ol> carries an explicit CSS padding/margin override,
        // openHTMLBlock() already consumed those values by shifting hrc['cellctx']['originx']
        // (and therefore tpx) into the element's content box.  Adding the same amount here
        // again would double-count the shift and push bullet+text too far right.
        // Use the default gutter width only when no CSS override was applied.
        $depthidx = $depth - 1;
        $hasCSSIndentOverride = isset($hrc['liststack'][$depthidx]['indent'])
            && \is_numeric($hrc['liststack'][$depthidx]['indent'])
            && ((float) $hrc['liststack'][$depthidx]['indent'] > 0.0);
        $indent = $hasCSSIndentOverride ? 0.0 : $this->getHTMLListIndentWidth();

        // IMPORTANT: Do NOT add liIndent here. When the <li> element carries CSS padding/margin,
        // openHTMLBlock() already consumed those values by shifting tpx. For 'outside' markers
        // the bullet must not be pushed into the li's content box by li padding-left; that
        // correction is applied below when computing $bulletx.

        $counter = $this->getHTMLListItemCounter($hrc, $key);
        $markerType = $this->getCurrentHTMLListMarkerType($hrc);
        $markerPosition = 'outside';
        $insideTextOffset = 0.0;
        if (
            isset($hrc['dom'][$key]['list-style-position'])
            && \is_string($hrc['dom'][$key]['list-style-position'])
            && ($hrc['dom'][$key]['list-style-position'] === 'inside')
        ) {
            $markerPosition = 'inside';
            // Browser-like inside markers reserve inline marker space before item text.
            $insideTextOffset = $this->getStringWidth('0 ');
        }

        $fontAscent = (isset($font['ascent']) && \is_numeric($font['ascent'])) ? (float) $font['ascent'] : 0.0;
        $baseline = $tpy + $this->toUnit($fontAscent);
        // For 'outside' markers the bullet hangs in the li's padding area (between the
        // list's content edge and the li's content edge). openHTMLBlock() already shifted
        // tpx by li margin+padding into the li's content box, so subtract the li's own
        // padding-left to re-anchor the marker at the li's border-box left edge instead
        // of pushing it further right into the content area.
        $liPaddingL = ($markerPosition === 'outside'
            && isset($hrc['dom'][$key]['padding']['L'])
            && \is_numeric($hrc['dom'][$key]['padding']['L']))
            ? (float) $hrc['dom'][$key]['padding']['L']
            : 0.0;
        $bulletx = ($tpx - $liPaddingL) + $indent + $insideTextOffset;

        // Get marker styles from li::marker selector
        $markerStyles = [];
        if (
            isset($hrc['dom'][$key]['attribute']['pseudo-marker-style'])
            && \is_array($hrc['dom'][$key]['attribute']['pseudo-marker-style'])
        ) {
            $markerStyles = $hrc['dom'][$key]['attribute']['pseudo-marker-style'];
        }

        $out .= $this->getHTMLTextPrefix($hrc, $key);
        if ($this->pdfuaMode !== '') {
            $this->beginStructElem('Lbl', $this->page->getPageId());
        }

        $bulletOut = $this->getHTMLliBullet($depth, $counter, $bulletx, $baseline, $markerType, $markerStyles);
        if ($this->pdfuaMode !== '') {
            $bulletOut = $this->tagPdfUaTextContent($bulletOut, $this->page->getPageId(), '');
            $this->endStructElem();
            $this->beginStructElem('LBody', $this->page->getPageId());
        }

        $out .= $bulletOut;

        // Mark the current list-item line as occupied by the marker so that
        // an immediate nested block (<ul>/<ol>/etc.) starts on the next line
        // instead of being rendered inline with the parent list item marker.
        $this->updateHTMLLineAdvance($hrc, $this->getHTMLLineAdvance($hrc, $key));

        $hrc['listack'][] = [
            'originx' => $listItemOriginX,
            'maxwidth' => $listItemMaxWidth,
        ];

        $tpx += $indent + $insideTextOffset;
        if ($markerPosition === 'outside') {
            if ($tpw > 0) {
                $tpw = \max(0.0, $tpw - $indent);
            }

            $hrc['cellctx']['originx'] = $tpx;
            $hrc['cellctx']['maxwidth'] = $tpw;
            $hrc['cellctx']['lineoriginx'] = $tpx;
        }

        return $out;
    }

    /**
     * Process HTML opening tag <marker>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENmarker(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <ol>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENol(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);

        $parentIsLi = isset($hrc['dom'][$key]['parent'])
            && \is_int($hrc['dom'][$key]['parent'])
            && ($hrc['dom'][$key]['parent'] >= 0)
            && isset($hrc['dom'][$hrc['dom'][$key]['parent']]['value'])
            && ($hrc['dom'][$hrc['dom'][$key]['parent']]['value'] === 'li');
        $inheritsListStyle = isset($hrc['dom'][$key]['style']['list-style'])
            && \is_string($hrc['dom'][$key]['style']['list-style'])
            && (\strtolower(\trim((string) $hrc['dom'][$key]['style']['list-style'])) === 'inherit');
        $inheritsLStyType = isset($hrc['dom'][$key]['style']['list-style-type'])
            && \is_string($hrc['dom'][$key]['style']['list-style-type'])
            && (\strtolower(\trim((string) $hrc['dom'][$key]['style']['list-style-type'])) === 'inherit');
        $lineAdvanceCtx = (isset($hrc['cellctx']['lineadvance']) && \is_numeric($hrc['cellctx']['lineadvance']))
            ? (float) $hrc['cellctx']['lineadvance']
            : 0.0;
        if (
            $parentIsLi
            && ($inheritsListStyle || $inheritsLStyType)
            && (
                ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE))
                || ($lineAdvanceCtx > self::WIDTH_TOLERANCE)
            )
        ) {
            $baseOriginX = (float) ($hrc['cellctx']['originx'] ?? 0.0);
            $lineStartX = $tpx;
            $this->moveHTMLToNextLine($hrc, $key, $tpx, $tpy, $tpw);
            // Nested lists inside <li> should continue from the current list-item
            // text column (browser-like), not from the parent list block origin.
            if ($lineStartX > ($baseOriginX + self::WIDTH_TOLERANCE)) {
                $delta = $lineStartX - $baseOriginX;
                $hrc['cellctx']['originx'] = $lineStartX;
                $hrc['cellctx']['maxwidth'] = \max(0.0, $hrc['cellctx']['maxwidth'] - $delta);
                $tpx = $lineStartX;
                if ($hrc['cellctx']['maxwidth'] > 0) {
                    $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
                }
            }
        }

        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        $this->pushHTMLList($hrc, $key, true);

        return $out;
    }

    /**
     * Process HTML opening tag <optgroup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENoptgroup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <option>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENoption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        $label = '';
        if (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value'])) {
            $label = $elm['attribute']['value'];
        }

        return $this->renderHTMLLiteralText($hrc, $key, $label, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <output>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENoutput(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        $label = '';
        if (!empty($elm['attribute']['value']) && \is_string($elm['attribute']['value'])) {
            $label = $elm['attribute']['value'];
        }

        return $this->renderHTMLLiteralText($hrc, $key, $label, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <p>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENp(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <pre>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENpre(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        ++$hrc['prelevel'];
        return $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML opening tag <s>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENs(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <select>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENselect(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        unset($tph);
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $name = (isset($attr['name']) && \is_string($attr['name']))
            ? $attr['name'] : ('select_' . \count($this->tagvspaces));
        $lineheight = $this->getHTMLLineAdvance($hrc, $key);
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $lineheight * 5;
        $fieldlabel = $this->getHTMLLabelTextForControl($hrc, $key);
        $fieldjsp = $this->getHTMLFormFieldJSProperties($attr, 'select');

        // Parse packed option string into [value, label] pairs and selected entries.
        $values = [];
        $optionValues = [];
        $explicitSelVals = [];
        $optionSelectedValues = [];
        if (isset($attr['value']) && \is_string($attr['value']) && ($attr['value'] !== '')) {
            foreach (\explode(',', $attr['value']) as $selval) {
                $selval = \trim($selval);
                if ($selval !== '') {
                    $explicitSelVals[] = $selval;
                }
            }
        }
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
                    $optionValues[] = $parts[0];
                    if ($isSelected && !\in_array($parts[0], $optionSelectedValues, true)) {
                        $optionSelectedValues[] = $parts[0];
                    }
                } else {
                    $values[] = [$entry, $entry];
                    $optionValues[] = $entry;
                    if ($isSelected && !\in_array($entry, $optionSelectedValues, true)) {
                        $optionSelectedValues[] = $entry;
                    }
                }
            }
        }

        $size = (isset($attr['size']) && \is_numeric($attr['size'])) ? (int) $attr['size'] : 0;
        $hasMultiple = $this->isHTMLBooleanAttributeEnabled($attr, 'multiple');
        $isListBox = $hasMultiple || ($size > 1);

        $selectedValues = [];
        foreach ($optionSelectedValues as $selectedOptionValue) {
            if (\in_array($selectedOptionValue, $optionValues, true)) {
                if (!\in_array($selectedOptionValue, $selectedValues, true)) {
                    $selectedValues[] = $selectedOptionValue;
                }
            }
        }
        if ($selectedValues === []) {
            foreach ($explicitSelVals as $explicitValue) {
                if (\in_array($explicitValue, $optionValues, true)) {
                    if (!\in_array($explicitValue, $selectedValues, true)) {
                        $selectedValues[] = $explicitValue;
                    }
                }
            }
        }
        if (($selectedValues === []) && ($optionValues !== [])) {
            $selectedValues[] = $optionValues[0];
        }
        $selectedValue = $selectedValues[0] ?? '';

        if ($isListBox) {
            $fieldheight = $lineheight * (float) \max(1, $size);
            $jsp = $fieldjsp;
            if ($hasMultiple) {
                $jsp['multipleSelection'] = 'true';
            }

            $opt = ['subtype' => 'Widget'];
            if ($fieldlabel !== '') {
                $opt['tu'] = $fieldlabel;
            }
            if ($selectedValue !== '') {
                $opt['v'] = $selectedValue;
            }
            $selectedIndices = [];
            foreach ($selectedValues as $selectedEntry) {
                $idx = \array_search($selectedEntry, $optionValues, true);
                if (!\is_int($idx) || \in_array($idx, $selectedIndices, true)) {
                    continue;
                }

                $selectedIndices[] = $idx;
            }
            if ($selectedIndices !== []) {
                $opt['i'] = $hasMultiple ? $selectedIndices : [$selectedIndices[0]];
            }

            /** @var TAnnotOpts $opt */
            $objid = $this->addFFListBox(
                $name,
                $tpx,
                $tpy,
                $fieldwidth,
                $fieldheight,
                $values,
                $opt,
                $jsp,
            );
        } else {
            $opt = ['v' => $selectedValue];
            if ($fieldlabel !== '') {
                $opt['tu'] = $fieldlabel;
            }
            $objid = $this->addFFComboBox($name, $tpx, $tpy, $fieldwidth, $lineheight, $values, $opt, $fieldjsp);
        }
        $this->page->addAnnotRef($objid, $this->page->getPageID());
        $tpx += $fieldwidth;

        if ($hrc['cellctx']['maxwidth'] > 0) {
            $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - $tpx + $hrc['cellctx']['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <small>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsmall(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <span>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENspan(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tpx, $tpy, $tpw, $tph);
        $elm = &$hrc['dom'][$key];

        if (!empty($elm['attribute']['color']) && \is_string($elm['attribute']['color'])) {
            $fgcolor = $this->getCSSColor($elm['attribute']['color']);
            if ($fgcolor !== '') {
                $elm['fgcolor'] = $fgcolor;
            }
        }

        if (!empty($elm['attribute']['bgcolor']) && \is_string($elm['attribute']['bgcolor'])) {
            $bgcolor = $this->getCSSColor($elm['attribute']['bgcolor']);
            if ($bgcolor !== '') {
                $elm['bgcolor'] = $bgcolor;
            }
        }

        return '';
    }

    /**
     * Process HTML opening tag <strike>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENstrike(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <strong>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENstrong(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <sub>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsub(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($hrc, $key, $tpy, self::VERT_SHIFT_SUB);
    }

    /**
     * Process HTML opening tag <sup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENsup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($hrc, $key, $tpy, -self::VERT_SHIFT_SUP);
    }

    /**
     * Process HTML opening tag <table>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtable(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        unset($tph);

        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        $width = ($tpw > 0) ? $tpw : $hrc['cellctx']['maxwidth'];
        $rawTableWidth = '';
        if (!empty($elm['style']['width']) && \is_string($elm['style']['width'])) {
            $rawTableWidth = \trim($elm['style']['width']);
        } elseif (!empty($elm['attribute']['width']) && \is_string($elm['attribute']['width'])) {
            $rawTableWidth = \trim($elm['attribute']['width']);
        }

        if (
            ($rawTableWidth !== '')
            && (\preg_match('/^([0-9.+\-]+)\s*%$/', $rawTableWidth, $pctMatch) === 1)
        ) {
            $ctxWidth = $width;
            if (isset($elm['ctxmaxwidth']) && \is_numeric($elm['ctxmaxwidth']) && ((float) $elm['ctxmaxwidth'] > 0.0)) {
                $ctxWidth = (float) $elm['ctxmaxwidth'];
            }

            $width = \max(0.0, ($ctxWidth * (float) $pctMatch[1]) / 100.0);
        }

        $cols = (!empty($elm['cols']) && \is_numeric($elm['cols'])) ? \max(1, (int) $elm['cols']) : 1;
        $colwidth = ($cols > 0) ? ($width / $cols) : $width;

        // Consume pre-computed column widths and spacing stored on the DOM node.
        $colwidths = (isset($elm['pendingcolwidths']) && \is_array($elm['pendingcolwidths']))
            ? $elm['pendingcolwidths'] : [];
        $cellspacingh = $this->getHTMLTableCellSpacingH($elm);
        $cellspacingv = $this->getHTMLTableCellSpacingV($elm);
        $cellpadding = (isset($elm['pendingcellpadding']) && \is_numeric($elm['pendingcellpadding']))
            ? (float) $elm['pendingcellpadding'] : 0.0;
        $availableWidth = \max(0.0, $width - $cellspacingh * \max(0, $cols + 1));
        $colwidth = ($cols > 0) ? ($availableWidth / $cols) : $availableWidth;

        if (empty($colwidths)) {
            $colwidths = \array_fill(0, $cols, $colwidth);
        }

        // Compute actual table width from column widths + cellspacing gutters.
        $contentWidth = $cellspacingh * ($cols + 1) + (float) \array_sum($colwidths);
        if (
            ($rawTableWidth !== '')
            && (\preg_match('/^([0-9.+\-]+)\s*%$/', $rawTableWidth) === 1)
        ) {
            // Percentage widths are already resolved against the available box;
            // keep the computed content width to avoid collapsing to tiny values.
            $width = $contentWidth;
        } else {
            $width = \min($width, $contentWidth);
        }

        // Update the block buffer width so the block-level border/background
        // wraps the actual table content, not the full container.
        if (!empty($hrc['blockbuf'])) {
            $bidx = \count($hrc['blockbuf']) - 1;
            if ($hrc['blockbuf'][$bidx]['openkey'] === $key) {
                $hrc['blockbuf'][$bidx]['bw'] = \min($hrc['blockbuf'][$bidx]['bw'], $width);
            }
        }

        if (
            isset($hrc['dom'][$key]['caption-top-html'])
            && \is_string($hrc['dom'][$key]['caption-top-html'])
            && ($hrc['dom'][$key]['caption-top-html'] !== '')
        ) {
            // Use local copies for caption rendering to prevent corrupting table width.
            // resetHTMLLineCursor called during caption rendering would otherwise modify
            // the table's width via the $tpw reference parameter.
            $captpx = $tpx;
            $captpy = $tpy;
            $captpw = $width;
            $out .= $this->replayHTMLTableCaption(
                $hrc,
                $hrc['dom'][$key]['caption-top-html'],
                $captpx,
                $captpy,
                $captpw,
            );
            // Only update Y position to move past the caption; preserve original width.
            $tpy = $captpy;
        }

        // Track regionoffset to account for region/page breaks after table creation.
        // When table cells are rendered later, their X positions must account for any
        // RX shifts that occurred after this table was opened (e.g., multi-column layout).
        $tableRegionoffset = (float) ($hrc['cellctx']['regionoffset'] ?? 0.0);

        $hrc['tablestack'][] = [
            'originx' => $tpx,
            'originy' => $tpy,
            'width' => $width,
            'dir' => (isset($elm['dir']) && \is_string($elm['dir'])) ? \strtolower(\trim($elm['dir'])) : 'ltr',
            'cols' => $cols,
            'colwidth' => $colwidth,
            'colwidths' => $colwidths,
            'cellspacingh' => $cellspacingh,
            'cellspacingv' => $cellspacingv,
            'cellpadding' => $cellpadding,
            'collapse' => (($elm['border-collapse'] ?? 'separate') === 'collapse'),
            'hascellborders' => false,
            'prevrowbottom' => [],
            'rowtop' => $tpy + $cellspacingv,
            'rowheight' => 0.0,
            'colindex' => 0,
            'cells' => [],
            'occupied' => \array_fill(0, $cols, 0),
            'rowspans' => [],
            'regionoffset' => $tableRegionoffset,
        ];

        $tpy += $cellspacingv;
        $tpw = $width;

        return $out;
    }

    /**
     * Process HTML opening tag <tablehead>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtablehead(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENtable($hrc, $key, $tpx, $tpy, $tpw, $tph);
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
        *
     * @param THTMLRenderContext $hrc HTML render context.
     */
    protected function executeHTMLTcpdfPageBreak(array &$hrc, string $mode, float &$tpx, float &$tpw): void
    {
        $pid = $this->pageBreak();
        if ($mode !== 'true') {
            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
            if ((($mode == 'left') && $leftmode) || (($mode == 'right') && !$leftmode)) {
                $this->pageBreak();
            }
        }

        $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
    }

    /**
     * Process HTML opening tag <tcpdf>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtcpdf(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
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
                    $this->executeHTMLTcpdfPageBreak($hrc, $mode, $tpx, $tpw);
                    return '';
                }

                try {
                    $this->{$tagdata['m']}(...$tagdata['p']);
                } catch (\Throwable) {
                    return '';
                }

                $this->resetHTMLLineCursor($hrc, $tpx, $tpw);
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

        $this->executeHTMLTcpdfPageBreak($hrc, $mode, $tpx, $tpw);

        return '';
    }

    /**
     * Process HTML opening tag <td>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtd(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = $hrc['dom'][$key];
        unset($tph);

        $tableidx = \count($hrc['tablestack']) - 1;
        if ($tableidx < 0) {
            return '';
        }

        if ($this->pdfuaMode !== '') {
            $role = (isset($elm['value']) && $elm['value'] === 'th') ? 'TH' : 'TD';
            $attr = [];
            if ($role === 'TH') {
                $attr = ['O' => 'Table', 'Scope' => 'Column'];
            }

            $this->beginStructElem($role, $this->page->getPageId(), null, $attr);
        }

        /** @var THTMLTableState $table */
        $table = $hrc['tablestack'][$tableidx];

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
        $rawCellStyles = $this->getHTMLTableCellBorderStyles($hrc, $key);
        $cellbstyles = $rawCellStyles;
        if (!empty($table['collapse'])) {
            $expandedStyles = $this->getHTMLCollapsedTableCellBorderStyles($rawCellStyles, true, true);
            $keepTop = ($rowtop <= ($table['originy'] + self::WIDTH_TOLERANCE));
            if (!$keepTop && isset($expandedStyles[0]) && \is_array($expandedStyles[0])) {
                $curTop = $expandedStyles[0];
                $hasUncoveredTop = false;
                $curTopWinsCovered = true;
                for ($idx = $colindex; $idx < ($colindex + $colspan); ++$idx) {
                    $prevBottom = $table['prevrowbottom'][$idx] ?? null;
                    if ($prevBottom === null) {
                        $hasUncoveredTop = true;
                        continue;
                    }

                    if ($this->getHTMLCollapsedPreferredBorderStyle($prevBottom, $curTop) !== $curTop) {
                        $curTopWinsCovered = false;
                    }
                }

                $keepTop = $hasUncoveredTop || $curTopWinsCovered;
            }

            $cellbstyles = $this->getHTMLCollapsedTableCellBorderStyles(
                $rawCellStyles,
                $keepTop,
                true,
            );
            if ($colindex > 0) {
                $leftStyle = (isset($cellbstyles[3]) && \is_array($cellbstyles[3])) ? $cellbstyles[3] : null;
                if (!empty($table['cells'])) {
                    $previdx = \count($table['cells']) - 1;
                    $prevcell = $table['cells'][$previdx];
                    $prevRightEdge = (int) $prevcell['colindex'] + (int) $prevcell['colspan'];
                    if ($prevRightEdge === $colindex) {
                        /** @var array<int|string, BorderStyle> $prevstyles */
                        $prevstyles = $prevcell['bstyles'];
                        $rightStyle = (isset($prevstyles[1]) && \is_array($prevstyles[1])) ? $prevstyles[1] : null;

                        if (($leftStyle !== null) || ($rightStyle !== null)) {
                            if ($rightStyle === null) {
                                /** @var BorderStyle $sharedStyle */
                                $sharedStyle = $leftStyle ?? [];
                            } elseif ($leftStyle === null) {
                                $sharedStyle = $rightStyle;
                            } else {
                                $sharedStyle = $this->getHTMLCollapsedPreferredVerticalBorderStyle(
                                    $rightStyle,
                                    $leftStyle,
                                    (isset($table['dir']) && \is_string($table['dir'])) ? $table['dir'] : 'ltr',
                                );
                            }

                            $prevstyles[1] = $sharedStyle;
                            $table['cells'][$previdx]['bstyles'] = $prevstyles;
                        }
                    }
                }

                if (!empty($table['rowspans'])) {
                    foreach ($table['rowspans'] as $spanidx => $rowspanCell) {
                        if ((($rowspanCell['colindex'] + $rowspanCell['colspan']) !== $colindex)) {
                            continue;
                        }

                        $rightStyle = (isset($rowspanCell['bstyles'][1]) && \is_array($rowspanCell['bstyles'][1]))
                            ? $rowspanCell['bstyles'][1] : null;
                        if (($leftStyle === null) && ($rightStyle === null)) {
                            break;
                        }

                        if ($rightStyle === null) {
                            /** @var BorderStyle $sharedStyle */
                            $sharedStyle = $leftStyle ?? [];
                        } elseif ($leftStyle === null) {
                            $sharedStyle = $rightStyle;
                        } else {
                            $sharedStyle = $this->getHTMLCollapsedPreferredVerticalBorderStyle(
                                $rightStyle,
                                $leftStyle,
                                (isset($table['dir']) && \is_string($table['dir'])) ? $table['dir'] : 'ltr',
                            );
                        }

                        $rowspanCell['bstyles'][1] = $sharedStyle;
                        $table['rowspans'][$spanidx] = $rowspanCell;
                        break;
                    }
                }

                // Shared vertical edge is drawn only once by the leading cell.
                unset($cellbstyles[3]);
            }
        }

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

        $hrc['bcellctx'][] = [
            'originx' => $hrc['cellctx']['originx'],
            'originy' => $hrc['cellctx']['originy'],
            'maxwidth' => $hrc['cellctx']['maxwidth'],
            'maxheight' => $hrc['cellctx']['maxheight'],
            'lineadvance' => (float) $hrc['cellctx']['lineadvance'],
            'linebottom' => (float) $hrc['cellctx']['linebottom'],
            'lineascent' => (float) $hrc['cellctx']['lineascent'],
            'linewordspacing' => (float) $hrc['cellctx']['linewordspacing'],
            'linewrapped' => !empty($hrc['cellctx']['linewrapped']),
            'rowtop' => $rowtop,
            'cellx' => $cellx,
            'cellw' => $cellw,
            'colindex' => $colindex,
            'colspan' => $colspan,
            'padding' => $elm['padding'],
            'margin' => $elm['margin'],
            'bstyles' => $cellbstyles,
            'fillstyle' => $this->getHTMLTableCellFillStyle($hrc, $key),
            'rowspan' => $rowspan,
            'valign' => (isset($elm['valign']) && \is_string($elm['valign']))
                ? \strtolower(\trim($elm['valign'])) : 'top',
            'buffer' => '',
        ];

        $hrc['cellctx']['originx'] = $originx;
        $hrc['cellctx']['originy'] = $originy;
        $hrc['cellctx']['maxwidth'] = $maxwidth;
        $hrc['cellctx']['lineoriginx'] = $originx;
        $hrc['cellctx']['lineadvance'] = 0.0;
        $hrc['cellctx']['linebottom'] = 0.0;
        $hrc['cellctx']['lineascent'] = 0.0;
        $hrc['cellctx']['linewordspacing'] = 0.0;
        $hrc['cellctx']['linewrapped'] = false;
        $hrc['cellctx']['textindentapplied'] = false;
        $hrc['cellctx']['pendingblockmarginb'] = 0.0;

        $tpx = $originx;
        $tpy = $originy;
        $tpw = $maxwidth;

        $table['colindex'] += $colspan;

        if ($rowspan > 1) {
            for ($idx = $colindex; $idx < ($colindex + $colspan); ++$idx) {
                $table['occupied'][$idx] = \max(
                    $table['occupied'][$idx] ?? 0,
                    $rowspan,
                );
            }
        }

        // @phpstan-ignore parameterByRef.type
        $hrc['tablestack'][$tableidx] = $table;

        return '';
    }

    /**
     * Process HTML opening tag <textarea>.
     *
        * @param THTMLRenderContext $hrc HTML render context.
        *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtextarea(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        unset($tph);
        $attr = $elm['attribute'] ?? [];
        if (!\is_array($attr)) {
            return '';
        }

        $name = (isset($attr['name']) && \is_string($attr['name']))
            ? $attr['name'] : ('textarea_' . \count($this->tagvspaces));
        $value = (isset($attr['value']) && \is_string($attr['value'])) ? $attr['value'] : '';
        $fieldlabel = $this->getHTMLLabelTextForControl($hrc, $key);
        $fieldjsp = $this->getHTMLFormFieldJSProperties($attr, 'textarea');
        $lineheight = $this->getHTMLLineAdvance($hrc, $key);
        $rows = (isset($attr['rows']) && \is_numeric($attr['rows'])) ? \max(1, (int) $attr['rows']) : 3;
        $maxwidth = ($tpw > 0) ? $tpw : $hrc['cellctx']['maxwidth'];
        $fieldwidth = (!empty($elm['width']) && \is_numeric($elm['width']))
            ? (float) $elm['width']
            : $maxwidth;
        $hasCols = (empty($elm['width']) || !\is_numeric($elm['width']))
            && isset($attr['cols']) && \is_numeric($attr['cols']);
        if ($hasCols) {
            // Use the current font metrics to map HTML cols to a character-based field width.
            $cols = \max(1, (int) $attr['cols']);
            $fieldwidth = $this->getStringWidth(\str_repeat('0', $cols));
            if (($maxwidth > 0) && ($fieldwidth > $maxwidth)) {
                $fieldwidth = $maxwidth;
            }
        }
        $fieldheight = $lineheight * $rows;

        $opt = ['v' => $value];
        if ($fieldlabel !== '') {
            $opt['tu'] = $fieldlabel;
        }
        $jsp = \array_merge($fieldjsp, ['multiline' => 'true']);
        $objid = $this->addFFText(
            $name,
            $tpx,
            $tpy,
            $fieldwidth,
            $fieldheight,
            $opt,
            $jsp
        );
        $this->page->addAnnotRef($objid, $this->page->getPageID());
        $tpx += $fieldwidth;

        if ($hrc['cellctx']['maxwidth'] > 0) {
            $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - $tpx + $hrc['cellctx']['originx']);
        }

        return '';
    }

    /**
     * Process HTML opening tag <th>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENth(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        /** @var THTMLRenderContext $hrc */
        // @phpstan-ignore parameterByRef.type
        return $this->parseHTMLTagOPENtd($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <thead>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENthead(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENtablehead($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <tfoot>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtfoot(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <tr>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($key, $tph);

        $tableidx = \count($hrc['tablestack']) - 1;
        if ($tableidx < 0) {
            return '';
        }

        if ($this->pdfuaMode !== '') {
            $this->beginStructElem('TR', $this->page->getPageId());
        }

        $table = $hrc['tablestack'][$tableidx];
        $table['rowtop'] = $tpy;
        $table['rowheight'] = 0.0;
        $table['colindex'] = 0;
        $table['cells'] = [];
        $hrc['tablestack'][$tableidx] = $table;

        $tpx = $table['originx'];
        $tpw = $table['width'];

        return '';
    }

    /**
     * Process HTML opening tag <tt>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENtt(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <code>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENcode(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagOPENtt($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML opening tag <u>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENu(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML opening tag <ul>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagOPENul(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);

        $parentIsLi = isset($hrc['dom'][$key]['parent'])
            && \is_int($hrc['dom'][$key]['parent'])
            && ($hrc['dom'][$key]['parent'] >= 0)
            && isset($hrc['dom'][$hrc['dom'][$key]['parent']]['value'])
            && ($hrc['dom'][$hrc['dom'][$key]['parent']]['value'] === 'li');
        $inheritsListStyle = isset($hrc['dom'][$key]['style']['list-style'])
            && \is_string($hrc['dom'][$key]['style']['list-style'])
            && (\strtolower(\trim((string) $hrc['dom'][$key]['style']['list-style'])) === 'inherit');
        $inheritsLStyType = isset($hrc['dom'][$key]['style']['list-style-type'])
            && \is_string($hrc['dom'][$key]['style']['list-style-type'])
            && (\strtolower(\trim((string) $hrc['dom'][$key]['style']['list-style-type'])) === 'inherit');
        $lineAdvanceCtx = (isset($hrc['cellctx']['lineadvance']) && \is_numeric($hrc['cellctx']['lineadvance']))
            ? (float) $hrc['cellctx']['lineadvance']
            : 0.0;
        if (
            $parentIsLi
            && ($inheritsListStyle || $inheritsLStyType)
            && (
                ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE))
                || ($lineAdvanceCtx > self::WIDTH_TOLERANCE)
            )
        ) {
            $baseOriginX = (float) ($hrc['cellctx']['originx'] ?? 0.0);
            $lineStartX = $tpx;
            $this->moveHTMLToNextLine($hrc, $key, $tpx, $tpy, $tpw);
            // Nested lists inside <li> should continue from the current list-item
            // text column (browser-like), not from the parent list block origin.
            if ($lineStartX > ($baseOriginX + self::WIDTH_TOLERANCE)) {
                $delta = $lineStartX - $baseOriginX;
                $hrc['cellctx']['originx'] = $lineStartX;
                $hrc['cellctx']['maxwidth'] = \max(0.0, $hrc['cellctx']['maxwidth'] - $delta);
                $tpx = $lineStartX;
                if ($hrc['cellctx']['maxwidth'] > 0) {
                    $tpw = \max(0.0, $hrc['cellctx']['maxwidth'] - ($tpx - $hrc['cellctx']['originx']));
                }
            }
        }

        $out = $this->openHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        $this->pushHTMLList($hrc, $key, false);

        return $out;
    }

    // FUNCTIONS TO PROCESS HTML CLOSING TAGS

    /**
     * Placeholder for closing-tag handlers.
     */
    /**
     * Process HTML closing tag </a>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @SuppressWarnings("PHPMD.UnusedFormalParameter")
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEa(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        $value = '';
        if (!empty($elm['value']) && \is_string($elm['value'])) {
            $value = \strtolower($elm['value']);
        }

        if ($value === 'a') {
            $hadlink = ($this->getCurrentHTMLLink($hrc) !== '');
            $this->popHTMLLink($hrc);
            if ($this->pdfuaMode !== '' && $hadlink) {
                $this->endStructElem();
            }
        }

        return '';
    }

    /**
     * Process HTML closing tag </b>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEb(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </blockquote>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEblockquote(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </body>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEbody(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </br>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEbr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </caption>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEcaption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </col>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEcol(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </colgroup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEcolgroup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </dd>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdd(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </del>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdel(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </figure>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEfigure(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </figcaption>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEfigcaption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </div>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdiv(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </dl>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdl(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </dt>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEdt(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </em>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEem(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </font>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEfont(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </form>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEform(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </h1>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh1(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </h2>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh2(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h3>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh3(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h4>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh4(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h5>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh5(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </h6>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEh6(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEh1($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </hr>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEhr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </i>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEi(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </img>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEimg(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </input>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEinput(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </label>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSElabel(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </li>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEli(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        // closeHTMLBlock calls endStructElem() once (pops LBody when pdfuaMode is active).
        $out = $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        if ($this->pdfuaMode !== '') {
            // Pop the LI that was opened by openHTMLBlock.
            $this->endStructElem();
        }

        $saved = \array_pop($hrc['listack']);
        if ($saved !== null) {
            $hrc['cellctx']['originx'] = $saved['originx'];
            $hrc['cellctx']['maxwidth'] = $saved['maxwidth'];
            $tpx = $saved['originx'];
            $tpw = $saved['maxwidth'];
        }

        return $out;
    }

    /**
     * Process HTML closing tag </marker>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEmarker(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </ol>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEol(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        $this->popHTMLList($hrc);

        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </optgroup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEoptgroup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </option>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEoption(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </output>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEoutput(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </p>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEp(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </pre>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEpre(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        $hrc['prelevel'] = \max(0, $hrc['prelevel'] - 1);
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }

    /**
     * Process HTML closing tag </s>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEs(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </select>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEselect(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </small>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsmall(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </span>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEspan(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </strike>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEstrike(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </strong>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEstrong(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </sub>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsub(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($hrc, $key, $tpy, -self::VERT_SHIFT_SUB * self::FONT_SMALL_RATIO);
    }

    /**
     * Process HTML closing tag </sup>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEsup(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tpx, $tpw, $tph);
        return $this->shiftHTMLVerticalPosition($hrc, $key, $tpy, self::VERT_SHIFT_SUP * self::FONT_SMALL_RATIO);
    }

    /**
     * Process HTML closing tag </table>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtable(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);

        if (empty($hrc['tablestack'])) {
            return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        }

        $table = \array_pop($hrc['tablestack']);
        if ($table === null) {
            return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
        }

        $tablebottom = \max($tpy, $table['rowtop']);
        $tpx = $table['originx'];
        $tpw = $table['width'];

        $out = '';

        $tableheight = \max(0.0, $tablebottom - $table['originy']);
        $tablebstyles = [];
        $tablekey = $key;
        if ($tableheight > 0.0) {
            // Use opening <table> styles for outer frame/fill.
            // Closing nodes may inherit unrelated styles from ancestors.
            if (
                isset($hrc['dom'][$key]['parent'])
                && \is_int($hrc['dom'][$key]['parent'])
                && isset($hrc['dom'][$hrc['dom'][$key]['parent']])
            ) {
                $tablekey = $hrc['dom'][$key]['parent'];
            }

            $tablebstyles = $this->getHTMLTableCellBorderStyles($hrc, $tablekey);
        }

        foreach ($table['rowspans'] as $cell) {
            $height = ($cell['usedheight'] > 0.0) ? $cell['usedheight'] : $cell['contenth'];
            $out .= $this->renderHTMLTableCell(
                $cell['cellx'],
                $cell['rowtop'],
                $cell['cellw'],
                $height,
                $cell['contenth'],
                $cell['valign'],
                $cell['bstyles'],
                $cell['fillstyle'],
                $cell['buffer'],
            );
        }

        if (($tableheight > 0.0) && ($tablebstyles !== [])) {
            // Draw table frame after cell content for crisp outer edges.
            $out .= $this->renderHTMLTableCell(
                $table['originx'],
                $table['originy'],
                $table['width'],
                $tableheight,
                $tableheight,
                'top',
                $tablebstyles,
                null,
                '',
            );
        }

        if (
            isset($hrc['dom'][$tablekey]['caption-bottom-html'])
            && \is_string($hrc['dom'][$tablekey]['caption-bottom-html'])
            && ($hrc['dom'][$tablekey]['caption-bottom-html'] !== '')
        ) {
            // Use local copies for caption rendering to prevent corrupting table width.
            // resetHTMLLineCursor called during caption rendering would otherwise modify
            // the coordinates via reference parameters.
            $captpx = $tpx;
            $captpy = $tpy;
            $captpw = $tpw;
            $out .= $this->replayHTMLTableCaption(
                $hrc,
                $hrc['dom'][$tablekey]['caption-bottom-html'],
                $captpx,
                $captpy,
                $captpw,
            );
            // Update cursor position to move past the caption.
            $tpx = $captpx;
            $tpy = $captpy;
            $tpw = $captpw;
        }

        // Emit buffered table background/content first, then overlay the
        // table frame/caption so outer borders stay visible above fills.
        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw) . $out;
    }

    /**
     * Process HTML closing tag </tablehead>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtablehead(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEtable($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </tcpdf>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtcpdf(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </td>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtd(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = &$hrc['dom'][$key];
        unset($tph);

        $tableidx = \count($hrc['tablestack']) - 1;
        $cellctx = \array_pop($hrc['bcellctx']);
        if (($tableidx < 0) || ($cellctx === null)) {
            return '';
        }

        $cellPaddingB = (
            isset($cellctx['padding'])
            && \is_array($cellctx['padding'])
            && isset($cellctx['padding']['B'])
            && \is_numeric($cellctx['padding']['B'])
        ) ? (float) $cellctx['padding']['B'] : (float) $elm['padding']['B'];
        $cellMarginB = (
            isset($cellctx['margin'])
            && \is_array($cellctx['margin'])
            && isset($cellctx['margin']['B'])
            && \is_numeric($cellctx['margin']['B'])
        ) ? (float) $cellctx['margin']['B'] : (float) $elm['margin']['B'];
        // Only add trailing line advance when inline content is still present
        // on the current line. Block-only content (e.g. nested tables) already
        // updates the vertical cursor and must not add an extra blank line here.
        $hasinlinecontent = ($tpx > ($hrc['cellctx']['originx'] + self::WIDTH_TOLERANCE));
        $lineAdvance = $hasinlinecontent ? $this->getCurrentHTMLLineAdvance($hrc, $key) : 0.0;
        $cellbottom = $tpy
            + $lineAdvance
            + $cellPaddingB
            + $cellMarginB;
        $rowheight = \max(0.0, $cellbottom - $cellctx['rowtop']);
        if (!empty($elm['height']) && \is_numeric($elm['height'])) {
            $rowheight = \max($rowheight, (float) $elm['height']);
        }
        $table = $hrc['tablestack'][$tableidx];
        if ($this->shouldHideHTMLEmptyTableCell($table, $elm, $cellctx)) {
            $cellctx['bstyles'] = [];
            $cellctx['fillstyle'] = null;
        }
        if ($cellctx['bstyles'] !== []) {
            $table['hascellborders'] = true;
        }
        if ($cellctx['rowspan'] > 1) {
            $table['rowspans'][] = [
                'cellx' => $cellctx['cellx'],
                'cellw' => $cellctx['cellw'],
                'colindex' => $cellctx['colindex'],
                'colspan' => $cellctx['colspan'],
                'rowtop' => $cellctx['rowtop'],
                'rowsremaining' => $cellctx['rowspan'],
                'usedheight' => 0.0,
                'contenth' => $rowheight,
                'valign' => $cellctx['valign'],
                'bstyles' => $cellctx['bstyles'],
                'fillstyle' => $cellctx['fillstyle'],
                'buffer' => $cellctx['buffer'],
            ];
        } else {
            $table['cells'][] = [
                'cellx' => $cellctx['cellx'],
                'cellw' => $cellctx['cellw'],
                'colindex' => $cellctx['colindex'],
                'colspan' => $cellctx['colspan'],
                'contenth' => $rowheight,
                'valign' => $cellctx['valign'],
                'bstyles' => $cellctx['bstyles'],
                'fillstyle' => $cellctx['fillstyle'],
                'buffer' => $cellctx['buffer'],
            ];
            $table['rowheight'] = \max(
                $table['rowheight'],
                $rowheight,
            );
        }
        $hrc['tablestack'][$tableidx] = $table;

        $hrc['cellctx']['originx'] = $cellctx['originx'];
        $hrc['cellctx']['originy'] = $cellctx['originy'];
        $hrc['cellctx']['maxwidth'] = $cellctx['maxwidth'];
        $hrc['cellctx']['maxheight'] = $cellctx['maxheight'];
        $hrc['cellctx']['lineadvance'] = $cellctx['lineadvance'];
        $hrc['cellctx']['linebottom'] = $cellctx['linebottom'];
        $hrc['cellctx']['lineascent'] = $cellctx['lineascent'];
        $hrc['cellctx']['linewordspacing'] = $cellctx['linewordspacing'];
        $hrc['cellctx']['linewrapped'] = !empty($cellctx['linewrapped']);

        $tpy = $cellctx['rowtop'];
        $tpx = $this->getHTMLTableColX($table, $table['colindex']);
        $tpw = \max(0.0, $table['width'] - ($tpx - $table['originx']));

        if ($this->pdfuaMode !== '') {
            $this->endStructElem();
        }

        return '';
    }

    /**
     * Process HTML closing tag </textarea>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtextarea(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </th>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEth(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEtd($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </thead>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEthead(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEtablehead($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </tfoot>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtfoot(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </tr>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtr(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($key, $tph);

        $tableidx = \count($hrc['tablestack']) - 1;
        if ($tableidx < 0) {
            return '';
        }

        $table = $hrc['tablestack'][$tableidx];
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

        $tpy = $table['rowtop'] + $rowheight + $table['cellspacingv'];

        $out = '';
        if (!empty($table['cells'])) {
            foreach ($table['cells'] as $cell) {
                $out .= $this->renderHTMLTableCell(
                    $cell['cellx'],
                    $table['rowtop'],
                    $cell['cellw'],
                    $rowheight,
                    $cell['contenth'],
                    $cell['valign'],
                    $cell['bstyles'],
                    $cell['fillstyle'],
                    $cell['buffer'],
                );
            }
        }

        $rowspans = [];
        $completedRowspans = [];
        foreach ($table['rowspans'] as $cell) {
            $cellspacing = ($cell['rowsremaining'] > 1) ? $table['cellspacingv'] : 0.0;
            $cell['usedheight'] += ($rowheight + $cellspacing);
            --$cell['rowsremaining'];
            if ($cell['rowsremaining'] <= 0) {
                $completedRowspans[] = $cell;
                $out .= $this->renderHTMLTableCell(
                    $cell['cellx'],
                    $cell['rowtop'],
                    $cell['cellw'],
                    $cell['usedheight'],
                    $cell['contenth'],
                    $cell['valign'],
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

        $prevRowBottom = [];
        foreach ($table['cells'] as $cell) {
            if (!isset($cell['bstyles'][2]) || !\is_array($cell['bstyles'][2])) {
                continue;
            }

            $bottomStyle = $cell['bstyles'][2];
            $start = (int) $cell['colindex'];
            $span = (int) $cell['colspan'];
            for ($idx = $start; $idx < ($start + $span); ++$idx) {
                if (!isset($prevRowBottom[$idx])) {
                    $prevRowBottom[$idx] = $bottomStyle;
                    continue;
                }

                $prevRowBottom[$idx] = $this->getHTMLCollapsedPreferredBorderStyle(
                    $prevRowBottom[$idx],
                    $bottomStyle,
                );
            }
        }

        foreach ($completedRowspans as $cell) {
            if (!isset($cell['bstyles'][2]) || !\is_array($cell['bstyles'][2])) {
                continue;
            }

            $bottomStyle = $cell['bstyles'][2];
            $start = (int) $cell['colindex'];
            $span = (int) $cell['colspan'];
            for ($idx = $start; $idx < ($start + $span); ++$idx) {
                if (!isset($prevRowBottom[$idx])) {
                    $prevRowBottom[$idx] = $bottomStyle;
                    continue;
                }

                $prevRowBottom[$idx] = $this->getHTMLCollapsedPreferredBorderStyle(
                    $prevRowBottom[$idx],
                    $bottomStyle,
                );
            }
        }

        $table['rowtop'] = $tpy;
        $table['rowheight'] = 0.0;
        $table['colindex'] = 0;
        $table['cells'] = [];
        $table['rowspans'] = $rowspans;
        $table['prevrowbottom'] = $prevRowBottom;
        $hrc['tablestack'][$tableidx] = $table;
        $tpx = $table['originx'];
        $tpw = $table['width'];

        if ($this->pdfuaMode !== '') {
            $this->endStructElem();
        }

        return $out;
    }

    /**
     * Process HTML closing tag </tt>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEtt(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </u>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEu(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($hrc, $key, $tpx, $tpy, $tpw, $tph);
        return '';
    }

    /**
     * Process HTML closing tag </code>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEcode(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        return $this->parseHTMLTagCLOSEtt($hrc, $key, $tpx, $tpy, $tpw, $tph);
    }

    /**
     * Process HTML closing tag </ul>.
     *
     * @param THTMLRenderContext $hrc HTML render context.
     * @param int    $key DOM array key.
     * @param float  $tpx Abscissa of upper-left corner.
     * @param float  $tpy Ordinate of upper-left corner.
     * @param float  $tpw Width.
     * @param float  $tph Height.
     *
     * @return string PDF code.
     */
    protected function parseHTMLTagCLOSEul(
        array &$hrc,
        int $key,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        unset($tph);
        $this->popHTMLList($hrc);

        return $this->closeHTMLBlock($hrc, $key, $tpx, $tpy, $tpw);
    }
}
