--
-- $Revision: 1.1 $
--

INSERT INTO report_definitions
(name, definition, usercompanyid)
SELECT 'customer_service',
'<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="29.7cm"
						page-width="21cm"
			                        margin="0.5cm" >
					<fo:region-body margin-top="2cm" margin-bottom="5mm"/>
					<fo:region-before extent="2cm"  />
					<fo:region-after extent="5mm"/>
				</fo:simple-page-master>
				<fo:page-sequence-master master-name="default-sequence">
					<fo:repeatable-page-master-reference master-reference="all-pages"/>
				</fo:page-sequence-master>
			</fo:layout-master-set>
			<fo:page-sequence master-reference="default-sequence">
				<!-- PAGE HEADER -->
				<fo:static-content flow-name="xsl-region-before" >
					<fo:block font-size="16px" text-align="center">
						<xsl:value-of select="/data/extra/title" />
					</fo:block>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" font-size="8px">
					<fo:block display-align="after">Page <!--[page_position]--></fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<!-- ORDER -->
					<fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="center">
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-column column-width="proportional-column-width(45)"/>
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-header>
								<fo:table-row>
									<fo:table-cell>
										<fo:block>Product Group</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block>Customer</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block>On Time</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block>In Full</fo:block>
									</fo:table-cell>
									<fo:table-cell>
										<fo:block>On Time/In Full</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<xsl:for-each select="data/extra/customerservice/group">
									<xsl:for-each select="data">
										<fo:table-row>
											<!-- this condition is to provide us with alternate row colours -->
											<xsl:if test="(position() mod 2 = 1)">
												<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
											</xsl:if>
											<fo:table-cell padding="1mm">
												<fo:block text-align="left">
													<xsl:value-of select="title" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="left">
													<xsl:value-of select="customer" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="ontime_c" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="infull_c" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="ontime_infull_c" />
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
									</xsl:for-each>
									<xsl:for-each select="total">
										<fo:table-row>
											<!-- this condition is to provide us with alternate row colours -->
											<xsl:if test="(position() mod 2 = 1)">
												<xsl:attribute name="background-color"><!--[table_row_total_colour]--></xsl:attribute>
											</xsl:if>
											<fo:table-cell padding="1mm">
												<fo:block text-align="left">
													<xsl:value-of select="title" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="left">
													<xsl:value-of select="customer" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="ontime_c" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="infull_c" />
												</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm">
												<fo:block text-align="right">
													<xsl:value-of select="ontime_infull_c" />
												</fo:block>
											</fo:table-cell>
										</fo:table-row>
									</xsl:for-each>
								</xsl:for-each>
							</fo:table-body>
						</fo:table>
					</fo:block>
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>',
  id
  FROM system_companies;

INSERT INTO report_definitions
(name, definition, usercompanyid)
SELECT 'cs_failure_codes',
'<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<fo:layout-master-set>
				<fo:simple-page-master master-name="all-pages"
						page-height="29.7cm"
						page-width="21cm"
			                        margin="0.5cm" >
					<fo:region-body margin-top="2cm" margin-bottom="5mm"/>
					<fo:region-before extent="2cm"  />
					<fo:region-after extent="5mm"/>
				</fo:simple-page-master>
				<fo:page-sequence-master master-name="default-sequence">
					<fo:repeatable-page-master-reference master-reference="all-pages"/>
				</fo:page-sequence-master>
			</fo:layout-master-set>
			<fo:page-sequence master-reference="default-sequence">
				<!-- PAGE HEADER -->
				<fo:static-content flow-name="xsl-region-before" >
					<fo:block font-size="16px" text-align="center">
						<xsl:value-of select="/data/extra/title" />
					</fo:block>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" font-size="8px">
					<fo:block display-align="after">Page <!--[page_position]--></fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body" font-size="10px">
					<!-- ORDER -->
					<fo:block border-top-width="1pt" border-top-style="solid" padding-top="5mm" padding-bottom="5mm" text-align="left">
						<fo:table table-layout="fixed" width="100%" >
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-column column-width="proportional-column-width(15)"/>
							<fo:table-column column-width="proportional-column-width(45)"/>
							<fo:table-header>
								<fo:table-row>
									<fo:table-cell padding="1mm">
										<fo:block>Period</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block  text-align="right">Count</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm">
										<fo:block>Failure Description</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<xsl:for-each select="data/extra/cs_failure_codes/data">
									<fo:table-row>
										<!-- this condition is to provide us with alternate row colours -->
										<xsl:if test="(position() mod 2 = 1)">
											<xsl:attribute name="background-color"><!--[table_row_alternate_colour]--></xsl:attribute>
										</xsl:if>
										<fo:table-cell padding="1mm">
											<fo:block text-align="left">
												<xsl:value-of select="period" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell padding="1mm">
											<fo:block text-align="right">
												<xsl:value-of select="count" />
											</fo:block>
										</fo:table-cell>
										<fo:table-cell padding="1mm">
											<fo:block text-align="left">
												<xsl:value-of select="description" />
											</fo:block>
										</fo:table-cell>
									</fo:table-row>
								</xsl:for-each>
							</fo:table-body>
						</fo:table>
					</fo:block>
					<fo:block id="last-page"/>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>',
  id
  FROM system_companies;