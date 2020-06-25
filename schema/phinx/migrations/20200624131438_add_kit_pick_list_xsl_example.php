<?php


use UzerpPhinx\UzerpMigration;

/**
 * Insert an example picklist document that shows sales kit items
 * 
 * We can't push this over the standard document as users
 * will have already modified the XSL to suit their needs
 */
class AddKitPickListXslExample extends UzerpMigration
{
    public function up()
    {
        $template = <<<'DOC'
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
    <xsl:template match="/">
        <fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
            <!-- define the various layouts -->
            <fo:layout-master-set>
                <!-- layout for the all pages -->
                <fo:simple-page-master master-name="rest"
                        page-height="29.7cm"
                        page-width="21cm"
                                    margin="0.5cm" >
                    <fo:region-body margin-top="7cm" margin-bottom="7mm"/>

                    <fo:region-before extent="7cm"  />
                </fo:simple-page-master>
                <!-- layout for the last pages -->
                <fo:simple-page-master master-name="last"
                        page-height="29.7cm"
                        page-width="21cm"
                                    margin="0.5cm" >
                    <fo:region-body margin-top="7cm" margin-bottom="7mm"/>
                    <fo:region-before extent="7cm"  />
                    <fo:region-after extent="5mm"/>
                </fo:simple-page-master>
                <!-- page sequences -->

                <fo:page-sequence-master master-name="default-sequence" >
                    <fo:repeatable-page-master-alternatives>
                        <fo:conditional-page-master-reference master-reference="rest" page-position="rest" />
                        <fo:conditional-page-master-reference master-reference="last" page-position="last" />
                        <!-- recommended fallback procedure -->
                        <fo:conditional-page-master-reference master-reference="rest" />
                    </fo:repeatable-page-master-alternatives>
                </fo:page-sequence-master>
            </fo:layout-master-set>

            <fo:page-sequence master-reference="default-sequence">
                <!-- PAGE HEADER -->
                <fo:static-content flow-name="xsl-region-before" font-size="10px">
                    <fo:block font-size="16px" text-align="center" padding="5mm">Sales Order Pick List</fo:block>
                    <fo:table table-layout="fixed" width="100%" >
                        <fo:table-column column-width="proportional-column-width(50)"/>
                        <fo:table-column column-width="proportional-column-width(75)"/>
                        <fo:table-column column-width="proportional-column-width(60)"/>

                        <fo:table-body>
                            <fo:table-row height="3.5cm">
                                <fo:table-cell padding="1mm">
                                    <!--[logo_relative]-->
                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <xsl:for-each select="data/extra/company_address/*" >
                                        <fo:block><xsl:value-of select="."/></fo:block>
                                    </xsl:for-each>

                                </fo:table-cell>
                                <fo:table-cell padding="1mm">
                                    <fo:table table-layout="fixed" width="6cm" >
                                        <fo:table-column column-width="proportional-column-width(35)"/>
                                        <fo:table-column column-width="proportional-column-width(25)"/>
                                        <fo:table-body>
                                            <fo:table-row>
                                                <fo:table-cell padding-bottom="1mm">
                                                    <fo:block>Page:</fo:block>

                                                </fo:table-cell>
                                                <fo:table-cell padding-bottom="1mm">
                                                    <fo:block>
                                                        <!--[page_position]-->
                                                    </fo:block>
                                                </fo:table-cell>
                                            </fo:table-row>
                                            <xsl:for-each select="data/extra/order_details/line/">
                                                <fo:table-row>

                                                    <fo:table-cell padding-bottom="1mm">
                                                        <fo:block>
                                                            <xsl:value-of select="label" />
                                                        </fo:block>
                                                    </fo:table-cell>
                                                    <fo:table-cell padding-bottom="1mm">
                                                        <fo:block>
                                                            <xsl:value-of select="value" />
                                                        </fo:block>

                                                    </fo:table-cell>
                                                </fo:table-row>
                                            </xsl:for-each>
                                        </fo:table-body>
                                    </fo:table>

                                </fo:table-cell>
                            </fo:table-row>
                            <fo:table-row height="1.5cm">

                                <fo:table-cell padding="1mm">
                                    <fo:block>Customer Delivery Details:</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm" number-columns-spanned="2">
                                    <xsl:for-each select="data/extra/delivery_details/*" >
                                        <fo:block><xsl:value-of select="."/></fo:block>
                                    </xsl:for-each>
                                </fo:table-cell>

                            </fo:table-row>
                        </fo:table-body>
                    </fo:table>
                </fo:static-content>
                <!-- PAGE FOOTER -->
                <fo:static-content flow-name="xsl-region-after" font-size="8px">
                    <fo:block text-align="center">*** End of List ***</fo:block>
                </fo:static-content>

                <!-- BODY -->
                <fo:flow flow-name="xsl-region-body" font-size="10px">
                    <fo:table table-layout="fixed" width="100%" border="solid" >
                        <fo:table-column column-width="proportional-column-width(35)"/>
                        <fo:table-column column-width="proportional-column-width(115)"/>
                        <fo:table-column column-width="proportional-column-width(25)"/>
                        <fo:table-column column-width="proportional-column-width(25)"/>
                        <fo:table-column column-width="proportional-column-width(25)"/>
                        <fo:table-header>

                            <fo:table-row>
                                <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Item</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Description</fo:block>
                                </fo:table-cell>

                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Order Qty</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Units</fo:block>
                                </fo:table-cell>
                                <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                    <fo:block>Initials</fo:block>

                                </fo:table-cell>
                            </fo:table-row>
                        </fo:table-header>
                        <fo:table-body>
                            <xsl:for-each select="data/SOrder/SOrderLine">
                                <fo:table-row>
                                    <!-- this condition is to provide us with alternate row colours -->
                                    <xsl:if test="(position() mod 2 = 1)">
                                        <xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>

                                    </xsl:if>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block>
                                            <xsl:value-of select="item_code" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block>
                                            <xsl:value-of select="description" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block>
                                            <xsl:value-of select="os_qty" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block>

                                            <xsl:value-of select="uom_name" />
                                        </fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                                        <fo:block>
                                            <xsl:value-of select="initials" />
                                        </fo:block>
                                    </fo:table-cell>
                                </fo:table-row>
                                <xsl:for-each select="/data/extra/kits/kit[line_id=current()/id]/">
                                    <fo:table-row border-left-style="solid" border-right-style="solid" border-collapse="collapse">
                                        <fo:table-cell padding="1mm" number-columns-spanned="5" background-color="lightgray"><fo:block margin-left="10pt"><xsl:value-of select="item" /> is a kit requiring the following items:</fo:block></fo:table-cell>
                                    </fo:table-row>
                                    <xsl:for-each select="ststructure/">
                                    <fo:table-row>
                                        <fo:table-cell border-left-style="solid" border-right-style="solid" border-collapse="collapse" padding="1mm" number-columns-spanned="2"><fo:block margin-left="20pt"><xsl:value-of select="item" /></fo:block></fo:table-cell>
                                        <fo:table-cell border-left-style="solid" border-right-style="solid" border-collapse="collapse" padding="1mm"><fo:block><xsl:value-of select="qty" /></fo:block></fo:table-cell>
                                        <fo:table-cell border-left-style="solid" border-right-style="solid" border-collapse="collapse" padding="1mm"><fo:block><xsl:value-of select="uom" /></fo:block></fo:table-cell>
                                        <fo:table-cell border-left-style="solid" border-right-style="solid" border-collapse="collapse" padding="1mm"><fo:block></fo:block></fo:table-cell>
                                    </fo:table-row>
                                    </xsl:for-each>
                                    <fo:table-row><fo:table-cell  border-bottom-style="solid" background-color="lightgray" number-columns-spanned="5" padding="1mm" ><fo:block margin-bottom="3pt"></fo:block></fo:table-cell></fo:table-row>
                                </xsl:for-each>
                            </xsl:for-each>
                        </fo:table-body>
                    </fo:table>
                    <fo:block id="last-page"/>
                </fo:flow>
            </fo:page-sequence>
        </fo:root>
    </xsl:template>
</xsl:stylesheet>
DOC;

$test_xml = <<<'TEST'
<data>
<SOrder>
    <id>48</id>
    <alteredby>admin</alteredby>
    <base_net_value>£20,600.00</base_net_value>
    <countrycode></countrycode>
    <county></county>
    <created>09/05/2014 11:02</created>
    <createdby>admin</createdby>
    <currency>GBP</currency>
    <currency_id>GBP</currency_id>
    <customer>Blazing Apostles Ltd</customer>
    <del_address_id>Sunburst Road, , , Harrogate, Yorks, HG11 2TH, United Kingdom</del_address_id>
    <del_partyaddress_id></del_partyaddress_id>
    <delivery_address>Sunburst Road, , , Harrogate, Yorks, HG11 2TH, United Kingdom</delivery_address>
    <delivery_term></delivery_term>
    <delivery_term_id></delivery_term_id>
    <description>Test</description>
    <despatch_action>DespatchTC-Despatch from trade counter</despatch_action>
    <despatch_date>22/05/2014</despatch_date>
    <due_date>23/05/2014</due_date>
    <ext_reference></ext_reference>
    <gross_value></gross_value>
    <inv_address_id>Sunburst Road, , , Harrogate, Yorks, HG11 2TH, United Kingdom</inv_address_id>
    <invoice_address>Sunburst Road, , , Harrogate, Yorks, HG11 2TH, United Kingdom</invoice_address>
    <lastupdated>23/06/2020 14:20</lastupdated>
    <net_value>£20,600.00</net_value>
    <order_date>09/05/2014</order_date>
    <order_number>46</order_number>
    <person></person>
    <person_id></person_id>
    <postcode></postcode>
    <project></project>
    <project_id></project_id>
    <rate>1.00</rate>
    <slmaster_id>Blazing Apostles Ltd</slmaster_id>
    <status>Part Despatched</status>
    <street1></street1>
    <street2></street2>
    <street3></street3>
    <task></task>
    <task_id></task_id>
    <tax_value></tax_value>
    <text1>                                                  </text1>
    <text2>                                                  </text2>
    <text3>                                                  </text3>
    <town></town>
    <twin_currency>GBP</twin_currency>
    <twin_currency_id>GBP</twin_currency_id>
    <twin_net_value>20600.00</twin_net_value>
    <twin_rate>1.00</twin_rate>
    <type>Sales Order</type>
    <usercompanyid>1</usercompanyid>
    <whaction>DespatchTC-Despatch from trade counter</whaction>
    <SOrderLine>
        <id>109</id>
        <actual_despatch_date></actual_despatch_date>
        <alteredby>admin</alteredby>
        <base_net_value>4,000.00</base_net_value>
        <commodity_code></commodity_code>
        <created>22/06/2020 13:17</created>
        <createdby>admin</createdby>
        <currency>GBP</currency>
        <currency_id>GBP</currency_id>
        <customer>Blazing Apostles Ltd</customer>
        <del_qty>0</del_qty>
        <delivery_note></delivery_note>
        <description>MIS-001  -  Rocker Basket Type A</description>
        <due_date>2014-05-23</due_date>
        <due_delivery_date>23/05/2014</due_delivery_date>
        <due_despatch_date>22/05/2014</due_despatch_date>
        <ean></ean>
        <external_data></external_data>
        <glaccount>1110 - Product Sales</glaccount>
        <glaccount_centre_id>1293</glaccount_centre_id>
        <glaccount_id>1110 - Product Sales</glaccount_id>
        <glcentre>14 - Technical & Specials</glcentre>
        <glcentre_id>14 - Technical & Specials</glcentre_id>
        <item_code>MIS-001</item_code>
        <item_description>MIS-001 - Rocker Basket Type A</item_description>
        <lastupdated>22/06/2020 13:17</lastupdated>
        <line_discount>0</line_discount>
        <line_number>22</line_number>
        <line_qtydisc_percentage>0</line_qtydisc_percentage>
        <line_tradedisc_percentage>0</line_tradedisc_percentage>
        <line_value>0.00</line_value>
        <net_value>4,000.00</net_value>
        <not_despatchable>f</not_despatchable>
        <note></note>
        <order_date>2014-05-09</order_date>
        <order_id>46</order_id>
        <order_number>46</order_number>
        <order_qty>8</order_qty>
        <os_qty>8</os_qty>
        <price>500.00</price>
        <product_description>MIS-001  -  Rocker Basket Type A</product_description>
        <product_search></product_search>
        <productline_id>MIS-001  -  Rocker Basket Type A</productline_id>
        <rate>1.00</rate>
        <revised_qty>8</revised_qty>
        <slmaster_id>8</slmaster_id>
        <status>New</status>
        <stitem>MIS-001 - Rocker Basket Type A</stitem>
        <stitem_id>MIS-001 - Rocker Basket Type A</stitem_id>
        <stuom_id>Each</stuom_id>
        <tax_rate>Standard Rate VAT</tax_rate>
        <tax_rate_id>Standard Rate VAT</tax_rate_id>
        <taxrate>Standard Rate VAT</taxrate>
        <twin>GBP</twin>
        <twin_currency_id>GBP</twin_currency_id>
        <twin_net_value>4000.00</twin_net_value>
        <twin_rate>1.00</twin_rate>
        <type></type>
        <uom_name>Each</uom_name>
        <usercompanyid>1</usercompanyid>
    </SOrderLine>
    <SOrderLine>
        <id>157</id>
        <actual_despatch_date></actual_despatch_date>
        <alteredby>admin</alteredby>
        <base_net_value>700.00</base_net_value>
        <commodity_code></commodity_code>
        <created>23/06/2020 14:20</created>
        <createdby>admin</createdby>
        <currency>GBP</currency>
        <currency_id>GBP</currency_id>
        <customer>Blazing Apostles Ltd</customer>
        <del_qty>0</del_qty>
        <delivery_note></delivery_note>
        <description>KIT100 - KIT 100</description>
        <due_date>2014-05-23</due_date>
        <due_delivery_date>23/05/2014</due_delivery_date>
        <due_despatch_date>22/05/2014</due_despatch_date>
        <ean></ean>
        <external_data></external_data>
        <glaccount>1110 - Product Sales</glaccount>
        <glaccount_centre_id>1290</glaccount_centre_id>
        <glaccount_id>1110 - Product Sales</glaccount_id>
        <glcentre>11 - Medical</glcentre>
        <glcentre_id>11 - Medical</glcentre_id>
        <item_code>KIT100</item_code>
        <item_description>KIT100 - KIT 100</item_description>
        <lastupdated>23/06/2020 14:20</lastupdated>
        <line_discount>0</line_discount>
        <line_number>41</line_number>
        <line_qtydisc_percentage>0</line_qtydisc_percentage>
        <line_tradedisc_percentage>0</line_tradedisc_percentage>
        <line_value>0.00</line_value>
        <net_value>700.00</net_value>
        <not_despatchable>f</not_despatchable>
        <note></note>
        <order_date>2014-05-09</order_date>
        <order_id>46</order_id>
        <order_number>46</order_number>
        <order_qty>7</order_qty>
        <os_qty>7</os_qty>
        <price>100.00</price>
        <product_description>KIT100 - KIT 100</product_description>
        <product_search></product_search>
        <productline_id>KIT100 - KIT 100</productline_id>
        <rate>1.00</rate>
        <revised_qty>7</revised_qty>
        <slmaster_id>8</slmaster_id>
        <status>New</status>
        <stitem>KIT100 - KIT 100</stitem>
        <stitem_id>KIT100 - KIT 100</stitem_id>
        <stuom_id>Box</stuom_id>
        <tax_rate>Standard Rate VAT</tax_rate>
        <tax_rate_id>Standard Rate VAT</tax_rate_id>
        <taxrate>Standard Rate VAT</taxrate>
        <twin>GBP</twin>
        <twin_currency_id>GBP</twin_currency_id>
        <twin_net_value>700.00</twin_net_value>
        <twin_rate>1.00</twin_rate>
        <type></type>
        <uom_name>Box</uom_name>
        <usercompanyid>1</usercompanyid>
    </SOrderLine>
</SOrder>
<extra>
    <company_address>
        <name>uzERP Demo Company Limited</name>
        <street1>Showground Road</street1>
        <town>Bridgwater</town>
        <county>Somerset</county>
        <postcode>TA6 6AJ</postcode>
        <country>United Kingdom</country>
    </company_address>
    <order_details>
            <line>
                <label>Picked By:</label>
                <value>admin</value>
            </line>
            <line>
                <label>Order Date:</label>
                <value>09/05/2014</value>
            </line>
            <line>
                <label>Our Order Number:</label>
                <value>46</value>
            </line>
            <line>
                <label>Customer Ref:</label>
                <value></value>
            </line>
            <line>
                <label>Due Date:</label>
                <value>23/05/2014</value>
            </line>
    </order_details>
    <delivery_details>
        <customer>Blazing Apostles Ltd</customer>
        <full_address>Sunburst Road, Harrogate, Yorks, HG11 2TH, United Kingdom</full_address>
    </delivery_details>
    <kits>
            <kit>
                <line_id>157</line_id>
                <line_number>41</line_number>
                <item>KIT100 - KIT 100</item>
                        <ststructure>
                            <item>GRB-100 - HRN Grab Mini Moto</item>
                            <qty>7</qty>
                            <uom>Each</uom>
                        </ststructure>
                        <ststructure>
                            <item>GRB-150 - HRN Grab Sport X</item>
                            <qty>7</qty>
                            <uom>Each</uom>
                        </ststructure>
                        <ststructure>
                            <item>PRT-009 - Metal AX112 Ragged</item>
                            <qty>14</qty>
                            <uom>Each</uom>
                        </ststructure>
            </kit>
    </kits>
</extra>
</data>
TEST;

        $rows = $this->query("INSERT INTO report_definitions (name, definition, test_xml, usercompanyid) VALUES ('SOPickList_SALESKIT_example', '${template}', '${test_xml}', 1)");
    }

    public function down()
    {
        return;
    }
}
