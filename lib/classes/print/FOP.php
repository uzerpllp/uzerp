<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/

class FOP {

	protected $version='$Revision: 1.1 $';
	
	public $xml;
	public $xsl;

	public function __construct(&$xml, &$xsl)
	{
		$this->xml = &$xml;
		$this->xsl = &$xsl;
	}

	public function go()
	{
		$fopcfg = dirname(__file__).'/fop_config.xml';
		$tmppdf = tempnam('/tmp', 'FOP');

		if(!extension_loaded('java')) {
			$tmpxml = tempnam('/tmp', 'FOP');
			$tmpxsl = tempnam('/tmp', 'FOP');
			file_put_contents($tmpxml, $this->xml);
			file_put_contents($tmpxsl, $this->xsl);
			exec("fop -xml {$tmpxml} -xsl {$tmpxsl} -pdf {$tmppdf} 2>&1");
			
			@unlink($tmpxml);
			@unlink($tmpxsl);
		} else {
			$xml = new DOMDocument;
			$xml->loadXML($this->xml);

			$xsl = new DOMDocument;
			$xsl->loadXML($this->xsl);

			$proc = new XSLTProcessor;
			$proc->importStyleSheet($xsl);

			$java_library_path = 'file:/usr/share/fop/lib/fop.jar;file:/usr/share/fop/lib/FOPWrapper.jar';
			try {
				java_require($java_library_path);
				$j_fwc = new JavaClass("FOPWrapper");
				$j_fw = $j_fwc->getInstance($fopcfg);
				$j_fw->run($proc->transformToXML($xml), $tmppdf);
			} catch (JavaException $ex) {
				$trace = new Java("java.io.ByteArrayOutputStream");
				$ex->printStackTrace(new Java("java.io.PrintStream", $trace));
				print "java stack trace: $trace\n";
			}
		}
		return($tmppdf);
	}
}

?>