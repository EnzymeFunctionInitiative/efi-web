#!/bin/bash
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source /etc/profile
php $DIR/remove_expired_files.php

