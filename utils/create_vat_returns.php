<?php
/**
 *  Utility to create VAT Returns for old periods
 *
 *  This is to be used *once* to poulate the vat_return database table
 *  after ugrading uzERP systems to release 1.14.
 *
 *  Place this script in a web accessible location and point a browser
 *  at the URL, e.g. https://uzerp.example.com/create_vat_returns.php.
 *  Wait for a long time as a VAT calculation will be run for each
 *  tax period. When the script has finished, it will display information
 *  for each period.
 *
 *  *************************************************
 *  *** REMOVE THIS FILE ONCE IT HAS DONE ITS JOB ***
 *  *************************************************
 *
 *  @author uzERP LLP and Steve Blamey <sblamey@uzerp.com>
 *  @license GPLv3 or later
 *  @copyright (c) 2019 uzERP LLP (support#uzerp.com). All rights reserved.
 *
 *  uzERP is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  any later version.
 */

// uzERP essentials
 require 'system.php';
$system = system::Instance();
$system->load_essential();

define('EGS_COMPANY_ID', 1);
define('EGS_USERNAME', 'admin');

// Get a list of tax periods
$qparams = [$year, $tax_period];
$query = <<<'QUERY'
SELECT year, max(tax_period) as tax_period
from gl_periods
where tax_period != 0
group by year, tax_period
order by year, tax_period
QUERY;

$db = DB::Instance();
$gl_periods = $db->getAll($query);

// Get the current tax period
$current_period = DataObjectFactory::Factory('GLPeriod');
$current_period->getCurrentTaxPeriod();

foreach ($gl_periods as $period)
{
    // Create the VAT Return record for the period
    echo($period['year'] . ' ' . $period['tax_period']);
    $vat = new Vat;
    $vat_return = new VatReturn;
    $vat_return->newVatReturn($period['year'], $period['tax_period']);

    // Calculate the VAT
    $boxes = $vat->getVATvalues($period['year'], $period['tax_period']);
    var_dump($boxes);

    // Set the finalised status on old/non MTD returns
    if (($period['year'] < $current_period->year) ||
    ($period['year'] = $current_period->year && $period['tax_period'] < $current_period->tax_period))
    {
        {
        $vat_return->finalised = true;
        $vat_return->save();
        }
    } else {
        $vat_return->finalised = false;
        $vat_return->save();
    }

    // Save the calculated values
    $vat_return->updateVatReturnBoxes($period['year'], $period['tax_period'], $boxes);
}
?>