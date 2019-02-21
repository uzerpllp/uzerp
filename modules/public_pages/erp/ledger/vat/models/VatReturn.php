<?php
class VatReturn extends DataObject
{
    protected $defaultDisplayFields = [
        year,
        tax_period,
        vat_due_sales, //Box 1 - VAT Due On Sales
        vat_due_aquisitions, //Box 2 - VAT Due On EU Purchases
        total_vat_due, // Box 3 - Output Tax - CHECK THIS!!
        vat_reclaimed_curr_period, //Box 4 - Input Tax - CHECK THIS!!
        net_vat_due, //Box 5 - Net Tax
        total_value_sales_ex_vat, //Box 6 - Sales Exc. VAT
        total_value_purchase_ex_vat, //Box 7 - Purchases Exc. VAT
        total_value_goods_supplied_ex_vat, //Box 8 - EU Sales Exc. VAT
        total_aquisitions_ex_vat, //Box 9 -EU Purchases Exc. VAT
        finalised
    ];

    public function __construct($tablename='vat_hmrc') {
        parent::__construct($tablename);
        $this->idField='id';
    }
}
?>