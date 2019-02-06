<?php
/**
 *	@author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *	@license GPLv3 or later
 *	@copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class VatInputs extends DataObject {
    protected $defaultDisplayFields = [
        'transaction_date',
        'gl_id',
        'docref',
        'ext_reference',
        'supplier',
        'comment',
        'vat',
        'net',
        'source',
        'type'
    ];

    function __construct($tablename='gltransactions_vat_inputs') {
        parent::__construct($tablename);
        $this->orderby = ['transaction_date'];

        // Use enumerators from the GLT model
        $glt = new GLTransaction;
        $this->setEnum('type', $glt->enums['type']);
        $this->setEnum('source', $glt->enums['source']);
    }
}
?>