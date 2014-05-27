<?php  
 
/** 
 *	(c) 2000-2012 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class DFCCONVReelLoadingChecksheet extends PrintObject {

	protected $version='$Revision: 1.3 $';
	
	function __construct($MFWorkorders) { 
		
		$this->printparams['subject']='DFCCONVReelLoadingChecksheet';
		$this->printparams['defaultfilename'] = 'DFCCONVReelLoadingChecksheet';
		
		$this->document['title']='DFC-CONV Reel Loading Checksheet';
		$this->document['size']='A4';
		$this->document['orientation']='portrait';
											
		// get product code --- $stitem->item_code
		$stitem=new STItem();
		$stitem->load($MFWorkorders->stitem_id);
	
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>0,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>3
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>13
														),
						'cols'=>array('1'=>array('title'=>'<b>DFC-CONV REEL LOADING CHECKSHEET</b>','width'=>555,'justification'=>'center')
													)
											)
										);
										
		$header_text=array(array('value'=>'Date and time of each reel loaded to be entered onto sheet'));

		$this->document['header']['content'][]=array(
								'type'=>'table',
								'contains'=>array('spacebefore'=>10,
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
 							,array('desc'=>'WORKS ORDER','value'=>$MFWorkorders->wo_number.''));
		
		$this->document['header']['content'][]=array(
								'type'=>'table',
								'contains'=>array('spacebefore'=>10,
 									 			  'uses'=>$header_table,
									  			  'format'=>array('textCol'=>$this->textcol
																 ,'rowGap'=>2
														 		 ,'colGap'=>2
																 ,'showHeadings'=>0
																 ,'width'=>110
															   	 ,'showLines'=>2
															   	 ,'shaded'=>0
															   	 ,'lineCol'=>$this->linecol
															   	 ,'xPos'=>20
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
		for ($counter=1;$counter<=40;$counter++) {
			$data[$counter]=array('1'=>''
								 ,'2'=>''
								 ,'3'=>''
								 ,'4'=>''
								 ,'5'=>''
								 ,'6'=>'');
		}
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>10,
										  'uses'=>$data,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>3
														 ,'colGap'=>2
														 ,'showHeadings'=>1
														 ,'width'=>555
														 ,'showLines'=>2
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>9
														 ,'float-bottom-margin'=>true
														),
						'cols'=>array('1'=>array('title'=>'DATE','width'=>70,'justification'=>'center')
									 ,'2'=>array('title'=>'TIME','width'=>70,'justification'=>'center')
									 ,'3'=>array('title'=>'REEL NUMBER','width'=>137.5,'justification'=>'center')
									 ,'4'=>array('title'=>'DATE','width'=>70,'justification'=>'center')
									 ,'5'=>array('title'=>'TIME','width'=>70,'justification'=>'center')
									 ,'6'=>array('title'=>'REEL NUMBER','width'=>137.5,'justification'=>'center')
									)
								)
							);		
													
		$footer_ref=array(array('value'=>'Issue 2:')
						  ,array('value'=>'13/02/2006'));

		$this->document['footer']['content'][]=array(
								'type'=>'table',
								'contains'=>array('top-margin'=>40,
												  'uses'=>$footer_ref,
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
									,'cols'=>array('value'=>array('title'=>'Description')
															    )
													)
												);																			
										
		$this->document['criteria']=''; 
	}
}
?>
