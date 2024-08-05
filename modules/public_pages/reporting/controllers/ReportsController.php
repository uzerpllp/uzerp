<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ReportsController extends printController {

	protected $version = '$Revision: 1.35 $';
	protected $_templateobject;

	protected $default_options = array(
		'normal_field_label'			=> '', 
		'normal_display_field'			=> TRUE, 
		'normal_break_on'				=> FALSE,
		'normal_method'					=> 'dont_total',
		'normal_total'					=> 'report', // default on the report level
		'normal_enable_formatting'		=> FALSE,
		'normal_decimal_places'			=> 0,
		'normal_red_negative_numbers'	=> FALSE,
		'normal_thousands_seperator'	=> FALSE
	);

	public function __construct($module = null, $action = null)
	{
		parent::__construct($module, $action);
		$this->_templateobject = new Report();
		$this->uses($this->_templateobject);
	}

	public function index($collection = null, $sh = '', &$c_query = null)
	{

		$errors = array();

		$s_data=array();

		if (!empty($this->_data['tablename']))
		{
			$s_data['tablename'] = $this->_data['tablename'];
		}

		$this->setSearch('reportsSearch', 'useDefault', $s_data);

		$this->view->set('clickaction', 'view');
		$reports = new ReportCollection($this->_templateobject);

		$sh=$this->setSearchHandler($reports);

		$cc = ownerConstraint();

		$hasreport=new HasReport();
		$report_list=$hasreport->getByRoles();

		if (!empty($report_list))
		{
			$cc->add(new Constraint('id', 'in', '('.implode(',', array_keys($report_list)).')'), 'OR');
		}

		$sh->addConstraintChain($cc);

		parent::index($reports, $sh);

		$sidebar = new SidebarController($this->view);

		$sidebarlist = array();

		$sidebarlist['new_any'] = array(
					'tag'	=> 'New Report',
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'new'
					)
				);

		if (!empty($this->_data['tablename']))
		{
			$sidebarlist['new_this'] = array(
					'tag'	=> 'New Report for '.$this->_data['tablename'],
					'link'	=> array(
						'modules'		=> $this->_modules,
						'controller'	=> $this->name,
						'action'		=> 'new',
						'tablename'		=> $this->_data['tablename']
					)
				);
		}

		$sidebar->addList('Actions', $sidebarlist);

		$this->view->register('sidebar', $sidebar);
		$this->view->set('sidebar', $sidebar);

	}

	public function delete($modelName = null)
	{

		$flash	= Flash::Instance();
		$report = $this->_uses[$this->modeltype];
		$report->load($this->_data['id']);

		if ($report->owner === EGS_USERNAME)
		{

			if (parent::delete($this->modeltype))
			{
				sendTo($this->name, 'index', $this->_modules,null);
			}
			else
			{
				sendTo($this->name, 'view', $this->_modules, array('id' => $this->_data['id']));
			}

 		}
 		else
 		{
			$flash->addError("You can only delete reports you own");
			sendTo($this->name, 'view', $this->_modules, array('id' => $this->_data['id']));
		}

	}

	public function _new()
	{

//		if (!$this->loadData())
//		{
//			$this->dataError();
//			sendBack();
//		}

		parent::_new();

		// an array of legacy search fields
		$search_fields = array();
		$flash = Flash::Instance();

		// get the report model, lets not envoke it again... that's not cool
		$report = $this->_uses[$this->modeltype];

		if (!empty($this->_data['tablename']))
		{
			$report_options[$this->_data['tablename']] = $this->_data['tablename'];
		}
		else
		{
			$report_options = $report::getTables();
		}

		if (!$report->isLoaded())
		{
			$report->tablename = current($report_options);
			$this->view->set('update', false);
		}
		else
		{
			$this->view->set('update', true);
		}

		$this->view->set('report_options', $report_options);

		// get the fields for the tablename and sort them
		$available_fields = $this->getColumns($report->tablename);
		ksort($available_fields);

		// unserialise the options from the db
		$options = unserialize($report->options);

		if ($options !== FALSE)
		{

			// overlay the defaults so we've got a full set of options
			$options = $this->expand_options($options, $report->tablename);

			// sort options by position
			$options = $this->sort_options($options);

			// loop through used fields and remove them from the available fields array
			foreach ($options as $field => $field_options)
			{

				// remove the field from the available fields array
				unset($available_fields[$field]);

				// we need to check against legacy search options
				if ($field_options['field_type'] === 'search')
				{

					// build an array of broken search options
					$search_fields[] = $field;

					// update the legacy options
					$options[$field] = array_merge($this->default_options, $options[$field]);

					// ATTN: boolean values didn't work here... but that's what the defaults are set as
					// set other specific search settings
					$options[$field]['field_type']				= 'normal';
					$options[$field]['normal_enable_search']	= 'true';
					$options[$field]['normal_display_field']	= 'false';

					// if a default value exists
					if (isset($options[$field]['default_value']))
					{

						// set it to the new option name
						$options[$field]['search_default_value'] = $options[$field]['default_value'];

						// unset the old version
						unset($options[$field]['default_value']);

					}

				}

			}

			unset($available_fields['filter']);

		}
		else
		{
			$options = array();
		}

		if (!empty($search_fields))
		{
			$flash->addError('Legacy search fields found, click save <strong>immediately</strong> to update');
		}

		$description = $report->description;

		// set smarty vars
		$this->view->set('description', $description);
		$this->view->set('available_fields', $available_fields);
		$this->view->set('selected_tablename', $report->tablename);
		$this->view->set('options', $options);
		$this->view->set('report', $report);

		//Set report defintion list
		$report_type_id = ReportType::getReportTypeID('Reports');
		$definition_list = ReportDefinition::getReportsByType($report_type_id);
		$definition_list = [0 => 'Default'] + $definition_list;
		$this->view->set('report_definitions', $definition_list);

		//Set currently selected report definition
		$selected_def = ReportDefinition::getDefinitionByID($report->report_definition);
		if ($selected_def->_data != null) {
			($selected_def->_data['name'] == 'PrintCollection') ? 
				$this->view->set('selected_reportdef', 0) :
				$this->view->set('selected_reportdef', $selected_def->_data['id']);
		}
	}

	public function copy() 
	{

		// get the report model, lets not envoke it again... that's not cool
		$report = $this->_uses[$this->modeltype];
		$report->load($this->_data['id']);

		// apply model data to controller data for saving
		$this->_data['Report'] = $report->_data;

		// remove the id so we generate a new record
		unset($this->_data['Report']['id']);

		// change the name slightly
		$this->_data['Report']['description'] .= ' - Copy';

		// post data, output status
		if (parent::save($this->modeltype))
		{
			sendTo($this->name, 'edit', $this->_modules, array('id' => $this->saved_model->id));
		}
		else
		{
			sendBack();
 		}

	}

	public function run()
	{

		// process any search data that may be passed
		if (is_array($this->_data['Search']) && isset($this->_data['Search']['report_id']))
		{
			$this->_data[$this->_templateobject->idField] = $this->_data['Search']['report_id'];
			unset($this->_data['Search']['report_id']);
		}

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$flash = Flash::Instance();

		// get the report model, lets not envoke it again... that's not cool
		$report = $this->_uses[$this->modeltype];

		// there's no point in processing all the following data just to display the dialog... display it now 
		if (isset($this->_data['printaction']) || isset($this->_data['printAction']) || isset($this->_data['ajax_print'])) {

			// build options array
			$defs = ReportDefinition::getDefinition('PrintCollection');
			$dialog_options = array(
				'type' => array(
					'pdf'	=> '',
					'xml'	=> '',
					'csv'	=> ''
				),
				'output' => array(
					'print'	=> '',
					'save'	=> '',
					'email'	=> '',
					'view'	=> ''
				),
				'filename' => strtolower(str_replace(" ", "_", $report->description)) . '_' . date('d-m-Y')
			);

			// if ajax_print is not set we must be on the dialog
			if (!isset($this->_data['ajax_print']))
			{
				return $dialog_options;
			}

		}

		// give smarty the title
		$this->view->set('title', $report->description);

		// unserialise the options from the db
		$options = unserialize($report->options);

		// overlay the defaults so we've got a full set of options
		$options = $this->expand_options($options, $report->tablename);

		// sort options by position
		$options = $this->sort_options($options);

		// vars
		$aggregate_fields	= array();
		$at_agg				= FALSE;
		$fields				= array();

		$display_fields		= array();
		$measure_fields		= array();
		$search_fields		= array();
		$filter_fields		= array();
		$aggregate_methods	= array();

		// build arrays for use in the view
		foreach ($options as $field => $field_options)
		{

			// we need to check against legacy search options
			if ($field_options['field_type'] === 'search')
			{

				$flash->addError('Report has legacy search fields, please edit this report to update');
				sendBack();

			}

			// ignore the filter field... it really isn't a field
			if ($field == 'filter')
			{
				continue;
			}

			// iron out the field label if it exists
			if (isset($field_options['field_label']) && !empty($field_options['field_label']))
			{
				$label = $field_options['field_label'];
			}
			else
			{
				$label = $field;
			}

			$position = $field_options['position'];

			// we're always dealing with normal fields now
			// no need for a switch statement

			// ignore fields that aren't meant to be displayed		
			if ($field_options['normal_display_field'] !== 'false')
			{

				// build two arrays, the display fields array will include filter fields... for now
				$original_fields[$position] = $field;
				$display_fields[$position] = $field;

				if ($field_options['normal_break_on'] === "true") 
				{
					$measure_fields[$position] = $field;
				}

				if (!empty($field_options['normal_method']) && $field_options['normal_method'] !== 'dont_total')
				{

					$aggregate_fields[$position] = $field;
					$aggregate_methods[$position] = $field_options['normal_method'] . '(' . $field . ') as ' . $field;

					// if we're setting an aggregate field... this must not be a display field
					unset($display_fields[$position]);

				}

			}

			// if the field is also a search field, add it to the search array
			if (isset($field_options['normal_enable_search']) && $field_options['normal_enable_search'] === 'true')
			{
				$search_fields[$field] = $field_options;
			}

		}

		// if the filters aren't empty, apply them one by one
		if (!empty($options['filter']))
		{

			// loop through filter lines
			foreach (range(1, 3) as $number)
			{

				if (!isset($filter_cc))
				{
					$filter_cc = new ConstraintChain();
				}

				// filter line 1 will never have an operator
				if ($number === 1)
				{
					$operator = 'AND';
				}
				else
				{
					$operator = $options['filter']['filter_' . $number . '_operator'];
				}

				$field		= $options['filter']['filter_' . $number . '_field'];
				$condition	= $options['filter']['filter_' . $number . '_condition'];
				$value		= $options['filter']['filter_' . $number . '_value'];

				// if we're dealing with a valid filter line...
				if (($number === 1 OR !empty($operator)) AND !empty($value))
				{

					// add the field to the display fields
					if (!array_search($field, $display_fields))
					{
						$display_fields = array_merge($display_fields, array($field));	
					}

					// add the filter to the contraint chain
					$filter_cc->add(new Constraint($field, $condition, $value), $operator);

				} 

			}

		}

		$idField = '';
		$do = $report->createDataObject($display_fields, $idField, $this->getColumns($report->tablename));

		$doc = new DataObjectCollection($do);
		$sh = new SearchHandler($doc, FALSE);
		$sh->setGroupby($display_fields);
		$sh->setOrderby($display_fields);

		if (!isset($this->_data['Search']))
		{
			$this->_data['Search'] = array();
		}

		// we don't need a condition here... always display the search box		
		$this->search = $report->createSearch($search_fields, $this->_data['Search']);
		$cc = $this->search->toConstraintChain();
		$cc->removeByField('report_id');
		$sh->addConstraintChain($cc);

		// if the filter constraint chain has been set, use it
		if (isset($filter_cc))
		{
			$sh->addConstraintChain($filter_cc);
		}

		$measure_fields = array_merge(array('' => 'report'), $measure_fields);

		/// merge the aggregate methods array in with the display fields array
		// the aggregate methods array is preset as an array to allow for empty values
		// we don't use the array_merge function as we want to maintain keys (representing position)
		$display_fields = (array)$display_fields + (array)$aggregate_methods;

		if (count($aggregate_fields) === 0)
		{
			$this->view->set('aggregate_count', 0);
		}

		// sort display fields by key (position)
		ksort($display_fields);

		// prepend the id field to the display fields... 
		// at this stage the items are in order, so keys don't matter
		$display_fields = array_merge(array($idField), $display_fields);

		$sh->setFields($display_fields);

		$data = $doc->load($sh, null, RETURN_ROWS);

		$this->view->set('total_records', $doc->total_records);

		$headings = $doc->getHeadings();

		// loop through headings...
		foreach ($headings as $key => $field)
		{

			if (!array_search($key, $original_fields))
			{
				// if item isn't in original fields remove it from headings array
				unset($headings[$key]);
			}
			else
			{

				// if label exists, use that for heading
				if (!empty($options[$key]['normal_field_label']))
				{
					$headings[$key] = $options[$key]['normal_field_label'];
				}

			}

		}

		$heading_keys = array_keys($headings);

		$this->view->set('headings', $headings);

		/*
		 * Build the data array
		 * 
		 * There is no point in processing everything just to output the 
		 * print dialog OR is we're CSVing the output.
		 */

		if ( (!isset($this->_data['printaction']) && !isset($this->_data['printAction'])) || $this->_data['print']['printtype']!=='csv' )
		{

			$response = $this->buildArray(
				$data,
				$headings,
				$measure_fields,
				$aggregate_fields,
				$heading_keys,
				$options
			);

			$data_arr = $response['data_arr'];
			$sub_total_keys = $response['sub_total_keys'];

		}

		$this->view->set('options', $options);

		// are we being called from a print dialog or are we actually printing?
		if (isset($this->_data['printaction']) || isset($this->_data['printAction']) || isset($this->_data['ajax_print']))
		{

			if ($this->_data['print']['printtype'] === 'csv')
			{
				$dialog_options['csv_source'] = $this->generate_csv($this->_data['print'], $data_arr, array_keys($headings));
			}
			else
			{

				// build xml
				$xml  =	'';
				$xml .=	"<data>"."\n";

				if (!empty($data_arr))
				{

					foreach ($data_arr as $key => $row)
					{

						// if row is a subtotal, construct an appropriate attribute
						$row_class = '';

						if (isset($sub_total_keys[$key]))
						{
							$row_class = 'sub_total="true"';
						}

						// build the xml
						// cannot utalise all of the output functions as we need to do some specific stuff
						$xml .= "\t" . "<record " . $row_class . ">" . "\n";

						foreach ($row as $field => $value)
						{
							// less-than causes issues in XML
							$value = str_replace('<', '&#60;', $value);
							$cell_class = array();

							if (isset($options[$field]['normal_red_negative_numbers']) && $options[$field]['normal_red_negative_numbers']=="true" && $value<0)
							{
								$cell_class[] = 'negative_number="true"';
							}

							if ($options[$field]['normal_enable_formatting'] === 'true')
							{		

								if (isset($options[$field]['normal_justify']))
								{
									$cell_class[] = 'text-align="' . $options[$field]['normal_justify'] . '"';
								}

							}

							$xml .= "\t\t" . "<" . $field . " " . implode(' ', $cell_class) . ">" . $value . "</" . $field . ">" . "\n";

						}

						$xml .= "\t" . "</record>" . "\n";

					}

				}

				$xml .= "</data>" . "\n";

				// build xsl
				$col_widths = array();

				if (isset($this->_data['col_widths']))
				{
					$col_widths = $this->parse_column_widths($this->_data['col_widths']);
				}

				// Use the report defintion defined in DB, else use the standard list xsl
				if ($report->report_definition)
				{
					$def = new ReportDefinition();
					$def->loadBy('id', $report->report_definition);
					$report_definition_name = $def->_data['name'];
				}
				else
				{
					$report_definition_name = 'PrintCollection';
				}

				$xsl = $this->build_custom_xsl(
					$doc,
					$report_definition_name,
					str_replace('<', '&#60;', $report->description), // less-than causes issues in XML
					$headings,
					$col_widths,
					$options
				);

				// set resources
				$dialog_options['xmlSource'] = $xml;
				$dialog_options['xslSource'] = $xsl;

				$search_options = $this->search->toString('fop');

				if (!empty($search_options))
				{
					$dialog_options['xslVars']['search_string'] = "Search options: " . $search_options;
				}
				else
				{
					$dialog_options['xslVars']['search_string'] = '';
				}

			}

			$this->_data['print']['attributes']['orientation-requested'] = 'landscape';

			// execute the print output function, echo the returned json for jquery
			echo $this->generate_output($this->_data['print'], $dialog_options);
			exit;

		}

		$this->view->set('report_array', $data_arr);
		$this->view->set('sub_total_keys', $sub_total_keys);

	}

	public function save($modelName = null, $dataIn = [], &$errors = []) : void
	{

		$flash=Flash::Instance();

		// this is going to be a prodomintely (sp?) ajax save... effectively we're 
		// not moving away from the page if we have an error... this is the best way
		// to preserve such a delicate form like this 

		// set a few vars
		$normal_field_exists = FALSE;

		// ensure we have some fields
		if (!isset($this->_data['Report']['options']) OR empty($this->_data['Report']['options']))
		{
			echo $this->returnResponse(FALSE,array('message' => '<li>No fields specified</li>'));
			exit;
		}

		// loop through fields, check if any are of NORMAL type
		foreach ($this->_data['Report']['options'] as $field => $options)
		{

			// remember, if the field type doesn't exist it's default is 'normal'
			if (!isset($options['field_type']) OR $options['field_type'])
			{
				$normal_field_exists=TRUE;
				break; // no point in continuing
			}

		}

		// if the normal fields exists status hasn't changed, throw the error
		if ($normal_field_exists === FALSE)
		{
			echo $this->returnResponse(FALSE, array('message' => '<li>No normal fields specified</li>'));
			exit;
		}

		// serialise the report options back to itself
		$this->_data['Report']['options']=serialize($this->_data['Report']['options']);

		// remove the id if we want to save as a copy, a new id (and thus the record) will be generated
		if (strtolower((string) $this->_data['save'])=='save copy')
		{
			unset($this->_data['Report']['id']);
		}

		// Set the rpeort definition id, unless Default was selected
		if ($this->_data['report_def'] != 0)
		{
			//$def = new ReportDefinition();
			//$rdef = $def->getDefinitionByID($this->_data['report_def']);
			$this->_data['Report']['report_definition'] = $this->_data['report_def'];
		}

		// fire up the db
		$db = DB::Instance();
		$db->StartTrans();

		// post data, output status
		if (parent::save($this->modeltype))
		{
			$db->CompleteTrans();

			$link = sprintf(
				'/?module=%s&controller=%s&action=%s&id=%s',
				$this->module,
				$this->name,
				'view',
				$this->saved_model->id
			);

			echo $this->returnResponse(TRUE, array('redirect' => $link));
			exit;

		}
		else
		{
			$db->FailTrans();
			$db->CompleteTrans();

			// get the errors
			$flash->save();
			$errors=$flash->__get('errors');
			$flash->clear();

			$message='<li>Failed to save Report</li>';

			if (!empty($errors)) {
				foreach ($errors as $error) {
					$message.="<li>".$error."</li>";
				}
			}

			echo $this->returnResponse(FALSE,array('message' => $message));
			exit;

 		}

	}

	public function view()
	{

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}

		$display_fields		= array();
		$measure_fields		= array();
		$aggregate_fields	= array();
		$search_fields		= array();
		$filter_fields		= array();

		// get report model
		$report=$this->_uses[$this->modeltype];

		// unserialise the options from the db
		$options=unserialize($report->options);

		// overlay the defaults so we've got a full set of options
		$options=$this->expand_options($options,$report->tablename);

		// sort options by position
		$options=$this->sort_options($options);

		foreach ($options as $field => $field_options)
		{

			// ignore the filter field... it really isn't a field
			if (in_array($field, array('filter', 'search')))
			{
				continue;
			}

			$position=$field_options['position'];

			// ignore fields that aren't meant to be displayed		
			if ($field_options['normal_display_field'] !== 'false')
			{

				// we're always dealing with normal fields now

				$display_fields[$position]=$field;

				if ($field_options['normal_break_on']=="true")
				{
					$measure_fields[$position]=$field;
				}

				if (!empty($field_options['normal_method']) && $field_options['normal_method']!='dont_total')
				{

					$aggregate_fields[$position]=$field_options['normal_method'].'('.$field.') as '.$field;

					// if we're setting an aggregate field... this must not be a display field
					unset($display_fields[$position]);	

				}

			}

			// if the field is also a search field, add it to the search array
			if (array_key_exists('normal_enable_search', $field_options)
				&& $field_options['normal_enable_search'] === 'true')
			{
				$search_fields[$field]=$field;	
			}

		}

		if (!empty($options['filter']))
		{

			// loop through filter lines
			foreach (range(1, 3) as $number)
			{

				// filter line 1 will never have an operator
				if ($number==1)
				{
					$operator	=	'';
				}
				else
				{
					$operator	=	$options['filter']['filter_'.$number.'_operator'].' ';
				}

				$field		=	$options['filter']['filter_'.$number.'_field'];
				$condition	=	$options['filter']['filter_'.$number.'_condition'];
				$value		=	$options['filter']['filter_'.$number.'_value'];

				// if we're dealing with a valid filter line...
				if (($number==1 OR !empty($operator)) AND !empty($field) AND !empty($value))
				{
					$filter_fields[$number]=$operator.$field.' '.$condition.' '.$value;
				} 

			}

		}

		// output to smarty
		$this->view->set('display_fields',implode(',',$display_fields));
		$this->view->set('break_on_fields',implode(',',$measure_fields));
		$this->view->set('aggregate_fields',implode('<br />',$aggregate_fields));
		$this->view->set('search_fields',implode(',',$search_fields));
		$this->view->set('filter_fields',implode('<br />',$filter_fields));

		$rd = ReportDefinition::getDefinitionByid($report->report_definition);
		if ($rd->_data !== null) {
			$report_definition_name = $rd->_data['name'];
			$this->view->set('report_definition', $report_definition_name);
		}

		$hasreport=new HasReport();
		$report_list=$hasreport->getAssignedRoles($report->id);
		$this->view->set('roles', $report_list);

		// create sidebar
		$this->view->set('report', $report);	
		$sidebar = new SidebarController($this->view);

		$sidebarlist=array();

		$sidebarlist['all']=array(
			'tag'	=>	'All Reports',
			'link'	=>	array(
				'modules'		=>	$this->_modules,
				'controller'	=>	$this->name,
				'action'		=>	'index'
			)
		);

		$sidebarlist['edit']=array(
			'tag'	=>	'Edit Report',
			'link'	=>	array(
				'modules'			=> $this->_modules,
				'controller'		=> $this->name,
				'action'			=> 'edit',
				$report->idField	=> $report->{$report->idField}
			)
		);

		if ($report->owner == EGS_USERNAME)
		{

			$sidebarlist['delete']=array(
				'tag'	=>	'Delete Report',
				'link'	=>	array(
					'modules'			=>	$this->_modules,
					'controller'		=>	$this->name,
					'action'			=>	'delete',
					$report->idField	=>	$report->{$report->idField}
				)
			);

		}

		$sidebarlist['copy']=array(
			'tag'	=>	'Copy Report',
			'link'	=>	array(
				'modules'			=>	$this->_modules,
				'controller'		=>	$this->name,
				'action'			=>	'copy',
				$report->idField	=>	$report->{$report->idField}
			)
		);

		$sidebarlist['run']=array(
			'tag'	=>	'Run Report',
			'link'	=>	array(
				'modules'			=>	$this->_modules,
				'controller'		=>	$this->name,
				'action'			=>	'run',
				$report->idField	=>	$report->{$report->idField}
			)
		);

		if ($report->owner == EGS_USERNAME)
		{
			if (empty($report_list))
			{
				$tag='Publish Report';
			}
			else
			{
				$tag='Amend Report Roles';
			}

			$sidebarlist['publish']=array(
				'tag'	=>	$tag,
				'link'	=>	array(
					'modules'			=>	$this->_modules,
					'controller'		=>	$this->name,
					'action'			=>	'publish_report',
					$report->idField	=>	$report->{$report->idField}
				)
			);
		}

		$sidebar->addList(
			'Actions',
			$sidebarlist
		);

		$this->view->register('sidebar',$sidebar);
		$this->view->set('sidebar',$sidebar);

	}

	public function publish_report () {

		if (!$this->loadData())
		{
			$this->dataError();
			sendBack();
		}
		// get report model
		$report=$this->_uses[$this->modeltype];

		$hasreport=new HasReport();

		if ($report->isLoaded())
		{
			// get the current list of assigned roles
			$report_list=$hasreport->getAssignedRoles($report->id);
			$this->view->set('roles', array_keys($report_list));
		}

		$this->view->set('hasreport', $hasreport);

	}

	public function save_report_roles () {

		if (!$this->checkParams('Report'))
		{
			$this->dataError();
			sendBack();
		}
		$flash=Flash::Instance();
		$errors=array();

		if (empty($this->_data['HasReport']['role_id']))
		{
			$errors[]='No roles selected';
		}
		elseif (!empty($this->_data['Report']['id']))
		{
			$data['report_id']=$this->_data['Report']['id'];

			$db=DB::Instance();
			$db->StartTrans();

			$new_roles=implode(',', $this->_data['HasReport']['role_id']);

			// Delete any existing report-roles that are not in the selection
			$hasreports=new HasReportCollection(new HasReport());
			$sh=new SearchHandler($hasreports, false);
			$sh->addConstraint(new Constraint('report_id', '=', $data['report_id']));
			$sh->addConstraint(new Constraint('role_id', 'not in', '('.$new_roles.')'));
			if ($hasreports->delete($sh)===false)
			{
					$errors[]='Error deleting existing roles';
			}
			else
			{
//echo 'Check for existing report-role<pre>'.print_r($this->_data['HasReport']['role_id'], true).'</pre><br>';
				foreach ($this->_data['HasReport']['role_id'] as $role_id)
				{
					// Check for existing report-role
					$hasreport=new HasReport();
					$hasreport->loadBy(array('report_id', 'role_id', 'permissions_id')
									 , array($data['report_id'], $role_id, ''));

					if (!$hasreport->isLoaded())
					{
						// no existing permission for this report-role so insert it
						$data['role_id']=$role_id;
						$hasreport=DataObject::Factory($data, $errors, 'HasReport');
						if (!$hasreport || !$hasreport->save())
						{
							$errors[]=$db->ErrorMsg();
							$errors[]='Error saving report roles';
							$db->FailTrans();
							break;
						}
					}
				}
			}
//echo 'Errors<pre>'.print_r($errors, true).'</pre><br>';
//exit;
			$db->completeTrans();

		}

		if(count($errors)===0)
			sendTo($this->name, 'view', $this->_modules, array('id'=>$this->_data[$this->modeltype]['id']));
		else {
			$flash->addErrors($errors);
			$this->refresh();
		}

	}

	// used to 'fill in the blanks' with default options
	public function expand_options($options, $tablename)
	{

		$report=$this->_uses[$this->modeltype];

		// get the field, we'll be needing the data type
		$fields=$this->getColumns($tablename);

		// ATTN: should this cover the search / filter field types too? 

		if (!empty($options))
		{

			foreach ($options as $field => $field_options)
			{

				// add the datatype
				$options[$field]['_field_data_type']=$fields[$field]->type;

				if (!isset($field_options['field_type']) || $field_options['field_type']=='normal')
				{

					// just for kicks, reapply the field type
					$options[$field]['field_type']='normal';

					// merge with defaults
					$options[$field] = array_merge($this->default_options, $options[$field]);

					// set the method if it doesn't exist, replies on field data type
					if (!isset($field_options['normal_method']))
					{

						// set default break method, differs depending on the field data type
						$types=$report->getAggregateMethods($fields[$field]);

						$options[$field]['normal_method']=key($types);

					}

				}

			}

		}

		return $options;

	}

	public function sort_options($options)
	{

		// custom sort, uses cmp_position() in lib.php
		uasort($options, 'cmp_position');

		return $options;

	}

	public function pivot_table()
	{

		$tablename='';

		if (isset($this->_data['tablename']))
		{
			$tablename=$this->_data['tablename'];
		}

		// need to load the model to get the tablename
		$report = $this->_uses[$this->modeltype];
		$report->load($this->_data['id']);

		if (isset($this->_data['description']))
		{
			$description=$this->_data['description'];
		}
		else
		{
			$description=$report->description;
		}

		$this->view->set('description',$description);
		$this->view->set('selected_tablename',$tablename);
		$this->view->set('models', array('Report' => $report));
		$this->view->set('available_fields', $this->getColumns($tablename));

		$report_options[$this->_data['tablename']] = $this->_data['tablename'];
		$this->view->set('report_options', $report::getTables());
	}

	private function getColumns($tablename='')
	{

		$fields=array();

		if (!empty($tablename))
		{
			$fields=system::getFields($tablename, FALSE);
		}

		return $fields;

	}

	public function getSearchTypes()
	{
		$report=$this->_uses[$this->modeltype];
		$searchTypes=$report->getSearchType($this->_data['type']);

		$this->view->set('options',$searchTypes);
		$this->setTemplateName('select_options');
	}

	public function getAggregateMethods()
	{
		$report=$this->_uses[$this->modeltype];
		$aggregateMethods=$report->getAggregateMethods($this->_data['type']);

		$this->view->set('options',$aggregateMethods);
		$this->setTemplateName('select_options');
	}

	protected function getPageName($base=null,$action=null)
	{
		return parent::getPageName('reports');
	}

	protected function reporting_number_format($params)
	{

		if ($params['options']['normal_enable_formatting']=='true') 
		{

			// set a few vars
			$class=array();

			$decimals=2;

			if (isset($params['options']['normal_decimal_places']))
			{
				$decimals=$params['options']['normal_decimal_places'];
			}

			$dec_point=".";

			$thousands_sep="";

			if (isset($params['options']['normal_thousands_seperator']) && $params['options']['normal_thousands_seperator']=="true")
			{
				$thousands_sep=",";
			}

			if (isset($params['options']['normal_justify']))
			{
				$class[]='justify-'.$params['options']['normal_justify'];
			}

			if (isset($params['options']['normal_red_negative_numbers']) && $params['options']['normal_red_negative_numbers']=="true")
			{
				$red_negative_numbers=TRUE;
			}

			if (!empty($params['number']) && is_numeric($params['number']))
			{

				// we don't want to apply the nagative numbers check if we're printing... this needs to be done elsewhere
				if ($params['number']<0 && $red_negative_numbers=="true" && !isset($this->_data['ajax_print']))
				{
					$class[]='red';
					$output=number_format($params['number'],$decimals,$dec_point,$thousands_sep);
				}

				$output=number_format($params['number'],$decimals,$dec_point,$thousands_sep);

			}
			else
			{
				$output=$params['number'];
			}

			// if we're not ajax printing, wrap the output in a span element with class
			if (!isset($this->_data['ajax_print']))
			{
				$output='<span class="'.implode(" ",$class).'">'.$output.'</span>';
			}

		}
		else
		{
			// no formatting, just output the original number
			$output=$params['number'];
		}

		return $output;

	}

	protected function buildArray($data, $headings, $measure_fields, $aggregate_fields, $heading_keys, $field_formatting)
	{

		/*
		 * NOTE: througout this function we deal with arrays that may only have
		 * a key, or where the value is irrevelent to the context. Therefore we
		 * use the variable $blank to hold this useless value variable, allowing
		 * the key to remain the key.
		 */

		// list of arrays we want to flip
		$args = array(
			'measure_fields'	=> $measure_fields,
			'aggregate_fields'	=> $aggregate_fields
		);

		// loop through said arrays, flipping the key for the value
		foreach ($args as $key => $arr)
		{

			if (is_array($arr))
			{
				$$key = array_flip($arr);
			}

		}

		// turn the measure fields around
		$reverse_measure_fields = array_reverse($measure_fields, TRUE);

		// strip the value from the measure fields
		foreach ($measure_fields as $key => $value)
		{
			$measure_fields[$key] = '';
		}

		// create a total levels array
		$total_levels = $reverse_measure_fields;
		unset($total_levels['result']);

		// loop through the field formatting, removing the current item if
		// it doesn't exist as an aggregate field

		// ATTN: this is disabled, might enable to prevent formatting measures for example

		#foreach ($field_formatting as $key=>$value) {
		#	if (empty($value['normal_method']) || $value['normal_method']=='dont_total') {
		#		unset($field_formatting[$key]);
		#	}
		#}

		// set a few vars
		$data_arr		= array();
		$sub_total_keys	= array();
		$total_fields	= array();
		$row_counter	= 0;
		$col_counter	= 0;
		$counter		= 0;


		// don't get the collection headings... these will include filter fields
		// that we may not want to display
		#$fields=$collection->getHeadings();

		$fields = $headings;

		if (count($data) > 0)
		{

			foreach ($data as $model)
			{

				// set break level
				$break = '';

				// check for a break level

				// ATTN: should this be key=>value?!
				foreach ($fields as $key => $fieldname)
				{
					// less-than causes issues in XML/HTML
					$model[$key] = str_replace('<', '&#60;', (string) $model[$key] ?? '');	
					if ($break === '' && isset($measure_fields[$key]))
					{
						if ($model[$key] === '')
						{
							$model_value = 'None';
						}
						else
						{
							$model_value = $model[$key];
						}

						if ($measure_fields[$key] <> '' && $measure_fields[$key] <> $model_value)
						{
							$break = $key;
						}

					}

				}

				if ($break <> '')
				{

					// break found so output break totals
					$previous_measure='';

					foreach ($reverse_measure_fields as $measure_name => $blank)
					{

						// Roll Up totals from lower levels
						if ($previous_measure <> '')
						{

							foreach ($aggregate_fields as $aggregate_name => $blank)
							{
								$key				= $aggregate_name . $previous_measure;
								$previous_total		= $total_fields[$key];
								$total_fields[$key]	= 0;
								$key				= $aggregate_name.$measure_name;
								$new_total			= $total_fields[$key] + $previous_total;
								$total_fields[$key]	= $new_total;
							}

						}

						// output break on total row
						if ($break<>'' && !empty($aggregate_fields))
						{

							// START ROW

								foreach ($fields as $key => $fieldname)
								{

									if ($measure_name == $key)
									{
										$sub_total_keys[$row_counter]=true;
									}

									// OUTPUT THE TOTAL?

									$total_level			= $field_formatting[$key]['normal_total'];
									$display_total_level	= FALSE;

									if (!in_array($total_level, array("false", "none")))
									{

										if (isset($aggregate_fields[$field_slug]) && 
											(
												($total_level === 'report' || $total_level === TRUE) ||
												($measure_name !== 'report' && $total_levels[$total_level] <= $total_levels[$measure_name])
											)
										)
										{
											$display_total_level = TRUE;
										}

									}									

									// START CELL
										if ($measure_name == $key)
										{

											if ($measure_fields[$key] == 'None')
											{
												$data_arr[$row_counter][$heading_keys[$col_counter]] = 'Total';
											}
											else
											{
												$data_arr[$row_counter][$heading_keys[$col_counter]] = 'Total ' . $measure_fields[$key];
											}

										}
										elseif (isset($aggregate_fields[$key]) && $display_total_level === TRUE)
										{

											if (!empty($field_formatting[$key]))
											{

												$formatted_number=$this->reporting_number_format(
													array(
														'number'	=>	$total_fields[$key.$measure_name],
														'options'	=>	$field_formatting[$key]
													)
												);

												$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$formatted_number;

											}
											else
											{
												$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$total_fields[$key.$measure_name];
											}

										}
										else
										{
											$data_arr[$row_counter][$heading_keys[$col_counter]]='';
										}

									// END CELL
									$col_counter++;
								}

							// END ROW
							$row_counter++;
							$col_counter = 0;

							$previous_measure = $measure_name;

						}
						else
						{
							$previous_measure = '';
						}

						if ($break==$measure_name)
						{
							// At the break level so stop here 
							$break='';
						}

					}

				}
				// Now output the detail line * }
				// START ROW
					$break=FALSE;

					foreach ($fields as $field_slug => $fieldname)
					{
						$measure_field = '';
						if (isset($measure_fields[$field_slug]))
						{
							$measure_field=$field_slug;
						}
						else
						{

							// Add aggregate value to lowest break level total
							if (isset($aggregate_fields[$field_slug]))
							{
								$key				= $field_slug . $measure_field;
								$new_total			= $total_fields[$key] + $model[$field_slug];
								$total_fields[$key]	= $new_total;
							}

						}

						if (isset($measure_fields[$field_slug]) && $measure_fields[$field_slug]<>$model[$field_slug])
						{
							$break=TRUE;
						}

						// START CELL
							if (!isset($measure_fields[$field_slug]) || $break)
							{

								// Print the field value if it is not a break field or break has occurred at this or a higher level
								if (!empty($field_formatting[$field_slug]))
								{

									$formatted_number=$this->reporting_number_format(
										array(
											'number'	=>	$model[$field_slug],
											'options'	=>	$field_formatting[$field_slug]
										)
									);
									$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$formatted_number;

								}
								else
								{
									$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$model[$field_slug];
								}

							}
							else
							{
								$data_arr[$row_counter][$heading_keys[$col_counter]]='';
							}

						// END CELL
						$col_counter++;

						if (isset($measure_fields[$field_slug]))
						{

							if ($model[$field_slug]=='')
							{
								$measure_fields[$field_slug]='None';
							}
							else
							{
								$measure_fields[$field_slug]=$model[$field_slug];
							}

						}

					}

				// END ROW
				$row_counter++;
				$col_counter=0;

			} // this is just a closing foreach...

			// Now force break on report and output final totals

			$previous_measure='';

			if (!empty($aggregate_fields))
			{

				foreach ($reverse_measure_fields as $measure_name => $blank)
				{

					// keep a status to check if a total row has values
					// if it doesn't have any at the end, we don't output the line

					$total_row_has_values = FALSE;

					// Roll Up totals from lower levels
					if ($previous_measure <> '')
					{

						foreach ($aggregate_fields as $aggregate_name => $blank)
						{
							$key = $aggregate_name . $previous_measure;
							$previous_total = $total_fields[$key];
							$key = $aggregate_name . $measure_name;
							$new_total = $total_fields[$key] + $previous_total;
							$total_fields[$key] = $new_total;
						}

					}

					// START ROW

						$total_row_counter = 0;

						foreach ($fields as $field_slug => $fieldname)
						{

							$total_row_counter++;

							// OUTPUT THE TOTAL?

							$display_total_level	= FALSE;
							$total_level			= $field_formatting[$field_slug]['normal_total'];

							if (!in_array($total_level, array("false", "none")))
							{

								// the structure of this if statement is horrible
								if (isset($aggregate_fields[$field_slug]) && 
									(
										($total_level === 'report' || $total_level === TRUE) ||
										($measure_name !== 'report' && $total_levels[$total_level] <= $total_levels[$measure_name])
									)
								)
								{
									$display_total_level = TRUE;
								}

							}

							if ($measure_name==$field_slug || $measure_name=='report')
							{
								$sub_total_keys[$row_counter]=TRUE;
							}

							// START CELL
								if ($measure_name=='report' && $total_row_counter==1)
								{
									$data_arr[$row_counter][$heading_keys[$col_counter]]='Report Total';
								}
								elseif ($measure_name==$field_slug)
								{
									$data_arr[$row_counter][$heading_keys[$col_counter]]='Total '.$measure_fields[$field_slug];
								}
								elseif (isset($aggregate_fields[$field_slug]) && $display_total_level !== FALSE )
								{

									// we have a value!
									$total_row_has_values = TRUE;

									$key=$field_slug.$measure_name;

									$total_value = 0;

									// the key variable works fine if the field has a break on... but if there is now break
									// we're stuck without a total value, in that instance we would use just the field slug
									// to get the value

									if (isset($total_fields[$key]))
									{
										$total_value = $total_fields[$key];
									}
									elseif (isset($total_fields[$field_slug]))
									{
										$total_value = $total_fields[$field_slug];
									}

									if (isset($field_formatting[$field_slug]))
									{

										$formatted_number=$this->reporting_number_format(
											array(
												'number'	=>	$total_value,
												'options'	=>	$field_formatting[$field_slug]
											)
										);

										$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$formatted_number;

									}
									else 
									{
										$data_arr[$row_counter][$heading_keys[$col_counter]]=''.$total_value;
									}

								}
								else
								{
									$data_arr[$row_counter][$heading_keys[$col_counter]]='';
								}

							// END CELL
							$col_counter++;
						}
					// END ROW

					// a quick check to see if we should keep this total line
					if ($total_row_has_values === FALSE)
					{
						unset($data_arr[$row_counter]);
					}

					$row_counter++;
					$col_counter=0;
				$previous_measure=$measure_name;


				}

			}

		}
		else
		{
			return FALSE;
		}

		return array(
			'data_arr'			=>	$data_arr,
			'sub_total_keys'	=> 	$sub_total_keys
		);
	}

	public function getFilterData()
	{

		// store the ajax status in a different var, then unset the current one
		// we do this because we don't want the functions we all to get confused
		$ajax = isset($this->_data['ajax']);
		unset($this->_data['ajax']);

		// set vars
		$_tablename=$this->_data['tablename'];

		$fields=array();
		$fields['']='';

		foreach ($this->getColumns($_tablename) as $key => $value)
		{
			$fields[$key]=$value->name;
		};

		$this->view->set('options',$fields);
		$this->setTemplateName('select_options');

	}

}

// end of ReportsCollection.php
