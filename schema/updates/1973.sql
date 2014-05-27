--
-- $Revision: 1.1 $
--

-- View: employee_pay_history_overview

DROP VIEW employee_pay_history_overview;

CREATE OR REPLACE VIEW employee_pay_history_overview AS 
 SELECT eph.id, eph.employee_id, eph.employee_pay_periods_id, eph.hours_type_id, eph.pay_rate, eph.pay_units, eph.created
 , eph.createdby, eph.lastupdated, eph.alteredby, eph.usercompanyid, eph.payment_type_id, eph.pay_frequency_id, eph.comment
 , eph.pay_rate * eph.pay_units AS pay_value
 , epp.period_start_date, epp.period_end_date, epp.pay_basis, epp.tax_year
 , epp.tax_month, epp.tax_week, epp.tax_year ||' '|| epp.tax_week ||' '|| epp.pay_basis as employee_pay_period, epp.calendar_week, epp.processed_period, epp.processed_date
 , epf.name AS pay_frequency, ht.name AS hours_type
 , ept.name AS payment_type, ept.allow_zero_units, eo.employee
   FROM employee_pay_history eph
   JOIN employee_pay_periods epp ON epp.id = eph.employee_pay_periods_id
   JOIN employeeoverview eo ON eo.id = eph.employee_id
   JOIN employee_pay_frequencies epf ON epf.id = eph.pay_frequency_id
   LEFT JOIN hour_types ht ON ht.id = eph.hours_type_id
   LEFT JOIN employee_payment_types ept ON ept.id = eph.payment_type_id;

ALTER TABLE employee_pay_history_overview OWNER TO "www-data";

