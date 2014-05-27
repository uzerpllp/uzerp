--
-- $Revision: 1.4 $
--

-- Table: entitiy_attachments

ALTER TABLE entity_attachments RENAME COLUMN entity_table TO data_model;
ALTER TABLE entity_attachments ADD COLUMN usercompanyid bigint;
ALTER TABLE entity_attachments ADD COLUMN created timestamp DEFAULT now();
ALTER TABLE entity_attachments ADD COLUMN createdby character varying;
ALTER TABLE entity_attachments ADD COLUMN alteredby character varying;
ALTER TABLE entity_attachments ADD COLUMN lastupdated timestamp DEFAULT now();

UPDATE entity_attachments
   SET usercompanyid = (SELECT usercompanyid
                          FROM file f
                         WHERE f.id = entity_attachments.file_id);

ALTER TABLE entity_attachments ALTER COLUMN usercompanyid SET NOT NULL;

ALTER TABLE entity_attachments
  ADD CONSTRAINT entity_attachments_usercompanyid_fkey FOREIGN KEY (usercompanyid)
      REFERENCES system_companies (id) MATCH SIMPLE
      ON UPDATE NO ACTION ON DELETE NO ACTION;

ALTER TABLE file ADD COLUMN createdby character varying;
ALTER TABLE file ADD COLUMN alteredby character varying;
ALTER TABLE file ADD COLUMN lastupdated timestamp DEFAULT now();

-- View: entity_attachments_overview

DROP VIEW IF EXISTS entity_attachments_overview;

ALTER TABLE file ALTER COLUMN revision type numeric;

CREATE OR REPLACE VIEW entity_attachments_overview AS 
 SELECT ea.*
      , f.name AS file, f.type, f.size, f.revision, f.note
   FROM entity_attachments ea
   LEFT JOIN file f ON ea.file_id = f.id;

ALTER TABLE entity_attachments_overview OWNER TO "www-data";

-- View: uzlet_modules_overview

-- DROP VIEW uzlet_modules_overview;

-- Change to LEFT join on uzlet_modules and modules
-- as it is possible to save a uzlet without linking it to a module
-- (i.e. a 'facility' to disable a uzlet!)
-- and this then ensures it does still appear in the list of uzlets!
CREATE OR REPLACE VIEW uzlet_modules_overview AS 
 SELECT u.id, u.name, u.title, u.preset, u.enabled, u.dashboard, u.uses, u.usercompanyid, u.created, u.createdby, u.alteredby, u.lastupdated, um.module_id, m.name AS module
   FROM uzlets u
   LEFT JOIN uzlet_modules um ON u.id = um.uzlet_id
   LEFT JOIN modules m ON m.id = um.module_id;

ALTER TABLE uzlet_modules_overview OWNER TO "www-data";

INSERT INTO uzlets
(
  "name",
  title,
  preset,
  enabled,
  dashboard,
  usercompanyid
)
SELECT 'ModuleDocumentsUZlet', 'Module Documents', false, true, true, id
  FROM system_companies;

---
--- Permissions
---

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'attachments', 'c', 'Manufacturing Documents', false, next.position, m.id, m.module_id
  from permissions m
     , (select max(c.position)+1 as position
          from permissions c
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where m.permission = 'manufacturing'
   and m.type = 'm'
   and not exists (select 1
                     from permissions c
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where c.permission = 'attachments'
                     and c.type = 'c');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select '_new', 'a', 'Upload Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = '_new'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'edit', 'a', 'Edit/Replace Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = 'edit'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select '_new', 'a', 'Upload Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = '_new'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'view_file', 'a', 'View Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = 'view_file'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'download', 'a', 'Download Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = 'download'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'view', 'a', 'View Attachment Details', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = 'view'
                     and a.type = 'a');

insert into permissions
(permission, type, title, display, position, parent_id, module_id)
select 'delete', 'a', 'Delete Attachment', false, next.position, c.id, c.module_id
  from permissions c
  join permissions m on m.id = c.parent_id
                    and m.type = 'm'
                    and m.permission = 'manufacturing'
     , (select coalesce(max(a.position)+1, 1) as position
          from permissions a
          join permissions c on c.id = a.parent_id
                    and c.type = 'c'
                    and c.permission = 'attachments'
          join permissions m on m.id = c.parent_id
                            and m.permission = 'manufacturing'
                            and m.type = 'm') as next
 where c.permission = 'attachments'
   and c.type = 'c'
   and not exists (select 1
                     from permissions a
                     join permissions c on c.id = a.parent_id
                                       and c.type = 'c'
                                       and c.permission = 'attachments'
                     join permissions m on m.id = c.parent_id
                                       and m.permission = 'manufacturing'
                                       and m.type = 'm'
                   where a.permission = 'delete'
                     and a.type = 'a');

