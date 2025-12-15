<?php

/**
 * HTML.php
 *
 * @since     2002-08-03
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
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 *
 * @phpstan-import-type StyleData from \Com\Tecnick\Pdf\Graph\Base as BorderStyle
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class HTML extends \Com\Tecnick\Pdf\JavaScript
{
    //@TODO: to be completed

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
     * HTML inheritable properties.
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
        return \preg_replace('/^' . $this->spaceregexp['p'] . '+/' . $this->spaceregexp['m'], $replace, $str) ?? '';
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
        return \preg_replace('/' . $this->spaceregexp['p'] . '+$/' . $this->spaceregexp['m'], $replace, $str) ?? '';
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
            '/<\/(table|tr|td|th|blockquote|dd|dt|dl|div|dt|h1|h2|h3|h4|h5|h6|hr|li|ol|ul|p)>[\s]+</',
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
     * @return array<string, mixed>
     */
    protected function getHTMLRootProperties(): array
    {
        $font = $this->font->getCurrentFont();
        return [
            'align' => '',
            //'azimuth' => '',//
            'bgcolor' => '',
            'block' => false,
            //'border-collapse' => '',//
            //'border-spacing' => '',//
            'border' => [],
            //'caption-side' => '',//
            'clip' => false,
            //'color' => '',//
            //'cursor' => '',//
            'dir' => $this->rtl ? 'rtl' : 'ltr',
            //'direction' => '',//
            //'empty-cells' => '',//
            'fgcolor' => 'black',
            'fill' => true,
            //'font-family' => '',//
            //'font-size-adjust' => '',//
            //'font-size' => $font['size'],//
            'font-stretch' => $font['stretching'],
            //'font-style' => $font['style'],//
            //'font-variant' => '',//
            //'font-weight' => '',//
            //'font' => '',//
            'fontname' => $font['key'],
            'fontsize' => $font['size'],
            'fontstyle' => $font['style'],
            'hide' => false,
            'letter-spacing' => $font['spacing'],
            'line-height' => 1.0,
            //'list-style-image' => '',//
            //'list-style-position' => '',//
            //'list-style-type' => '',//
            //'list-style' => '',//
            'listtype' => '',
            //'orphans' => '',//
            //'page-break-inside' => '',//
            //'page' => '',//
            'parent' => 0,
            //'quotes' => '',//
            //'speak-header' => '',//
            //'speak' => '',//
            'stroke' => 0.0,
            'strokecolor' => 'black',
            'tag' => false,
            //'text-align' => '',//
            'text-indent' => 0.0,
            'text-transform' => '',
            'value' => '',
            //'volume' => '',//
            //'white-space' => '',//
            //'widows' => '',//
            //'word-spacing' => '',//
        ];
    }

    /**
     * Parse and returs the HTML DOM array,
     *
     * @param string $html HTML code to parse.
     *
     * @return array<int, array<string, mixed>> HTML DOM Array
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

        $dom = [];
        $dom[0] = $this->getHTMLRootProperties();
        $level = [0];

        $elm = \preg_split(self::HTML_TAG_PATTERN, $html, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        if ($elm === false) {
            return $dom;
        }

        $maxel = \count($elm);
        $elkey = 0;
        $key = 1;

        while ($elkey < $maxel) {
            $dom[$key] = [];
            $dom[$key]['elkey'] = $elkey;
            $element = $elm[$elkey];
            $parent = \intval(\end($level));

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
                if ($element[0] == '/') { // closing tag
                    array_pop($level);
                    $this->processHTMLDOMClosingTag($dom, $elm, $key, $parent, $cssarray);
                } else { // opening or self-closing html tag
                    $this->processHTMLDOMOpeningTag(
                        $dom,
                        $css,
                        $level,
                        $element,
                        $key,
                        $parent,
                        $thead,
                    );
                }
            } else {
                // content between tags (TEXT)
                $this->processHTMLDOMText($dom, $element, $key, $parent);
            }

            ++$elkey;
            ++$key;
        }

        return $dom;
    }

    /**
     * Process the content between tags (text).
     *
     * @param array<int, array<string, mixed>> $dom DOM array.
     * @param string $element Element data.
     * @param int $key Current element ID.
     * @param int $parent ID of the parent element.
     *
     * @return void
     */
    protected function processHTMLDOMText(array &$dom, string $element, int $key, int $parent): void
    {
        $dom[$key]['tag'] = false;
        $dom[$key]['block'] = false;
        $dom[$key]['parent'] = $parent;
        $dom[$key]['dir'] = $dom[$parent]['dir'];
        if (!empty($dom[$parent]['text-transform'])) {
            $ttm = [
                'capitalize' => MB_CASE_TITLE,
                'uppercase' => MB_CASE_UPPER,
                'lowercase' => MB_CASE_LOWER,
            ];
            if (
                isset($dom[$parent]['text-transform'])
                && \is_string($dom[$parent]['text-transform'])
                && !empty($ttm[$dom[$parent]['text-transform']])
            ) {
                $element = \mb_convert_case(
                    $element,
                    $ttm[$dom[$parent]['text-transform']],
                    $this->encoding,
                );
            }
            $element = \preg_replace("/&NBSP;/i", "&nbsp;", $element) ?? '';
        }
        $dom[$key]['value'] = \stripslashes($this->unhtmlentities($element));
    }

    /**
     * Inherith HTML properties from a parent element.
     *
     * @param array<int, array<string, mixed>> $dom DOM array.
     * @param int $key ID of the current HTML element.
     * @param int $parent ID of the parent element from which to inherit properties.
     *
     * @return void
     */
    protected function inheritHTMLProperties(array &$dom, int $key, int $parent): void
    {
        foreach (self::HTML_INHPROP as $prp) {
            $dom[$key][$prp] = $dom[$parent][$prp];
        }
    }

    /**
     * Process the HTML DOM closing tag.
     *
     * @param array<int, array<string, mixed>> $dom DOM array.
     * @param array<int, string> $elm Current element.
     * @param int $key Current element ID.
     * @param int $parent ID of the parent element.
     * @param string $cssarray.
     *
     * @return void
     */
    protected function processHTMLDOMClosingTag(array &$dom, array $elm, int $key, int $parent, string $cssarray): void
    {
        $dom[$key]['opening'] = false;
        $dom[$key]['parent'] = $parent;
        $granparent = $dom[$parent]['parent'];
        $this->inheritHTMLProperties($dom, $key, $granparent);

        // set the number of columns in table tag
        if (($dom[$key]['value'] == 'tr') && (!empty($dom[$granparent]['cols']))) {
            $dom[$granparent]['cols'] = $dom[$parent]['cols'];
        }
        if (($dom[$key]['value'] == 'td') || ($dom[$key]['value'] == 'th')) {
            $dom[$parent]['content'] = $cssarray;
            for ($idx = ($parent + 1); $idx < $key; ++$idx) {
                if (isset($dom[$idx]['elkey']) && \is_int($dom[$idx]['elkey'])) {
                    $dom[$parent]['content'] .= \stripslashes($elm[$dom[$idx]['elkey']]);
                }
            }
            $key = $idx;
            // mark nested tables
            $dom[$parent]['content'] = \str_replace('<table', '<table nested="true"', $dom[$parent]['content']);
            // remove thead sections from nested tables
            $dom[$parent]['content'] = \str_replace('<thead>', '', $dom[$parent]['content']);
            $dom[$parent]['content'] = \str_replace('</thead>', '', $dom[$parent]['content']);
        }
        // store header rows on a new table
        if (
            ($dom[$key]['value'] === 'tr')
            && !empty($dom[$parent]['thead'])
            && ($dom[$parent]['thead'] === true)
        ) {
            if (
                empty($dom[$granparent]['thead'])
                && !empty($dom[$granparent]['elkey'])
                && \is_int($dom[$granparent]['elkey'])
            ) {
                $dom[$granparent]['thead'] = $cssarray . $elm[$dom[$granparent]['elkey']];
            }
            for ($idx = $parent; $idx <= $key; ++$idx) {
                if (
                    isset($dom[$idx]['elkey'])
                    && \is_int($dom[$idx]['elkey'])
                    && \is_string($dom[$granparent]['thead'])
                ) {
                    $dom[$granparent]['thead'] .= $elm[$dom[$idx]['elkey']];
                }
            }
            if (!isset($dom[$parent]['attribute'])) {
                $dom[$parent]['attribute'] = [];
            }
            // header elements must be always contained in a single page
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $dom[$parent]['attribute']['nobr'] = 'true';
        }
        if (
            ($dom[$key]['value'] == 'table')
            && (!empty($dom[$parent]['thead']))
            && \is_string($dom[$parent]['thead'])
        ) {
            // remove the nobr attributes from the table header
            $dom[$parent]['thead'] = \str_replace(' nobr="true"', '', $dom[$parent]['thead']);
            $dom[$parent]['thead'] .= '</tablehead>';
        }
    }

    /**
     * Process HTML DOM Opening Tag.
     *
     * @param array<int, array<string, mixed>> $dom
     * @param array<string, string> $css
     * @param array<int> $level
     * @param string $element
     * @param int $key
     * @param int $parent
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
        int $parent,
        bool $thead,
    ): void {
        $dom[$key]['opening'] = true;
        $dom[$key]['parent'] = $parent;
        $dom[$key]['self'] = ((\substr($element, -1, 1) == '/')
            || (\in_array($dom[$key]['value'], self::HTML_SELF_CLOSING_TAGS)));
        if (!$dom[$key]['self']) {
            array_push($level, $key);
        }
        $parentkey = 0;
        if ($key > 0) {
            $parentkey = $dom[$key]['parent'];
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
                $dom[$key]['attribute'][strtolower($name)] = $attr_array[2][$id];
            }
        }

        if (!empty($css)) {
            // merge CSS style to current style
            list($dom[$key]['csssel'], $dom[$key]['cssdata']) = $this->getHTMLDOMCSSData($dom, $css, $key);
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $dom[$key]['attribute']['style'] = $this->implodeCSSData($dom[$key]['cssdata']);
        }

        $this->splitHTMLStyleAttributes($dom, $key, $parentkey);

        //@TODO ...
        $thead = $thead; //@TODO
    }

    /**
     * Returns the styles array that apply for the selected HTML tag.
     *
     * @param array<int, array<string, mixed>> $dom
     * @param array<string, string> $css
     * @param int $key Key of the current HTML tag.
     *
     * @return array{array<string>, array<string, array{'k': string, 'c': string, 's': string}>}
     */
    public function getHTMLDOMCSSData(array $dom, array $css, int $key): array
    {
        $ret = [];
        // get parent CSS selectors
        /** @var array<string> $selectors */
        $selectors = [];
        if (
            isset($dom[$key]['parent'])
            && \is_int($dom[$key]['parent'])
            && !empty($dom[($dom[$key]['parent'])]['csssel'])
            && \is_array($dom[($dom[$key]['parent'])]['csssel'])
        ) {
            $selectors = $dom[($dom[$key]['parent'])]['csssel'];
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
        // @phpstan-ignore offsetAccess.nonOffsetAccessible
        if (!empty($dom[$key]['attribute']['style'])) {
            // attach inline style (latest properties have high priority)
            $ret[] = [
                'k' => '',
                's' => '1000',
                'c' => $dom[$key]['attribute']['style'],
            ];
        }
        // order the css array to account for specificity
        $cssordered = [];
        foreach ($ret as $key => $val) {
            $skey = \sprintf('%04d', $key);
            $cssordered[$val['s'] . '_' . $skey] = $val;
        }
        // sort selectors alphabetically to account for specificity
        \ksort($cssordered, SORT_STRING);
        // @phpstan-ignore return.type
        return [$selectors, $cssordered];
    }

    /**
     * Returns true if the CSS selector is valid for the selected HTML tag.
     *
     * @param array<int, array<string, mixed>> $dom
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
        if (
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            !empty($dom[$key]['attribute']['class'])
            && \is_string($dom[$key]['attribute']['class'])
        ) {
            $class = \explode(' ', \strtolower($dom[$key]['attribute']['class']));
        }
        $idx = '';
        if (
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            !empty($dom[$key]['attribute']['id'])
            && \is_string($dom[$key]['attribute']['id'])
        ) {
            $idx = \strtolower($dom[$key]['attribute']['id']);
        }
        $selector = \preg_replace('/([\>\+\~\s]{1})([\.]{1})([^\>\+\~\s]*)/si', '\\1*.\\3', $selector) ?? '';
        $matches = [];
        if (empty(\preg_match_all('/([\>\+\~\s]{1})([a-zA-Z0-9\*]+)([^\>\+\~\s]*)/si', $selector, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))) {
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
                    if (empty(\preg_match('/\[([a-zA-Z0-9]*)[\s]*([\~\^\$\*\|\=]*)[\s]*["]?([^"\]]*)["]?\]/i', $attrib, $attrmatch))) {
                        break;
                    }
                    $att = \strtolower($attrmatch[1]);
                    $val = $attrmatch[3];
                    if (
                        // @phpstan-ignore offsetAccess.nonOffsetAccessible
                        isset($dom[$key]['attribute'][$att])
                        && \is_string($dom[$key]['attribute'][$att])
                    ) {
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
                        // (:root, :nth-child(n), :nth-last-child(n), :nth-of-type(n), :nth-last-of-type(n), :first-child, :last-child, :first-of-type, :last-of-type, :only-child, :only-of-type, :empty, :link, :visited, :active, :hover, :focus, :target, :lang(fr), :enabled, :disabled, :checked)
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
     * Split the HTML DOM Style attributes.
     *
     * @param array<int, array<string, mixed>> $dom
     * @param int $key key of the current HTML tag.
     * @param int $parentkey Key of the parent element.
     */
    public function splitHTMLStyleAttributes(array $dom, int $key, int $parentkey): void
    {
        if (
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            empty($dom[$key]['attribute']['style'])
            || !\is_string($dom[$key]['attribute']['style'])
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
            // in case of duplicate attribute the last replace the previous
            $dom[$key]['style'][\strtolower($name)] = \trim($style_array[2][$id]);
        }
        // --- get some style attributes ---
        // text direction
        if (isset($dom[$key]['style']['direction'])) {
            $dom[$key]['dir'] = $dom[$key]['style']['direction'];
        }
        // display
        if (isset($dom[$key]['style']['display'])) {
            $dom[$key]['hide'] = (\trim(\strtolower($dom[$key]['style']['display'])) == 'none');
        }
        // font family
        if (!empty($dom[$key]['style']['font-family'])) {
            $dom[$key]['fontname'] = $this->font->getFontFamilyName($dom[$key]['style']['font-family']);
        }
        // list-style-type
        if (!empty($dom[$key]['style']['list-style-type'])) {
            $dom[$key]['listtype'] = \trim(\strtolower($dom[$key]['style']['list-style-type']));
            if ($dom[$key]['listtype'] == 'inherit') {
                $dom[$key]['listtype'] = $dom[$parentkey]['listtype'];
            }
        }
        // text-indent
        if (isset($dom[$key]['style']['text-indent'])) {
            $dom[$key]['text-indent'] = $this->toUnit($this->getUnitValuePoints($dom[$key]['style']['text-indent']));
            if ($dom[$key]['text-indent'] == 'inherit') {
                $dom[$key]['text-indent'] = $dom[$parentkey]['text-indent'];
            }
        }
        // text-transform
        if (isset($dom[$key]['style']['text-transform'])) {
            $dom[$key]['text-transform'] = $dom[$key]['style']['text-transform'];
        }
        // font size
        if (
            isset($dom[$key]['style']['font-size'])
            && \is_numeric($dom[$key]['style']['font-size'])
        ) {
            $fsize = \trim($dom[$key]['style']['font-size']);
            $ref = self::REFUNITVAL;
            if (\is_numeric($dom[$parentkey]['fontsize'])) {
                $ref['parent'] = \floatval($dom[$parentkey]['fontsize']);
            }
            $dom[$key]['fontsize'] = $this->getFontValuePoints($fsize, $ref, 'pt');
        }
        // font-stretch
        if (
            isset($dom[$key]['style']['font-stretch'])
            && \is_numeric($dom[$parentkey]['font-stretch'])
        ) {
            $dom[$key]['font-stretch'] = $this->getTAFontStretching(
                $dom[$key]['style']['font-stretch'],
                \floatval($dom[$parentkey]['font-stretch']),
            );
        }
        // letter-spacing
        if (
            isset($dom[$key]['style']['letter-spacing'])
            && \is_numeric($dom[$parentkey]['letter-spacing'])
        ) {
            $dom[$key]['letter-spacing'] = $this->getTALetterSpacing(
                $dom[$key]['style']['letter-spacing'],
                \floatval($dom[$parentkey]['letter-spacing']),
            );
        }
        // line-height (internally is the cell height ratio)
        if (isset($dom[$key]['style']['line-height'])) {
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
                    $dom[$key]['line-height'] = $this->toUnit($this->getUnitValuePoints($lineheight, defunit: '%'));
                    if (\substr($lineheight, -1) !== '%') {
                        if ($dom[$key]['fontsize'] <= 0) {
                            $dom[$key]['line-height'] = 1;
                        } elseif (\is_numeric($dom[$key]['fontsize'])) {
                            $dom[$key]['line-height'] = ($dom[$key]['line-height'] / floatval($dom[$key]['fontsize']));
                        }
                    }
            }
        }
        // font style
        if (
            isset($dom[$key]['style']['font-weight'])
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
        if (
            isset($dom[$key]['style']['font-style'])
            && \is_string($dom[$key]['fontstyle'])
            && (\strtolower($dom[$key]['style']['font-style'][0]) == 'i')
        ) {
            $dom[$key]['fontstyle'] .= 'I';
        }
        // font color
        if ((!empty($dom[$key]['style']['color']))) {
            $dom[$key]['fgcolor'] = $this->getCSSColor($dom[$key]['style']['color']);
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fgcolor'] = 'blue';
        }
        // background color
        if ((!empty($dom[$key]['style']['background-color']))) {
                $dom[$key]['bgcolor'] = $this->getCSSColor($dom[$key]['style']['background-color']);
        }
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
                        'o '=> 'O',
                        default => '',
                    };
                }
            }
        } elseif ($dom[$key]['value'] == 'a') {
            $dom[$key]['fontstyle'] = 'U';
        }
        // check width attribute
        if (!empty($dom[$key]['style']['width'])) {
            $dom[$key]['width'] = $dom[$key]['style']['width'];
        }
        // check height attribute
        if (!empty($dom[$key]['style']['height'])) {
            $dom[$key]['height'] = $dom[$key]['style']['height'];
        }
        // check text alignment
        if (!empty($dom[$key]['style']['text-align'])) {
            $dom[$key]['align'] = \strtoupper($dom[$key]['style']['text-align'][0]);
        }
        // check CSS border properties
        if (!empty($dom[$key]['style']['border'])) {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
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
        if (isset($dom[$key]['style']['border-width'])) {
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
            $brd_styles = \preg_split('/[\s]+/', trim($dom[$key]['style']['border-style']));
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
        foreach ($cellside as $bsk => $bsv) {
            if (isset($dom[$key]['style']['border-' . $bsv])) {
                $brdr[$bsk] = $this->getCSSBorderStyle($dom[$key]['style']['border-' . $bsv]);
            }
            if (isset($dom[$key]['style']['border-' . $bsv . '-color'])) {
                $brdr[$bsk]['lineColor'] = $this->getCSSColor($dom[$key]['style']['border-' . $bsv . '-color']);
            }
            if (isset($dom[$key]['style']['border-' . $bsv . '-width'])) {
                $brdr[$bsk]['lineWidth'] = $this->getCSSBorderWidth($dom[$key]['style']['border-' . $bsv . '-width']);
            }
            if (isset($dom[$key]['style']['border-' . $bsv . '-style'])) {
                $brdr[$bsk]['dashPhase'] = $this->getCSSBorderDashStyle($dom[$key]['style']['border-' . $bsv . '-style']);
                if ($brdr[$bsk]['dashPhase'] < 0) {
                    $brdr[$bsk] = [];
                }
            }
            if ($brdr[$bsk]['lineWidth'] > 0) {
                // @phpstan-ignore offsetAccess.nonOffsetAccessible
                $dom[$key]['border'][$bsk] = $brdr[$bsk];
            }
        }
        // check for CSS padding properties
        $dom[$key]['padding'] = empty($dom[$key]['style']['padding']) ? 0 : $this->getCSSPadding($dom[$key]['style']['padding']);

        /*
        foreach ($cellside as $psk => $psv) {
            if (isset($dom[$key]['style']['padding-'.$psv])) {
                $dom[$key]['padding'][$psk] = $this->getHTMLUnitToUnits($dom[$key]['style']['padding-'.$psv], 0, 'px', false);
            }
        }
        // check for CSS margin properties
        if (isset($dom[$key]['style']['margin'])) {
            $dom[$key]['margin'] = $this->getCSSMargin($dom[$key]['style']['margin']);
        } else {
            $dom[$key]['margin'] = $this->cell_margin;
        }
        foreach ($cellside as $psk => $psv) {
            if (isset($dom[$key]['style']['margin-'.$psv])) {
                $dom[$key]['margin'][$psk] = $this->getHTMLUnitToUnits(str_replace('auto', '0', $dom[$key]['style']['margin-'.$psv]), 0, 'px', false);
            }
        }
        // check for CSS border-spacing properties
        if (isset($dom[$key]['style']['border-spacing'])) {
            $dom[$key]['border-spacing'] = $this->getCSSBorderMargin($dom[$key]['style']['border-spacing']);
        }
        // page-break-inside
        if (isset($dom[$key]['style']['page-break-inside']) AND ($dom[$key]['style']['page-break-inside'] == 'avoid')) {
            $dom[$key]['attribute']['nobr'] = 'true';
        }
        // page-break-before
        if (isset($dom[$key]['style']['page-break-before'])) {
            if ($dom[$key]['style']['page-break-before'] == 'always') {
                $dom[$key]['attribute']['pagebreak'] = 'true';
            } elseif ($dom[$key]['style']['page-break-before'] == 'left') {
                $dom[$key]['attribute']['pagebreak'] = 'left';
            } elseif ($dom[$key]['style']['page-break-before'] == 'right') {
                $dom[$key]['attribute']['pagebreak'] = 'right';
            }
        }
        // page-break-after
        if (isset($dom[$key]['style']['page-break-after'])) {
            if ($dom[$key]['style']['page-break-after'] == 'always') {
                $dom[$key]['attribute']['pagebreakafter'] = 'true';
            } elseif ($dom[$key]['style']['page-break-after'] == 'left') {
                $dom[$key]['attribute']['pagebreakafter'] = 'left';
            } elseif ($dom[$key]['style']['page-break-after'] == 'right') {
                $dom[$key]['attribute']['pagebreakafter'] = 'right';
            }
        }
        */
    }
}
