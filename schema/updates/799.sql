DROP VIEW qc_complaints_overview;

CREATE OR REPLACE VIEW qc_complaints_overview AS 
 SELECT qc.id, qc.date, qc.slmaster_id, sl.name AS retailer, qc.customer, qc.stitem_id, st.description AS product, qc.complaint_code_id, cc.code AS complaint_code, qc.supplementary_code_id, qc.problem, qc.investigation, qc.outcome, qc.credit_amount, qc.credit_note_no, qc.invoice_debit_no, qc.date_complete, qc.cost, qc.usercompanyid, qc.lastupdated, qc.alteredby, qc.type, qc.complaint_number, qc.assignedto
   FROM qc_complaints qc
   LEFT JOIN slmaster sl ON sl.id = qc.slmaster_id
   LEFT JOIN st_items st ON st.id = qc.stitem_id
   LEFT JOIN qc_complaint_codes cc ON cc.id = qc.complaint_code_id;