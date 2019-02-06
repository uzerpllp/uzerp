<?php



class VatOutputsCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatOutputs', $tablename='gltransactions_vat_outputs') {
        parent::__construct($do, $tablename);
    }
}
?>