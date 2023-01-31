#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI Master script"

source /etc/profile

if [[ "`ps -ef | grep $0 | grep -v grep | wc -l`" -gt 3 ]]; then echo "Already running; exiting (`ps -ef | grep $0 | grep -v grep`)"; exit; fi

#export EFI_DEBUG=1

php $DIR/check_jobs.php generate
sleep 1
php $DIR/check_jobs.php analysis
sleep 1
php $DIR/check_jobs.php gnn
sleep 1
php $DIR/check_jobs.php diagram
sleep 1
php $DIR/check_jobs.php identify
sleep 1
php $DIR/check_jobs.php quantify

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI Master script"

