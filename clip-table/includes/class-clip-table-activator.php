<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.linkedin.com/in/rowanevenstar/
 * @since      1.0.0
 *
 * @package    clip-table
 * @subpackage clip-table/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    clip-table
 * @subpackage clip-table/includes
 * @author     Rowan Evenstar <rowan.evenstar@me.com>
 */
class Clip_Table_Activator {

	/**
	 * Create Plugin Table in Database if doesnt exist.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {

		error_log('CLIPTABLE PLUGIN  - Activate Function') ;

		//CREATE TABLE ON ACTIVATION
		global $wpdb; //The wordpress Database
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php'); //makes dbDelta work
	
	
		//Table Settings - TABLE NAME
		$charset_collate = $wpdb->get_charset_collate();
		//$table_name = $wpdb->prefix . 'cliptable'; //5a_ prefix failed. Not using prefix. //BUG TODO
		$table_name ='cliptable';
	
		$sql = "CREATE TABLE ". $table_name." (
			id int(11) NOT NULL AUTO_INCREMENT,
			Title varchar(255)  NOT NULL,
			Description varchar(255)  NOT NULL,
			Details varchar(255)  NULL,
			PRIMARY KEY  (id)
			) ENGINE=MyISAM DEFAULT CHARSET=latin1;
			";
	
		//Output SQL to allow for debuggin
		error_log( 'CLIPTABLE PLUGIN - Creation SQL ' );
		error_log( print_r( $sql, true ) );
	  
		//Run Create Statement, If Table Doesn't exist 
		if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) 
		 { 
		  dbDelta($sql);
		  error_log( 'CLIPTABLE PLUGIN  - Database Created ' );   
		 }
	}
}
