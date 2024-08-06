<?php

use Phinx\Migration\AbstractMigration;

class PorderScheduleReportDef extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $xsl = <<<'UPDOC'
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
                <fo:region-body margin-top="10cm" margin-bottom="2cm"/>
                <fo:region-before extent="10cm" />
            </fo:simple-page-master>

            <!-- layout for the last pages -->
            <fo:simple-page-master master-name="last"
                page-height="29.7cm"
                page-width="21cm"
                margin="0.5cm" >
                <fo:region-body margin-top="10cm" margin-bottom="2cm"/>
                <fo:region-before extent="10cm" />
                <fo:region-after extent="2.5cm" />
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
            <fo:static-content flow-name="xsl-region-before" font-size="10px" font-family="Helvetica" >
               <!--[logo]-->
               <fo:block text-align="center" font-size="14px">PURCHASE ORDER DELIVERY SCHEDULE</fo:block>
               <fo:table table-layout="fixed" width="100%" margin-top="1cm" >
                  <fo:table-column column-width="proportional-column-width(1)" />
                  <fo:table-column column-width="proportional-column-width(1)" />
                  <fo:table-column column-width="proportional-column-width(1)" />
                  <fo:table-body>
                     <fo:table-row>
                        <fo:table-cell>
                           <xsl:for-each select="data/extra/company_address/*" >
                              <fo:block><xsl:value-of select="."/></fo:block>
                           </xsl:for-each>
                        </fo:table-cell>
                        <fo:table-cell>
                           <xsl:for-each select="data/extra/company_details/*" >
                              <fo:block><xsl:value-of select="."/></fo:block>
                           </xsl:for-each>
                        </fo:table-cell>
                        <fo:table-cell>
                           <fo:table table-layout="fixed" >
                              <fo:table-column column-width="3cm" />
                              <fo:table-column column-width="3cm" />
                              <fo:table-body>
                                 <fo:table-row>
                                    <fo:table-cell padding="0.5mm">
                                       <fo:block>Page:</fo:block>
                                    </fo:table-cell>
                                    <fo:table-cell padding="0.5mm" >
                                       <fo:block><!--[page_position]--></fo:block>
                                    </fo:table-cell>

                                 </fo:table-row>
                                 <xsl:for-each select="data/extra/document_reference/line" >
                                    <fo:table-row>
                                       <fo:table-cell padding="0.5mm">
                                          <fo:block>
                                             <xsl:value-of select="label"/>:
                                          </fo:block>
                                       </fo:table-cell>
                                       <fo:table-cell padding="0.5mm" >

                                          <fo:block>
                                             <xsl:value-of select="value"/>
                                          </fo:block>
                                       </fo:table-cell>
                                    </fo:table-row>
                                 </xsl:for-each>
                              </fo:table-body>
                           </fo:table>
                        </fo:table-cell>

                     </fo:table-row>
                  </fo:table-body>
               </fo:table>

               <!-- first line -->
               <fo:block-container
                  absolute-position="absolute"
                  top="5cm"
                  border-top-width="1pt"
                  border-top-style="solid"
                  border-top-color="<!--[border_colour]-->" >
                  <fo:block />
               </fo:block-container>

               <fo:table table-layout="fixed" width="100%" margin-top="1cm" >

                  <fo:table-column column-width="proportional-column-width(1)" />
                  <fo:table-column column-width="proportional-column-width(1)" />
                  <fo:table-body>
                     <fo:table-row>
                        <fo:table-cell>
                           <xsl:for-each select="data/extra/supplier_address/*" >
                              <fo:block><xsl:value-of select="."/></fo:block>
                           </xsl:for-each>
                        </fo:table-cell>

                        <fo:table-cell>
                           <xsl:for-each select="data/extra/delivery_address/*" >
                              <fo:block><xsl:value-of select="."/></fo:block>
                           </xsl:for-each>
                        </fo:table-cell>
                     </fo:table-row>
                  </fo:table-body>
               </fo:table>

               <!-- second line -->
               <fo:block-container
                  absolute-position="absolute"
                  top="9.5cm"
                  border-top-width="1pt"
                  border-top-style="solid"
                  border-top-color="<!--[border_colour]-->" >
                  <fo:block />
               </fo:block-container>
            </fo:static-content>
            <!-- PAGE FOOTER -->
            <fo:static-content flow-name="xsl-region-after"  font-size="10px">
               <fo:block border-top-width="1pt" border-top-style="solid" border-top-color="<!--[border_colour]-->" />
               
               <fo:block border-top-width="1pt" border-top-style="solid" border-top-color="<!--[border_colour]-->" />

            </fo:static-content>
            <!-- BODY -->
            <fo:flow flow-name="xsl-region-body"  font-family="Helvetica">
               <fo:table table-layout="fixed" font-size="8px">
                  <fo:table-column column-width="proportional-column-width(15)" />

                  <fo:table-column column-width="proportional-column-width(50)" />
                  <fo:table-column column-width="proportional-column-width(25)" />
                  <fo:table-column column-width="proportional-column-width(25)" />

                  <fo:table-column column-width="proportional-column-width(15)" />
                  <fo:table-header>
                     <fo:table-row>

                        <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
                        <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                           <fo:block font-weight="bold">Line</fo:block>
                        </fo:table-cell>
                        <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                           <fo:block font-weight="bold">Description</fo:block>
                        </fo:table-cell>
                        <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                           <fo:block font-weight="bold">Delivery Date</fo:block>

                        </fo:table-cell>
                        <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                           <fo:block font-weight="bold" text-align="right">Order Qty</fo:block>
                        </fo:table-cell>
                        <fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
                           <fo:block font-weight="bold" text-align="right">Units</fo:block>
                        </fo:table-cell>
                     </fo:table-row>

                  </fo:table-header>
                  <fo:table-body>
                     <xsl:for-each select="data/POrder/POrderLine" >
                        <fo:table-row>
                           <fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
                              <fo:block><xsl:value-of select="line_number" /></fo:block>
                           </fo:table-cell>
                           <fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
                              <fo:block><xsl:value-of select="description"/></fo:block>

                           </fo:table-cell>
                           <fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
                              <fo:block><xsl:value-of select="due_delivery_date"/></fo:block>
                           </fo:table-cell>
                           <fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
                              <fo:block text-align="right"><xsl:value-of select="revised_qty"/></fo:block>
                           </fo:table-cell>
                           <fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
                              <fo:block text-align="right"><xsl:value-of select="uom_name"/></fo:block>

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
UPDOC;
        $date = new DateTime();
        $update_time = $date->format('Y-m-d H:i:s.u');
        $result = $this->query("INSERT INTO report_definitions (name, definition, lastupdated, alteredby, usercompanyid) VALUES ('PurchaseOrderSchedule', '{$xsl}', '{$update_time}', 'phinx', '1')");
    }
    
    public function down()
    {
        $result = $this->query("DELETE FROM report_definitions WHERE name='PurchaseOrderSchedule'");
    }
}
