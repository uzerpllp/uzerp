<?php

use UzerpPhinx\UzerpMigration;

class SlAgedDebtorsOverviewValue extends UzerpMigration
{
    /**
     * Modify sl_aged_debtors_overview to use currency
     * instead of base outstanding value.
     */
    public function up()
    {
        $view_name = 'sl_aged_debtors_overview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW sl_aged_debtors_overview AS
 SELECT (((s.slmaster_id::text || '-'::text) || s.our_reference::text) || '-'::text) || s.transaction_type::text AS id,
    s.slmaster_id,
    s.customer,
    date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)) AS age,
    s.usercompanyid,
    sum(s.os_value) AS value,
    s.our_reference,
    s.transaction_type
   FROM sltransactionsoverview s
  WHERE s.status::text <> 'P'::text
  GROUP BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone,
    s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone)),
    s.usercompanyid, s.our_reference, s.transaction_type
  ORDER BY s.customer, s.slmaster_id, date_part('year'::text, age(('now'::text::date - 1)::timestamp with time zone,
    s.transaction_date::timestamp with time zone)) * 12::double precision + date_part('month'::text, age(('now'::text::date - 1)::timestamp with time zone, s.transaction_date::timestamp with time zone));
VIEW;

        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
    }
}
