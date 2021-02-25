<?php

namespace PelemanPrintpartnerIntegrator\Includes;

/**
 * Fired during plugin activation
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    Peleman_Printpartner_Integrator
 * @subpackage Peleman_Printpartner_Integrator/includes
 * @author     Noë Baeten, Jason Goossens, Chris Schippers
 */
class PpiActivator
{

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate()
	{
		PpiActivator::init_database();
	}

	/**
	 * Init database fields
	 *
	 * @return void
	 */
	public static function init_database()
	{
		global $wpdb;
		$table_name = $wpdb->prefix . "ppi_user_projects";

		$charset_collate =  $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id int(11) NOT NULL,
        project_id int(11) NOT NULL,
        name tinytext  NOT NULL,
        product_id int(11)  NOT NULL,
        content_filename tinytext DEFAULT NULL,
        created datetime DEFAULT CURRENT_TIMESTAMP,
        updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
      ) $charset_collate;";

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
}
