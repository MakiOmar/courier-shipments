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
            id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
            shipment_id BIGINT NOT NULL,
            employee_id BIGINT UNSIGNED NOT NULL,
            status ENUM('collected', 'packaged', 'processing', 'shipped', 'delivered') NOT NULL,
            description TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (shipment_id) REFERENCES {$wpdb->prefix}jet_cct_shipments(_ID) ON DELETE CASCADE ON UPDATE CASCADE,
            FOREIGN KEY (employee_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE ON UPDATE CASCADE
        ) $charset_collate;
    ";
	// Include the WordPress upgrade library.
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Execute the SQL to create the table.
	dbDelta( $sql );
}

/**
 * Create the salary management tables.
 */
function create_salary_management_tables() {
	global $wpdb;

	// Define table names.
	$salaries_table     = $wpdb->prefix . 'salaries';
	$transactions_table = $wpdb->prefix . 'salary_transactions';
	$payments_table     = $wpdb->prefix . 'salary_payments';

	// Define character set and collation.
	$charset_collate = $wpdb->get_charset_collate();

	// SQL for salaries table.
	$salaries_sql = "CREATE TABLE IF NOT EXISTS {$salaries_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL UNIQUE,
        base_salary DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate;";

	// SQL for salary transactions table.
	$transactions_sql = "CREATE TABLE IF NOT EXISTS {$transactions_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        salary_id BIGINT UNSIGNED NOT NULL,
        transaction_type ENUM('bonus', 'advance', 'deduction') NOT NULL,
        amount DECIMAL(10, 2) NOT NULL,
        description TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (salary_id) REFERENCES {$salaries_table}(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate;";

	// SQL for salary payments table.
	$payments_sql = "CREATE TABLE IF NOT EXISTS {$payments_table} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        salary_id BIGINT UNSIGNED NOT NULL,
        payment_amount DECIMAL(10, 2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        description TEXT,
        FOREIGN KEY (salary_id) REFERENCES {$salaries_table}(id) ON DELETE CASCADE ON UPDATE CASCADE
    ) $charset_collate;";

	// Include WordPress upgrade library.
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	// Execute SQL to create tables.
	dbDelta( $salaries_sql );
	dbDelta( $transactions_sql );
	dbDelta( $payments_sql );
}
