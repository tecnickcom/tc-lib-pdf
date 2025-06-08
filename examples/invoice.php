<?php
/**
 * index.php
 *
 * @since       2017-05-08
 * @category    Library
 * @package     Pdf
 * @author      Nicola Asuni <info@tecnick.com>
 * @copyright   2002-2025 Nicola Asuni - Tecnick.com LTD
 * @license     http://www.gnu.org/copyleft/lesser.html GNU-LGPL v3 (see LICENSE.TXT)
 * @link        https://github.com/tecnickcom/tc-lib-pdf
 *
 * This file is part of tc-lib-pdf software library.
 */

// NOTE: run make deps fonts in the project root to generate the dependencies and example fonts.

// autoloader when using Composer
require(__DIR__ . '/../vendor/autoload.php');


define('OUTPUT_FILE', realpath(__DIR__ . '/../target') . '/example_invoice.pdf');

// define fonts directory
define('K_PATH_FONTS', realpath(__DIR__ . '/../vendor/tecnickcom/tc-lib-pdf-font/target/fonts'));

// autoloader when using RPM or DEB package installation
//require ('/usr/share/php/Com/Tecnick/Pdf/autoload.php');

// main TCPDF object
$pdf = new \Com\Tecnick\Pdf\Tcpdf(
    'mm', // string $unit = 'mm',
    true, // bool $isunicode = true,
    false, // bool $subsetfont = false,
    true, // bool $compress = true,
    'pdfa3', // string $mode = '',
    null, // ?ObjEncrypt $objEncrypt = null,
);

// ----------


$pdf->setCreator('tc-lib-pdf');
$pdf->setAuthor('John Doe');
$pdf->setSubject('tc-lib-pdf invoice example');
$pdf->setTitle('Example: Invoice');
$pdf->setKeywords('TCPDF tc-lib-pdf invoice example');
$pdf->setPDFFilename('test_invoice.pdf');

$pdf->setViewerPreferences(['DisplayDocTitle' => true]);

$pdf->enableDefaultPageContent();

// ----------
// Insert fonts

$bfont1 = $pdf->font->insert($pdf->pon, 'helvetica', '', 12);

// ----------


// Factur-X 1.07 / ZUGFeRD 2.3

$pageF01 = $pdf->addPage();
$pdf->setBookmark('Factur', '', 0, -1, 0, 0, 'B', '');

$pdf->page->addContent($bfont1['out']);

$txtF1 = 'Example of custom XMP metadata for Factur-X 1.07 / ZUGFeRD 2.3';
$txtboxF1 = $pdf->getTextCell($txtF1, 15, 15, 150, valign: 'T', halign: 'L');
$pdf->page->addContent($txtboxF1);

$invoiceXML = <<<XML
<?xml version='1.0' encoding='UTF-8' ?>
<rsm:CrossIndustryInvoice xmlns:rsm="urn:un:unece:uncefact:data:standard:CrossIndustryInvoice:100" xmlns:qdt="urn:un:unece:uncefact:data:standard:QualifiedDataType:100" xmlns:ram="urn:un:unece:uncefact:data:standard:ReusableAggregateBusinessInformationEntity:100" xmlns:xs="http://www.w3.org/2001/XMLSchema" xmlns:udt="urn:un:unece:uncefact:data:standard:UnqualifiedDataType:100">
    <rsm:ExchangedDocumentContext>
        <ram:BusinessProcessSpecifiedDocumentContextParameter>
            <ram:ID>Baurechnung</ram:ID>
        </ram:BusinessProcessSpecifiedDocumentContextParameter>
        <ram:GuidelineSpecifiedDocumentContextParameter>
            <ram:ID>urn:cen.eu:en16931:2017</ram:ID>
        </ram:GuidelineSpecifiedDocumentContextParameter>
    </rsm:ExchangedDocumentContext>
    <rsm:ExchangedDocument>
        <ram:ID>181301674</ram:ID>
        <ram:TypeCode>204</ram:TypeCode>
        <ram:IssueDateTime>
            <udt:DateTimeString format="102">20241115</udt:DateTimeString>
        </ram:IssueDateTime>
        <ram:IncludedNote>
            <ram:Content>Rapport-Nr.: 42389 vom 01.11.2024
Im 2. OG BT1 Besprechungsraum eine Beamerhalterung an die Decke montiert. Dafür eine Deckenplatte ausgesägt. Beamerhalterung zur Montage auseinander gebaut. Ein Stromkabel für den Beamer, ein HDMI Kabel und ein VGA Kabel durch die Halterung gezogen. Beamerhalterung wieder zusammengebaut und Beamer montiert. Beamer verkabelt und ausgerichtet. Decke geschlossen.</ram:Content>
        </ram:IncludedNote>
    </rsm:ExchangedDocument>
    <rsm:SupplyChainTradeTransaction>
        <ram:IncludedSupplyChainTradeLineItem>
            <ram:AssociatedDocumentLineDocument>
                <ram:LineID>01</ram:LineID>
                <ram:IncludedNote>
                    <ram:Content>01 Beamermontage
Für die doppelte Verlegung, falls erforderlich.</ram:Content>
                </ram:IncludedNote>
            </ram:AssociatedDocumentLineDocument>
            <ram:SpecifiedTradeProduct>
                <ram:Name>TGA Obermonteur/Monteur</ram:Name>
            </ram:SpecifiedTradeProduct>
            <ram:SpecifiedLineTradeAgreement>
                <ram:GrossPriceProductTradePrice>
                    <ram:ChargeAmount>43.2</ram:ChargeAmount>
                </ram:GrossPriceProductTradePrice>
                <ram:NetPriceProductTradePrice>
                    <ram:ChargeAmount>43.2</ram:ChargeAmount>
                </ram:NetPriceProductTradePrice>
            </ram:SpecifiedLineTradeAgreement>
            <ram:SpecifiedLineTradeDelivery>
                <ram:BilledQuantity unitCode="HUR">3</ram:BilledQuantity>
            </ram:SpecifiedLineTradeDelivery>
            <ram:SpecifiedLineTradeSettlement>
                <ram:ApplicableTradeTax>
                    <ram:TypeCode>VAT</ram:TypeCode>
                    <ram:CategoryCode>S</ram:CategoryCode>
                    <ram:RateApplicablePercent>19</ram:RateApplicablePercent>
                </ram:ApplicableTradeTax>
                <ram:SpecifiedTradeSettlementLineMonetarySummation>
                    <ram:LineTotalAmount>129.6</ram:LineTotalAmount>
                </ram:SpecifiedTradeSettlementLineMonetarySummation>
            </ram:SpecifiedLineTradeSettlement>
        </ram:IncludedSupplyChainTradeLineItem>
        <ram:IncludedSupplyChainTradeLineItem>
            <ram:AssociatedDocumentLineDocument>
                <ram:LineID>02</ram:LineID>
                <ram:IncludedNote>
                    <ram:Content>02 Außerhalb Angebot</ram:Content>
                </ram:IncludedNote>
            </ram:AssociatedDocumentLineDocument>
            <ram:SpecifiedTradeProduct>
                <ram:Name>Beamer-Deckenhalterung</ram:Name>
            </ram:SpecifiedTradeProduct>
            <ram:SpecifiedLineTradeAgreement>
                <ram:GrossPriceProductTradePrice>
                    <ram:ChargeAmount>122.5</ram:ChargeAmount>
                </ram:GrossPriceProductTradePrice>
                <ram:NetPriceProductTradePrice>
                    <ram:ChargeAmount>122.5</ram:ChargeAmount>
                </ram:NetPriceProductTradePrice>
            </ram:SpecifiedLineTradeAgreement>
            <ram:SpecifiedLineTradeDelivery>
                <ram:BilledQuantity unitCode="H87">1</ram:BilledQuantity>
            </ram:SpecifiedLineTradeDelivery>
            <ram:SpecifiedLineTradeSettlement>
                <ram:ApplicableTradeTax>
                    <ram:TypeCode>VAT</ram:TypeCode>
                    <ram:CategoryCode>S</ram:CategoryCode>
                    <ram:RateApplicablePercent>19</ram:RateApplicablePercent>
                </ram:ApplicableTradeTax>
                <ram:SpecifiedTradeSettlementLineMonetarySummation>
                    <ram:LineTotalAmount>122.5</ram:LineTotalAmount>
                </ram:SpecifiedTradeSettlementLineMonetarySummation>
            </ram:SpecifiedLineTradeSettlement>
        </ram:IncludedSupplyChainTradeLineItem>
        <ram:ApplicableHeaderTradeAgreement>  
          <ram:BuyerReference>Liselotte Müller-Lüdenscheidt</ram:BuyerReference>
            <ram:SellerTradeParty>
                <ram:ID>549910</ram:ID>
                <ram:Name>ELEKTRON Industrieservice GmbH</ram:Name>
                <ram:Description>Geschäftsführer Egon Schrempp Amtsgericht Stuttgart HRB 1234</ram:Description>
                <ram:PostalTradeAddress>
                    <ram:PostcodeCode>74465</ram:PostcodeCode>
                    <ram:LineOne>Erfurter Strasse 13</ram:LineOne>
                    <ram:CityName>Demoort</ram:CityName>
                    <ram:CountryID>DE</ram:CountryID>
                </ram:PostalTradeAddress>
                <ram:SpecifiedTaxRegistration>
                    <ram:ID schemeID="VA">DE136695976</ram:ID>
                </ram:SpecifiedTaxRegistration>
            </ram:SellerTradeParty>
            <ram:BuyerTradeParty>
                <ram:ID>16259</ram:ID>
                <ram:Name>ConsultingService GmbH</ram:Name>
                <ram:PostalTradeAddress>
                    <ram:PostcodeCode>76138</ram:PostcodeCode>
                    <ram:LineOne>Musterstr. 18</ram:LineOne>
                    <ram:CityName>Karlsruhe</ram:CityName>
                    <ram:CountryID>DE</ram:CountryID>
                </ram:PostalTradeAddress>
            </ram:BuyerTradeParty>
            <ram:SellerOrderReferencedDocument>
                <ram:IssuerAssignedID>per Mail vom 01.09.2024</ram:IssuerAssignedID>
            </ram:SellerOrderReferencedDocument>
            <ram:AdditionalReferencedDocument>
                <ram:IssuerAssignedID>13130162</ram:IssuerAssignedID>
                <ram:URIID>#ef=Aufmass.png</ram:URIID>
                <ram:TypeCode>916</ram:TypeCode>
            </ram:AdditionalReferencedDocument>
             <ram:AdditionalReferencedDocument>
                <ram:IssuerAssignedID>42389</ram:IssuerAssignedID>
                <ram:URIID>#ef=ElektronRapport_neu-red.pdf</ram:URIID>
                <ram:TypeCode>916</ram:TypeCode>
            </ram:AdditionalReferencedDocument>
            <ram:SpecifiedProcuringProject>
                <ram:ID>13130162</ram:ID>
                <ram:Name>Projekt</ram:Name>
            </ram:SpecifiedProcuringProject>
        </ram:ApplicableHeaderTradeAgreement>
        <ram:ApplicableHeaderTradeDelivery>
            <ram:ActualDeliverySupplyChainEvent>
                <ram:OccurrenceDateTime>
                    <udt:DateTimeString format="102">20241101</udt:DateTimeString>
                </ram:OccurrenceDateTime>
            </ram:ActualDeliverySupplyChainEvent>
        </ram:ApplicableHeaderTradeDelivery>
        <ram:ApplicableHeaderTradeSettlement>
            <ram:PaymentReference>Rechnung 181301674</ram:PaymentReference>
            <ram:InvoiceCurrencyCode>EUR</ram:InvoiceCurrencyCode>
            <ram:SpecifiedTradeSettlementPaymentMeans>
                <ram:TypeCode>58</ram:TypeCode>
                <ram:PayeePartyCreditorFinancialAccount>
                    <ram:IBANID>DE91100000000123456789</ram:IBANID>
                </ram:PayeePartyCreditorFinancialAccount>
            </ram:SpecifiedTradeSettlementPaymentMeans>
            <ram:ApplicableTradeTax>
                <ram:CalculatedAmount>47.9</ram:CalculatedAmount>
                <ram:TypeCode>VAT</ram:TypeCode>
                <ram:BasisAmount>252.1</ram:BasisAmount>
                <ram:CategoryCode>S</ram:CategoryCode>
                <ram:RateApplicablePercent>19</ram:RateApplicablePercent>
            </ram:ApplicableTradeTax>
            <ram:SpecifiedTradePaymentTerms>
                <ram:Description>Zahlbar sofort rein netto</ram:Description>
            </ram:SpecifiedTradePaymentTerms>
            <ram:SpecifiedTradeSettlementHeaderMonetarySummation>
                <ram:LineTotalAmount>252.1</ram:LineTotalAmount>
                <ram:ChargeTotalAmount>0</ram:ChargeTotalAmount>
                <ram:AllowanceTotalAmount>0</ram:AllowanceTotalAmount>
                <ram:TaxBasisTotalAmount>252.1</ram:TaxBasisTotalAmount>
                <ram:TaxTotalAmount currencyID="EUR">47.9</ram:TaxTotalAmount>
                <ram:GrandTotalAmount>300</ram:GrandTotalAmount>
                <ram:TotalPrepaidAmount>0</ram:TotalPrepaidAmount>
                <ram:DuePayableAmount>300</ram:DuePayableAmount>
            </ram:SpecifiedTradeSettlementHeaderMonetarySummation>
            <ram:ReceivableSpecifiedTradeAccountingAccount>
                <ram:ID>420</ram:ID>
            </ram:ReceivableSpecifiedTradeAccountingAccount>
        </ram:ApplicableHeaderTradeSettlement>
    </rsm:SupplyChainTradeTransaction>
</rsm:CrossIndustryInvoice>
XML;

$pdf->addContentAsEmbeddedFile('factur-x.xml', $invoiceXML);

$pdf->setCustomXMP('x:xmpmeta.rdf:RDF.rdf:Description.pdfaExtension:schemas.rdf:Bag',
    '<rdf:li rdf:parseType="Resource">
    <pdfaSchema:schema>Factur-X PDFA Extension Schema</pdfaSchema:schema>
    <pdfaSchema:namespaceURI>urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#</pdfaSchema:namespaceURI>
    <pdfaSchema:prefix>fx</pdfaSchema:prefix>
    <pdfaSchema:property>
      <rdf:Seq>
        <rdf:li rdf:parseType="Resource">
          <pdfaProperty:name>DocumentFileName</pdfaProperty:name>
          <pdfaProperty:valueType>Text</pdfaProperty:valueType>
          <pdfaProperty:category>external</pdfaProperty:category>
          <pdfaProperty:description>The name of the embedded XML document</pdfaProperty:description>
        </rdf:li>
        <rdf:li rdf:parseType="Resource">
          <pdfaProperty:name>DocumentType</pdfaProperty:name>
          <pdfaProperty:valueType>Text</pdfaProperty:valueType>
          <pdfaProperty:category>external</pdfaProperty:category>
          <pdfaProperty:description>The type of the hybrid document in capital letters, e.g. INVOICE or ORDER</pdfaProperty:description>
        </rdf:li>
        <rdf:li rdf:parseType="Resource">
          <pdfaProperty:name>Version</pdfaProperty:name>
          <pdfaProperty:valueType>Text</pdfaProperty:valueType>
          <pdfaProperty:category>external</pdfaProperty:category>
          <pdfaProperty:description>The actual version of the standard applying to the embedded XML document</pdfaProperty:description>
        </rdf:li>
        <rdf:li rdf:parseType="Resource">
          <pdfaProperty:name>ConformanceLevel</pdfaProperty:name>
          <pdfaProperty:valueType>Text</pdfaProperty:valueType>
          <pdfaProperty:category>external</pdfaProperty:category>
          <pdfaProperty:description>The conformance level of the embedded XML document</pdfaProperty:description>
        </rdf:li>
      </rdf:Seq>
    </pdfaSchema:property>
</rdf:li>'
);

$pdf->setCustomXMP('x:xmpmeta.rdf:RDF',
    '<rdf:Description xmlns:fx="urn:factur-x:pdfa:CrossIndustryDocument:invoice:1p0#" rdf:about="">
  <fx:DocumentType>INVOICE</fx:DocumentType>
  <fx:DocumentFileName>factur-x.xml</fx:DocumentFileName>
  <fx:Version>1.0</fx:Version>
  <fx:ConformanceLevel>EN 16931</fx:ConformanceLevel>
</rdf:Description>');

// =============================================================

// ----------
// get PDF document as raw string
$rawpdf = $pdf->getOutPDFString();

// ----------

// Various output modes:

//$pdf->savePDF(dirname(__DIR__).'/target', $rawpdf);
$pdf->renderPDF($rawpdf);
//$pdf->downloadPDF($rawpdf);
//echo $pdf->getMIMEAttachmentPDF($rawpdf);
