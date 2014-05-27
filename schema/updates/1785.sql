--
-- $Revision: 1.2 $
--

-- Table: company

ALTER TABLE company ADD COLUMN tax_description character varying DEFAULT 'VAT Code';

UPDATE company
   SET tax_description = 'VAT Code'
 WHERE vatnumber <> ''
   AND EXISTS (SELECT 1
                 FROM tax_statuses ts
                    , slmaster slm
                WHERE slm.tax_status_id = ts.id
                  AND slm.company_id = company.id
                  AND (
                       (apply_tax AND NOT eu_tax)
                       OR
                       (NOT apply_tax AND eu_tax)
                      )
               );

-- Table: report_definitions

UPDATE report_definitions
   SET definition = '﻿<?xml version="1.0" encoding="UTF-8"?>
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
					<fo:region-body margin-top="9cm" margin-bottom="4.5cm"/>
					<fo:region-before extent="9cm" />
				</fo:simple-page-master>
				<!-- layout for the last pages -->
				<fo:simple-page-master master-name="last"
						page-height="29.7cm"
						page-width="21cm"
			                        margin="0.5cm" >
					<fo:region-body margin-top="9cm" margin-bottom="4.5cm"/>
					<fo:region-before extent="9cm" />
					<fo:region-after extent="4.5cm" />
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
				<fo:static-content flow-name="xsl-region-before" font-size="9pt" font-family="Helvetica" >
					<!--[logo]-->
					<!-- list company address -->
					<fo:block-container absolute-position="absolute" left="6cm" top="0" width="7cm" >
						<xsl:for-each select="data/extra/company_address/*" >
							<fo:block><xsl:value-of select="."/></fo:block>
						</xsl:for-each>
					</fo:block-container>
					<!-- list company details -->
					<fo:block-container absolute-position="absolute" right="0cm" top="0" width="7cm" >
						<xsl:for-each select="data/extra/company_details/*" >
							<fo:block><xsl:value-of select="."/></fo:block>
						</xsl:for-each>
					</fo:block-container>
					<!-- list delivery address -->
					<fo:block-container 
						<!--[address_block_position]--> >
						<xsl:for-each select="data/extra/invoice_address/*" >
							<fo:block><xsl:value-of select="."/></fo:block>
						</xsl:for-each>
					</fo:block-container>
					<xsl:if test="data/extra/vatnumber != \'\'">
						<fo:block-container absolute-position="absolute" left="0.78cm" top="8.22cm" >
							<fo:table table-layout="fixed" >
								<fo:table-column column-width="3cm" />
								<fo:table-column column-width="4.23cm" />
								<fo:table-body>
									<fo:table-row>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="data/extra/tax_description" /></fo:block>
										</fo:table-cell>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="data/extra/vatnumber" /></fo:block>
										</fo:table-cell>
									</fo:table-row>
								</fo:table-body>
							</fo:table>
						</fo:block-container>
					</xsl:if>
					<!-- pro forma title -->
					<fo:block-container absolute-position="absolute" right="0" top="4cm" width="7cm" text-align="center">
						<fo:block><xsl:value-of select="data/SInvoice/transaction_type"/></fo:block>
					</fo:block-container>
					<!-- list delivery details -->
					<fo:block-container absolute-position="absolute" right="0" top="4.5cm" width="7cm">	
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="2.5cm" />
							<fo:table-column column-width="4.5cm" />
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Page</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><!--[page_position]--></fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Date</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SInvoice/invoice_date" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Number</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SInvoice/invoice_number" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Your Ref</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SInvoice/ext_reference" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Del Date</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SInvoice/despatch_date" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Del Note</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/delivery_note" /></fo:block>								</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after"  font-size="8px">
					<fo:block border-top-width="1pt" border-top-style="solid" border-top-color="<!--[border_colour]-->"></fo:block>
					<!-- vat analysis table -->
					<fo:block-container absolute-position="absolute" left="0" top="3mm" width="10.5cm" height="2.5cm">

						<xsl:choose>
							<xsl:when test="data/extra/vat_analysis_exempt">
								<fo:table table-layout="fixed" >
									<fo:table-column column-width="proportional-column-width(1)" />
									<fo:table-header>
										<fo:table-row>
											<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">VAT Analysis</fo:block>
											</fo:table-cell>
										</fo:table-row>
									</fo:table-header>
									<fo:table-body>
										<xsl:for-each select="data/extra/vat_analysis_exempt/line">
											<fo:table-row>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="." /></fo:block>
												</fo:table-cell>
											</fo:table-row>
										</xsl:for-each>
									</fo:table-body>
								</fo:table>
							</xsl:when>
							<xsl:otherwise>
								<fo:table table-layout="fixed" >
									<fo:table-column column-width="proportional-column-width(25)" />
									<fo:table-column column-width="proportional-column-width(15)" />
									<fo:table-column column-width="proportional-column-width(15)" />
									<fo:table-column column-width="proportional-column-width(25)" />
									<fo:table-column column-width="proportional-column-width(20)" />
									<fo:table-header>
										<fo:table-row>
											<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">VAT Analysis</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">Curr</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">Rate</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">Net Amount</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
												<fo:block font-weight="bold">VAT</fo:block>
											</fo:table-cell>
										</fo:table-row>
									</fo:table-header>
									<fo:table-body>
										<xsl:for-each select="data/extra/vat_analysis/line">
											<fo:table-row>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="description" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="currency" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="tax_rate" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="net_value" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
													<fo:block><xsl:value-of select="tax_value" /></fo:block>
												</fo:table-cell>
											</fo:table-row>
										</xsl:for-each>
									</fo:table-body>
								</fo:table>
							</xsl:otherwise>
						</xsl:choose>
					<!-- settlement table -->
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(100)" />
							<fo:table-header>
								<fo:table-row>
									<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">Settlement Terms</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<fo:table-row height="1cm">
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/settlement_terms" /></fo:block>
										<xsl:if test="data/extra/bank_account/bank_account_number != \'\'">
											<fo:block>
												Bank Name <xsl:value-of select="data/extra/bank_account/bank_name" />
												Account Number <xsl:value-of select="data/extra/bank_account/bank_account_number" />
												Sort Code <xsl:value-of select="data/extra/bank_account/bank_sort_code" />
											</fo:block>
											<fo:block>Address <xsl:value-of select="data/extra/bank_account/bank_address" /></fo:block>
											<fo:block>
												<xsl:if test="data/extra/bank_account/bank_iban_number != \'\'">
													Iban Number <xsl:value-of select="data/extra/bank_account/bank_iban_number" />
												</xsl:if>
												<xsl:if test="data/extra/bank_account/bank_bic_code != \'\'">
													Bic Code <xsl:value-of select="data/extra/bank_account/bank_bic_code" />
												</xsl:if>
											</fo:block>
										</xsl:if>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					<!-- totals table -->
					<fo:block-container absolute-position="absolute" right="0" top="3mm" width="9cm">	
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(60)" />
							<fo:table-column column-width="proportional-column-width(40)" />
							<fo:table-body>
								<xsl:for-each select="data/extra/invoice_totals/line">
									<fo:table-row>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="field1" /></fo:block>
										</fo:table-cell>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block text-align="right"><xsl:value-of select="field2" /></fo:block>
										</fo:table-cell>
									</fo:table-row>
								</xsl:for-each>
							</fo:table-body>
						</fo:table>

					<!-- delivery table -->
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(100)" />
							<fo:table-header>
								<fo:table-row>
									<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">Delivery Address</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<fo:table-row height="1cm">
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/delivery_address" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body"  font-family="Helvetica">
					<xsl:if test="data/SInvoice/description != \'\' or data/extra/notes != \'\'">
						<fo:table table-layout="fixed" font-size="8px" margin-bottom="5mm">
							<fo:table-column column-width="proportional-column-width(1)" />
							<fo:table-column column-width="proportional-column-width(1)" />
							<fo:table-header>
								<fo:table-row>
									<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">Notes</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">Description</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/notes" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/SInvoice/description" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:if>
					<fo:table table-layout="fixed" font-size="8px">
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(54)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-header>
							<fo:table-row>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Item Code</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Description</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Qty</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Units</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">Price</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">Net Amount</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/SInvoice/SInvoiceLine" >
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="item_code" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="description" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="sales_qty"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="uom_name"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="sales_price"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="net_value"/></fo:block>
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
</xsl:stylesheet>'
 WHERE name = 'SalesInvoice';

