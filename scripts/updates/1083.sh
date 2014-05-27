#!/bin/bash
#
# $Revision: 1.2 $
#
sed -i".bak" "/?>/ i\
define('AUTOCOMPLETE_SELECT_LIMIT', 5000);
" conf/config.php

rm ./modules/public_pages/erp/order/purchase_order/templates/porders/update_glcodes.tpl
rm -r ./modules/public_pages/contacts/resources
