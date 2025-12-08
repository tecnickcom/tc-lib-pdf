<?php

/**
 * JavaScript.php
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
 * Com\Tecnick\Pdf\JavaScript
 *
 * JavaScript PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license   http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TAnnotOpts from Output
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class JavaScript extends \Com\Tecnick\Pdf\CSS
{
    //@TODO

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
     * @var array<string, array{
     *         'n': int,
     *         '#readonly#': bool,
     *         'kid'?: int,
     *         'def': string,
     *      }>
     */
    protected array $radiobuttonGroups = [];

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
        if ($this->pdfa > 0) {
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
     *
     * @param string $type field type.
     * @param string $name field name.
     * @param float $posx horizontal position in user units (LTR).
     * @param float $posy vertical position in user units (LTR).
     * @param float $width width in user units.
     * @param float $height height in user units.
     * @param array<string, string> $prop javascript field properties (see: Javascript for Acrobat API reference).
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
        $curfont = $this->font->getCurrentFont();
        // avoid fields duplication after saving the document
        $this->javascript .= "if (getField('tcpdfdocsaved').value != 'saved') {"
        . sprintf(
            "f" . $name . "=this.addField('%s','%s',%u,[%F,%F,%F,%F]);",
            $name,
            $type,
            $page['num'] - 1,
            $this->toPoints($posx),
            $this->toYPoints($posy, $page['pheight']) + 1.0,
            $this->toPoints($posx + $width),
            $this->toYPoints($posy + $height, $page['pheight']) + 1.0,
        ) . "\n"
        . 'f' . $name . '.textSize=' . $curfont['size'] . ";\n";
        foreach ($prop as $key => $val) {
            if (\strcmp(\substr($key, -5), 'Color') == 0) {
                $color = $this->color->getColorObj($val);
                $val = ($color === null) ? '' : $color->getJsPdfColor();
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
     */
    public function getAnnotOptFromJSProp(array $prp): array
    {
        if (!empty($prp['aopt']) && \is_array($prp['aopt'])) {
            // the annotation options are already defined
            return $prp['aopt']; // @phpstan-ignore return.type
        }
        /** @var TAnnotOpts $opt */
        $opt = [];
        // alignment: Controls how the text is laid out within the text field.
        if (!empty($prp['alignment'])) {
            $opt['q'] = match ($prp['alignment']) {
                'left' => 0,
                'center' => 1,
                'right' => 2,
                default => $this->rtl ? 2 : 0,
            };
        }
        // lineWidth:
        // Specifies the thickness of the border when stroking the perimeter of a field's rectangle.
        $linewidth = (isset($prp['lineWidth']) && (\is_numeric($prp['lineWidth']))) ? \intval($prp['lineWidth']) : 1;
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
        if (!isset($opt['mk'])) {
            $opt['mk'] = [];
        }
        if (!isset($opt['mk']['if'])) {
            $opt['mk']['if'] = [];
        }
        // @phpstan-ignore offsetAccess.nonOffsetAccessible
        $opt['mk']['if']['a'] = [0.5, 0.5];
        // buttonAlignX:
        // Controls how space is distributed from the left of the button face with respect to the icon.
        if (isset($prp['buttonAlignX'])) {
            $opt['mk']['if']['a'][0] = $prp['buttonAlignX'];
        }
        // buttonAlignY:
        // Controls how unused space is distributed from the bottom of the button face with respect to the icon.
        if (isset($prp['buttonAlignY'])) {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if']['a'][1] = $prp['buttonAlignY'];
        }
        // buttonFitBounds:
        // If true, the extent to which the icon may be scaled is set to the bounds of the button field.
        if (isset($prp['buttonFitBounds']) && ($prp['buttonFitBounds'] == 'true')) {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if']['fb'] = true;
        }
        // buttonScaleHow:
        // Controls how the icon is scaled (if necessary) to fit inside the button face.
        if (isset($prp['buttonScaleHow'])) {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if']['s'] = match ($prp['buttonScaleHow']) {
                'scaleHow.proportional' => 'P',
                'scaleHow.anamorphic' => 'A',
                default => 'P',
            };
        }
        // buttonScaleWhen:
        // Controls when an icon is scaled to fit inside the button face.
        if (isset($prp['buttonScaleWhen'])) {
            // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if']['sw'] = match ($prp['buttonScaleWhen']) {
                'scaleWhen.always' => 'A',
                'scaleWhen.never' => 'N',
                'scaleWhen.tooBig' => 'B',
                'scaleWhen.tooSmall' => 'S',
                default => 'N',
            };
        }
        // buttonPosition:
        // Controls how the text and the icon of the button are positioned with respect to each other
        //  within the button face.
        if (isset($prp['buttonPosition'])) {
            if (\is_numeric($prp['buttonPosition'])) {
                $mktp = \intval($prp['buttonPosition']);
                if ($mktp >= 0 && $mktp <= 6) {
                    $opt['mk']['tp'] = $mktp;
                }
            } else {
                $opt['mk']['tp'] = match ($prp['buttonPosition']) {
                    'position.textOnly' =>  0,
                    'position.iconOnly' =>  1,
                    'position.iconTextV' =>  2,
                    'position.textIconV' =>  3,
                    'position.iconTextH' =>  4,
                    'position.textIconH' =>  5,
                    'position.overlay' =>  6,
                    default => 0,
                };
            }
        }
        // fillColor:
        // Specifies the background color for a field.
        if (isset($prp['fillColor'])) {
            if (\is_array($prp['fillColor'])) {
                $opt['mk']['bg'] = $prp['fillColor'];
            } elseif (\is_string($prp['fillColor'])) {
                $fillColor = $this->color->getColorObj($prp['fillColor']);
                if ($fillColor !== null) {
                    $opt['mk']['bg'] = $fillColor->getPDFacArray();
                }
            }
        }
        // strokeColor:
        // Specifies the stroke color for a field that is used to stroke the rectangle of the field
        // with a line as large as the line width.
        if (isset($prp['strokeColor'])) {
            if (\is_array($prp['strokeColor'])) {
                $opt['mk']['bc'] = $prp['strokeColor'];
            } elseif (\is_string($prp['strokeColor'])) {
                $strokeColor = $this->color->getColorObj($prp['strokeColor']);
                if ($strokeColor !== null) {
                    $opt['mk']['bc'] = $strokeColor->getPDFacArray();
                }
            }
        }
        // rotation:
        // The rotation of a widget in counterclockwise increments.
        if (isset($prp['rotation'])) {
            $opt['mk']['r'] = $prp['rotation'];
        }
        // charLimit:
        // Limits the number of characters that a user can type into a text field.
        if (isset($prp['charLimit']) && \is_numeric($prp['charLimit'])) {
            $opt['maxlen'] = intval($prp['charLimit']);
        }
        $off = 0;
        // readonly:
        // The read-only characteristic of a field.
        // If a field is read-only, the user can see the field but cannot change it.
        if (!empty($prp['readonly']) && ($prp['readonly'] == 'true')) {
            $off += 1 << 0;
        }
        // required:
        // Specifies whether a field requires a value.
        if (!empty($prp['required']) && ($prp['required'] == 'true')) {
            $off += 1 << 1;
        }
        // multiline:
        // Controls how text is wrapped within the field.
        if (!empty($prp['multiline']) && ($prp['multiline'] == 'true')) {
            $off += 1 << 12;
        }
        // password:
        // Specifies whether the field should display asterisks when data is entered in the field.
        if (!empty($prp['password']) && ($prp['password'] == 'true')) {
            $off += 1 << 13;
        }
        // NoToggleToOff:
        // If set, exactly one radio button shall be selected at all times;
        // selecting the currently selected button has no effect.
        if (!empty($prp['NoToggleToOff']) && ($prp['NoToggleToOff'] == 'true')) {
            $off += 1 << 14;
        }
        // Radio:
        // If set, the field is a set of radio buttons.
        if (!empty($prp['Radio']) && ($prp['Radio'] == 'true')) {
            $off += 1 << 15;
        }
        // Pushbutton:
        // If set, the field is a pushbutton that does not retain a permanent value.
        if (!empty($prp['Pushbutton']) && ($prp['Pushbutton'] == 'true')) {
            $off += 1 << 16;
        }
        // Combo:
        // If set, the field is a combo box; if clear, the field is a list box.
        if (!empty($prp['Combo']) && ($prp['Combo'] == 'true')) {
            $off += 1 << 17;
        }
        // editable:
        // Controls whether a combo box is editable.
        if (!empty($prp['editable']) && ($prp['editable'] == 'true')) {
            $off += 1 << 18;
        }
        // Sort:
        // If set, the field's option items shall be sorted alphabetically.
        if (!empty($prp['Sort']) && ($prp['Sort'] == 'true')) {
            $off += 1 << 19;
        }
        // fileSelect:
        // If true, sets the file-select flag in the Options tab of the text field
        // (Field is Used for File Selection).
        if (!empty($prp['fileSelect']) && ($prp['fileSelect'] == 'true')) {
            $off += 1 << 20;
        }
        // multipleSelection:
        // If true, indicates that a list box allows a multiple selection of items.
        if (!empty($prp['multipleSelection']) && ($prp['multipleSelection'] == 'true')) {
            $off += 1 << 21;
        }
        // doNotSpellCheck:
        // If true, spell checking is not performed on this editable text field.
        if (!empty($prp['doNotSpellCheck']) && ($prp['doNotSpellCheck'] == 'true')) {
            $off += 1 << 22;
        }
        // doNotScroll:
        // If true, the text field does not scroll and the user,
        // therefore, is limited by the rectangular region designed for the field.
        if (!empty($prp['doNotScroll']) && ($prp['doNotScroll'] == 'true')) {
            $off += 1 << 23;
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
        if (!empty($prp['comb']) && ($prp['comb'] == 'true')) {
            $off += 1 << 24;
        }
        // radiosInUnison:
        // If false, even if a group of radio buttons have the same name and export value,
        // they behave in a mutually exclusive fashion, like HTML radio buttons.
        if (!empty($prp['radiosInUnison']) && ($prp['radiosInUnison'] == 'true')) {
            $off += 1 << 25;
        }
        // richText:
        // If true, the field allows rich text formatting.
        if (!empty($prp['richText']) && ($prp['richText'] == 'true')) {
            $off += 1 << 25;
        }
        // commitOnSelChange:
        // Controls whether a field value is committed after a selection change.
        if (!empty($prp['commitOnSelChange']) && ($prp['commitOnSelChange'] == 'true')) {
            $off += 1 << 26;
        }
        $opt['ff'] = $off;
        // defaultValue:
        // The default value of a field - that is, the value that the field is set to when the form is reset.
        if (isset($prp['defaultValue'])) {
            $opt['dv'] = $prp['defaultValue'];
        }
        $anf = 4; // default value for annotation flags
        // readonly:
        // The read-only characteristic of a field.
        // If a field is read-only, the user can see the field but cannot change it.
        if (!empty($prp['readonly']) && ($prp['readonly'] == 'true')) {
            $anf += 1 << 6;
        }
        // display:
        // Controls whether the field is hidden or visible on screen and in print.
        if (!empty($prp['display'])) {
            if ($prp['display'] == 'display.visible') {
                //
            } elseif ($prp['display'] == 'display.hidden') {
                $anf += 1 << 1;
            } elseif ($prp['display'] == 'display.noPrint') {
                $anf -= 1 << 2;
            } elseif ($prp['display'] == 'display.noView') {
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
                foreach ($prp['value'] as $key => $optval) {
                    // exportValues:
                    // An array of strings representing the export values for the field.
                    // @phpstan-ignore offsetAccess.nonOffsetAccessible
                    if (isset($prp['exportValues'][$key])) {
                        // @phpstan-ignore offsetAccess.nonOffsetAccessible
                        $opt['opt'][$key] = [$prp['exportValues'][$key], $optval];
                    } else {
                        // @phpstan-ignore offsetAccess.nonOffsetAccessible
                        $opt['opt'][$key] = $optval;
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
            $opt['tm'] = $prp['submitName'];
        }
        // name:
        // Fully qualified field name.
        if (isset($prp['name'])) {
            $opt['t'] = $prp['name'];
        }
        // userName:
        // The user name (short description string) of the field.
        if (isset($prp['userName'])) {
            $opt['tu'] = $prp['userName'];
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
        return $opt;
    }
}
