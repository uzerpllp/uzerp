--
-- $Revision: 1.1 $
--

-- Table: po_header

ALTER TABLE po_header
  ADD CONSTRAINT po_header_owner_fkey FOREIGN KEY ("owner")
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

-- Table: objectroles

ALTER TABLE objectroles DROP CONSTRAINT objectroles_pkey;

ALTER TABLE objectroles
  ADD CONSTRAINT objectroles_pkey PRIMARY KEY(id);

ALTER TABLE objectroles ALTER COLUMN role_id DROP NOT NULL;

ALTER TABLE objectroles
  ADD CONSTRAINT objectroles_unq1 UNIQUE(object_id, object_type, role_id);

-- Table: shared_roles

CREATE TABLE shared_roles
(
  id bigserial NOT NULL,
  username character varying NOT NULL,
  object_type character varying NOT NULL,
  role_id bigint,
  "read" boolean NOT NULL DEFAULT false,
  "write" boolean NOT NULL DEFAULT false,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  CONSTRAINT shared_roles_pkey PRIMARY KEY (id),
  CONSTRAINT shared_roles_roleid_fkey FOREIGN KEY (role_id)
      REFERENCES roles (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT shared_roles_unq1 UNIQUE (username, object_type, role_id)
);

ALTER TABLE shared_roles OWNER TO "www-data";

-- View: po_auth_requisitions becomes view po_auth_summary

DROP VIEW po_no_auth_user;

DROP VIEW po_auth_requisitions;

CREATE OR REPLACE VIEW po_auth_summary AS 
 SELECT h.id, a.order_number, h.status, a.type, a.username, h.order_date, h.due_date, h.payee_name, h.supplier
   FROM ( SELECT o.order_number, a.username, o.type, count(*) AS authlines
           FROM po_linesum o
      JOIN po_authlist a ON a.glaccount_id = o.glaccount_id AND a.glcentre_id = o.glcentre_id
     WHERE o.value <= a.order_limit
     GROUP BY o.order_number, a.username, o.type) a
   JOIN ( SELECT o.order_number, count(*) AS totallines
           FROM po_linesum o
          GROUP BY o.order_number) b ON a.order_number = b.order_number
   JOIN po_headeroverview h ON h.order_number = a.order_number
  WHERE a.authlines = b.totallines;

ALTER TABLE po_auth_summary OWNER TO "www-data";

CREATE OR REPLACE VIEW po_no_auth_user AS 
 SELECT h.id, h.order_number, h.order_date, h.due_date, h.payee_name, h.supplier, h.status
   FROM po_headeroverview h
   LEFT JOIN po_auth_summary a ON a.order_number = h.order_number
  WHERE h.type::text = 'R'::text AND a.username IS NULL;

ALTER TABLE po_no_auth_user OWNER TO "www-data";