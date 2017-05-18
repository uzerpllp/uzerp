<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class CampaignsController extends Controller {

	protected $version='$Revision: 1.4 $';
	
	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Campaign();
		$this->uses($this->_templateobject);
	
		

	}

	public function index(){
		global $smarty;
		$this->view->set('clickaction', 'edit');
		
		$s_data=array();
		$errors=array();

		$this->setSearch('CampaignSearch', 'useDefault', $s_data);

		parent::index(new CampaignCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'Campaigns','action'=>'new'),
					'tag'=>'new_campaign'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}

	public function delete(){
		$flash = Flash::Instance();
		parent::delete('Campaign');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	}
	
	public function save() {
	$flash=Flash::Instance();
	if(parent::save('Campaign'))
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null);
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}
}
?>
