<div class="ppi-settings">
    <h1>Peleman Printshop Integrator Settings</h1>
    <hr>
    <h2>PHP maximum uploaded file size</h2>
    <p>
        Some Peleman products require additional user content, that may exceed the default PHP maximum uploaded file size of 2MB.
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