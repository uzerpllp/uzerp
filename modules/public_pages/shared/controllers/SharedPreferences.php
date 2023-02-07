<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

class SharedPreferences extends ModulePreferences
{

	protected $version = '$Revision: 1.13 $';

	function __construct($getCurrentValues = true, $model = 'UserPreferences', $username = EGS_USERNAME)
	{
		parent::__construct();

		$userPreferences = $model::instance($username);

		$this->setModuleName('shared');

// items-per-page
		if ($getCurrentValues)
		{
			$num_items = $userPreferences->getPreferenceValue('items-per-page', 'shared');
		}
		else
		{
			$num_items = 10;
		}

		$this->registerPreference(
			array(
				'name'			=> 'items-per-page',
				'display_name'	=> 'Items to display per page',
				'type'			=> 'select',
				'value'			=> $num_items,
				'data'			=> array(
										array('label'=>5,'value'=>5),
										array('label'=>10,'value'=>10),
										array('label'=>15,'value'=>15),
										array('label'=>20,'value'=>20),
										array('label'=>25,'value'=>25),
										array('label'=>30,'value'=>30),
										array('label'=>35,'value'=>35),
										array('label'=>40,'value'=>40),
										array('label'=>45,'value'=>45),
										array('label'=>50,'value'=>50),
									),
				'default'		=> '10',
				'position'		=> 1
			)
		);

// default_printer
		$printerlist = array();

		foreach (printController::selectPrinters() as $key=>$printer)
		{
			$printerlist[] = array('label'=>$printer, 'value'=>$key);
		}

		if ($getCurrentValues)
		{
			$current_printer = $userPreferences->getPreferenceValue('default_printer', 'shared');
		}
		else
		{
			$current_printer = '';
		}

		$this->registerPreference(
			array(
				'name'			=> 'default_printer',
				'display_name'	=> 'Default Printer',
				'type'			=> 'select',
				'value'			=> $current_printer,
				'data'			=> $printerlist,
				'default'		=> '',
				'position'		=> 2
			)
		);

// password change
		if ($username == EGS_USERNAME)
		{
			$this->registerField(
				array(
					'name'			=> 'current_password',
					'display_name'	=> 'Current Password',
					'type'			=> 'password',
					'value'			=> '',
					'position'		=> 3
				)
			);
			$this->registerHandledPreference(
				array(
					'name'			=> 'new_password',
					'display_name'	=> 'New Password',
					'type'			=> 'password',
					'value'			=> '',
					'position'		=> 4,
					'callback'		=> 'changePassword'
				)
			);
			$this->registerField(
				array(
					'name'			=> 'confirm_password',
					'display_name'	=> 'Confirm Password',
					'type'			=> 'password',
					'value'			=> '',
					'position'		=> 5
				)
			);
		}

// pdf-preview
		if ($getCurrentValues)
		{
			$pdf_preview			= $userPreferences->getPreferenceValue('pdf-preview', 'shared');
		}
		else
		{
			$pdf_preview = 'off';
		}

		$this->registerPreference(
			array(
				'name'			=> 'pdf-preview',
				'display_name'	=> 'Enable PDF Preview',
				'type'			=> 'checkbox',
				'status'		=> (empty($pdf_preview) || $pdf_preview == 'off') ? 'off' : 'on',
				'default'		=> 'off',
				'position'		=> 6
			)
		);

// default_page
		$modulelist = array();

		// Get modules user has access to
		$ao = AccessObject::instance();

		$per = DataObjectFactory::Factory('Permission');

		$permissions = $this->getUserModules($username);

		if (!empty($permissions))
		{
			foreach ($permissions as $permission)
			{
				$modulelist[] = array('label'=>$permission['title'], 'value'=>strtolower($per->getEnum('type', $permission['type'])).','.$permission['permission']);
			}
		}

		if ($getCurrentValues)
		{
			$default_page = $userPreferences->getPreferenceValue('default_page', 'shared');
		}
		else
		{
			$default_page = '';
		}

		$this->registerPreference(
			array(
				'name'			=> 'default_page',
				'display_name'	=> 'Home page',
				'type'			=>'select',
				'value'			=> $default_page,
				'data'			=> $modulelist,
				'default'		=> '',
				'position'		=> 8
			)
		);

	}

	public function changePassword($data)
	{
		$current_password	= $data['current_password'];
		$new_password		= $data['new_password'];
		$confirm_password	= $data['confirm_password'];

		$user = new User();
		$cc = new ConstraintChain();
		$cc->add(new Constraint('username','=', EGS_USERNAME));

		$result = $user->loadBy($cc);
		$current_match = password_verify($current_password, $result->password);

		if($result !== FALSE && $current_match === TRUE && $new_password === $confirm_password && strlen($new_password) >= 10)
		{
			User::updatePassword($new_password, EGS_USERNAME);
		}
		else
		{
			$flash = Flash::Instance();
			$flash->addError('Please check your current password is correct, and that you have typed your new password correctly both times');
			$flash->addError('Passwords must be at least 10 characters long');
			return FALSE;
		}
	}

	/**
	 * Get modules available to user
	 *
	 * @param string $username
	 * @return void
	 */
	private function getUserModules($username = EGS_USERNAME)
	{
		// Get the roles for the user
		$hasrole = DataObjectFactory::Factory('HasRole');
		$roles = $hasrole->getRoleID($username);
		$hp	= new HasPermissionCollection;
		$cc = new ConstraintChain;
		$cc->add(new Constraint('type', '=', 'm'));
		$cc->add(new Constraint('display', 'is', true));
		if (!empty($roles))	{
			$cc->add(new Constraint('roleid', 'in', '(' . implode(',', $roles) . ')'));
		}
		
		$sh = new SearchHandler($hp, false);
		$sh->addConstraintChain($cc);
		$sh->setOrderby('title');
		
		return $hp->load($sh, null, RETURN_ROWS);
	}
}

// End of SharedPreferences
