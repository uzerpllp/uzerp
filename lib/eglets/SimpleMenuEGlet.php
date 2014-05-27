<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SimpleMenuEGlet extends SimpleListEGlet
{

	protected $version='$Revision: 1.16 $';
	
	protected $template='menu_eglet.tpl';
	
	function populate()
	{
		
	}


	function setMenuData($module, $pid)
	{
		
		$ao = AccessObject::instance();
		
		if (empty($pid))
		{
			$pid = $ao->getPermission($module);
		}
		
		if (!empty($pid))
		{
			$this->contents = $this->getMenuLinks($ao->tree, $pid);
		}
		else
		{
			$this->contents = array();
		}
		
	}
	
	private function getMenuLinks($tree, $pid, $level = 1)
	{
		$menu = array();
		
		foreach ($tree[$pid] as $item)
		{
			
			$item['link'] += array('pid'=>$item['id']);
			switch ($item['type'])
			{
				case 'm':
					$permission = $item['link']['module'];
					break;
				case 'c':
					$permission = $item['link']['controller'];
					break;
				case 'a':
					$permission = $item['link']['action'];
					break;
			}
			
			$menu[$item['id']]['main'] = new MenuLink('?'.setParamsString($item['link']), $level, $item['type'], $item['title'], $this->getIcons($item['type'], $permission));
			
			if (isset($tree[$item['id']]))
			{
				$menu[$item['id']]['sub'] = $this->getMenuLinks($tree, $item['id'], $level+1);
			}
			 
		}
		
		return $menu;
	}

	private function getIcons ($type, $name)
	{
		
		$icons=array('menu_closed_nofocus'=>''
					,'menu_closed_focus'=>''
					,'menu_open_nofocus'=>''
					,'menu_open_focus'=>'');
		
		foreach ($icons as $key=>$value)
		{
			if (file_exists(THEME_ROOT.THEME.'/graphics/'.$key.'.png'))
			{
				$icons[$key]=THEME_URL.THEME.'/graphics/'.$key.'.png';
			}
		}
		
		if (file_exists(THEME_ROOT.THEME.'/graphics/menu_noexpand.png'))
		{
			$icons['menu_noexpand']=THEME_URL.THEME.'/graphics/menu_noexpand.png';
		}
		
		$permission=DataObjectFactory::Factory('Permission');
		
		$icons['icon']=$permission->getIcon($type, $name);
		
		return $icons;
	}
	
}

class MenuLink
{
	public $link;
	public $level;
	public $type;
	public $title;
	public $icons;
	
	function __construct($link, $level, $type, $title, $icons=array())
	{
		$this->link=$link;
		$this->level=$level;
		$this->type=$type;
		$this->title=$title;
		$this->icons=$icons;
	}

}

// End of SimpleMenuEGlet
