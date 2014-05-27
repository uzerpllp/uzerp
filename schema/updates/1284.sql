CREATE TABLE so_packing_slips
(
  id bigserial NOT NULL,
  order_id integer NOT NULL,
  "name" character varying NOT NULL,
  tracking_code character varying,
  courier character varying,
  courier_service character varying,
  contents character varying,
  created timestamp without time zone DEFAULT now(),
  createdby character varying,
  alteredby character varying,
  lastupdated timestamp without time zone DEFAULT now(),
  usercompanyid bigint NOT NULL,
  CONSTRAINT so_packing_slips_pkey PRIMARY KEY (id),
  CONSTRAINT so_packing_slips_order_id_fkey FOREIGN KEY (order_id)
      REFERENCES so_header (id) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE VIEW so_packing_slips_overview as
select sop.*
     , soh.order_number
  from so_packing_slips sop
  LEFT JOIN so_header soh ON soh.id = sop.order_id;