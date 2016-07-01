<?php
use UzerpPhinx\UzerpMigration;

class AddReportDefSolinesSummary extends UzerpMigration
{

    /**
     * Add Sales order lines summary report definition
     */
    public function up()
    {
        $xsl = <<<'DOC'
<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<!-- define the various layouts -->
			<fo:layout-master-set>
				<!-- layout for the all pages -->
				<fo:simple-page-master master-name="rest"
						page-height="21cm"
						page-width="29.7cm"
                        margin="0.5cm" >
					<fo:region-body margin-top="1cm" margin-bottom="7mm"/>
					<fo:region-before extent="1cm"  />
				</fo:simple-page-master>
				<!-- layout for the last pages -->
				<fo:simple-page-master master-name="last"
						page-height="21cm"
						page-width="29.7cm"
			            margin="0.5cm" >
					<fo:region-body margin-top="1cm" margin-bottom="7mm"/>
					<fo:region-before extent="1cm"  />
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
					<fo:block font-size="12px" text-align="center" padding-bottom="3mm">
						Sales Order <xsl:value-of select="data/SOrder/order_number"/> - <xsl:value-of select="data/SOrder/customer"/>
					</fo:block>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" font-size="10px">
					<fo:block text-align="right"></fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(95)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(65)"/>
						<fo:table-header>
							<fo:table-row>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Description</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Order Qty</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Units</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Notes</fo:block>
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
											<xsl:value-of select="description" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block text-align="right">
											<xsl:value-of select="revised_qty" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block text-align="right">
											<xsl:value-of select="uom_name" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block>
											<xsl:value-of select="note" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
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
        $date = new DateTime();

        $report_def = [
            'name' => 'SOLinesSummary',
            'definition' => $xsl,
            'created' => $date->format('Y-m-d H:i:s.u'),
            'createdby' => 'phinx',
            'usercompanyid' => '1'
        ];

        $table = $this->table('report_definitions');
        $table->insert($report_def);
        $table->saveData();
    }


    /**
     * Remove Sales order lines summary report definition
     */
    public function down()
    {
        $this->execute("DELETE FROM report_definitions WHERE name='SOLinesSummary'");
    }
}
