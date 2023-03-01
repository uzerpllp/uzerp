#!/bin/bash

exit

# Apply coding statndards
find ./modules -type f -name "*Controller.php" -print0 | xargs -0 vendor/bin/ecs --fix check
git commit modules/. -m "Apply coding standards"

# Controller index method sigs
find ./modules -type f -name "*Controller.php" -print0 | xargs -0 sed -i "s/function\ index()/function\ index\(\$collection\ \=\ null\, \$sh\ \=\ \'\'\, \&\$c_query\=null\)/g"

# save method sigs
find ./modules -type f -name "*Controller.php" -print0 | xargs -0 sed -i "s/function\ save()/function\ save\(\$modelName\ \=\ null,\ \$dataIn\ \=\ array\(\),\ \&\$errors\ \=\ array())/g"

# delete method sigs
find ./modules -type f -name "*Controller.php" -print0 | xargs -0 sed -i "s/function\ delete()/function\ delete\(\$modelName\ \=\ null\)/g"

git commit -m "Fix controller method signatures (https://wiki.php.net/rfc/lsp_errors)"
