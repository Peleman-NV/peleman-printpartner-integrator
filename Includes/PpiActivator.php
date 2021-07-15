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
        project_id tinytext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
        name tinytext DEFAULT NULL,
        product_id int(11) NOT NULL,
        content_filename tinytext DEFAULT NULL,
        content_pages int(11) DEFAULT NULL,
        ordered int(11) DEFAULT 0,
        created datetime DEFAULT CURRENT_TIMESTAMP,
        updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
      ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}

	/**
	 * Create folders
	 */
	public static function init_plugin_folders()
	{
		$pluginDirectories = [
			'uploadDirectory' => ABSPATH . 'wp-content/uploads/ppi/content',
			'imaxelFilesDirectory' => ABSPATH . 'wp-content/uploads/ppi/imaxelfiles',
			'thumbnailDirectory' => ABSPATH . 'wp-content/uploads/ppi/thumbnails',
			'logDirectory' => ABSPATH . 'wp-content/uploads/ppi/logs',
		];

		foreach ($pluginDirectories as $name => $dir) {
			if (!is_dir($dir)) {
				mkdir($dir, 0777, true);
			}
		}
	}
}
