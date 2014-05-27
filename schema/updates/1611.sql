--
-- $Revision: 1.1 $
--

-- View: po_receivedoverview

DROP VIEW po_receivedoverview;

CREATE OR REPLACE VIEW po_receivedoverview AS 
 SELECT pr.id, pr.gr_number, pr.order_id, pr.plmaster_id, pr.received_date, pr.received_qty, pr.orderline_id
 , pr.productline_id, pr.stuom_id, pr.stitem_id, pr.status, pr.usercompanyid, pr.item_description, pr.delivery_note
 , pr.net_value, pr.tax_rate_id, pr.invoice_number, pr.invoice_id, pr.received_by, pr.currency, pr.created, pr.createdby
 , pr.alteredby, pr.lastupdated
 , s.payee_name
 , c.name AS supplier
 , ph.order_number
 , (i.item_code::text || ' - '::text) || i.description::text AS stitem, i.qty_decimals
 , u.uom_name
   FROM po_receivedlines pr
   LEFT JOIN st_items i ON i.id = pr.stitem_id
   JOIN po_header ph ON ph.id = pr.order_id
   JOIN plmaster s ON s.id = pr.plmaster_id
   JOIN company c ON s.company_id = c.id
   JOIN st_uoms u ON u.id = pr.stuom_id;

ALTER TABLE po_receivedoverview OWNER TO "www-data";

INSERT INTO report_definitions
("name", definition, usercompanyid)
SELECT 'GRN-labels'
,'<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:fo="http://www.w3.org/1999/XSL/Format" version="1.0">
	<xsl:template match="/">
		<fo:root xmlns:fo="http://www.w3.org/1999/XSL/Format">
			<!-- define the various layouts -->
			<default-page-settings height="21cm" width="14.8cm"/>
			<fo:layout-master-set>
				<!-- layout for the all pages -->
				<fo:simple-page-master master-name="rest"
						page-height="21cm"
						page-width="14.8cm"
			                        margin="0.3cm">
					<fo:region-body margin-top="0.25cm" margin-bottom="0cm"/>
					<fo:region-before extent="0" />
				</fo:simple-page-master>
				<!-- layout for the last pages -->
				<fo:simple-page-master master-name="last"
						page-height="21cm"
						page-width="14.8cm"
			                        margin="0.3cm" >
					<fo:region-body margin-top="0.25cm" margin-bottom="0cm"/>
					<fo:region-before extent="0" />
					<fo:region-after extent="0" />
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
				<fo:static-content flow-name="xsl-region-before" >
					<fo:block font-size="8px" text-align="center">
					</fo:block>
				</fo:static-content>
				<!-- PAGE FOOTER -->
				<fo:static-content flow-name="xsl-region-after" >
					<fo:block font-size="8px" text-align="center">
					</fo:block>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body"  font-family="Helvetica">
					<xsl:for-each select="data/extra/GRN" >
						<fo:table table-layout="fixed" font-size="40px" space-before="0.25cm">
							<fo:table-column column-width="proportional-column-width(1" />
							<fo:table-column column-width="proportional-column-width(1)" />
							<fo:table-body>
								<fo:table-row font-weight="bold" border-top-width="1pt" border-top-style="solid" border-top-color="black"
														border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black"
														border-left-width="1pt" border-left-style="solid" border-left-color="black"
														border-right-width="1pt" border-right-style="solid" border-right-color="black">
									<fo:table-cell display-align="center"
												padding-top="0.5cm"  padding-bottom="0.25cm"
												border-left-width="1pt" border-left-style="solid" border-left-color="black"
												border-right-width="1pt" border-right-style="solid" border-right-color="black" >
										<fo:block text-align="center">
											<xsl:value-of select="item_type" />
										</fo:block>
									</fo:table-cell>
									<fo:table-cell display-align="center"
												padding-top="0.5cm"  padding-bottom="0.25cm"
												 border-left-width="1pt" border-left-style="solid" border-left-color="black"
												 border-right-width="1pt" border-right-style="solid" border-right-color="black" >
										<fo:block text-align="center">
											<xsl:value-of select="item_code" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row font-weight="bold" border-top-width="1pt" border-top-style="solid" border-top-color="black"
														border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black"
														border-left-width="1pt" border-left-style="solid" border-left-color="black"
														border-right-width="1pt" border-right-style="solid" border-right-color="black">
									<fo:table-cell display-align="center"
												padding-top="0.5cm"  padding-bottom="0.25cm"
												border-left-width="1pt" border-left-style="solid" border-left-color="black"
												border-right-width="1pt" border-right-style="solid" border-right-color="black" >
										<fo:block text-align="center">
											GRN
										</fo:block>
									</fo:table-cell>
									<fo:table-cell display-align="center"
												padding-top="0.5cm"  padding-bottom="0.25cm"
												border-left-width="1pt" border-left-style="solid" border-left-color="black"
												border-right-width="1pt" border-right-style="solid" border-right-color="black" >
										<fo:block text-align="center">
											<xsl:value-of select="gr_number" />
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
						<fo:table table-layout="fixed" font-size="40px" space-after="0.25cm">
							<fo:table-column column-width="proportional-column-width(1)" />
							<fo:table-body>
								<fo:table-row font-weight="bold" border-top-width="1pt" border-top-style="solid" border-top-color="black"
														border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black"
														border-left-width="1pt" border-left-style="solid" border-left-color="black"
														border-right-width="1pt" border-right-style="solid" border-right-color="black">
									<fo:table-cell display-align="center"
												padding-top="0.45cm"  padding-bottom="0.25cm" >
										<fo:block text-align="center">
											<xsl:value-of select="received_qty"/>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
								<fo:table-row font-weight="bold" border-top-width="1pt" border-top-style="solid" border-top-color="black"
														border-bottom-width="1pt" border-bottom-style="solid" border-bottom-color="black"
														border-left-width="1pt" border-left-style="solid" border-left-color="black"
														border-right-width="1pt" border-right-style="solid" border-right-color="black">
									<fo:table-cell display-align="center"
												padding-top="0.45cm"  padding-bottom="0.25cm" >
										<fo:block text-align="center">
											<xsl:value-of select="received_date"/>
										</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:for-each>
				</fo:flow>
			</fo:page-sequence>
		</fo:root>
	</xsl:template>
</xsl:stylesheet>' 
,id
FROM system_companies;