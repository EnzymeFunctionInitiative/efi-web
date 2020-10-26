# efi-web
Replaces the est-web and gnt-web repositories by unifying them into a single repo.

Each sub-dir is a separate website with its own configuration and code.  However, they share some libraries,
configuration settings, and HTML, JS, and CSS code. These are stored in, ROOT/conf, ROOT/libs, ROOT/html,
and ROOT/includes.


# INSTALLATION

bin/setup-composer.sh
php composer.phar install


# WINDOWS INSTALLATION

Assuming this is installed via XAMPP.

Open Terminal from XAMPP control panel.

cd to installation directory

bin\setup-composer.bat
php composer.phar install



# DATABASE SETUP

Create database efi_web, using SQL in sql\01_COMPLETE_ALL_TOOLS.sql

After database is created, apply any other SQL files in sql

Create user efi_web and set a password



# MAKE SYMLINKS


