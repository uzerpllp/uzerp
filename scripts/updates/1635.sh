#!/bin/bash
#
# $Revision: 1.1 $
#
# Edit the config.php file to add the AUDIT_LOGIN constant
# Do not edit if the AUDIT_LOGIN constant already exists
# otherwise, add the line before the first space line
# after the BASE_TITLE constant
#
grep AUDIT_LOGIN conf/config.php > /dev/null
if [ $? -eq 0 ]
then
  echo "Config file already contains AUDIT_LOGIN constant"
  exit
fi
sed -i".bak" "
/BASE_TITLE/,/^[[:space:]]*$/ {
/^[[:space:]]*$/ {
i\

i\
// Defines whether to write login attempts to audit log
i\
\\\t\tdefine('AUDIT_LOGIN', true);
}
}
" conf/config.php
