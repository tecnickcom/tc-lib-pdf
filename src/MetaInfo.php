<?php

declare(strict_types=1);

/**
 * MetaInfo.php
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
 *
 * @phpcs:disable Generic.Files.LineLength
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\MetaInfo
 *
 * Meta Informaton PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TViewerPref from Base
 * @phpstan-import-type TObjID from Base
 * @phpstan-import-type TCustomXMP from Base
 * @mixin \Com\Tecnick\Pdf\Base
 * @property string $version
 * @property int $pdfa
 * @property string $pdfaConformance
 * @property string $pdfver
 * @property string $pdfuaMode
 * @property bool $pdfx
 * @property string $pdfxMode
 * @property bool $sRGB
 * @property int $doctime
 * @property int $docmodtime
 * @property string $creator
 * @property string $author
 * @property string $subject
 * @property string $title
 * @property string $keywords
 * @property string $fileid
 * @property \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt
 * @property int $pon
 * @property array<string, mixed> $objid
 * @property array<string, string> $custom_xmp
 * @property array<string, mixed> $viewerpref
 * @property bool $rtl
 * @property bool $isunicode
 *
 * @SuppressWarnings("PHPMD.DepthOfInheritance")
 */
abstract class MetaInfo extends \Com\Tecnick\Pdf\HTML
{
    /**
     * Valid document zoom modes
     *
     * @var array<string>
     */
    protected const VALIDZOOM = ['fullpage', 'fullwidth', 'real', 'default'];

    /**
     * Map normalized page box names to canonical PDF box names.
     *
     * @var array<string, string>
     */
    protected const VALID_PAGE_BOXES = [
        'mediabox' => 'MediaBox',
        'cropbox' => 'CropBox',
        'bleedbox' => 'BleedBox',
        'trimbox' => 'TrimBox',
        'artbox' => 'ArtBox',
    ];

    /**
     * Read an existing property through the documented magic-property path.
     *
     * @param string $name Property name.
     *
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'version' => $this->version,
            'pdfa' => $this->pdfa,
            'pdfaConformance' => $this->pdfaConformance,
            'pdfver' => $this->pdfver,
            'pdfuaMode' => $this->pdfuaMode,
            'pdfx' => $this->pdfx,
            'pdfxMode' => $this->pdfxMode,
            'sRGB' => $this->sRGB,
            'doctime' => $this->doctime,
            'docmodtime' => $this->docmodtime,
            'creator' => $this->creator,
            'author' => $this->author,
            'subject' => $this->subject,
            'title' => $this->title,
            'keywords' => $this->keywords,
            'fileid' => $this->fileid,
            'encrypt' => $this->encrypt,
            'pon' => $this->pon,
            'objid' => $this->objid,
            'custom_xmp' => $this->custom_xmp,
            'viewerpref' => $this->viewerpref,
            'rtl' => $this->rtl,
            default => null,
        };
    }

    /**
     * Write an existing property through the documented magic-property path.
     *
     * @param string $name Property name.
     * @param mixed $value Property value.
     */
    public function __set(string $name, mixed $value): void
    {
        switch ($name) {
            case 'version':
                if (\is_string($value)) {
                    $this->version = $value;
                }
                return;
            case 'pdfa':
                if (\is_int($value)) {
                    $this->pdfa = $value;
                }
                return;
            case 'pdfaConformance':
                if (\is_string($value)) {
                    $this->pdfaConformance = $value;
                }
                return;
            case 'pdfver':
                if (\is_string($value)) {
                    $this->pdfver = $value;
                }
                return;
            case 'pdfuaMode':
                if (\is_string($value)) {
                    $this->pdfuaMode = $value;
                }
                return;
            case 'pdfx':
                if (\is_bool($value)) {
                    $this->pdfx = $value;
                }
                return;
            case 'pdfxMode':
                if (\is_string($value)) {
                    $this->pdfxMode = $value;
                }
                return;
            case 'sRGB':
                if (\is_bool($value)) {
                    $this->sRGB = $value;
                }
                return;
            case 'doctime':
                if (\is_int($value)) {
                    $this->doctime = $value;
                }
                return;
            case 'docmodtime':
                if (\is_int($value)) {
                    $this->docmodtime = $value;
                }
                return;
            case 'creator':
                if (\is_string($value)) {
                    $this->creator = $value;
                }
                return;
            case 'author':
                if (\is_string($value)) {
                    $this->author = $value;
                }
                return;
            case 'subject':
                if (\is_string($value)) {
                    $this->subject = $value;
                }
                return;
            case 'title':
                if (\is_string($value)) {
                    $this->title = $value;
                }
                return;
            case 'keywords':
                if (\is_string($value)) {
                    $this->keywords = $value;
                }
                return;
            case 'fileid':
                if (\is_string($value)) {
                    $this->fileid = $value;
                }
                return;
            case 'encrypt':
                if ($value instanceof \Com\Tecnick\Pdf\Encrypt\Encrypt) {
                    $this->encrypt = $value;
                }
                return;
            case 'pon':
                if (\is_int($value)) {
                    $this->pon = $value;
                }
                return;
            case 'objid':
                if (\is_array($value)) {
                    $objid = $this->objid;
                    if (isset($value['catalog']) && \is_numeric($value['catalog'])) {
                        $objid['catalog'] = (int) $value['catalog'];
                    }
                    if (isset($value['dests']) && \is_numeric($value['dests'])) {
                        $objid['dests'] = (int) $value['dests'];
                    }
                    if (isset($value['dss']) && \is_numeric($value['dss'])) {
                        $objid['dss'] = (int) $value['dss'];
                    }
                    if (isset($value['info']) && \is_numeric($value['info'])) {
                        $objid['info'] = (int) $value['info'];
                    }
                    if (isset($value['pages']) && \is_numeric($value['pages'])) {
                        $objid['pages'] = (int) $value['pages'];
                    }
                    if (isset($value['resdic']) && \is_numeric($value['resdic'])) {
                        $objid['resdic'] = (int) $value['resdic'];
                    }
                    if (isset($value['signature']) && \is_numeric($value['signature'])) {
                        $objid['signature'] = (int) $value['signature'];
                    }
                    if (isset($value['srgbicc']) && \is_numeric($value['srgbicc'])) {
                        $objid['srgbicc'] = (int) $value['srgbicc'];
                    }
                    if (isset($value['xmp']) && \is_numeric($value['xmp'])) {
                        $objid['xmp'] = (int) $value['xmp'];
                    }
                    if (isset($value['form']) && \is_array($value['form'])) {
                        $form = [];
                        foreach (\array_keys($value['form']) as $formKey) {
                            if (!isset($value['form'][$formKey]) || !\is_numeric($value['form'][$formKey])) {
                                continue;
                            }

                            $form[] = (int) $value['form'][$formKey];
                        }
                        $objid['form'] = $form;
                    }

                    $this->objid = $objid;
                }
                return;
            case 'custom_xmp':
                if (\is_array($value)) {
                    $customXmp = $this->custom_xmp;
                    if (isset($value['x:xmpmeta']) && \is_string($value['x:xmpmeta'])) {
                        $customXmp['x:xmpmeta'] = $value['x:xmpmeta'];
                    }
                    if (isset($value['x:xmpmeta.rdf:RDF']) && \is_string($value['x:xmpmeta.rdf:RDF'])) {
                        $customXmp['x:xmpmeta.rdf:RDF'] = $value['x:xmpmeta.rdf:RDF'];
                    }
                    if (
                        isset($value['x:xmpmeta.rdf:RDF.rdf:Description'])
                        && \is_string($value['x:xmpmeta.rdf:RDF.rdf:Description'])
                    ) {
                        $customXmp['x:xmpmeta.rdf:RDF.rdf:Description'] = $value['x:xmpmeta.rdf:RDF.rdf:Description'];
                    }
                    if (
                        isset($value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'])
                        && \is_string($value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'])
                    ) {
                        $customXmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'] =
                            $value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'];
                    }
                    if (
                        isset($value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'])
                        && \is_string($value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'])
                    ) {
                        $customXmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'] =
                            $value['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'];
                    }
                    $this->custom_xmp = $customXmp;
                }
                return;
            case 'viewerpref':
                if (\is_array($value)) {
                    /** @var TViewerPref $viewerPref */
                    $viewerPref = [];
                    if (isset($value['HideToolbar']) && \is_bool($value['HideToolbar'])) {
                        $viewerPref['HideToolbar'] = $value['HideToolbar'];
                    }
                    if (isset($value['HideMenubar']) && \is_bool($value['HideMenubar'])) {
                        $viewerPref['HideMenubar'] = $value['HideMenubar'];
                    }
                    if (isset($value['HideWindowUI']) && \is_bool($value['HideWindowUI'])) {
                        $viewerPref['HideWindowUI'] = $value['HideWindowUI'];
                    }
                    if (isset($value['FitWindow']) && \is_bool($value['FitWindow'])) {
                        $viewerPref['FitWindow'] = $value['FitWindow'];
                    }
                    if (isset($value['CenterWindow']) && \is_bool($value['CenterWindow'])) {
                        $viewerPref['CenterWindow'] = $value['CenterWindow'];
                    }
                    if (isset($value['DisplayDocTitle']) && \is_bool($value['DisplayDocTitle'])) {
                        $viewerPref['DisplayDocTitle'] = $value['DisplayDocTitle'];
                    }
                    if (isset($value['PickTrayByPDFSize']) && \is_bool($value['PickTrayByPDFSize'])) {
                        $viewerPref['PickTrayByPDFSize'] = $value['PickTrayByPDFSize'];
                    }
                    if (isset($value['NonFullScreenPageMode']) && \is_string($value['NonFullScreenPageMode'])) {
                        $viewerPref['NonFullScreenPageMode'] = $value['NonFullScreenPageMode'];
                    }
                    if (isset($value['Direction']) && \is_string($value['Direction'])) {
                        $viewerPref['Direction'] = $value['Direction'];
                    }
                    if (isset($value['ViewArea']) && \is_string($value['ViewArea'])) {
                        $viewerPref['ViewArea'] = $value['ViewArea'];
                    }
                    if (isset($value['ViewClip']) && \is_string($value['ViewClip'])) {
                        $viewerPref['ViewClip'] = $value['ViewClip'];
                    }
                    if (isset($value['PrintArea']) && \is_string($value['PrintArea'])) {
                        $viewerPref['PrintArea'] = $value['PrintArea'];
                    }
                    if (isset($value['PrintClip']) && \is_string($value['PrintClip'])) {
                        $viewerPref['PrintClip'] = $value['PrintClip'];
                    }
                    if (isset($value['PrintScaling']) && \is_string($value['PrintScaling'])) {
                        $viewerPref['PrintScaling'] = $value['PrintScaling'];
                    }
                    if (isset($value['Duplex']) && \is_string($value['Duplex'])) {
                        $viewerPref['Duplex'] = $value['Duplex'];
                    }
                    if (isset($value['NumCopies']) && \is_numeric($value['NumCopies'])) {
                        $viewerPref['NumCopies'] = (int) $value['NumCopies'];
                    }
                    if (isset($value['PrintPageRange']) && \is_array($value['PrintPageRange'])) {
                        $printPageRange = [];
                        foreach (\array_keys($value['PrintPageRange']) as $rangeKey) {
                            if (
                                !isset($value['PrintPageRange'][$rangeKey])
                                || !\is_numeric($value['PrintPageRange'][$rangeKey])
                            ) {
                                continue;
                            }

                            $printPageRange[] = (int) $value['PrintPageRange'][$rangeKey];
                        }
                        $viewerPref['PrintPageRange'] = $printPageRange;
                    }

                    $this->viewerpref = $viewerPref;
                }
                return;
            case 'rtl':
                if (\is_bool($value)) {
                    $this->rtl = $value;
                }
                return;
        }
    }

    /**
     * Format a text string for output.
     *
     * @param string $str String to escape.
     * @param int    $oid Current PDF object number.
     * @param bool   $bom If true set the Byte Order Mark (BOM).
     *
     * @return string escaped string.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    protected function getOutTextString(string $str, int $oid, bool $bom = false): string
    {
        if ($this->isunicode) {
            $str = $this->uniconv->toUTF16BE($str);
            if ($bom) {
                $str = "\xFE\xFF" . $str;
            }
        }

        return $this->encrypt->escapeDataString($str, $oid);
    }

    /**
     * Return the program version.
     */
    public function getVersion(): string
    {
        return $this->version;
    }

    /**
     * Defines the creator of the document.
     * This is typically the name of the application that generates the PDF.
     *
     * @param string $creator The name of the creator.
     */
    public function setCreator(string $creator): static
    {
        if ($creator !== '') {
            $this->creator = $creator;
        }

        return $this;
    }

    /**
     * Defines the author of the document.
     *
     * @param string $author The name of the author.
     */
    public function setAuthor(string $author): static
    {
        if ($author !== '') {
            $this->author = $author;
        }

        return $this;
    }

    /**
     * Defines the subject of the document.
     *
     * @param string $subject The subject.
     */
    public function setSubject(string $subject): static
    {
        if ($subject !== '') {
            $this->subject = $subject;
        }

        return $this;
    }

    /**
     * Defines the title of the document.
     *
     * @param string $title The title.
     */
    public function setTitle(string $title): static
    {
        if ($title !== '') {
            $this->title = $title;
        }

        return $this;
    }

    /**
     * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
     *
     * @param string $keywords Space-separated list of keywords.
     */
    public function setKeywords(string $keywords): static
    {
        if ($keywords !== '') {
            $this->keywords = $keywords;
        }

        return $this;
    }

    /**
     * Set the PDF version (check PDF reference for valid values).
     *
     * @param string $version PDF document version.
     *
     * @throws PdfException in case of error.
     */
    public function setPDFVersion(string $version = '1.7'): static
    {
        // PDF/A-1 is based on and require the PDF 1.4.
        if ($this->pdfa === 1) {
            $this->pdfver = '1.4';
            return $this;
        }

        // PDF/A-2 (ISO 19005-2:2011) and PDF/A-3 (ISO 19005-3:2012)
        // are based on and require the PDF 1.7 standard (ISO 32000-1:2008)
        if ($this->pdfa === 2 || $this->pdfa === 3) {
            $this->pdfver = '1.7';
            return $this;
        }

        // // PDF/A-4 is based on and require the PDF 2.0 (ISO 32000-2)
        if ($this->pdfa === 4) {
            $this->pdfver = '2.0';
            return $this;
        }

        // PDF/UA-2 uses PDF 2.0.
        if ($this->pdfuaMode === 'pdfua2') {
            $this->pdfver = '2.0';
            return $this;
        }

        // PDF/UA and PDF/UA-1 use PDF 1.7.
        if ($this->pdfuaMode !== '') {
            $this->pdfver = '1.7';
            return $this;
        }

        // PDF/X-1a and PDF/X-3 require a minimum of PDF 1.3.
        // PDF/X-4 and PDF/X-5 require a minimum of PDF 1.6.
        if ($this->pdfx) {
            $isvalid = \preg_match('/^[1-9]+[.]\d+$/', $version);
            if ($isvalid !== 1) {
                throw new PdfException('Invalid PDF version format');
            }

            $minVersion = match ($this->pdfxMode) {
                'pdfx4', 'pdfx5' => '1.6',
                default => '1.3',
            };
            $this->pdfver = \version_compare($version, $minVersion, '<') ? $minVersion : $version;
            return $this;
        }

        $isvalid = \preg_match('/^[1-9]+[.]\d+$/', $version);
        if ($isvalid !== 1) {
            throw new PdfException('Invalid PDF version format');
        }

        $this->pdfver = $version;
        return $this;
    }

    /**
     * Returns the canonical GTS_PDFXVersion string for the active PDF/X variant.
     * Used in both the Info dictionary and XMP metadata.
     */
    protected function getGtsPdfxVersionString(): string
    {
        return match ($this->pdfxMode) {
            'pdfx1a' => 'PDF/X-1a:2003',
            'pdfx3' => 'PDF/X-3:2003',
            'pdfx4' => 'PDF/X-4:2010',
            'pdfx5' => 'PDF/X-5g:2010',
            default => 'PDF/X-3:2003',
        };
    }

    /**
     * Set the sRGB mode.
     *
     * @param bool $enabled Set to true to add the default sRGB ICC color profile
     */
    public function setSRGB(bool $enabled): static
    {
        $this->sRGB = $enabled;
        return $this;
    }

    /**
     * Returns a formatted date for meta information.
     * (ref. Chapter 7.9.4 Dates of PDF32000_2008.pdf).
     *
     * @param int $time Time in seconds.
     *
     * @return string date-time string.
     */
    protected function getFormattedDate(int $time): string
    {
        $date = \date('YmdHisp', $time);
        return \str_ends_with($date, 'Z') ? $date : \substr_replace($date, "'", -3, 1) . "'";
    }

    /**
     * Returns a formatted date for XMP meta information.
     *
     * @param int $time Time in seconds.
     *
     * @return string date-time string.
     */
    protected function getXMPFormattedDate(int $time): string
    {
        return \date('Y-m-d\TH:i:sp', $time);
    }

    /**
     * Returns the producer string.
     */
    protected function getProducer(): string
    {
        return (
            "\x54\x43\x50\x44\x46\x20"
            . $this->version
            . "\x20\x28\x68\x74\x74\x70\x73\x3a\x2f\x2f"
            . "\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29"
        );
    }

    /**
     * Returns a formatted date for meta information.
     *
     * @param int $time Time in seconds.
     * @param int $oid  Current PDF object number.
     *
     * @return string escaped date-time string.
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    protected function getOutDateTimeString(int $time, int $oid): string
    {
        if ($time === 0) {
            $time = $this->doctime;
        }

        return $this->encrypt->escapeDataString('D:' . $this->getFormattedDate($time), $oid);
    }

    /**
     * Get the PDF output string for the Document Information Dictionary.
     * (ref. Chapter 14.3.3 Document Information Dictionary of PDF32000_2008.pdf).
     *
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     * @throws \Com\Tecnick\Unicode\Exception
     */
    protected function getOutMetaInfo(): string
    {
        $oid = ++$this->pon;
        $this->objid['info'] = $oid;
        return (
            $oid
            . ' 0 obj'
            . "\n"
            . '<<'
            . ' /Creator '
            . $this->getOutTextString($this->creator, $oid, true)
            . ' /Author '
            . $this->getOutTextString($this->author, $oid, true)
            . ' /Subject '
            . $this->getOutTextString($this->subject, $oid, true)
            . ' /Title '
            . $this->getOutTextString($this->title, $oid, true)
            . ' /Keywords '
            . $this->getOutTextString($this->keywords, $oid, true)
            . ' /Producer '
            . $this->getOutTextString($this->getProducer(), $oid, true)
            . ' /CreationDate '
            . $this->getOutDateTimeString($this->doctime, $oid)
            . ' /ModDate '
            . $this->getOutDateTimeString($this->docmodtime, $oid)
            . ' /Trapped /False'
            . (
                $this->pdfx
                    ? ' /GTS_PDFXVersion ' . $this->getOutTextString($this->getGtsPdfxVersionString(), $oid, true)
                    : ''
            )
            . ' >>'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Escape some special characters (&lt; &gt; &amp;) for XML output.
     *
     * @param string $str Input string to escape.
     */
    protected function getEscapedXML(string $str): string
    {
        return \strtr($str, [
            "\0" => '',
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }

    /**
     * Set additional custom XMP data to be appended just before the end of the tag indicated by the key.
     *
     * IMPORTANT:
     * This data is added as-is without controls, so you have to validate your data before using this method.
     *
     * @param string $key Key for the custom XMP data. Valid keys are:
     *                    - 'x:xmpmeta'
     *                    - 'x:xmpmeta.rdf:RDF'
     *                    - 'x:xmpmeta.rdf:RDF.rdf:Description'
     *                    - 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'
     *                    - 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'
     * @param string $xmp Custom XMP data.
     */
    public function setCustomXMP(string $key, string $xmp): static
    {
        if ($key === '' || $xmp === '') {
            return $this;
        }

        switch ($key) {
            case 'x:xmpmeta':
            case 'x:xmpmeta.rdf:RDF':
            case 'x:xmpmeta.rdf:RDF.rdf:Description':
            case 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas':
            case 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag':
                $this->custom_xmp[$key] = $xmp;
                break;
        }

        return $this;
    }

    /**
     * Get the PDF output string for the XMP data object
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     *
     * @throws \Com\Tecnick\Unicode\Exception
     */
    protected function getOutXMP(): string
    {
        $uuid =
            'uuid:'
            . \substr($this->fileid, 0, 8)
            . '-'
            . \substr($this->fileid, 8, 4)
            . '-'
            . \substr($this->fileid, 12, 4)
            . '-'
            . \substr($this->fileid, 16, 4)
            . '-'
            . \substr($this->fileid, 20, 12);

        // @codingStandardsIgnoreStart
        $xmp =
            '<?xpacket begin="'
            . $this->uniconv->chr(0xfeff)
            . '" id="W5M0MpCehiHzreSzNTczkc9d"?>'
            . "\n"
            . '<x:xmpmeta xmlns:x="adobe:ns:meta/"'
            . ' x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">'
            . "\n"
            . "\t"
            . '<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">'
            . "\n"
            . "\t\t\t"
            . '<dc:format>application/pdf</dc:format>'
            . "\n"
            . "\t\t\t"
            . '<dc:title>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Alt>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li xml:lang="x-default">'
            . $this->getEscapedXML($this->title)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Alt>'
            . "\n"
            . "\t\t\t"
            . '</dc:title>'
            . "\n"
            . "\t\t\t"
            . '<dc:creator>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li>'
            . $this->getEscapedXML($this->author)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Seq>'
            . "\n"
            . "\t\t\t"
            . '</dc:creator>'
            . "\n"
            . "\t\t\t"
            . '<dc:description>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Alt>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li xml:lang="x-default">'
            . $this->getEscapedXML($this->subject)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Alt>'
            . "\n"
            . "\t\t\t"
            . '</dc:description>'
            . "\n"
            . "\t\t\t"
            . '<dc:subject>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Bag>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li>'
            . $this->getEscapedXML($this->keywords)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Bag>'
            . "\n"
            . "\t\t\t"
            . '</dc:subject>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">'
            . "\n"
            . "\t\t\t"
            . '<xmp:CreateDate>'
            . $this->getXMPFormattedDate($this->doctime)
            . '</xmp:CreateDate>'
            . "\n"
            . "\t\t\t"
            . '<xmp:CreatorTool>'
            . $this->getEscapedXML($this->creator)
            . '</xmp:CreatorTool>'
            . "\n"
            . "\t\t\t"
            . '<xmp:ModifyDate>'
            . $this->getXMPFormattedDate($this->docmodtime)
            . '</xmp:ModifyDate>'
            . "\n"
            . "\t\t\t"
            . '<xmp:MetadataDate>'
            . $this->getXMPFormattedDate($this->doctime)
            . '</xmp:MetadataDate>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'
            . "\n"
            . "\t\t\t"
            . '<pdf:Keywords>'
            . $this->getEscapedXML($this->keywords)
            . '</pdf:Keywords>'
            . "\n"
            . "\t\t\t"
            . '<pdf:Producer>'
            . $this->getEscapedXML($this->getProducer())
            . '</pdf:Producer>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'
            . "\n"
            . "\t\t\t"
            . '<xmpMM:DocumentID>'
            . $uuid
            . '</xmpMM:DocumentID>'
            . "\n"
            . "\t\t\t"
            . '<xmpMM:InstanceID>'
            . $uuid
            . '</xmpMM:InstanceID>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n";

        if ($this->pdfa !== 0) {
            $xmp .=
                '		<rdf:Description rdf:about="" xmlns:pdfaid="http://www.aiim.org/pdfa/ns/id/">'
                . "\n"
                . "\t\t\t"
                . '<pdfaid:part>'
                . $this->pdfa
                . '</pdfaid:part>'
                . "\n"
                . "\t\t\t"
                . '<pdfaid:conformance>'
                . $this->pdfaConformance
                . '</pdfaid:conformance>'
                . "\n"
                . "\t\t"
                . '</rdf:Description>'
                . "\n";
        }

        if ($this->pdfuaMode !== '') {
            $part = 1;
            $matches = [];
            if (\preg_match('/^pdfua([12])$/', $this->pdfuaMode, $matches) === 1 && isset($matches[1])) {
                $part = (int) $matches[1];
            }

            $xmp .=
                "\t\t"
                . '<rdf:Description rdf:about="" xmlns:pdfuaid="http://www.aiim.org/pdfua/ns/id/">'
                . "\n"
                . "\t\t\t"
                . '<pdfuaid:part>'
                . $part
                . '</pdfuaid:part>'
                . "\n";
            if ($part === 2) {
                $xmp .= "\t\t\t" . '<pdfuaid:rev>2024</pdfuaid:rev>' . "\n";
            }

            $xmp .= "\t\t" . '</rdf:Description>' . "\n";
        }

        if ($this->pdfx) {
            $xmp .=
                "\t\t"
                . '<rdf:Description rdf:about="" xmlns:pdfxid="http://www.npes.org/pdfx/ns/id/">'
                . "\n"
                . "\t\t\t"
                . '<pdfxid:GTS_PDFXVersion>'
                . $this->getGtsPdfxVersionString()
                . '</pdfxid:GTS_PDFXVersion>'
                . "\n"
                . "\t\t"
                . '</rdf:Description>'
                . "\n";
        }

        // XMP extension schemas
        $xmp .=
            "\t\t"
            . '<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/"'
            . ' xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#"'
            . ' xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'
            . "\n"
            . "\t\t\t"
            . '<pdfaExtension:schemas>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Bag>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:namespaceURI>http://ns.adobe.com/pdf/1.3/</pdfaSchema:namespaceURI>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:prefix>pdf</pdfaSchema:prefix>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:schema>Adobe PDF Schema</pdfaSchema:schema>'
            . "\n"
            . "\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:namespaceURI>http://ns.adobe.com/xap/1.0/mm/</pdfaSchema:namespaceURI>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:prefix>xmpMM</pdfaSchema:prefix>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:schema>XMP Media Management Schema</pdfaSchema:schema>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t\t\t"
            . '<rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:category>internal</pdfaProperty:category>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:description>UUID based identifier'
            . ' for specific incarnation of a document</pdfaProperty:description>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:name>InstanceID</pdfaProperty:name>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:valueType>URI</pdfaProperty:valueType>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t\t\t"
            . '</rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '</pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:namespaceURI>http://www.aiim.org/pdfa/ns/id/</pdfaSchema:namespaceURI>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:prefix>pdfaid</pdfaSchema:prefix>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:schema>PDF/A ID Schema</pdfaSchema:schema>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t\t\t"
            . '<rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:category>internal</pdfaProperty:category>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:description>Part of PDF/A standard</pdfaProperty:description>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:name>part</pdfaProperty:name>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:valueType>Integer</pdfaProperty:valueType>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:category>internal</pdfaProperty:category>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:description>Amendment of PDF/A standard</pdfaProperty:description>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:name>amd</pdfaProperty:name>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:valueType>Text</pdfaProperty:valueType>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:category>internal</pdfaProperty:category>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:description>Conformance level of PDF/A standard</pdfaProperty:description>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:name>conformance</pdfaProperty:name>'
            . "\n"
            . "\t\t\t\t\t\t\t\t\t"
            . '<pdfaProperty:valueType>Text</pdfaProperty:valueType>'
            . "\n"
            . "\t\t\t\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t\t\t\t"
            . '</rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '</pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag']
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Bag>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas']
            . "\n"
            . "\t\t\t"
            . '</pdfaExtension:schemas>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description']
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta.rdf:RDF']
            . "\n"
            . "\t"
            . '</rdf:RDF>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta']
            . "\n"
            . '</x:xmpmeta>'
            . "\n"
            . '<?xpacket end="w"?>';
        // @codingStandardsIgnoreEnd

        $oid = ++$this->pon;
        $this->objid['xmp'] = $oid;

        return (
            $oid
            . ' 0 obj'
            . "\n"
            . '<<'
            . ' /Type /Metadata'
            . ' /Subtype /XML'
            . ' /Length '
            . \strlen($xmp)
            . ' >> stream'
            . "\n"
            . $xmp
            . "\n"
            . 'endstream'
            . "\n"
            . 'endobj'
            . "\n"
        );
    }

    /**
     * Set the viewer preferences dictionary
     * controlling the way the document is to be presented on the screen or in print.
     *
     * @param TViewerPref $pref Array of options (see PDF reference "Viewer Preferences").
     */
    public function setViewerPreferences(array $pref): static
    {
        $this->viewerpref = $pref;
        return $this;
    }

    /**
     * Sanitize the page box name and return the default 'CropBox' in case of error.
     *
     * @param string $name Entry name.
     */
    protected function getPageBoxName(string $name): string
    {
        $box = 'CropBox';
        if (isset($this->viewerpref[$name]) && \is_string($this->viewerpref[$name])) {
            $lookup = \strtolower($this->viewerpref[$name]);
            if (isset(self::VALID_PAGE_BOXES[$lookup])) {
                $box = self::VALID_PAGE_BOXES[$lookup];
            }
        }

        return ' /' . $name . ' /' . $box;
    }

    /**
     * Sanitize the page box name and return the default 'CropBox' in case of error.
     */
    protected function getPagePrintScaling(): string
    {
        $mode = 'AppDefault';
        if (isset($this->viewerpref['PrintScaling'])) {
            $name = \strtolower($this->viewerpref['PrintScaling']);
            $valid = [
                'none' => 'None',
                'appdefault' => 'AppDefault',
            ];
            if (isset($valid[$name])) {
                $mode = $valid[$name];
            }
        }

        return ' /PrintScaling /' . $mode;
    }

    /**
     * Returns the Duplex mode for the Viewer Preferences
     */
    protected function getDuplexMode(): string
    {
        if (isset($this->viewerpref['Duplex'])) {
            $name = \strtolower($this->viewerpref['Duplex']);
            $valid = [
                'simplex' => 'Simplex',
                'duplexflipshortedge' => 'DuplexFlipShortEdge',
                'duplexfliplongedge' => 'DuplexFlipLongEdge',
            ];
            if (isset($valid[$name])) {
                return ' /Duplex /' . $valid[$name];
            }
        }

        return '';
    }

    /**
     * Returns the Viewer Preference boolean entry.
     *
     * @param string $name Entry name.
     */
    protected function getBooleanMode(string $name): string
    {
        if (isset($this->viewerpref[$name])) {
            return ' /' . $name . ' ' . ($this->viewerpref[$name] === true ? 'true' : 'false');
        }

        return '';
    }

    /**
     * Returns the PDF viewer preferences for the catalog section
     */
    protected function getOutViewerPref(): string
    {
        $vpr = $this->viewerpref;
        $out = ' /ViewerPreferences <<';
        if ($this->rtl) {
            $out .= ' /Direction /R2L';
        } else {
            $out .= ' /Direction /L2R';
        }

        $out .= $this->getBooleanMode('HideToolbar');
        $out .= $this->getBooleanMode('HideMenubar');
        $out .= $this->getBooleanMode('HideWindowUI');
        $out .= $this->getBooleanMode('FitWindow');
        $out .= $this->getBooleanMode('CenterWindow');
        // PDF/UA requires DisplayDocTitle true (ISO 14289-1 §7.1); force it if not already explicitly set.
        if ($this->pdfuaMode !== '' && !isset($this->viewerpref['DisplayDocTitle'])) {
            $out .= ' /DisplayDocTitle true';
        } else {
            $out .= $this->getBooleanMode('DisplayDocTitle');
        }
        if (isset($vpr['NonFullScreenPageMode'])) {
            $out .= ' /NonFullScreenPageMode /' . $this->page->getDisplay($vpr['NonFullScreenPageMode']);
        }

        $out .= $this->getPageBoxName('ViewArea');
        $out .= $this->getPageBoxName('ViewClip');
        $out .= $this->getPageBoxName('PrintArea');
        $out .= $this->getPageBoxName('PrintClip');
        $out .= $this->getPagePrintScaling();
        $out .= $this->getDuplexMode();
        $out .= $this->getBooleanMode('PickTrayByPDFSize');
        if (isset($vpr['PrintPageRange'])) {
            $PrintPageRangeNum = '';
            foreach ($vpr['PrintPageRange'] as $pnum) {
                $PrintPageRangeNum .= ' ' . ($pnum - 1) . '';
            }

            $out .= ' /PrintPageRange [' . $PrintPageRangeNum . ' ]';
        }

        if (isset($vpr['NumCopies'])) {
            $out .= ' /NumCopies ' . (int) $vpr['NumCopies'];
        }

        return $out . ' >>';
    }
}
