<?php



class VatEuPurchasesCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatEuPurchases', $tablename='gl_taxeupurchases') {
        parent::__construct($do, $tablename);
    }
}
?>