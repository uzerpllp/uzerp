--
-- $Revision: 1.1 $
--

UPDATE report_definitions
   SET definition =
'<?xml version="1.0" encoding="UTF-8"?>
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
					<fo:region-body margin-top="9cm" margin-bottom="0"/>
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
				<fo:static-content flow-name="xsl-region-before" font-size="10px" >
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
					<!-- list paymemt address -->
					<fo:block-container 
						<!--[address_block_position]--> >
						<xsl:for-each select="data/extra/payment_address/*" >
							<fo:block><xsl:value-of select="."/></fo:block>
						</xsl:for-each>
					</fo:block-container>
					<!-- title -->
					<fo:block-container absolute-position="absolute" right="0" top="4cm" width="7cm" text-align="center">
						<fo:block font-weight="bold">STATEMENT</fo:block>
					</fo:block-container>
					<!-- list delivery details -->
					<fo:block-container absolute-position="absolute" right="0" top="4.5cm" width="6cm">	
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="3cm" />
							<fo:table-column column-width="3cm" />
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding="0.5mm">
										<fo:block>Page</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><!--[page_position]--></fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm">
										<fo:block>Date</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="data/extra/current_date" /></fo:block>								</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					<!-- bottom line -->
					<fo:block-container 
						absolute-position="absolute" 
						top="8.7cm"
						border-top-width="1pt" 
						border-top-style="solid" 
						border-top-color="<!--[border_colour]-->" >
						<fo:block />
					</fo:block-container>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after"  font-size="8px">
					<fo:block border-top-width="1pt" border-top-style="solid" border-top-color="<!--[border_colour]-->" />
					<!-- totals table -->
					<fo:block-container absolute-position="absolute" right="2cm" top="0" width="6cm">
						<fo:block text-align="center"  font-size="10px" font-weight="bold" margin-bottom="1mm" margin-top="1mm">Aged Summary</fo:block>
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(30)" />
							<fo:table-column column-width="proportional-column-width(30)" />
							<fo:table-body>
								<xsl:for-each select="data/extra/aged_debtor_summary/line">
									<fo:table-row>
										<xsl:if test="(position() mod 2 = 1)">
											<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
										</xsl:if>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="title" /></fo:block>
										</fo:table-cell>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block text-align="right"><xsl:value-of select="value" /></fo:block>
										</fo:table-cell>
									</fo:table-row>
								</xsl:for-each>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body"  font-family="Helvetica">
					<fo:table table-layout="fixed" font-size="8px">
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-column column-width="proportional-column-width(25)" />
						<fo:table-header>
							<fo:table-row font-weight="bold">
								<fo:table-cell padding="1mm">
									<fo:block>Type</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Reference</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Date</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Due Date</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Status</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="right">Original</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="right">Outstanding</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block>Currency</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/SLCustomer/SLTransaction" >
								<fo:table-row>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="transaction_type" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="our_reference"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="transaction_date"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="due_date"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="status"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block text-align="right"><xsl:value-of select="gross_value"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block text-align="right"><xsl:value-of select="os_value"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm">
										<fo:block><xsl:value-of select="currency"/></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</xsl:for-each>
							<fo:table-row font-weight="bold" border-top-width="1pt" border-top-style="solid" border-top-color="<!--[border_colour]-->">
								<fo:table-cell padding="0.5mm" number-columns-spanned="4">
									<fo:block />
								</fo:table-cell>
								<fo:table-cell padding="0.5mm" text-align="right" number-columns-spanned="2">
									<fo:block>Outstanding Balance</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="0.5mm">
									<fo:block text-align="right"><xsl:value-of select="data/SLCustomer/outstanding_balance"/></fo:block>
								</fo:table-cell>
								<fo:table-cell padding="0.5mm">
									<fo:block><xsl:value-of select="data/SLCustomer/currency"/></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<!-- this is required to calculate the last page number -->
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>'
 WHERE name = 'Statement';


