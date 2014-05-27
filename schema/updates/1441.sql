--
-- $Revision: 1.2 $
--

DROP VIEW qc_supplementary_complaint_code_overview;

CREATE OR REPLACE VIEW qc_supplementary_complaint_code_overview AS 

   SELECT scc.id, cc.id as complaint_code_id, cc.code || '-' || cc.description as parent_code, scc.code, scc.description
 
   FROM qc_supplementary_complaint_codes scc

   JOIN qc_complaint_codes cc ON cc.id = scc.complaint_code_id;
   