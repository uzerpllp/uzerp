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
						page-height="27.9cm"
						page-width="21.5cm"
						margin="1cm" >
					<fo:region-body margin-top="1cm" margin-bottom="4.5cm" />
					<fo:region-before extent="1cm" />
				</fo:simple-page-master>
				<!-- layout for the last pages -->
				<fo:simple-page-master master-name="last"
						page-height="27.9cm"
						page-width="21.5cm"
						margin="1cm" >
					<fo:region-body margin-top="1cm" margin-bottom="4.5cm" />
					<fo:region-before extent="1cm" />
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
			<!-- end: defines page layout -->


			<!-- start sequence -->
			<fo:page-sequence master-reference="default-sequence">
				<!-- PAGE HEADER -->
				<fo:static-content flow-name="xsl-region-before" font-family="Courier, monospace, Monospaced" font-size="12pt">
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-body>
							<fo:table-row>
								<fo:table-cell padding="1mm">
									<fo:block />
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="center">DESPATCH NOTE</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm">
									<fo:block text-align="right">Page <!--[page_position]--></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after"  font-family="Courier, monospace, Monospaced" font-size="12px">
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(33)" />
						<fo:table-column column-width="proportional-column-width(33)" />
						<fo:table-column column-width="proportional-column-width(33)" />
						<fo:table-body>
							<fo:table-row height="1cm">
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>No. of Pallets:</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Weight:</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Cube:</fo:block>
								</fo:table-cell>
							</fo:table-row>
							<fo:table-row height="1cm">
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Driver''s Name:</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Driver''s Signature:</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Date and Time:</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(50)" />
						<fo:table-column column-width="proportional-column-width(50)" />
						<fo:table-body>
							<fo:table-row height="1cm">
								<fo:table-cell padding="1mm" border-style="solid" border-width="1pt">
									<fo:block>Vehicle Reg No:</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" border-style="solid" border-width="1pt">
									<fo:block>Loader''s Initials:</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(20)" />
						<fo:table-column column-width="proportional-column-width(35)" />
						<fo:table-column column-width="proportional-column-width(20)" />
						<fo:table-column column-width="proportional-column-width(15)" />
						<fo:table-column column-width="proportional-column-width(15)" />
						<fo:table-column column-width="proportional-column-width(15)" />
						<fo:table-column column-width="proportional-column-width(15)" />
						<fo:table-column column-width="proportional-column-width(15)" />
						<fo:table-column column-width="proportional-column-width(20)" />
						<fo:table-body>
							<fo:table-row height="1cm">
								<fo:table-cell text-align="center" display-align="after">
									<fo:block></fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Pickup Required</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block></fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Mon</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Tue</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Wed</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Thu</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block>Fri</fo:block>
								</fo:table-cell>
								<fo:table-cell text-align="center" display-align="after">
									<fo:block></fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-body>
					</fo:table>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body"  font-family="Courier, monospace, Monospaced" font-size="12px">
					<!-- list company address -->
					<fo:table table-layout="fixed" width="100%" >
						<fo:table-column column-width="proportional-column-width(1)"/>
						<fo:table-column column-width="proportional-column-width(1)"/>
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
							</fo:table-row>
						</fo:table-body>
					</fo:table>

					<!-- despatch details -->
					<fo:block-container border-top-style="solid" border-top-width="1pt" >
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(60)"/>
							<fo:table-column column-width="proportional-column-width(25)"/>
							<fo:table-column column-width="proportional-column-width(50)"/>
							<fo:table-column column-width="proportional-column-width(35)"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding-top="3mm">
										<fo:block>Despatch Note Number</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block><xsl:value-of select="data/SODespatchLine/despatch_number" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block>Despatch Note Date</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block><xsl:value-of select="data/SODespatchLine/despatch_date" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding-top="3mm">
										<fo:block>Our Order Number</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block><xsl:value-of select="data/SOrder/order_number" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block>Customer''s Order No.</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm">
										<fo:block><xsl:value-of select="data/SOrder/ext_reference" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					<!-- address details etc -->
					<fo:block-container border-top-style="solid" border-top-width="1pt" >
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(60)"/>
							<fo:table-column column-width="proportional-column-width(120)"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block>SDL Customer Number</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block><xsl:value-of select="data/extra/account_number" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell>
										<xsl:for-each select="data/extra/customer_address/*" >
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
					</fo:block-container>
					<fo:block-container left="0" border-top-style="solid" border-top-width="1pt" >
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-column column-width="proportional-column-width(1)"/>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block>Delivery Instructions</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block><xsl:value-of select="data/SOrder/due_date"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block>Description</fo:block>
									</fo:table-cell>
									<fo:table-cell padding-top="3mm" padding-bottom="3mm">
										<fo:block><xsl:value-of select="data/SOrder/description"/></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</fo:block-container>
					<fo:table table-layout="fixed" width="100%">
						<fo:table-column column-width="proportional-column-width(145)" />
						<fo:table-column column-width="proportional-column-width(20)" />
						<fo:table-column column-width="proportional-column-width(20)" />
						<fo:table-header>
							<fo:table-row>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Description</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Qty</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block>Units</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/SODespatchLine/SOrderLine" >				
								<fo:table-row>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="description" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="revised_qty" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="uom_name" /></fo:block>
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
</xsl:stylesheet>'
 WHERE name = 'DespatchNote';


