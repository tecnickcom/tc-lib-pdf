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
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 *
 */

namespace Com\Tecnick\Pdf;

use Com\Tecnick\Pdf\Exception as PdfException;

/**
 * Com\Tecnick\Pdf\MetaInfo
 *
 * Meta Information PDF class
 *
 * @since     2002-08-03
 * @category  Library
 * @package   Pdf
 * @author    Nicola Asuni <info@tecnick.com>
 * @copyright 2002-2026 Nicola Asuni - Tecnick.com LTD
 * @license   https://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE)
 * @link      https://github.com/tecnickcom/tc-lib-pdf
 *
 * @phpstan-import-type TViewerPref from Base
 * @phpstan-import-type TObjID from Base
 * @phpstan-import-type TCustomXMP from Base
 * @phpstan-import-type THybridDoc from Base
 * @phpstan-import-type TXMPProperties from Base
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
 * @property string $producersuffix
 * @property string $fileid
 * @property \Com\Tecnick\Pdf\Encrypt\Encrypt $encrypt
 * @property int $pon
 * @property array<string, mixed> $objid
 * @property array<string, string> $custom_xmp
 * @property THybridDoc $hybriddoc
 * @property array<string, mixed> $viewerpref
 * @property array<string, string> $lang
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
     * Document family ID (32 hexadecimal digits) used for the XMP xmpMM:DocumentID
     * property. Empty to derive it from the file ID.
     */
    protected string $documentid = '';

    /**
     * Number of 100 byte padding lines emitted before the end of the XMP packet.
     *
     * @var non-negative-int
     */
    protected int $xmppaddinglines = 20;

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
     * User access permission bit granting content extraction for accessibility
     * (PDF 32000-1 table 22, bit 10) in the encryption /P value.
     */
    protected const PERMBITEXTRACT = 512;

    /**
     * Namespace prefixes in scope at each custom XMP insertion point.
     *
     * Used to check fragments passed to setCustomXMP(). A fragment using any other
     * prefix has to declare it on its own root element.
     *
     * @var array<string, array<string, string>>
     */
    protected const CUSTOMXMPNS = [
        'x:xmpmeta' => [
            'x' => 'adobe:ns:meta/',
        ],
        'x:xmpmeta.rdf:RDF' => [
            'x' => 'adobe:ns:meta/',
            'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
        ],
        'x:xmpmeta.rdf:RDF.rdf:Description' => [
            'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'pdfaExtension' => 'http://www.aiim.org/pdfa/ns/extension/',
            'pdfaSchema' => 'http://www.aiim.org/pdfa/ns/schema#',
            'pdfaProperty' => 'http://www.aiim.org/pdfa/ns/property#',
        ],
        'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas' => [
            'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'pdfaExtension' => 'http://www.aiim.org/pdfa/ns/extension/',
            'pdfaSchema' => 'http://www.aiim.org/pdfa/ns/schema#',
            'pdfaProperty' => 'http://www.aiim.org/pdfa/ns/property#',
        ],
        'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag' => [
            'rdf' => 'http://www.w3.org/1999/02/22-rdf-syntax-ns#',
            'pdfaExtension' => 'http://www.aiim.org/pdfa/ns/extension/',
            'pdfaSchema' => 'http://www.aiim.org/pdfa/ns/schema#',
            'pdfaProperty' => 'http://www.aiim.org/pdfa/ns/property#',
        ],
    ];

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
     * An empty string removes the entry.
     *
     * @param string $creator The name of the creator.
     */
    public function setCreator(string $creator): static
    {
        $this->creator = $creator;
        return $this;
    }

    /**
     * Defines the author of the document.
     *
     * An empty string removes the entry.
     *
     * @param string $author The name of the author.
     */
    public function setAuthor(string $author): static
    {
        $this->author = $author;
        return $this;
    }

    /**
     * Defines the subject of the document.
     *
     * An empty string removes the entry.
     *
     * @param string $subject The subject.
     */
    public function setSubject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Defines the title of the document.
     *
     * An empty string removes the entry.
     *
     * @param string $title The title.
     */
    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Associates keywords with the document, generally in the form 'keyword1 keyword2 ...'.
     *
     * An empty string removes the entry.
     *
     * @param string $keywords Space-separated list of keywords.
     */
    public function setKeywords(string $keywords): static
    {
        $this->keywords = $keywords;
        return $this;
    }

    /**
     * Sets the document creation date.
     *
     * Drives the Info dictionary /CreationDate entry and the xmp:CreateDate XMP
     * property. Defaults to the time the document object was constructed; set it
     * explicitly to record a real creation date or to obtain reproducible output.
     *
     * @param int|\DateTimeInterface $time Seconds since the Unix epoch, or a date object.
     */
    public function setDocCreationDate(int|\DateTimeInterface $time): static
    {
        $this->doctime = $time instanceof \DateTimeInterface ? $time->getTimestamp() : $time;
        return $this;
    }

    /**
     * Sets the document modification date.
     *
     * Drives the Info dictionary /ModDate entry and the xmp:ModifyDate and
     * xmp:MetadataDate XMP properties.
     *
     * @param int|\DateTimeInterface $time Seconds since the Unix epoch, or a date object.
     */
    public function setDocModificationDate(int|\DateTimeInterface $time): static
    {
        $this->docmodtime = $time instanceof \DateTimeInterface ? $time->getTimestamp() : $time;
        return $this;
    }

    /**
     * Sets the document file identifier.
     *
     * Drives the trailer /ID array and the XMP xmpMM:InstanceID property, which
     * default to a random value. Pin it, together with the creation and modification
     * dates, to obtain byte-for-byte reproducible output.
     *
     * @param string $fileid 32 hexadecimal digits, or any other string, which is hashed
     *                       to that form.
     *
     * @throws PdfException if the document is encrypted, since the encryption key is
     *                      derived from the file identifier chosen at construction time.
     */
    public function setFileId(string $fileid): static
    {
        if ($this->encrypt->getEncryptionData()['encrypted']) {
            throw new PdfException('The file ID of an encrypted document cannot be changed');
        }

        $this->fileid = $this->getNormalizedDocumentId($fileid);
        return $this;
    }

    /**
     * Sets the identifier shared by all the renditions of the document.
     *
     * Drives the XMP xmpMM:DocumentID property, which defaults to a value derived from
     * the file identifier.
     *
     * @param string $documentid 32 hexadecimal digits, or any other string, which is
     *                           hashed to that form.
     */
    public function setDocumentId(string $documentid): static
    {
        $this->documentid = $this->getNormalizedDocumentId($documentid);
        return $this;
    }

    /**
     * Sets the number of padding lines emitted before the end of the XMP packet.
     *
     * Each line is 100 bytes. The XMP specification recommends about 2 KB of padding so
     * that a reader can rewrite the metadata in place; set 0 to emit a read-only packet
     * with no padding.
     *
     * @param int $lines Number of padding lines, capped at 200.
     */
    public function setXMPPaddingLines(int $lines): static
    {
        $this->xmppaddinglines = \max(0, \min(200, $lines));
        return $this;
    }

    /**
     * Returns a document identifier as 32 hexadecimal digits.
     */
    protected function getNormalizedDocumentId(string $value): string
    {
        $value = \trim($value);
        if (\preg_match('/^[0-9a-fA-F]{32}$/', $value) === 1) {
            return \strtolower($value);
        }

        return \md5($value);
    }

    /**
     * Sets the trapping status of the document.
     *
     * Drives both the Info dictionary /Trapped entry and the pdf:Trapped XMP
     * property, which ISO 19005 requires to be equivalent.
     *
     * @param string $trapped One of 'True', 'False' or 'Unknown' (case-insensitive).
     *                        PDF/X does not allow 'Unknown'; it is coerced to 'False'.
     */
    public function setTrapped(string $trapped): static
    {
        $value = match (\strtolower(\trim($trapped))) {
            'true' => 'True',
            'false' => 'False',
            'unknown' => 'Unknown',
            default => '',
        };

        if ($value === '') {
            return $this;
        }

        if ($value === 'Unknown' && $this->pdfx) {
            // ISO 15930 requires the trapping status to be known.
            \trigger_error("PDF/X does not allow /Trapped 'Unknown': using 'False'.", E_USER_WARNING);
            $this->trapped = 'False';
            return $this;
        }

        $this->trapped = $value;
        return $this;
    }

    /**
     * Returns the canonical GTS_PDFXVersion string for the active PDF/X variant.
     * Used in both the Info dictionary and XMP metadata.
     *
     * @return string The canonical GTS_PDFXVersion identifier.
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
     * Set the output intent describing the intended printing condition.
     *
     * PDF/X (ISO 15930) requires the output intent to identify a real printing
     * condition: either $identifier is a name registered in the ICC characterization
     * data registry, or $iccfile must supply the profile emitted as
     * /DestOutputProfile. PDF/X-4 and PDF/X-5 always require the embedded profile.
     *
     * @param string $identifier Value of /OutputConditionIdentifier, for example 'FOGRA39'.
     * @param string $iccfile    Path of the ICC profile to embed as /DestOutputProfile.
     *                           Subject to the same allowlist as every other local file
     *                           read: the path must be covered by the 'allowedPaths'
     *                           entry of the constructor fileOptions. Note that setting
     *                           'allowedPaths' replaces the package defaults, so the
     *                           bundled font directory has to be listed again alongside
     *                           the profile location.
     * @param string $info       Human readable value of /Info. Defaults to $identifier.
     * @param string $registry   Value of /RegistryName.
     * @param string $condition  Human readable value of /OutputCondition.
     */
    public function setOutputIntent(
        string $identifier,
        string $iccfile = '',
        string $info = '',
        string $registry = 'http://www.color.org',
        string $condition = '',
    ): static {
        if ($identifier === '') {
            return $this;
        }

        $this->outputintent = [
            'identifier' => $identifier,
            'condition' => $condition,
            'info' => $info === '' ? $identifier : $info,
            'registry' => $registry,
            'iccfile' => $iccfile,
        ];

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
     *
     * The library attribution is always first; the suffix set by
     * setProducerSuffix() follows it.
     */
    protected function getProducer(): string
    {
        $producer =
            "\x54\x43\x50\x44\x46\x20"
            . $this->version
            . "\x20\x28\x68\x74\x74\x70\x73\x3a\x2f\x2f"
            . "\x74\x63\x70\x64\x66\x2e\x6f\x72\x67\x29";

        if ($this->producersuffix === '') {
            return $producer;
        }

        return $producer . ' - ' . $this->producersuffix;
    }

    /**
     * Sets an application identification appended to the producer string.
     *
     * Drives both the Info dictionary /Producer entry and the pdf:Producer XMP
     * property, which ISO 19005 requires to be equivalent. Control characters are
     * removed and the suffix is truncated to 255 characters.
     *
     * @param string $suffix Application identification, or an empty string to remove it.
     */
    public function setProducerSuffix(string $suffix): static
    {
        // Byte-wise: control bytes never occur inside a UTF-8 multi-byte sequence.
        $clean = \preg_replace('/[\x00-\x1F\x7F]/', '', $suffix);
        $this->producersuffix = \mb_substr(\trim($clean ?? ''), 0, 255);
        return $this;
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
     * Warn about metadata that the active conformance mode requires but that the
     * caller has not supplied.
     *
     * These are emitted as warnings rather than exceptions because the document is
     * still structurally valid; only an external conformance check would reject it.
     */
    protected function checkConformanceMetadata(): void
    {
        if ($this->title === '') {
            if ($this->pdfx) {
                // ISO 15930 requires a Title entry in the document information dictionary.
                \trigger_error('PDF/X requires a non-empty document title: call setTitle().', E_USER_WARNING);
            } elseif ($this->pdfuaMode !== '') {
                // ISO 14289-1 clause 7.1 requires the title, and the viewer is told to
                // display it through the ViewerPreferences DisplayDocTitle entry.
                \trigger_error('PDF/UA requires a non-empty document title: call setTitle().', E_USER_WARNING);
            }
        }

        // An absent entry is defaulted to true by getOutViewerPref(), so only an
        // explicit false conflicts with the requirement.
        if ($this->pdfuaMode !== '' && !($this->viewerpref['DisplayDocTitle'] ?? true)) {
            \trigger_error('PDF/UA requires the DisplayDocTitle viewer preference to be true.', E_USER_WARNING);
        }

        $this->checkTaggedModeLanguage();
        $this->checkPdfUaEncryptionPermissions();

        // Factur-X, ZUGFeRD and Order-X are defined as PDF/A-3 documents carrying
        // the XML payload as an associated file.
        if ($this->hybriddoc['uri'] !== '' && $this->pdfa !== 3) {
            \trigger_error(
                'Factur-X/ZUGFeRD/Order-X requires PDF/A-3: set the conformance mode to pdfa3.',
                E_USER_WARNING,
            );
        }
    }

    /**
     * Warn when a tagged document declares a natural language nobody chose.
     *
     * ISO 14289-1 clause 7.2 and ISO 19005 conformance level A require the natural
     * language of the content to be declared. The catalog falls back to 'en-US', which
     * no validator can tell apart from a deliberate choice, so an unset language is
     * reported here.
     */
    protected function checkTaggedModeLanguage(): void
    {
        if (!$this->isTaggedMode()) {
            return;
        }

        if (($this->lang['a_meta_language'] ?? '') !== '') {
            return;
        }

        \trigger_error(
            'Tagged PDF requires the document language: call setLanguage(). Defaulting to "en-US".',
            E_USER_WARNING,
        );
    }

    /**
     * Warn when encryption denies assistive technology access to the content.
     *
     * ISO 14289-1 does not forbid encryption, but an encrypted PDF/UA document has to
     * remain extractable for accessibility purposes (PDF 32000-1 table 22, bit 10).
     * PDF/A and PDF/X forbid encryption altogether, so only PDF/UA is checked here.
     * No validator reports this: veraPDF refuses encrypted files.
     */
    protected function checkPdfUaEncryptionPermissions(): void
    {
        if ($this->pdfuaMode === '') {
            return;
        }

        $encdata = $this->encrypt->getEncryptionData();
        if (!$encdata['encrypted'] || ($encdata['protection'] & self::PERMBITEXTRACT) !== 0) {
            return;
        }

        \trigger_error(
            'PDF/UA requires an encrypted document to allow content extraction for accessibility:'
            . " remove 'extract' from the blocked permissions.",
            E_USER_WARNING,
        );
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
        $this->checkConformanceMetadata();
        $oid = ++$this->pon;
        $this->objid['info'] = $oid;

        // An unset entry is omitted rather than filled with a placeholder: its XMP
        // counterpart is omitted too, which keeps the two descriptions equivalent.
        $optional = '';
        foreach ([
            'Creator' => $this->creator,
            'Author' => $this->author,
            'Subject' => $this->subject,
            'Title' => $this->title,
            'Keywords' => $this->keywords,
        ] as $key => $value) {
            if ($value === '') {
                continue;
            }

            $optional .= ' /' . $key . ' ' . $this->getOutTextString($value, $oid, true);
        }

        return (
            $oid
            . ' 0 obj'
            . "\n"
            . '<<'
            . $optional
            . ' /Producer '
            . $this->getOutTextString($this->getProducer(), $oid, true)
            . ' /CreationDate '
            . $this->getOutDateTimeString($this->doctime, $oid)
            . ' /ModDate '
            . $this->getOutDateTimeString($this->docmodtime, $oid)
            . ' /Trapped /'
            . $this->trapped
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
     * Escape a string for XML output in the XMP packet.
     *
     * Invalid UTF-8 sequences and the control characters that XML 1.0 does not
     * permit are removed before the markup characters (&lt; &gt; &amp;) are
     * escaped, so that arbitrary caller input cannot make the metadata stream
     * unparseable.
     *
     * @param string $str Input string to escape.
     */
    protected function getEscapedXML(string $str): string
    {
        if (!\mb_check_encoding($str, 'UTF-8')) {
            $substitute = \mb_substitute_character();
            \mb_substitute_character(0xfffd);
            $converted = \mb_convert_encoding($str, 'UTF-8', 'UTF-8');
            \mb_substitute_character($substitute);
            $str = \is_string($converted) ? $converted : '';
        }

        // XML 1.0 allows only tab, line feed, carriage return and code points >= 0x20.
        $stripped = \preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $str);
        if ($stripped !== null) {
            $str = $stripped;
        }

        return \strtr($str, [
            '&' => '&amp;',
            '<' => '&lt;',
            '>' => '&gt;',
        ]);
    }

    /**
     * Set additional custom XMP data to be appended just before the end of the tag indicated by the key.
     *
     * Repeated calls with the same key append to the previously stored fragments,
     * separated by a newline. Pass $replace to overwrite them instead; an empty
     * $xmp with $replace clears the key.
     *
     * The fragment is checked for XML well-formedness in the namespace context of
     * the insertion point (see CUSTOMXMPNS). A fragment using any other prefix must
     * declare it on its own root element: the 'rdf:Description' keys inject into the
     * PDF/A extension schema description, where only three prefixes are in scope.
     * DOCTYPE and ENTITY declarations are rejected.
     *
     * @param string $key     Key for the custom XMP data. Valid keys are:
     *                        - 'x:xmpmeta'
     *                        - 'x:xmpmeta.rdf:RDF'
     *                        - 'x:xmpmeta.rdf:RDF.rdf:Description'
     *                        - 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'
     *                        - 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'
     * @param string $xmp     Custom XMP data.
     *                        Each 'rdf:li' added to the 'pdfaExtension:schemas' bag should contain
     *                        'pdfaSchema:schema', 'pdfaSchema:namespaceURI', 'pdfaSchema:prefix',
     *                        'pdfaSchema:property' and 'pdfaSchema:valueType'. Set the latter to an
     *                        empty 'rdf:Seq' when the schema defines no custom structured value type:
     *                        strict PDF/A validators reject extension schema entries without it.
     * @param bool   $replace Replace the fragments already stored for the key instead of appending.
     *
     * @throws PdfException if the fragment is not well-formed in the context of the key.
     */
    public function setCustomXMP(string $key, string $xmp, bool $replace = false): static
    {
        switch ($key) {
            case 'x:xmpmeta':
            case 'x:xmpmeta.rdf:RDF':
            case 'x:xmpmeta.rdf:RDF.rdf:Description':
            case 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas':
            case 'x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag':
                break;
            default:
                return $this;
        }

        if ($xmp === '') {
            if ($replace) {
                $this->custom_xmp[$key] = '';
            }

            return $this;
        }

        $this->checkCustomXMP($key, $xmp);

        $current = $this->custom_xmp[$key] ?? '';
        $this->custom_xmp[$key] = $replace || $current === '' ? $xmp : $current . "\n" . $xmp;

        return $this;
    }

    /**
     * Check that a custom XMP fragment is well-formed where it will be inserted.
     *
     * The fragment is parsed inside a synthetic root element declaring the prefixes
     * in scope at the insertion point, so that an undeclared prefix is reported here
     * instead of corrupting the metadata stream.
     *
     * @param string $key Key for the custom XMP data.
     * @param string $xmp Custom XMP data.
     *
     * @throws PdfException if the fragment is not well-formed.
     */
    protected function checkCustomXMP(string $key, string $xmp): void
    {
        if (\preg_match('/<!(?:DOCTYPE|ENTITY)/i', $xmp) === 1) {
            throw new PdfException(
                'Custom XMP fragment for "' . $key . '" must not contain a DOCTYPE or ENTITY declaration',
            );
        }

        $wrapper = '<tcpdfCustomXmp';
        foreach (self::CUSTOMXMPNS[$key] ?? [] as $prefix => $uri) {
            $wrapper .= ' xmlns:' . $prefix . '="' . $uri . '"';
        }

        $wrapper .= '>' . $xmp . '</tcpdfCustomXmp>';

        $internal = \libxml_use_internal_errors(true);
        \libxml_clear_errors();
        $parsed = \simplexml_load_string($wrapper, \SimpleXMLElement::class, LIBXML_NONET);
        $errors = \libxml_get_errors();
        \libxml_clear_errors();
        \libxml_use_internal_errors($internal);

        if ($parsed !== false && $errors === []) {
            return;
        }

        $error = $errors[0] ?? null;
        $detail = $error instanceof \LibXMLError
            ? \trim($error->message) . ' (line ' . $error->line . ')'
            : 'malformed XML';

        throw new PdfException('Invalid custom XMP fragment for "' . $key . '": ' . $detail);
    }

    /**
     * Embed the XML payload of a hybrid electronic document (Factur-X, ZUGFeRD,
     * Order-X) and declare it in the XMP metadata.
     *
     * The XML is embedded as an associated file with the name, MIME type and
     * /AFRelationship required by the profile, and the matching PDF/A extension
     * schema and profile properties are added to the metadata stream.
     *
     * The document must be in PDF/A-3 mode: a warning is raised at output time
     * otherwise.
     *
     * @param string                   $xml      Content of the XML payload.
     * @param HybridProfile|string     $profile  Standard followed by the payload.
     * @param HybridConformance|string $level    Conformance level of the payload.
     * @param string                   $doctype  DocumentType property; defaults to the profile value.
     * @param string                   $filename Name of the embedded file; defaults to the profile value.
     * @param string                   $version  Version property; defaults to the profile value.
     * @param string                   $desc     Description of the embedded file; defaults to the profile value.
     *
     * @throws PdfException in case of error.
     */
    public function setFacturX(
        string $xml,
        HybridProfile|string $profile = HybridProfile::FacturX,
        HybridConformance|string $level = HybridConformance::En16931,
        string $doctype = '',
        string $filename = '',
        string $version = '',
        string $desc = '',
    ): static {
        $profile = HybridProfile::fromLoose($profile);
        $level = HybridConformance::fromLoose($level);

        if (\trim($xml) === '') {
            throw new PdfException('Empty XML payload');
        }

        $filename = $filename === '' ? $profile->fileName() : \basename($filename);
        $desc = $desc === '' ? $profile->description() : $desc;

        $this->addContentAsEmbeddedFile($filename, $xml, 'text/xml', AFRelationship::Alternative, $desc);

        $this->hybriddoc = [
            'filename' => $filename,
            'doctype' => $doctype === '' ? $profile->documentType() : $doctype,
            'version' => $version === '' ? $profile->version() : $version,
            'conformance' => $level->value,
            'uri' => $profile->namespaceUri(),
            'prefix' => $profile->prefix(),
            'schema' => $profile->schemaName(),
        ];

        return $this;
    }

    /**
     * Get the PDF output string for the XMP data object
     *
     * @SuppressWarnings("PHPMD.ExcessiveMethodLength")
     *
     * @throws \Com\Tecnick\Unicode\Exception
     * @throws \Com\Tecnick\Pdf\Encrypt\Exception
     */
    protected function getOutXMP(): string
    {
        // XMP defines DocumentID as stable across the renditions of a document and
        // InstanceID as unique to one saved instance, so the two differ.
        $instanceid = $this->getFormattedUuid($this->fileid);
        $documentid = $this->getFormattedUuid(
            $this->documentid === '' ? \md5('document:' . $this->fileid) : $this->documentid,
        );

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
            . $this->getOutXMPAltProperty('dc:title', $this->title)
            . $this->getOutXMPSeqProperty('dc:creator', $this->author)
            . $this->getOutXMPAltProperty('dc:description', $this->subject)
            . $this->getOutXMPKeywordBag()
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
            . $this->getOutXMPSimpleProperty('xmp:CreatorTool', $this->creator)
            . "\t\t\t"
            . '<xmp:ModifyDate>'
            . $this->getXMPFormattedDate($this->docmodtime)
            . '</xmp:ModifyDate>'
            . "\n"
            . "\t\t\t"
            . '<xmp:MetadataDate>'
            . $this->getXMPFormattedDate($this->docmodtime)
            . '</xmp:MetadataDate>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">'
            . "\n"
            . $this->getOutXMPSimpleProperty('pdf:Keywords', $this->keywords)
            . "\t\t\t"
            . '<pdf:Producer>'
            . $this->getEscapedXML($this->getProducer())
            . '</pdf:Producer>'
            . "\n"
            // ISO 19005 requires every Info dictionary entry with an analogous
            // predefined XMP property to be mirrored here with an equal value.
            . "\t\t\t"
            . '<pdf:Trapped>'
            . $this->trapped
            . '</pdf:Trapped>'
            . "\n"
            . "\t\t"
            . '</rdf:Description>'
            . "\n"
            . "\t\t"
            . '<rdf:Description rdf:about="" xmlns:xmpMM="http://ns.adobe.com/xap/1.0/mm/">'
            . "\n"
            . "\t\t\t"
            . '<xmpMM:DocumentID>'
            . $documentid
            . '</xmpMM:DocumentID>'
            . "\n"
            . "\t\t\t"
            . '<xmpMM:InstanceID>'
            . $instanceid
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

        $xmp .= $this->getOutXMPHybridDoc();

        if ($this->pdfuaMode !== '') {
            $part = $this->getPdfuaPart();

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
        $xmp .= $this->getOutXMPExtensionSchemas();

        $xmp .=
            $this->custom_xmp['x:xmpmeta.rdf:RDF']
            . "\n"
            . "\t"
            . '</rdf:RDF>'
            . "\n"
            . $this->custom_xmp['x:xmpmeta']
            . "\n"
            . '</x:xmpmeta>'
            . "\n"
            // A writable packet requires trailing padding for a reader to rewrite the
            // metadata in place without moving the rest of the file. Without padding the
            // packet is declared read-only.
            . $this->getXMPPadding()
            . ($this->xmppaddinglines > 0 ? '<?xpacket end="w"?>' : '<?xpacket end="r"?>');

        $oid = ++$this->pon;
        $this->objid['xmp'] = $oid;

        // The metadata stream follows the document encryption unless /EncryptMetadata is false.
        if ($this->encrypt->getEncryptionData()['EncryptMetadata']) {
            $xmp = $this->encrypt->encryptString($xmp, $oid);
        }

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
     * Returns a 32 hexadecimal digit identifier as an XMP uuid URI.
     */
    protected function getFormattedUuid(string $id): string
    {
        return (
            'uuid:'
            . \substr($id, 0, 8)
            . '-'
            . \substr($id, 8, 4)
            . '-'
            . \substr($id, 12, 4)
            . '-'
            . \substr($id, 16, 4)
            . '-'
            . \substr($id, 20, 12)
        );
    }

    /**
     * Returns the whitespace padding of a writable XMP packet.
     *
     * The XMP specification recommends about 2 KB of padding, laid out as lines of
     * spaces, so that a reader can grow the packet in place.
     */
    protected function getXMPPadding(): string
    {
        return \str_repeat(\str_repeat(' ', 99) . "\n", $this->xmppaddinglines);
    }

    /**
     * Get the XMP description block declaring the hybrid electronic document
     * properties.
     *
     * Returns an empty string when setFacturX() has not been called.
     */
    protected function getOutXMPHybridDoc(): string
    {
        if ($this->hybriddoc['uri'] === '') {
            return '';
        }

        $prefix = $this->hybriddoc['prefix'];

        $out = "\t\t" . '<rdf:Description rdf:about="" xmlns:' . $prefix . '="' . $this->hybriddoc['uri'] . '">' . "\n";

        foreach ([
            'DocumentType' => $this->hybriddoc['doctype'],
            'DocumentFileName' => $this->hybriddoc['filename'],
            'Version' => $this->hybriddoc['version'],
            'ConformanceLevel' => $this->hybriddoc['conformance'],
        ] as $name => $value) {
            $out .=
                "\t\t\t"
                . '<'
                . $prefix
                . ':'
                . $name
                . '>'
                . $this->getEscapedXML($value)
                . '</'
                . $prefix
                . ':'
                . $name
                . '>'
                . "\n";
        }

        return $out . "\t\t" . '</rdf:Description>' . "\n";
    }

    /**
     * Returns an XMP property holding a simple value, or an empty string when unset.
     *
     * An unset property is omitted so that it stays equivalent to the matching Info
     * dictionary entry, which is omitted too.
     *
     * @param string $name  Qualified property name.
     * @param string $value Property value.
     */
    protected function getOutXMPSimpleProperty(string $name, string $value): string
    {
        if ($value === '') {
            return '';
        }

        return "\t\t\t" . '<' . $name . '>' . $this->getEscapedXML($value) . '</' . $name . '>' . "\n";
    }

    /**
     * Returns an XMP property holding a language alternative, or an empty string
     * when unset.
     *
     * @param string $name  Qualified property name.
     * @param string $value Property value.
     */
    protected function getOutXMPAltProperty(string $name, string $value): string
    {
        if ($value === '') {
            return '';
        }

        return (
            "\t\t\t"
            . '<'
            . $name
            . '>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Alt>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li xml:lang="x-default">'
            . $this->getEscapedXML($value)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Alt>'
            . "\n"
            . "\t\t\t"
            . '</'
            . $name
            . '>'
            . "\n"
        );
    }

    /**
     * Returns an XMP property holding an ordered array, or an empty string when unset.
     *
     * @param string $name  Qualified property name.
     * @param string $value Property value.
     */
    protected function getOutXMPSeqProperty(string $name, string $value): string
    {
        if ($value === '') {
            return '';
        }

        return (
            "\t\t\t"
            . '<'
            . $name
            . '>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t"
            . '<rdf:li>'
            . $this->getEscapedXML($value)
            . '</rdf:li>'
            . "\n"
            . "\t\t\t\t"
            . '</rdf:Seq>'
            . "\n"
            . "\t\t\t"
            . '</'
            . $name
            . '>'
            . "\n"
        );
    }

    /**
     * Returns the dc:subject bag, or an empty string when no keyword is set.
     *
     * dc:subject is an unordered set of individual keywords, unlike the
     * pdf:Keywords property which mirrors the raw Info dictionary string.
     */
    protected function getOutXMPKeywordBag(): string
    {
        $items = $this->getOutXMPKeywordItems();
        if ($items === '') {
            return '';
        }

        return (
            "\t\t\t"
            . '<dc:subject>'
            . "\n"
            . "\t\t\t\t"
            . '<rdf:Bag>'
            . "\n"
            . $items
            . "\t\t\t\t"
            . '</rdf:Bag>'
            . "\n"
            . "\t\t\t"
            . '</dc:subject>'
            . "\n"
        );
    }

    /**
     * Returns the rdf:li items of the dc:subject bag.
     *
     * dc:subject is an unordered set of individual keywords, unlike the
     * pdf:Keywords property which mirrors the raw Info dictionary string.
     */
    protected function getOutXMPKeywordItems(): string
    {
        $keywords = \preg_split('/\s+/', \trim($this->keywords), -1, PREG_SPLIT_NO_EMPTY);
        if ($keywords === false || $keywords === []) {
            return '';
        }

        $out = '';
        foreach ($keywords as $keyword) {
            $out .= "\t\t\t\t\t" . '<rdf:li>' . $this->getEscapedXML($keyword) . '</rdf:li>' . "\n";
        }

        return $out;
    }

    /**
     * Returns the PDF/UA part number for the active PDF/UA mode.
     */
    protected function getPdfuaPart(): int
    {
        $matches = [];
        if (\preg_match('/^pdfua([12])$/', $this->pdfuaMode, $matches) === 1 && isset($matches[1])) {
            return (int) $matches[1];
        }

        return 1;
    }

    /**
     * Get one XMP extension schema description.
     *
     * @param string         $name       Schema name.
     * @param string         $uri        Schema namespace URI.
     * @param string         $prefix     Schema namespace prefix.
     * @param TXMPProperties $properties Properties described by the schema.
     */
    protected function getOutXMPSchemaEntry(string $name, string $uri, string $prefix, array $properties): string
    {
        $out =
            "\t\t\t\t\t"
            . '<rdf:li rdf:parseType="Resource">'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:namespaceURI>'
            . $this->getEscapedXML($uri)
            . '</pdfaSchema:namespaceURI>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:prefix>'
            . $this->getEscapedXML($prefix)
            . '</pdfaSchema:prefix>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:schema>'
            . $this->getEscapedXML($name)
            . '</pdfaSchema:schema>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t\t\t"
            . '<rdf:Seq>'
            . "\n";

        foreach ($properties as $property) {
            $out .=
                "\t\t\t\t\t\t\t\t"
                . '<rdf:li rdf:parseType="Resource">'
                . "\n"
                . "\t\t\t\t\t\t\t\t\t"
                . '<pdfaProperty:category>'
                . $this->getEscapedXML($property['category'])
                . '</pdfaProperty:category>'
                . "\n"
                . "\t\t\t\t\t\t\t\t\t"
                . '<pdfaProperty:description>'
                . $this->getEscapedXML($property['description'])
                . '</pdfaProperty:description>'
                . "\n"
                . "\t\t\t\t\t\t\t\t\t"
                . '<pdfaProperty:name>'
                . $this->getEscapedXML($property['name'])
                . '</pdfaProperty:name>'
                . "\n"
                . "\t\t\t\t\t\t\t\t\t"
                . '<pdfaProperty:valueType>'
                . $this->getEscapedXML($property['valueType'])
                . '</pdfaProperty:valueType>'
                . "\n"
                . "\t\t\t\t\t\t\t\t"
                . '</rdf:li>'
                . "\n";
        }

        // The empty 'valueType' element is required by strict PDF/A validators
        // even when the schema defines no custom structured value type.
        return (
            $out
            . "\t\t\t\t\t\t\t"
            . '</rdf:Seq>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '</pdfaSchema:property>'
            . "\n"
            . "\t\t\t\t\t\t"
            . '<pdfaSchema:valueType><rdf:Seq /></pdfaSchema:valueType>'
            . "\n"
            . "\t\t\t\t\t"
            . '</rdf:li>'
            . "\n"
        );
    }

    /**
     * Get the XMP extension schema entries describing the non-predefined
     * properties written by the active conformance mode.
     *
     * Returns an empty string when no conformance mode is active.
     */
    protected function getOutXMPSchemaEntries(): string
    {
        if ($this->pdfa === 0 && $this->pdfuaMode === '' && !$this->pdfx && $this->hybriddoc['uri'] === '') {
            return '';
        }

        $out = $this->getOutXMPSchemaEntry('XMP Media Management Schema', 'http://ns.adobe.com/xap/1.0/mm/', 'xmpMM', [
            [
                'category' => 'internal',
                'description' => 'UUID based identifier for specific incarnation of a document',
                'name' => 'InstanceID',
                'valueType' => 'URI',
            ],
            [
                'category' => 'internal',
                'description' => 'UUID based identifier for the document',
                'name' => 'DocumentID',
                'valueType' => 'URI',
            ],
        ]);

        // pdf:Trapped mirrors the Info dictionary /Trapped entry. It is not part of the
        // predefined set recognized by PDF/A validators, so it has to be described here.
        $out .= $this->getOutXMPSchemaEntry('Adobe PDF Schema', 'http://ns.adobe.com/pdf/1.3/', 'pdf', [[
            'category' => 'internal',
            'description' => 'Trapping status of the document',
            'name' => 'Trapped',
            'valueType' => 'Text',
        ]]);

        if ($this->pdfa !== 0) {
            $out .= $this->getOutXMPSchemaEntry('PDF/A ID Schema', 'http://www.aiim.org/pdfa/ns/id/', 'pdfaid', [
                [
                    'category' => 'internal',
                    'description' => 'Part of PDF/A standard',
                    'name' => 'part',
                    'valueType' => 'Integer',
                ],
                [
                    'category' => 'internal',
                    'description' => 'Amendment of PDF/A standard',
                    'name' => 'amd',
                    'valueType' => 'Text',
                ],
                [
                    'category' => 'internal',
                    'description' => 'Conformance level of PDF/A standard',
                    'name' => 'conformance',
                    'valueType' => 'Text',
                ],
            ]);
        }

        if ($this->pdfuaMode !== '') {
            $properties = [[
                'category' => 'internal',
                'description' => 'Part of ISO 14289 standard',
                'name' => 'part',
                'valueType' => 'Integer',
            ]];
            if ($this->getPdfuaPart() === 2) {
                $properties[] = [
                    'category' => 'internal',
                    'description' => 'Revision of ISO 14289 standard',
                    'name' => 'rev',
                    'valueType' => 'Integer',
                ];
            }

            $out .= $this->getOutXMPSchemaEntry(
                'PDF/UA Universal Accessibility Schema',
                'http://www.aiim.org/pdfua/ns/id/',
                'pdfuaid',
                $properties,
            );
        }

        if ($this->pdfx) {
            $out .= $this->getOutXMPSchemaEntry('PDF/X ID Schema', 'http://www.npes.org/pdfx/ns/id/', 'pdfxid', [[
                'category' => 'internal',
                'description' => 'ID of PDF/X standard',
                'name' => 'GTS_PDFXVersion',
                'valueType' => 'Text',
            ]]);
        }

        if ($this->hybriddoc['uri'] !== '') {
            $out .= $this->getOutXMPSchemaEntry(
                $this->hybriddoc['schema'],
                $this->hybriddoc['uri'],
                $this->hybriddoc['prefix'],
                [
                    [
                        'category' => 'external',
                        'description' => 'The name of the embedded XML document',
                        'name' => 'DocumentFileName',
                        'valueType' => 'Text',
                    ],
                    [
                        'category' => 'external',
                        'description' => 'The type of the hybrid document in capital letters, e.g. INVOICE or ORDER',
                        'name' => 'DocumentType',
                        'valueType' => 'Text',
                    ],
                    [
                        'category' => 'external',
                        'description' => 'The actual version of the standard applying to the embedded XML document',
                        'name' => 'Version',
                        'valueType' => 'Text',
                    ],
                    [
                        'category' => 'external',
                        'description' => 'The conformance level of the embedded XML document',
                        'name' => 'ConformanceLevel',
                        'valueType' => 'Text',
                    ],
                ],
            );
        }

        return $out;
    }

    /**
     * Get the XMP extension schema description block.
     *
     * Returns an empty string when neither a conformance mode nor a custom XMP
     * fragment requires the block.
     */
    protected function getOutXMPExtensionSchemas(): string
    {
        $custombag = $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag'];
        $customschemas = $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas'];
        $customdesc = $this->custom_xmp['x:xmpmeta.rdf:RDF.rdf:Description'];
        $entries = $this->getOutXMPSchemaEntries();

        $schemas = '';
        if ($entries !== '' || $custombag !== '' || $customschemas !== '') {
            $schemas =
                "\t\t\t"
                . '<pdfaExtension:schemas>'
                . "\n"
                . "\t\t\t\t"
                . '<rdf:Bag>'
                . "\n"
                . $entries
                . ($custombag === '' ? '' : $custombag . "\n")
                . "\t\t\t\t"
                . '</rdf:Bag>'
                . "\n"
                . ($customschemas === '' ? '' : $customschemas . "\n")
                . "\t\t\t"
                . '</pdfaExtension:schemas>'
                . "\n";
        }

        if ($schemas === '' && $customdesc === '') {
            return '';
        }

        // The namespace declarations are kept even when no schema is described,
        // so that custom fragments using those prefixes still resolve.
        return (
            "\t\t"
            . '<rdf:Description rdf:about="" xmlns:pdfaExtension="http://www.aiim.org/pdfa/ns/extension/"'
            . ' xmlns:pdfaSchema="http://www.aiim.org/pdfa/ns/schema#"'
            . ' xmlns:pdfaProperty="http://www.aiim.org/pdfa/ns/property#">'
            . "\n"
            . $schemas
            . ($customdesc === '' ? '' : $customdesc . "\n")
            . "\t\t"
            . '</rdf:Description>'
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
     * Returns the PrintScaling entry for the Viewer Preferences.
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
