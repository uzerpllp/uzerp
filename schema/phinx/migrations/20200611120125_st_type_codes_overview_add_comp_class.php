<?php

use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add comp_class and active columns to st_typecodes_overview view
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class StTypeCodesOverviewAddCompClass extends UzerpMigration
{
    public function up()
    {
        $view_name = 'st_typecodes_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.st_typecodes_overview
AS
SELECT tc.id,
tc.type_code,
tc.description,
tc.usercompanyid,
tc.backflush_action_id,
tc.complete_action_id,
tc.issue_action_id,
tc.comp_class,
tc.active,
tc.created,
tc.createdby,
tc.alteredby,
tc.lastupdated,
(a1.action_name::text || ' - '::text) || a1.description::text AS backflush_action,
(a2.action_name::text || ' - '::text) || a2.description::text AS complete_action,
(a3.action_name::text || ' - '::text) || a3.description::text AS issue_action,
(a4.action_name::text || ' - '::text) || a4.description::text AS return_action
FROM st_typecodes tc
    LEFT JOIN wh_actions a1 ON a1.id = tc.backflush_action_id
    LEFT JOIN wh_actions a2 ON a2.id = tc.complete_action_id
    LEFT JOIN wh_actions a3 ON a3.id = tc.issue_action_id
    LEFT JOIN wh_actions a4 ON a4.id = tc.return_action_id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }

    public function down()
    {
        $view_name = 'st_typecodes_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW public.st_typecodes_overview
AS
SELECT tc.id,
tc.type_code,
tc.description,
tc.usercompanyid,
tc.backflush_action_id,
tc.complete_action_id,
tc.issue_action_id,
tc.created,
tc.createdby,
tc.alteredby,
tc.lastupdated,
(a1.action_name::text || ' - '::text) || a1.description::text AS backflush_action,
(a2.action_name::text || ' - '::text) || a2.description::text AS complete_action,
(a3.action_name::text || ' - '::text) || a3.description::text AS issue_action
FROM st_typecodes tc
    LEFT JOIN wh_actions a1 ON a1.id = tc.backflush_action_id
    LEFT JOIN wh_actions a2 ON a2.id = tc.complete_action_id
    LEFT JOIN wh_actions a3 ON a3.id = tc.issue_action_id;
VIEW;
        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
