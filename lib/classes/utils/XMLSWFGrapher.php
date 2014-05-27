<?php
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class XMLSWFGrapher {
	
	protected $version='$Revision: 1.3 $';
	
	protected $dom;
	protected $id;
	function __construct($id) {
		$this->dom=new DOMDocument('1.0');
		$this->dom->formatOutput = true;
		$this->id=$id;
	}

	function isCached() {
		if(file_exists(DATA_ROOT.'tmp/chart'.$this->id))
			return true;
	}


	function createChart($type) {
		$chart=$this->dom->createElement('chart');
		$this->dom->appendChild($chart);
		
		$chart_type=$this->dom->createElement('chart_type',$type);
		$chart->appendChild($chart_type);
		
		$chart_value=$this->dom->createElement('chart_value');
		$chart->appendChild($chart_value);

		$axis_value=$this->dom->createElement('axis_value');

		switch($type) {
			case 'pie' :
				$chart_value->setAttribute('position','outside');
				break;
			case 'bar' :	
				$this->hideLegend();
				/* The following set the font size and increase the graph size to make up for the removed legend.
 				 * This will probably only work for landscape graphs and will need looking into updating but
				 * works at present */
				$axis_category=$chart->appendChild($this->dom->createElement('axis_category'));

				$axis_category->setAttribute('size','10');

				$axis_value->setAttribute('size','10');

				$chart_rect=$chart->appendChild($this->dom->createElement('chart_rect'));

				$chart_rect->setAttribute('x','100');
				$chart_rect->setAttribute('y','20');
				$chart_rect->setAttribute('height','165');
				$chart_rect->setAttribute('width','350');
				break;
			case 'line':
				$chart_rect=$chart->appendChild($this->dom->createElement('chart_rect'));
				$chart_rect->setAttribute('x','100');
				$chart_rect->setAttribute('y','20');
				$chart_rect->setAttribute('height','160');
				$chart_rect->setAttribute('width','350');
				$chart_value->setAttribute('chart_type','line');
				$axis_value->setAttribute('min', 0);
				break;
		}
		
		$chart->appendChild($axis_value);
		
		$chart_data=$this->dom->createElement('chart_data');
		$chart->appendChild($chart_data);
		return $chart;
	}
	
	function &getAxisCategory() {
		$chart=$this->dom->getElementsByTagName('chart')->item(0);
		$axis_category = $this->dom->createElement('axis_category');
		$chart->appendChild($axis_category);
		return $axis_category;
	}
	
	function &getAxisValue() {
		$axis_value = $this->dom->getElementsByTagName('axis_value')->item(0);
		return $axis_value;
	}
	
	function &getChartRect() {
		$chart_rect=$this->dom->getElementsByTagName('chart_rect')->item(0);
		return $chart_rect;
	}
	
	function &getDraw() {
		$draw = $this->dom->createElement('draw');
		$this->dom->getElementsByTagName('chart')->item(0)->appendChild($draw);
		return $draw;
	}
	
	function setText($text='', $x=0, $y=0, $width=500) {
		$draw=$this->getDraw();
		$draw->appendChild(new DOMElement('text',$text));
		$text = $this->dom->getElementsByTagName('text')->item(0);
		$text->setAttribute('x',$x);
		$text->setAttribute('y',$y);
		$text->setAttribute('width',$width);
	}
	
	function setLabelPrefix($prefix) {
		$c_val=$this->dom->getElementsByTagName('chart_value')->item(0);
		$c_val->setAttribute('prefix',$prefix);
	}
	
	function setAxisPrefix($prefix) {
		$a_val=$this->dom->getElementsByTagName('axis_value')->item(0);
		$a_val->setAttribute('prefix',$prefix);
	}
	
	function hideLegend() {
		$chart=$this->dom->getElementsByTagName('chart')->item(0);
		$legend=$chart->appendChild($this->dom->createElement('legend_rect'));
		
		$legend->setAttribute('x','-100');
		$legend->setAttribute('y','-100');

	}
	
	function setLegend($x=0,$y=0) {
		$chart=$this->dom->getElementsByTagName('chart')->item(0);
		$legend=$chart->appendChild($this->dom->createElement('legend_rect'));
		
		$legend->setAttribute('x',$x);
		$legend->setAttribute('y',$y);

	}
	
	function addRow() {
		$row = $this->dom->createElement('row');
		$this->dom->getElementsByTagName('chart_data')->item(0)->appendChild($row);
		return $row;
	}
	
	function addLabelsRow() {
		$row=$this->addRow();
		$row->appendChild(new DOMElement('null'));
		return $row;
	}
	
	function addDataRow($label) {
		$row=$this->addRow();
		$row->appendChild(new DOMElement('string',$label));
		return $row;
	}
	
	function setSeriesColourForUsername($username) {
		$colour = substr(md5($username),0,6);
		$this->setSeriesColours($username);
	}
	
	function setSeriesColours($colours) {
		if(!is_array($colours)) {
			$colours=array($colours);
		}
		$chart=$this->dom->getElementsByTagName('chart')->item(0);
		$series_colours = $this->dom->createElement('series_color');
		foreach($colours as $colour) {
			$series_colours->appendChild(new DOMElement('color',$colour));
		}
		$chart->appendChild($series_colours);
	}
	
	function out() {
		echo $this->dom->saveXML($this->dom->getElementsByTagName('chart')->item(0));
	}
	
	function save() {
		$fp=fopen(DATA_ROOT.'tmp/chart'.$this->id,'w+');
		fwrite($fp,$this->dom->saveXML($this->dom->getElementsByTagName('chart')->item(0)));
		fclose($fp);
	}

}
?>
