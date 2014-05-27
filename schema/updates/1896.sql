--
-- $Revision: 1.12 $
--

ALTER TABLE expenses_lines ALTER COLUMN tax_rate_id SET NOT NULL;

DROP VIEW expenses_overview;

DROP TABLE expenses;

-- View: expenses_header_overview

DROP VIEW expenses_header_overview;

CREATE OR REPLACE VIEW expenses_header_overview AS 
 SELECT eh.id, eh.expense_number, eh.our_reference, eh.employee_id, eh.expense_date, eh.currency_id, eh.rate, eh.gross_value
 , eh.tax_value, eh.net_value, eh.twin_currency_id, eh.twin_rate, eh.twin_gross_value, eh.twin_tax_value, eh.twin_net_value
 , eh.base_gross_value, eh.base_tax_value, eh.base_net_value, eh.status, eh.description, eh.authorised_date
 , eh.authorised_by, eh.created, eh.createdby, eh.alteredby, eh.lastupdated, eh.usercompanyid, eh.project_id, eh.task_id
 , (p.firstname::text || ' '::text) || p.surname::text AS employee, cum.currency, twc.currency AS twin
   FROM expenses_header eh
   JOIN employees e ON eh.employee_id = e.id
   JOIN person p ON e.person_id = p.id
   JOIN cumaster cum ON eh.currency_id = cum.id
   JOIN cumaster twc ON eh.twin_currency_id = twc.id;

ALTER TABLE expenses_header_overview OWNER TO "www-data";

--
-- Report Definitions
--

INSERT INTO report_definitions
(name, definition, usercompanyid)
SELECT 'expenses',
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
					<!-- Expense title -->
					<fo:block-container absolute-position="absolute" left="0" top="2.7cm" width="21cm" text-align="center" font-size="14pt" font-weight="bold">
						<fo:block>Expense Claim Form</fo:block>
					</fo:block-container>
					<!-- list employee address -->
					<fo:block-container 
						<!--[address_block_position]--> >
						<xsl:for-each select="data/extra/employee_address/*" >
							<fo:block><xsl:value-of select="."/></fo:block>
						</xsl:for-each>
					</fo:block-container>
					<!-- list delivery details -->
					<fo:block-container absolute-position="absolute" right="0" top="3.7cm" width="7cm">	
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
										<fo:block><xsl:value-of select="data/Expense/expense_date" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Number</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/Expense/expense_number" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Ref</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/Expense/our_reference" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Authorised Date</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/Expense/authorised_date" /></fo:block>								</fo:table-cell>
								</fo:table-row>
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block>Authorised By</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/extra/authoriser" /></fo:block>								</fo:table-cell>
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
												<fo:block font-weight="bold">Currency</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
												<fo:block font-weight="bold">Rate %</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
												<fo:block font-weight="bold">Net Amount</fo:block>
											</fo:table-cell>
											<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
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
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
													<fo:block><xsl:value-of select="tax_rate" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
													<fo:block><xsl:value-of select="net_value" /></fo:block>
												</fo:table-cell>
												<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
													<fo:block><xsl:value-of select="tax_value" /></fo:block>
												</fo:table-cell>
											</fo:table-row>
										</xsl:for-each>
									</fo:table-body>
								</fo:table>
							</xsl:otherwise>
						</xsl:choose>
					</fo:block-container>
					<!-- totals table -->
					<fo:block-container absolute-position="absolute" right="0" top="3mm" width="9cm">	
						<fo:table table-layout="fixed" >
							<fo:table-column column-width="proportional-column-width(60)" />
							<fo:table-column column-width="proportional-column-width(40)" />
							<fo:table-body>
								<xsl:for-each select="data/extra/expense_totals/line">
									<fo:table-row>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="label" /></fo:block>
										</fo:table-cell>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block text-align="right"><xsl:value-of select="value" /></fo:block>
										</fo:table-cell>
									</fo:table-row>
								</xsl:for-each>
							</fo:table-body>
						</fo:table>
					<!-- Final Instructions! -->
						<fo:block padding="2mm" font-size="10pt" font-weight="bold">Attach all relevant receipts and hand to HR</fo:block>
					</fo:block-container>
				</fo:static-content>
				<!-- BODY -->
				<fo:flow flow-name="xsl-region-body"  font-family="Helvetica">
					<xsl:if test="data/Expense/description != ''''">
						<fo:table table-layout="fixed" font-size="8px" margin-bottom="3mm">
							<fo:table-column column-width="proportional-column-width(1)" />
							<fo:table-header>
								<fo:table-row>
									<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">Description</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<fo:table-row>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="data/Expense/description" /></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-body>
						</fo:table>
					</xsl:if>
					<!-- Expense title -->
					<fo:block-container text-align="center" font-size="11pt" font-weight="bold">
						<fo:block>Expenses Details</fo:block>
					</fo:block-container>
					<fo:table table-layout="fixed" font-size="8px" margin-bottom="3mm">
						<fo:table-column column-width="proportional-column-width(66)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-column column-width="proportional-column-width(11)" />
						<fo:table-header>
							<fo:table-row>
								<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Description</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
									<fo:block font-weight="bold">Qty</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">Price</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">Net Amount</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">VAT Amount</fo:block>
								</fo:table-cell>
								<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
									<fo:block font-weight="bold">Gross Amount</fo:block>
								</fo:table-cell>
							</fo:table-row>
						</fo:table-header>
						<fo:table-body>
							<xsl:for-each select="data/Expense/ExpenseLine" >
								<fo:table-row>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="item_description" /></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
										<fo:block><xsl:value-of select="qty"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="purchase_price"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="net_value"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="tax_value"/></fo:block>
									</fo:table-cell>
									<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block><xsl:value-of select="gross_value"/></fo:block>
									</fo:table-cell>
								</fo:table-row>
							</xsl:for-each>
						</fo:table-body>
					</fo:table>
					<!-- GL Account Analysis table -->
					<fo:block-container text-align="center" font-size="11pt" font-weight="bold">
						<fo:block>Expenses Analysis</fo:block>
					</fo:block-container>
						<fo:table table-layout="fixed" font-size="8px">
							<fo:table-column column-width="proportional-column-width(75)" />
							<fo:table-column column-width="proportional-column-width(25)" />
							<fo:table-header>
								<fo:table-row>
									<xsl:attribute name="background-color"><!--[table_header_colour]--></xsl:attribute>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> >
										<fo:block font-weight="bold">GL Account Code</fo:block>
									</fo:table-cell>
									<fo:table-cell padding="1mm" <!--[table_cell_borders]--> text-align="right" >
										<fo:block font-weight="bold">Net Amount</fo:block>
									</fo:table-cell>
								</fo:table-row>
							</fo:table-header>
							<fo:table-body>
								<xsl:for-each select="data/extra/analysis/line">
									<fo:table-row>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block><xsl:value-of select="account" /></fo:block>
										</fo:table-cell>
										<fo:table-cell padding="0.5mm" <!--[table_cell_borders]--> >
											<fo:block text-align="right"><xsl:value-of select="value" /></fo:block>
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
</xsl:stylesheet>',
  id
  FROM system_companies;

--
-- Module Components
--

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'hrcontroller', 'C', location||'/controllers/HrController.php', id, 'HR Parent Controller'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
 (name, "type", location, module_id, title)
 SELECT 'expensesearch', 'M', location||'/models/expenseSearch.php', id, 'Expense Search'
   FROM modules m
  WHERE name = 'hr';

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'expenseswaitingauthuzlet', 'E', "location"||'/uzlets/ExpensesWaitingAuthUZlet.php', id
  FROM modules
 WHERE "name" = 'hr';

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'expenseswaitingpaymentuzlet', 'E', "location"||'/uzlets/ExpensesWaitingPaymentUZlet.php', id
  FROM modules
 WHERE "name" = 'hr';

INSERT INTO module_components
(
  "name",
  "type",
  "location",
  module_id
)
SELECT 'holidayswaitingauthuzlet', 'E', "location"||'/uzlets/HolidaysWaitingAuthUZlet.php', id
  FROM modules
 WHERE "name" = 'hr';

--
-- UZlets
--

INSERT INTO uzlets
(
  "name",
  title,
  preset,
  enabled,
  dashboard,
  usercompanyid
)
SELECT 'ExpensesWaitingAuthUZlet', 'Expenses Awaiting Authorisation', false, true, true, id
  FROM system_companies;

INSERT INTO uzlet_modules
(
  uzlet_id,
  module_id,
  usercompanyid
)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
    , modules m
 WHERE u.name='ExpensesWaitingAuthUZlet'
   AND m.name='hr';

INSERT INTO uzlets
(
  "name",
  title,
  preset,
  enabled,
  dashboard,
  usercompanyid
)
SELECT 'ExpensesWaitingPaymentUZlet', 'Expenses Awaiting Payment', false, true, true, id
  FROM system_companies;

INSERT INTO uzlet_modules
(
  uzlet_id,
  module_id,
  usercompanyid
)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
    , modules m
 WHERE u.name='ExpensesWaitingPaymentUZlet'
   AND m.name='hr';

INSERT INTO uzlets
(
  "name",
  title,
  preset,
  enabled,
  dashboard,
  usercompanyid
)
SELECT 'HolidaysWaitingAuthUZlet', 'Holidays Awaiting Authorisation', false, true, true, id
  FROM system_companies;

INSERT INTO uzlet_modules
(
  uzlet_id,
  module_id,
  usercompanyid
)
SELECT u.id, m.id, u.usercompanyid
  FROM uzlets u
    , modules m
 WHERE u.name='HolidaysWaitingAuthUZlet'
   AND m.name='hr';

--
-- Permissions
--

update permissions a
   set parent_id = (select c.parent_id
                      from permissions c
                      join permissions m on m.id = c.parent_id
                                        and m.type = 'm'
                                        and m.permission = 'hr'
                                        and m.parent_id is not null
                     where c.id = a.parent_id)
 where a.type = 'a'
   and a.permission = 'view_my_expenses'
   and exists (select 1
                 from permissions c
                 join permissions m on m.id = c.parent_id
                                   and m.type = 'm'
                                   and m.permission = 'hr'
                                   and m.parent_id is not null
                 where c.id = a.parent_id
                   and c.permission = 'expenses');

update permissions a
   set position = 1
     , parent_id = (select c.id
                      from permissions c
                      join permissions m on m.id = c.parent_id
                                        and m.type = 'm'
                                        and m.permission = 'hr'
                                        and m.parent_id is not null
                     where c.type = 'a'
                       and c.permission = 'view_my_expenses')
 where a.type = 'a'
   and a.permission = 'new_expense'
   and exists (select 1
                 from permissions c
                 join permissions m on m.id = c.parent_id
                                   and m.type = 'm'
                                   and m.permission = 'hr'
                                   and m.parent_id is not null
                 where c.id = a.parent_id
                   and c.permission = 'expenses');

update permissions a
   set position = 1
 where a.type = 'c'
   and a.permission = 'holidayrequests'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = a.parent_id);

update permissions a
   set position = 2
 where a.type = 'a'
   and a.permission = 'view_my_expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = a.parent_id);

update permissions a
   set position = 3
     , display = false
 where a.type = 'c'
   and a.permission = 'expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = a.parent_id);

update permissions a
   set position = 4
 where a.type = 'c'
   and a.permission = 'hours'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = a.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'view', 'a', 'View Expense Detail', false, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 1) as position
          from permissions a
          join permissions c on a.parent_id = c.id
	                    and c.type = 'c'
                            and c.permission='expenses'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='hr'
                            and m.parent_id is not null
         where a.type = 'a') next
 where type='c'
   and permission='expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = c.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'save', 'a', 'Save Expense', false, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 0)+1 as position
          from permissions a
          join permissions c on a.parent_id = c.id
	                    and c.type = 'c'
                            and c.permission='expenses'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='hr'
                            and m.parent_id is not null
         where a.type = 'a') next
 where type='c'
   and permission='expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = c.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'print_expense', 'a', 'Print Expense Form', false, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 0)+1 as position
          from permissions a
          join permissions c on a.parent_id = c.id
	                    and c.type = 'c'
                            and c.permission='expenses'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='hr'
                            and m.parent_id is not null
         where a.type = 'a') next
 where type='c'
   and permission='expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is not null
                  and m.id = c.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'post', 'a', 'Post to Ledger', false, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 0)+1 as position
          from permissions a
          join permissions c on a.parent_id = c.id
	                    and c.type = 'c'
                            and c.permission='expenses'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='hr'
                            and m.parent_id is null
         where a.type = 'a') next
 where type='c'
   and permission='expenses'
   and exists (select 1
                 from permissions m
                where m.type = 'm'
                  and m.permission = 'hr'
                  and m.parent_id is null
                  and m.id = c.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'uzlet_setup', 'm', 'uzLET Setup', true, c.id, next.position
  from permissions c
     , (select coalesce(max(c.position), 0)+1 as position
          from permissions c
          join permissions m on c.parent_id = m.id
                            and m.type = 'g'
                            and m.permission='systemsetup'
                            and m.parent_id is null
         where c.type = 'c') next
 where c.type='g'
   and c.permission='systemsetup'
   and not exists (select 1
                    from permissions m
                   where m.type = 'm'
                     and m.permission = 'uzlet_setup'
                     and c.id = m.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'uzlets', 'c', 'uzLETs', true, c.id, next.position
  from permissions c
     , (select coalesce(max(c.position), 0)+1 as position
          from permissions c
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='uzlet_setup'
          join permissions g on m.parent_id = g.id
                            and g.type = 'g'
                            and g.permission='systemsetup'
                            and g.parent_id is null
         where c.type = 'c') next
 where c.type='m'
   and c.permission='uzlet_setup'
   and not exists (select 1
                    from permissions m
                   where m.type = 'c'
                     and m.permission = 'uzlets'
                     and c.id = m.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select '_new', 'a', 'New uzLET', true, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 0)+1 as position
          from permissions a
          join permissions c on a.parent_id = c.id
                            and c.type = 'c'
                            and c.permission='uzlets'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='uzlet_setup'
          join permissions g on m.parent_id = g.id
                            and g.type = 'g'
                            and g.permission='systemsetup'
                            and g.parent_id is null
         where c.type = 'c') next
 where c.type='c'
   and c.permission='uzlets'
   and not exists (select 1
                    from permissions m
                   where m.type = 'a'
                     and m.permission = '_new'
                     and c.id = m.parent_id);

insert into permissions
(permission, type, title, display, parent_id, position)
select 'edit', 'a', 'Edit uzLET', true, c.id, next.position
  from permissions c
     , (select coalesce(max(a.position), 0)+1 as position
          from permissions a
          join permissions c on a.parent_id = c.id
                            and c.type = 'c'
                            and c.permission='uzlets'
          join permissions m on c.parent_id = m.id
                            and m.type = 'm'
                            and m.permission='uzlet_setup'
          join permissions g on m.parent_id = g.id
                            and g.type = 'g'
                            and g.permission='systemsetup'
                            and g.parent_id is null
         where c.type = 'c') next
 where c.type='c'
   and c.permission='uzlets'
   and not exists (select 1
                    from permissions m
                   where m.type = 'a'
                     and m.permission = 'edit'
                     and c.id = m.parent_id);

update permissions c
   set position = position+1
 where position >= 7
   and exists (select 1
                 from permissions m
                where m.id = c.parent_id
                  and m.permission = 'systemsetup'
                  and m.type = 'g');

update permissions c
   set position = 7
 where type='m'
   and permission = 'uzlet_setup'
   and exists (select 1
                 from permissions m
                where m.id = c.parent_id
                  and m.permission = 'systemsetup'
                  and m.type = 'g');
