<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EGSBar extends EGSChart {
	protected $version='$Revision: 1.2 $';
	
	static $colours = array('blue','cadetblue','brown3');

	public function __construct($data=null,$id=null) {
		parent::__construct($data,$id);
		require (APP_ROOT."plugins/jpgraph/jpgraph_bar.php"); 
		
		$this->grapher  = new Graph(500,400);
		$this->grapher->SetScale('textlin');
		
		$this->grapher->SetShadow();
		$this->addPlots($data);
	}
	
	function addPlots($data) {
		$plots=array();
		$labels=array();
		$i=0;
		foreach($data as $key=>$plot_data) {
			$plot =  new BarPlot(array_values($plot_data));
			$plot->setLegend(prettify($key));
			$plot->setFillColor(self::$colours[$i]);
			$i++;
			$plots[] =$plot;
			$labels=array_merge($labels,array_keys($plot_data));
		}
		
		$group_plot = new GroupBarPlot($plots);
		$this->grapher->add($group_plot);
		$this->grapher->xaxis->setTickLabels($labels);
		
	}
	
	function addPlot($data) {
		$plot = new BarPlot(array_values($data));
		$this->grapher->add($plot);
	}
	
}
?>