<?php
class VatEuSales extends DataObject {
    protected $defaultDisplayFields = [
        'transaction_date',
        'gl_id',
        'docref',
        'ext_reference',
        'customer',
        'comment',
        'vat',
        'net',
        'source',
        'type'
    ];

    function __construct($tablename='gl_taxeusales') {
        parent::__construct($tablename);
        $this->orderby = ['transaction_date'];

        // Use enumerators from the GLT model
        $glt = new GLTransaction;
        $this->setEnum('type', $glt->enums['type']);
        $this->setEnum('source', $glt->enums['source']);
    }
}
?>