#!/bin/bash
#
# $Revision: 1.1 $
#
sed -i".bak" "
/?>/ {
i\
//define('IPP_LOG_PATH',''); // file name or email address
i\
//define('IPP_LOG_TYPE', 'logger');  // file, e-mail or logger
i\
//define('IPP_LOG_LEVEL',0);  // 0 - no logging, 3 - most verbose
}" conf/config.php