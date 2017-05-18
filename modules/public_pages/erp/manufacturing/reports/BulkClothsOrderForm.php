<?php   
 
/** 
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved. 
 * 
 *	Released under GPLv3 license; see LICENSE. 
 **/
class BulkClothsOrderForm extends PrintObject {

	protected $version='$Revision: 1.8 $';
	
	function __construct($MFWorkorders) {
		$this->printparams['subject']='Works Order Form';
		$this->printparams['defaultfilename'] = 'works_order_form';
		
		$this->document['title']='Works Order Form';
		$this->document['size']='A4';
		$this->document['orientation']='portrait';
		$this->document['header']=array();
		$this->document['header']['options']=array('pages'=>1);
		$this->document['header']['content']=array();
	
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>3
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>295
														 ,'xOrientation'=>'center'
														 ,'fontSize'=>18
														),
						'cols'=>array('title'=>array('title'=>'WORKS ORDER FORM','width'=>205)
													)
											)
										);										
		
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>20,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>5
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>12
 										  				 ),
						  'cols'=>array('order_no'=>array('title'=>'Order No.','width'=>100)
										,'blank1'=>array('title'=>$MFWorkorders->wo_number,'width'=>176)
										,'date'=>array('title'=>'Customer','width'=>100)
										,'blank2'=>array('title'=>'_______________________','width'=>176)
													 )
											)
										);	
		// Display only the product code (stitems)										
		if(ereg(' - ', $MFWorkorders->stitem)) {
			$stitem=split(" - ",$MFWorkorders->stitem);										
		} else {
			$stitem[0]=$MFWorkorders->stitem;										
		}
											
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>0,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>5
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
													     ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>12
 										  				 ),
						  'cols'=>array('order_no'=>array('title'=>'Product','width'=>100)
										,'blank1'=>array('title'=>$stitem[0],'width'=>176)
										,'date'=>array('title'=>'No. of Pallets','width'=>100)
										,'blank2'=>array('title'=>'_______________________','width'=>176)
													 )
											)
										);	

		// Check if data sheet id exists, if not, out put line
		if($MFWorkorders->data_sheet_id=='') {
			$data_sheet='_______________________';										
		} else {
			$data_sheet=$MFWorkorders->data_sheet_id;
		}
		
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>0,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>5
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>12
 										  				 ),
						  'cols'=>array('order_no'=>array('title'=>'Data Sheet No.','width'=>100)
										,'blank1'=>array('title'=>$data_sheet,'width'=>176)
										,'date'=>array('title'=>'No. of Cases','width'=>100)
										,'blank2'=>array('title'=>'_______________________','width'=>176)
													 )
											)
										);	

		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>20,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>3
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'shaded'=>0
														 ,'width'=>555
														 ,'showLines'=>0
														 ,'shaded'=>1
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>295
														 ,'xOrientation'=>'center'
														 ,'fontSize'=>16
														),
						 'cols'=>array('title'=>array('title'=>'Check Data Sheet for Information','width'=>245)
													)
											)
										);

		$data=array();
		for ($counter=1;$counter<=6;$counter++) {
			$data[$counter]=array('audit'=>''
								 ,'time'=>''
								 ,'pass_fail'=>''
								 ,'initials'=>''
								 ,'blank1'=>'');
		}
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>5,
										  'uses'=>$data,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>1
														 ,'width'=>555
														 ,'showLines'=>2
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
														 ,'float-bottom-margin'=>true
														),
						'cols'=>array('audit'=>array('title'=>'AUDIT DATE','width'=>80)
									 ,'time'=>array('title'=>'TIME','width'=>50)
								     ,'pass_fail'=>array('title'=>'PASS/FAIL','width'=>50)
								     ,'initials'=>array('title'=>'INITIALS/COMMENTS','width'=>155)
								     ,'blank1'=>array('title'=>'','value'=>'SPECIAL INSTRUCTION','width'=>220)
									 )
								)
							);		
				
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>480,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>178
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>450
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('pal_no'=>array('title'=>'Date','width'=>88,'justification'=>'center')
									 ,'cloths'=>array('title'=>'No. of Packs','width'=>90,'justification'=>'center')
													 )
											)
										);		
										
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>480,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>198
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>465
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('sig'=>array('title'=>'Sig','width'=>100,'justification'=>'center')
													 )
											)
										);		
																					
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>467,
 										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>198
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>450
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('complete'=>array('title'=>'Complete','width'=>50,'justification'=>'center')
									 ,'booked'=>array('title'=>'Booked','width'=>50,'justification'=>'center')
													 )
											)
										);		
																	
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>480,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>178
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>298
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>450
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('pal_no'=>array('title'=>'Date','width'=>88,'justification'=>'center')
									 ,'cloths'=>array('title'=>'No. of Packs','width'=>90,'justification'=>'center')
													 )
											)
										);		
										
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>480,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>476
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>465
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('sig'=>array('title'=>'Sig','width'=>100,'justification'=>'center')
													 )
											)
										);		
																					
		$this->document['header']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>467,
 										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>1
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>1
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>476
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>8
 										  				 ,'bottom-margin'=>450
 										  				 ,'float-bottom-margin'=>true
														),
						'cols'=>array('cmoplete'=>array('title'=>'Complete','width'=>50,'justification'=>'center')
									 ,'booked'=>array('title'=>'Booked','width'=>50,'justification'=>'center')
													 )
											)
										);		
																								
						for ($counter=1;$counter<=13;$counter+=1) {
							if($counter==1)
								$sb=4;
							else
								$sb=0;
								
									$this->document['header']['content'][]=array(
												'type'=>'table',
												'contains'=>array('spacebefore'=>$sb,
						 										  'format'=>array('textCol'=>$this->textcol
																				 ,'rowGap'=>5
																				 ,'colGap'=>2
																				 ,'showHeadings'=>0
																				 ,'width'=>555
																				 ,'showLines'=>1
																				 ,'shaded'=>0
																				 ,'lineCol'=>$this->linecol
																				 ,'xPos'=>20
																				 ,'xOrientation'=>'right'
																				 ,'fontSize'=>8
																				 ,'float-bottom-margin'=>true
																				),
						'cols'=>array('blank1'=>array('title'=>'','width'=>88,'justification'=>'center')
									 ,'blank3'=>array('title'=>'','width'=>90,'justification'=>'center')
									 ,'blank5'=>array('title'=>'','width'=>50,'justification'=>'center')
									 ,'blank6'=>array('title'=>'','width'=>50,'justification'=>'center')
									 ,'blank7'=>array('title'=>'','width'=>88,'justification'=>'center')
									 ,'blank9'=>array('title'=>'','width'=>90,'justification'=>'center')
									 ,'blank11'=>array('title'=>'','width'=>50,'justification'=>'center')
									 ,'blank12'=>array('title'=>'','width'=>50,'justification'=>'center')
																			 )
																	)
																);
						}
								
		$this->document['footer']['content'][]=array(
						'type'=>'table',
						'contains'=>array('top-margin'=>80,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>0
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>0
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>7
														),
						'cols'=>array('ref'=>array('title'=>'<b>Ref</b>','width'=>50)
								   	,'ref_no'=>array('title'=>'CE/COF/','width'=>50)
													 )
											)
										);								
										
		$this->document['footer']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>5,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>0
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>0
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>7
														),
						'cols'=>array('blank'=>array('title'=>'','width'=>50)
								   	,'issue'=>array('title'=>'Issue 8:','width'=>50)
													 )
											)
										);								
										
		$this->document['footer']['content'][]=array(
						'type'=>'table',
						'contains'=>array('spacebefore'=>5,
  										  'format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>0
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>0
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>7
														),
						'cols'=>array('blank1'=>array('title'=>'','width'=>50)
									,'date'=>array('title'=>'20/12/2006','width'=>50)
													 )
											)
										);		
	
		$this->document['footer']['content'][]=array(
						'type'=>'table',
						'contains'=>array('format'=>array('textCol'=>$this->textcol
														 ,'rowGap'=>0
														 ,'colGap'=>2
														 ,'showHeadings'=>0
														 ,'width'=>100
														 ,'showLines'=>0
														 ,'shaded'=>0
														 ,'lineCol'=>$this->linecol
														 ,'xPos'=>20
														 ,'xOrientation'=>'right'
														 ,'fontSize'=>5
														),
						'cols'=>array('ref'=>array('title'=>'Bulk Cloths','width'=>150)
													 )
											)
										);								
		
		$this->document['criteria']=''; 
	}
}
?>
