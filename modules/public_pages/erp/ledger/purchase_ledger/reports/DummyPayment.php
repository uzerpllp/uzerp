<?php

class DummyPayment
{
    public $testprint = FALSE;
	public $no_output = true;


	function setData() {
		return;
	}
	
	function constructPrint() {
		return true;
	}

    function validate($data, &$errors)
	{
		
		$return			= true;
		
		if (isset($data['PLTransaction'])) 
		{
			$progressbar = new Progressbar('checking_supplier_details');
			
			$callback = function($unused, $supplier_id) use (&$errors)
			{
				
				$supplier = DataObjectFactory::Factory('PLSupplier');
				$supplier->load($supplier_id);
				
				if (!$supplier->isLoaded())
				{
					$errors[] = 'Error checking supplier';
					return false;
				}

				
			};
			
			$return = $progressbar->process($data['PLTransaction'], $callback);
			
		}
		
		return $return;

	}

}