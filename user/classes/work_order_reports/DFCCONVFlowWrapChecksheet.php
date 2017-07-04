<?php  
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DFCCONVFlowWrapChecksheet extends PrintObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($MFWorkorders) { 
		
		$this->printparams['subject']='DFCCONVFlowWrapChecksheet';
		$this->printparams['defaultfilename'] = 'DFCCONVFlowWrapChecksheet';
		
		$this->document['title']='DFC-CONV Flow-Wrap Checksheet';
		$this->document['size']='A4';
		$this->document['orientation']='landscape';
											
		// get product code ---
		$stitem=new STItem();
		$stitem->load($MFWorkorders->stitem_id);
	
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>580,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>3
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>800
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>420
														 ,'xOrientation'=>'center'
														 ,'fontSize'=>13
														),
						'cols'=>array('1'=>array('title'=>'<b>DFC-CONV FLOW-WRAP CHECKSHEET</b>','width'=>800,'justification'=>'center')
													)
											)
										);
										
		$header_text=array(array('value'=>'Initial checks to be made at start up')
						  ,array('value'=>'Further checks to be made at 1 hourly intervals or following breakdown'));

		$this->document['header']['content'][]=array(
								'type'=>'table',
								'contains'=>array('top-margin'=>555,
												  'uses'=>$header_text,
												  'format'=>array('textCol'=>$this->textcol
																 ,'rowGap'=>2
																 ,'showHeadings'=>0
																 ,'width'=>300
																 ,'showLines'=>0
																 ,'shaded'=>0
																 ,'lineCol'=>$this->linecol
																 ,'xPos'=>20
																 ,'xOrientation'=>'right'
																 ,'fontSize'=>9
																)
								 ,'cols'=>array('value'=>array('title'=>'Description')
															    )
													)
												);									

 		$header_table=array(array('desc'=>'PRODUCT CODE','value'=>$stitem->item_code.'')
 							,array('desc'=>'WORKS ORDER','value'=>$MFWorkorders->wo_number.'')
 							,array('desc'=>'DATE CODE AT START','value'=>''));
		
		$this->document['header']['content'][]=array(
								'type'=>'table',
								'contains'=>array('top-margin'=>570,
 									 			  'uses'=>$header_table,
									  			  'format'=>array('textCol'=>$this->textcol
																 ,'rowGap'=>2
																 ,'showHeadings'=>0
																 ,'width'=>110
															   	 ,'showLines'=>2
															   	 ,'shaded'=>0
															   	 ,'lineCol'=>$this->linecol
															   	 ,'xPos'=>612
															   	 ,'xOrientation'=>'right'
														 		 ,'fontSize'=>9
														 		 ,'float-bottom-margin'=>true
																),
												  'cols'=>array('desc'=>array('title'=>'desc','width'=>110)
															   ,'value'=>array('title'=>'value','width'=>100)
												  				)
													)
												);				
										
		$data=array();
		for ($counter=1;$counter<=30;$counter++) {
			$data[$counter]=array('1'=>''
								 ,'2'=>''
								 ,'3'=>''
								 ,'4'=>''
								 ,'5'=>''
								 ,'6'=>''
								 ,'7'=>''
								 ,'8'=>''
								 ,'9'=>''
								 ,'10'=>''
								 ,'11'=>''
								 ,'12'=>'');
		}
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>10,
										  'uses'=>$data,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>2
														 ,'colGap'=>2
														 ,'showHeadings'=>1
														 ,'width'=>800
														 ,'showLines'=>2
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>9
														 ,'float-bottom-margin'=>true
														),
						'cols'=>array('1'=>array('title'=>'DATE','width'=>55,'justification'=>'center')
									 ,'2'=>array('title'=>'TIME','width'=>55,'justification'=>'center')
									 ,'3'=>array('title'=>'SHEET SIZE LENGTH / WIDTH','width'=>90,'justification'=>'center')
									 ,'5'=>array('title'=>'SHEET COUNT','width'=>50,'justification'=>'center')
									 ,'6'=>array('title'=>'DATE CODE / TIME LEGIBLE','width'=>90,'justification'=>'center')
									 ,'7'=>array('title'=>'NETT PACK WEIGHT (GMS)','width'=>90,'justification'=>'center')
									 ,'8'=>array('title'=>'OPENING FLAP OPENS CLEANLY','width'=>90,'justification'=>'center')
									 ,'9'=>array('title'=>'FIRMLY GLUED END','width'=>90,'justification'=>'center')
									 ,'10'=>array('title'=>'APPEARANCE','width'=>90,'justification'=>'center')
									 ,'11'=>array('title'=>'MET.DET Y/N','width'=>50,'justification'=>'center')
									 ,'12'=>array('title'=>'INITIALS','width'=>50,'justification'=>'center')
									)
								)
							);		
													
		$this->document['footer']['content'][]=array(
								'type'=>'table',
								'contains'=>array('top-margin'=>30,
												  'format'=>array('textCol'=>$this->textcol
																 ,'rowGap'=>2
																 ,'showHeadings'=>0
																 ,'width'=>555
																 ,'showLines'=>0
																 ,'shaded'=>0
																 ,'lineCol'=>$this->linecol
																 ,'xPos'=>20
																 ,'xOrientation'=>'right'
																 ,'fontSize'=>5
																)
												  ,'cols'=>array('1'=>array('value'=>'DFC-FW/HCS                  Issue 1                  10/02/2009')
															    )
													)
												);									
			
		$this->document['criteria']=''; 
	}
}
?>
