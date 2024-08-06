<?php

use UzerpPhinx\UzerpMigration;

class PoPlannedOverview extends UzerpMigration
{
    public function up()
    {
        $view_name = 'po_plannedoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.po_plannedoverview AS 
SELECT
po_planned.id,
po_planned.item_code AS stitem,
po_planned.description,
( SELECT sti.id
    FROM st_items sti
    WHERE sti.item_code::text = po_planned.item_code::text) AS stitem_id,
po_planned.supplier_name,
( SELECT p.id
    FROM plmaster p
        JOIN company c ON c.id = p.company_id
    WHERE c.name::text = po_planned.supplier_name::text) AS plmaster_id,
po_planned.order_date,
po_planned.delivery_date,
po_planned.qty,
po_planned.product_group_desc
FROM po_planned;
VIEW_WRAP;

        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
