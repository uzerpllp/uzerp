<?php


use UzerpPhinx\UzerpMigration;

class UpdateVatReturnDoc extends UzerpMigration
{
    public function up()
    {
        $xsl = <<<'XMLDOC'
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
            <fo:layout-master-set>
                <fo:simple-page-master master-name="all-pages"
                        page-height="29.7cm"
                        page-width="21cm"
                                    margin="0.5cm" >

                    <fo:region-body margin-top="2cm" margin-bottom="5mm"/>
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
                    <fo:block font-size="16px" text-align="center">
                        <xsl:value-of select="/data/extra/title" />
                    </fo:block>
                    <xsl:if test="data/extra/tax_period_not_closed">
                        <fo:block font-size="10px" margin-top="5mm" margin-bottom="5mm">Warning: Tax period is not closed therefore the figures below may not be final.</fo:block>
                    </xsl:if>

                </fo:static-content>
                <!-- PAGE FOOTER -->
                <fo:static-content flow-name="xsl-region-after" font-size="8px">
                    <fo:block display-align="after">Page <!--[page_position]--></fo:block>
                </fo:static-content>
                <!-- BODY -->
                <fo:flow flow-name="xsl-region-body" font-size="10px">
                    <!-- ORDER -->
                    <xsl:choose>
                        <xsl:when test="data/extra/mtd_not_submitted">
                            <fo:block font-size="10px" margin-top="5mm" margin-bottom="5mm">Not submitted to HMRC</fo:block>
                        </xsl:when>
                        <xsl:otherwise>
                        <fo:block font-weight="bold" text-align="left">
                            Making Tax Digital VAT Receipt
                        </fo:block>
                        <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
                            <fo:table table-layout="fixed" width="12cm" >
                                <fo:table-column column-width="proportional-column-width(30)"/>
                                <fo:table-column column-width="proportional-column-width(70)"/>
                                <fo:table-body>
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                Date submitted:
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                <xsl:value-of select="data/extra/submission/processing_date" />
                                            </fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                Form Bundle:
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                <xsl:value-of select="data/extra/submission/form_bundle" />
                                            </fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                Charge Ref. Number:
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                <xsl:value-of select="data/extra/submission/charge_ref_number" />
                                            </fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                Receipt ID:
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                <xsl:value-of select="data/extra/submission/receipt_id_header" />
                                            </fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                </fo:table-body>
                            </fo:table>
                        </fo:block>
                        </xsl:otherwise>
                    </xsl:choose>
                    <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
                        <fo:table table-layout="fixed">
                            <fo:table-column column-width="proportional-column-width(20)"/>
                            <fo:table-column column-width="proportional-column-width(100)"/>
                            <fo:table-column column-width="proportional-column-width(20)"/>
                            <fo:table-body>
                                <xsl:for-each select="data/extra/boxes/line">
                                    <fo:table-row>
                                        <!-- this condition is to provide us with alternate row colours -->

                                        <xsl:if test="(position() mod 2 = 1)">
                                            <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
                                        </xsl:if>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">
                                                <xsl:value-of select="box_num" />
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">

                                            <fo:block text-align="left">
                                                <xsl:value-of select="title" />
                                            </fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="right">
                                                <xsl:value-of select="value" />
                                            </fo:block>
                                        </fo:table-cell>

                                    </fo:table-row>
                                </xsl:for-each>
                            </fo:table-body>
                        </fo:table>
                    </fo:block>
                    <fo:block id="last-page"/>
                </fo:flow>
            </fo:page-sequence>
        </fo:root>

    </xsl:template>
</xsl:stylesheet>
XMLDOC;

$date = new DateTime();
$update_time = $date->format('Y-m-d H:i:s.u');
$rows = $this->query("UPDATE report_definitions SET definition='${xsl}', lastupdated='${update_time}', alteredby='admin' WHERE name='VatReturn'");
    }
}
