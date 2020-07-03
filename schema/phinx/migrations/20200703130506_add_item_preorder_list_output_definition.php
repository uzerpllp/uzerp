<?php


use UzerpPhinx\UzerpMigration;

class AddItemPreorderListOutputDefinition extends UzerpMigration
{
    public function up()
    {
        $template = <<<'DOC'
<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
            <fo:layout-master-set>
                <fo:simple-page-master master-name="all-pages"
                        page-height="29.7cm"
                        page-width="21cm"
                        margin="0.5cm" >

                    <fo:region-body margin-top="4cm" margin-bottom="6mm"/>
                    <fo:region-before extent="4cm"/>
                    <fo:region-after extent="5mm"/>
                    </fo:simple-page-master>
                </fo:layout-master-set>
            <!-- format is the style of page numbering, 1 for 1,2,3, i for roman numerals (sp?)-->
            <fo:page-sequence master-reference="all-pages" format="1" font-size="8pt">
                <!-- header with running glossary entries -->
                <fo:static-content flow-name="xsl-region-before" >
                    <fo:block text-align="center" font-size="18pt" margin-bottom="5mm" >
                        PRE-ORDER LIST
                    </fo:block>
                    <fo:table table-layout="fixed" width="100%" font-size="10pt">
                        <fo:table-column column-width="proportional-column-width(35)"/>
                        <fo:table-column column-width="proportional-column-width(150)"/>
                        <fo:table-body>

                            <fo:table-row height="5mm">
                                <fo:table-cell>
                                    <fo:block font-weight="bold">Item</fo:block>
                                </fo:table-cell>
                                <fo:table-cell>
                                    <fo:block><xsl:value-of select="data/extra/item" /></fo:block>
                                </fo:table-cell>
                            </fo:table-row>

                            <fo:table-row height="5mm">
                                <fo:table-cell>
                                    <fo:block font-weight="bold">Quantity Required</fo:block>
                                </fo:table-cell>
                                <fo:table-cell>
                                    <fo:block><xsl:value-of select="data/extra/qty" /></fo:block>
                                </fo:table-cell>
                            </fo:table-row>

                            <fo:table-row height="5mm">
                                <fo:table-cell>
                                    <fo:block font-weight="bold">UOM Name</fo:block>
                                </fo:table-cell>
                                <fo:table-cell>
                                    <fo:block><xsl:value-of select="data/extra/uom" /></fo:block>
                                </fo:table-cell>
                            </fo:table-row>

                        </fo:table-body>
                    </fo:table>
                </fo:static-content>
                <fo:static-content flow-name="xsl-region-after" font-size="8pt">
                    <fo:block>Page <!--[page_position]--></fo:block>
                </fo:static-content>
                <fo:flow flow-name="xsl-region-body" >
                    <fo:table table-layout="fixed" width="100%" font-size="8pt" break-before="page">

                        <fo:table-column column-width="proportional-column-width(132)"/>
                        <fo:table-column column-width="proportional-column-width(27)"/>
                        <fo:table-column column-width="proportional-column-width(35)"/>
                        <fo:table-header>
                            <fo:table-row font-size="10pt" font-weight="bold">
                                <xsl:attribute name="background-color">rgb(204,231,255)</xsl:attribute>
                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Stock Item</fo:block>

                                </fo:table-cell>
                                <fo:table-cell text-align="right" padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Required</fo:block>
                                </fo:table-cell>
                                <fo:table-cell text-align="right" padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Units</fo:block>
                                </fo:table-cell>
                            </fo:table-row>

                        </fo:table-header>
                        <fo:table-body>
                            <xsl:for-each select="data/MFStructure">
                                <fo:table-row>
                                    <!-- this condition is to provide us with alternate row colours -->
                                    <xsl:if test="(position() mod 2 = 1)">
                                        <xsl:attribute name="background-color">rgb(229,243,255)</xsl:attribute>
                                    </xsl:if>

                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block text-align="left"><xsl:value-of select="ststructure" /></fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block text-align="right"><xsl:value-of select="qty" /></fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block text-align="right"><xsl:value-of select="uom" /></fo:block>
                                    </fo:table-cell>

                                </fo:table-row>
                            </xsl:for-each>
                        </fo:table-body>
                    </fo:table>
                    <!-- this is required to calculate the last page number -->
                    <fo:block id="last-page"/>
                </fo:flow>

            </fo:page-sequence>
        </fo:root>

    </xsl:template>
</xsl:stylesheet>
DOC;

        $test_xml = <<<'TEST'
<data>
<MFStructure>
    <id>25</id>
    <alteredby>admin</alteredby>
    <comp_class>M</comp_class>
    <created>01/07/2020 15:43</created>
    <createdby>admin</createdby>
    <end_date></end_date>
    <lastupdated>01/07/2020 15:43</lastupdated>
    <latest_cost>12.25</latest_cost>
    <latest_lab>0</latest_lab>
    <latest_mat>12.25</latest_mat>
    <latest_ohd>0</latest_ohd>
    <latest_osc>0</latest_osc>
    <line_no>10</line_no>
    <qty>1</qty>
    <remarks></remarks>
    <start_date>01/07/2020</start_date>
    <std_cost>0</std_cost>
    <std_lab>0</std_lab>
    <std_mat>0</std_mat>
    <std_ohd>0</std_ohd>
    <std_osc>0</std_osc>
    <stitem>KIT100 - Grab kit</stitem>
    <stitem_id>KIT100 - Grab kit</stitem_id>
    <ststructure>GRB-100 - HRN Grab Mini Moto</ststructure>
    <ststructure_id>GRB-100 - HRN Grab Mini Moto</ststructure_id>
    <uom>Each</uom>
    <uom_id>Each</uom_id>
    <usercompanyid>1</usercompanyid>
    <waste_pc>0</waste_pc>
    <STItem>
        <id>7</id>
        <abc_class>A</abc_class>
        <alpha_code>Mini Moto</alpha_code>
        <alteredby>admin</alteredby>
        <balance>29</balance>
        <batch_size></batch_size>
        <comp_class>Manufactured</comp_class>
        <cost_basis>Volume</cost_basis>
        <cost_decimals>2</cost_decimals>
        <created>12/03/2010 14:42</created>
        <createdby>admin</createdby>
        <description>HRN Grab Mini Moto</description>
        <float_qty>0</float_qty>
        <free_qty>0</free_qty>
        <item_code>GRB-100</item_code>
        <lastupdated>02/07/2020 12:35</lastupdated>
        <latest_cost>14</latest_cost>
        <latest_lab>1.05</latest_lab>
        <latest_mat>12.25</latest_mat>
        <latest_ohd>0.7</latest_ohd>
        <latest_osc>0</latest_osc>
        <lead_time></lead_time>
        <max_qty>0</max_qty>
        <min_qty>0</min_qty>
        <obsolete_date></obsolete_date>
        <price>0</price>
        <prod_group_id>2 - Grabs</prod_group_id>
        <product_group>2 - Grabs</product_group>
        <qty_decimals>0</qty_decimals>
        <ref1></ref1>
        <std_cost>14</std_cost>
        <std_lab>1.05</std_lab>
        <std_mat>12.25</std_mat>
        <std_ohd>0.7</std_ohd>
        <std_osc>0</std_osc>
        <tax_rate>Standard Rate VAT</tax_rate>
        <tax_rate_id>Standard Rate VAT</tax_rate_id>
        <text1></text1>
        <type_code>MFG - Manufactured Items</type_code>
        <type_code_id>MFG - Manufactured Items</type_code_id>
        <uom_id>Each</uom_id>
        <uom_name>Each</uom_name>
        <usercompanyid>1</usercompanyid>
    </STItem>
</MFStructure>
<MFStructure>
    <id>26</id>
    <alteredby>admin</alteredby>
    <comp_class>M</comp_class>
    <created>01/07/2020 15:43</created>
    <createdby>admin</createdby>
    <end_date></end_date>
    <lastupdated>01/07/2020 15:43</lastupdated>
    <latest_cost>10.6</latest_cost>
    <latest_lab>0</latest_lab>
    <latest_mat>10.6</latest_mat>
    <latest_ohd>0</latest_ohd>
    <latest_osc>0</latest_osc>
    <line_no>20</line_no>
    <qty>1</qty>
    <remarks></remarks>
    <start_date>01/07/2020</start_date>
    <std_cost>0</std_cost>
    <std_lab>0</std_lab>
    <std_mat>0</std_mat>
    <std_ohd>0</std_ohd>
    <std_osc>0</std_osc>
    <stitem>KIT100 - Grab kit</stitem>
    <stitem_id>KIT100 - Grab kit</stitem_id>
    <ststructure>GRB-150 - HRN Grab Sport X</ststructure>
    <ststructure_id>GRB-150 - HRN Grab Sport X</ststructure_id>
    <uom>Each</uom>
    <uom_id>Each</uom_id>
    <usercompanyid>1</usercompanyid>
    <waste_pc>0</waste_pc>
    <STItem>
        <id>8</id>
        <abc_class>A</abc_class>
        <alpha_code>Sport</alpha_code>
        <alteredby>admin</alteredby>
        <balance>59</balance>
        <batch_size></batch_size>
        <comp_class>Manufactured</comp_class>
        <cost_basis>Volume</cost_basis>
        <cost_decimals>2</cost_decimals>
        <created>12/03/2010 14:42</created>
        <createdby>admin</createdby>
        <description>HRN Grab Sport X</description>
        <float_qty>0</float_qty>
        <free_qty>0</free_qty>
        <item_code>GRB-150</item_code>
        <lastupdated>02/07/2020 12:35</lastupdated>
        <latest_cost>12.35</latest_cost>
        <latest_lab>1.05</latest_lab>
        <latest_mat>10.6</latest_mat>
        <latest_ohd>0.7</latest_ohd>
        <latest_osc>0</latest_osc>
        <lead_time></lead_time>
        <max_qty>0</max_qty>
        <min_qty>0</min_qty>
        <obsolete_date></obsolete_date>
        <price>0</price>
        <prod_group_id>2 - Grabs</prod_group_id>
        <product_group>2 - Grabs</product_group>
        <qty_decimals>0</qty_decimals>
        <ref1></ref1>
        <std_cost>12.35</std_cost>
        <std_lab>1.05</std_lab>
        <std_mat>10.6</std_mat>
        <std_ohd>0.7</std_ohd>
        <std_osc>0</std_osc>
        <tax_rate>Standard Rate VAT</tax_rate>
        <tax_rate_id>Standard Rate VAT</tax_rate_id>
        <text1></text1>
        <type_code>MFG - Manufactured Items</type_code>
        <type_code_id>MFG - Manufactured Items</type_code_id>
        <uom_id>Each</uom_id>
        <uom_name>Each</uom_name>
        <usercompanyid>1</usercompanyid>
    </STItem>
</MFStructure>
<extra>
    <item>KIT100 - Grab kit</item>
    <qty>1</qty>
    <uom>Box</uom>
</extra>
</data>    
TEST;

        $rows = $this->query("INSERT INTO report_definitions (name, definition, test_xml, usercompanyid) VALUES ('ItemPreorderList', '${template}', '${test_xml}', 1)");
    }

    public function down()
    {
        return;
    }

}
