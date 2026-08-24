<?php

declare(strict_types=1);

/**
 * ListStyle.php
 *
 * @since     2026-08-24
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

namespace Com\Tecnick\Pdf\CSS;

/**
 * Resolves list marker types and generates the counter text for list items.
 *
 * The HTML "type" attribute values 1/a/A/i/I are case-sensitive and are mapped to the
 * equivalent CSS list-style-type keyword; CSS keywords are case-insensitive.
 * Counters outside the range of their counter style fall back to the decimal counter.
 *
 * @since     2026-08-24
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 */
class ListStyle
{
    /**
     * Maximum number representable with the Roman notation, using two vinculum layers.
     */
    public const ROMAN_LIMIT = 3_999_999_999;

    /**
     * Maps the case-sensitive HTML list "type" attribute values
     * to the equivalent CSS list-style-type keyword.
     *
     * @var array<array-key, string>
     */
    private const TYPE_ATTR = [
        '1' => 'decimal',
        'a' => 'lower-alpha',
        'A' => 'upper-alpha',
        'i' => 'lower-roman',
        'I' => 'upper-roman',
    ];

    /**
     * Maps CSS list-style-type aliases to the keyword used as table key.
     *
     * @var array<string, string>
     */
    private const TYPE_ALIAS = [
        'lower-latin' => 'lower-alpha',
        'upper-latin' => 'upper-alpha',
        'armenian' => 'upper-armenian',
    ];

    /**
     * Internal marker types that are not counter styles:
     * default unordered, default ordered, and no-justification symbols.
     *
     * @var array<string>
     */
    private const TYPE_SENTINEL = [
        '!',
        '#',
        '^',
    ];

    /**
     * List marker types without a counter.
     *
     * @var array<string>
     */
    private const UNORDERED = [
        'circle',
        'disc',
        'none',
        'square',
    ];

    /**
     * Symbols of the alphabetic counter styles, in CSS Counter Styles order.
     *
     * @var array<string, array<string>>
     */
    private const ALPHABETIC = [
        'lower-alpha' => [
            'a',
            'b',
            'c',
            'd',
            'e',
            'f',
            'g',
            'h',
            'i',
            'j',
            'k',
            'l',
            'm',
            'n',
            'o',
            'p',
            'q',
            'r',
            's',
            't',
            'u',
            'v',
            'w',
            'x',
            'y',
            'z',
        ],
        'upper-alpha' => [
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
        ],
        'lower-greek' => [
            "\u{03B1}",
            "\u{03B2}",
            "\u{03B3}",
            "\u{03B4}",
            "\u{03B5}",
            "\u{03B6}",
            "\u{03B7}",
            "\u{03B8}",
            "\u{03B9}",
            "\u{03BA}",
            "\u{03BB}",
            "\u{03BC}",
            "\u{03BD}",
            "\u{03BE}",
            "\u{03BF}",
            "\u{03C0}",
            "\u{03C1}",
            "\u{03C3}",
            "\u{03C4}",
            "\u{03C5}",
            "\u{03C6}",
            "\u{03C7}",
            "\u{03C8}",
            "\u{03C9}",
        ],
        'hiragana' => [
            "\u{3042}",
            "\u{3044}",
            "\u{3046}",
            "\u{3048}",
            "\u{304A}",
            "\u{304B}",
            "\u{304D}",
            "\u{304F}",
            "\u{3051}",
            "\u{3053}",
            "\u{3055}",
            "\u{3057}",
            "\u{3059}",
            "\u{305B}",
            "\u{305D}",
            "\u{305F}",
            "\u{3061}",
            "\u{3064}",
            "\u{3066}",
            "\u{3068}",
            "\u{306A}",
            "\u{306B}",
            "\u{306C}",
            "\u{306D}",
            "\u{306E}",
            "\u{306F}",
            "\u{3072}",
            "\u{3075}",
            "\u{3078}",
            "\u{307B}",
            "\u{307E}",
            "\u{307F}",
            "\u{3080}",
            "\u{3081}",
            "\u{3082}",
            "\u{3084}",
            "\u{3086}",
            "\u{3088}",
            "\u{3089}",
            "\u{308A}",
            "\u{308B}",
            "\u{308C}",
            "\u{308D}",
            "\u{308F}",
            "\u{3090}",
            "\u{3091}",
            "\u{3092}",
            "\u{3093}",
        ],
        'hiragana-iroha' => [
            "\u{3044}",
            "\u{308D}",
            "\u{306F}",
            "\u{306B}",
            "\u{307B}",
            "\u{3078}",
            "\u{3068}",
            "\u{3061}",
            "\u{308A}",
            "\u{306C}",
            "\u{308B}",
            "\u{3092}",
            "\u{308F}",
            "\u{304B}",
            "\u{3088}",
            "\u{305F}",
            "\u{308C}",
            "\u{305D}",
            "\u{3064}",
            "\u{306D}",
            "\u{306A}",
            "\u{3089}",
            "\u{3080}",
            "\u{3046}",
            "\u{3090}",
            "\u{306E}",
            "\u{304A}",
            "\u{304F}",
            "\u{3084}",
            "\u{307E}",
            "\u{3051}",
            "\u{3075}",
            "\u{3053}",
            "\u{3048}",
            "\u{3066}",
            "\u{3042}",
            "\u{3055}",
            "\u{304D}",
            "\u{3086}",
            "\u{3081}",
            "\u{307F}",
            "\u{3057}",
            "\u{3091}",
            "\u{3072}",
            "\u{3082}",
            "\u{305B}",
            "\u{3059}",
        ],
        'katakana' => [
            "\u{30A2}",
            "\u{30A4}",
            "\u{30A6}",
            "\u{30A8}",
            "\u{30AA}",
            "\u{30AB}",
            "\u{30AD}",
            "\u{30AF}",
            "\u{30B1}",
            "\u{30B3}",
            "\u{30B5}",
            "\u{30B7}",
            "\u{30B9}",
            "\u{30BB}",
            "\u{30BD}",
            "\u{30BF}",
            "\u{30C1}",
            "\u{30C4}",
            "\u{30C6}",
            "\u{30C8}",
            "\u{30CA}",
            "\u{30CB}",
            "\u{30CC}",
            "\u{30CD}",
            "\u{30CE}",
            "\u{30CF}",
            "\u{30D2}",
            "\u{30D5}",
            "\u{30D8}",
            "\u{30DB}",
            "\u{30DE}",
            "\u{30DF}",
            "\u{30E0}",
            "\u{30E1}",
            "\u{30E2}",
            "\u{30E4}",
            "\u{30E6}",
            "\u{30E8}",
            "\u{30E9}",
            "\u{30EA}",
            "\u{30EB}",
            "\u{30EC}",
            "\u{30ED}",
            "\u{30EF}",
            "\u{30F0}",
            "\u{30F1}",
            "\u{30F2}",
            "\u{30F3}",
        ],
        'katakana-iroha' => [
            "\u{30A4}",
            "\u{30ED}",
            "\u{30CF}",
            "\u{30CB}",
            "\u{30DB}",
            "\u{30D8}",
            "\u{30C8}",
            "\u{30C1}",
            "\u{30EA}",
            "\u{30CC}",
            "\u{30EB}",
            "\u{30F2}",
            "\u{30EF}",
            "\u{30AB}",
            "\u{30E8}",
            "\u{30BF}",
            "\u{30EC}",
            "\u{30BD}",
            "\u{30C4}",
            "\u{30CD}",
            "\u{30CA}",
            "\u{30E9}",
            "\u{30E0}",
            "\u{30A6}",
            "\u{30F0}",
            "\u{30CE}",
            "\u{30AA}",
            "\u{30AF}",
            "\u{30E4}",
            "\u{30DE}",
            "\u{30B1}",
            "\u{30D5}",
            "\u{30B3}",
            "\u{30A8}",
            "\u{30C6}",
            "\u{30A2}",
            "\u{30B5}",
            "\u{30AD}",
            "\u{30E6}",
            "\u{30E1}",
            "\u{30DF}",
            "\u{30B7}",
            "\u{30F1}",
            "\u{30D2}",
            "\u{30E2}",
            "\u{30BB}",
            "\u{30B9}",
        ],
    ];

    /**
     * Number to symbol pairs of the additive counter styles, in descending order.
     *
     * @var array<string, array<int, string>>
     */
    private const ADDITIVE = [
        'hebrew' => [
            10_000 => "\u{05D9}\u{05F3}",
            9000 => "\u{05D8}\u{05F3}",
            8000 => "\u{05D7}\u{05F3}",
            7000 => "\u{05D6}\u{05F3}",
            6000 => "\u{05D5}\u{05F3}",
            5000 => "\u{05D4}\u{05F3}",
            4000 => "\u{05D3}\u{05F3}",
            3000 => "\u{05D2}\u{05F3}",
            2000 => "\u{05D1}\u{05F3}",
            1000 => "\u{05D0}\u{05F3}",
            400 => "\u{05EA}",
            300 => "\u{05E9}",
            200 => "\u{05E8}",
            100 => "\u{05E7}",
            90 => "\u{05E6}",
            80 => "\u{05E4}",
            70 => "\u{05E2}",
            60 => "\u{05E1}",
            50 => "\u{05E0}",
            40 => "\u{05DE}",
            30 => "\u{05DC}",
            20 => "\u{05DB}",
            19 => "\u{05D9}\u{05D8}",
            18 => "\u{05D9}\u{05D7}",
            17 => "\u{05D9}\u{05D6}",
            16 => "\u{05D8}\u{05D6}",
            15 => "\u{05D8}\u{05D5}",
            10 => "\u{05D9}",
            9 => "\u{05D8}",
            8 => "\u{05D7}",
            7 => "\u{05D6}",
            6 => "\u{05D5}",
            5 => "\u{05D4}",
            4 => "\u{05D3}",
            3 => "\u{05D2}",
            2 => "\u{05D1}",
            1 => "\u{05D0}",
        ],
        'upper-armenian' => [
            9000 => "\u{0554}",
            8000 => "\u{0553}",
            7000 => "\u{0552}",
            6000 => "\u{0551}",
            5000 => "\u{0550}",
            4000 => "\u{054F}",
            3000 => "\u{054E}",
            2000 => "\u{054D}",
            1000 => "\u{054C}",
            900 => "\u{054B}",
            800 => "\u{054A}",
            700 => "\u{0549}",
            600 => "\u{0548}",
            500 => "\u{0547}",
            400 => "\u{0546}",
            300 => "\u{0545}",
            200 => "\u{0544}",
            100 => "\u{0543}",
            90 => "\u{0542}",
            80 => "\u{0541}",
            70 => "\u{0540}",
            60 => "\u{053F}",
            50 => "\u{053E}",
            40 => "\u{053D}",
            30 => "\u{053C}",
            20 => "\u{053B}",
            10 => "\u{053A}",
            9 => "\u{0539}",
            8 => "\u{0538}",
            7 => "\u{0537}",
            6 => "\u{0536}",
            5 => "\u{0535}",
            4 => "\u{0534}",
            3 => "\u{0533}",
            2 => "\u{0532}",
            1 => "\u{0531}",
        ],
        'lower-armenian' => [
            9000 => "\u{0584}",
            8000 => "\u{0583}",
            7000 => "\u{0582}",
            6000 => "\u{0581}",
            5000 => "\u{0580}",
            4000 => "\u{057F}",
            3000 => "\u{057E}",
            2000 => "\u{057D}",
            1000 => "\u{057C}",
            900 => "\u{057B}",
            800 => "\u{057A}",
            700 => "\u{0579}",
            600 => "\u{0578}",
            500 => "\u{0577}",
            400 => "\u{0576}",
            300 => "\u{0575}",
            200 => "\u{0574}",
            100 => "\u{0573}",
            90 => "\u{0572}",
            80 => "\u{0571}",
            70 => "\u{0570}",
            60 => "\u{056F}",
            50 => "\u{056E}",
            40 => "\u{056D}",
            30 => "\u{056C}",
            20 => "\u{056B}",
            10 => "\u{056A}",
            9 => "\u{0569}",
            8 => "\u{0568}",
            7 => "\u{0567}",
            6 => "\u{0566}",
            5 => "\u{0565}",
            4 => "\u{0564}",
            3 => "\u{0563}",
            2 => "\u{0562}",
            1 => "\u{0561}",
        ],
        'georgian' => [
            10_000 => "\u{10F5}",
            9000 => "\u{10F0}",
            8000 => "\u{10EF}",
            7000 => "\u{10F4}",
            6000 => "\u{10EE}",
            5000 => "\u{10ED}",
            4000 => "\u{10EC}",
            3000 => "\u{10EB}",
            2000 => "\u{10EA}",
            1000 => "\u{10E9}",
            900 => "\u{10E8}",
            800 => "\u{10E7}",
            700 => "\u{10E6}",
            600 => "\u{10E5}",
            500 => "\u{10E4}",
            400 => "\u{10F3}",
            300 => "\u{10E2}",
            200 => "\u{10E1}",
            100 => "\u{10E0}",
            90 => "\u{10DF}",
            80 => "\u{10DE}",
            70 => "\u{10DD}",
            60 => "\u{10F2}",
            50 => "\u{10DC}",
            40 => "\u{10DB}",
            30 => "\u{10DA}",
            20 => "\u{10D9}",
            10 => "\u{10D8}",
            9 => "\u{10D7}",
            8 => "\u{10F1}",
            7 => "\u{10D6}",
            6 => "\u{10D5}",
            5 => "\u{10D4}",
            4 => "\u{10D3}",
            3 => "\u{10D2}",
            2 => "\u{10D1}",
            1 => "\u{10D0}",
        ],
        'cjk-ideographic' => [
            9000 => "\u{4E5D}\u{5343}",
            8000 => "\u{516B}\u{5343}",
            7000 => "\u{4E03}\u{5343}",
            6000 => "\u{516D}\u{5343}",
            5000 => "\u{4E94}\u{5343}",
            4000 => "\u{56DB}\u{5343}",
            3000 => "\u{4E09}\u{5343}",
            2000 => "\u{4E8C}\u{5343}",
            1000 => "\u{5343}",
            900 => "\u{4E5D}\u{767E}",
            800 => "\u{516B}\u{767E}",
            700 => "\u{4E03}\u{767E}",
            600 => "\u{516D}\u{767E}",
            500 => "\u{4E94}\u{767E}",
            400 => "\u{56DB}\u{767E}",
            300 => "\u{4E09}\u{767E}",
            200 => "\u{4E8C}\u{767E}",
            100 => "\u{767E}",
            90 => "\u{4E5D}\u{5341}",
            80 => "\u{516B}\u{5341}",
            70 => "\u{4E03}\u{5341}",
            60 => "\u{516D}\u{5341}",
            50 => "\u{4E94}\u{5341}",
            40 => "\u{56DB}\u{5341}",
            30 => "\u{4E09}\u{5341}",
            20 => "\u{4E8C}\u{5341}",
            10 => "\u{5341}",
            9 => "\u{4E5D}",
            8 => "\u{516B}",
            7 => "\u{4E03}",
            6 => "\u{516D}",
            5 => "\u{4E94}",
            4 => "\u{56DB}",
            3 => "\u{4E09}",
            2 => "\u{4E8C}",
            1 => "\u{4E00}",
        ],
    ];

    /**
     * Maps Roman Vinculum symbols to number multipliers.
     *
     * @var array<string, int>
     */
    private const ROMAN_VINCULUM = [
        "\u{033F}" => 1_000_000,
        "\u{0305}" => 1_000,
        '' => 1,
    ];

    /**
     * Maps Roman symbols to numbers.
     *
     * @var array<string, int>
     */
    private const ROMAN_SYMBOL = [
        'M' => 1_000,
        'CM' => 900,
        'D' => 500,
        'CD' => 400,
        'C' => 100,
        'XC' => 90,
        'L' => 50,
        'XL' => 40,
        'X' => 10,
        'IX' => 9,
        'V' => 5,
        'IV' => 4,
    ];

    /**
     * Highest number each counter style can represent.
     * Counters start at 1: numbers outside that range use the decimal counter.
     *
     * @var array<string, int>
     */
    private const COUNTER_MAX = [
        'cjk-ideographic' => 9_999,
        'georgian' => 19_999,
        'hebrew' => 10_999,
        'hiragana' => PHP_INT_MAX,
        'hiragana-iroha' => PHP_INT_MAX,
        'katakana' => PHP_INT_MAX,
        'katakana-iroha' => PHP_INT_MAX,
        'lower-alpha' => PHP_INT_MAX,
        'lower-armenian' => 9_999,
        'lower-greek' => PHP_INT_MAX,
        'lower-roman' => self::ROMAN_LIMIT,
        'upper-alpha' => PHP_INT_MAX,
        'upper-armenian' => 9_999,
        'upper-roman' => self::ROMAN_LIMIT,
    ];

    /**
     * Maps the counter styles to the PDF ListNumbering values.
     *
     * @var array<string, string>
     */
    private const PDFUA_NUMBERING = [
        'circle' => 'Circle',
        'cjk-ideographic' => 'Decimal',
        'decimal' => 'Decimal',
        'decimal-leading-zero' => 'Decimal',
        'disc' => 'Disc',
        'georgian' => 'Decimal',
        'hebrew' => 'Decimal',
        'hiragana' => 'Decimal',
        'hiragana-iroha' => 'Decimal',
        'katakana' => 'Decimal',
        'katakana-iroha' => 'Decimal',
        'lower-alpha' => 'LowerAlpha',
        'lower-armenian' => 'Decimal',
        'lower-greek' => 'Decimal',
        'lower-roman' => 'LowerRoman',
        'none' => 'None',
        'square' => 'Square',
        'upper-alpha' => 'UpperAlpha',
        'upper-armenian' => 'Decimal',
        'upper-roman' => 'UpperRoman',
    ];

    /**
     * Returns the canonical list-style-type keyword for a list marker type.
     * Internal sentinels and image markers are returned unchanged.
     *
     * @param string $type List marker type.
     */
    public static function resolveType(string $type): string
    {
        $type = \trim($type);
        if ($type === '' || \in_array($type, self::TYPE_SENTINEL, true) || \str_starts_with($type, 'img|')) {
            return $type;
        }

        if (isset(self::TYPE_ATTR[$type])) {
            return self::TYPE_ATTR[$type];
        }

        $type = \strtolower($type);

        return self::TYPE_ALIAS[$type] ?? $type;
    }

    /**
     * Returns true when the list marker type has no counter.
     *
     * @param string $type List marker type.
     */
    public static function isUnordered(string $type): bool
    {
        return \in_array(self::resolveType($type), self::UNORDERED, true);
    }

    /**
     * Returns true when the list marker type is a supported bullet or counter style.
     *
     * @param string $type List marker type.
     */
    public static function isKnownType(string $type): bool
    {
        return self::isKnownStyle(self::resolveType($type));
    }

    /**
     * Returns the canonical keyword of a supported bullet or counter style,
     * or an empty string for any other value.
     *
     * @param string $type List marker type.
     */
    public static function resolveKnownType(string $type): string
    {
        $style = self::resolveType($type);

        return self::isKnownStyle($style) ? $style : '';
    }

    /**
     * Returns true when the canonical keyword is a supported bullet or counter style.
     *
     * @param string $style Canonical list-style-type keyword.
     */
    private static function isKnownStyle(string $style): bool
    {
        return (
            isset(self::COUNTER_MAX[$style])
            || \in_array($style, self::UNORDERED, true)
            || $style === 'decimal'
            || $style === 'decimal-leading-zero'
        );
    }

    /**
     * Returns the counter text for a list item, without the marker suffix.
     * Numbers outside the range of the counter style use the decimal counter.
     *
     * @param string $type  List marker type.
     * @param int    $count List entry position, starting from 1.
     */
    public static function counterText(string $type, int $count): string
    {
        $style = self::resolveType($type);

        if ($style === 'decimal-leading-zero') {
            return self::decimalLeadingZero($count);
        }

        $max = self::COUNTER_MAX[$style] ?? 0;
        if ($count < 1 || $count > $max) {
            return \strval($count);
        }

        if (isset(self::ALPHABETIC[$style])) {
            return self::alphabetic($count, self::ALPHABETIC[$style]);
        }

        if (isset(self::ADDITIVE[$style])) {
            return self::additive($count, self::ADDITIVE[$style]);
        }

        if ($style === 'lower-roman') {
            return \strtolower(self::roman($count));
        }

        if ($style === 'upper-roman') {
            return self::roman($count);
        }

        return \strval($count);
    }

    /**
     * Returns the PDF ListNumbering value for a list marker type.
     *
     * @param string $type List marker type.
     * @param string $tag  HTML tag name of the list element.
     */
    public static function pdfUaNumbering(string $type, string $tag): string
    {
        $numbering = self::PDFUA_NUMBERING[self::resolveType($type)] ?? '';
        if ($numbering !== '') {
            return $numbering;
        }

        return match (\strtolower(\trim($tag))) {
            'ul' => 'Disc',
            'ol' => 'Decimal',
            default => '',
        };
    }

    /**
     * Returns the Roman representation of an integer number.
     * Roman standard notation can represent numbers up to 3,999.
     * For bigger numbers, up to two layers of the "vinculum" notation
     * are used for a max value of 3,999,999,999.
     *
     * @param int $num Number to convert.
     */
    public static function roman(int $num): string
    {
        if ($num < 1 || $num > self::ROMAN_LIMIT) {
            return \strval($num);
        }

        $rmn = '';
        foreach (self::ROMAN_VINCULUM as $sfx => $mul) {
            foreach (self::ROMAN_SYMBOL as $sym => $val) {
                $limit = $mul * $val;
                while ($num >= $limit) {
                    $rmn .= $sym[0] . $sfx . (\strlen($sym) > 1 ? $sym[1] . $sfx : '');
                    $num -= $limit;
                }
            }
        }

        while ($num >= 1) {
            $rmn .= 'I';
            --$num;
        }

        return $rmn;
    }

    /**
     * Returns the alphabetic representation of an integer number:
     * the symbols in order, then two-symbol sequences, and so on.
     *
     * @param int           $num     Number to convert, counting from 1.
     * @param array<string> $symbols Counter symbols, in order.
     */
    private static function alphabetic(int $num, array $symbols): string
    {
        $base = \count($symbols);
        if ($num < 1 || $base < 1) {
            return \strval($num);
        }

        $out = '';
        while ($num > 0) {
            --$num;
            $out = ($symbols[$num % $base] ?? '') . $out;
            $num = \intdiv($num, $base);
        }

        return $out;
    }

    /**
     * Returns the additive representation of an integer number.
     *
     * @param int               $num   Number to convert, counting from 1.
     * @param array<int,string> $table Number to symbol pairs, in descending order.
     */
    private static function additive(int $num, array $table): string
    {
        $out = '';
        foreach ($table as $value => $symbol) {
            if ($value < 1) {
                continue;
            }

            while ($num >= $value) {
                $out .= $symbol;
                $num -= $value;
            }
        }

        return $out;
    }

    /**
     * Returns the decimal representation of an integer number,
     * with the digits padded to two characters.
     *
     * @param int $num Number to convert.
     */
    private static function decimalLeadingZero(int $num): string
    {
        $digits = \ltrim(\strval($num), '-');
        if (\strlen($digits) < 2) {
            $digits = '0' . $digits;
        }

        return ($num < 0 ? '-' : '') . $digits;
    }
}
