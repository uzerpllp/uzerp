#!/bin/bash
#
# $Revision: 1.1 $
#
# Edit the config.php file to add the AUDIT_LOGIN constant
# Do not edit if the AUDIT_LOGIN constant already exists
# otherwise, add the line before the first space line
# after the BASE_TITLE constant
#

rm modules/public_pages/erp/manufacturing/templates/mfworkorders/bookoverunder.tpl
rm modules/public_pages/erp/manufacturing/eglets/WOrdersBookOverUnderNewEGlet.php
rm modules/common/templates/eglets/worders_book_overunder_list.tpl
