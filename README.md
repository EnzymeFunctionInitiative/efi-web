# efi-web

[![Build Status](https://www.travis-ci.com/EnzymeFunctionInitiative/efi-web.svg?branch=master)](https://www.travis-ci.com/EnzymeFunctionInitiative/efi-web)

The web interface for the EFI Tools, [https://github.com/EnzymeFunctionInitiative/EFITools](https://github.com/EnzymeFunctionInitiative/EFITools)


# LINUX INSTALLATION

Ensure that the php-pdo, php-mysqlnd, php-mbstring PHP packages are installed.

    bin/setup-composer.sh
    php composer.phar install

Configure Apache for efi-web/html path

Open terminal.

    cd efi-web
    bin/setup-composer.sh
    php composer.phar install


# WEB SITE CONFIGURATION



# DATABASE SETUP

Create database `efi_web`, using all of the SQL files in `sql`, applying them in order smallest to largest.

Create user `efi_web` and set a password

This can be done with phpMySqlAdmin control panel, or with the terminal:

For example:

    mysqladmin -u root create efi_web
    mysql -u root efi_web < sql/06...
    mysql -u root efi_web < sql/07...
    mysql -u root efi_web < sql/08...
    mysql -u root efi_web < sql/create_user.sql.example

MAKE SURE TO UPDATE THE PASSWORD IN sql/create_user.sql.example


# EFI-WEB CONFIGURATION FILES

    cp conf/settings_base.inc.php.example conf/settings_base.inc.php
    cp conf/settings_paths.inc.php.example conf/settings_paths.inc.php
    cp conf/settings_auth.inc.php.example conf/settings_auth.inc.php
    cp conf/settings.inc.php.example conf/settings.inc.php
    cp conf/efi_est/settings.inc.php.example conf/efi_est/settings.inc.php
    cp conf/efi_est/settings_shared.inc.php.example conf/efi_est/settings_shared.inc.php
    cp conf/efi_gnt/settings.inc.php.example conf/efi_gnt/settings.inc.php
    cp conf/efi_cgfp/settings.inc.php.example conf/efi_cgfp/settings.inc.php
    cp conf/efi_cgfp/settings_shared.inc.php.example conf/efi_cgfp/settings_shared.inc.php
    cp conf/users/settings.inc.php.example conf/users/settings.inc.php
    cp conf/training/settings.inc.php.example conf/training/settings.inc.php
    cp conf/training/settings_examples.inc.php.example conf/training/settings_examples.inc.php

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

Change the following in conf/efi_est/settings.inc.php:

    __LOG_FILE__
    __UPLOADS_DIR__

## GNT

Change the following in conf/efi_gnt/settings.inc.php:

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


# MAKE SYMLINKS

On Linux, symlink the following:

    ln -s __EST_RESULTS_DIR__ html/efi-est/results
    ln -s (GNT)__OUTPUT_DIR__ html/efi-gnt/results
    ln -s (GNT)__DIAGRAM_OUTPUT_DIR__ html/efi-gnt/gnd_results
    ln -s (ShortBred)__OUTPUT_DIR__ html/efi-cgfp/results

Adjust paths accordingly.

