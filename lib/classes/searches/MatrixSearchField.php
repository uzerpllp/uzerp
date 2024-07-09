<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class MatrixSearchField extends SearchField
{

	protected $version		= '$Revision: 1.6 $';
	protected $options		= array();
	protected $operators	= array(
		'='		=> 'Equals',
		'!='	=> 'Not Equals',
		'LIKE'	=> 'Contains',
		'>'		=> 'Greater Than',
		'>='	=> 'Greater Than or Equals To',
		'<'		=> 'Less Than',
		'<='	=> 'Less Than or Equal To'
	);
						
	public function toHTML()
	{
		
		$value = $this->value;
		
		if (!empty($value))
		{
			$value['count'] = count($value['field']);
		}
		else
		{
			
			/*
			 * if the array hasn't been sent, set the value to 1 
			 * and set some dummy data (or perhaps test in the loop 
			 * we need to set the loop at 1 to load the default single row.
			 */
			
			$value = array('', '', ''); // 
			$value['count'] = 1;
			
		}
		
		$html .= '<dd><div id="matrix_' . $this->fieldname . '">';
		
		for ($i= 0; $i <= $value['count'] - 1; $i++)
		{
			
			// generate the matrix line
			$html .= '<p id="p:' . $this->fieldname . ':' . $i . '" class="matrix_field">';
			
			// generate the field selector
			$html .= '    <select style="clear: both;" id="search:' . $this->fieldname . ':field:' . $i . '" name="Search[' . $this->fieldname . '][field][]">';
			
			foreach ($this->options as $val => $opt)
			{
				
				$selected = '';
				
				if ($value['field'][$i] == $val || (is_null($this->value) && $this->default == $val))
				{
					$selected = 'selected="selected"';
				}
				
				$html .= '        <option value="' . $val . '" ' . $selected . '>' . uzh(prettify($opt)) . '</option>';
			}
			
			$html .= '    </select>';
			
			// generate the operator selector
			$html.='    <select id="search:' . $this->fieldname . ':operator:' . $i . '" name="Search[' . $this->fieldname . '][operator][]">';

			foreach ($this->operators as $val => $opt)
			{
				
				$selected = '';
				
				if ($value['operator'][$i] == $val)
				{
					$selected = 'selected="selected"';
				}
				
				$html .= '        <option value="' . $val . '" ' . $selected . '>' . uzh(prettify($opt)) . '</option>';
			}
			$html .= '    </select>';
			
			// generate the value input
			$html .= '    <input id="search:' . $this->fieldname . ':value:' . $i . '" type="text" name="Search[' . $this->fieldname . '][value][]" value="' . $value['value'][$i] . '" />';
			
			$html .= '	<a href="#" class="remove_matrix" id="search:' . $this->fieldname . ':delete:' . $i . '"><img src="/assets/graphics/delete.png" /></a>';
			$html .= '</p>';
			
		}
		
		$html .= '</div>';
		$html .= '<p style="clear:both;"><a href="#" class="clone_matrix" rel="' . $this->fieldname . '"><img src="/assets/graphics/add.png" style="float: left; margin-right: 5px;"/>Add a new constraint</a></p>';
		$html .= '</dd><br />';
		
		return $this->labelHTML() . $html;
		
	}

	public function toConstraint()
	{
		
		$value = array();
		
		if ($this->value_set)
		{
			$value = $this->value;
		}
		else
		{
			$value = $this->default;
		}
		
		if (!empty($value) && $value != -1)
		{
			
			// construct the fields array
			foreach ($value['field'] as $key2 => $value2)
			{
				$fields[$value2][$key2] = $key2;
			}
			
			$c = new ConstraintChain();
			
			// loop through each field type
			foreach ($fields as $field_key => $field_value)
			{
				
				$cc = new ConstraintChain();
				
				// loop through each instace of a field
				foreach ($field_value as $key3 => $value3)
				{
					
					switch(strtolower($value['operator'][$key3]))
					{
						
						case "like":
							// jake actually posted a post on carsonified about speeding up like searches (vs ILIKE)
							// http://carsonified.com/blog/dev/databases/speed-up-your-web-app-by-1000-with-1-line-of-sql/
							$cc->add(new Constraint('lower(' . $field_key . ')', $value['operator'][$key3], '%' . strtolower($value['value'][$key3]) . '%'), 'OR');
							break;
							
						default:
							$cc->add(new Constraint($field_key, $value['operator'][$key3], $value['value'][$key3]), 'OR');
							break;
							
					}
					
				}
				
				$c->add($cc);
				
			}
			
			return $c;
			
		}
		
		return FALSE;
		
	}
	
	public function setOptions($options)
	{
		$this->options = $options;
	}
	
	public function isValid($val, &$errors = [])
	{
		return TRUE;
	}
	
}

// end of MatrixSearchField.php
