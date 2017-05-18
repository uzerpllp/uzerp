<?php

/**
 * Trait - getSalesOrderOptions
 *
 * @package uzerp
 * @author uzERP LLP and Steve Blamey <blameys@blueloop.net>
 * @license GPLv3 or later
 * @copyright (c) 2017 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 * @see PordersController
 * @see MfworkordersController
 */
trait getSalesOrderOptions
{

    /**
     * getSalesOrders - Return a list of sales orders with company and contact
     *
     * Used to gather options to populate a UI drop-down
     *
     * @param string $_order_id
     *            Sales Order record id
     * @return array
     *            Drop-down options
     */
    private function getSalesOrders($_order_id = '')
    {
        $sorder = DataObjectFactory::Factory('SOrder');

        $sorder->identifierField = array(
            'order_number',
            'customer',
            'person'
        );
        $sorder->orderby = 'order_number';

        $cc = new ConstraintChain();
        $cc1 = new ConstraintChain();

        $cc1->add(new Constraint('type', '=', $sorder->sales_order()));
        $cc1->add(new Constraint('status', 'in', "('" . $sorder->newStatus() . "','" . $sorder->openStatus() . "')"));

        // TODO: Check that orderlines for each order satisfy the conditions in getOrderLines above

        $cc->add($cc1);

        if (! empty($_order_id)) {
            $cc2 = new ConstraintChain();
            $cc2->add(new Constraint('id', '=', $_order_id));
            $cc->add($cc2, 'OR');
        }

        return $sorder->getAll($cc, true, true);
    }
}
?>