ALTER TABLE holiday_entitlements ADD COLUMN created timestamp;
ALTER TABLE holiday_entitlements ALTER COLUMN created SET DEFAULT now();
ALTER TABLE holiday_entitlements ADD COLUMN createdby varchar;
ALTER TABLE holiday_entitlements ALTER COLUMN lastupdated type timestamp;
ALTER TABLE holiday_entitlements ADD COLUMN updatedby varchar;

ALTER TABLE holiday_requests ADD COLUMN createdby varchar;
ALTER TABLE holiday_requests ALTER COLUMN created type timestamp;
ALTER TABLE holiday_requests ADD COLUMN lastupdated timestamp;
ALTER TABLE holiday_requests ALTER COLUMN lastupdated SET DEFAULT now();
ALTER TABLE holiday_requests ADD COLUMN updatedby varchar;

DROP VIEW mf_outside_opsoverview;

ALTER TABLE mf_outside_ops ALTER COLUMN created SET NOT NULL;
ALTER TABLE mf_outside_ops ALTER COLUMN lastupdated type timestamp;

CREATE OR REPLACE VIEW mf_outside_opsoverview AS 
 SELECT o.id, o.op_no, o.start_date, o.end_date, o.stitem_id, o.description, o.std_osc, o.latest_osc, o.lastupdated, o.alteredby, o.usercompanyid, s.description AS stitem
   FROM mf_outside_ops o
   JOIN st_items s ON o.stitem_id = s.id;

ALTER TABLE mf_structures ALTER COLUMN created SET NOT NULL;
ALTER TABLE mf_structures ALTER COLUMN lastupdated type timestamp;

DROP VIEW po_no_auth_user;

DROP VIEW po_auth_requisitions;

DROP VIEW po_headeroverview;

ALTER TABLE po_header ALTER COLUMN created type timestamp;
ALTER TABLE po_header ALTER COLUMN created SET NOT NULL;
ALTER TABLE po_header ALTER COLUMN lastupdated type timestamp;

CREATE OR REPLACE VIEW po_headeroverview AS 
 SELECT po.id, po.order_number, po.plmaster_id, po.del_address_id, po.order_date, po.due_date, po.ext_reference, po.currency_id, po.rate, po.net_value, po.twin_currency_id, po.twin_rate, po.twin_net_value, po.base_net_value, po."type", po.status, po.description, po.usercompanyid, po.date_authorised, po.raised_by, po.authorised_by, po.created, po."owner", po.lastupdated, po.alteredby, plm.name AS supplier, cum.currency, twc.currency AS twin_currency, pr.username AS raised_by_person, pa.username AS authorised_by_person, p.name AS project
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id;

CREATE OR REPLACE VIEW po_auth_requisitions AS 
 SELECT h.id, a.order_number, h.status, a.username, h.order_date, h.due_date, h.supplier
   FROM ( SELECT o.order_number, a.username, count(*) AS authlines
           FROM po_linesum o
      JOIN po_authlist a ON a.glaccount_id = o.glaccount_id AND a.glcentre_id = o.glcentre_id
     WHERE o.value <= a.order_limit AND o."type"::text = 'R'::text
     GROUP BY o.order_number, a.username) a
   JOIN ( SELECT o.order_number, count(*) AS totallines
           FROM po_linesum o
          GROUP BY o.order_number) b ON a.order_number = b.order_number
   JOIN po_headeroverview h ON h.order_number = a.order_number
  WHERE a.authlines = b.totallines;

CREATE OR REPLACE VIEW po_no_auth_user AS 
 SELECT h.id, h.order_number, h.order_date, h.due_date, h.supplier, h.status
   FROM po_headeroverview h
   LEFT JOIN po_auth_requisitions a ON a.order_number = h.order_number
  WHERE h."type"::text = 'R'::text AND a.username IS NULL;

DROP VIEW st_costsoverview;

ALTER TABLE st_costs ALTER COLUMN created SET NOT NULL;
ALTER TABLE st_costs ALTER COLUMN lastupdated type timestamp;

CREATE OR REPLACE VIEW st_costsoverview AS 
 SELECT c.id, c.stitem_id, c."type", c.cost, c.mat, c.lab, c.osc, c.ohd, c.effect_on_stock, c.lastupdated, c.alteredby, c.usercompanyid, (i.item_code::text || ' - '::text) || i.description::text AS stitem
   FROM st_costs c
   JOIN st_items i ON c.stitem_id = i.id;

ALTER TABLE taxrates ALTER COLUMN created SET NOT NULL;
ALTER TABLE taxrates ALTER COLUMN lastupdated type timestamp;
