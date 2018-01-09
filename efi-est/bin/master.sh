#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI-EST Master script"

#export EFI_DEBUG=1

php $DIR/check_generatedata.php

sleep 1
php $DIR/generatedata.php

sleep 1
php $DIR/check_blasthits.php

sleep 1
php $DIR/blasthits.php

sleep 1 
php $DIR/check_fasta.php

sleep 1
php $DIR/fasta.php

sleep 1
php $DIR/check_accessions.php

sleep 1
php $DIR/accessions.php

sleep 1
php $DIR/check_analyzedata.php

sleep 1
php $DIR/analyzedata.php

sleep 1
php $DIR/check_colorssn.php

sleep 1
php $DIR/colorssn.php

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI-EST Master script"
