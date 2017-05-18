<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
abstract class EGSChart {

	protected $version='$Revision: 1.2 $';
	
	protected $type;
	/**
	 * @private $grapher GanttGraph
	 * An instance of the JPGraph Gantt Chart class
	 */
	protected $grapher;
	
	/**
	 * @private $title string
	 * The title of the chart
	 */
	protected $title;
	
	public function __construct($data=null,$id=null,$width=600,$height=400) {
		if(!self::Installed()) {
			throw new Exception('JPGraph isn\'t installed. Call EGSGantt::Installed() before instantiating!');
		}
		if(!defined('TTF_DIR')) {
			DEFINE('TTF_DIR','/usr/share/fonts/truetype/msttcorefonts/');
		}
		require_once APP_ROOT.'plugins/jpgraph/jpgraph.php';
		if($id==null) {
			$id=time();
		}
		$this->id=$id;
	}
	
	/**
	 * @param void
	 * @return boolean
	 * Returns true iff the JPGraph files are present in the correct location
	 */
	public static function Installed() {
		$var= file_exists(APP_ROOT.'plugins/jpgraph/jpgraph.php');
		return $var;
	}
	
	
	/**
	 * @param $title string
	 * @return void
	 *
	 * Sets the title for the chart
	 */
	public function setTitle($title) {
		$this->title=$title;
		$this->grapher->title->Set($title);
		$this->grapher->title->SetFont(FF_ARIAL, FS_BOLD,12);
	}
	
	protected function makeFileName($full=false) {
		$filename = $this->type.$this->id;
		if($full) {
			$filename = DATA_ROOT.'tmp/'.$filename;
		}
		return $filename;
	}
	
	public function render($resource=false) {
		if($resource) {
			return $this->grapher->Stroke(__IMG_HANDLER);
		}
		else {
			$this->grapher->Stroke($this->makeFileName(true));
		}
	}
	
	public function isCached() {
		return false;
	}
	
	public function getFilename() {
		return $this->makeFileName();
	}
	
	public function landscape() {
		if(!HAS_IMAGICK) {
			throw new Exception('Landscape graphs need IMagick installed');
		}
		$filename = $this->makeFileName(true);
		$source = imagick_readimage($filename);
		$rotate = imagick_rotate($source,270);
		imagick_writeimage($source,$filename);
	}
	
	
}
?>
