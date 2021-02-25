<div class="ppi-settings">
    <h1>Peleman Printshop Integrator Settings</h1>
    <hr>
    <h2>PHP maximum uploaded file size</h2>
    <p>
        Some Peleman products require additional user content, that may exceed the default PHP maximum uploaded file size of 2MB.
        <br>
        This plugin allows uploads up to 100MB, so your PHP installation will need to be changed to allow this <strong>(without this, the plugin will not work correctly)</strong>.<br>
        Please have your system administrator change your "php.ini" file. The lines containing "upload_max_filesize" and "post_max_size" must be changed to allow uploads up to 100MB.
    </p>
    <hr>
    <h2>Enter your Imaxel keys here</h2>
    <form method="POST" action="options.php">
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
        <button type="submit" class="button button-primary">Save changes</button>
    </form>
</div>