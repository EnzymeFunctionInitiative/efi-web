#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI-GNT Master script"

source /etc/profile

if [[ "`ps -ef | grep $0 | grep -v grep | wc -l`" -gt 3 ]]; then echo "Already running; exiting"; exit; fi
#export EFI_DEBUG=1

echo "Checking for GNN completion"
php $DIR/check_gnn.php

sleep 1
echo "Checking for new GNN submissions"
php $DIR/gnn.php

sleep 1
echo "Checking for diagram completion"
php $DIR/check_diagrams.php

sleep 1
echo "Checking for new diagram submissions"
php $DIR/diagrams.php

sleep 1
echo "Checking for BiG-SCAPE completions"
php $DIR/check_bigscape.php

sleep 1
echo "Checking for new BiG-SCAPE submissions"
php $DIR/bigscape.php

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI-GNT Master script"

