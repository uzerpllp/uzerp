<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SopackingslipsController extends printController {

	protected $version = '$Revision: 1.15 $';
	
	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = DataObjectFactory::Factory('SOPackingSlip');
		$this->uses($this->_templateobject);
	}

	public function index()
	{
		
		if (!$this->checkParams('order_id'))
		{
			sendBack();
		}
		
		$this->view->set('clickaction', 'view');
		
		parent::index(new SOPackingSlipCollection($this->_templateobject));
		
		$sidebar = new SidebarController($this->view);
		
		$sidebar->addList(
			'Actions',
			array(
				'new' => array(
					'link' => array(
						'modules'		=>$this->_modules,
						'controller'	=>$this->name,
						'action'		=>'new',
						'order_id'		=>$this->_data['order_id']
					),
					'tag' => 'new_SO_Packing_Slip'
				)
			)
		);
		
		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);
		
	}

	public function _new()
	{
		
		parent::_new();
		
		$flash = Flash::Instance();
		
		$sopackingslip = $this->_uses[$this->modeltype];
		
		if ($sopackingslip->isLoaded())
		{
			// Get current packed totals on this packing list
			$contents = unserialize(base64_decode($sopackingslip->contents));
			$this->view->set('contents', $contents);
		}
		
		$order = DataObjectFactory::Factory('SOrder');
		$order->load($this->_data['order_id']);
		
		if ($order->isLoaded())
		{
			
			$lines	= array();
			$packed	= $order->packing_slips->getPackedTotals();
			
			$this->view->set('packed', $packed);
			
			foreach ($order->lines as $line)
			{
				
				// Get total of all items on the order, excluding cancelled items
				if ($line->status!=$line->cancelStatus())
				{
					
					$key = str_replace('"', '', $line->description);
					
					if (isset($lines[$key]))
					{
						$lines[$key] += $line->revised_qty;
					}
					else
					{
						$lines[$key] = $line->revised_qty;
					}
					
				}
				
			}
			
			$this->view->set('lines', $lines);
			
		}
		else
		{
			$flash->addError('Cannot load order');
			sendBack();
		}
		
		$this->view->set('no_ordering', TRUE);
		
	}
	
	public function delete()
	{
		
		if (!$this->checkParams($this->modeltype))
		{
			sendBack();
		}
		
		$flash = Flash::Instance();
		parent::delete($this->modeltype);
		
		sendTo(
			$_SESSION['refererPage']['controller'],
			$_SESSION['refererPage']['action'],
			$_SESSION['refererPage']['modules'],
			isset($_SESSION['refererPage']['other']) ? $_SESSION['refererPage']['other'] : null
		);
		
	}
	
	public function save()
	{
		
		if (!$this->checkParams($this->modeltype)) {
			sendBack();
		}
		
		$flash		= Flash::Instance();
		$errors		= array();
		$contains	= array();
		
		foreach ($this->_data[$this->modeltype] as $key => $packinglist)
		{
			
			if (is_array($packinglist) && isset($packinglist['contains']))
			{

				if ($packinglist['contains'] > $packinglist['available'])
				{
					$errors[] = 'Pack qty for ' . $key . ' exceeds that available for packing';
				}
				else
				{
					$contains[$key] = $packinglist['contains'];
				}
				
			}
			
		}
		
		$this->_data[$this->modeltype]['contents'] = base64_encode(serialize($contains));
		
		if (count($errors) == 0 && parent::save($this->modeltype))
		{
			sendTo('sorders', 'view', $this->_modules, array('id' => $this->_data[$this->modeltype]['order_id']));
		}
		else
		{
			$flash->addErrors($errors);
			$this->_data['order_id'] = $this->_data[$this->modeltype]['order_id'];
			$this->refresh();
		}

	}
	
	/* output functions */
	public function print_packing_slips($status = 'generate')
	{
		
		// build options array
		$options = array(
			'type'		=> array('pdf'=>''),
			'output'	=> array(
				'print'	=> '',
   				'save'	=> '',
   				'email'	=> '',
   				'view'	=> ''
			),
			'report'	=> 'SO_PackingList',
			'xslVars'	=> $xslVars
		);
		
		if (strtolower($status) == "dialog")
		{
			return $options;
		}
		
		// we need to merge the original data back with the normal data array
		// should this be done automatically?
		
		$original_data	= $this->decode_original_form_data($this->_data['encoded_query_data']);
		$this->_data	= array_merge($this->_data, $original_data);

		
		if (!$this->checkParams('SOrder'))
		{
			sendBack();
		}
		
		$errors		= array();
		$messages	= array();
		
		if (isset($this->_data['SOrder']['id']))
		{
			
			$sorder = DataObjectFactory::Factory('SOrder');
			$sorder->load($this->_data['SOrder']['id']);
			
			if (!$sorder->isLoaded())
			{
				$errors[] = 'Cannot find order';
			}
			
		}
		else
		{
			$errors[] = 'No order id';
		}
		
		$print_count = 0;
		
		foreach ($this->_data[$this->modeltype] as $id => $action)
		{
			
			if (isset($action['print']))
			{
				$print_count += 1;
			}
			
		}
		
		if ($print_count == 0)
		{
			$errors[] = 'No packing slips selected for printing';			
		}
		
		if (count($errors) > 0)
		{
			echo $this->returnResponse(FALSE, array('message' => implode('<br />', $errors)));
			exit;
		}
		
			
		foreach ($this->_data[$this->modeltype] as $id => $action)
		{
			
			if (isset($action['print']))
			{
				
				$sopackingslip = DataObjectFactory::Factory('SOPackingSlip');
				$sopackingslip->load($id);
				
				if ($sopackingslip->isLoaded())
				{
					
					// set a new filename for each document
					$options['filename'] = 'so_packing_list_' . $id;
					
					// set a few variables
					$order = $sopackingslip->order_detail;
					
					// set extra variable
					$extra = array();
					
					// set packing line
					$contents = array($contents=unserialize(base64_decode($sopackingslip->contents)));
					
					// make sure the contents var is a single array
					if (is_array(reset($contents)))
					{
						$contents = reset($contents);
					}
					
					$packinglines = array();
					
					foreach ($contents as $description => $qty)
					{
						
						if ($qty > 0)
						{
							$packinglines[]['line'][] = array(
								'description'	=> $description,
								'qty'			=> $qty
							);
						}
						
					}
					
					$extra['packing_lines'] = $packinglines;
					
					// set company address
					$company_address = array('name' => $this->getCompanyName());
					$company_address += $this->formatAddress($this->getCompanyAddress());
					$extra['company_address'] = $company_address;

					// set document details
					$document_reference=array();
					$document_reference[]['line']	= array('label' => 'Order Date', 'value' => un_fix_date($order->order_date));
					$document_reference[]['line']	= array('label' => 'Our Order Number', 'value' => $order->order_number);
					$document_reference[]['line']	= array('label' => 'Customer Ref', 'value' => $order->ext_reference);
					$document_reference[]['line']	= array('label' => 'Due Date', 'value' => un_fix_date($order->due_date));
					$document_reference[]['line']	= array('label' => 'Pack Ref', 'value' => $sopackingslip->name);
					$extra['document_reference']	= $document_reference;
					
					// generate xml
					$options['xmlSource'] = $this->generateXML(
						array(
							'model'						=> $sopackingslip,
							'relationship_whitelist'	=> array('order_detail'),
							'extra'						=> $extra
						)
					);
					
					// generate output
					$response = json_decode($this->constructOutput($this->_data['print'], $options), TRUE);

					// check for failure and set errors
					if ($response['status'] === FALSE)
					{
						$errors[] = $this->_data['printaction'] . ' Sales Order ' . $sopackingslip->order_number . ' Packing List ' . $sopackingslip->name . ' Failed';
					}
					else
					{
						$messages[] = $response['message'];
					}
				}
			}
		}
		
		if (count($errors) > 0)
		{
			echo $this->returnResponse(FALSE, array('message' => implode('<br />', $errors)));
		}
		else
		{
			$response['message'] = implode('<br />', $messages);
			echo $this->returnResponse(TRUE, $response);
		}
		
		exit;
		
	}
	
	protected function getPageName($base = null, $action = null)
	{
		return parent::getPageName((!empty($base))?$base:'sales_order_packing_slip',$action);
	}

}

// end of SopackingslipsController