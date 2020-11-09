<?php


use UzerpPhinx\UzerpMigration;

/**
 * Phinx Migration - Add postponed vat accounting to VAT status
 *
 * @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 * @license GPLv3 or later
 * @copyright (c) 2020 uzERP LLP (support#uzerp.com). All rights reserved.
 */
class AddPvaToTaxStatus extends UzerpMigration
{
    public function change()
    {
        $table = $this->table('tax_statuses');
        $table->addColumn('postponed_vat_accounting', 'boolean', ['default' => false, 'null' => true])
            ->addColumn('reverse_charge', 'boolean', ['default' => false, 'null' => true])
            ->save();
        
        // Prevent eu_tax and postponed_vat_accounting both being true
        $this->query("ALTER TABLE public.tax_statuses DROP CONSTRAINT IF EXISTS exclusive_eu_pva");
        $this->query("ALTER TABLE public.tax_statuses DROP CONSTRAINT IF EXISTS exclusive_eu_rc");
        $this->query("ALTER TABLE public.tax_statuses DROP CONSTRAINT IF EXISTS exclusive_eu");
        $this->query("ALTER TABLE public.tax_statuses
                        ADD CONSTRAINT exclusive_eu CHECK ((eu_tax IS TRUE AND postponed_vat_accounting IS FALSE AND reverse_charge IS FALSE)
                        OR (eu_tax is FALSE AND postponed_vat_accounting IS TRUE AND reverse_charge IS FALSE)
                        OR (eu_tax is FALSE AND postponed_vat_accounting IS FALSE AND reverse_charge IS TRUE)
                        OR (eu_tax IS FALSE AND postponed_vat_accounting IS FALSE AND reverse_charge IS FALSE))
                        NOT VALID;");
    }
}
