<?php

/**
 *	(c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	Released under GPLv3 license; see LICENSE.
 **/
class POProductlineHeader extends DataObject
{

    protected $version = '$Revision: 1.9 $';

    protected $defaultDisplayFields = array(
        'gl_account',
        'gl_centre',
        'stitem',
        'uom_name',
        'description',
        'start_date',
        'end_date',
        'tax_rate',
        'product_group',
        'glaccount_id',
        'glcentre_id',
        'latest_cost',
        'std_cost',
        'stitem_id',
        'stuom_id',
        'tax_rate_id',
        'prod_group_id'
    );

    public function __construct($tablename = 'po_product_lines_header')
    {

        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'description';
        $this->orderby = 'description';
        $this->setTitle('PO Product');

        // Define relationships
        $this->belongsTo('STItem', 'stitem_id', 'stitem');
        $this->belongsTo('STuom', 'stuom_id', 'uom_name');
        $this->belongsTo('GLAccount', 'glaccount_id', 'gl_account');
        $this->belongsTo('GLCentre', 'glcentre_id', 'gl_centre');
        $this->belongsTo('TaxRate', 'tax_rate_id', 'tax_rate');
        $this->belongsTo('STProductgroup', 'prod_group_id', 'product_group');
        $this->hasOne('STItem', 'stitem_id', 'item_detail');

        $this->hasMany('POProductLine', 'lines', 'productline_header_id');

        // Define field formats

        // set formatters

        // Define validation
        $this->validateUniquenessOf('stitem_id', NULL, TRUE);
        $this->validateUniquenessOf('description');

        // Define enumerated types

        // set defaults

        // Set link rules for 'belongs to' to appear in related view controller sidebar
    }

    /*
     * Update descriptions on linked productlines
     */
    public function updateProductlineDescriptions(&$errors)
    {
        $db = DB::Instance();
        $db->StartTrans();
        $product_lines = new POProductlineCollection(new POProductLine());
        $sh = new SearchHandler($product_lines, false);
        $cc = new ConstraintChain();
        $cc->add(new Constraint('productline_header_id', '=', $this->id));
        $cc->add(currentDateConstraint());
        $sh->addConstraintChain($cc);
        $product_lines->load($sh);

        if ($product_lines->count() > 0) {
            $updated_product_lines = $product_lines->update(['description'], [$this->description], $sh);
            if ($updated_product_lines === false) {
                $errors[] = 'Error updating product lines : ' . $db->ErrorMsg();
                $db->FailTrans();
            }
        }
        $db->CompleteTrans();
    }

    public function getProductGroups($stitem_id = '')
    {
        if (empty($stitem_id) && $this->isLoaded()) {
            $stitem_id = $this->stitem_id;
        }

        if (empty($stitem_id)) {
            $pg = DataObjectFactory::Factory('STProductgroup');
            return $pg->getAll();
        } else {
            $this->loadSTItem($stitem_id);

            return array(
                $this->item_detail->prod_group_id => $this->item_detail->stproductgroup
            );
        }
    }

    public function getProductGroup()
    {
        if (is_null($this->stitem_id)) {
            return $this->prod_group_id;
        } else {
            return $this->item_detail->prod_group_id;
        }
    }

    public function getUomList($stitem_id = '')
    {
        if ($this->isLoaded() && empty($stitem_id)) {
            $stitem_id = $this->stitem_id;
        }

        $this->loadSTItem($stitem_id);

        return $this->item_detail->getUomList();
    }

    public function getEndDate($stitem_id = '')
    {
        if ($this->isLoaded() && empty($stitem_id)) {
            $stitem_id = $this->stitem_id;
        }

        if (empty($stitem_id)) {
            return null;
        } else {
            $this->loadSTItem($stitem_id);

            return $this->item_detail->obsolete_date;
        }
    }

    public function getPrice($stitem_id = '')
    {
        if ($this->isLoaded() && empty($stitem_id)) {
            $stitem_id = $this->stitem_id;
        }

        if (empty($stitem_id)) {
            return 0;
        } else {
            $this->loadSTItem($stitem_id);

            return $this->item_detail->price;
        }
    }

    public function getItem($stitem_id = '')
    {
        if ($this->isLoaded() && empty($stitem_id)) {
            $stitem_id = $this->stitem_id;
        }

        if (empty($stitem_id)) {
            return '';
        } else {
            $this->loadSTItem($stitem_id);

            return $this->item_detail->getIdentifierValue();
        }
    }

    public function checkOrderlines($_productlines = array())
    {
        $order_items = array();

        if (! is_array($_productlines)) {
            $_productlines = array(
                $_productlines
            );
        }

        if (count($_productlines) > 0) {
            $orderline = DataObjectFactory::Factory('POrderline');

            $cc = new ConstraintChain();
            $cc->add(new Constraint('productline_id', 'in', '(' . implode(',', $_productlines) . ')'));

            $order_items = $orderline->getAll($cc);
        }

        return $order_items;
    }

    public function checkInvoicelines($_productlines = array())
    {
        $invoice_items = array();

        if (! is_array($_productlines)) {
            $_productlines = array(
                $_productlines
            );
        }

        if (count($_productlines) > 0) {

            $cc = new ConstraintChain();
            $cc->add(new Constraint('productline_id', 'in', '(' . implode(',', $_productlines) . ')'));

            $invoiceline = DataObjectFactory::Factory('PInvoiceline');

            $invoice_items = $invoiceline->getAll($cc, true, true);
        }

        return $invoice_items;
    }

    public function getLineIds($_productline_header_id = '')
    {
        if ($this->isLoaded() && empty($_productline_header_id)) {
            $_productline_header_id = $this->id;
        }

        if (empty($_productline_header_id)) {
            return false;
        }

        $product_line = DataObjectFactory::Factory('POProductline');

        $product_line->identifierField = 'id';

        $cc = new ConstraintChain();

        $cc->add(new Constraint('productline_header_id', '=', $_productline_header_id));

        return $product_line->getAll($cc);
    }

    public function end_lines()
    {
        $productlines = new POProductlineCollection(DataObjectFactory::Factory('POProductline'));
        $sh = new SearchHandler($productlines, FALSE);

        $sh->addConstraint(new Constraint('productline_header_id', '=', $this->id));

        $cc = new ConstraintChain();
        $cc->add(new Constraint('end_date', 'is', 'NULL'));
        $cc->add(new Constraint('end_date', '>', $this->end_date), 'OR');

        $sh->addConstraint($cc);

        return $productlines->update('end_date', $this->end_date, $sh);
    }

    public function save()
    {
        $db = DB::Instance();
        $flash = Flash::Instance();

        $db->StartTrans();

        $result = parent::save();

        if (! $result) {
            $flash->addError('Error saving product : ' . $db->ErrorMsg());
            $db->FailTrans();
        } else {
            // Check for current orders/invoices that use this product lines

            if (! is_null($this->end_date) && $this->end_date != 'null') {
                $result = $this->end_lines();

                // $result contains number of lines closed off;
                // this may be zero, which is valid if there are no lines
                // or all lines have been closed previously
                if ($result !== false) {
                    $flash->addMessage('Closed off ' . $result . ' open price' . (($result > 1) ? 's' : '') . ' at product end date');
                } else {
                    $flash->addError('Error checking end dates on prices : ' . $db->ErrorMsg());
                    $db->FailTrans();
                }
            }
        }

        $db->CompleteTrans();

        // As above, $result will contain the number of lines updated (which could be zero)
        // or will set to FALSE on error. Need to return TRUE or FALSE, with zero lines being TRUE!
        return ($result !== false);
    }

    /*
     * Private Functions
     */
    private function loadSTItem($stitem_id)
    {
        if (! is_null($this->stitem_id) && $this->stitem_id == $stitem_id) {
            return;
        }

        $this->stitem_id = $stitem_id;
    }
}

// end of POProductlineHeader
