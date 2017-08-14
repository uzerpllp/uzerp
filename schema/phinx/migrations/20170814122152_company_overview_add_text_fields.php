<?php

use UzerpPhinx\UzerpMigration;

class CompanyOverviewAddTextFields extends UzerpMigration
{

    /**
     * Add text1 and text2 fields to companyoverview
     *
     * These are free-text fields for any user defined purpose.
     */
    public function up()
    {
        $view_name = 'companyoverview';
        $view_owner = 'www-data';
        $view = <<<'VIEW'
CREATE OR REPLACE VIEW companyoverview AS
 SELECT c.id,
    c.name,
    c.accountnumber,
    c.vatnumber,
    c.companynumber,
    c.website,
    c.employees,
    c.usercompanyid,
    c.parent_id,
    c.owner,
    c.assigned,
    c.created,
    c.lastupdated,
    c.alteredby,
    c.description,
    c.is_lead,
    c.party_id,
    c.classification_id,
    c.source_id,
    c.industry_id,
    c.status_id,
    c.rating_id,
    c.type_id,
    c.createdby,
    c.tax_description,
    c.date_inactive,
    pa.address_id,
    a.street1,
    a.street2,
    a.street3,
    a.town,
    a.county,
    a.countrycode,
    a.postcode,
    a.country,
    a.address AS main_address,
    cm1.contact AS phone,
    cm2.contact AS email,
    cm3.contact AS fax,
    cm4.contact AS mobile,
    cc.name AS company_classification,
    ci.name AS company_industry,
    cr.name AS company_rating,
    cs.name AS company_source,
    cst.name AS company_status,
    ct.name AS company_type,
    pc.name AS company_parent,
    c.text1,
    c.text2
   FROM company c
     LEFT JOIN company pc ON c.parent_id = pc.id
     LEFT JOIN party p ON c.party_id = p.id
     LEFT JOIN partyaddress pa ON p.id = pa.party_id AND pa.main
     LEFT JOIN addressoverview a ON a.id = pa.address_id
     LEFT JOIN party_contact_methods pcm1 ON p.id = pcm1.party_id AND pcm1.main AND pcm1.type::text = 'T'::text
     LEFT JOIN party_contact_methods pcm2 ON p.id = pcm2.party_id AND pcm2.main AND pcm2.type::text = 'E'::text
     LEFT JOIN party_contact_methods pcm3 ON p.id = pcm3.party_id AND pcm3.main AND pcm3.type::text = 'F'::text
     LEFT JOIN party_contact_methods pcm4 ON p.id = pcm4.party_id AND pcm4.main AND pcm4.type::text = 'M'::text
     LEFT JOIN contact_methods cm1 ON cm1.id = pcm1.contactmethod_id
     LEFT JOIN contact_methods cm2 ON cm2.id = pcm2.contactmethod_id
     LEFT JOIN contact_methods cm3 ON cm3.id = pcm3.contactmethod_id
     LEFT JOIN contact_methods cm4 ON cm4.id = pcm4.contactmethod_id
     LEFT JOIN company_classifications cc ON cc.id = c.classification_id
     LEFT JOIN company_industries ci ON ci.id = c.industry_id
     LEFT JOIN company_ratings cr ON cr.id = c.rating_id
     LEFT JOIN company_sources cs ON cs.id = c.source_id
     LEFT JOIN company_statuses cst ON cst.id = c.status_id
     LEFT JOIN company_types ct ON ct.id = c.type_id
  WHERE NOT (EXISTS ( SELECT sc.id
           FROM system_companies sc
          WHERE c.id = sc.company_id));
VIEW;

        $this->query("select deps_save_and_drop_dependencies('public', '{$view_name}')");
        $this->query("DROP VIEW {$view_name}");
        $this->query($view);
        $this->query("ALTER TABLE {$view_name} OWNER TO \"{$view_owner}\"");
        $this->query("select deps_restore_dependencies('public', '{$view_name}')");
    }
}
