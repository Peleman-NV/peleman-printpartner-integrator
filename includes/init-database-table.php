<?php

function init_database()
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
