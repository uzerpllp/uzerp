<?php

class MFPlanned extends DataObject
{
	protected $defaultDisplayFields = [
        'item_code',
        'stitem',
        'qty',
        'uom_name',
        'start_date',
        'required_by'
    ];

    function __construct($tablename='mf_planned')
	{
        parent::__construct($tablename);
		//$this->idField = 'stitem_id';
		//$this->identifierField = 'stitem_id';
        $this->orderby='required_by';
        $this->orderdir='desc';
        
        $this->belongsTo('STItem', 'stitem_id', 'stitem');
        //$this->hasOne('STItem', 'stitem_id', 'stock_item');
    }
}