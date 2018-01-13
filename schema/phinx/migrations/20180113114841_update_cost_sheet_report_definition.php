<?php


use Phinx\Migration\AbstractMigration;

class UpdateCostSheetReportDefinition extends AbstractMigration
{
    /*
     * Update report definition 'CostSheet'
     */
    public function up()
    {
        $xsl=<<<'REPORT'
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
            <fo:layout-master-set>
                <fo:simple-page-master master-name="all-pages"
                        page-height="21cm"
                        page-width="29.7cm"
                                    margin="0.5cm" >
                    <fo:region-body margin-top="1cm" margin-bottom="5mm"/>
                    <fo:region-before extent="1cm"  />
                    <fo:region-after extent="5mm"/>
                </fo:simple-page-master>
                <fo:page-sequence-master master-name="default-sequence">
                    <fo:repeatable-page-master-reference master-reference="all-pages"/>
                </fo:page-sequence-master>
            </fo:layout-master-set>
            <fo:page-sequence master-reference="default-sequence">
                <xsl:variable name="type" select="data/extra/type"/>
                <!-- PAGE HEADER -->
                <fo:static-content flow-name="xsl-region-before" >
                    <fo:block font-size="16px" text-align="center">

                        <xsl:value-of select="/data/extra/title" />
                    </fo:block>
                </fo:static-content>
                <!-- PAGE FOOTER -->
                <fo:static-content flow-name="xsl-region-after" font-size="8px">
                    <fo:block>Page <!--[page_position]--></fo:block>
                </fo:static-content>
                <!-- BODY -->

                <fo:flow flow-name="xsl-region-body" font-size="8px">
                    <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm">
                        <fo:table table-layout="fixed" width="100%" >
                            <fo:table-column column-width="proportional-column-width(70)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(85)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(70)"/>
                            <fo:table-header>

                                <fo:table-row>
                                    <xsl:attribute name="background-color">rgb(204,231,255)</xsl:attribute>
                                    <fo:table-cell padding="1mm">
                                        <fo:block />
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Item</fo:block>
                                    </fo:table-cell>

                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Description</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">UoM</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Cost Basis</fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                            </fo:table-header>
                            <fo:table-body>
                                <xsl:for-each select="data/STItem">
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block />
                                        </fo:table-cell>

                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="item_code" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="description" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="uom_name" /></fo:block>
                                        </fo:table-cell>

                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="cost_basis" /></fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                </xsl:for-each>
                            </fo:table-body>
                        </fo:table>
                    </fo:block>
                    <xsl:if test="data/MFStructure">

                        <!-- STUCTURE (MATERIALS) -->
                        <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
                            <fo:block margin-bottom="5mm">Structures (Materials)</fo:block>
                            <fo:table table-layout="fixed" width="100%" >
                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(85)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(25)"/>

                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-header>
                                    <fo:table-row>
                                        <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>

                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Line No.</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Stock Item</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Quantity</fo:block>

                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">UoM</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Waste %</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">

                                            <fo:block text-align="left">Mat</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Lab</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Osc</fo:block>

                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Ohd</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Total</fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>

                                </fo:table-header>
                                <fo:table-body>
                                    <xsl:for-each select="data/MFStructure">
                                        <fo:table-row>
                                            <!-- this condition is to provide us with alternate row colours -->
                                            <xsl:if test="(position() mod 2 = 1)">
                                                <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
                                            </xsl:if>
                                            <fo:table-cell padding="1mm">

                                                <fo:block text-align="left">
                                                    <xsl:value-of select="line_no" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="ststructure" />
                                                </fo:block>
                                            </fo:table-cell>

                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="qty" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="uom" />
                                                </fo:block>

                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="waste_pc" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_mat'')]" />

                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_lab'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">

                                                    <xsl:value-of select="*[name() = concat($type, ''_osc'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_ohd'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">

                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_cost'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                        </fo:table-row>
                                    </xsl:for-each>
                                </fo:table-body>
                            </fo:table>
                        </fo:block>

                    </xsl:if>
                    <xsl:if test="data/MFOperation">
                        <!-- OPERATIONS (LABOUR OVERHEAD) -->
                        <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
                            <fo:block margin-bottom="5mm">Operations (Labour Overhead)</fo:block>
                            <fo:table table-layout="fixed" width="100%" >
                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(85)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(30)"/>
                               <xsl:choose>
                                 <xsl:when test="cost_basis = ''Volume''">
                                <fo:table-column column-width="proportional-column-width(30)"/>
                                <fo:table-column column-width="proportional-column-width(30)"/>
                                 </xsl:when>
                            </xsl:choose>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-header>
                                    <fo:table-row>

                                        <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Op No.</fo:block>
                                        </fo:table-cell>

                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Remarks</fo:block>
                                        </fo:table-cell>

                                        <fo:table-cell padding="1mm">
                               <xsl:choose>
                                 <xsl:when test="cost_basis = ''Volume''">
                                            <fo:block text-align="left">Volume Target</fo:block>
                                 </xsl:when>
                                 <xsl:otherwise>
                                            <fo:block text-align="left">Time</fo:block>
                                </xsl:otherwise>
                            </xsl:choose>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                               <xsl:choose>
                                 <xsl:when test="cost_basis = ''Volume''">
                                            <fo:block text-align="left">Volume Period</fo:block>
                                 </xsl:when>
                                 <xsl:otherwise>
                                            <fo:block text-align="left">Time Unit</fo:block>
                                </xsl:otherwise>
                            </xsl:choose>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">UoM</fo:block>
                                        </fo:table-cell>
                               <xsl:choose>
                                 <xsl:when test="cost_basis = ''Volume''">
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Quality Target</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Uptime Target</fo:block>
                                        </fo:table-cell>
                                 </xsl:when>
                            </xsl:choose>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Lab</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Ohd</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Total</fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>
                                </fo:table-header>
                                <fo:table-body>

                                    <xsl:for-each select="data/MFOperation">
                                        <fo:table-row>
                                            <!-- this condition is to provide us with alternate row colours -->
                                            <xsl:if test="(position() mod 2 = 1)">
                                                <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
                                            </xsl:if>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="op_no" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="remarks" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="volume_target" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="volume_period" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="volume_uom" />
                                                </fo:block>
                                            </fo:table-cell>
                               <xsl:choose>
                                 <xsl:when test="cost_basis = ''Volume''">
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="quality_target" />
                                                </fo:block>
                                            </fo:table-cell>

                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="uptime_target" />
                                                </fo:block>
                                            </fo:table-cell>
                                 </xsl:when>
                            </xsl:choose>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_lab'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_ohd'')]" />
                                                </fo:block>

                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_cost'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                        </fo:table-row>
                                    </xsl:for-each>
                                </fo:table-body>

                            </fo:table>
                        </fo:block>
                    </xsl:if>
                    <xsl:if test="data/MFOutsideOperation">
                        <!-- OUTSIDE OPERATIONS (OUTSIDE CONTRACT) -->
                        <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
                            <fo:block margin-bottom="5mm">Operations (Labour Overhead)</fo:block>
                            <fo:table table-layout="fixed" width="100%" >

                                <fo:table-column column-width="proportional-column-width(15)"/>
                                <fo:table-column column-width="proportional-column-width(85)"/>
                                <fo:table-column column-width="proportional-column-width(20)"/>
                                <fo:table-header>
                                    <fo:table-row>
                                        <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Op No.</fo:block>

                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Description</fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left">Outside Contract</fo:block>
                                        </fo:table-cell>
                                    </fo:table-row>

                                </fo:table-header>
                                <fo:table-body>
                                    <xsl:for-each select="data/MFOutsideOperation">
                                        <fo:table-row>
                                            <!-- this condition is to provide us with alternate row colours -->
                                            <xsl:if test="(position() mod 2 = 1)">
                                                <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
                                            </xsl:if>
                                            <fo:table-cell padding="1mm">

                                                <fo:block text-align="left">
                                                    <xsl:value-of select="op_no" />
                                                </fo:block>
                                            </fo:table-cell>
                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="description" />
                                                </fo:block>
                                            </fo:table-cell>

                                            <fo:table-cell padding="1mm">
                                                <fo:block text-align="left">
                                                    <xsl:value-of select="*[name() = concat($type, ''_osc'')]" />
                                                </fo:block>
                                            </fo:table-cell>
                                        </fo:table-row>
                                    </xsl:for-each>
                                </fo:table-body>
                            </fo:table>

                        </fo:block>
                    </xsl:if>
                    <!-- TOTALS -->
                    <fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm">
                        <fo:table table-layout="fixed" width="100%" >
                            <fo:table-column column-width="proportional-column-width(70)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>

                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(25)"/>
                            <fo:table-column column-width="proportional-column-width(70)"/>
                            <fo:table-header>
                                <fo:table-row>
                                    <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                                    <fo:table-cell padding="1mm">
                                        <fo:block />
                                    </fo:table-cell>

                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Materials</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Labour</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Outside Contract</fo:block>

                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Overhead</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">
                                        <fo:block text-align="left">Total Cost</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm">

                                        <fo:block />
                                    </fo:table-cell>
                                </fo:table-row>
                            </fo:table-header>
                            <fo:table-body>
                                <xsl:for-each select="data/extra/totals">
                                    <fo:table-row>
                                        <fo:table-cell padding="1mm">
                                            <fo:block />

                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="*[name() = concat($type, ''_mat'')]" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="*[name() = concat($type, ''_lab'')]" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="*[name() = concat($type, ''_osc'')]" /></fo:block>

                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="*[name() = concat($type, ''_ohd'')]" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block text-align="left"><xsl:value-of select="*[name() = concat($type, ''_cost'')]" /></fo:block>
                                        </fo:table-cell>
                                        <fo:table-cell padding="1mm">
                                            <fo:block />

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
REPORT;

        $date = new DateTime();
        $update_time = $date->format('Y-m-d H:i:s.u');
        $rows = $this->query("UPDATE report_definitions SET definition='${xsl}', lastupdated='${update_time}', alteredby='admin' WHERE name='CostSheet'");
    }
}
