# Peleman Printpartner Integrator

## Requirements

-   Minimum PHP file upload size of 100MB. Contact your system administrator to make the following changes in your php.ini file:
    -   upload_max_filesize=100MB
    -   post_max_size=120MB
    -   memory_limit=120MB
-   [Composer](https://getcomposer.org/) must be installed on your system

## Installation:

-   Place the plugin files in the WordPress plugin folder (wp-content/plugins) and activate in the WordPress admin backend.
-   In the admin backend, enter your public & private keys from Imaxel
-   Run `composer install` to download the vendor packages.

## Delete when live

Imaxel data

-   Public key: pmXNzFLR8hNG9hF52AfZxf
-   Private key: Wax3DTV6MgywT88FAyndj4
    Imaxel sandboxes:
-   [Create project (POST)](https://services.imaxel.com/peleman/apisandbox/#/api/create_project)
-   [Edit project (GET)](https://services.imaxel.com/peleman/apisandbox/#/api/edit_project)
