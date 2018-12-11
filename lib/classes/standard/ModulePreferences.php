<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class ModulePreferences {
	
	protected $version='$Revision: 1.8 $';
	
	private $moduleName;
	private $preferences;
	private $additionalFields;
	private $handledPreferences;

	function __construct()
	{
		
		$system = System::Instance();
		
		$this->view = $system->view;
		
		$this->preferences = array();
		
		$this->additionalFields=array();
		
		$this->handledPreferences=array();
		
	}
	
	public function registerPreference($hash)
	{
		$this->preferences[$hash['name']] = $hash;
	}

	protected function registerField($hash)
	{
		$this->additionalFields[$hash['name']]=$hash;
	}

	protected function registerHandledPreference($hash)
	{
		$this->handledPreferences[$hash['name']]=$hash;
	}

	public function setModuleName($moduleName)
	{
		$this->moduleName = $moduleName;
	}
	
	public function generateTemplate()
	{
		
		$html = '';

		$data = array();
		
		$data['attrs']['name']	= '__moduleName';
		$data['attrs']['id']	= '__moduleName_id';
		$data['attrs']['type']	= 'hidden';
		$data['attrs']['value'] = $this->moduleName;
		
		$data['attrs']			= build_attribute_string($data['attrs']);
		$data['label']['attrs']	= build_attribute_string($data['label']['attrs']);
		
		$this->view->set('function_input', $data);
		
		$html .= $this->view->fetch('smarty/function.input');
		
		$fields = array_merge($this->preferences,$this->additionalFields,$this->handledPreferences);
		
		usort($fields,array('ModulePreferences','sortOnPosition'));
		
		foreach ($fields as $preference)
		{
			switch($preference['type'])
			{
				case 'select_multiple':

					$data = array();
					$data['select']['attrs']['name'] = $preference['name'].'[]';
					$data['select']['attrs']['multiple'] = 'multiple';
					$data['select']['attrs']['id'] = $preference['name'].'_id';
					$data['dt']['attrs']['class'] = 'for_multiple';
					$data['dd']['attrs']['class'] = 'for_multiple';
					
					if (isset($preference['display_name']))
					{
						$data['select']['label'] = $preference['display_name'];
					}
					else
					{
						$data['select']['label'] = $preference['name'];
					}
					
					$data['select']['label'] = $preference['display_name'];
					
					$data['select']['id'] = $preference['name'];
					
					if (isset($preference['value']))
					{
						$selected = $preference['value'];
					}
					else
					{
						$selected = '';
					}

					foreach ($preference['data'] as $index=>$option)
					{
						
						$option_attrs = array();
						
						$key = $option['value'];
						
						$option_attrs['value'] = uzh($key, ENT_COMPAT);
						
						if (isset($option['selected']) && $option['selected'])
						{
							$option_attrs['selected'] = 'selected';
						}
						
						$data['select']['options'][$index]['attrs'] = build_attribute_string($option_attrs);
						$data['select']['options'][$index]['value'] = uzh($option['label']);
					}
					
					$data['display_tags'] = TRUE;
					
					$data['select']['attrs'] = build_attribute_string($data['select']['attrs']);
					$data['dt']['attrs'] = build_attribute_string($data['dt']['attrs']);
					$data['dd']['attrs'] = build_attribute_string($data['dd']['attrs']);
					
					$this->view->set('function_select', $data);
					
					$html .= $this->view->fetch('smarty/function.select');
					
					break;
					
				case 'select':

					$data = array();
					$data['select']['attrs']['name'] = $preference['name'];
					$data['select']['attrs']['id'] = $preference['name'].'_id';
					
					if (isset($preference['display_name']))
					{
						$data['select']['label'] = $preference['display_name'];
					}
					else
					{
						$data['select']['label'] = $preference['name'];
					}
					
					$data['select']['id'] = $preference['name'];
					
					if (isset($preference['value']))
					{
						$selected = $preference['value'];
					}
					else
					{
						$selected = '';
					}

					foreach ($preference['data'] as $index=>$option)
					{
						
						$option_attrs = array();
						
						$key = $option['value'];
						
						$option_attrs['value'] = uzh($key, ENT_COMPAT);
						
						if ((is_array($selected) && in_array($key, $selected)) || ($selected==$key))
						{
							$option_attrs['selected'] = 'selected';
						}
						
						$data['select']['options'][$index]['attrs'] = build_attribute_string($option_attrs);
						$data['select']['options'][$index]['value'] = uzh($option['label']);
					}
					
					$data['display_tags'] = TRUE;
					
					$data['select']['attrs'] = build_attribute_string($data['select']['attrs']);
					
					$this->view->set('function_select', $data);
					
					$html .= $this->view->fetch('smarty/function.select');
					
					break;
					
				case 'checkbox':

					$data = array();
					
					$data['attrs']['name'] = $preference['name'];
					$data['attrs']['id'] = $preference['name'].'_id';
					$data['label']['attrs']['for'] = $preference['name'];
					
					if (isset($preference['display_name']))
					{
						$data['label']['value'] = $preference['display_name'];
					}
					
					if (isset($preference['status']) && $preference['status'] == 'on')
					{
						$data['attrs']['checked'] = 'checked';
					}
					
					$data['attrs']['type'] = 'checkbox';
					$data['attrs']['class'] = 'checkbox';
					
					$data['display_tags'] = TRUE;
					$data['display_label'] = TRUE;
					
					$data['attrs'] = build_attribute_string($data['attrs']);
					$data['label']['attrs'] = build_attribute_string($data['label']['attrs']);
					
					$this->view->set('function_input', $data);
					
					$html .= $this->view->fetch('smarty/function.input');
					break;
					
				case 'numeric':
				case 'password':
				case 'text':

					$data = array();
					
					$data['attrs']['name'] = $preference['name'];
					$data['attrs']['id'] = $preference['name'].'_id';
					$data['label']['attrs']['for'] = $preference['name'];
					
					if (isset($preference['display_name']))
					{
						$data['label']['value'] = $preference['display_name'];
					}
					
					if (isset($preference['value']))
					{
						$data['attrs']['value'] = $preference['value'];
					}
					
					$data['attrs']['type'] = ($preference['type']=='password')?'password':'text';
					$data['attrs']['class'] = ($preference['type']=='numeric')?' class="numeric"':'';
					
					$data['display_tags'] = TRUE;
					$data['display_label'] = TRUE;
					
					$data['attrs'] = build_attribute_string($data['attrs']);
					$data['label']['attrs'] = build_attribute_string($data['label']['attrs']);
					
					$this->view->set('function_input', $data);
					
					$html .= $this->view->fetch('smarty/function.input');
					
			}
		}

		return $html;
		
	}
	
	public function getPreferenceNames()
	{
		return array_keys($this->preferences);
	}

	public function getHandledPreferences()
	{
		return $this->handledPreferences;
	}

	public function getPreference($preferenceName)
	{
		if (isset($this->preferences[$preferenceName]))
		{
			return $this->preferences[$preferenceName];
		}
		else
		{
			return array();
		}
	}
	
	public function getPreferenceDefault($preferenceName)
	{
		if (isset($this->preferences[$preferenceName]))
		{
			return $this->preferences[$preferenceName]['default'];
		}
		else
		{
			return array();
		}
	}

	public static function sortOnPosition($a,$b)
	{
		if ( ( !isset($a['position'])&&!isset($b['position']) ) || $a['position']==$b['position'] )
		{
			return 0;
		}
		
		if(!isset($a['position']) || $a['position'] > $b['position'])
		{
			return 1;
		}
		
		return -1;
	}

}

// End of ModulePreferences
