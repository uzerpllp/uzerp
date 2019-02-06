<?php



class VatEuSalesCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatEuSales', $tablename='gl_taxeusales') {
        parent::__construct($do, $tablename);
    }
}
?>