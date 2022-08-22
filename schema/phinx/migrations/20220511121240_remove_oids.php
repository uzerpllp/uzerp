<?php


use UzerpPhinx\UzerpMigration;

/**
 * Remove unnecessary OIDS from tables
 * 
 */
class RemoveOids extends UzerpMigration
{
    protected $tables = [
        'public.activities',
        'public.activitytype',
        'public.campaigns',
        'public.campaignstatus',
        'public.campaigntype',
        'public.companies_in_categories',
        'public.company',
        'public.contact_categories',
        'public.countries',
        'public.lang',
        'public.ledger_categories',
        'public.opportunities',
        'public.opportunitysource',
        'public.opportunitystatus',
        'public.users'
    ];

    public function up()
    {
        foreach ($this->tables as $table) {
            $this->query("ALTER TABLE {$table} SET WITHOUT OIDS");
        }
    }
}
