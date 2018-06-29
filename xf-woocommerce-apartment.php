<?php
/**
 * Plugin Name: WooCommerce Apartment by xFrontend
 * Plugin URI: https://xfrontend.com/wordpress-plugins/
 * Description: Add apartment number field to WooCommerce billing address.
 * Author: xFrontend
 * Author URI: https://xfrontend.com/
 * License: GPL v2 or Later
 * License URI: http://www.gnu.org/licenses/gpl.html
 * Text Domain: xf-woocommerce-apartment
 * Domain Path: /languages/
 * Version: 0.0.1
 */

/**
 * Copyright (C) 2018, Omaar Osmaan <https://moonomo.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( ! defined( 'ABSPATH' ) ) :
	exit; // Exit if accessed directly
endif;


/**
 * Bail unless WooCommerce is installed, and active.
 */
if ( ! function_exists( 'WC' ) ) {
	exit;
}


/**
 * Add our Fields into Billing Address.
 */
add_filter( 'woocommerce_billing_fields', function ( $fields ) {

	// Do not show default apartment/suit/unit etc field added by WooCommerce.
	unset( $fields['billing_address_2'] );

	// Add apartment number field.
	$custom_fields = array(
		'apartment_number' => array(
			'type'        => 'text',
			'label'       => esc_html__( 'Apartment Number', 'xf-woocommerce-apartment' ),
			'placeholder' => esc_html__( 'Enter apartment number', 'xf-woocommerce-apartment' ),
			'required'    => false,
			'class'       => array( 'form-row-wide' ),
			'priority'    => 60,
		),
	);

	return array_merge( $fields, $custom_fields );

}, 999 );


/**
 * Validate apartment number.
 */
add_action( 'woocommerce_checkout_process', function () {

	// Get apartment number provided by the user, treat this as a string for now.
	$apartment_number = filter_input( INPUT_POST, 'apartment_number', FILTER_SANITIZE_STRING );

	// Show error if entered value is not a number.
	if ( ! empty( $apartment_number ) && ! is_numeric( $apartment_number ) ) {
		/* translators: %s is the field label */
		wc_add_notice( sprintf( esc_html__( '%s should be numeric value.', 'woocommerce' ), '<strong>' . esc_html__( 'Apartment Number', 'xf-woocommerce-apartment' ) . '</strong>' ), 'error' );
	}
} );


/**
 * Update the order meta with apartment number.
 */
add_action( 'woocommerce_checkout_update_order_meta', function ( $order_id ) {

	// Get only numeric value for the apartment number
	$apartment_number = filter_input( INPUT_POST, 'apartment_number', FILTER_SANITIZE_NUMBER_INT );

	// Do we have a numeric value to save?
	if ( ! empty( $apartment_number ) ) {
		update_post_meta( $order_id, 'billing_apartment_number', $apartment_number );
	}
} );


/**
 * Pass apartment number value to the billing address.
 */
add_filter( 'woocommerce_get_order_address', function ( $address, $type, $order ) {

	/**
	 * @var $order WC_Order
	 */
	$apartment_number = get_post_meta( $order->get_id(), 'billing_apartment_number', true );

	if ( 'billing' === $type && ! empty( $apartment_number ) ) {
		$address['apartment_number'] = $apartment_number;
	}

	return $address;

}, 10, 3 );


/**
 * Alter address format to show our apartment number.
 */
add_filter( 'woocommerce_formatted_address_replacements', function ( $map, $args ) {

	if ( isset( $args['apartment_number'] ) ) {
		/* translators: %d is the apartment number */
		$map['{address_2}'] = sprintf( esc_html__( 'Apartment number: %d', 'xf-woocommerce-apartment' ), $args['apartment_number'] );
	}

	return $map;

}, 10, 2 );
