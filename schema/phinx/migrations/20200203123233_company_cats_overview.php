<?php


use UzerpPhinx\UzerpMigration;

/**
 * Add contact fields to Company Categories Overview
 */
class CompanyCatsOverview extends UzerpMigration
{
    public function up()
    {
        $view_name = 'companies_in_categories_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.companies_in_categories_overview
AS
SELECT cic.company_id,
    cic.category_id,
    cic.id,
    cic.created,
    cic.createdby,
    cic.alteredby,
    cic.lastupdated,
    cc.name AS category,
    c.name AS company,
    c.is_lead,
    c.accountnumber,
    c.phone,
    c.email,
    c.website,
    c.date_inactive
    FROM companies_in_categories cic
    JOIN contact_categories cc ON cc.id = cic.category_id
    JOIN companyoverview c ON c.id = cic.company_id
    WHERE c.is_lead is false;
VIEW;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'companies_in_categories_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.companies_in_categories_overview
AS
SELECT cic.company_id,
    cic.category_id,
    cic.id,
    cic.created,
    cic.createdby,
    cic.alteredby,
    cic.lastupdated,
    cc.name AS category,
    c.name AS company,
    c.is_lead
    FROM companies_in_categories cic
    JOIN contact_categories cc ON cc.id = cic.category_id
    JOIN company c ON c.id = cic.company_id;
VIEW;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name}  OWNER TO \"www-data\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
