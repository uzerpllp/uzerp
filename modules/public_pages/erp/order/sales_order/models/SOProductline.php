<?php

/**
 *	uzERP SOProductline Model
 *
 *	@author uzERP LLP
 *	@license GPLv3 or later
 *	@copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *	uzERP is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU General Public License as published by
 *	the Free Software Foundation, either version 3 of the License, or
 *	any later version.
 */
class SOProductline extends DataObject
{

    protected $version = '$Revision: 1.31 $';

    protected $defaultDisplayFields = array(
        'description',
        'customer',
        'customer_product_code',
        'glaccount' => 'GL Account',
        'glcentre' => 'GL Centre',
        'stitem' => 'Stock Item',
        'stproductgroup' => 'Product Group',
        'uom_name',
        'so_price_type',
        'start_date',
        'end_date',
        'price',
        'currency',
        'taxrate' => 'Tax Rate',
        'slmaster_id',
        'stitem_id',
        'prod_group_id',
        'productline_header_id'
    );

    function __construct($tablename = 'so_product_lines')
    {

        // Register non-persistent attributes

        // Contruct the object
        parent::__construct($tablename);

        // Set specific characteristics
        $this->idField = 'id';
        $this->identifierField = 'description';
        $this->orderby = 'description';
        $this->setTitle('SO Product Line');

        // Define relationships
        $this->belongsTo('SLCustomer', 'slmaster_id', 'customer');
        $this->belongsTo('Currency', 'currency_id', 'currency');
        $this->belongsTo('GLAccount', 'glaccount_id', 'glaccount');
        $this->belongsTo('GLCentre', 'glcentre_id', 'glcentre');
        $this->belongsTo('SOPriceType', 'so_price_type_id', 'so_price_type');
        $this->belongsTo('SOProductlineHeader', 'productline_header_id', 'product');
        $this->hasOne('SOProductlineHeader', 'productline_header_id', 'product_detail');

        // Define field formats

        // set formatters

        // set validators

        // Define enumerated types

        // set defaults
        $params = DataObjectFactory::Factory('GLParams');
        $this->getField('currency_id')->setDefault($params->base_currency());

        // Set link rules for 'belongs to' to appear in related view controller sidebar
    }

    function cb_loaded()
    {
        $this->getField('price')->setFormatter(new CurrencyFormatter($this->_data['currency_id']));
    }

    function delete($id = null, &$errors = array())
    {
        return parent::delete($id, $errors, TRUE);
    }

    /**
     * getCustomerLines - Return a list of customer product lines (prices)
     *
     * Returns an array of product line id, product line description
     * containing all product lines specific to a customer
     * and all other non-specific customer product lines
     * that are for items not specific to the customer
     *
     * NOTE: To get filtered results in a single query, this function
     * uses sql queries, instead of constraint-chains and the
     * DataObject functions from the framework. It *should* be
     * faster than executing multiple queries and concatenating
     * the results in PHP.
     *
     * @param int $customer
     *            sl_master table record id
     * @param string $productsearch
     *            begining of description to search
     * @return (int|string)[]
     */
    public function getCustomerLines($customer, $productsearch = '')
    {
        $customerdetail = DataObjectFactory::Factory('SLCustomer');
        $customerdetail->load($customer);
        $price_type = $customerdetail->so_price_type_id;

        if (! is_null($price_type)) {
            $params_productsearch = [
                $productsearch . '%',
                EGS_COMPANY_ID,
                $customer,
                $price_type,
                $productsearch . '%',
                EGS_COMPANY_ID,
                $customer,
                $productsearch . '%',
                EGS_COMPANY_ID,
                $price_type,
                $productsearch . '%',
                EGS_COMPANY_ID
            ];

            $params = [
                EGS_COMPANY_ID,
                $customer,
                $price_type,
                EGS_COMPANY_ID,
                $customer,
                EGS_COMPANY_ID,
                $price_type,
                EGS_COMPANY_ID
            ];

            // Find prices for cust/price_type, cust only, price_type only,
            // default prices with no cust or price_type,
            // filtering out any prices found in the previous query.
            $query_productsearch = "WITH cust_type_prices AS
    	       (SELECT id, description, productline_header_id
    	           FROM so_productlines_overview
    	           WHERE description ILIKE ?
    	               AND (usercompanyid = ?
    	               AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	               AND (slmaster_id = ? AND so_price_type_id = ?)))),
    	           cust_type_products AS (SELECT productline_header_id FROM cust_type_prices)
    	           SELECT id, description FROM cust_type_prices
    	       UNION
    	       SELECT id, description
    	           FROM so_productlines_overview
    	           WHERE description ilike ?
    	           AND productline_header_id NOT IN (SELECT * FROM cust_type_products)
    	           AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	           AND (slmaster_id = ? AND so_price_type_id is NULL)))
    	       UNION
    	       (WITH cust_prices AS
    	           (SELECT id, description, productline_header_id
    	               FROM so_productlines_overview
    	               WHERE description ilike ?
    	               AND productline_header_id NOT IN (SELECT * FROM cust_type_products)
    	               AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	               AND (slmaster_id IS NULL AND so_price_type_id = ?)))),
    	           cust_products AS (SELECT productline_header_id FROM cust_prices)
    	           SELECT id, description FROM cust_prices
    	        UNION
    	        SELECT id, description
    	           FROM so_productlines_overview
    	           WHERE description ilike ?
    	           AND productline_header_id NOT IN (SELECT * FROM cust_type_products UNION SELECT * FROM cust_products)
    	           AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	           AND (slmaster_id IS NULL AND so_price_type_id IS NULL)))
    	       )
    	    ORDER BY description";

            // Save 100ms approx, if product search not needed
            $query = "WITH cust_type_prices AS
    	       (SELECT id, description, productline_header_id
    	           FROM so_productlines_overview
    	           WHERE (usercompanyid = ?
    	               AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	               AND (slmaster_id = ? AND so_price_type_id = ?)))),
    	           cust_type_products AS (SELECT productline_header_id FROM cust_type_prices)
    	           SELECT id, description FROM cust_type_prices
    	       UNION
    	       SELECT id, description
    	           FROM so_productlines_overview
    	           WHERE productline_header_id NOT IN (SELECT * FROM cust_type_products)
    	           AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	           AND (slmaster_id = ? AND so_price_type_id is NULL)))
    	       UNION
    	       (WITH cust_prices AS
    	           (SELECT id, description, productline_header_id
    	               FROM so_productlines_overview
    	               WHERE productline_header_id NOT IN (SELECT * FROM cust_type_products)
    	               AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	               AND (slmaster_id IS NULL AND so_price_type_id = ?)))),
    	           cust_products AS (SELECT productline_header_id FROM cust_prices)
    	           SELECT id, description FROM cust_prices
    	        UNION
    	        SELECT id, description
    	           FROM so_productlines_overview
    	           WHERE productline_header_id NOT IN (SELECT * FROM cust_type_products UNION SELECT * FROM cust_products)
    	           AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
    	           AND (slmaster_id IS NULL AND so_price_type_id IS NULL)))
    	       )
    	    ORDER BY description";
        } else {
            $params_productsearch = [
                $productsearch . '%',
                EGS_COMPANY_ID,
                $customer,
                $productsearch . '%',
                EGS_COMPANY_ID
            ];

            $params = [
                EGS_COMPANY_ID,
                $customer,
                EGS_COMPANY_ID
            ];

            $query_productsearch = "WITH cust_prices AS
	            (SELECT id, description, productline_header_id
	               FROM so_productlines_overview
	               WHERE description ILIKE ? AND
	               (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
	               AND (slmaster_id = ? AND so_price_type_id IS NULL)))),
                cust_products AS (SELECT productline_header_id FROM cust_prices)
                SELECT id, description FROM cust_prices
                UNION
                SELECT id, description
	               FROM so_productlines_overview
	               WHERE description ILIKE ? AND
	               productline_header_id NOT IN (SELECT * FROM cust_products)
	               AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
	               AND (slmaster_id IS NULL AND so_price_type_id IS NULL)))
                ORDER BY description";

            $query = "WITH cust_prices AS
	            (SELECT id, description, productline_header_id
	               FROM so_productlines_overview
	               WHERE (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
	               AND (slmaster_id = ? AND so_price_type_id IS NULL)))),
                cust_products AS (SELECT productline_header_id FROM cust_prices)
                SELECT id, description FROM cust_prices
                UNION
                SELECT id, description
	               FROM so_productlines_overview
	               WHERE productline_header_id NOT IN (SELECT * FROM cust_products)
	               AND (usercompanyid = ? AND ((start_date <= 'today'::date AND (end_date is NULL OR end_date >= 'today'::date))
	               AND (slmaster_id IS NULL AND so_price_type_id IS NULL)))
                ORDER BY description";
        }

        if ($productsearch != '') {
            $query = $query_productsearch;
            $params = $params_productsearch;
        }

        $db = &DB::Instance();
        $rset = $db->Execute($query, $params);

        if ($rset) {
            $rows = $rset->GetAssoc();
            return $rows;
        }

        return [];
    }

    function getCustomerItems($customer)
    {
        // Returns an array of product line id, stock item id
        // containing all stock items specific to a customer
        // and all other non-specific customer stock items

        // Firstly , get any items specific to the customer
        $cc = new ConstraintChain();

        $cc1 = new ConstraintChain();

        $cc1->add(new Constraint('slmaster_id', '=', $customer));
        $cc1->add(new Constraint('stitem_id', 'is not', 'NULL'));

        $cc->add($cc1);
        $cc->add($this->currentConstraint());

        $this->identifierField = 'stitem_id';

        $item_codes = $this->getAll($cc1, true, true);

        $cc = new ConstraintChain();

        if (! empty($item_codes)) {
            // There are items specific to the customer
            // so get all the other non-customer specific items as well
            $cc2 = new ConstraintChain();
            $cc2->add(new Constraint('slmaster_id', 'is', 'NULL'));
            $cc3 = new ConstraintChain();
            $cc3->add(new Constraint('stitem_id', 'not in', '(' . implode(',', $item_codes) . ')'));
            $cc2->add($cc3);
            $cc->add($cc1);
            $cc->add($cc2, 'OR');
        } else {
            // No items specific to the customer so get all non-customer specific items
            $cc->add(new Constraint('slmaster_id', 'is', 'NULL'));
            $cc->add(new Constraint('stitem_id', 'is not', 'NULL'));
        }

        $cc->add($this->currentConstraint());

        return $this->getAll($cc, true, true);
    }

    function getNonSpecific($productsearch = '', $_so_price_type_id = '')
    {
        $cc = new ConstraintChain();

        $cc->add(new Constraint('slmaster_id', 'is', 'NULL'));

        if (! empty($_so_price_type_id)) {
            $cc->add(new Constraint('so_price_type_id', '=', $_so_price_type_id));
        } else {
            $cc->add(new Constraint('so_price_type_id', 'is', 'NULL'));
        }

        $cc->add($this->currentConstraint($productsearch));

        return $this->getAll($cc);
    }

    function getDescription()
    {
        if (! $this->price && $this->stitem_id) {
            $this->loadSTItem($stitem_id);

            return $this->item_detail->getIdentifier();
        } else {
            return $this->description;
        }
    }

    function getGrossPrice($_stitem_id = '')
    {
        if (! $this->price) {
            $price = $this->product_detail->getPrice($_stitem_id);
        } else {
            $price = $this->price;
        }
        return $price;
    }

    function getPrice($_prod_group_id = '', $_stitem_id = '', $_slmaster_id = '')
    {
        $price = $this->getGrossPrice($_stitem_id);

        $price_discount = $this->getPriceDiscount($_prod_group_id = '', $_slmaster_id);

        if ($price_discount == 0) {
            return $price;
        }

        $discount = bcsub(1, bcdiv($price_discount, 100, 5), 4);

        return bcadd(round($price * $discount, 2), 0);
    }

    function getPriceDiscount($_prod_group_id = '', $_slmaster_id = '')
    {
        $_prod_group_id = empty($_prod_group_id) ? $this->product_detail->prod_group_id : $_prod_group_id;

        if (empty($_slmaster_id) || empty($_prod_group_id)) {
            return 0;
        }
        return SLDiscount::getDiscount($_slmaster_id, $_prod_group_id);
    }

    public function getUnitSize()
    {
        return 1;
    }

    public function currentConstraint($productsearch = '')
    {
        $ccdate = new ConstraintChain();

        if (! empty($productsearch)) {
            $ccdate->add(new Constraint('description', 'like', $productsearch . '%'));
        }

        $ccdate->add(new Constraint('start_date', '<=', Constraint::TODAY));

        $ccend = new ConstraintChain();

        $ccend->add(new Constraint('end_date', 'is', 'NULL'));
        $ccend->add(new Constraint('end_date', '>=', Constraint::TODAY), 'OR');

        $ccdate->add($ccend);

        return $ccdate;
    }
}

// end of SOProductline.php
