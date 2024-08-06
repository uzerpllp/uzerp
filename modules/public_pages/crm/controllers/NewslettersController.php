<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class NewslettersController extends Controller {

	protected $_templateobject;

	public function __construct($module=null,$action=null) {
		parent::__construct($module, $action);
		$this->_templateobject = new Newsletter();
		$this->uses($this->_templateobject);
	
		

	}

	public function index($collection = null, $sh = '', &$c_query = null){
		$this->view->set('clickaction', 'view');
		parent::index(new NewsletterCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		$sidebar->addList(
			'Actions',
			array(
				'new'=>array(
					'link'=>array('module'=>'crm','controller'=>'Newsletters','action'=>'new'),
					'tag'=>'new_Newsletter'
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	public function view() {
		$newsletter=$this->_uses['Newsletter'];
		$newsletter->load($this->_data['id']) or sendBack();
		$sidebar=new SidebarController($this->view);
		$sidebar->addCurrentBox('currently_viewing',$newsletter->name,array('module'=>'crm','controller'=>'newsletters','action'=>'edit','id'=>$newsletter->id));
		$sidebar->addList(
			'related_items',
			array(
				'views'=>array(
					'tag'=>'Views',
					'link'=>array('module'=>'crm','controller'=>'newsletters','action'=>'showviews','newsletter_id'=>$newsletter->id)
				),
				'clicks'=>array(
					'tag'=>'Clicks',
					'link'=>array('module'=>'crm','controller'=>'newsletters','action'=>'showclicks','newsletter_id'=>$newsletter->id)
				)
			)
		);
		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);
	}
	
	public function showviews() {
		$views = new NewsletterviewCollection(new Newsletterview);
		$sh = new SearchHandler($views,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('newsletter_id','=',$this->_data['newsletter_id']));
		if(isset($sh->fields['newsletter'])) {
			unset($sh->fields['newsletter']);
		}
		$views->load($sh);
		$this->setTemplateName('view_related');
		$this->view->set('clickaction','viewperson');
		$this->view->set('paging_link',array('module'=>'crm','controller'=>'newsletters','action'=>'showviews','newsletter_id'=>$this->_data['newsletter_id']));
		$this->view->set('no_ordering',true);
		$this->view->set('related_collection',$views);
		$this->view->set('num_pages',$views->num_pages);
		$this->view->set('cur_page',$views->cur_page);
	}
	
	public function viewperson() {
		$view = new Newsletterview();
		$view->load($this->_data['id']) or sendBack();
		$person = new Person();
		$person->load($view->person_id);
		sendTo('persons','view','contacts',array('id'=>$person->id));
	}
	
	public function showclicks() {
		$clicks = new NewsletterurlclickCollection(new Newsletterurlclick);
		$sh = new SearchHandler($clicks,false);
		$sh->extract();
		$sh->addConstraint(new Constraint('newsletter_id','=',$this->_data['newsletter_id']));
		if(isset($sh->fields['newsletter'])) {
			unset($sh->fields['newsletter']);
		}
		$clicks->load($sh);
		$this->setTemplateName('view_related');
		$this->view->set('clickaction','viewperson');
		$this->view->set('paging_link',array('module'=>'crm','controller'=>'newsletters','action'=>'showclicks','newsletter_id'=>$this->_data['newsletter_id']));
		$this->view->set('no_ordering',true);
		$this->view->set('related_collection',$clicks);
		$this->view->set('num_pages',$clicks->num_pages);
		$this->view->set('cur_page',$clicks->cur_page);
	}
	
	
	public function delete($modelName = null){
		$flash = Flash::Instance();
		parent::delete('Newsletter');
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	}
	
	public function save($modelName = null, $dataIn = [], &$errors = []) : void {
	$flash=Flash::Instance();
	if(parent::save('Newsletter'))
		sendTo($_SESSION['refererPage']['controller'],$_SESSION['refererPage']['action'],$_SESSION['refererPage']['modules'],$_SESSION['refererPage']['other'] ?? null);
	else {
			$this->_new();
			$this->_templateName=$this->getTemplateName('new');
		}

	}
}
?>
