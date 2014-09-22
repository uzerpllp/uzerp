<?php

use Phinx\Migration\AbstractMigration;

class ReportDefinitionSalesAcknowledgement extends AbstractMigration
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
					<fo:block text-align="right">Total Order Value: <xsl:value-of select="data/SOrder/base_net_value" /></fo:block>

				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(95)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
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
									<fo:block text-align="right">Due Date</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Price</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Order Value</fo:block>

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
											<xsl:value-of select="due_delivery_date" />
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
UPDOC;
        $date = new DateTime();
        $update_time = $date->format('Y-m-d H:i:s.u');
        $rows = $this->query("UPDATE report_definitions SET definition='${xsl}', lastupdated='${update_time}', alteredby='phinx' WHERE name='SOacknowledgement'");
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $xsl = <<<'DOWNDOC'
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
					<fo:region-body margin-top="10cm" margin-bottom="7mm"/>
					<fo:region-before extent="10cm"  />
				</fo:simple-page-master>
				<!-- layout for the last pages -->
				<fo:simple-page-master master-name="last"
						page-height="29.7cm"
						page-width="21cm"
			                        margin="0.5cm" >
					<fo:region-body margin-top="10cm" margin-bottom="7mm"/>
					<fo:region-before extent="10cm"  />
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
					<fo:block text-align="right">Total Order Value: <xsl:value-of select="data/SOrder/base_net_value" /></fo:block>

				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(95)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
						<fo:table-column column-width="proportional-column-width(25)"/>
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
									<fo:block text-align="right">Price</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block text-align="right">Order Value</fo:block>

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
DOWNDOC;
        $rows = $this->query("UPDATE report_definitions SET definition='${xsl}', lastupdated='2014-06-24 12:55:45', alteredby='phinx' WHERE name='SOacknowledgement'");
    }
}

