<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC Order Item Data Store Interface
 *
 * Functions that must be defined by the order item data store (for functions).
 *
 * @version  2.7.0
 * @category Interface
 * @author   WooCommerce
 */
interface WC_Order_Item_Data_Store_Interface {
	/**
	 * Add an order item to an order.
	 * @param  int   $order_id
	 * @param  array $item. order_item_name and order_item_type.
	 * @return int Order Item ID
	 */
	public function add_order_item( $order_id, $item );

	/**
	 * Update an order item.
	 * @param  int   $item_id
	 * @param  array $item. order_item_name or order_item_type.
	 * @return boolean
	 */
	public function update_order_item( $item_id, $item );

	/**
	 * Delete an order item.
	 * @param int $item_id
	 */
	public function delete_order_item( $item_id );

	/**
	 * Update term meta.
	 * @param  mixed $item_id
	 * @param  mixed $meta_key
	 * @param  mixed $meta_value
	 * @param  string $prev_value (default: '')
	 * @return bool
	 */
	function update_metadata( $item_id, $meta_key, $meta_value, $prev_value = '' );

	/**
	 * Add term meta.
	 * @param  mixed $item_id
	 * @param  mixed $meta_key
	 * @param  mixed $meta_value
	 * @param  bool $unique (default: false)
	 * @return int New row ID or 0
	 */
	function add_metadata( $item_id, $meta_key, $meta_value, $unique = false );


	/**
	 * Delete term meta.
	 * @param  mixed $item_id
	 * @param  mixed $meta_key
	 * @param  string $meta_value (default: '')
	 * @param  bool $delete_all (default: false)
	 * @return bool
	 */
	function delete_metadata( $item_id, $meta_key, $meta_value = '', $delete_all = false );

	/**
	 * Get term meta.
	 * @param  mixed $item_id
	 * @param  mixed $key
	 * @param  bool $single (default: true)
	 * @return mixed
	 */
	function get_metadata( $item_id, $key, $single = true );
}
