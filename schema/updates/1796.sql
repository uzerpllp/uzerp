--
-- $Revision: 1.2 $
--

--

ALTER TABLE project_issue_header ADD COLUMN created character varying;
ALTER TABLE project_issue_header ADD COLUMN createdby character varying;

ALTER TABLE project_issue_lines ADD COLUMN assigned_to character varying;
ALTER TABLE project_issue_lines ADD COLUMN createdby character varying;


ALTER TABLE project_issue_lines
 ADD CONSTRAINT project_issue_lines_assigned_to_fkey FOREIGN KEY (assigned_to)
      REFERENCES users (username) MATCH SIMPLE
      ON UPDATE CASCADE ON DELETE CASCADE;

DROP VIEW project_issue_lines_overview;

CREATE OR REPLACE VIEW project_issue_lines_overview AS 
 SELECT pl.*
   FROM project_issue_lines pl
   LEFT JOIN project_issue_header ph ON pl.header_id = ph.id;

ALTER TABLE project_issue_lines_overview OWNER TO "www-data";