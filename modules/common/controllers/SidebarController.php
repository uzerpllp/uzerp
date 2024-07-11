<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SidebarController {

	protected $version = '$Revision: 1.20 $';
	
	private $components	= array();
	private $actions	= array('view', 'edit', 'delete');
	
	public function __construct(View &$view)
	{
		
		$view->set('sideBarTemplateName', 'elements/sidebar.tpl');
				
		$report		= new HasReport();
		$reports	= $report->getByPermission();
		
		if (!empty($reports))
		{
			
			$sidebar_reports = array();
			
			foreach ($reports as $report_id => $report)
			{
				
				$sidebar_reports[$report] = array(
					'tag'	=> $report,
					'link'	=> array(
						'module'		=> 'reporting',
						'controller'	=> 'reports',
						'action'		=> 'run',
						'id'			=> $report_id
					)
				);
			}
			
			$this->addList('reports', $sidebar_reports);
			
		}
		
	}

	public function addList($name, Array $data)
	{
		
		foreach ($data as $linkkey => $link)
		{
			
			if (is_array($link))
			{
				
				foreach ($link as $urlkey => $url)
				{
					// Sanitize the tag for display
					$data[$linkkey]['tag'] = uzh($data[$linkkey]['tag']);

					if (is_array($url) && !$this->checkPermissions($url))
					{
						unset($data[$linkkey][$urlkey]);
					}
					else
					{
						$data[$linkkey][$urlkey] = $url;
					}
					
				}
				
				if (count($data[$linkkey]) == 1)
				{
					// All links removed so remove Tag (link title)
					unset($data[$linkkey]);
				}
				
			}
			
		}
		
		// Only add to sidebar if user has permissions to one or more items in the sidebar
		if (!empty($data))
		{
			
			if (isset($this->components[$name]))
			{
				$this->components[$name]['data'] += $data;
			}
			else
			{
				$this->components[$name] = array('type' => 'list', 'data' => $data);
			}
			
		}
		
	}
	
	public function addCurrentBox($name, $title, Array $url)
	{

		foreach ($this->actions as $action)
		{
			
			$url['action'] = $action;
			unset($url['pid']);
			
			if ($this->checkPermissions($url))
			{
				$this->components[$name]['data'][$action]	= $url;
			}
			
		}
		
		if (isset($this->components[$name]))
		{
			$this->components[$name]['type']	= 'currentBox';
			$this->components[$name]['title']	= $title;
		}
		
	}
	
	public function addCompanySelector($companies)
	{
		$this->components['company_selector'] = array('type' => 'companySelector', 'data' => $companies);
	}
	
	public function addCalendar()
	{
		$this->components['calendar'] = array('type' => 'calendar');
	}	
		
	public function addSearch(BaseSearch $search)
	{
		$this->components['search'] = array('type' => 'search', 'data' => $search);
	}
	
	public function display($params, &$smarty)
	{
		
		$type	= $params['type'];
		$fn		= 'display' . ucfirst($type);
		
		$this->$fn($params, $smarty);
		
	}

	public function displayList($params, &$smarty)
	{
		
		$smarty->assign('sidebar_list_data', $this->components[$params['name']]['data']);
		$smarty->display('elements/list.tpl', md5(EGS_COMPANY_ID . $params['name'] . EGS_USERNAME . $_SERVER['REQUEST_URI']));
		
	}

	function displayCurrentBox($params, &$smarty)
	{
		
		if (count($this->components[$params['name']]) > 0)
		{
			$smarty->assign('components', $this->components[$params['name']]);
		}
		
		$smarty->display('elements/linkbox.tpl', md5(EGS_COMPANY_ID . $params['name'].EGS_USERNAME));
		
	}

	public function displayCompanySelector($params, &$smarty)
	{
		
		$results = $this->components['company_selector']['data'];
		
		$smarty->assign('companyselector', $results);
		$smarty->display('elements/companyselector.tpl');
		
	}

	public function displayCalendar($params, &$smarty)
	{
		$smarty->display('elements/calendar.tpl');
	}

	public function displaySearch($params, &$smarty)
	{
		$smarty->assign('search', $this->components['search']['data']);
		$smarty->display('elements/search.tpl');
	}

	public function getComponents()
	{
		
		// NOTE: related items should probably be set here too 
		
		// add any additional components
		$this->get_related_menu_items();
		
		
		foreach ($this->components as $name=>$data)
		{
			// remove any 'spacer' elements from the start
			foreach ($data['data'] as $key=>$value)
			{
				if (is_array($value))
				{
					break;
				}
				elseif ($value == 'spacer')
				{
					unset($this->components[$name]['data'][$key]);
				}
			}
			
			$count = count($this->components[$name]['data']);
			
			// remove any adjacent 'spacer' elements
			for ( $i = 0; $i < $count; $i++)
			{
				if (array_key_exists($i, $this->components[$name]['data']) && !is_array($this->components[$name]['data'][$i])
					&& $this->components[$name]['data'][$i] == 'spacer'
					&& !is_array($this->components[$name]['data'][$i+1])
					&& $this->components[$name]['data'][$i+1] == 'spacer')
				{
					unset($this->components[$name]['data'][$i]);
				}
			}
			
			// remove 'spacer' element from end
			if (is_array($this->components[$name]['data']))
			{
				$last = end($this->components[$name]['data']);
				if (!is_array($last) && $last == 'spacer')
				{
					array_pop($this->components[$name]['data']);
				}
			}
			
			// if no actual data, then remove this component
			if (count($this->components[$name]['data']) == 0)
			{
				unset($this->components[$name]);
			}
		}
	
		// return components
		return $this->components;
		
	}
	
	private function get_related_menu_items()
	{
		
		$menu_items = array();
		
		if (!isset($_GET['pid']))
		{
			$ao		= AccessObject::Instance();
			$pid	= $ao->getPermission($_GET['module'], $_GET['controller'], $_GET['action']);
		}
		else
		{
			$pid	= $_GET['pid'];
		}
		
		$permission = new Permission();
		$permissions = new PermissionCollection();
		$sh = new SearchHandler($permissions, FALSE);

		$sh->addConstraint(new Constraint('parent_id', '=', $pid));
		$sh->addConstraint(new Constraint('display_in_sidebar', 'IS', 'true'));
		
		$data = $permissions->load($sh, null, RETURN_ROWS);
		
		if (!empty($data))
		{
			
			foreach ($data as $item)
			{
				
				$link_array = array();
				
				foreach ($permission->build_link($item['id']) as $key => $value)
				{
					$link_array[$key] = $value;
				}
				
				$menu_items[] = array(
					'tag'	=> $item['title'],
					'link'	=> $link_array
	        	);
	        	
			}
			
		}
		
		if (!empty($menu_items))
		{
			$this->addList('Related Menu Items', $menu_items);
		}        
		
	}

	private function checkPermissions(&$link)
	{
		
		if (isset($link['modules']))
		{
			$modules = $link['modules'];
		}
		elseif (isset($link['module']))
		{
			$modules = $link['module'];
		}
		else
		{
			$modules = '';
		}
		
		if (isset($link['controller']))
		{
			$controller = $link['controller'];
		}
		else
		{
			$controller = '';
		}
		
		if (isset($link['action']) && strtolower($link['action']) == 'printdialog' && isset($link['printaction']))
		{
			$action = $link['printaction'];
		}
		elseif (isset($link['action']))
		{
			$action = $link['action'];
		}
		else
		{
			$action = '';
		}
		
		if (is_array($modules))
		{
			$module = current($modules);
		}
		else
		{
			$module = $modules;
		}
		
		$ao = AccessObject::Instance();
		
		$pid = $ao->getCache($module, $controller, $action);
		
		if ($pid)
		{
		
			if (empty($link['pid']))
			{
				$link['pid'] = $pid;
			}
			
			return TRUE;
		}
		
		if (empty($link['pid']))
		{
			$link['pid'] = $ao->getPermission($modules, $controller, $action);
		}
		
		$pid = $link['pid'];
		
		if ($ao->hasPermission($modules, $controller, $action, $pid))
		{
		
			$ao->saveCache($module, $controller, $action, $pid);
		
			return TRUE;
		}
		else
		{
			return FALSE;
		}
		
	}

}

// end of SidebarController.php