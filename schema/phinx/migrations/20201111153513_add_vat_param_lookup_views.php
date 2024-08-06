<?php


use UzerpPhinx\UzerpMigration;


/**
 * Phinx Migration - Add utility views to lookup VAT GL Params
 * 
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddVatParamLookupViews extends UzerpMigration
{
    public function up()
    {
        $view_name = ['glparams_vat_pv', 'glparams_vat_rc'];
        $view_owner = 'www-data';
        $view1 = <<<'VIEW_WRAP'
    CREATE OR REPLACE VIEW glparams_vat_pv AS
    SELECT p.paramvalue_id,
        p.usercompanyid
        FROM gl_params p
        WHERE p.paramdesc::text = 'VAT Postponed Account'::text;

VIEW_WRAP;
    
    $view2 = <<<'VIEW_WRAP'
    CREATE OR REPLACE VIEW glparams_vat_rc AS
    SELECT p.paramvalue_id,
        p.usercompanyid
        FROM gl_params p
        WHERE p.paramdesc::text = 'VAT Reverse Charge Account'::text;
VIEW_WRAP;

        $this->query($view1);
        $this->query($view2);
        foreach ($view_name as $view_n) {
            $this->query("ALTER TABLE {$view_n} OWNER TO \"{$view_owner}\"");
        }
    }
}
