<?php
declare(strict_types=1);

use UzerpPhinx\UzerpMigration;

final class CrmAsctivitiesOverviewAddText1 extends UzerpMigration
{
    public function up()
    {
        $view_name = 'activitiesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
        CREATE OR REPLACE VIEW public.activitiesoverview
        AS
            SELECT activities.id,
            activities.type_id,
            activities.owner,
            activities.company_id,
            activities.person_id,
            activities.opportunity_id,
            activities.name,
            activities.description,
            activities.startdate,
            activities.enddate,
            activities.completed,
            activities.usercompanyid,
            activities.duration,
            activities.created,
            activities.alteredby,
            activities.lastupdated,
            activities.campaign_id,
            activities.assigned,
            activities.text1,
            activitytype.name AS type,
            opportunities.name AS opportunity,
            campaigns.name AS campaign,
            company.name AS company,
            (person.firstname::text || ' '::text) || person.surname::text AS person
            FROM activities
                LEFT JOIN activitytype ON activities.type_id = activitytype.id
                LEFT JOIN opportunities ON activities.opportunity_id = opportunities.id
                LEFT JOIN campaigns ON activities.campaign_id = campaigns.id
                LEFT JOIN company ON activities.company_id = company.id
                LEFT JOIN person ON activities.person_id = person.id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'activitiesoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
        CREATE OR REPLACE VIEW public.activitiesoverview
        AS
            SELECT activities.id,
            activities.type_id,
            activities.owner,
            activities.company_id,
            activities.person_id,
            activities.opportunity_id,
            activities.name,
            activities.description,
            activities.startdate,
            activities.enddate,
            activities.completed,
            activities.usercompanyid,
            activities.duration,
            activities.created,
            activities.alteredby,
            activities.lastupdated,
            activities.campaign_id,
            activities.assigned,
            activitytype.name AS type,
            opportunities.name AS opportunity,
            campaigns.name AS campaign,
            company.name AS company,
            (person.firstname::text || ' '::text) || person.surname::text AS person
            FROM activities
                LEFT JOIN activitytype ON activities.type_id = activitytype.id
                LEFT JOIN opportunities ON activities.opportunity_id = opportunities.id
                LEFT JOIN campaigns ON activities.campaign_id = campaigns.id
                LEFT JOIN company ON activities.company_id = company.id
                LEFT JOIN person ON activities.person_id = person.id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
