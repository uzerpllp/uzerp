#!/bin/bash
#
# $Revision: 1.1 $
#
mkdir data/users
for i in `ls data`
do
  if [ "$i" != "tmp" -a "$i" != "resource_cache" -a "$i" != "templates_c" -a "$i" != "company1" -a "$i" != "print_debug" -a "$i" != "logs" -a "$i" != "CVS" -a "$i" != "users" ]
  then
    echo "moving data/$i to data/users/$i"
    mv data/$i data/users/.
  fi
done