ALTER TABLE po_auth_limits DROP CONSTRAINT po_auth_limits_person_id_fkey;

DROP VIEW po_authlimitsoverview;

ALTER TABLE po_auth_limits 
  RENAME person_id TO username;

ALTER TABLE po_auth_limits 
  ALTER COLUMN username TYPE VARCHAR;

CREATE OR REPLACE VIEW po_authlimitsoverview AS 
 SELECT al.id, al.username, al.glcentre_id, al.order_limit, al.usercompanyid, (c.cost_centre::text || ' - '::text) || c.description::text AS cost_centre
   FROM po_auth_limits al
   LEFT JOIN gl_centres c ON al.glcentre_id = c.id;

UPDATE po_auth_limits
   SET username = (SELECT username
                     FROM users u
                    WHERE u.person_id=po_auth_limits.username);

ALTER TABLE po_auth_limits
  ADD CONSTRAINT po_auth_limits_username_fkey FOREIGN KEY (username)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE po_header DROP CONSTRAINT po_header_raised_by_fkey;

ALTER TABLE po_header DROP CONSTRAINT po_header_authorised_by_fkey;

DROP VIEW po_headeroverview;

ALTER TABLE po_header 
  ALTER COLUMN raised_by TYPE VARCHAR;

ALTER TABLE po_header 
  ALTER COLUMN authorised_by TYPE VARCHAR;

UPDATE po_header
   SET raised_by = (SELECT username
                     FROM users u
                    WHERE u.person_id=po_header.raised_by);

UPDATE po_header
   SET authorised_by = (SELECT username
                          FROM users u
                         WHERE u.person_id=po_header.authorised_by);

ALTER TABLE po_header
  ADD CONSTRAINT po_header_raised_by_fkey FOREIGN KEY (raised_by)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE po_header
  ADD CONSTRAINT po_header_authorised_by_fkey FOREIGN KEY (authorised_by)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;DROP VIEW po_authlimitsoverview;

CREATE OR REPLACE VIEW po_headeroverview AS 
 SELECT po.id, po.order_number, po.plmaster_id, po.del_address_id, po.order_date, po.due_date, po.ext_reference, po.currency_id, po.rate, po.net_value, po.twin_currency_id, po.twin_rate, po.twin_net_value, po.base_net_value, po."type", po.status, po.description, po.usercompanyid, po.date_authorised, po.raised_by, po.authorised_by, po.created, po."owner", po.lastupdated, po.alteredby, plm.name AS supplier, cum.currency, twc.currency AS twin_currency, pr.username AS raised_by_person, pa.username AS authorised_by_person, p.name AS project
   FROM po_header po
   JOIN plmaster plm ON po.plmaster_id = plm.id
   JOIN cumaster cum ON po.currency_id = cum.id
   JOIN cumaster twc ON po.twin_currency_id = twc.id
   JOIN users pr ON po.raised_by::text = pr.username::text
   LEFT JOIN users pa ON po.authorised_by::text = pa.username::text
   LEFT JOIN projects p ON po.project_id = p.id;

CREATE TABLE po_awaiting_auth
(
  id bigserial NOT NULL,
  order_id int8 NOT NULL,
  username varchar NOT NULL,
  CONSTRAINT po_awaiting_auth_pkey PRIMARY KEY (id),
  CONSTRAINT po_awaiting_auth_order_id_fkey FOREIGN KEY (order_id)
      REFERENCES po_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT po_awaiting_auth_username_fkey FOREIGN KEY (username)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION
);

CREATE OR REPLACE VIEW po_authlist AS 
 SELECT al.id, al.username, al.glcentre_id, al.order_limit, al.usercompanyid
, ac.glaccount_id
   FROM po_auth_limits al
   JOIN po_auth_accounts ac ON al.id = ac.po_auth_limit_id;