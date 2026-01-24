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
                $html_b = \preg_replace(
                    "'<option([\s]+)value=\"([^\"]*)\"([^\>]*)>(.*?)</option>'si",
                    "\\2#!TaB!#\\4#!NwL!#",
                    $html_b,
                ) ?? '';
                $html_b = \preg_replace(
                    "'<option([^\>]*)>(.*?)</option>'si",
                    "\\2#!NwL!#",
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
            'fontname' => $font['key'],
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
                // check if we are inside a table header
                $thead = false;
                if ($tagname == 'thead') {
                    $thead = ($element[0] !== '/');
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
                        $thead,
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
        if (!empty($dom[$parent]['text-transform'])) {
            if (!empty(self::HTML_TEXT_TRANSFORM[$dom[$parent]['text-transform']])) {
                $element = \mb_convert_case(
                    $element,
                    self::HTML_TEXT_TRANSFORM[$dom[$parent]['text-transform']],
                    $this->encoding,
                );
            }
            $element = \preg_replace("/&NBSP;/i", "&nbsp;", $element) ?? '';
        }
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
        $dom[$key] = \array_merge($dom[$parent], $dom[$key]);
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
        $this->inheritHTMLProperties($dom, $key, $granparent);

        // set the number of columns in table tag
        if (
            ($dom[$key]['value'] == 'tr')
            && (!empty($dom[$parent]['cols']))
            && (empty($dom[$granparent]['cols']))
        ) {
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
        $dom[$parent]['content'] = $content;
        /** @var array<int, THTMLAttrib> $dom */
        // store header rows on a new table
        if (
            ($dom[$key]['value'] == 'tr')
            && !empty($dom[$parent]['thead'])
        ) {
            if (empty($dom[$granparent]['thead'])) {
                $dom[$granparent]['thead'] = $cssarray . $elm[$dom[$granparent]['elkey']];
            }
            for ($idx = $parent; $idx <= $key; ++$idx) {
                /** @var array<int, THTMLAttrib> $dom */
                $dom[$granparent]['thead'] .= $elm[$dom[$idx]['elkey']];
            }
            /** @var array<int, THTMLAttrib> $dom */
            // header elements must be always contained in a single page
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
        array $level,
        string $element,
        int $key,
        bool $thead,
    ): void {
        $dom[$key]['opening'] = true;
        /** @var array<int, THTMLAttrib> $dom */
        $dom[$key]['self'] = ((\substr($element, -1, 1) == '/')
            || (\in_array($dom[$key]['value'], self::HTML_SELF_CLOSING_TAGS)));
        if (!$dom[$key]['self']) {
            \array_push($level, $key);
        }
        $parentkey = 0;
        if ($key > 0) {
            $parentkey = (int) $dom[$key]['parent'];
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
            $dom[$key]['attribute'] = []; // reset attribute array
            foreach ($attr_array[1] as $id => $name) {
                /** @var array<int, THTMLAttrib> $dom */
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
        if (
            !empty($dom[$key]['style']['font-size'])
            && \is_numeric($dom[$key]['style']['font-size'])
        ) {
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
            if (\strtolower($dom[$key]['style']['font-weight'][0]) == 'n') {
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
            $dom[$key]['fontstyle'] = 'U';
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
        $dom[$key]['padding'] = empty($dom[$key]['style']['padding']) ?
            self::ZEROCELLBOUND : $this->getCSSPadding($dom[$key]['style']['padding']);
        /** @var array<int, THTMLAttrib> $dom */
        // check for CSS margin properties
        $dom[$key]['margin'] = empty($dom[$key]['style']['margin']) ?
            self::ZEROCELLBOUND : $this->getCSSMargin($dom[$key]['style']['margin']);
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
            $dom[$key]['fontstyle'] = 'U';
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
            $dom[$key]['attribute']['colspan'] = $colspan;
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
        $lspace = 2 * $font['cw'][32]; // width of two spaces
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
                $txti = \chr(97 + $count - 1);
                break;
            case 'A':
            case 'upper-alpha':
            case 'upper-latin':
                $txti = \chr(65 + $count - 1);
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

        // return ordered item as text
        $txti = $this->rtl ? '.' . $txti : $txti . '.';
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
     * @TODO - EXPERIMENTAL - DRAFT - IN PROGRESS
     *
     * Returns the PDF code to render an HTML block inside a rectangular cell.
     *
     * @param string      $html        HTML code to be processed.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param ?TCellDef   $cell        Optional to overwrite cell parameters for padding, margin etc.
     * @param array<int, BorderStyle> $styles Cell border styles (see: getCurrentStyleArray).
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
        $numel = \count($dom);
        $key = 0;

        $tpx = $posx;
        $tpy = $posy;
        $tpw = $width;
        $tph = $height;

        $cell = $cell; // @TODO
        $styles = $styles; // @TODO

        while ($key < $numel) {
            $elm = $dom[$key];

            if ($elm['tag']) { // HTML TAG
                if ($elm['opening']) { // opening tag
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
                        if ($elm['attribute']['pagebreak'] != 'true') {
                            $leftmode = ($this->rtl ^ (($pid % 2) == 0));
                            if (
                                (($elm['attribute']['pagebreak'] == 'left') && $leftmode)
                                || (($elm['attribute']['pagebreak'] == 'right') && !$leftmode)
                            ) {
                                $this->pageBreak();
                            }
                        }
                    }

                    // if (!empty($elm['attribute']['nobr'])) {
                    //     if (!empty($dom[($dom[$key]['parent'])]['attribute']['nobr'])) {
                    //         $dom[$key]['attribute']['nobr'] = '';
                    //     }
                    // }

                    $out .= match ($elm['value']) {
                        // 'a'          => $this->parseHTMLTagOPENa($elm, $tpx, $tpy, $tpw, $tph),
                        // 'b'          => $this->parseHTMLTagOPENb($elm, $tpx, $tpy, $tpw, $tph),
                        // 'blockquote' => $this->parseHTMLTagOPENblockquote($elm, $tpx, $tpy, $tpw, $tph),
                        // 'body'       => $this->parseHTMLTagOPENbody($elm, $tpx, $tpy, $tpw, $tph),
                        // 'br'         => $this->parseHTMLTagOPENbr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dd'         => $this->parseHTMLTagOPENdd($elm, $tpx, $tpy, $tpw, $tph),
                        // 'del'        => $this->parseHTMLTagOPENdel($elm, $tpx, $tpy, $tpw, $tph),
                        // 'div'        => $this->parseHTMLTagOPENdiv($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dl'         => $this->parseHTMLTagOPENdl($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dt'         => $this->parseHTMLTagOPENdt($elm, $tpx, $tpy, $tpw, $tph),
                        // 'em'         => $this->parseHTMLTagOPENem($elm, $tpx, $tpy, $tpw, $tph),
                        // 'font'       => $this->parseHTMLTagOPENfont($elm, $tpx, $tpy, $tpw, $tph)
                        // 'form'       => $this->parseHTMLTagOPENform($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h1'         => $this->parseHTMLTagOPENh1($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h2'         => $this->parseHTMLTagOPENh2($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h3'         => $this->parseHTMLTagOPENh3($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h4'         => $this->parseHTMLTagOPENh4($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h5'         => $this->parseHTMLTagOPENh5($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h6'         => $this->parseHTMLTagOPENh6($elm, $tpx, $tpy, $tpw, $tph),
                        // 'hr'         => $this->parseHTMLTagOPENhr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'i'          => $this->parseHTMLTagOPENi($elm, $tpx, $tpy, $tpw, $tph),
                        // 'img'        => $this->parseHTMLTagOPENimg($elm, $tpx, $tpy, $tpw, $tph),
                        // 'input'      => $this->parseHTMLTagOPENinput($elm, $tpx, $tpy, $tpw, $tph),
                        // 'label'      => $this->parseHTMLTagOPENlabel($elm, $tpx, $tpy, $tpw, $tph),
                        // 'li'         => $this->parseHTMLTagOPENli($elm, $tpx, $tpy, $tpw, $tph),
                        // 'marker'     => $this->parseHTMLTagOPENmarker($elm, $tpx, $tpy, $tpw, $tph),
                        // 'ol'         => $this->parseHTMLTagOPENol($elm, $tpx, $tpy, $tpw, $tph),
                        // 'option'     => $this->parseHTMLTagOPENoption($elm, $tpx, $tpy, $tpw, $tph),
                        // 'output'     => $this->parseHTMLTagOPENoutput($elm, $tpx, $tpy, $tpw, $tph),
                        // 'p'          => $this->parseHTMLTagOPENp($elm, $tpx, $tpy, $tpw, $tph),
                        // 'pre'        => $this->parseHTMLTagOPENpre($elm, $tpx, $tpy, $tpw, $tph),
                        // 's'          => $this->parseHTMLTagOPENs($elm, $tpx, $tpy, $tpw, $tph),
                        // 'select'     => $this->parseHTMLTagOPENselect($elm, $tpx, $tpy, $tpw, $tph),
                        // 'small'      => $this->parseHTMLTagOPENsmall($elm, $tpx, $tpy, $tpw, $tph),
                        // 'span'       => $this->parseHTMLTagOPENspan($elm, $tpx, $tpy, $tpw, $tph),
                        // 'strike'     => $this->parseHTMLTagOPENstrike($elm, $tpx, $tpy, $tpw, $tph),
                        // 'strong'     => $this->parseHTMLTagOPENstrong($elm, $tpx, $tpy, $tpw, $tph),
                        // 'sub'        => $this->parseHTMLTagOPENsub($elm, $tpx, $tpy, $tpw, $tph),
                        // 'table'      => $this->parseHTMLTagOPENtable($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tablehead'  => $this->parseHTMLTagOPENtablehead($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tcpdf'      => $this->parseHTMLTagOPENtcpdf($elm, $tpx, $tpy, $tpw, $tph),
                        // 'td'         => $this->parseHTMLTagOPENtd($elm, $tpx, $tpy, $tpw, $tph),
                        // 'textarea'   => $this->parseHTMLTagOPENtextarea($elm, $tpx, $tpy, $tpw, $tph),
                        // 'th'         => $this->parseHTMLTagOPENth($elm, $tpx, $tpy, $tpw, $tph),
                        // 'thead'      => $this->parseHTMLTagOPENthead($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tr'         => $this->parseHTMLTagOPENtr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tt'         => $this->parseHTMLTagOPENtt($elm, $tpx, $tpy, $tpw, $tph),
                        // 'u'          => $this->parseHTMLTagOPENu($elm, $tpx, $tpy, $tpw, $tph),
                        // 'ul'         => $this->parseHTMLTagOPENul($elm, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                } else { // closing tag
                    $out .= match ($elm['value']) {
                        // 'a'          => $this->parseHTMLTagCLOSEa($elm, $tpx, $tpy, $tpw, $tph),
                        // 'b'          => $this->parseHTMLTagCLOSEb($elm, $tpx, $tpy, $tpw, $tph),
                        // 'blockquote' => $this->parseHTMLTagCLOSEblockquote($elm, $tpx, $tpy, $tpw, $tph),
                        // 'body'       => $this->parseHTMLTagCLOSEbody($elm, $tpx, $tpy, $tpw, $tph),
                        // 'br'         => $this->parseHTMLTagCLOSEbr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dd'         => $this->parseHTMLTagCLOSEdd($elm, $tpx, $tpy, $tpw, $tph),
                        // 'del'        => $this->parseHTMLTagCLOSEdel($elm, $tpx, $tpy, $tpw, $tph),
                        // 'div'        => $this->parseHTMLTagCLOSEdiv($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dl'         => $this->parseHTMLTagCLOSEdl($elm, $tpx, $tpy, $tpw, $tph),
                        // 'dt'         => $this->parseHTMLTagCLOSEdt($elm, $tpx, $tpy, $tpw, $tph),
                        // 'em'         => $this->parseHTMLTagCLOSEem($elm, $tpx, $tpy, $tpw, $tph),
                        // 'font'       => $this->parseHTMLTagCLOSEfont($elm, $tpx, $tpy, $tpw, $tph)
                        // 'form'       => $this->parseHTMLTagCLOSEform($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h1'         => $this->parseHTMLTagCLOSEh1($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h2'         => $this->parseHTMLTagCLOSEh2($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h3'         => $this->parseHTMLTagCLOSEh3($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h4'         => $this->parseHTMLTagCLOSEh4($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h5'         => $this->parseHTMLTagCLOSEh5($elm, $tpx, $tpy, $tpw, $tph),
                        // 'h6'         => $this->parseHTMLTagCLOSEh6($elm, $tpx, $tpy, $tpw, $tph),
                        // 'hr'         => $this->parseHTMLTagCLOSEhr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'i'          => $this->parseHTMLTagCLOSEi($elm, $tpx, $tpy, $tpw, $tph),
                        // 'img'        => $this->parseHTMLTagCLOSEimg($elm, $tpx, $tpy, $tpw, $tph),
                        // 'input'      => $this->parseHTMLTagCLOSEinput($elm, $tpx, $tpy, $tpw, $tph),
                        // 'label'      => $this->parseHTMLTagCLOSElabel($elm, $tpx, $tpy, $tpw, $tph),
                        // 'li'         => $this->parseHTMLTagCLOSEli($elm, $tpx, $tpy, $tpw, $tph),
                        // 'marker'     => $this->parseHTMLTagCLOSEmarker($elm, $tpx, $tpy, $tpw, $tph),
                        // 'ol'         => $this->parseHTMLTagCLOSEol($elm, $tpx, $tpy, $tpw, $tph),
                        // 'option'     => $this->parseHTMLTagCLOSEoption($elm, $tpx, $tpy, $tpw, $tph),
                        // 'output'     => $this->parseHTMLTagCLOSEoutput($elm, $tpx, $tpy, $tpw, $tph),
                        // 'p'          => $this->parseHTMLTagCLOSEp($elm, $tpx, $tpy, $tpw, $tph),
                        // 'pre'        => $this->parseHTMLTagCLOSEpre($elm, $tpx, $tpy, $tpw, $tph),
                        // 's'          => $this->parseHTMLTagCLOSEs($elm, $tpx, $tpy, $tpw, $tph),
                        // 'select'     => $this->parseHTMLTagCLOSEselect($elm, $tpx, $tpy, $tpw, $tph),
                        // 'small'      => $this->parseHTMLTagCLOSEsmall($elm, $tpx, $tpy, $tpw, $tph),
                        // 'span'       => $this->parseHTMLTagCLOSEspan($elm, $tpx, $tpy, $tpw, $tph),
                        // 'strike'     => $this->parseHTMLTagCLOSEstrike($elm, $tpx, $tpy, $tpw, $tph),
                        // 'strong'     => $this->parseHTMLTagCLOSEstrong($elm, $tpx, $tpy, $tpw, $tph),
                        // 'sub'        => $this->parseHTMLTagCLOSEsub($elm, $tpx, $tpy, $tpw, $tph),
                        // 'table'      => $this->parseHTMLTagCLOSEtable($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tablehead'  => $this->parseHTMLTagCLOSEtablehead($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tcpdf'      => $this->parseHTMLTagCLOSEtcpdf($elm, $tpx, $tpy, $tpw, $tph),
                        // 'td'         => $this->parseHTMLTagCLOSEtd($elm, $tpx, $tpy, $tpw, $tph),
                        // 'textarea'   => $this->parseHTMLTagCLOSEtextarea($elm, $tpx, $tpy, $tpw, $tph),
                        // 'th'         => $this->parseHTMLTagCLOSEth($elm, $tpx, $tpy, $tpw, $tph),
                        // 'thead'      => $this->parseHTMLTagCLOSEthead($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tr'         => $this->parseHTMLTagCLOSEtr($elm, $tpx, $tpy, $tpw, $tph),
                        // 'tt'         => $this->parseHTMLTagCLOSEtt($elm, $tpx, $tpy, $tpw, $tph),
                        // 'u'          => $this->parseHTMLTagCLOSEu($elm, $tpx, $tpy, $tpw, $tph),
                        // 'ul'         => $this->parseHTMLTagCLOSEul($elm, $tpx, $tpy, $tpw, $tph),
                        default      => '',
                    };
                }
            } else { // Text Content
                $out .= $this->parseHTMLText($elm, $tpx, $tpy, $tpw, $tph);
            }
        }

        return $out;
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
     * @return string PDF code.
     */
    protected function parseHTMLText(
        array $elm,
        float &$tpx,
        float &$tpy,
        float &$tpw,
        float &$tph,
    ): string {
        $elm = $elm; // @TODO
        $tpx = $tpx; // @TODO
        $tpy = $tpy; // @TODO
        $tpw = $tpw; // @TODO
        $tph = $tph; // @TODO
        return '';
    }

    // FUNCTIONS TO PROCESS HTML OPENING TAGS
    // @TODO

    // FUNCTIONS TO PROCESS HTML CLOSING TAGS
    // @TODO
}
