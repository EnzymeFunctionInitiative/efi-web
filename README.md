# efi-web

The web interface for the EFI Tools.


# LINUX INSTALLATION

    bin/setup-composer.sh
    php composer.phar install

Configure Apache for efi-web/html path

Open terminal.

    cd efi-web
    bin/setup-composer.sh
    php composer.phar install


# DATABASE SETUP

Create database efi_web, using SQL in sql\01_COMPLETE_ALL_TOOLS.sql

After database is created, apply any other SQL files in sql

Create user efi_web and set a password

This can be done with phpMySqlAdmin control panel, or with the terminal:

For example:

    mysqladmin -u root create efi_web
    mysql -u root efi_web < sql\01_COMPLETE_ALL_TOOLS.sql
    mysql -u root efi_web < sql\create_user.sql.example

MAKE SURE TO UPDATE THE PASSWORD IN sql\create_user.sql.example


# CREATE UPLOAD DIRECTORIES

    mkdir efi-est/uploads
    chown www-data:www-data efi-est/uploads
    mkdir efi-gnt/uploads
    chown www-data:www-data efi-gnt/uploads
    mkdir shortbred/uploads
    chown www-data:www-data shortbred/uploads
    mkdir efi-est/log
    chown www-data:www-data efi-est/log
    mkdir efi-gnt/log
    chown www-data:www-data efi-gnt/log
    mkdir shortbred/log
    chown www-data:www-data shortbred/log

where www-data is the Apache user/group (set appropriately).


# EFI-WEB CONFIGURATION FILES

    cp conf/settings_base.inc.php.example conf/settings_base.inc.php
    cp conf/settings.inc.php.example conf/settings.inc.php
    cp efi-est/conf/settings.inc.php.example efi-est/conf/settings.inc.php
    cp efi-est/conf/settings_shared.inc.php.example efi-est/conf/settings_shared.inc.php
    cp efi-gnt/conf/settings.inc.php.example efi-gnt/conf/settings.inc.php
    cp shortbred/conf/settings.inc.php.example shortbred/conf/settings.inc.php
    cp shortbred/conf/settings_shared.inc.php.example shortbred/conf/settings_shared.inc.php
    cp users/conf/settings.inc.php.example users/conf/settings.inc.php
    cp training/conf/settings.inc.php.example training/conf/settings.inc.php
    cp training/conf/settings_examples.inc.php.example training/conf/settings_examples.inc.php

When entering file or directory paths on Windows into the config files always use the forward slash for directory separator char (/).

## Main Config

Change the following in conf/settings_base.inc.php:

    __BASE_WEB_ROOT__ (on XAMPP set to http://localhost)
    __TIMEZONE__
    __ADMIN_EMAIL__
    __ERROR_ADMIN_EMAIL__
    __FEEDBACK_EMAIL__
    __EST_RESULTS_DIR__
    __BASE_WEB_PATH__ (optional, but if you are using EST on an Alias (like in XAMPP) then set to that Alias (e.g. /efi-web)
    __MYSQL_HOST__ (left at default if using same paramters as in Database Setup)
    __MYSQL_DATABASE__ (left at default if using same paramters as in Database Setup)
    __MYSQL_USER__ (left at default if using same paramters as in Database Setup)
    __MYSQL_PASSWORD__ (set to password in Database Setup)
    __MYSQL_AUTH_DATABASE__ (left at default if using same paramters as in Database Setup)

For __EST_RESULTS_DIR__ on Windows this can be left blank because there is no EFITools backend installation available.

To use sample data on Windows:

    __EST_RESULTS_DIR__ = C:/efi/web_sample_data/est

## EST

Change the following in efi-est/conf/settings.inc.php:

    __LOG_FILE__
    __UPLOADS_DIR__

## GNT

Change the following in efi-gnt/conf/settings.inc.php:

    __OUTPUT_DIR__
    __DIAGRAM_OUTPUT_DIR__
    __UPLOAD_DIR__
    __LOG_FILE__

## CGFP

Change

    __OUTPUT_DIR__
    __UPLOAD_DIR__
    __LOG_FILE__
    __METAGENOME_DB_LIST__


# LOAD SAMPLE DATA

To use the sample data change __RETENTION_DAYS__ and __FILE_RETENTION_DAYS__ to 1000.  This should be set to the default of 30 on production systems.

Download web_sample_data and family_sizes from https://efi.igb.illinois.edu/databases/sample_data/ and unzip.

    mysql -u root efi_web < web_sample_data/install_examples.sql
    mysql -u root efi_web < load_family_sizes.sql


# MAKE SYMLINKS

On Linux, symlink the following:

    ln -s __EST_RESULTS_DIR__ efi-est/html/results
    ln -s (GNT)__OUTPUT_DIR__ efi-gnt/html/results
    ln -s (GNT)__DIAGRAM_OUTPUT_DIR__ efi-gnt/html/gnd_results
    ln -s (ShortBred)__OUTPUT_DIR__ shortbred/html/results

Adjust paths accordingly.

