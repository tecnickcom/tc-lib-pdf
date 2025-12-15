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
            'border' => 0.0,
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
                    $this->processHTMLDOMOpeningTag($dom, $css, $level, $element, $key, $parent, $thead);
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
            if (!empty($ttm[$dom[$parent]['text-transform']])) {
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
     * @param string $element Element data.
     * @param int $key Current element ID.
     * @param int $parent ID of the parent element.
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
                $dom[$parent]['content'] .= \stripslashes($elm[$dom[$idx]['elkey']]);
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
            if (empty($dom[$granparent]['thead'])) {
                $dom[$granparent]['thead'] = $cssarray . $elm[$dom[$granparent]['elkey']];
            }
            for ($idx = $parent; $idx <= $key; ++$idx) {
                $dom[$granparent]['thead'] .= $elm[$dom[$idx]['elkey']];
            }
            if (!isset($dom[$parent]['attribute'])) {
                $dom[$parent]['attribute'] = [];
            }
            // header elements must be always contained in a single page
            $dom[$parent]['attribute']['nobr'] = 'true';
        }
        if (($dom[$key]['value'] == 'table') && (!empty($dom[$parent]['thead']))) {
            // remove the nobr attributes from the table header
            $dom[$parent]['thead'] = str_replace(' nobr="true"', '', $dom[$parent]['thead']);
            $dom[$parent]['thead'] .= '</tablehead>';
        }
    }

    /**
     * Process HTML DOM Opening Tag.
     *
     * @param array<int, array<string, mixed>> $dom
     * @param array<string, mixed> $css
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
            \list($dom[$key]['csssel'], $dom[$key]['cssdata']) = $this->getHTMLDOMCSSData($dom, $css, $key);
            $dom[$key]['attribute']['style'] = $this->implodeCSSData($dom[$key]['cssdata']);
        }

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
     * @return array CSS properties
     */
    public function getHTMLDOMCSSData(array $dom, array $css, int $key): array
    {
        $ret = [];
        // get parent CSS selectors
        $selectors = [];
        if (!empty($dom[($dom[$key]['parent'])]['csssel'])) {
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
        return [$selectors, $cssordered];
    }

    /**
     * Returns true if the CSS selector is valid for the selected HTML tag.
     *
     * @param array $dom array of HTML tags and properties
     * @param int $key key of the current HTML tag
     * @param string $selector CSS selector string
     *
     * @return bool True if the selector is valid, false otherwise
     */
    public function isValidCSSSelectorForTag(array $dom, int $key, int $selector): bool
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
        $selector = \preg_replace('/([\>\+\~\s]{1})([\.]{1})([^\>\+\~\s]*)/si', '\\1*.\\3', $selector) ?? '';
        $matches = [];
        if (empty(\preg_match_all('/([\>\+\~\s]{1})([a-zA-Z0-9\*]+)([^\>\+\~\s]*)/si', $selector, $matches, PREG_PATTERN_ORDER | PREG_OFFSET_CAPTURE))) {
            return $ret;
        }
        $parentop = \array_pop($matches[1]);
        $operator = $parentop[0];
        $offset = $parentop[1];
        $lasttag = \array_pop($matches[2]);
        $lasttag = \strtolower(\trim($lasttag[0]));
        if (($lasttag !== '*') && ($lasttag !== $tag)) {
            return $ret;
        }
        // the last element on selector is our tag or 'any tag'
        $attrib = \array_pop($matches[3]);
        $attrib = \strtolower(\trim($attrib[0]));
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
                    if (isset($dom[$key]['attribute'][$att])) {
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
                                || (preg_match('/' . $val . '[\-]{1}/i', $dom[$key]['attribute'][$att]) > 0));
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

        if ($ret && ($offset > 0)) {
            $ret = false;
            // check remaining selector part
            $selector = \substr($selector, 0, $offset);
            switch ($operator) {
                case ' ': // descendant of an element
                    while ($dom[$key]['parent'] > 0) {
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
}
