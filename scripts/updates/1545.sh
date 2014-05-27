#!/bin/bash
#
# $Revision: 1.1 $
#
# Create local import directory
#


for i in `ls data | grep company`
do
   echo "creating folder data/$i/local/imports/processed"
   mkdir -p data/$i/local/imports/processed
done