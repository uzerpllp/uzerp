<?php
/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Component\HttpFoundation\Cookie;

//set some defaults
date_default_timezone_set('Europe/London');	//probably needs to be configurable
bcscale(2);	//default to 2dp on BC operations, defaults to 0 otherwise

showtime('post-library-load');

//set_error_handler("systemError");
//set_error_handler("systemError", E_ERROR);

//date format
//@todo move to user preferences
$dateFormat		= "d/m/Y";
$dateTimeFormat	= "d/m/Y H:i";

define('DATE_FORMAT', $dateFormat);
define('DATE_TIME_FORMAT', $dateTimeFormat);

define ('DB_DATE_FORMAT', "Y-m-d");
define ('DB_DATE_TIME_FORMAT', "Y-m-d H:i");

// TODO: Remove these constatnts as they don't seem to be used [SB]
//define('PUBLISH_HOST', '');
//define('PUBLISH_PORT', 8091);
//define('PUBLISH_URL', '_rpc/RPC2.php');

define('SEARCH_SESSION_IDLE', 20);

function add ($value1, $value2)
{

	// php error - adding 0.09+0.01 gives 0.0:
	if (($value1 == 0.09 && $value2 == 0.01) || ($value1 == 0.01 && $value2 == 0.09))
	{
		return 0.1;
	}
	else
	{
		return $value1 + $value2;
	}

}

function array2xml($array, $xml = FALSE, $parent = '')
{

	if ($xml === FALSE)
	{
		$xml = new SimpleXMLElement('<root/>');
	}

	foreach ($array as $key => $value)
	{

		if (is_array($value))
		{

			if(!is_numeric($key) && is_numeric(key($value)))
			{
				array2xml($value, $xml, $key);
			}
			elseif (is_numeric($key))
			{
				array2xml($value, $xml->addChild($parent));
			}
			else
			{
				array2xml($value, $xml->addChild($key));
			}

		}
		else
		{
			$xml->addChild($key, $value);
		}
	}

	return $xml->asXML();

}

function audit($msg)
{

	if (defined('AUDIT') && AUDIT)
	{

		$db			= DB::Instance();
		$debug		= $db->debug;
		$db->debug	= FALSE;
		$audit		= Audit::Instance();

		$audit->write($msg, TRUE, (microtime(TRUE) - START_TIME));
		$db->debug($debug);

	}

}

function debug($msg)
{

	if (defined('DEBUG') && DEBUG)
	{

		$db			= DB::Instance();
		$db->debug	= FALSE;
		$debug		= Debug::Instance();

		$debug->write($msg);
		$db->debug(DEBUG);

	}

}

function system_email($subject, $body, &$errors = [])
{
	$email = get_config('ADMIN_EMAIL');
	$from = get_config('ADMIN_FROM_EMAIL');

	if (!empty($email) && !get_config('DEV_PREVENT_EMAIL'))
	{

		$mailer_conf = get_config('PHPMAILER_CONF');
        $mail = new PHPMailer(true);

		if (is_array($mailer_conf)) {
			foreach ($mailer_conf as $conf => $val) {
				if ($conf == 'isSMTP') {
					$mail->isSMTP();
					continue;
				}

				$mail->$conf = $val;
			}
		}

        try {
            $mail->setFrom($from);
            $mail->addReplyTo($from);
        	$mail->addAddress($email);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
            return true;
        } catch (Exception $e) {
            $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }
	}

}

/**
 * ADOdb logging
 * see: https://adodb.org/dokuwiki/doku.php?id=v5:reference:logging
 * 
 * @param string $msg
 * @return void
 */
function logMessageText($msg)
{
    $loggingObject = uzLogger::Instance();
	$loggingObject->log($loggingObject::WARNING,$msg);
}

function isLoggedIn()
{
	return (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == TRUE);
}

function setLoggedIn()
{
	$_SESSION['loggedin'] = TRUE;
	$_SESSION['last_active'] = $_SERVER['REQUEST_TIME'];
}

/*
 * Sets the global constants EGS_USERNAME and EGS_COMPANY_ID
 */
function setupLoggedInUser()
{

	showtime('start-user');

	if (empty($_SESSION['username']))
	{
		session_destroy();
		header("Location: /");
		exit;
	}

	define('EGS_USERNAME', $_SESSION['username']);

	// If the user has selected a company using the company selector
	// update their lastcompanylogin value and save this value to their session
	if (isset($_GET['companyselector']))
	{

		$access = DataObjectFactory::Factory('Usercompanyaccess');

		if ($access->loadBy(array('username', 'usercompanyid'), array(EGS_USERNAME, $_GET['companyselector'])))
		{
			$user = getCurrentUser();
			$user->update(EGS_USERNAME, 'lastcompanylogin', $_GET['companyselector']);
			$_SESSION['EGS_COMPANY_ID'] = $_GET['companyselector'];
		}

	}

	// Define the EGS_COMPANY_ID global constant either from
	// 1 - saved session variable
	// 2 - users lastcompanylogin
	// 3 - System Company that is linked from user person_id via person company_id
	// 4 -  first entry returned from User Company Access that is enabled for this user
	if (isset($_SESSION['EGS_COMPANY_ID']) && ($_SESSION['EGS_COMPANY_ID'] != 'EGS_COMPANY_ID'))
	{
		define('EGS_COMPANY_ID', $_SESSION['EGS_COMPANY_ID']);
	}
	else
	{

		$user = getCurrentUser();
		$lcl = $user->lastcompanylogin;

		if (!empty($lcl))
		{
			define('EGS_COMPANY_ID', $lcl);
		}
		elseif (!is_null($user->person_id))
		{

			$sc = DataObjectFactory::Factory('SystemCompany');
			$sc->loadBy('company_id', $user->persondetail->company_id);

			if ($sc->isLoaded())
			{
				define('EGS_COMPANY_ID',$sc->usercompanyid);
			}
			else
			{

				$uca		= DataObjectFactory::Factory('UserCompanyAccess');

				$companies	= $uca->getCompanies($_SESSION['username']);

				if (count($companies) > 0) {
					define('EGS_COMPANY_ID', $companies[0]['usercompanyid']);
				}
			}
		}

		$_SESSION['EGS_COMPANY_ID'] = EGS_COMPANY_ID;
	}

	showtime('end-user');

}

function getCurrentUser()
{

	if (!isLoggedIn())
	{
		return FALSE;
	}

	static $user;

	if (!isset($user))
	{

		$user = DataObjectFactory::Factory('User');
		$user->load(EGS_USERNAME);

	}

	return $user;

}

function returnJSONResponse($status,$extra=array()) {
		header('Content-type: application/json');
		$response = array();
		$response['status']=$status;
		if(is_array($extra) && !empty($extra)) {
			$response+=$extra;
		}
		audit(print_r($_POST,true).print_r($response,true));
		return json_encode($response);
	}

function sendTo() 
{

	global $system;

	$args = func_get_args();

	// If this is a dialog box, and it is not 'Save and add Another'
	// then need to return JSON response and exit
	if ((isset($_GET['dialog']) || isset($_POST['dialog']))
		&& !isset($_GET['saveAnother']) && !isset($_POST['saveAnother']))
	{
		$flash = Flash::Instance();
		$flash->save();

		// Close dialog and refresh page or redirect?
		if (isset($_GET['refresh']) || isset($_POST['refresh']))
		{
			// Include any parameters passed from the caller
			$url_params = [];
			$model_name = ''; // subordinate fk model name
			if (isset($system->controller->modeltype)) {
				$model_name = strtolower($system->controller->modeltype);
			}

			foreach ($args[3] as $param => $val) {
				if ($param == 'id') {
					$url_params[$model_name.'_id'] = $val;
				} else {
					$url_params[$param] = $val;
				}
			}

			echo returnJSONResponse(true, ['refresh_params' => $url_params]);
		}
		else
		{
			$link=array('modules'=>$args[2],
						'controller'=>$args[0],
						'action'=>$args[1],
						'other'=>$args[3]
						);
			echo returnJSONResponse(true, ['redirect' => '/?'.setParamsString($link)]);
		}

		exit;
	}

	if (isset($_GET['ajax']) || isset($_POST['ajax']))
	{
		$args[3]['ajax'] = '';
	}

	if (isset($_GET['dialog']) || isset($_POST['dialog']))
	{
		$args[3]['dialog'] = '';
	}

	$redirector = $system->injector->instantiate('Redirection');
	$redirector->Redirect($args);

}

function sendBack($status = null)
{

	if ($status !== null && isset($_GET['ajax']))
	{
		die($status);
	}

	$current = setParamsString(getParamsArray($_SERVER['QUERY_STRING']));
	$referer = getParamsString();

	//echo 'lib::sendBack current='.$current.' referer='.$referer.'<br>';

	if ($current == $referer)
	{

		if (isset($_SESSION['referer'][$current]))
		{
			$referer = $_SESSION['referer'][$current];
		}
		else
		{
			$referer = '';
		}
	}

	$referer = getParamsArray($referer);

	//	$referer=getParamsArray();
	//echo 'lib::sendBack<pre>'.print_r($referer, TRUE).'</pre><br>';

	sendTo(
		$referer['controller'],
		$referer['action'],
		$referer['modules'],
		$referer['other'] ?? null
	);

}

function getParamsString($url = '')
{

	if (empty($url) && isset($_SERVER['HTTP_REFERER']))
	{
		$url = $_SERVER['HTTP_REFERER'];
	}

	$params = explode('?', (string) $url);

	return ($params[1] ?? '');

}

function getParamsArray($params = '')
{

	if (empty($params))
	{
		$params = getParamsString();
	}

	$res = array(
		'modules'		=> array(),
		'controller'	=> 'Index',
		'action'		=> 'index'
	);

	if (!empty($params)) 
	{

		$refs = explode('&', (string) $params);

		foreach ($refs as $ref) 
		{

			$refsplit = explode('=', $ref);

			if (substr($refsplit[0], -6)=='module')
			{

				if (!empty($refsplit[1]))
				{
					$res['modules'][$refsplit[0]] = $refsplit[1];
				}

			} 
			elseif (strtolower($refsplit[0]) == 'controller' || strtolower($refsplit[0]) == 'action' || strtolower($refsplit[0]) == 'pid')
			{
				$res[$refsplit[0]] = $refsplit[1];
			}
			else {

				if ($refsplit[0]!='rememberUser' && $refsplit[0] != 'pid')
				{

					if (isset($refsplit[1]))
					{
						$res['other'][$refsplit[0]] = $refsplit[1];
					}

				}

			}

		}

	}

	return $res;

}

function setParamsString($array)
{

	$params_string	= '';
	$params_array	= array();

	if (is_array($array))
	{

		if (!isset($array['pid']))
		{

			if (isset($array['modules']))
			{
				$module = $array['modules'];
			}
			elseif (isset($array['module']))
			{
				$module = $array['module'];
			}
			else
			{
				$module = '';
			}

			if (isset($array['controller']))
			{
				$controller = $array['controller'];
			}
			else
			{
				$controller = '';
			}

			if (isset($array['action']))
			{
				$action = $array['action'];
			}
			else
			{
				$action = '';
			}

			$ao=AccessObject::Instance();
			$pid=$ao->getPermission($module, $controller, $action);

			if (!empty($pid))
			{
				$array['pid']=$pid;
			}

		}

		if (isset($array['pid']))
		{
			$params_array[] = 'pid=' . $array['pid'];
		}

		if (isset($array['modules']))
		{

			foreach ($array['modules'] as $key => $value)
			{
				$params_array[] = $key . '=' . $value;
			}

		}
		elseif (isset($array['module']))
		{
			$params_array[] = 'module=' . $array['module'];
		}

		if (isset($array['controller']))
		{
			$params_array[] = 'controller=' . strtolower((string) $array['controller']);
		}

		if (isset($array['action']))
		{
			$params_array[] = 'action=' . $array['action'];
		}

		if (isset($array['other']))
		{

			foreach ($array['other'] as $key => $value)
			{
				$params_array[] = $key . '=' . $value;
			}

		}

		$params_string = implode('&', $params_array);
	}

	return $params_string;

}

function setReferer($refs = '') 
{

	if (empty($refs))
	{

		if (isset($_SERVER['HTTP_REFERER']))
		{
			$refs = getParamsArray();
		}
		else
		{
			$refs = $_SESSION['refererPage'];
		}
	}

	$referer = setParamsString($refs);
	$current = setParamsString(getParamsArray($_SERVER['QUERY_STRING']));

	//	echo 'lib::setReferer referer='.$referer.'<br>';
	//	echo 'lib::setReferer current='.$current.'<br>';

	if ($current != $referer)
	{
		$_SESSION['referer'][$current] = $referer;
	}
	elseif (!isset($_SESSION['referer'][$current]))
	{
		$_SESSION['referer'][$current] = $current;
	}

	//	echo 'lib::setReferer<pre>'.print_r($_SESSION['referer'][$current],TRUE).'</pre><br>';

	debug('lib::setReferer' . $_SESSION['referer'][$current]);

}

function setRefererPage() 
{

	// Backwards compatibility
	if (isset($_SESSION['referer']))
	{

		$referer = setParamsString(getParamsArray());

		if (isset($_SESSION['referer'][$referer]))
		{
			$_SESSION['refererPage'] = getParamsArray($_SESSION['referer'][$referer]);
		}
		else
		{
			$_SESSION['refererPage'] = getParamsArray('');
			$_SESSION['refererPage']['action'] = '';
		}

	}
	else
	{
		$_SESSION['refererPage'] = getParamsArray('');
	}

	//	echo 'lib::setRefererPage $referer='.$referer.'<br>';
	//	echo 'lib::setRefererPage <pre>'.print_r($_SESSION['referer'], TRUE).'</pre><br>';
	//	echo 'lib::setRefererPage<pre>'.print_r($_SESSION['refererPage'], TRUE).'</pre><br>';

}

// define the autoload function
// we have to do this as smarty 3 has it's own autoloader that would superceed this


/**
 * Define the uzERP autoload function
 *
 * @param string $class_name
 * @return void
 */
function uz_autoload($class_name)
{

	$autoloader = AutoLoader::Instance();
	$autoloader->load($class_name);

}
spl_autoload_register('uz_autoload');


function with(&$params, &$smarty)
{

	$with = $smarty->getTemplateVars('with');

	if (!is_array($with))
	{
		return;
	}

	foreach ($with as $key=>$val)
	{

		if (empty($params[$key]))
		{
			$params[$key] = $val;
		}

	}

}

/**
 * Sanitize string for output in templates
 *
 * @param String $string
 * @param Const(htmlentities flags) $quote_style
 * @return String
 */
function uzh($string, $quote_style = ENT_NOQUOTES) {
	return nl2br(htmlentities($string, $quote_style, 'UTF-8'));
}

function u($string)
{
	return urlencode(str_replace(' ', '_', $string));
}

function un_u($string)
{
	return str_replace('_', ' ', urldecode((string) $string));
}

function file_path_concat($path,$array)

{
	$scanpath = $path;

	foreach($array as $dir)
	{
		$scanpath .= $dir . "/";
	}

	return $scanpath;

}

/**
 * Generate a link based on params and permissions
 *
 * @access	public
 * @param	array $params
 * @param	boolean $data
 * @param	boolean $html
 * @return	string
 */
function link_to($params, $data = FALSE, $html = TRUE) {

	$script			= '';
	$url			= '?';
	$attr_array		= array();
	$attr_string	= '';
	$modules		= array();
	$module			= '';
	$tag			= '';

	$ao = AccessObject::Instance();

	if (isset($params['modules']))
	{

		$modules = $params['modules'];

		while (empty($module) && count($modules) > 0)
		{
			$module = array_pop($modules);
		}

	}
	else
	{

		$modkey = 'module';

		while (key_exists($modkey, $params))
		{
			if (!empty($params[$modkey]))
			{
				$module = $params[$modkey];
			}
			$modkey = 'sub' . $modkey;
		}

	}

	// loop through each item here, check the params array and set the type variable
	foreach (array('pid', 'controller', 'action') as $type)
	{

		if (isset($params[$type]))
		{
			$$type = $params[$type];
		}
		else
		{
			$$type = '';
		}

	}

	// build data attributes, apply them to attributes array
	if (isset($params['data_attrs']))
	{
		$attr_array[] = build_attribute_string(build_data_attributes($params['data_attrs']));
	}

	// if we're handling a print link we want to test permissions
	// against the print action and not the dialog action

	if (isset($params['printaction']))
	{
		$action = $params['printaction'];
	}

	if (empty($pid))
	{
		$pid = $ao->getPermission($module, $controller, $action); /** @phpstan-ignore-line */
	}

	$allowed = $ao->hasPermission($module, $controller, $action, $pid); /** @phpstan-ignore-line */

	$modules = array();

	if (!empty($pid))
	{
		$modules['pid'] = $pid;
	}

	if (!empty($pid) && !isset($params['value']))
	{

		if (!empty($params['action']))
		{
			$permission = $params['action'];
		}
		elseif (!empty($params['controller']))
		{
			$permission = $params['controller'];
		}
		elseif (!empty($module))
		{
			$permission = $module;
		}

		$per = DataObjectFactory::Factory('Permission');
		$per->load($pid);

		switch ($per->type)
		{

			case 'g':
			case 'm':
			case 's':
				if ($per->permission == $permission)
				{
					$tag = $per->title;
				}
				break;

			case 'c':
				if ($per->permission==$permission)
				{
					$tag = $per->title;
				}
				break;

			case 'a':
				if ($per->permission==$permission)
				{
					$tag = $per->title;
				}

		}

	}

	if (empty($tag) && isset($params['value']))
	{
		$tag = $params['value'];
	}

	if (isset($params['modules']))
	{

		$modulekey = 'module';

		foreach ($params['modules'] as $module)
		{
			$modules[$modulekey] = $module;
			$modulekey = 'sub' . $modulekey;
		}

		unset($params['modules']);

	}

	$params = array_merge($modules, $params);

	// remove the data attrs item
	unset($params['data_attrs']);

	foreach ($params as $key=>$val)
	{

		//special cases
		if (substr($key, 0, 1) === '_')
		{
			$attr_array[str_replace('_', '', $key)] = $val;
			continue;
		}

		if ($key == 'value' || $key == 'img' || $key == 'alt' || $key == 'no_prettify')
		{
			continue;
		}

		if (!is_array($val))
		{
			$url .= strtolower($key) . '=' . urlencode((string) $val) . '&amp;';
		}
	}

	//remove last ampersand
	$url = substr($url, 0, -5);
	$url = '/' . $url;

	if (isset($params['link']))
	{
		$url = $params['link'];
	}

	$attr_array['href'] = $url;

	// convert the attributes array to string
	$attr_string = build_attribute_string($attr_array);

	if (!$allowed)
	{

		if (isset($params['img']) || $data)
		{
			$string = $params['value'];
		}
		else
		{
			$string = prettify($tag);
			if ($html === true){
				$attr_array['class'] = "{$attr_array['class']} not-allowed";
				$attr_string = build_attribute_string($attr_array);
				$string = "<span {$attr_string}>{$string}</span>";
			} else {
				$string = $string;
			}
		}

	}
	else
	{

		if ($html === TRUE)
		{

			if ($params['value']=='')
			{
				$params['value'] = 'link';
			}

			if (isset($params['img']))
			{
				$params['value'] = '<img src="' . $params['img'] . '" alt="' . $params['alt'] . '" />';
				$string = '<a ' . $attr_string . ' >' . $params['value'] . '</a>';
			}
			else if($data)
			{
				$string = '<a ' . $attr_string . ' >' . $params['value'] . '</a>';
			}
			elseif(isset($params['no_prettify']))
			{
				$string = '<a ' . $attr_string . ' >' . $tag . '</a>';
			}
			else
			{
				$string = '<a ' . $attr_string . ' >' . prettify($tag) . '</a>';
			}

		}
		else
		{
			$string = str_replace('&amp;', '&', $url);
		}
	}

	return $string;

}

function prettify($word)
{

	static $word_cache;
	static $translator;

	if (!isset($word_cache[$word]))
	{

		if (!isset($translator))
		{
			global $system;
			$translator = $system->injector->instantiate('Translation');
		}

		$word_cache[$word] = $translator->translate($word);

	}

	return $word_cache[$word];


}

function cssify($css)
{
	return '<style type="text/css">' . $css . '</style>';
}

function pricify($number, $html = TRUE)
{

	if (empty($number) || !is_numeric($number))
	{
		return;
	}

	if ($html)
	{
		$symbol = uzh(EGS_CURRENCY_SYMBOL);
	}
	else
	{
		$symbol = EGS_CURRENCY_SYMBOL;
	}

	return $symbol . number_format($number, 2);

}

function sizify($b, $p = null)
{

	/**
	 *
	 * @author Martin Sweeny
	 * @version 2010.0617
	 *
	 * returns formatted number of bytes.
	 * two parameters: the bytes and the precision (optional).
	 * if no precision is set, function will determine clean
	 * result automatically.
	 *
	 **/

	$units = array("B","kB","MB","GB","TB","PB","EB","ZB","YB");
	$c=0;

	if (!$p && $p !== 0) 
	{

		foreach($units as $k => $u)
		{

			if(($b / pow(1024,$k)) >= 1) 
			{
				$r["bytes"] = $b / pow(1024,$k);
				$r["units"] = $u;
				$c++;
			}

		}

		return number_format($r["bytes"],2) . " " . $r["units"];

	} 
	else
	{
		return number_format($b / pow(1024,$p)) . " " . $units[$p];
	}

}

function isModuleAdmin($name = null)
{

	return true;

	$router = RouteParser::Instance();

	if (isset($name))
	{
		$module = $name;
	}
	else
	{
		$module=$router->dispatch('module');
	}

	if (isset($_SESSION['module_admins']))
	{
		$cache = $_SESSION['module_admins'];
	}
	else
	{
		$cache = array();
	}

	if (!isset($cache[$module]))
	{

		$access			= AccessObject::Instance();
		$db				= DB::Instance();
		$roles_string	= implode(',', $access->roles);

		//		foreach ($access->roles as $role) {
		//			$roles_string.=$role.',';
		//		}
		//		$roles_string=rtrim($roles_string,',');

		$query = 'SELECT module_name FROM module_admins WHERE role_id IN (' . $roles_string . ') AND module_name=' . $db->qstr($module);
		debug('lib::isModuleAdmin ' . $query);

		$module = $db->GetOne($query);

		if (!empty($module) && $module !== FALSE)
		{
			$cache[$module] = TRUE;
		}
		else
		{

			foreach ($access->tree as $treenode)
			{
				if ($treenode['name'] == 'egs')
				{
					$cache[$module] = TRUE;
				}

			}

			$cache[$module] = FALSE;

		}

	}

	$_SESSION['module_admins'][$module] = $cache[$module];

	return $cache[$module];

}

/**
 * Will return an array of all the values between $min and $max, with separation $step
 * e.g. getRange(0,1,0.2) will return [0.0,0.2,0.4,0.6,0.8,1.0]
 * - will maintain the precision of the most precise argument
 * @see maxdp()
 */
function getRange($min, $max, $step, $keys = FALSE, $value_prefix = '', $value_suffix = '', $signed = FALSE, $ignore_zero = FALSE)
{

	$values	= array();
	$dp		= maxdp($min,$max,$step);

	for ($i = $min; $i <= $max; $i += $step)
	{

		if ($ignore_zero && $i == 0) 
		{
			continue;
		}

		$value = sprintf('%01.' . $dp . 'f', $i);

		if ($signed && floatval($value) > 0)
		{
			$value = '+' . $value;
		}

		if ($keys)
		{
			$values[$value] = $value_prefix . $value . $value_suffix;
		}
		else
		{
			$values[] = $value;
		}

	}

	return $values;

}

/**
 *  Returns the maximum number of decimal places found in the supplied arguments
 *e.g. maxdp(0.6,1.2,1.23); will return 2
 */
function maxdp()
{

	$dp		= 0;
	$args	= func_get_args();

	foreach ($args as $arg)
	{

		if (strrpos((string) $arg, '.') !== FALSE && (strlen(strval($arg)) - strrpos(strval($arg), '.')) -1 > $dp)
		{
			$dp = strlen(strval($arg)) - strrpos(strval($arg), '.') -1;
		}

	}

	return $dp;

}

function to_working_days($time, $suffix = TRUE)
{

	$time			= explode(':', (string) $time);
	$hours			= $time[0];
	$minutes		= $time[1];
	$day_length		= SystemCompanySettings::DAY_LENGTH;
	$hour			= $hours + ($minutes/60);
	$days			= $hours / $day_length;
	$suffix_text	= ($suffix)?' days':'';

	return $days . $suffix_text;

}

function coalesce()
{

	$args = func_get_args();

	foreach ($args as $arg)
	{

		if ($arg !== null)
		{
			return $arg;
		}

	}

}

function decorate($object, $decorator)
{

	if (class_exists($decorator))
	{
		$decorator = new $decorator($object);
		return $decorator;
	}

	throw new Exception('Class not found: ' . $decorator);

}

function date_strip($date_string)
{

	list($date,$time) = explode(' ', (string) $date_string);
	list($time,) = explode('.', $time);
	list($h, $m,) = explode(':', $time);

	return $date . ' ' . $h . ':' . $m;

}

function overdue($date)
{

	$o_date	= $date;
	$date	= fix_date($date);
	$t_date	= strtotime((string) $date);

	if ($t_date < time())
	{
		return '<em class="overdue_date">' . $o_date . '</em>';
	}

	return $o_date;

}

function month_to_string($number)
{

	$months = array('January','February','March','April','May','June','July','August','September','October','November','December');

	return $months[$number-1];

	//return date('F',($number%12-1)*(60*60*24*31));

}

function trunc($num, $precision = 0)
{
	return bcmul((string) $num, 1, $precision);
}

function currentDateConstraint($date = '')
{
	if (empty($date))
	{
		$date = Constraint::TODAY;
	}

	$ccdate=new ConstraintChain();
	$ccdate->add(new Constraint('start_date', '<=', $date));
	$ccend=new ConstraintChain();
	$ccend->add(new Constraint('end_date', 'is', 'NULL'));
	$ccend->add(new Constraint('end_date', '>=', $date), 'OR');
	$ccdate->add($ccend);
	return $ccdate;
}

function ownerConstraint($_field = 'owner', $_value = EGS_USERNAME)
{
	$cc = new ConstraintChain();

	$cc->add(new Constraint($_field, '=', $_value));

	return $cc;
}

function fix_date($date, $format = DATE_FORMAT, &$errors = array())
{

	//
	//	strptime is not available on all OS platforms
	//
	//	$format = format_for_strptime(DATE_FORMAT);
	//
	//	$date_array = strptime($date,$format);
	//	$month = sprintf('%02d',$date_array['tm_mon']+1);
	//	$year = $date_array['tm_year']+1900;
	//	$day = sprintf('%02d',$date_array['tm_mday']);

	$date_array = date_parse_from_format($format, $date);

	if ($date_array['error_count'] > 0)
	{
		$errors = array_merge_recursive($errors, $date_array['errors']);
		return FALSE;
	}

	$month	= sprintf('%02d', $date_array['month']);
	$year	= $date_array['year'];
	$day	= sprintf('%02d', $date_array['day']);

	if ($format == DATE_TIME_FORMAT)
	{
		$hour	= sprintf('%02d', $date_array['hour']);
		$minute	= sprintf('%02d', $date_array['minute']);

		return $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minute;
	}

	return $year . '-' . $month . '-' . $day;

}

function format_for_strptime($format)
{
	return '%' . str_replace(array('/', ' ', ':i'), array('/%', ' %', ':%M'), $format);	
}

function un_fix_date($date, $showtime = FALSE)
{

	if (empty($date))
	{
		return;
	}

	if ($showtime)
	{
		return date(DATE_TIME_FORMAT, strtotime((string) $date));
	} 
	else
	{
		return date(DATE_FORMAT, strtotime((string) $date));
	}

}

function next_working_day($date)
{

	$dayno = date('w', strtotime((string) $date));

	if ($dayno == 0)
	{
		return date(DATE_FORMAT, strtotime('+1 days', strtotime((string) $date)));
	}

	if ($dayno == 6)
	{
		return date(DATE_FORMAT, strtotime('+2 days', strtotime((string) $date)));
	}

	return un_fix_date($date);

}

function last_day($month, $year)
{

	// Use mktime to create a timestamp one month into the future, but one
	//  day less.  Also make the time for almost midnight, so it can be
	//  used as an 'end of month' boundary

	return mktime(23, 59, 59, $month + 1, 0, $year);

}

/**
 *  $trans_date is expected to be a 'fixed_date', i.e. yyyy-mm-dd
 */
function calc_due_date($trans_date, $basis, $days = 0, $months = 0)
{

	$trans_date = fix_date($trans_date);

	if ($basis == 'I' || $basis == 'Invoice')
	{
		$time = strtotime((string) $trans_date);
	}
	elseif ($basis == 'M' || $basis == 'Month')
	{
		$month		= date('m',strtotime((string) $trans_date));
		$year		= date('Y',strtotime((string) $trans_date));
		$month_end	= last_day($month+$months,$year);
		$time		= $month_end;
	}
	else
	{
		throw new Exception('calc_due_date only understands Invoice and Month type payment term logic');
	}

	$time = strtotime('+ ' . $days . ' days', $time);

	return date(DATE_FORMAT, $time);

}

function calc_tax_percentage($rate_id, $status_id, $amount)
{

	// global $injector;
	global $system;

	$redirector = $system->injector->instantiate('TaxCalculation');

	return $redirector->calc_percentage($rate_id, $status_id, $amount);

}

function logtime($msg = '')
{

	$starttime	= START_TIME;
	$mtime		= microtime();
	$mtime		= explode(" ", $mtime);
	$mtime		= $mtime[1] + $mtime[0];
	$endtime	= $mtime;
	$fp			= fopen('/tmp/timelog5', 'a+');

	fwrite($fp, $msg . ($endtime - $starttime) . "\n");
	fclose($fp);

	echo $msg . ($endtime - $starttime) . '<br>';

}

function showtime($msg = '', $return = FALSE)
{

	return;

	static $prev = 0;

	$msg = empty($msg) ? $msg : $msg . ': ';

	global $starttime;

	$time = microtime(TRUE);
	$time = ($time - $starttime);

	$diff = $time-$prev;
	$prev = $time;

	if ($return)
	{
		//return $msg."		".str_pad($time,20)."			($diff)\n<br>";
	}

	//	echo str_pad($msg,30,'-')."			".str_pad($time,20)."			(".($diff*1).")\n<br>";

}

function remove_line_breaks($string)
{

	$find    = array(chr(10), chr(13), "\n", "\r");
	$replace = array(" ", " ", " ", " ");

	return str_replace($find, $replace, $string);

}

function json_reply($data)
{

	header('Content-type: application/json');
	echo json_encode($data);
	exit;

}

function isPartUppercase($string)
{
	return (preg_match("/[A-Z]/", (string) $string) === 0);
}

/**
 * build_data_attributes
 *
 * loops through given array, if any of the keys start with "data_" that key / value
 * pair is output to a string for use in a HTML element.
 *
 * Specify data attributes in Smarty as data_row_id="", this will be converted to data-row-id=""
 *
 * @param array $params
 * @param boolean $parse_non_data <-- will NOT parse keys that do not start with data_
 */
function build_data_attributes($params = array(), $parse_non_data = FALSE)
{

	$arr = array();

	foreach ($params as $key => $value)
	{

		if (substr($key, 0, 5) === 'data_')
		{
			$arr[str_replace('_', '-', $key)] = $value;
		}
		elseif ($parse_non_data)
		{
			$arr['data-' . str_replace('_', '-', $key)] = $value;
		}

	}

	return $arr;

}

/**
 * build_attribute_string
 *
 * takes an array and coverts it to a html attributes string, example:
 *
 * array(
 *     rel   => 'abc',
 *     class => array(
 *         'right',
 *         'numeric',
 *         'red'
 *     )
 * )
 *
 * makes:
 *
 * rel="abc" class="right numeric red"
 *
 * @param $attrs
 */
function build_attribute_string($attrs)
{

	if (!is_array($attrs))
	{
		return array();
	}

	$arr = array();

	foreach ($attrs as $key => $value)
	{

		if (is_object($value))
		{
			continue;
		}

		if (is_numeric($key))
		{
			$arr[] = $value;
		}
		else
		{

			if (is_array($value))
			{
				$arr[] = $key . '="' . implode(' ', $value) . '"';
			}
			else
			{
				$arr[] = $key . '="' . $value . '"';
			}

		}

	}

	return implode(' ', $arr);

}


function back_trace()
{

	$backtrace = debug_backtrace();

	foreach ($backtrace as $key => $value)
	{

		if (isset($value['file']) && isset($value['line']))
		{
			echo 'Trace "' . $value['function'] . '() ' . $value['file'] . '" on line ' . $value['line'] . '<br />' . "\r\n";
		}

	}

	echo "<br /><br />" . "\r\n";

}
//******************
//  uasort callbacks

function cmp_position($a, $b)
{
	return $a['position'] > $b['position'] ? 1 : -1;
}

function cmp_filename($a, $b)
{
	return strtolower((string) $a['name']) > strtolower((string) $b['name']) ? 1 : -1;
}

function get_string_between($string, $start, $end)
{
	$string	= " " . $string;
	$ini	= strpos($string,(string) $start);

	if ($ini == 0) 
	{
		return "";
	}

	$ini += strlen((string) $start);
	$len  = strpos($string, (string) $end, $ini) - $ini;

	return substr($string, $ini, $len);

}

function get_plural_string($number, $plural = 's', $singular = '')
{

	if ($number > 1)
	{
		return $plural;
	}
	else
	{
		return $singular;
	}

}

function is_ajax() {

	if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
	{
		return TRUE;
	}

	return FALSE;

}

function is_direct_request()
{

	// get the backtrace data
	// NOTE: as of PHP 5.4.0 a limit can be passed to the debug_backtrace that might
	// aid performance -> http://php.net/manual/en/function.debug-backtrace.php

	$backtrace = debug_backtrace();

	// if this function is index 0, we want the previous element, index 1
	$backtrace = $backtrace[1];

	if (strtolower($backtrace['function']) === strtolower((string) $_GET['action']))
	{
		return TRUE;
	}

	return FALSE;

}

function clean_tmp_directory($directory)
{

	/* http://return-true.com/2009/03/deleting-files-in-a-directory-older-than-today-every-week-with-php/ */

	$files		= array();
	$index		= array();
	$yesterday	= strtotime('yesterday');

	if ($handle = opendir($directory))
	{

		clearstatcache();

		while (FALSE !== ($file = readdir($handle)))
		{

			if ($file != "." && $file != "..") 
			{
				$files[] = $file;
				$index[] = filemtime($directory . $file);
			}

		}

		closedir($handle);

	}

	asort( $index );

	foreach ($index as $i => $t)
	{

		if ($t < $yesterday)
		{
			@unlink($directory . $files[$i]);
		}

	}

}

function get_file_extension($path)
{
	return pathinfo((string) $path, PATHINFO_EXTENSION);
}

function smarty_plugin_template(&$smarty, $data=NULL, $identifier='')
{

	// first, check if $data is set that the identifier is too
	if ($data !== NULL && empty($identifier))
	{
		return FALSE;
	}

	$filename	= $identifier;
	$identifier	= str_replace('.', '_', $identifier);

	// if data has been set, assign the variable to smarty
	if ($data !== NULL)
	{
		$smarty->assign($identifier, $data);
	}

	// fetch the template
	$html = $smarty->fetch($filename . '.tpl');

	// if needed, clear the assigned smarty var
	if ($data !== NULL)
	{
		$smarty->clearAssign($identifier);
	}

	// finally return the html back to the callee
	return $html;

}

function is_css($file)
{
	return (in_array(strtolower((string) get_file_extension($file)), array('css', 'less')));
}

function is_js($file)
{
	return (in_array(strtolower((string) get_file_extension($file)), array('js')));
}

function get_config($key)
{

	$config = Config::Instance();

	return $config->get($key);

}

function set_config($key, $value)
{

	$config = Config::Instance();

	return $config->get($key, $value);

}

function is_domain_availible($domain)
{

	if (function_exists('curl_init'))
	{

		// ATTN check if curl_init exists
		$agent	= "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)";
		$ch		= curl_init();

		curl_setopt($ch, CURLOPT_URL, $domain);
		curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSLVERSION, 3);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		$page		= curl_exec($ch);
		$httpcode	= curl_getinfo($ch, CURLINFO_HTTP_CODE);

		curl_close($ch);

		return ($httpcode >= 200 && $httpcode < 300);

	}

	return FALSE;

}

/**
 * Get the class methods for the class in question, and NOT
 * methods that may have been inherited from an extended class
 * 
 * @param string $object
 */
function get_final_class_methods($object)
{

	if (class_exists('ReflectionClass') && !empty($object))
	{

		$reflection		= new ReflectionClass($object);
		$methods		= $reflection->getMethods();
		$methods_array	= array();

		// dealing with a lowercase controller name
		$object = strtolower($object);

		foreach($methods as $method) {

			if (strtolower($method->class) == $object)
			{
				$methods_array[$method->name] = $method->name;
			}

		}

		return $methods_array;

	}

	return FALSE;

}

function registerProgress ($value = 0, $name = 'progress')
{
	$_SESSION[$name] = $value;

	// Need to close the session to write to the session file
	session_write_close();
	// Now need to re-start the session
	session_start();

}

/**
 * Set a cookie
 * 
 * Handles adding the samesite cookie attribute
 *
 * @param string $name
 * @param string $value
 * @param integer $expires
 * @param boolean $replace
 * @return void
 */
function addCookie ($name='', $value='', $expires=0, $replace=false)
{
	$samesite = false;
	$params = session_get_cookie_params();

	$cookieOptions = [
		'expires' => $expires,
		'path' => $params['path'],
		'domain' => $params['domain'],
		'secure' => $params['secure'],
		'httponly' => $params['httponly'],
	];

	if (isset($params['secure']) && $params['secure']) {
		$cookieOptions['samesite'] == 'strict';
	}

	// For PHP < 7.3 we need to set the cookie manually to add 'samesite'
	if (!isset($params['samesite'])) {
		if ($samesite === false) {
			$cookie = new Cookie($name, $value,
				$cookieOptions['expires'],
				$cookieOptions['path'],
				$cookieOptions['domain'],
				$cookieOptions['secure'],
				$cookieOptions['httponly']
			);
		} else {
			$cookie = new Cookie($name, $value,
				$cookieOptions['expires'],
				$cookieOptions['path'],
				$cookieOptions['domain'],
				$cookieOptions['secure'],
				$cookieOptions['httponly'],
				false,
				$cookieOptions['samesite']
			);
		}
		header("Set-Cookie: {$cookie->__toString()}", $replace);
		return;
	}
	// For PHP >= 7.3 use the new signature for setcookie
	setcookie($name, $value, $cookieOptions);
}

/**
 * Get javascript file path for a module
 *
 * @param String $module
 * @return String file path
 */
function getModuleJS(String $module) {
	global $system;

	$jsdir = $system::findModulePath(PUBLIC_MODULES, $module, FALSE);
	$jsdir .= DIRECTORY_SEPARATOR . 'resources/js';
	if (!strpos($jsdir, 'user/modules')) {
		$jsdir = str_replace(FILE_ROOT . 'modules/public_pages' , 'dist/js/modules', $jsdir);
	} else {
		// Serve user module js from module directory
		$jsdir = str_replace(FILE_ROOT , '', $jsdir);
	}
	$paths = glob("{$jsdir}/*.js");
	return DIRECTORY_SEPARATOR . $paths[0];
}


/**
 * Sanitize arrays and strings
 *
 * @param mixed $input (string|array)
 * @return string|array
 */
function sanitize($input)
{
	if (is_array($input)) {
		foreach ($input as $key=>$value) {
			$safe_key = htmlentities(strip_tags($key), ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
			$result[$safe_key] = sanitize($value);
		}
	} else {
		$result = htmlentities((string) $input, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
	}

		return $result;
}

/* End of file lib.php */
/* Location: ./lib/lib.php */
