<div class="ppi-settings">
    <h1>Peleman Printshop Integrator Settings</h1>
    <hr>
    <h2>PHP maximum uploaded file size</h2>
    <p>
        Some Peleman products require additional user content, in the form of a PDF upload. This may exceed the default PHP maximum uploaded file size of 2MB.
        <br>
        This plugin allows uploads up to 100MB, so your PHP installation will need to be changed to allow this <strong>(without this, the plugin will not work correctly)</strong>.<br>
        Please have your system administrator change your "php.ini" file. The following lines need to be changed:
    </p>
    <ul>
        <li>"upload_max_filesize": 100MB;</li>
        <li>"post_max_size": 120MB;</li>
        <li>"memory_limit": 120MB;</li>
        <li>"max_execution_time": 300;</li>
        <li>"max_input_time": 300.</li>
    </ul>
    <p>This plugin requires a cronjob to download pending orders from Imaxel. Set up a cronjob that runs every minute and calls the following API GET endpoint: <strong>"/wp-json/ppi/v1/pendingorders"</strong>.<br>
        An example of the exact crontab line is:
    <pre><strong>* * * * * curl -s "https://example-site.peleman.com/wp-json/ppi/v1/pendingorders" > /dev/null</strong></pre>
    </p>
    <form method="POST" action="options.php">
        <hr>
        <h2>Plugin configuration</h2>
        <?php
        settings_fields('ppi_custom_settings');
        do_settings_sections('ppi_custom_settings');
        ?>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-imaxel-private-key">Imaxel private key</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-imaxel-private-key" name="ppi-imaxel-private-key" value="<?= get_option('ppi-imaxel-private-key'); ?>" placeholder="Imaxel private key">
            </div>
        </div>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-imaxel-public-key">Imaxel public key</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-imaxel-public-key" name="ppi-imaxel-public-key" value="<?= get_option('ppi-imaxel-public-key'); ?>" placeholder="Imaxel public key">
            </div>
        </div>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-imaxel-shop-code">Imaxel shop code</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-imaxel-shop-code" name="ppi-imaxel-shop-code" value="<?= get_option('ppi-imaxel-shop-code'); ?>" placeholder="Imaxel Shop code">
            </div>
        </div>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-wc-key">WooCommerce key</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-wc-key" name="ppi-wc-key" value="<?= get_option('ppi-wc-key'); ?>" placeholder="WooCommerce key">
            </div>
        </div>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-wc-secret">WooCommerce secret</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-wc-secret" name="ppi-wc-secret" value="<?= get_option('ppi-wc-secret'); ?>" placeholder="WooCommerce secret">
            </div>
        </div>
        <p>Here you define the custom label for all products. Each product can have its own custom label as well.</p>
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="ppi-custom-add-to-cart-label">Label</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="ppi-custom-add-to-cart-label" name="ppi-custom-add-to-cart-label" value="<?= get_option('ppi-custom-add-to-cart-label'); ?>" placeholder="Eg: Personalise Your Product">
            </div>
        </div>
        <button type="submit" class="button button-primary">Save changes</button>
    </form>
    <hr>
    <h2>Display Imaxel project JSON (dev functionality)</h2>
    <form action="admin-post.php" method="POST">
        <input type="hidden" name="action" value="read_imaxel_project">
        <div class="form-row">
            <div class="grid-medium-column">
                <label for="project_id">Imaxel Project ID</label>
            </div>
            <div class="grid-large-column">
                <input type="text" id="project_id" name="project_id" placeholder="Project ID">
            </div>
        </div>
        <button type="submit" class="button button-info">Show it</button>
    </form>
</div>