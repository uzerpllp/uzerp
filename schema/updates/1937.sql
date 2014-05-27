--
-- $Revision: 1.1 $
--

INSERT INTO report_definitions
(name, definition, usercompanyid)
SELECT 'SOQuote',
'﻿<?xml version="1.0" encoding="UTF-8"?>
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
					<fo:region-body margin-top="10cm" margin-bottom="7mm"/>
					<fo:region-before extent="10cm"  />
					<fo:region-after extent="2cm"/>
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
						<xsl:value-of select="data/extra/title"/>
					</fo:block>
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(60)"/>
						<fo:table-column column-width="proportional-column-width(60)"/>
						<fo:table-column column-width="proportional-column-width(60)"/>
						<fo:table-body>
							<fo:table-row height="3.5cm" border-bottom-style="solid" border-bottom-width="1pt" border-bottom-color="<!--[border_colour]-->">
								<fo:table-cell padding="1mm">
									<!--[logo_relative]-->
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<xsl:for-each select="data/extra/company_address/*" >
										<fo:block><xsl:value-of select="."/></fo:block>
									</xsl:for-each>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<xsl:for-each select="data/extra/company_details/*" >
										<fo:block><xsl:value-of select="."/></fo:block>
									</xsl:for-each>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row height="4.5cm" border-bottom-style="solid" border-bottom-width="1pt" border-bottom-color="<!--[border_colour]-->">
								<fo:table-cell padding="1mm" padding-top="3mm">
									<xsl:for-each select="data/extra/customer_address/*" >
										<fo:block><xsl:value-of select="."/></fo:block>
									</xsl:for-each>
								</fo:table-cell>
								<fo:table-cell padding="1mm" padding-top="3mm">
									<xsl:for-each select="data/extra/delivery_address/*" >
										<fo:block><xsl:value-of select="."/></fo:block>
									</xsl:for-each>
								</fo:table-cell>
								<fo:table-cell padding="1mm" padding-top="3mm">
									<fo:block>
										<fo:table table-layout="fixed" >
											<fo:table-column column-width="3.5cm" />
											<fo:table-column column-width="2.5cm" />
											<fo:table-body>
												<fo:table-row>
													<fo:table-cell padding="0.5mm">
														<fo:block>Page</fo:block>
													</fo:table-cell>
													<fo:table-cell padding="0.5mm">
														<fo:block><!--[page_position]--></fo:block>
													</fo:table-cell>
												</fo:table-row>
												<xsl:for-each select="data/extra/order_details/line" >
													<fo:table-row>
														<fo:table-cell padding="0.5mm">
															<fo:block>
																<xsl:value-of select="label"/>
															</fo:block>
														</fo:table-cell>
														<fo:table-cell padding="0.5mm">
															<fo:block>
																<xsl:value-of select="value"/>
															</fo:block>
														</fo:table-cell>
													</fo:table-row>
												</xsl:for-each>
											</fo:table-body>
										</fo:table>
									</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" font-size="10px">
					<fo:block text-align="right">
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-column column-width="6.5cm" />
							<fo:table-column column-width="2.5cm" />
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding="1mm" column-number="2" <!--[table_cell_borders]--> >
										<fo:block text-align="left">Total Net Value (Excl VAT): </fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" column-number="3" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SOrder/base_net_value" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="1mm" column-number="2" <!--[table_cell_borders]--> >
										<fo:block text-align="left">Estimated Total VAT (at current rates): </fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" column-number="3" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/order_totals/VAT" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="1mm" column-number="2" <!--[table_cell_borders]--> >
										<fo:block text-align="left">Total Order Value (Inc VAT): </fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" column-number="3" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/order_totals/gross" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(100)"/>
						<fo:table-column column-width="proportional-column-width(20)"/>
						<fo:table-column column-width="proportional-column-width(20)"/>
						<fo:table-column column-width="proportional-column-width(20)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(20)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
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
									<fo:block text-align="right">Net Price</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Net Value</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Estimated VAT</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Gross Value</fo:block>
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
										<fo:block text-align="right">
											<xsl:value-of select="price" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block text-align="right">
											<xsl:value-of select="base_net_value" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block text-align="right">
											<xsl:value-of select="vat_value" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block text-align="right">
											<xsl:value-of select="gross_value" />
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
</xsl:stylesheet>',
  id
  FROM system_companies;
