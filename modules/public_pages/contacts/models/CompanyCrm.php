<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class CompanyCrm extends DataObject
{
    protected $version = '$Revision: 1.6 $';

    public function __construct($tablename = 'company_crm')
    {
        parent::__construct($tablename);

        $this->belongsTo('CompanyClassification', 'classification_id', 'company_classification');
        $this->belongsTo('CompanyIndustry', 'industry_id', 'company_industry');
        $this->belongsTo('CompanyRating', 'rating_id', 'company_rating');
        $this->belongsTo('CompanySource', 'source_id', 'company_source');
        $this->belongsTo('CompanyStatus', 'status_id', 'company_status');
        $this->belongsTo('CompanyType', 'type_id', 'company_type');
    }
}

// End of CompanyCrm
