<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class LedgerAllocationReportDef extends UzerpMigration
{
    public function up()
    {
        $xsl = <<<'UPDOC'
<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="21cm"
						page-width="29.7cm"
						margin="1cm" >
					<fo:region-body margin-top="1cm" margin-bottom="1.1cm"/>
					<fo:region-before extent="1cm"/>
					<fo:region-after extent="5mm"/>
	  			</fo:simple-page-master>
		  	</fo:layout-master-set>
			<!-- format is the style of page numbering, 1 for 1,2,3, i for roman numerals (sp?)-->
			<fo:page-sequence master-reference="all-pages" format="1">
				<!-- header with running glossary entries -->
				<fo:static-content flow-name="xsl-region-before">
					<fo:block>Allocations</fo:block>
				</fo:static-content>
				<fo:static-content flow-name="xsl-region-after">
					<fo:block>Page <!--[page_position]--></fo:block>
				</fo:static-content>
				<fo:flow flow-name="xsl-region-body" >
					<fo:table table-layout="fixed" width="100%" font-size="8pt">
                        <fo:table-column column-width="proportional-column-width(150)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(50)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
                        <fo:table-column column-width="proportional-column-width(150)"/>
                        <fo:table-column column-width="proportional-column-width(100)"/>
						<fo:table-header>
				                            <fo:table-row border-bottom-style ="solid" font-weight="bold">
				                                <xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Company</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Transaction Date</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Transaction Type</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Our Ref</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Ext Ref</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block>Currency</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block text-align="right">Gross Value</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block text-align="right">Allocation Date</fo:block>
				                                </fo:table-cell>
				                                <fo:table-cell padding="1mm">
				                                    <fo:block text-align="right">Payment Value</fo:block>
				                                </fo:table-cell>
				                            </fo:table-row>
				                </fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/*">
								<fo:table-row>
									<!-- this condition is to provide us with alternate row colours -->
									<xsl:if test="(position() mod 2 = 1)">
										<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
									</xsl:if>
									<xsl:variable name="company" select="supplier"/>
									<xsl:choose>
										<xsl:when test="$company != ''''">
										        <fo:table-cell padding="1mm"><fo:block><xsl:value-of select="supplier" /></fo:block></fo:table-cell>
										</xsl:when>
										<xsl:otherwise>
										        <fo:table-cell padding="1mm"><fo:block><xsl:value-of select="customer" /></fo:block></fo:table-cell>
										</xsl:otherwise>
									</xsl:choose>
									<fo:table-cell padding="1mm"><fo:block><xsl:value-of select="transaction_date" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block><xsl:value-of select="transaction_type" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block><xsl:value-of select="our_reference" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block><xsl:value-of select="ext_reference" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block><xsl:value-of select="currency" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block text-align="right"><xsl:value-of select="gross_value" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block text-align="right"><xsl:value-of select="allocation_date" /></fo:block></fo:table-cell>
									<fo:table-cell padding="1mm"><fo:block text-align="right"><xsl:value-of select="payment_value" /></fo:block></fo:table-cell>
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
        $result = $this->query("INSERT INTO report_definitions (name, definition, lastupdated, alteredby, usercompanyid) VALUES ('LedgerAllocation', '{$xsl}', '{$update_time}', 'phinx', '1')");
    }
    
    public function down()
    {
        $result = $this->query("DELETE FROM report_definitions WHERE name='LedgerAllocation'");
    }
}

