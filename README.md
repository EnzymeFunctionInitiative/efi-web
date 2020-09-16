# efi-web
Replaces the est-web and gnt-web repositories by unifying them into a single repo.

Each sub-dir is a separate website with its own configuration and code.  However, they share some libraries,
configuration settings, and HTML, JS, and CSS code. These are stored in, ROOT/conf, ROOT/libs, ROOT/html,
and ROOT/includes.


# INSTALLATION

bin/setup-composer.sh
php composer.phar install

