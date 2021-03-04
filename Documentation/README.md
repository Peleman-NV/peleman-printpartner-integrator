# Peleman Printpartner Integrator

This plugin allows a WordPress/WooCommerce webshop to show and sell Peleman products. Some of our products require user content to either be uploaded and/or created in an online editor, which is made possible through this plugin.

## Prerequisites

-   Wordpress installation.
-   WooCommerce plugin.
-   Minimum PHP file upload size of 100MB and longer execution times to handle larger files. Contact your system administrator to make the following changes in your php.ini file:
    -   upload_max_filesize=100MB;
    -   post_max_size=120MB;
    -   memory_limit=120MB;
    -   max_execution_time=300;
    -   max_input_time=300.
-   PHP ImageMagick extension (called Imagick) must be installed and enabled, to extract information from PDF files, and create thumbnails.
-   [Composer](https://getcomposer.org/) package manager must be installed on your system, to install the various vendor packages.
-   [Setasign FDPI PDFParser license](https://www.setasign.com/products/fpdi-pdf-parser/details/) is necessary to handle PDF files version 1.5 and higher. Please contact Setasign to purchase a license.

## Installation:

-   Place the plugin files in the WordPress plugin folder (wp-content/plugins) and activate it in the WordPress admin backend.
-   In the admin backend, enter your public & private keys from Imaxel
-   Run `composer install` to download the vendor packages. You will need Setasign credentials to download the FPDI PDFParser package.

---

## Delete when live

Imaxel data

-   Public key: pmXNzFLR8hNG9hF52AfZxf
-   Private key: Wax3DTV6MgywT88FAyndj4
    Imaxel sandboxes:
-   [Create project (POST)](https://services.imaxel.com/peleman/apisandbox/#/api/create_project)
-   [Edit project (GET)](https://services.imaxel.com/peleman/apisandbox/#/api/edit_project)

Setasign PDFParser

-   U: online@createmybooks.com
-   P: SaScsmb2015
