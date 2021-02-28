<?php

namespace PelemanPrintpartnerIntegrator\Includes;

/**
 * This class defines all code necessary to run during the plugin's activation.
 */
class PpiActivator
{
	/**
	 * Runs during plugin activation
	 */
	public static function activate()
	{
		PpiActivator::init_database();
		PpiActivator::init_plugin_folders();
	}

	/**
	 * Init database fields
	 *
	 * @return void
	 */
	public static function init_database()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . PPI_USER_PROJECTS_TABLE;

		$charset_collate =  $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        project_id int(11) NOT NULL,
        name tinytext DEFAULT NULL,
        product_id int(11)  NOT NULL,
        content_filename tinytext DEFAULT NULL,
        created datetime DEFAULT CURRENT_TIMESTAMP,
        updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
      ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	/**
	 * Create upload folder
	 */
	public static function init_plugin_folders()
	{
		$uploadDirectory = PPI_UPLOAD_DIR;

		if (!is_dir($uploadDirectory)) {
			mkdir($uploadDirectory, 0777, true);
		}

		$thumbnailDirectory = PPI_THUMBNAIL_DIR;

		if (!is_dir($thumbnailDirectory)) {
			mkdir($thumbnailDirectory, 0777, true);
		}
	}
}
