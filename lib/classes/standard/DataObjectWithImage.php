<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DataObjectWithImage extends DataObject {
	
	protected $version='$Revision: 1.2 $';
	
	public $image_filename;
	private $image_width=100;
	private $image_height=100;
	public function load($clause, $override = false, $return = false) {
		$res=parent::load($clause);
		$path = DATA_ROOT.'tmp/';
		$file=new File($path);
		$image=$this->image;
		if(!empty($image)) {
			$file->load($this->image);
			if($file===false) {
				throw new Exception('Failed to load file for '.get_class($this).' with id '.$this->image);
			}
			$a=$file->Pull($this->image_width,$this->image_height);
			$this->image_filename='/data/tmp/'.$a['filename'];
		}
		return $res;
	}
	
	function setImageDimensions($width,$height) {
		$this->image_width = $width;
		$this->image_height = $height;
	}
}
?>
