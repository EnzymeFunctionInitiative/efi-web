#!/bin/bash
DATE=$(date +"%Y-%m-%d %H:%M:%S")
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
echo "$DATE: Start EFI-EST Master script"

source /etc/profile

if [[ "`ps -ef | grep $0 | grep -v grep | wc -l`" -gt 3 ]]; then echo "Already running; exiting"; exit; fi

#export EFI_DEBUG=1

echo "Checking for job cancellation requests"
sleep 1
php $DIR/check_cancels.php

echo "Checking FAMILIES"
php $DIR/check_job.php FAMILIES

sleep 1
php $DIR/run_job.php FAMILIES

sleep 1
echo "Checking BLAST"
php $DIR/check_job.php BLAST

sleep 1
php $DIR/run_job.php BLAST

sleep 1 
echo "Checking FASTA"
php $DIR/check_job.php FASTA

sleep 1
php $DIR/run_job.php FASTA

sleep 1
echo "Checking ACCESSION"
php $DIR/check_job.php ACCESSION

sleep 1
php $DIR/run_job.php ACCESSION

sleep 1
echo "Checking TAXONOMY"
php $DIR/check_job.php TAXONOMY

sleep 1
php $DIR/run_job.php TAXONOMY

sleep 1
echo "Checking analyze jobs"
php $DIR/check_analyze.php

sleep 1
php $DIR/run_analyze.php

sleep 1
echo "Checking COLORSSN"
php $DIR/check_job.php COLORSSN

sleep 1
php $DIR/run_job.php COLORSSN

DATE=$(date +"%Y-%m-%d %H:%M:%S")
echo "$DATE: Finish EFI-EST Master script"
