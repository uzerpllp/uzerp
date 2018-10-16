<?php

class POPlanned extends DataObject
{
	protected $defaultDisplayFields = [
        'order_date',
        'stitem' => 'Item',
        'description',
        'supplier_name',
        'delivery_date',
        'qty' => 'Qty Required',
        'product_group_desc',
    ];

    function __construct($tablename='po_planned')
	{
        parent::__construct($tablename);
        $this->orderby='order_date';
        $this->orderdir='asc';
        
        $this->belongsTo('STItem', 'stitem_id', 'stitem');
    }
}