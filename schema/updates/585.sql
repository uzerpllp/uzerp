ALTER TABLE wh_actions ADD COLUMN "position" int4;

UPDATE wh_actions SET "position" = 0;

ALTER TABLE wh_actions ALTER COLUMN "position" SET NOT NULL;

ALTER TABLE wh_actions ALTER COLUMN "position" SET DEFAULT 0;