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
 * @phpstan-import-type TGTransparency from Output
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
     * @param float $heigth Height of the XObject.
     * @param ?TGTransparency $transpgroup Optional group attributes.
     *
     * @return string XObject template object ID.
     */
    public function newXObjectTemplate(
        float $width = 0,
        float $heigth = 0,
        ?array $transpgroup = null,
    ): string {
        $oid = ++$this->pon;
        $tid = 'XT' . $oid;
        $this->xobjtid = $tid;

        $region = $this->page->getRegion();

        if (empty($width) || $width < 0) {
            $width = $region['RW'];
        }

        if (empty($heigth) || $heigth < 0) {
            $heigth = $region['RH'];
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
            'h' => $heigth,
            'outdata' => '',
            'transparency' => $transpgroup,
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

    // Annotation Form Fields



    // /**
    //  * Adds an annotation button form field.
    //  *
    //  * @param string $name field name.
    //  * @param float $posx horizontal position in user units (LTR).
    //  * @param float $posy vertical position in user units (LTR).
    //  * @param float $width width in user units.
    //  * @param float $height height in user units.
    //  * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
    //  * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
    //  *
    //  * @return int PDF Object ID.
    //  */
    // public function addFFButton(
    //     string $name,
    //     float $posx,
    //     float $posy,
    //     float $width,
    //     float $height,
    //     array $opt = [],
    //     array $jsp = [],
    // ): int {
    // }
//
    // /**
    //  * Adds an annotation checkbox form field.
    //  *
    //  * @param string $name field name.
    //  * @param float $posx horizontal position in user units (LTR).
    //  * @param float $posy vertical position in user units (LTR).
    //  * @param float $width width in user units.
    //  * @param float $height height in user units.
    //  * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
    //  * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
    //  *
    //  * @return int PDF Object ID.
    //  */
    // public function addFFCheckBox(
    //     string $name,
    //     float $posx,
    //     float $posy,
    //     float $width,
    //     float $height,
    //     array $opt = [],
    //     array $jsp = [],
    // ): int {
    // }
//
    // /**
    //  * Adds an annotation combobox form field.
    //  *
    //  * @param string $name field name.
    //  * @param float $posx horizontal position in user units (LTR).
    //  * @param float $posy vertical position in user units (LTR).
    //  * @param float $width width in user units.
    //  * @param float $height height in user units.
    //  * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
    //  * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
    //  *
    //  * @return int PDF Object ID.
    //  */
    // public function addFFComboBox(
    //     string $name,
    //     float $posx,
    //     float $posy,
    //     float $width,
    //     float $height,
    //     array $opt = [],
    //     array $jsp = [],
    // ): int {
    // }
//
    // /**
    //  * Adds an annotation listbox form field.
    //  *
    //  * @param string $name field name.
    //  * @param float $posx horizontal position in user units (LTR).
    //  * @param float $posy vertical position in user units (LTR).
    //  * @param float $width width in user units.
    //  * @param float $height height in user units.
    //  * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
    //  * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
    //  *
    //  * @return int PDF Object ID.
    //  */
    // public function addFFListBox(
    //     string $name,
    //     float $posx,
    //     float $posy,
    //     float $width,
    //     float $height,
    //     array $opt = [],
    //     array $jsp = [],
    // ): int {
    // }
//
    // /**
    //  * Adds an annotation radiobutton form field.
    //  *
    //  * @param string $name field name.
    //  * @param float $posx horizontal position in user units (LTR).
    //  * @param float $posy vertical position in user units (LTR).
    //  * @param float $width width in user units.
    //  * @param float $height height in user units.
    //  * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
    //  * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
    //  *
    //  * @return int PDF Object ID.
    //  */
    // public function addFFRadioButton(
    //     string $name,
    //     float $posx,
    //     float $posy,
    //     float $width,
    //     float $height,
    //     array $opt = [],
    //     array $jsp = [],
    // ): int {
    // }
//
 //   /**
 //    * Adds an annotation text form field.
 //    *
 //    * @param string $name field name.
 //    * @param float $posx horizontal position in user units (LTR).
 //    * @param float $posy vertical position in user units (LTR).
 //    * @param float $width width in user units.
 //    * @param float $height height in user units.
 //    * @param TAnnotOpts $opt Array of options (Annotation Types) - all lowercase.
 //    * @param array<string, string> $jsp javascript field properties (see: Javascript for Acrobat API reference).
 //    *
 //    * @return int PDF Object ID.
 //    */
 //   public function addFFText(
 //       string $name,
 //       float $posx,
 //       float $posy,
 //       float $width,
 //       float $height,
 //       array $opt = [],
 //       array $jsp = [],
 //   ): int {
 //       // merge properties
 //       $jsp = \array_merge($this->defJSAnnotProp, $jsp);
 //       $opt = \array_merge($opt, $this->getAnnotOptFromJSProp($jsp));
 //       // set font
 //       $curfont = $this->font->getCurrentFont();
 //       $this->annotation_fonts[$curfont['key']] = $curfont['idx'];
//      $fontstyle = $curfont['outraw'];
 //       $style = $this->graph->getCurrentStyleArray();
 //       if (!empty($style['fillColor'])) {
 //           $txtcol = $this->color->getColorObj($style['fillColor']);
 //           if ($txtcol != null) {
 //               $fontstyle .= ' '.$txtcol->getPdfColor();
 //           }
 //       }
//      $opt['da'] = $fontstyle;
//      // appearance stream
//      $opt['ap'] = [];
//      $opt['ap']['n'] = '/Tx BMC q '.$fontstyle.' ';
//      $text = empty($opt['v']) ? '' : $opt['v'];
//
//
//
//
//      $tmpid = $this->startTemplate($w, $h, false);
//      $align = '';
//      if (isset($popt['q'])) {
//          switch ($popt['q']) {
//              case 0: {
//                  $align = 'L';
//                  break;
//              }
//              case 1: {
//                  $align = 'C';
//                  break;
//              }
//              case 2: {
//                  $align = 'R';
//                  break;
//              }
//              default: {
//                  $align = '';
//                  break;
//              }
//          }
//      }
//      $this->MultiCell($w, $h, $text, 0, $align, false, 0, 0, 0, true, 0, false, true, 0, 'T', false);
//      $this->endTemplate();
//
//      --$this->n;
//      $popt['ap']['n'] .= $this->xobjects[$tmpid]['outdata'];
//      unset($this->xobjects[$tmpid]);
//      $popt['ap']['n'] .= 'Q EMC';
//      // merge options
//      $opt = array_merge($popt, $opt);
//      // remove some conflicting options
//      unset($opt['bs']);
//      // set remaining annotation data
//      $opt['Subtype'] = 'Widget';
//      $opt['ft'] = 'Tx';
//      $opt['t'] = $name;
//      // Additional annotation's parameters (check _putannotsobj() method):
//      //$opt['f']
//      //$opt['as']
//      //$opt['bs']
//      //$opt['be']
//      //$opt['c']
//      //$opt['border']
//      //$opt['h']
//      //$opt['mk'];
//      //$opt['mk']['r']
//      //$opt['mk']['bc'];
//      //$opt['mk']['bg'];
//      unset($opt['mk']['ca']);
//      unset($opt['mk']['rc']);
//      unset($opt['mk']['ac']);
//      unset($opt['mk']['i']);
//      unset($opt['mk']['ri']);
//      unset($opt['mk']['ix']);
//      unset($opt['mk']['if']);
//      //$opt['mk']['if']['sw'];
//      //$opt['mk']['if']['s'];
//      //$opt['mk']['if']['a'];
//      //$opt['mk']['if']['fb'];
//      unset($opt['mk']['tp']);
//      //$opt['tu']
//      //$opt['tm']
//      //$opt['ff']
//      //$opt['v']
//      //$opt['dv']
//      //$opt['a']
//      //$opt['aa']
//      //$opt['q']
//      $this->Annotation($x, $y, $w, $h, $name, $opt, 0);
//
//   }

//@TODO
}
