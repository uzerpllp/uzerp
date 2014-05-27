<?php

/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class SetupController extends MasterSetupController
{
	
	protected $version='$Revision: 1.10 $';
	
	protected $setup_options = array('hr_authorisers'			=> 'HRAuthoriser'
									,'employee_grades'			=> 'EmployeeGrade'
									,'employee_pay_frequencies'	=> 'EmployeePayFrequency'
									,'employee_payment_types'	=> 'EmployeePaymentType'
									,'hour_groups'				=> 'HourTypeGroup'
									,'hour_types'				=> 'HourType'
									,'hour_payment_types'		=> 'HourPaymentType'
									,'training_objectives'		=> 'TrainingObjective'
									);
	
}

// End of HR:SetupController
