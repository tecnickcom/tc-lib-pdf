<?php

declare(strict_types=1);

/**
 * JavaScript.php
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
 * Com\Tecnick\Pdf\JavaScript
 *
 * JavaScript PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TAnnotOpts from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TGTransparency from \Com\Tecnick\Pdf\Base
 * @phpstan-import-type TXOBject from \Com\Tecnick\Pdf\Base
 *
 * @phpstan-type TRadioButtonItem array{
 *         'n': int,
 *         'def': string,
 *     }
 *
 * @phpstan-type TRadioButton array{
 *         'n': int,
 *         '#readonly#': bool,
 *         'kids': array<TRadioButtonItem>,
 *     }
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class JavaScript extends \Com\Tecnick\Pdf\CSS
{
    /**
     * Interactive annotation subtypes disallowed in PDF/X mode.
     *
     * @var array<int, string>
     */
    protected const PDFX_BLOCKED_ANNOT_SUBTYPES = [
        'widget',
        'screen',
        'movie',
        'sound',
        'fileattachment',
        '3d',
    ];

    /**
     * Fonts used in annotations.
     *
     * @var array<string, int>
     */
    protected array $annotation_fonts = [];

    /**
     * Destinations.
     *
     * @var array<string, array{
     *          'p': int,
     *          'x': float,
     *          'y': float,
     *      }>
     */
    protected array $dests = [];

    /**
     * Links.
     *
     * @var array<string, array{
     *          'p': int,
     *          'y': float,
     *      }>
     */
    protected array $links = [];

    /**
     * Radio Button Groups.
     *
     * @var array<string, TRadioButton>
     */
    protected array $radiobuttons = [];

    /**
     * Javascript block to add.
     */
    protected string $javascript = '';

    /**
     * Javascript objects.
     *
     * @var array<int, array{
     *          'n': int,
     *          'js': string,
     *          'onload': bool,
     *      }>
     */
    protected array $jsobjects = [];

    /**
     * Valid AFRelationship values for PDF/A-3 embedded files.
     *
     * @var array<string>
     */
    protected const VALID_AF_RELATIONSHIPS = [
        'Source',
        'Data',
        'Alternative',
        'Supplement',
        'Unspecified',
    ];

    /**
     * Deafult Javascript Annotation properties.
     * Possible values are described on official Javascript for Acrobat API reference.
     * Annotation options can be directly specified using the 'aopt' entry.
     *
     * @var array<string, mixed>
     */
    protected array $defJSAnnotProp = [
        'lineWidth' => 1, // 1=thin
        'borderStyle' => 'solid',
        'fillColor' => 'white',
        'strokeColor' => 'grey',
    ];

    /**
     * Append raw javascript string to the global one.
     *
     * @param string $script Raw Javascript string.
     *
     * @return void
     */
    public function appendRawJavaScript(string $script): void
    {
        if ($this->pdfa > 0 || $this->pdfx || $this->pdfuaMode !== '') {
            return;
        }

        $this->javascript .= $script;
    }

    /**
     * Add a raw javascript string as new object.
     *
     * @param string $script Raw Javascript string.
     * @param bool $onload Set to true to execute the script when opening the document.
     *
     * @return int PDF object ID or -1 in case of error.
     */
    public function addRawJavaScriptObj(string $script, bool $onload = false): int
    {
        if ($this->pdfa > 0 || $this->pdfx || $this->pdfuaMode !== '') {
            return -1;
        }
        $oid = ++$this->pon;
        $this->jsobjects[] = [
            'n' => $oid,
            'js' => $script,
            'onload' => $onload,
        ];
        return $oid;
    }

    /**
     * Adds a JavaScript field.
     * Requires Acrobat Writer or an alternative PDF reader with Javascript support.
     *
     * @param string $type field type: button, checkbox, combobox, listbox, radiobutton, text.
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<string, string> $prop javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    protected function addJavaScriptField(
        string $type,
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $prop,
    ): void {
        $page = $this->page->getPage();
        /** @var array{num: int, pheight: float} $page */
        $curfont = $this->font->getCurrentFont();
        /** @var array{size: float} $curfont */
        $pageNum = $page['num'];
        $pageHeight = $page['pheight'];
        $fontSize = $curfont['size'];
        // avoid fields duplication after saving the document
        $this->javascript .= \sprintf(
            "if (getField('tcpdfdocsaved').value != 'saved') {f%s=this.addField('%s','%s',%u,[%F,%F,%F,%F]);\nf%s.textSize=%s;\n",
            $name,
            $name,
            $type,
            $pageNum - 1,
            $this->toPoints($posx),
            $this->toYPoints($posy, $pageHeight) + 1.0,
            $this->toPoints($posx + $width),
            $this->toYPoints($posy + $height, $pageHeight) + 1.0,
            $name,
            $fontSize,
        );
        foreach ($prop as $key => $val) {
            if (\strcmp(\substr($key, -5), 'Color') === 0) {
                $color = $this->color->getColorObj($val);
                $val = $color === null ? '' : $color->getJsPdfColor();
            } else {
                $val = "'" . $val . "'";
            }
            $this->javascript .= 'f' . $name . '.' . $key . '=' . $val . ";\n";
        }
        $this->javascript .= '}';
    }

    /**
     * Set the default Javascript Annotation properties.
     * Possible values are described on official Javascript for Acrobat API reference.
     * Annotation options can be directly specified using the 'aopt' entry.
     *
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function setDefJSAnnotProp(array $data): void
    {
        $this->defJSAnnotProp = $data;
    }

    /**
     * Returns the default Javascript Annotation properties.
     * Possible values are described on official Javascript for Acrobat API reference.
     *
     * @return array<string, mixed>
     */
    public function getDefJSAnnotProp(): array
    {
        return $this->defJSAnnotProp;
    }

    /**
     * Convert Javascript Annotation properties to Annotation options.
     *
     * @param array<string, mixed> $prp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return TAnnotOpts Annotation properties.
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getAnnotOptFromJSProp(array $prp): array
    {
        /** @var TAnnotOpts $opt */
        $opt = [];
        if ($prp === []) {
            return $opt;
        }
        if (isset($prp['aopt']) && \is_array($prp['aopt'])) {
            // the annotation options are already defined
            /** @var TAnnotOpts */
            return \array_merge($opt, $prp['aopt']);
        }
        // alignment: Controls how the text is laid out within the text field.
        if (isset($prp['alignment'])) {
            $opt['q'] = match ($prp['alignment']) {
                'left' => 0,
                'center' => 1,
                'right' => 2,
                default => $this->rtl ? 2 : 0,
            };
        }
        // lineWidth:
        // Specifies the thickness of the border when stroking the perimeter of a field's rectangle.
        $linewidth = isset($prp['lineWidth']) && \is_numeric($prp['lineWidth']) ? \intval($prp['lineWidth']) : 1;
        // borderStyle: The border style for a field.
        if (isset($prp['borderStyle'])) {
            switch ($prp['borderStyle']) {
                case 'border.d':
                case 'dashed':
                    $opt['border'] = [0, 0, $linewidth, [3, 2]];
                    $opt['bs'] = ['w' => $linewidth, 's' => 'D', 'd' => [3, 2]];
                    break;
                case 'border.b':
                case 'beveled':
                    $opt['border'] = [0, 0, $linewidth];
                    $opt['bs'] = ['w' => $linewidth, 's' => 'B'];
                    break;
                case 'border.i':
                case 'inset':
                    $opt['border'] = [0, 0, $linewidth];
                    $opt['bs'] = ['w' => $linewidth, 's' => 'I'];
                    break;
                case 'border.u':
                case 'underline':
                    $opt['border'] = [0, 0, $linewidth];
                    $opt['bs'] = ['w' => $linewidth, 's' => 'U'];
                    break;
                case 'border.s':
                case 'solid':
                    $opt['border'] = [0, 0, $linewidth];
                    $opt['bs'] = ['w' => $linewidth, 's' => 'S'];
                    break;
            }
        }
        if (isset($prp['border']) && \is_array($prp['border'])) {
            $opt['border'] = $prp['border'];
        }
        $mkifAlign = [0.5, 0.5];
        /** @var array<array-key, mixed> $mkif */
        $mkif = [];
        // buttonAlignX:
        // Controls how space is distributed from the left of the button face with respect to the icon.
        if (isset($prp['buttonAlignX'])) {
            $mkifAlign[0] = $prp['buttonAlignX'];
        }
        // buttonAlignY:
        // Controls how unused space is distributed from the bottom of the button face with respect to the icon.
        if (isset($prp['buttonAlignY'])) {
            $mkifAlign[1] = $prp['buttonAlignY'];
        }
        $mkif['a'] = $mkifAlign;
        // buttonFitBounds:
        // If true, the extent to which the icon may be scaled is set to the bounds of the button field.
        if (
            isset($prp['buttonFitBounds'])
            && \is_string($prp['buttonFitBounds'])
            && $prp['buttonFitBounds'] === 'true'
        ) {
            $mkif['fb'] = true;
        }
        // buttonScaleHow:
        // Controls how the icon is scaled (if necessary) to fit inside the button face.
        if (isset($prp['buttonScaleHow'])) {
            $mkif['s'] = match ($prp['buttonScaleHow']) {
                'scaleHow.proportional' => 'P',
                'scaleHow.anamorphic' => 'A',
                default => 'P',
            };
        }
        // buttonScaleWhen:
        // Controls when an icon is scaled to fit inside the button face.
        if (isset($prp['buttonScaleWhen'])) {
            $mkif['sw'] = match ($prp['buttonScaleWhen']) {
                'scaleWhen.always' => 'A',
                'scaleWhen.never' => 'N',
                'scaleWhen.tooBig' => 'B',
                'scaleWhen.tooSmall' => 'S',
                default => 'N',
            };
        }

        /** @var array<array-key, mixed> $mk */
        $mk = ['if' => $mkif];
        // buttonPosition:
        // Controls how the text and the icon of the button are positioned with respect to each other
        //  within the button face.
        if (isset($prp['buttonPosition'])) {
            if (\is_numeric($prp['buttonPosition'])) {
                $mktp = \intval($prp['buttonPosition']);
                if ($mktp >= 0 && $mktp <= 6) {
                    $mk['tp'] = $mktp;
                }
            } else {
                $mk['tp'] = match ($prp['buttonPosition']) {
                    'position.textOnly' => 0,
                    'position.iconOnly' => 1,
                    'position.iconTextV' => 2,
                    'position.textIconV' => 3,
                    'position.iconTextH' => 4,
                    'position.textIconH' => 5,
                    'position.overlay' => 6,
                    default => 0,
                };
            }
        }
        // fillColor:
        // Specifies the background color for a field.
        if (isset($prp['fillColor'])) {
            if (\is_array($prp['fillColor'])) {
                $mk['bg'] = $prp['fillColor'];
            } elseif (\is_string($prp['fillColor'])) {
                $fillColor = $this->color->getColorObj($prp['fillColor']);
                if ($fillColor !== null) {
                    $mk['bg'] = $fillColor->getPDFacArray();
                }
            }
        }
        // strokeColor:
        // Specifies the stroke color for a field that is used to stroke the rectangle of the field
        // with a line as large as the line width.
        if (isset($prp['strokeColor'])) {
            if (\is_array($prp['strokeColor'])) {
                $mk['bc'] = $prp['strokeColor'];
            } elseif (\is_string($prp['strokeColor'])) {
                $strokeColor = $this->color->getColorObj($prp['strokeColor']);
                if ($strokeColor !== null) {
                    $mk['bc'] = $strokeColor->getPDFacArray();
                }
            }
        }
        // rotation:
        // The rotation of a widget in counterclockwise increments.
        if (isset($prp['rotation'])) {
            $mk['r'] = $prp['rotation'];
        }

        if ($mk !== []) {
            $opt['mk'] = $mk;
        }
        // charLimit:
        // Limits the number of characters that a user can type into a text field.
        if (isset($prp['charLimit']) && \is_numeric($prp['charLimit'])) {
            $opt['maxlen'] = intval($prp['charLimit']);
        }
        $flg = 0;
        // readonly:
        // The read-only characteristic of a field.
        // If a field is read-only, the user can see the field but cannot change it.
        if (isset($prp['readonly']) && $prp['readonly'] === 'true') {
            $flg += 1;
        }
        // required:
        // Specifies whether a field requires a value.
        if (isset($prp['required']) && $prp['required'] === 'true') {
            $flg += 1 << 1;
        }
        // multiline:
        // Controls how text is wrapped within the field.
        if (isset($prp['multiline']) && $prp['multiline'] === 'true') {
            $flg += 1 << 12;
        }
        // password:
        // Specifies whether the field should display asterisks when data is entered in the field.
        if (isset($prp['password']) && $prp['password'] === 'true') {
            $flg += 1 << 13;
        }
        // NoToggleToOff:
        // If set, exactly one radio button shall be selected at all times;
        // selecting the currently selected button has no effect.
        if (isset($prp['NoToggleToOff']) && $prp['NoToggleToOff'] === 'true') {
            $flg += 1 << 14;
        }
        // Radio:
        // If set, the field is a set of radio buttons.
        if (isset($prp['Radio']) && $prp['Radio'] === 'true') {
            $flg += 1 << 15;
        }
        // Pushbutton:
        // If set, the field is a pushbutton that does not retain a permanent value.
        if (isset($prp['Pushbutton']) && $prp['Pushbutton'] === 'true') {
            $flg += 1 << 16;
        }
        // Combo:
        // If set, the field is a combo box; if clear, the field is a list box.
        if (isset($prp['Combo']) && $prp['Combo'] === 'true') {
            $flg += 1 << 17;
        }
        // editable:
        // Controls whether a combo box is editable.
        if (isset($prp['editable']) && $prp['editable'] === 'true') {
            $flg += 1 << 18;
        }
        // Sort:
        // If set, the field's option items shall be sorted alphabetically.
        if (isset($prp['Sort']) && $prp['Sort'] === 'true') {
            $flg += 1 << 19;
        }
        // fileSelect:
        // If true, sets the file-select flag in the Options tab of the text field
        // (Field is Used for File Selection).
        if (isset($prp['fileSelect']) && $prp['fileSelect'] === 'true') {
            $flg += 1 << 20;
        }
        // multipleSelection:
        // If true, indicates that a list box allows a multiple selection of items.
        if (isset($prp['multipleSelection']) && $prp['multipleSelection'] === 'true') {
            $flg += 1 << 21;
        }
        // doNotSpellCheck:
        // If true, spell checking is not performed on this editable text field.
        if (isset($prp['doNotSpellCheck']) && $prp['doNotSpellCheck'] === 'true') {
            $flg += 1 << 22;
        }
        // doNotScroll:
        // If true, the text field does not scroll and the user,
        // therefore, is limited by the rectangular region designed for the field.
        if (isset($prp['doNotScroll']) && $prp['doNotScroll'] === 'true') {
            $flg += 1 << 23;
        }
        // comb:
        // If set to true, the field background is drawn as series of boxes
        // (one for each character in the value of the field) and each character
        // of the content is drawn within those boxes.
        // The number of boxes drawn is determined from the charLimit property.
        // It applies only to text fields.
        // The setter will also raise if any of the following field properties are also set multiline,
        // password, and fileSelect.
        // A side-effect of setting this property is that the doNotScroll property is also set.
        if (isset($prp['comb']) && $prp['comb'] === 'true') {
            $flg += 1 << 24;
        }
        // radiosInUnison:
        // If false, even if a group of radio buttons have the same name and export value,
        // they behave in a mutually exclusive fashion, like HTML radio buttons.
        if (isset($prp['radiosInUnison']) && $prp['radiosInUnison'] === 'true') {
            $flg += 1 << 25;
        }
        // richText:
        // If true, the field allows rich text formatting.
        if (isset($prp['richText']) && $prp['richText'] === 'true') {
            $flg += 1 << 25;
        }
        // commitOnSelChange:
        // Controls whether a field value is committed after a selection change.
        if (isset($prp['commitOnSelChange']) && $prp['commitOnSelChange'] === 'true') {
            $flg += 1 << 26;
        }
        $opt['ff'] = $flg;
        // defaultValue:
        // The default value of a field - that is, the value that the field is set to when the form is reset.
        if (isset($prp['defaultValue'])) {
            $opt['dv'] = $prp['defaultValue'];
        }
        $anf = 4; // default value for annotation flags
        // readonly:
        // The read-only characteristic of a field.
        // If a field is read-only, the user can see the field but cannot change it.
        if (isset($prp['readonly']) && $prp['readonly'] === 'true') {
            $anf += 1 << 6;
        }
        // display:
        // Controls whether the field is hidden or visible on screen and in print.
        if (isset($prp['display']) && \is_string($prp['display'])) {
            if ($prp['display'] === 'display.visible') {
            } elseif ($prp['display'] === 'display.hidden') {
                $anf += 1 << 1;
            } elseif ($prp['display'] === 'display.noPrint') {
                $anf -= 1 << 2;
            } elseif ($prp['display'] === 'display.noView') {
                $anf += 1 << 5;
            }
        }
        $opt['f'] = $anf;
        // currentValueIndices:
        // Reads and writes single or multiple values of a list box or combo box.
        if (isset($prp['currentValueIndices']) && \is_array($prp['currentValueIndices'])) {
            $opt['i'] = $prp['currentValueIndices'];
        }
        // value: The value of the field data that the user has entered.
        if (isset($prp['value'])) {
            if (\is_array($prp['value'])) {
                $opt['opt'] = [];
                /** @var array<array-key, bool|float|int|string|null> $valueList */
                $valueList = $prp['value'];
                foreach ($valueList as $key => $optval) {
                    if (\is_bool($optval)) {
                        $optstr = $optval ? 'true' : 'false';
                    } else {
                        $optstr = (string) $optval;
                    }
                    // exportValues:
                    // An array of strings representing the export values for the field.

                    if (isset($prp['exportValues'][$key])) {
                        $opt['opt'][$key] = [$prp['exportValues'][$key], $optstr];
                    } else {
                        $opt['opt'][$key] = $optstr;
                    }
                }
            } else {
                $opt['v'] = $prp['value'];
            }
        }
        // richValue:
        // This property specifies the text contents and formatting of a rich text field.
        if (isset($prp['richValue'])) {
            $opt['rv'] = $prp['richValue'];
        }
        // submitName:
        // If nonempty, used during form submission instead of name.
        // Only applicable if submitting in HTML format (that is, URL-encoded).
        if (isset($prp['submitName'])) {
            $opt['tm'] = (string) $prp['submitName'];
        }
        // name:
        // Fully qualified field name.
        if (isset($prp['name'])) {
            $opt['t'] = (string) $prp['name'];
        }
        // userName:
        // The user name (short description string) of the field.
        if (isset($prp['userName'])) {
            $opt['tu'] = (string) $prp['userName'];
        }
        // highlight:
        // Defines how a button reacts when a user clicks it.
        if (isset($prp['highlight'])) {
            $opt['h'] = match ($prp['highlight']) {
                'none' => 'N',
                'highlight.n' => 'N',
                'invert' => 'i',
                'highlight.i' => 'i',
                'push' => 'P',
                'highlight.p' => 'P',
                'outline' => 'O',
                'highlight.o' => 'O',
                default => 'N',
            };
        }
        // Unsupported options:
        // - calcOrderIndex: Changes the calculation order of fields in the document.
        // - delay: Delays the redrawing of a field's appearance.
        // - defaultStyle: This property defines the default style attributes for the form field.
        // - style: Allows the user to set the glyph style of a check box or radio button.
        // - textColor, textFont, textSize
        /** @var TAnnotOpts $opt */
        return $opt;
    }

    // ===| ANNOTATION |====================================================

    /**
     * Add an embedded file.
     * If a file with the same name already exists, it will be ignored.
     *
     * @param string $file  File name (absolute or relative path).
     * @param string $mime  MIME type of the file (e.g., 'application/xml').
     * @param string $afrel AFRelationship value (Source, Data, Alternative, Supplement, Unspecified).
     * @param string $desc  Optional description of the file.
     *
     * @throws PdfException in case of error.
     */
    public function addEmbeddedFile(
        string $file,
        string $mime = 'application/octet-stream',
        string $afrel = 'Source',
        string $desc = '',
    ): void {
        if ($this->pdfa === 1 || $this->pdfa === 2) {
            throw new PdfException('Embedded files are not allowed in PDF/A mode version 1 and 2');
        }

        if ($file === '') {
            throw new PdfException('Empty file name');
        }

        if ($this->pdfa === 3 && !\in_array($afrel, self::VALID_AF_RELATIONSHIPS, strict: true)) {
            throw new PdfException('afrel must be one of: ' . \implode(', ', self::VALID_AF_RELATIONSHIPS));
        }

        $filekey = \basename($file);
        if (!isset($this->embeddedfiles[$filekey])) {
            $this->embeddedfiles[$filekey] = [
                'a' => 0,
                'f' => ++$this->pon,
                'n' => ++$this->pon,
                'file' => $file,
                'content' => '',
                'mimeType' => $mime,
                'afRelationship' => $afrel,
                'description' => $desc,
                'creationDate' => \time(),
                'modDate' => \time(),
            ];
        }
    }

    /**
     * Add string content as an embedded file.
     * If a file with the same name already exists, it will be ignored.
     *
     * @param string $file    File name to be used a key for the embedded file.
     * @param string $content Content of the embedded file.
     * @param string $mime  MIME type of the file (e.g., 'application/xml').
     * @param string $afrel AFRelationship value (Source, Data, Alternative, Supplement, Unspecified).
     * @param string $desc  Optional description of the file.
     *
     * @throws PdfException in case of error.
     */
    public function addContentAsEmbeddedFile(
        string $file,
        string $content,
        string $mime = 'application/octet-stream',
        string $afrel = 'Source',
        string $desc = '',
    ): void {
        if ($this->pdfa === 1 || $this->pdfa === 2) {
            throw new PdfException('Embedded files are not allowed in PDF/A mode version 1 and 2');
        }
        if ($file === '' || $content === '') {
            throw new PdfException('Empty file name or content');
        }
        if (!isset($this->embeddedfiles[$file])) {
            $this->embeddedfiles[$file] = [
                'a' => 0,
                'f' => ++$this->pon,
                'n' => ++$this->pon,
                'file' => $file,
                'content' => $content,
                'mimeType' => $mime,
                'afRelationship' => $afrel,
                'description' => $desc,
                'creationDate' => \time(),
                'modDate' => \time(),
            ];
        }
    }

    /**
     * Add an annotation and returns the object id.
     *
     * @param float      $posx   Abscissa of upper-left corner.
     * @param float      $posy   Ordinate of upper-left corner.
     * @param float      $width  Width.
     * @param float      $height Height.
     * @param string     $txt    Annotation text or alternate content.
     * @param TAnnotOpts $opt    Array of options (Annotation Types) - all lowercase.
     *
     * @return int Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\File\Exception
     */
    public function setAnnotation(
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $txt,
        array $opt = [
            'subtype' => 'text',
        ],
    ): int {
        if ($opt['subtype'] === '') {
            $opt['subtype'] = 'text';
        }

        $subtype = \strtolower($opt['subtype']);

        // PDF/X interaction restriction: suppress interactive annotation subtypes.
        if ($this->pdfx && \in_array($subtype, self::PDFX_BLOCKED_ANNOT_SUBTYPES, true)) {
            return 0;
        }

        if ($this->xobjtid !== '' && isset($this->xobjects[$this->xobjtid])) {
            // Store annotationparameters for later use on a XObject template.
            $this->xobjects[$this->xobjtid]['annotations'][] = [
                'n' => 0,
                'x' => $posx,
                'y' => $posy,
                'w' => $width,
                'h' => $height,
                'txt' => $txt,
                'opt' => $opt,
            ];

            return 0;
        }
        $oid = ++$this->pon;
        $this->annotation[$oid] = [
            'n' => $oid,
            'x' => $posx,
            'y' => $posy,
            'w' => $width,
            'h' => $height,
            'txt' => $txt,
            'opt' => $opt,
        ];
        switch ($subtype) {
            case 'fileattachment':
            case 'sound':
                if (isset($opt['fs']) && $opt['fs'] !== '') {
                    $this->addEmbeddedFile($opt['fs']);
                }
        }
        // Add widgets annotation's icons
        if (isset($opt['mk'])) {
            $mk = $opt['mk'];
            if (isset($mk['i']) && \is_string($mk['i'])) {
                $this->image->add($mk['i']);
            }
            if (isset($mk['ri']) && \is_string($mk['ri'])) {
                $this->image->add($mk['ri']);
            }
            if (isset($mk['ix']) && \is_string($mk['ix'])) {
                $this->image->add($mk['ix']);
            }
        }
        return $oid;
    }

    /**
     * Creates a link in the specified area.
     * A link annotation represents either a hypertext link to a destination elsewhere in the document.
     *
     * @param float      $posx   Abscissa of upper-left corner.
     * @param float      $posy   Ordinate of upper-left corner.
     * @param float      $width  Width.
     * @param float      $height Height.
     * @param string     $link   URL to open when the link is clicked or an identifier returned by addInternalLink().
     *                           A single character prefix may be used to specify the link action:
     *                           - '#' = internal destination
     *                           - '%' = embedded PDF file
     *                           - '*' = embedded generic file
     *
     * @return int Object ID (Add to a page via: $pdf->page->addAnnotRef($aoid);).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     */
    public function setLink(float $posx, float $posy, float $width, float $height, string $link): int
    {
        return $this->setAnnotation($posx, $posy, $width, $height, $link, ['subtype' => 'Link']);
    }

    /**
     * Defines the page and vertical position an internal link points to.
     *
     * @param int $page Page number.
     * @param float $posy Vertical position.
     *
     * @return string Internal link identifier to be used with setLink().
     *
     */
    public function addInternalLink(int $page = -1, float $posy = 0): string
    {
        $lnkid = '@' . (\count($this->links) + 1);
        $this->links[$lnkid] = [
            'p' => $page < 0 ? $this->page->getPageID() : $page,
            'y' => $posy,
        ];
        return $lnkid;
    }

    /**
     * Add a named destination.
     *
     * @param string $name Named destination (must be unique).
     * @param int    $page Page number.
     * @param float  $posx Abscissa of upper-left corner.
     * @param float  $posy Ordinate of upper-left corner.
     *
     * @return string Destination name.
     */
    public function setNamedDestination(string $name, int $page = -1, float $posx = 0, float $posy = 0): string
    {
        $ename = $this->encrypt->encodeNameObject($name);
        $this->dests[$ename] = [
            'p' => $page < 0 ? $this->page->getPageID() : $page,
            'x' => $posx,
            'y' => $posy,
        ];
        return '#' . $ename;
    }

    /**
     * Add a bookmark entry.
     *
     * @param string $name   Bookmark description that will be printed in the TOC.
     * @param string $link   (Optional) URL to open when the link is clicked
     *                       or an identifier returned by addInternalLink().
     *                       A single character prefix may be used to specify the link action:
     *                       - '#' = internal destination
     *                       - '%' = embedded PDF file
     *                       - '*' = embedded generic file
     * @param int    $level  Bookmark level (minimum 0).
     *
     * @param int    $page   Page number.
     * @param float  $posx   Abscissa of upper-left corner.
     * @param float  $posy   Ordinate of upper-left corner.
     * @param string $fstyle Font style.
     *                       Possible values are (case insensitive):
     *                       - regular (default)
     *                       - B: bold
     *                       - I: italic
     *                       - U: underline
     *                       - D: strikeout (linethrough)
     *                       - O: overline
     * @param string $color Color name.
     */
    public function setBookmark(
        string $name,
        string $link = '',
        int $level = 0,
        int $page = -1,
        float $posx = 0,
        float $posy = 0,
        string $fstyle = '',
        string $color = '',
    ): void {
        $lastOutline = null;
        $lastOutlineKey = \array_key_last($this->outlines);
        if ($lastOutlineKey !== null && isset($this->outlines[$lastOutlineKey])) {
            $lastOutline = $this->outlines[$lastOutlineKey];
        }

        $maxlevel = isset($lastOutline['l']) ? (int) $lastOutline['l'] + 1 : 0;
        if ($level < 0) {
            $outlineLevel = 0;
        } elseif ($level > $maxlevel) {
            $outlineLevel = $maxlevel;
        } else {
            $outlineLevel = $level;
        }

        $this->outlines[] = [
            't' => $name,
            'u' => $link,
            'l' => $outlineLevel,
            'p' => $page < 0 ? $this->page->getPageID() : $page,
            'x' => $posx,
            'y' => $posy,
            's' => \strtoupper($fstyle),
            'c' => $color,
            'parent' => 0,
            'first' => -1,
            'last' => -1,
            'next' => -1,
            'prev' => -1,
        ];
    }

    // ===| XOBJECT |=======================================================

    /**
     * Create a new XObject template and return the object id.
     *
     * An XObject Template is a PDF block that is a self-contained description
     * of any sequence of graphics objects (including path objects, text objects,
     * and sampled images). An XObject Template may be painted multiple times,
     * either on several pages or at several locations on the same page and
     * produces the same results each time, subject only to the graphics state
     * at the time it is invoked.
     *
     * @param float $width  Width of the XObject.
     * @param float $height Height of the XObject.
     * @param ?TGTransparency $transpgroup Optional group attributes.
     *
     * @return string XObject template object ID.
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function newXObjectTemplate(float $width = 0, float $height = 0, ?array $transpgroup = null): string
    {
        $oid = ++$this->pon;
        $tid = 'XT' . $oid;
        $this->xobjtid = $tid;

        $region = $this->page->getRegion();
        $regionWidth = $region['RW'];
        $regionHeight = $region['RH'];

        if ($width <= 0) {
            $width = $regionWidth;
        }

        if ($height <= 0) {
            $height = $regionHeight;
        }

        $this->xobjects[$tid] = [
            'spot_colors' => [],
            'extgstate' => [],
            'gradient' => [],
            'font' => [],
            'image' => [],
            'xobject' => [],
            'annotations' => [],
            'id' => $tid,
            'n' => $oid,
            'x' => 0,
            'y' => 0,
            'w' => $width,
            'h' => $height,
            'outdata' => '',
            'transparency' => $transpgroup,
            'pheight' => $this->page->setPagePHeight($this->toPoints($height)),
            'gheight' => $this->graph->setPageHeight($height),
        ];

        return $tid;
    }

    /**
     * Exit from the XObject template mode.
     *
     * See: newXObjectTemplate.
     *
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function exitXObjectTemplate(): void
    {
        if ($this->xobjtid === '' || !isset($this->xobjects[$this->xobjtid])) {
            $this->xobjtid = '';
            return;
        }

        if (isset($this->xobjects[$this->xobjtid]['pheight'])) {
            $this->page->setPagePHeight($this->xobjects[$this->xobjtid]['pheight']);
        }

        if (isset($this->xobjects[$this->xobjtid]['gheight'])) {
            $this->graph->setPageHeight($this->xobjects[$this->xobjtid]['gheight']);
        }

        // restore page height
        $this->xobjtid = '';
    }

    /**
     * Returns the PDF code to render the specified XObject template.
     *
     * See: newXObjectTemplate.
     *
     * @param string      $tid         The XObject Template object as returned by the newXObjectTemplate method.
     * @param float       $posx        Abscissa of upper-left corner.
     * @param float       $posy        Ordinate of upper-left corner.
     * @param float       $width       Width.
     * @param float       $height      Height.
     * @param string      $valign      Vertical alignment inside the specified box: T=top; C=center; B=bottom.
     * @param string      $halign      Horizontal alignment inside the specified box: L=left; C=center; R=right.
     *
     * @return string The PDF code to render the specified XObject template.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     */
    public function getXObjectTemplate(
        string $tid,
        float $posx = 0,
        float $posy = 0,
        float $width = 0,
        float $height = 0,
        string $valign = 'T',
        string $halign = 'L',
    ): string {
        $this->xobjtid = '';
        $region = $this->page->getRegion();
        $regionWidth = $region['RW'];
        $regionHeight = $region['RH'];

        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return '';
        }
        $xobjWidth = $xobj['w'];
        $xobjHeight = $xobj['h'];
        $xobjId = $xobj['id'];

        if ($width <= 0) {
            $width = \min($xobjWidth, $regionWidth);
        }

        if ($height <= 0) {
            $height = \min($xobjHeight, $regionHeight);
        }

        $tplx = $this->cellHPos($posx, $width, $halign, $this->defcell);
        $tply = $this->cellVPos($posy, $height, $valign, $this->defcell);

        $this->bbox[] = [
            'x' => $tplx,
            'y' => $tply,
            'w' => $width,
            'h' => $height,
        ];

        $ctm = [
            0 => $xobjWidth > 0.0 ? $width / $xobjWidth : 1.0,
            1 => 0,
            2 => 0,
            3 => $xobjHeight > 0.0 ? $height / $xobjHeight : 1.0,
            4 => $this->toPoints($tplx),
            5 => $this->toYPoints($tply + $height),
        ];

        $out = $this->graph->getStartTransform();
        $out .= $this->graph->getTransformation($ctm);
        $out .= '/' . $xobjId . ' Do' . "\n";
        $out .= $this->graph->getStopTransform();

        if ($xobj['annotations'] !== []) {
            foreach ($xobj['annotations'] as $annot) {
                // transform original coordinates
                $clt = $this->graph->getCtmProduct($ctm, [
                    1,
                    0,
                    0,
                    1,
                    $this->toPoints($annot['x']),
                    $this->toPoints(-$annot['y']),
                ]);
                $clt4 = $clt[4];
                $clt5 = $clt[5];
                $anx = $this->toUnit($clt4);
                $any = $this->toYUnit($clt5 + $this->toUnit($height));

                $crb = $this->graph->getCtmProduct($ctm, [
                    1,
                    0,
                    0,
                    1,
                    $this->toPoints($annot['x'] + $annot['w']),
                    $this->toPoints(-$annot['y'] - $annot['h']),
                ]);
                $crb4 = $crb[4];
                $crb5 = $crb[5];
                $anw = $this->toUnit($crb4) - $anx;
                $anh = $this->toYUnit($crb5 + $this->toUnit($height)) - $any;

                $out .= $this->setAnnotation($anx, $any, $anw, $anh, $annot['txt'], $annot['opt']);
            }
        }

        return $out;
    }

    /**
     * @param string $tid
     * @return TXOBject|null
     */
    protected function getXObjectByID(string $tid): ?array
    {
        return $this->xobjects[$tid] ?? null;
    }

    /**
     * @param string $tid
     * @param TXOBject $xobj
     */
    protected function setXObjectByID(string $tid, array $xobj): void
    {
        $this->xobjects[$tid] = $xobj;
    }

    /**
     * @param string $tid
     * @return string
     */
    protected function getXObjectOutDataByID(string $tid): string
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return '';
        }

        return $xobj['outdata'];
    }

    /**
     * Add the specified raw PDF content to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $data  The raw PDF content data to add.
     */
    public function addXObjectContent(string $tid, string $data): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['outdata'] .= $data;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified XObject ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The XObject key to add.
     */
    public function addXObjectXObjectID(string $tid, string $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['xobject'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified Image ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  TheImage key to add.
     */
    public function addXObjectImageID(string $tid, int $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['image'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified Font ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The Font key to add.
     */
    public function addXObjectFontID(string $tid, string $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['font'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified Gradient ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  The Gradient key to add.
     */
    public function addXObjectGradientID(string $tid, int $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['gradient'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified ExtGState ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  The ExtGState key to add.
     */
    public function addXObjectExtGStateID(string $tid, int $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['extgstate'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    /**
     * Add the specified SpotColor ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The SpotColor key to add.
     */
    public function addXObjectSpotColorID(string $tid, string $key): void
    {
        $xobj = $this->getXObjectByID($tid);
        if ($xobj === null) {
            return;
        }
        $xobj['spot_colors'][] = $key;
        $this->setXObjectByID($tid, $xobj);
    }

    // ===| ANNOTATION FORM FIELDS |=======================================================

    /**
     * Retyurns the PDF command to ser the default fill color from the style stack.
     *
     * @return string
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function getPDFDefFillColor(): string
    {
        $style = $this->graph->getCurrentStyleArray();
        if (isset($style['fillColor']) && $style['fillColor'] !== '') {
            $colobj = $this->color->getColorObj($style['fillColor']);
            if ($colobj !== null) {
                return $colobj->getPdfColor();
            }
        }
        return '';
    }

    /**
     * Merge annotation options with Javascript properties.
     *
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @param string $color Default PDF color command.
     *
     * @return TAnnotOpts merged Annotation options.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     */
    protected function mergeAnnotOptions(
        array $opt = [
            'subtype' => 'text',
        ],
        array $jsp = [],
        string $color = '',
    ): array {
        // merge properties
        $jsp = \array_merge($this->defJSAnnotProp, $jsp);
        $opt = \array_merge($opt, $this->getAnnotOptFromJSProp($jsp));
        // set font
        $curfont = $this->font->getCurrentFont();
        /** @var array{key: string, idx: int, outraw: string} $curfont */
        $fontKey = $curfont['key'];
        $fontIdx = $curfont['idx'];
        $this->annotation_fonts[$fontKey] = $fontIdx;
        $fontstyle = $curfont['outraw'];
        if (isset($jsp['textColor']) && \is_string($jsp['textColor']) && \trim($jsp['textColor']) !== '') {
            $colobj = $this->color->getColorObj($jsp['textColor']);
            if ($colobj !== null) {
                $color = $colobj->getPdfColor();
            }
        }
        if ($color === '') {
            $black = $this->color->getColorObj('black');
            $color = $black !== null ? $black->getPdfColor() : '';
        }
        $opt['da'] = $fontstyle . ' ' . $color;
        /** @var TAnnotOpts $opt */
        return $opt;
    }

    /**
     * Adds an annotation button form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param string $caption caption.
     * @param string|array<string,mixed> $action action triggered by pressing the button.
     *                      Use a string to specify a javascript action.
     *                      Use an array to specify a form action options
     *                      as in section 12.7.5 of PDF32000_2008.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function addFFButton(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $caption,
        string|array $action,
        array $opt = [
            'subtype' => 'Widget',
        ],
        array $jsp = [],
    ): int {
        $jsp['Pushbutton'] = 'true';
        $jsp['highlight'] = 'push';
        $jsp['display'] = 'display.noPrint';
        $opt = $this->mergeAnnotOptions($opt, $jsp);
        // appearance stream
        $opt['ap'] = [];
        $da = isset($opt['da']) ? $opt['da'] : '';
        $opt['ap']['n'] = '/Tx BMC q ' . $da . ' ';
        $tid = $this->newXObjectTemplate($width, $height);
        $strokeColor =
            isset($jsp['strokeColor']) && \is_string($jsp['strokeColor']) && \trim($jsp['strokeColor']) !== ''
                ? \trim($jsp['strokeColor'])
                : '#333333';
        $fillColor =
            isset($jsp['fillColor']) && \is_string($jsp['fillColor']) && \trim($jsp['fillColor']) !== ''
                ? \trim($jsp['fillColor'])
                : '#cccccc';
        $lineWidth = isset($jsp['lineWidth']) && \is_numeric($jsp['lineWidth'])
            ? $this->toUnit((float) \max(0, (float) $jsp['lineWidth']))
            : $this->toUnit(1);
        $dashArray = [];
        if (isset($jsp['borderStyle']) && \is_string($jsp['borderStyle'])) {
            $cssBorderStyle = \strtolower(\trim($jsp['borderStyle']));
            if ($cssBorderStyle === 'dashed') {
                $dashArray = [3, 2];
            }
        }
        $defbstyle = [
            'lineWidth' => $lineWidth,
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'dashArray' => $dashArray,
            'dashPhase' => 0,
            'lineColor' => $strokeColor,
            'fillColor' => $fillColor,
        ];
        $bstyle = [
            'all' => $defbstyle,
            0 => $defbstyle, // TOP
            1 => $defbstyle, // RIGHT
            2 => $defbstyle, // BOTTOM
            3 => $defbstyle, // LEFT
        ];
        if (!isset($jsp['strokeColor']) || \trim($jsp['strokeColor']) === '') {
            $bstyle[0]['lineColor'] = '#e7e7e7';
            $bstyle[3]['lineColor'] = '#e7e7e7';
        }
        $txtbox = $this->getTextCell($caption, 0, 0, $width, $height, 0, 0, 'C', 'C', null, $bstyle);
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $xobjOutData = $this->getXObjectOutDataByID($tid);
        $opt['ap']['n'] .= $xobjOutData;
        $opt['ap']['n'] .= 'Q EMC';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Btn';
        $opt['t'] = $caption;
        $opt['v'] = $name;
        if (!isset($opt['mk'])) {
            $opt['mk'] = [];
        }
        $oid = $this->pon + 1; // from setAnnotation
        $opt['mk']['ca'] = $this->getOutTextString($caption, $oid, true);
        $opt['mk']['rc'] = $this->getOutTextString($caption, $oid, true);
        $opt['mk']['ac'] = $this->getOutTextString($caption, $oid, true);
        if ($action !== '' && $action !== []) {
            if (\is_string($action)) {
                if ($this->pdfa <= 0) {
                    $opt['a'] = '/S /JavaScript /JS ' . $this->getOutTextString($action, $oid, true);
                }
            } else {
                // form action options as in section 12.7.5 of PDF32000_2008.
                $opt['a'] = '/S';
                $bmode = ['SubmitForm', 'ResetForm', 'ImportData'];
                if (isset($action['S']) && \is_string($action['S']) && \in_array($action['S'], $bmode, strict: true)) {
                    $opt['a'] = '/S /' . $action['S'];
                }
                if (isset($action['F']) && \is_string($action['F']) && $action['F'] !== '') {
                    $opt['a'] .= ' /F ' . $this->encrypt->escapeDataString($action['F'], $oid);
                }
                if (isset($action['Fields']) && \is_array($action['Fields']) && $action['Fields'] !== []) {
                    $opt['a'] .= ' /Fields [';
                    $opt['a'] .= \array_reduce(
                        $action['Fields'],
                        fn(string $carry, mixed $field): string => !\is_string($field)
                            ? $carry
                            : $carry . ' ' . $this->getOutTextString($field, $oid),
                        '',
                    );
                    $opt['a'] .= ']';
                }
                if (isset($action['Flags'])) {
                    $flg = 0;
                    if (\is_array($action['Flags'])) {
                        $flg = \array_reduce(
                            $action['Flags'],
                            static fn(int $carry, mixed $flag): int => !\is_string($flag)
                                ? $carry
                                : $carry
                                    + match ($flag) {
                                        'Include/Exclude' => 1,
                                        'IncludeNoValueFields' => 1 << 1,
                                        'ExportFormat' => 1 << 2,
                                        'GetMethod' => 1 << 3,
                                        'SubmitCoordinates' => 1 << 4,
                                        'XFDF' => 1 << 5,
                                        'IncludeAppendSaves' => 1 << 6,
                                        'IncludeAnnotations' => 1 << 7,
                                        'SubmitPDF' => 1 << 8,
                                        'CanonicalFormat' => 1 << 9,
                                        'ExclNonUserAnnots' => 1 << 10,
                                        'ExclFKey' => 1 << 11,
                                        'EmbedForm' => 1 << 13,
                                        default => 0,
                                    },
                            0,
                        );
                    } elseif (\is_numeric($action['Flags'])) {
                        $flg = intval($action['Flags']);
                    }
                    $opt['a'] .= ' /Flags ' . $flg;
                }
            }
        }
        if (isset($this->xobjects[$tid])) {
            unset($this->xobjects[$tid]);
        }
        unset($opt['mk']['i'], $opt['mk']['ri'], $opt['mk']['ix']);
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $height, $name, $annotOpt);
    }

    /**
     * Adds an annotation checkbox form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param string $onvalue Value to be returned if selected.
     * @param bool $checked Define the initial state.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     */
    public function addFFCheckBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        string $onvalue = 'Yes',
        bool $checked = false,
        array $opt = [
            'subtype' => 'Widget',
        ],
        array $jsp = [],
    ): int {
        $font = $this->font->insert($this->pon, 'zapfdingbats');
        /** @var array{cw: array<int, float>, ascent: float, descent: float, outraw: string} $font */
        $color = $this->getPDFDefFillColor();
        $jsp['borderStyle'] = 'inset';
        $jsp['value'] = !isset($jsp['value']) || $jsp['value'] === [] ? ['Yes'] : $jsp['value'];
        $opt = $this->mergeAnnotOptions($opt, $jsp, $color);
        $onvalue = $onvalue === '' ? 'Yes' : $onvalue;
        $opt['ap'] = [];
        $opt['ap']['n'] = [];
        $cw110 = isset($font['cw'][110]) ? $font['cw'][110] : 0.0;
        $rfx = ($this->toPoints($width) - $cw110) / 2;
        $fontAscent = $font['ascent'];
        $fontDescent = $font['descent'];
        $fontOutRaw = $font['outraw'];
        $rfy = $this->toPoints($width) - ($fontAscent - $fontDescent);
        $opt['ap']['n']['Yes'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(110) . ') Tj ET Q',
            $color,
            $fontOutRaw,
            $rfx,
            $rfy,
        );

        $cw111 = isset($font['cw'][111]) ? $font['cw'][111] : 0.0;
        $rfx = ($this->toPoints($width) - $cw111) / 2;

        $opt['ap']['n']['Off'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(111) . ') Tj ET Q',
            $color,
            $fontOutRaw,
            $rfx,
            $rfy,
        );
        $opt['opt'] = [$onvalue];
        $opt['as'] = $checked ? 'Yes' : 'Off';
        $opt['v'] = ['/' . $opt['as']];
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Btn';
        $opt['t'] = $name;
        $this->font->popLastFont();
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $width, $name, $annotOpt);
    }

    /**
     * Adds an annotation combobox (select) form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<array{string,string}>|array<string> $values List of selection values.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function addFFComboBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $values,
        array $opt = [
            'subtype' => 'Widget',
        ],
        array $jsp = [],
    ): int {
        $jsp['Combo'] = true;
        $opt = $this->mergeAnnotOptions($opt, $jsp);
        // appearance stream
        $opt['ap'] = [];
        $defaultAppearance = isset($opt['da']) ? $opt['da'] : '';
        $opt['ap']['n'] = '/Tx BMC q ' . $defaultAppearance . ' ';
        $text = '';
        foreach ($values as $item) {
            if (\is_array($item)) {
                $text .= $item[1] . "\n";
            } else {
                $text .= $item . "\n";
            }
        }
        $tid = $this->newXObjectTemplate($width, $height);
        $txtbox = $this->getTextCell($text, 0, 0, $width, $height, 0, 0, 'T');
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->getXObjectOutDataByID($tid);
        $opt['ap']['n'] .= 'Q EMC';
        $opt['subtype'] = 'Widget';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Ch';
        $opt['t'] = $name;
        $opt['opt'] = $values;
        unset(
            $this->xobjects[$tid],
            $opt['mk']['ca'],
            $opt['mk']['rc'],
            $opt['mk']['ac'],
            $opt['mk']['i'],
            $opt['mk']['ri'],
            $opt['mk']['ix'],
            $opt['mk']['if'],
            $opt['mk']['tp'],
        );
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $height, $name, $annotOpt);
    }

    /**
     * Adds an annotation listbox (select) form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<array{string,string}>|array<string> $values List of selection values.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function addFFListBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $values,
        array $opt = [
            'subtype' => 'Widget',
        ],
        array $jsp = [],
    ): int {
        $opt = $this->mergeAnnotOptions($opt, $jsp);
        // appearance stream
        $opt['ap'] = [];
        $defaultAppearance = isset($opt['da']) ? $opt['da'] : '';
        $opt['ap']['n'] = '/Tx BMC q ' . $defaultAppearance . ' ';
        $text = '';
        foreach ($values as $item) {
            if (\is_array($item)) {
                $text .= $item[1] . "\n";
            } else {
                $text .= $item . "\n";
            }
        }
        $tid = $this->newXObjectTemplate($width, $height);
        $txtbox = $this->getTextCell($text, $posx, $posy, $width, $height, 0, 0, 'T');
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->getXObjectOutDataByID($tid);
        $opt['ap']['n'] .= 'Q EMC';
        $opt['subtype'] = 'Widget';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Ch';
        $opt['t'] = $name;
        $opt['opt'] = $values;
        unset(
            $this->xobjects[$tid],
            $opt['mk']['ca'],
            $opt['mk']['rc'],
            $opt['mk']['ac'],
            $opt['mk']['i'],
            $opt['mk']['ri'],
            $opt['mk']['ix'],
            $opt['mk']['if'],
            $opt['mk']['tp'],
        );
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $height, $name, $annotOpt);
    }

    /**
     * Adds an annotation radiobutton form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param string $onvalue Value to be returned if selected.
     * @param bool $checked Define the initial state.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     */
    public function addFFRadioButton(
        string $name,
        float $posx,
        float $posy,
        float $width,
        string $onvalue = 'On',
        bool $checked = false,
        array $opt = [
            'subtype' => 'Widget',
        ],
        array $jsp = [],
    ): int {
        $font = $this->font->insert($this->pon, 'zapfdingbats');
        /** @var array{cw: array<int, float>, ascent: float, descent: float, outraw: string} $font */
        $color = $this->getPDFDefFillColor();
        $jsp['NoToggleToOff'] = 'true';
        $jsp['Radio'] = 'true';
        $jsp['borderStyle'] = 'inset';
        $opt = $this->mergeAnnotOptions($opt, $jsp, $color);
        $onvalue = $onvalue === '' ? 'On' : $onvalue;
        $defval = $checked ? $onvalue : 'Off';
        if (!isset($this->radiobuttons[$name])) {
            $oid = ++$this->pon;
            $this->radiobuttons[$name] = [
                'n' => $oid,
                '#readonly#' => false,
                'kids' => [],
            ];
        }
        $this->radiobuttons[$name]['kids'][] = [
            'n' => $this->pon + 1, // this is assigned on setAnnotation
            'def' => $defval,
        ];
        $opt['ap'] = [];
        $opt['ap']['n'] = [];
        $cw108 = isset($font['cw'][108]) ? $font['cw'][108] : 0.0;
        $rfx = ($this->toPoints($width) - $cw108) / 2;
        $fontAscent = $font['ascent'];
        $fontDescent = $font['descent'];
        $fontOutRaw = $font['outraw'];
        $rfy = $this->toPoints($width) - ($fontAscent - $fontDescent);
        $opt['ap']['n'][$onvalue] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(108) . ') Tj ET Q',
            $color,
            $fontOutRaw,
            $rfx,
            $rfy,
        );
        $cw109 = isset($font['cw'][109]) ? $font['cw'][109] : 0.0;
        $rfx = ($this->toPoints($width) - $cw109) / 2;

        $opt['ap']['n']['Off'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(109) . ') Tj ET Q',
            $color,
            $fontOutRaw,
            $rfx,
            $rfy,
        );
        if (!isset($opt['mk'])) {
            $opt['mk'] = [];
        }
        $opt['mk']['ca'] = '(l)';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Btn';
        if ($checked) {
            $opt['v'] = ['/' . $onvalue];
            $opt['as'] = $onvalue;
        } else {
            $opt['as'] = 'Off';
        }
        // store readonly flag
        /** @var mixed $annotFlagValue */
        $annotFlagValue = $opt['f'] ?? null;
        $annotFlags = \is_numeric($annotFlagValue) ? (int) $annotFlagValue : 0;
        $this->radiobuttons[$name]['#readonly#'] = $this->radiobuttons[$name]['#readonly#'] || ($annotFlags & 64) !== 0;
        $this->font->popLastFont();
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $width, $name, $annotOpt);
    }

    /**
     * Adds an annotation text form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
     * @param array<string, mixed> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     *
     * @return int PDF Object ID.
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     * @throws \Com\Tecnick\File\Exception
     * @throws \Com\Tecnick\Pdf\Image\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    public function addFFText(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $opt = [
            'subtype' => 'text',
        ],
        array $jsp = [],
    ): int {
        /** @var mixed $fieldValue */
        $fieldValue = $opt['v'] ?? null;
        $opt = $this->mergeAnnotOptions($opt, $jsp);
        // appearance stream
        $opt['ap'] = [];
        $defaultAppearance = $opt['da'] ?? '';
        $opt['ap']['n'] = '/Tx BMC q ' . $defaultAppearance . ' ';
        $text = \is_scalar($fieldValue) ? (string) $fieldValue : '';
        $txtColor = '';
        if (isset($jsp['textColor']) && \is_string($jsp['textColor']) && \trim($jsp['textColor']) !== '') {
            $colobj = $this->color->getColorObj($jsp['textColor']);
            if ($colobj !== null) {
                $txtColor = $colobj->getPdfColor();
            }
        }
        if ($txtColor === '') {
            $black = $this->color->getColorObj('black');
            if ($black !== null) {
                $txtColor = $black->getPdfColor();
            }
        }
        $halign = match ((int) ($opt['q'] ?? -1)) {
            0 => 'L',
            1 => 'C',
            2 => 'R',
            default => '',
        };
        $tid = $this->newXObjectTemplate($width, $height);
        $zerocell = $this::ZEROCELL;
        $txtbox = $this->getTextCell($text, 0, 0, $width, $height, 0, 0, 'T', $halign, $zerocell);
        $this->addXObjectContent($tid, $txtColor . $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->getXObjectOutDataByID($tid);
        $opt['ap']['n'] .= 'Q EMC';
        $opt['subtype'] = 'Widget';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Tx';
        $opt['t'] = $name;
        unset(
            $this->xobjects[$tid],
            $opt['bs'],
            $opt['mk']['ca'],
            $opt['mk']['rc'],
            $opt['mk']['ac'],
            $opt['mk']['i'],
            $opt['mk']['ri'],
            $opt['mk']['ix'],
            $opt['mk']['if'],
            $opt['mk']['tp'],
        );
        /** @var TAnnotOpts $annotOpt */
        $annotOpt = $opt;
        return $this->setAnnotation($posx, $posy, $width, $height, $name, $annotOpt);
    }

    // ==| JS Fiedls |==

    /**
     * Adds a JavaScript button form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param string $caption caption.
     * @param string $action action triggered by pressing the button.
     *                      Use a string to specify a javascript action.
     *                      Use an array to specify a form action options
     *                      as in section 12.7.5 of PDF32000_2008.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSButton(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $caption,
        string $action,
        array $jsp = [],
    ): void {
        $this->addJavaScriptField('button', $name, $posx, $posy, $width, $height, $jsp);
        $this->javascript .= 'f' . $name . ".buttonSetCaption('" . \addslashes($caption) . "');\n";
        $this->javascript .= 'f' . $name . ".setAction('MouseUp','" . \addslashes($action) . "');\n";
        $this->javascript .= 'f' . $name . ".highlight='push';\n";
        $this->javascript .= 'f' . $name . ".print=false;\n";
    }

    /**
     * Adds a JavaScript checkbox form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSCheckBox(string $name, float $posx, float $posy, float $width, array $jsp = []): void
    {
        $this->addJavaScriptField('checkbox', $name, $posx, $posy, $width, $width, $jsp);
    }

    /**
     * Adds a JavaScript combobox (select) form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<array{string,string}>|array<string> $values List of selection values.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSComboBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $values,
        array $jsp = [],
    ): void {
        $this->addJavaScriptField('combobox', $name, $posx, $posy, $width, $height, $jsp);
        $itm = '';
        foreach ($values as $value) {
            if (is_array($value)) {
                $itm .= ',[\'' . \addslashes($value[1]) . '\',\'' . \addslashes($value[0]) . '\']';
            } else {
                $itm .= ',[\'' . \addslashes($value) . '\',\'' . \addslashes($value) . '\']';
            }
        }
        $this->javascript .= 'f' . $name . '.setItems(' . \substr($itm, 1) . ');' . "\n";
    }

    /**
     * Adds a JavaScript listbox (select) form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<array{string,string}>|array<string> $values List of selection values.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSListBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $values,
        array $jsp = [],
    ): void {
        $this->addJavaScriptField('listbox', $name, $posx, $posy, $width, $height, $jsp);
        $itm = '';
        foreach ($values as $value) {
            if (\is_array($value)) {
                $itm .= ',[\'' . \addslashes($value[1]) . '\',\'' . \addslashes($value[0]) . '\']';
            } else {
                $itm .= ',[\'' . \addslashes($value) . '\',\'' . \addslashes($value) . '\']';
            }
        }
        $this->javascript .= 'f' . $name . '.\setItems(' . \substr($itm, 1) . ');' . "\n";
    }

    /**
     * Adds a JavaScript radiobutton form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSRadioButton(string $name, float $posx, float $posy, float $width, array $jsp = []): void
    {
        $this->addJavaScriptField('radiobutton', $name, $posx, $posy, $width, $width, $jsp);
    }

    /**
     * Adds a JavaScript text form field.
     *
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
     * @throws PdfException in case of error.
     * @throws \Com\Tecnick\Pdf\Font\Exception
     * @throws \Com\Tecnick\Color\Exception
     * @throws \Com\Tecnick\Pdf\Page\Exception
     */
    public function addJSText(
        string $name,
        float $posx,
        float $posy,
        float $width,
        float $height,
        array $jsp = [],
    ): void {
        $this->addJavaScriptField('text', $name, $posx, $posy, $width, $height, $jsp);
    }
}
