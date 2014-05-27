<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class GLAccountCentre extends DataObject {

	protected $version='$Revision: 1.8 $';
	
	function __construct($tablename='gl_account_centres')
	{
// Register non-persistent attributes
		
// Construct the object
		parent::__construct($tablename);
		
// Set specific characteristics
		$this->identifierField = 'glaccount ||\'/\'|| glcentre';		
		
// Set ordering attributes
		
// Define validation
		
// Define relationships
		$this->belongsTo('GLAccount','glaccount_id','glaccount');
		$this->belongsTo('GLCentre','glcentre_id','glcentre');
		$this->hasMany('Currency', 'currencies', array('writeoff_glaccount_id', 'glcentre_id'), array('glaccount_id', 'glcentre_id'), FALSE);
		$this->hasMany('CBAccount', 'cb_accounts', array('glaccount_id', 'glcentre_id'), array('glaccount_id', 'glcentre_id'), FALSE);
		$this->hasMany('WHLocation', 'wh_locations', array('glaccount_id', 'glcentre_id'), array('glaccount_id', 'glcentre_id'), FALSE);
		$this->hasMany('PeriodicPayment', 'periodic_payments', 'glaccount_centre_id', NULL, FALSE);
		$this->hasMany('PInvoiceLine', 'pi_lines', 'glaccount_centre_id', NULL, FALSE);
		$this->hasMany('POrderLine', 'po_lines', 'glaccount_centre_id', NULL, FALSE);
		$this->hasMany('SInvoiceLine', 'si_lines', 'glaccount_centre_id', NULL, FALSE);
		$this->hasMany('SOrderLine', 'so_lines', 'glaccount_centre_id', NULL, FALSE);
				
// Define field formats
		
// Define enumerated types
		
// Define system defaults
		
// Define related item rules
	
	}
 	
	public static function getAccountCentreId($_glaccount_id = '', $_glcentre_id = '', &$errors=array())
	{
		if (!empty($_glaccount_id) && !empty($_glcentre_id))
		{
		
			$accountcentre=new GLAccountCentre;
			$accountcentre->loadBy(array('glaccount_id', 'glcentre_id'), array($_glaccount_id, $_glcentre_id));
		
			if ($accountcentre->isLoaded())
			{
				return $accountcentre->id;
			}
			else
			{
				$errors[]='Invalid Account/Centre combination';
			}

			return '';
		}
	
	}
	
}

// End of GLAccountCentre