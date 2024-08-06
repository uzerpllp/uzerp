<?php


use UzerpPhinx\UzerpMigration;

class ItemAvailabilityAddSoType extends UzerpMigration
{
    private $view_name = 'public.so_items';

    public function up()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.so_items AS
SELECT sl.stitem_id,
    sl.usercompanyid,
    u.uom_name,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    i.prod_group_id,
    sum(sl.revised_qty) AS required,
    sh.type
    FROM so_lines sl
    JOIN st_items i ON i.id = sl.stitem_id
    JOIN st_uoms u ON sl.stuom_id = u.id
    JOIN so_header sh on sh.id = sl.order_id
    WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::text, 'R'::text, 'S'::text]))
    GROUP BY sl.stitem_id, ((i.item_code::text || ' - '::text) || i.description::text), u.uom_name, i.prod_group_id, sl.usercompanyid, sh.type;
VIEW_WRAP;
    $this->query("select deps_save_and_drop_dependencies('public', '{$this->view_name}')");
    $this->query("DROP VIEW {$this->view_name}");
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    $this->query("select deps_restore_dependencies('public', '{$this->view_name}')");
    }

    public function down()
    {
        $view_owner = 'www-data';
        $view = <<<'VIEW_WRAP'
CREATE OR REPLACE VIEW public.so_items AS
SELECT sl.stitem_id,
    sl.usercompanyid,
    u.uom_name,
    (i.item_code::text || ' - '::text) || i.description::text AS stitem,
    i.prod_group_id,
    sum(sl.revised_qty) AS required
    FROM so_lines sl
    JOIN st_items i ON i.id = sl.stitem_id
    JOIN st_uoms u ON sl.stuom_id = u.id
    WHERE sl.stitem_id IS NOT NULL AND (sl.status::text = ANY (ARRAY['N'::text, 'R'::text, 'S'::text]))
    GROUP BY sl.stitem_id, ((i.item_code::text || ' - '::text) || i.description::text), u.uom_name, i.prod_group_id, sl.usercompanyid;
VIEW_WRAP;
    $this->query("select deps_save_and_drop_dependencies('public', '{$this->view_name}')");
    $this->query("DROP VIEW {$this->view_name}");
    $this->query($view);
    $this->query("ALTER TABLE {$this->view_name} OWNER TO \"{$view_owner}\"");
    $this->query("select deps_restore_dependencies('public', '{$this->view_name}')");
    }
}
