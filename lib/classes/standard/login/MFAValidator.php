<?php
/**
 * Handle MFA Enrollment and Factor Validation
 * 
 * @author uzERP LLP
 * @license GPLv3 or later
 * @copyright (c) 2022 uzERP LLP (support#uzerp.com). All rights reserved.
 */

interface MFAValidator {
	public function Enroll(User $user, &$errors);
	public function VerifyEnroll(User $user, array $params, string $token, &$errors);
	public function ValidateToken(User $user, string $token, &$errors);
	public function ResetEnrollment(User $user, &$errors);
}
