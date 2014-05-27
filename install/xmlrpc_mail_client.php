<?php

/**
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/

$version='$Revision: 1.8 $';

// Takes four parameters;
// -m the mail server imap details to connect to
// -u the mailbox username to connect to
// -f the path and name of the encrypted password file
// -h the name of the xml server to connect to
// -p (optional) the port of the xml server

$value='';
$arguments = getopt("f:h:m:p::u:");
//echo 'Arguments='.print_r($arguments, true)."\n";
if (empty($arguments['f']))
{
    echo "No password file specfied\n";
    exit;
}

if (!file_exists($arguments['f']))
{
    echo "password file does not exist\n";
    exit;
}

if (empty($arguments['u']))
{
    echo "No username specfied\n";
    exit;
}

if (empty($arguments['m']))
{
    echo "No mailhost/mailbox specfied\n";
    exit;
}

if (empty($arguments['h']))
{
    echo "No hostname specfied\n";
    exit;
}
else
{
	$host=$arguments['h'];
}

if (empty($arguments['p']))
{
    $port='';
}
else
{
	$port=$arguments['p'];
}

$handle = fopen($arguments['f'], "r");
if ($handle) {

	while (($buffer = fgets($handle)) !== false)
    {
        $line=explode(':', $buffer);
        
        if ($line[0]==$arguments['u'])
        {
             $value=$line[1];
             break;
        }
    }
    
    if (empty($value) && !feof($handle))
    {
        echo "Error: unexpected fgets() fail\n";
    }
    
    fclose($handle);

}

if (empty($value))
{
    echo "Entry not found for ".$arguments['u']."\n";
    break;
}

if (isset($_GET['type']) && strtolower($_GET['type'])=='html')
{
  $nl='<br>';
}
else
{
  $nl=chr(10);
}

/* connect to gmail */
$hostname = $arguments['m'];
$username = $arguments['u'];
$password = base64_decode($value);

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

if ($inbox===false)
{
	echo "Failed to connect\n";
	exit;
}

/* grab emails */
$emails = imap_search($inbox,'ALL');
/* if emails are returned, cycle through each... */
if($emails)
{
	
	include 'plugins/xmlrpc/xmlrpc.inc';

	// Make an object to represent our server.
	$server = new xmlrpc_client('server.php',
                            $host, $port);

	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
	
	/* for every email... */
	$check_for = array('…', '–', '‘', '’', '“');
	$replace_with = array('&#8230;', '-', "'", "'", '"');
	
	foreach($emails as $email_number)
	{
		/* get information specific to this email */
		$header = imap_headerinfo($inbox,$email_number);
		$overview = imap_fetch_overview($inbox,$email_number,0);
		$structure = imap_fetchstructure($inbox, $email_number );
		$body=array();

		if (isset($structure->parts))
		{
			
			foreach ($structure->parts as $key=>$part)
			{
				$content = imap_fetchbody($inbox, $email_number, $key+1);
				
				if($part->encoding == "3")
				{
					$content=base64_decode($content);
				}
				
				if($part->encoding == "4")
				{
					$content = imap_qprint($content);
				}
				
				$content = str_replace($check_for, $replace_with, $content);
				
				$bodypart = array('encoding'=>new xmlrpcval($part->encoding, 'int')
								 ,'type'=>new xmlrpcval($part->type, 'int')
								 ,'subtype'=>new xmlrpcval($part->subtype, 'string')
								 ,'content'=>new xmlrpcval($content, 'string'));
				
				if (isset($part->ifdparameters)
					&& $part->ifdparameters
					&& $part->dparameters[0]->attribute=='FILENAME')
				{
					$bodypart['filename']=new xmlrpcval($part->dparameters[0]->value, 'string');
				}
				
				$body[$key] = new xmlrpcval($bodypart, 'struct');
			}
		}
		else
		{
			$content = imap_fetchbody($inbox, $email_number, 1);
			
			if($structure->encoding == "3")
			{
				$content=base64_decode($content);
			}
			
			if($structure->encoding == "4")
			{
				$content = imap_qprint($content);
			}
			
			$content = str_replace($check_for, $replace_with, $content);
			
			if (isset($structure->ifsubtype))
			{
				$subtype=$structure->subtype;
			}
			else
			{
				$subtype='';
			}
			
			$bodypart = array('encoding'=>new xmlrpcval($structure->encoding, 'int')
							 ,'type'=>new xmlrpcval($structure->type, 'int')
							 ,'subtype'=>new xmlrpcval($subtype, 'string')
							 ,'content'=>new xmlrpcval($content, 'string'));
			
			$body[1] = new xmlrpcval($bodypart, 'struct');
		}
		
		/* output the email header information */
		$subject= $overview[0]->subject;
		$to= $header->to[0]->mailbox.'@'.$header->to[0]->host;
		$from= $overview[0]->from;
		$date= $overview[0]->date;
		$msgid= $header->message_id;
		
		$message = new xmlrpcmsg('support.request'
								,array(new xmlrpcval($subject, 'string')
									  ,new xmlrpcval($from, 'string')
									  ,new xmlrpcval($body, 'struct')
									  ,new xmlrpcval($to, 'string')));
		//$server->setdebug(1);
		$result = $server->send($message);

		// Process the response.
		if (!$result)
		{
   			echo "Could not connect to HTTP server.\n";
		}
		elseif ($result->faultCode())
		{
   			echo "XML-RPC Fault #" . $result->faultCode() . ": " .
       			$result->faultString()."\n";
		}
		else
		{
   			$struct = $result->value();
   			$result = $struct->structmem('result');
   			$response = $struct->structmem('response');
			
   			echo $result->scalarval().':'.$response->scalarval()."\n";
			
			if ($result->scalarval()=='SUCCESS')
			{
				imap_mail_copy($inbox,$email_number,'processed');
				imap_delete($inbox, $email_number);
			}
		}

	}
	imap_expunge($inbox);
}

/* close the connection */
imap_close($inbox);

// End of xmlrpc_mail_client
