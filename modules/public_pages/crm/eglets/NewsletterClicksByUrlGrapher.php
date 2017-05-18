<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NewsletterClicksByUrlGrapher extends SimpleGraphEGlet {

	protected $version='$Revision: 1.3 $';
	
	function populate() {
		
		$db=&DB::Instance();
		$query = 'SELECT u.url, count(c.id) AS clicks FROM newsletter_urls u JOIN newsletter_url_clicks c ON (u.id=c.url_id) WHERE u.usercompanyid='.$db->qstr(EGS_COMPANY_ID);
		if(isset($_GET['id'])) {
			$query.=' AND u.newsletter_id='.$db->qstr($_GET['id']);
		}
		$query.=' GROUP BY u.url';
		
		$results=$db->GetAssoc($query);
		
		foreach($results as $url => $clicks) {
			$data['x'][] = $url;
			$data['y'][] = (float) $clicks;
		}
			
		$options['seriesList'][] = array(
			'label'			=> '',
			'legendEntry'	=> FALSE,
			'data'			=> $data
		);
		
		
		$options['type']		= 'bar';
		$options['identifier']	= __CLASS__;
			
		$this->contents = json_encode($options);
		
	}
	
}

// end of NewsletterClicksByUrlGrapher.php