<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add totals to VAT Transactions report definition
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class VatTransactionReportAddTotals extends UzerpMigration
{
    public function up()
    {
        $xsl = <<<'XMLDOC'
<?xml version="1.0" encoding="UTF-8"?>
<!-- Unified template for printing VAT Transcation reports; inputs, outputs and both EU sales lists -->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
            <fo:layout-master-set>
                <fo:simple-page-master master-name="all-pages"
                        page-height="21cm"
                        page-width="29.7cm"
                                    margin="0.5cm" >
                    <fo:region-body margin-top="2cm" margin-bottom="7mm"/>
                    <fo:region-before extent="2cm"  />
                    <fo:region-after extent="5mm"/>
                </fo:simple-page-master>
                <fo:page-sequence-master master-name="default-sequence">
                    <fo:repeatable-page-master-reference master-reference="all-pages"/>
                </fo:page-sequence-master>
            </fo:layout-master-set>
            <fo:page-sequence master-reference="default-sequence">
                <!-- PAGE HEADER -->
                <fo:static-content flow-name="xsl-region-before" >
                    <fo:block font-size="16px" text-align="center" border-bottom-width="1pt" border-bottom-style="solid" padding="2mm">
                        <xsl:value-of select="/data/extra/title" />
                    </fo:block>
                </fo:static-content>
                <!-- PAGE FOOTER -->
                <fo:static-content flow-name="xsl-region-after" font-size="8px">
                    <fo:block>Page <!--[page_position]--></fo:block>
                </fo:static-content>
                <!-- BODY -->
                <fo:flow flow-name="xsl-region-body" font-size="8px">
                    <fo:table table-layout="fixed" width="100%" >
                        <fo:table-column column-width="proportional-column-width(70)"/>
                        <fo:table-column column-width="proportional-column-width(70)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(150)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(70)"/>
                        <fo:table-column column-width="proportional-column-width(70)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(70)"/>
                        <fo:table-header>
                            <fo:table-row border-bottom-style ="solid">
                                <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Date</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Doc Ref</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Ext Ref</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Company</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Comment</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block text-align="right">VAT</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block text-align="right">Net</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Source</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:block>Type</fo:block>
                                </fo:table-cell>
                            </fo:table-row>
                        </fo:table-header>
                        <fo:table-body>
                            <xsl:for-each select="data/*[starts-with(local-name(), ''Vat'')]">
                                <fo:table-row>
                                    <!-- this condition is to provide us with alternate row colours -->
                                    <xsl:if test="(position() mod 2 = 1)">
                                        <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
                                    </xsl:if>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="transaction_date" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="docref" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="ext_reference" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="supplier|customer|company" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="comment" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="right">
                                            <xsl:value-of select="vat" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="right">
                                            <xsl:value-of select="net" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="source" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block>
                                            <xsl:value-of select="type" />
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                            </xsl:for-each>
                <!-- TOTAL ROW -->
                <fo:table-row border-top-style ="solid">
                <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                <fo:table-cell padding="1mm">
                        <fo:block font-weight="bold">Report Total</fo:block>
                </fo:table-cell>
                <fo:table-cell padding="1mm" number-columns-spanned="4">
                        <fo:block />
                </fo:table-cell>
                <fo:table-cell padding="1mm">
                    <fo:block font-weight="bold" text-align="right" > <xsl:value-of select="/data/extra/totalvat" /></fo:block>
                </fo:table-cell>
                <fo:table-cell padding="1mm">
                    <fo:block font-weight="bold" text-align="right" > <xsl:value-of select="/data/extra/totalnet" /></fo:block>
                </fo:table-cell>
                <fo:table-cell padding="1mm" number-columns-spanned="2">
                        <fo:block />
                </fo:table-cell>
                    </fo:table-row>
                        </fo:table-body>
                    </fo:table>
            <fo:block id="last-page"/>
                </fo:flow>
            </fo:page-sequence>
        </fo:root>
    </xsl:template>
</xsl:stylesheet>
XMLDOC;

        $date = new DateTime();
        $update_time = $date->format('Y-m-d H:i:s.u');
        $rows = $this->query("UPDATE report_definitions SET definition='${xsl}', lastupdated='${update_time}', alteredby='admin' WHERE name='VatTransaction'");
    }
}
