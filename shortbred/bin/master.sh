#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI-ShortBRED Master script"

#export EFI_DEBUG=1

echo "CHECKING UP ON IDENTIFY JOBS"
sleep 1
php $DIR/check_identify.php

echo "CHECKING UP ON QUANTIFY JOBS"
sleep 1
php $DIR/check_quantify.php

echo "STARTING ANY NEW IDENTIFY JOBS"
sleep 1
php $DIR/identify.php

echo "STARTING ANY NEW QUANTIFY JOBS"
sleep 1
php $DIR/quantify.php

echo "CHECKING FOR JOB CANCELLATION REQUESTS"
sleep 1
php $DIR/check_cancels.php

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI-ShortBRED Master script"

