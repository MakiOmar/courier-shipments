<?php
/**
 * Database
 *
 * @package Courier
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Create the shipment_tracking table if it does not exist.
 */
function create_shipment_tracking_table() {
	global $wpdb;

	// Define table name.
	$table_name = $wpdb->prefix . 'shipment_tracking';

	// Get the character set and collation.
	$charset_collate = $wpdb->get_charset_collate();
    // SQL to create the table.
    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            shipment_id BIGINT UNSIGNED NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            status ENUM('collected', 'packaged', 'processing', 'shipped', 'delivered') NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (shipment_id) REFERENCES {$wpdb->prefix}jet_cct_shipment(_ID) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;
    ";
    // Include the WordPress upgrade library.
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    // Execute the SQL to create the table.
    dbDelta( $sql );
}

