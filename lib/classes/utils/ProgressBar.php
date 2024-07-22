<?php

/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class Progressbar
{

	protected $version = '$Revision: 1.2 $';

	private $name;

	function __construct($name)
	{
		$this->name = $name;

		$this->registerProgress();
	}

	function process($data, $callback)
	{
		$count = 0;

		$total_items = count($data);

		$this->registerProgress();

		foreach ($data as $id=>$value)
		{

			if (call_user_func($callback, $value, $id)===FALSE)
			{
				$this->registerProgress(-1);
				return FALSE;
			}

			$count++;

			$this->registerProgress(round($count*100/$total_items, 0));
		}
	}

	private function registerProgress ($value = 0)
	{
		$_SESSION[$this->name] = $value;

		// Need to close the session to write to the session file
		session_write_close();
		// Now need to re-start the session
		session_start();

	}

}

// end of Progressbar