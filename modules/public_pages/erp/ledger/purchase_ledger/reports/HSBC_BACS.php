<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class HSBC_BACS
{

	protected $version = '$Revision: 1.18 $';
	
	private $bacs_reference;
	private $account;
	private $payments;
	private $plpayment;
	private $create_date;
	private $processing_date;
	private $expiry_date;
	
	public $testprint = FALSE;
	public $defaultfilename;
	protected $controller;
	
	function __construct(&$_this)
	{
		
		// we're not extending an object, so let's get the callee model (printController) in to access it's methods
		$this->controller = $_this;
		
	}
	
	function constructPrint($data, $options)
	{
		
		$options['txtArray'] = $this->formatReport();
		
		$response = json_decode($this->controller->constructOutput($data, $options));
		
		return $response->status;
		
	}
	
	function validate($data, &$errors)
	{
		
		$return			= TRUE;
		$today			= fix_date(date(DATE_FORMAT));
		$payment_date	= fix_date($data['payment_date']);
		$process_date	= $this->getProcessDate($payment_date);
		
		if ($process_date <= $today)
		{
			$errors[]	= 'Payment Date must be at least 2 working days from today';
			$return		= FALSE;
		}
		
		if (in_array(date('D', strtotime($payment_date)), array('Sat', 'Sun')))
		{
			$errors[]	= 'Payment Date cannot be Saturday or Sunday';
			$return		= FALSE;
		}
		
		if (isset($data['PLTransaction'])) 
		{
			$progressbar = new progressBar('checking_supplier_details');
			
			$callback = function($unused, $supplier_id) use (&$errors)
			{
				
				$supplier = DataObjectFactory::Factory('PLSupplier');
				$supplier->load($supplier_id);
				
				if (!$supplier->isLoaded())
				{
					$errors[] = 'Error checking supplier';
					return FALSE;
				}
				elseif (!is_numeric($supplier->sort_code)
						|| strlen($supplier->sort_code) != 6
						|| !is_numeric($supplier->account_number)
						|| strlen($supplier->account_number) != 8)
				{
						
					$errors[$supplier_id]	 = 'Invalid Bank Account details for ' . $supplier->name;
					$errors[$supplier->name] = $supplier->name . ' Sort Code:' . $supplier->sort_code . ' Account Number:' . $supplier->account_number;
						
					return FALSE;
						
				}
				
			};
			
			$return = $progressbar->process($data['PLTransaction'], $callback);
			
		}
		
		return $return;

	}
	
	function setData($bacs_reference, $payments, &$errors = array(), $data, $plpayment)
	{
		
		$this->bacs_reference	= $bacs_reference;
		$this->payments			= $payments;
		$this->plpayment		= $plpayment;
		$account				= DataObjectFactory::Factory('CBAccount');
		
		if (isset($data['cb_account_id']))
		{
			$account->load($data['cb_account_id']);
		}
		
		$this->account		= $account;
		$userPreferences	= UserPreferences::instance(EGS_USERNAME);
        $defaultPrinter		= $userPreferences->getPreferenceValue('default_printer','shared'); 
        $params				= $data['print'];
        $print_params		= array();
        $params['printer']	= (empty($data['printer'])?$defaultPrinter:$data['printer']);
        
		if (!$this->controller->setPrintParams($params, $print_params, $errors))
		{
			$errors[] = 'Failed to set print parameters';
		}

		$year				= date('y');
		$create_date		= bcadd(date('z'), 1, 0);
		$this->create_date	= $year . str_pad($create_date, 3, '0', STR_PAD_LEFT);
		$expiry_date		= bcadd(date('z', strtotime($this->plpayment->payment_date)), 1, 0);

		$processing_date = bcadd(date('z', strtotime($this->getProcessDate($plpayment->payment_date))), 1, 0);

		$this->processing_date	= $year . str_pad($processing_date, 3, '0', STR_PAD_LEFT);
		$this->expiry_date		= $year . str_pad($expiry_date, 3, '0', STR_PAD_LEFT);
		
		if ($plpayment->override==='f')
		{
			$this->validate(
				array(
					'payment_date' => un_fix_date($plpayment->payment_date)
				),
				$errors
			);
		
			if ($this->processing_date <= $this->create_date)
			{
				$errors[] = 'Invalid Processing Date';
			}
		}
		
	}

	private function getProcessDate($payment_date)
	{
		
		$payment_date	= strtotime($payment_date);
		$day			= date('D', $payment_date);
		
		switch ($day)
		{
			
			case 'Mon':
				$process_date = fix_date(date(DATE_FORMAT,strtotime('-3 days', $payment_date)));
				break;
				
			case 'Sun':
				$process_date = fix_date(date(DATE_FORMAT,strtotime('-2 days', $payment_date)));
				break;
				
			default:
				$process_date = fix_date(date(DATE_FORMAT,strtotime('-1 days', $payment_date)));
				
		}
		
		return $process_date;
		
	}
	
	private function formatReport()
	{
		
		$lines	= array();
		$payer	= $this->controller->getCompany();
		$db		= DB::Instance();
		
		$ssn		= '99' . str_pad($db->GenID('bacs_file_id_seq'), 4, '0', STR_PAD_LEFT);
		$lines[]	= 'VOL1'.$ssn.str_pad('HSBC  ', 27, ' ', STR_PAD_LEFT) . str_pad('1', 43, ' ', STR_PAD_LEFT);
		$hdrid1		= 'A      S  1      ' . $ssn . '00010001       ' . $this->create_date . ' ' . $this->expiry_date . ' 000000' . str_pad(' ', 20, ' ', STR_PAD_LEFT);
		$lines[]	= 'HDR1' . $hdrid1; 		
		$hdrid2		= 'F0200000100' . str_pad('00', 37, ' ', STR_PAD_LEFT) . str_pad(' ', 28, ' ', STR_PAD_LEFT);
		$lines[]	= 'HDR2' . $hdrid2;
		$lines[]	= 'UHL1 ' . $this->processing_date . '999999    000000001 DAILY  001' . str_pad(' ', 40, ' ', STR_PAD_LEFT);
		
		$cols = array(
			'sort_code'				=> array(),
			'account_number'		=> array(),
			'trans_type'			=> array('value' => '099'),
			'payer_sort_code'		=> array('value' => $this->account->bank_sort_code),
			'payer_account_number'	=> array('value' => $this->account->bank_account_number),
			'fill1'					=> array('value' => '    '),
			'negate'				=> array('value' => array('base_gross_value', 2, FALSE, TRUE, 11)),
			'payer_name'			=> array('value' => substr($this->account->bank_account_name, 0, 18)),
			'our_reference'			=> array(),
			'payee_name_check'		=> array(),
			'pay_date'				=> array('value' => ' ' . $this->processing_date)
		);
					
		foreach ($this->payments as $payment)
		{
			
			$line = $this->controller->construct_line($payment, $cols);
			
			$lines[] = $line['sort_code']
				.$line['account_number']
				.$line['trans_type']
				.$line['payer_sort_code']
				.$line['payer_account_number']
				.$line['fill1']
				.$line['negate']
				.$line['payer_name']
				.str_pad($line['our_reference'], 18, ' ', STR_PAD_RIGHT)
				.str_pad(substr($line['payee_name_check'], 0, 18), 18, ' ', STR_PAD_RIGHT)
				.$line['pay_date'];
			
		}

		$lines[] = $this->account->bank_sort_code
			.$this->account->bank_account_number
			.'017'
			.$this->account->bank_sort_code
			.$this->account->bank_account_number
			.'    '
			.str_pad(bcmul($this->plpayment->payment_total, 100, 0), 11, '0', STR_PAD_LEFT)
			.str_pad(substr($this->plpayment->reference, 0, 18), 18, ' ', STR_PAD_RIGHT)
			.str_pad('CONTRA', 18, ' ', STR_PAD_RIGHT)
			.substr($this->account->bank_account_name, 0, 18)
			.' ' . $this->processing_date;
			
		$lines[] = 'EOF1' . $hdrid1;
		$lines[] = 'EOF2' . $hdrid2;
		
		$lines[] = 'UTL1'
			.str_pad(bcmul($this->plpayment->payment_total, 100, 0), 13, '0', STR_PAD_LEFT)
			.str_pad(bcmul($this->plpayment->payment_total, 100, 0), 13, '0', STR_PAD_LEFT)
			.'0000001' . str_pad($this->plpayment->number_transactions, 7, '0', STR_PAD_LEFT)
			.str_pad(' ', 36, ' ', STR_PAD_LEFT);
		 		
		return $lines;
		
	}
	
}

// end of HSBC_BACS.php