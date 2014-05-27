<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class Interval {

	protected $version='$Revision: 1.2 $';
	
	const ZERO='00:00:00';
	
	private $string;	
	public $hours=0;
	public  $minutes=0;
	public  $seconds=0;
	public function __construct($int=null) {
		if($int==null) {
			$int=Interval::ZERO;
		}
		$this->string = $int;
		$this->splitString();
	}
	
	private function splitString() {
		$t = explode(':',$this->string);
		$this->hours = $t[0];
		$this->minutes = $t[1];
		$this->seconds = $t[2];
	}
	
	public function add(Interval $int) {
		$hours = $this->hours + $int->hours;
		
		$seconds = $this->seconds + $int->seconds;
		if($seconds>59) {
			$this->minutes+=(int)$seconds/60;
			$seconds=$seconds%60;
		}
		
		$minutes = $this->minutes + $int->minutes;
		if($minutes>59) {
			$hours+=(int)$minutes/60;
			$minutes=$minutes%60;
		}
		return new Interval($hours.':'.$minutes.':'.$seconds);		
	}
	public function getValue() {
		return sprintf('%01d',$this->hours).':'.sprintf('%02d',$this->minutes).':'.sprintf('%02d',$this->seconds);
	}
	
	public function __toString() {
		return $this->getValue();
	}
	
}
?>