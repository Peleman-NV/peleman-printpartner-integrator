<div class="ppi-settings">
    <h3>Peleman Printshop Integrator Settings</h3>
    <h4 class="fw-light">Enter your Imaxel keys here</h4>
    <hr>
    <form method="POST" action="options.php">
        <?php
        settings_fields('ppi_custom_settings');
        do_settings_sections('ppi_custom_settings');
        ?>
        <div class="row mb-2">
            <div class="col-2">
                <label for="ppi-imaxel-private-key" class="form-label">Imaxel private key</label>
            </div>
            <div class="col-2">
                <input type="text" class="form-control" id="ppi-imaxel-private-key" name="ppi-imaxel-private-key" value="<?= get_option('ppi-imaxel-private-key'); ?>" placeholder="Imaxel private key">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-2">
                <label for="ppi-imaxel-public-key" class="form-label">Imaxel public key</label>
            </div>
            <div class="col-2">
                <input type="text" class="form-control" id="ppi-imaxel-public-key" name="ppi-imaxel-public-key" value="<?= get_option('ppi-imaxel-public-key'); ?>" placeholder="Imaxel public key">
            </div>
        </div>
        <button type="submit" class="btn btn-sm btn-primary">Save</button>
    </form>
</div>