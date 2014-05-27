<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class EGSPie extends EGSChart{

	protected $version='$Revision: 1.2 $';
	
	protected $type='pie';
	private $plot_size=0.15;

	public function __construct($data=null,$id=null,$width=600,$height=400)  {
		parent::__construct($data,$id);
		require (APP_ROOT."plugins/jpgraph/jpgraph_pie.php"); 
//		require (APP_ROOT."plugins/jpgraph/jpgraph_pie3d.php"); 
		$this->grapher  = new PieGraph($width,$height);
		$this->grapher->SetScale('linlin');
		$this->grapher->SetShadow();
		$this->addPlots($data);
	}
	
	public function addSinglePlot($data,$x=0.5,$y=0.5,$title=null) {
		$plot = new PiePlot(array_values($data));
		$plot->SetSize($this->plot_size);
		$plot->SetCenter($x,$y);
		$plot->setLabelPos(0.6);
		//$plot->setLegends(array_keys($data));
		if($title!=null) {
			$plot->title->set($title);
		}
		$this->grapher->Add($plot);
	}
	public function addPlots($data) {
		if(!is_array(current($data))) {
			$data=array($data);
		}		
		$num_plots = count($data);
		$centres = $this->getCoords($num_plots);
		$i=0;
		foreach($data as $key=>$plot_data) {
			$this->addSinglePlot($plot_data,$centres[$i][0],$centres[$i][1],$key);
			$i++;
		}
	}
	
	public function getCoords($count) {
		$points=array();
		switch($count) {
			case 1:
				$points=array(
					array(0.5,0.5)
				);
				break;
			case 4: $points=array(
				array(0.25,0.32),
				array(0.25,0.75),
				array(0.75,0.32),
				array(0.75,0.75)
			);
		}
		return $points;
	}
}