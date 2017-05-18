<?php
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EGSLinkBuilder implements LinkBuilding {
	
	public function build($params,$data=false) {
		$script = '';
		$url='?';
		$attrs=' ';
		foreach($params as $key=>$val) {
			//special cases
			if(substr($key,0,1)==='_') {
				$attrs.=str_replace('_','',$key).'="'.$val.'" ';
				continue;
			}
			if($key=='value'||$key=='img'||$key=='alt') {
				continue;
			}
			//module and submodule
			if($key=='modules') {
				if(isset($val[0])) {
					$url.='module='.$val[0].'&amp;';
					//only allow submodule if module is set
					if(isset($val[1]))
						$url.='submodule='.$val[1].'&amp;';
				}
			//everything else
			} else {
				$url.=strtolower($key).'='.urlencode($val).'&amp;';
			}
		}
		
		//remove last ampersand
		$url=substr($url,0,-5);
		$url='/'.$url;
		if(isset($params['link']))
			$url=$params['link'];
		if(empty($params['value']))
			$params['value']='link';
			
		if(isset($params['img'])) {
			$params['value']='<img src="'.$params['img'].'" alt="'.$params['alt'].'" />';
			$string= '<a '.$attrs.' href="'.$url.'">'.$params['value'].'</a>';
		}	
		else if($data) {
			$string='<a '.$attrs.' href="'.$url.'">'.$params['value'].'</a>';
		}
		else {
			$string='<a '.$attrs.' href="'.$url.'">'.prettify($params['value']).'</a>';
		}
		return $string;
	}
}
?>