#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI-ShortBRED Master script"

#export EFI_DEBUG=1

sleep 1
php $DIR/check_shortbred.php

sleep 1
php $DIR/shortbred.php

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI-ShortBRED Master script"

