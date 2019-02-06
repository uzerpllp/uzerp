<?php



class VatInputsCollection extends DataObjectCollection {
    
    public $field;
    
    function __construct($do='VatInputs', $tablename='gltransactions_vat_inputs') {
        parent::__construct($do, $tablename);
    }
}
?>