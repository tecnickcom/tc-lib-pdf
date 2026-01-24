<?php

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
 * @phpstan-import-type TAnnotOpts from Output
 * @phpstan-import-type TGTransparency from Output
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
     * Requires Acrobat Writer or an alternative PDF reader with Javascript support.
     *
     * @param string $type field type: button, checkbox, combobox, listbox, radiobutton, text.
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
    protected function getAnnotOptFromJSProp(array $prp): array
    {
        /** @var TAnnotOpts $opt */
        $opt = [];
        if (empty($prp)) {
            return $opt;
        }
        if (!empty($prp['aopt']) && \is_array($prp['aopt'])) {
            // the annotation options are already defined
            return $prp['aopt']; // @phpstan-ignore return.type
        }
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
        $flg = 0;
        // readonly:
        // The read-only characteristic of a field.
        // If a field is read-only, the user can see the field but cannot change it.
        if (!empty($prp['readonly']) && ($prp['readonly'] == 'true')) {
            $flg += 1 << 0;
        }
        // required:
        // Specifies whether a field requires a value.
        if (!empty($prp['required']) && ($prp['required'] == 'true')) {
            $flg += 1 << 1;
        }
        // multiline:
        // Controls how text is wrapped within the field.
        if (!empty($prp['multiline']) && ($prp['multiline'] == 'true')) {
            $flg += 1 << 12;
        }
        // password:
        // Specifies whether the field should display asterisks when data is entered in the field.
        if (!empty($prp['password']) && ($prp['password'] == 'true')) {
            $flg += 1 << 13;
        }
        // NoToggleToOff:
        // If set, exactly one radio button shall be selected at all times;
        // selecting the currently selected button has no effect.
        if (!empty($prp['NoToggleToOff']) && ($prp['NoToggleToOff'] == 'true')) {
            $flg += 1 << 14;
        }
        // Radio:
        // If set, the field is a set of radio buttons.
        if (!empty($prp['Radio']) && ($prp['Radio'] == 'true')) {
            $flg += 1 << 15;
        }
        // Pushbutton:
        // If set, the field is a pushbutton that does not retain a permanent value.
        if (!empty($prp['Pushbutton']) && ($prp['Pushbutton'] == 'true')) {
            $flg += 1 << 16;
        }
        // Combo:
        // If set, the field is a combo box; if clear, the field is a list box.
        if (!empty($prp['Combo']) && ($prp['Combo'] == 'true')) {
            $flg += 1 << 17;
        }
        // editable:
        // Controls whether a combo box is editable.
        if (!empty($prp['editable']) && ($prp['editable'] == 'true')) {
            $flg += 1 << 18;
        }
        // Sort:
        // If set, the field's option items shall be sorted alphabetically.
        if (!empty($prp['Sort']) && ($prp['Sort'] == 'true')) {
            $flg += 1 << 19;
        }
        // fileSelect:
        // If true, sets the file-select flag in the Options tab of the text field
        // (Field is Used for File Selection).
        if (!empty($prp['fileSelect']) && ($prp['fileSelect'] == 'true')) {
            $flg += 1 << 20;
        }
        // multipleSelection:
        // If true, indicates that a list box allows a multiple selection of items.
        if (!empty($prp['multipleSelection']) && ($prp['multipleSelection'] == 'true')) {
            $flg += 1 << 21;
        }
        // doNotSpellCheck:
        // If true, spell checking is not performed on this editable text field.
        if (!empty($prp['doNotSpellCheck']) && ($prp['doNotSpellCheck'] == 'true')) {
            $flg += 1 << 22;
        }
        // doNotScroll:
        // If true, the text field does not scroll and the user,
        // therefore, is limited by the rectangular region designed for the field.
        if (!empty($prp['doNotScroll']) && ($prp['doNotScroll'] == 'true')) {
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
        if (!empty($prp['comb']) && ($prp['comb'] == 'true')) {
            $flg += 1 << 24;
        }
        // radiosInUnison:
        // If false, even if a group of radio buttons have the same name and export value,
        // they behave in a mutually exclusive fashion, like HTML radio buttons.
        if (!empty($prp['radiosInUnison']) && ($prp['radiosInUnison'] == 'true')) {
            $flg += 1 << 25;
        }
        // richText:
        // If true, the field allows rich text formatting.
        if (!empty($prp['richText']) && ($prp['richText'] == 'true')) {
            $flg += 1 << 25;
        }
        // commitOnSelChange:
        // Controls whether a field value is committed after a selection change.
        if (!empty($prp['commitOnSelChange']) && ($prp['commitOnSelChange'] == 'true')) {
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

    // ===| ANNOTATION |====================================================

    /**
     * Add an embedded file.
     * If a file with the same name already exists, it will be ignored.
     *
     * @param string $file File name (absolute or relative path).
     *
     * @throws PdfException in case of error.
     */
    public function addEmbeddedFile(string $file): void
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            throw new PdfException('Embedded files are not allowed in PDF/A mode version 1 and 2');
        }

        if (empty($file)) {
            throw new PdfException('Empty file name');
        }
        $filekey = \basename((string) $file);
        if (
            ! empty($filekey)
            && empty($this->embeddedfiles[$filekey])
        ) {
            $this->embeddedfiles[$filekey] = [
                'a' => 0,
                'f' => ++$this->pon,
                'n' => ++$this->pon,
                'file' => (string) $file,
                'content' => '',
            ];
        }
    }

    /**
     * Add string content as an embedded file.
     * If a file with the same name already exists, it will be ignored.
     *
     * @param string $file File name to be used a key for the embedded file.
     * @param string $content  Content of the embedded file.
     *
     * @throws PdfException in case of error.
     */
    public function addContentAsEmbeddedFile(string $file, string $content): void
    {
        if (($this->pdfa == 1) || ($this->pdfa == 2)) {
            throw new PdfException('Embedded files are not allowed in PDF/A mode version 1 and 2');
        }
        if (empty($file) || empty($content)) {
            throw new PdfException('Empty file name or content');
        }
        if (empty($this->embeddedfiles[$file])) {
            $this->embeddedfiles[$file] = [
                'a' => 0,
                'f' => ++$this->pon,
                'n' => ++$this->pon,
                'file' => $file,
                'content' => $content,
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
     */
    public function setAnnotation(
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $txt,
        array $opt = [
            'subtype' => 'text',
        ]
    ): int {
        if (empty($opt['subtype'])) {
            $opt['subtype'] = 'text';
        }
        if (!empty($this->xobjtid)) {
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
        switch (\strtolower($opt['subtype'])) {
            case 'fileattachment':
            case 'sound':
                $this->addEmbeddedFile($opt['fs']);
        }
        // Add widgets annotation's icons
        if (isset($opt['mk']['i']) && \is_string($opt['mk']['i'])) {
            $this->image->add($opt['mk']['i']);
        }
        if (isset($opt['mk']['ri']) && \is_string($opt['mk']['ri'])) {
            $this->image->add($opt['mk']['ri']);
        }
        if (isset($opt['mk']['ix']) && \is_string($opt['mk']['ix'])) {
            $this->image->add($opt['mk']['ix']);
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
     */
    public function setLink(
        float $posx,
        float $posy,
        float $width,
        float $height,
        string $link,
    ): int {
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $height,
            $link,
            ['subtype' => 'Link']
        );
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
            'p' => ($page < 0) ? $this->page->getPageID() : $page,
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
    public function setNamedDestination(
        string $name,
        int $page = -1,
        float $posx = 0,
        float $posy = 0,
    ): string {
        $ename = $this->encrypt->encodeNameObject($name);
        $this->dests[$ename] = [
            'p' => ($page < 0) ? $this->page->getPageID() : $page,
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
        $maxlevel = ((\count($this->outlines) > 0) ? (\end($this->outlines)['l'] + 1) : 0);
        $this->outlines[] = [
            't' => $name,
            'u' => $link,
            'l' => (($level < 0) ? 0 : ($level > $maxlevel ? $maxlevel : $level)),
            'p' => (($page < 0) ? $this->page->getPageID() : $page),
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
     */
    public function newXObjectTemplate(
        float $width = 0,
        float $height = 0,
        ?array $transpgroup = null,
    ): string {
        $oid = ++$this->pon;
        $tid = 'XT' . $oid;
        $this->xobjtid = $tid;

        $region = $this->page->getRegion();

        if (empty($width) || $width < 0) {
            $width = $region['RW'];
        }

        if (empty($height) || $height < 0) {
            $height = $region['RH'];
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
     */
    public function exitXObjectTemplate(): void
    {
        // restore page height
        $this->page->setPagePHeight($this->xobjects[$this->xobjtid]['pheight']);
        $this->graph->setPageHeight($this->xobjects[$this->xobjtid]['gheight']);
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

        if (empty($this->xobjects[$tid])) {
            return '';
        }

        $xobj = $this->xobjects[$tid];

        if (empty($width) || $width < 0) {
            $width = \min($xobj['w'], $region['RW']);
        }

        if (empty($height) || $height < 0) {
            $height = \min($xobj['h'], $region['RH']);
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
            0 => ($width / $xobj['w']),
            1 => 0,
            2 => 0,
            3 => ($height / $xobj['h']),
            4 => $this->toPoints($tplx),
            5 => $this->toYPoints($tply + $height),
        ];

        $out = $this->graph->getStartTransform();
        $out .= $this->graph->getTransformation($ctm);
        $out .= '/' . $xobj['id'] . ' Do' . "\n";
        $out .= $this->graph->getStopTransform();

        if (!empty($xobj['annotations'])) {
            foreach ($xobj['annotations'] as $annot) {
                // transform original coordinates
                $clt = $this->graph->getCtmProduct(
                    $ctm,
                    array(
                        1,
                        0,
                        0,
                        1,
                        $this->toPoints($annot['x']),
                        $this->toPoints(-$annot['y']),
                    ),
                );
                $anx = $this->toUnit($clt[4]);
                $any = $this->toYUnit($clt[5] + $this->toUnit($height));

                $crb = $this->graph->getCtmProduct(
                    $ctm,
                    array(
                        1,
                        0,
                        0,
                        1,
                        $this->toPoints(($annot['x'] + $annot['w'])),
                        $this->toPoints((-$annot['y'] - $annot['h'])),
                    ),
                );
                $anw = $this->toUnit($crb[4]) - $anx;
                $anh = $this->toYUnit($crb[5] + $this->toUnit($height)) - $any;

                $out .= $this->setAnnotation(
                    $anx,
                    $any,
                    $anw,
                    $anh,
                    $annot['txt'],
                    $annot['opt']
                );
            }
        }

        return $out;
    }

    /**
     * Add the specified raw PDF content to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $data  The raw PDF content data to add.
     */
    public function addXObjectContent(string $tid, string $data): void
    {
        $this->xobjects[$tid]['outdata'] .= $data;
    }

    /**
     * Add the specified XObject ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The XObject key to add.
     */
    public function addXObjectXObjectID(string $tid, string $key): void
    {
        $this->xobjects[$tid]['xobject'][] = $key;
    }

    /**
     * Add the specified Image ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  TheImage key to add.
     */
    public function addXObjectImageID(string $tid, int $key): void
    {
        $this->xobjects[$tid]['image'][] = $key;
    }

    /**
     * Add the specified Font ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The Font key to add.
     */
    public function addXObjectFontID(string $tid, string $key): void
    {
        $this->xobjects[$tid]['font'][] = $key;
    }

    /**
     * Add the specified Gradient ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  The Gradient key to add.
     */
    public function addXObjectGradientID(string $tid, int $key): void
    {
        $this->xobjects[$tid]['gradient'][] = $key;
    }

    /**
     * Add the specified ExtGState ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param int     $key  The ExtGState key to add.
     */
    public function addXObjectExtGStateID(string $tid, int $key): void
    {
        $this->xobjects[$tid]['extgstate'][] = $key;
    }

    /**
     * Add the specified SpotColor ID to the XObject template.
     *
     * @param string  $tid  The XObject Template object as returned by the newXObjectTemplate method.
     * @param string  $key  The SpotColor key to add.
     */
    public function addXObjectSpotColorID(string $tid, string $key): void
    {
        $this->xobjects[$tid]['spot_colors'][] = $key;
    }

    // ===| ANNOTAION FORM FIELDS |=======================================================

      /**
       * Retyurns the PDF command to ser the default fill color from the style stack.
       *
       * @return string
       */
    protected function getPDFDefFillColor(): string
    {
        $style = $this->graph->getCurrentStyleArray();
        if (!empty($style['fillColor'])) {
            $colobj = $this->color->getColorObj($style['fillColor']);
            if ($colobj != null) {
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
        $this->annotation_fonts[$curfont['key']] = $curfont['idx'];
        $fontstyle = $curfont['outraw'];
        $color = empty($color) ? $this->getPDFDefFillColor() : $color;
        $opt['da'] = $fontstyle . ' ' . $color;
        return $opt; // @phpstan-ignore return.type
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
        $opt['ap']['n'] = '/Tx BMC q ' . $opt['da'] . ' ';
        $tid = $this->newXObjectTemplate($width, $height);
        $defbstyle =  [
            'lineWidth' => $this->toUnit(1),
            'lineCap' => 'square',
            'lineJoin' => 'miter',
            'dashArray' => [],
            'dashPhase' => 0,
            'lineColor' => '#333333',
            'fillColor' => '#cccccc',
        ];
        $bstyle = [
            'all' => $defbstyle,
            0 => $defbstyle, // TOP
            1 => $defbstyle, // RIGHT
            2 => $defbstyle, // BOTTOM
            3 => $defbstyle, // LEFT
        ];
        $bstyle[0]['lineColor'] = $bstyle[3]['lineColor'] = '#e7e7e7';
        $txtbox = $this->getTextCell(
            $caption,
            0,
            0,
            $width,
            $height,
            0,
            0,
            'C',
            'C',
            null,
            $bstyle, // @phpstan-ignore argument.type
        );
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->xobjects[$tid]['outdata'];
        $opt['ap']['n'] .= 'Q EMC';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Btn';
        $opt['t'] = $caption;
        $opt['v'] = $name;
        if (!isset($opt['mk'])) {
            $opt['mk'] = [];
        }
        $oid = ($this->pon + 1); // from setAnnotation
        if (!empty($action) && !\is_array($action)) {
            ++$oid; // from addRawJavaScriptObj
        }
        $opt['mk']['ca'] = $this->getOutTextString($caption, $oid, true);
        $opt['mk']['rc'] = $this->getOutTextString($caption, $oid, true);
        $opt['mk']['ac'] = $this->getOutTextString($caption, $oid, true);
        if (!empty($action)) {
            if (\is_string($action)) {
                // raw javascript action
                $jsoid = $this->addRawJavaScriptObj($action);
                if ($jsoid > 0) {
                    $opt['aa'] = '/D ' . $jsoid . ' 0 R';
                }
            } elseif (\is_array($action)) {
                // form action options as in section 12.7.5 of PDF32000_2008.
                $opt['aa'] = '/D <<';
                $bmode = ['SubmitForm', 'ResetForm', 'ImportData'];
                foreach ($action as $key => $val) {
                    if (($key == 'S') && \is_string($val) && \in_array($val, $bmode)) {
                        $opt['aa'] .= ' /S /' . $val;
                    } elseif (($key == 'F') && (!empty($val)) && \is_string($val)) {
                        $opt['aa'] .= ' /F ' . $this->encrypt->escapeDataString($val, $oid);
                    } elseif (($key == 'Fields') && !empty($val) && \is_array($val)) {
                        $opt['aa'] .= ' /Fields [';
                        foreach ($val as $field) {
                            if (\is_string($field)) {
                                $opt['aa'] .= ' ' . $this->getOutTextString($field, $oid);
                            }
                        }
                        $opt['aa'] .= ']';
                    } elseif (($key == 'Flags')) {
                        $flg = 0;
                        if (\is_array($val)) {
                            foreach ($val as $flag) {
                                $flg += match ($flag) {
                                    'Include/Exclude' => 1 << 0,
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
                                };
                            }
                        } elseif (\is_numeric($val)) {
                            $flg = intval($val);
                        }
                        $opt['aa'] .= ' /Flags ' . $flg;
                    }
                }
                $opt['aa'] .= ' >>';
            }
        }
        unset(
            $this->xobjects[$tid],
            $opt['mk']['i'],  // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ri'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ix'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
        );
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $height,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
        $color = $this->getPDFDefFillColor();
        $jsp['borderStyle'] = 'inset';
        $jsp['value'] = empty($jsp['value']) ? ['Yes'] : $jsp['value'];
        $opt = $this->mergeAnnotOptions($opt, $jsp, $color);
        $onvalue = empty($onvalue) ? 'Yes' : $onvalue;
        $opt['ap'] = [];
        $opt['ap']['n'] = [];
        $rfx = (($this->toPoints($width) - $font['cw'][110]) / 2);
        $rfy = ($this->toPoints($width) - ($font['ascent'] - $font['descent']));
        $opt['ap']['n']['Yes'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(110) . ') Tj ET Q',
            $color,
            $font['outraw'],
            $rfx,
            $rfy,
        );

        $rfx = (($this->toPoints($width) - $font['cw'][111]) / 2);
        // @phpstan-ignore offsetAccess.nonOffsetAccessible
        $opt['ap']['n']['Off'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(111) . ') Tj ET Q',
            $color,
            $font['outraw'],
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
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $width,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
        $opt['ap']['n'] = '/Tx BMC q ' . $opt['da'] . ' ';
        $text = '';
        foreach ($values as $item) {
            if (\is_array($item)) {
                $text .= $item[1] . "\n";
            } else {
                $text .= $item . "\n";
            }
        }
        $tid = $this->newXObjectTemplate($width, $height);
        $txtbox = $this->getTextCell(
            $text,
            $posx,
            $posy,
            $width,
            $height,
            0,
            0,
            'T',
        );
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->xobjects[$tid]['outdata'];
        $opt['ap']['n'] .= 'Q EMC';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Ch';
        $opt['t'] = $name;
        $opt['opt'] = $values;
        unset(
            $this->xobjects[$tid],
            $opt['mk']['ca'],
            $opt['mk']['rc'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ac'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['i'],  // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ri'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ix'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['tp'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
        );
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $height,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
        $opt['ap']['n'] = '/Tx BMC q ' . $opt['da'] . ' ';
        $text = '';
        foreach ($values as $item) {
            if (\is_array($item)) {
                $text .= $item[1] . "\n";
            } else {
                $text .= $item . "\n";
            }
        }
        $tid = $this->newXObjectTemplate($width, $height);
        $txtbox = $this->getTextCell(
            $text,
            $posx,
            $posy,
            $width,
            $height,
            0,
            0,
            'T',
        );
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->xobjects[$tid]['outdata'];
        $opt['ap']['n'] .= 'Q EMC';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Ch';
        $opt['t'] = $name;
        $opt['opt'] = $values;
        unset(
            $this->xobjects[$tid],
            $opt['mk']['ca'],
            $opt['mk']['rc'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ac'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['i'],  // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ri'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ix'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['tp'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
        );
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $height,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
        $color = $this->getPDFDefFillColor();
        $jsp['NoToggleToOff'] = 'true';
        $jsp['Radio'] = 'true';
        $jsp['borderStyle'] = 'inset';
        $opt = $this->mergeAnnotOptions($opt, $jsp, $color);
        $onvalue = empty($onvalue) ? 'On' : $onvalue;
        $defval = ($checked) ? $onvalue : 'Off';
        if (empty($this->radiobuttons[$name])) {
            $oid = ++$this->pon;
            $this->radiobuttons[$name] = [
                'n' => $oid,
                '#readonly#' => false,
                'kids' => [],
            ];
        }
        $this->radiobuttons[$name]['kids'][] = [
            'n' => ($this->pon + 1), // this is assigned on setAnnotation
            'def' => $defval,
        ];
        $opt['ap'] = [];
        $opt['ap']['n'] = [];
        $rfx = (($this->toPoints($width) - $font['cw'][108]) / 2);
        $rfy = ($this->toPoints($width) - ($font['ascent'] - $font['descent']));
        $opt['ap']['n'][$onvalue] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(108) . ') Tj ET Q',
            $color,
            $font['outraw'],
            $rfx,
            $rfy,
        );
        $rfx = (($this->toPoints($width) - $font['cw'][109]) / 2);
        // @phpstan-ignore offsetAccess.nonOffsetAccessible
        $opt['ap']['n']['Off'] = \sprintf(
            'q %s BT %s %F %F Td (' . \chr(109) . ') Tj ET Q',
            $color,
            $font['outraw'],
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
        $this->radiobuttons[$name]['#readonly#'] = ($this->radiobuttons[$name]['#readonly#'] || (bool)($opt['f'] & 64));
        $this->font->popLastFont();
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $width,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
        $opt = $this->mergeAnnotOptions($opt, $jsp);
       // appearance stream
        $opt['ap'] = [];
        $opt['ap']['n'] = '/Tx BMC q ' . $opt['da'] . ' ';
        $text = (!empty($opt['v']) && \is_string($opt['v'])) ? $opt['v'] : '';
        $halign = '';
        if (isset($opt['q'])) {
            $halign = match ($opt['q']) {
                0 => 'L',
                1 => 'C',
                2 => 'R',
                default => '',
            };
        }
        $tid = $this->newXObjectTemplate($width, $height);
        $txtbox = $this->getTextCell(
            $text,
            $posx,
            $posy,
            $width,
            $height,
            0,
            0,
            'T',
            $halign,
        );
        $this->addXObjectContent($tid, $txtbox);
        $this->exitXObjectTemplate();
        $opt['ap']['n'] .= $this->xobjects[$tid]['outdata'];
        $opt['ap']['n'] .= 'Q EMC';
        $opt['Subtype'] = 'Widget';
        $opt['ft'] = 'Tx';
        $opt['t'] = $name;
        unset(
            $this->xobjects[$tid],
            $opt['bs'],
            $opt['mk']['ca'],
            $opt['mk']['rc'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ac'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['i'],  // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ri'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['ix'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['if'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
            $opt['mk']['tp'], // @phpstan-ignore offsetAccess.nonOffsetAccessible
        );
        return $this->setAnnotation(
            $posx,
            $posy,
            $width,
            $height,
            $name,
            $opt, // @phpstan-ignore argument.type
        );
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
     */
    public function addJSCheckBox(
        string $name,
        float $posx,
        float $posy,
        float $width,
        array $jsp = [],
    ): void {
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
     */
    public function addJSRadioButton(
        string $name,
        float $posx,
        float $posy,
        float $width,
        array $jsp = [],
    ): void {
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
