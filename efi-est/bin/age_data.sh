#!/bin/bash
source /etc/profile
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
php $DIR/remove_expired_files.php

