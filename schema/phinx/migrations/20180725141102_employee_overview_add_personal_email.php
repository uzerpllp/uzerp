<?php

use UzerpPhinx\UzerpMigration;

class EmployeeOverviewAddPersonalEmail extends UzerpMigration
{
    /**
     * Add personal email id to employee overview to link back to party contact
     */
    public function up()
    {
        $view_name = 'employeeoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
        CREATE OR REPLACE VIEW employeeoverview AS 
        SELECT ee.id,
           ee.person_id,
           ee.employee_number,
           ee.next_of_kin,
           ee.nok_address,
           ee.nok_phone,
           ee.nok_relationship,
           ee.bank_name,
           ee.bank_address,
           ee.bank_account_name,
           ee.bank_account_number,
           ee.bank_sort_code,
           ee.start_date,
           ee.finished_date,
           ee.created,
           ee.lastupdated,
           ee.alteredby,
           ee.usercompanyid,
           ee.ni,
           ee.dob,
           ee.expenses_balance,
           ee.works_number,
           ee.employee_grade_id,
           ee.pay_frequency_id,
           ee.address_id,
           ee.contact_phone_id,
           ee.contact_mobile_id,
           ee.contact_email_id,
           ee.mfdept_id,
           epf.name AS pay_frequency,
           (p.surname::text || ' '::text) || p.firstname::text AS employee,
           p.reports_to,
           p.department,
           (eg.name::text || ' - '::text) || eg.description::text AS employee_grade
          FROM employees ee
            JOIN person p ON p.id = ee.person_id
            JOIN employee_pay_frequencies epf ON epf.id = ee.pay_frequency_id
            LEFT JOIN employee_grades eg ON eg.id = ee.employee_grade_id;
VIEW_WRAP;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
