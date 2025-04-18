<?php
/**
 * Starter plan
 *
 * @package my-jetpack
 */

namespace Automattic\Jetpack\My_Jetpack\Products;

use Automattic\Jetpack\My_Jetpack\Module_Product;
use Automattic\Jetpack\My_Jetpack\Wpcom_Products;
use WP_Error;

/**
 * Class responsible for handling the Starter plan
 */
class Starter extends Module_Product {

	/**
	 * The product slug
	 *
	 * @var string
	 */
	public static $slug = 'starter';

	/**
	 * The Jetpack module name
	 *
	 * @var string
	 */
	public static $module_name = 'starter';

	/**
	 * Get the product name
	 *
	 * @return string
	 */
	public static function get_name() {
		return 'Starter';
	}

	/**
	 * Get the product title
	 *
	 * @return string
	 */
	public static function get_title() {
		return 'Jetpack Starter';
	}

	/**
	 * Get the internationalized product description
	 *
	 * @return string
	 */
	public static function get_description() {
		return __( 'Essential security tools: real-time backups and comment spam protection.', 'jetpack-my-jetpack' );
	}

	/**
	 * Get the internationalized product long description
	 *
	 * @return string
	 */
	public static function get_long_description() {
		return __( 'Essential security tools: real-time backups and comment spam protection.', 'jetpack-my-jetpack' );
	}

	/**
	 * Get the internationalized features list
	 *
	 * @return array Starter features list
	 */
	public static function get_features() {
		return array(
			_x( 'Real-time cloud backups with 1GB storage', 'Starter Product Feature', 'jetpack-my-jetpack' ),
			_x( 'One-click fixes for most threats', 'Starter Product Feature', 'jetpack-my-jetpack' ),
			_x( 'Comment & form spam protection', 'Starter Product Feature', 'jetpack-my-jetpack' ),
		);
	}

	/**
	 * Get the product princing details
	 *
	 * @return array Pricing details
	 */
	public static function get_pricing_for_ui() {
		return array_merge(
			array(
				'available'          => true,
				'wpcom_product_slug' => static::get_wpcom_product_slug(),
			),
			Wpcom_Products::get_product_pricing( static::get_wpcom_product_slug() )
		);
	}

	/**
	 * Get the WPCOM product slug used to make the purchase
	 *
	 * @return ?string
	 */
	public static function get_wpcom_product_slug() {
		return 'jetpack_starter_yearly';
	}

	/**
	 * Checks whether the Jetpack module is active
	 *
	 * This is a bundle and not a product. We should not use this information for anything
	 *
	 * @return bool
	 */
	public static function is_module_active() {
		return false;
	}

	/**
	 * Activates the product by installing and activating its plugin
	 *
	 * @param bool|WP_Error $current_result Is the result of the top level activation actions. You probably won't do anything if it is an WP_Error.
	 * @return boolean|\WP_Error
	 */
	public static function do_product_specific_activation( $current_result ) {

		$product_activation = parent::do_product_specific_activation( $current_result );

		if ( is_wp_error( $product_activation ) && 'module_activation_failed' === $product_activation->get_error_code() ) {
			// A bundle is not a module. There's nothing in the plugin to be activated, so it's ok to fail to activate the module.
			$product_activation = true;
		}

		// At this point, Jetpack plugin is installed. Let's activate each individual product.
		$activation = Anti_Spam::activate();
		if ( is_wp_error( $activation ) ) {
			return $activation;
		}

		$activation = Backup::activate();
		if ( is_wp_error( $activation ) ) {
			return $activation;
		}

		return $activation;
	}

	/**
	 * Checks whether the Product is active
	 *
	 * Jetpack Starter is a bundle and not a module. Activation takes place on WPCOM. So lets consider it active if jetpack is active and has the plan.
	 *
	 * @return boolean
	 */
	public static function is_active() {
		return static::is_jetpack_plugin_active() && static::has_required_plan();
	}

	/**
	 * Checks whether the current plan (or purchases) of the site already supports the product
	 *
	 * @return boolean
	 */
	public static function has_required_plan() {
		$purchases_data = Wpcom_Products::get_site_current_purchases();
		if ( is_wp_error( $purchases_data ) ) {
			return false;
		}
		if ( is_array( $purchases_data ) && ! empty( $purchases_data ) ) {
			foreach ( $purchases_data as $purchase ) {
				if ( str_starts_with( $purchase->product_slug, 'jetpack_starter' ) ) {
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Get the product-slugs of the paid plans for this product.
	 * (Do not include bundle plans, unless it's a bundle plan itself).
	 *
	 * @return array
	 */
	public static function get_paid_plan_product_slugs() {
		return array(
			'jetpack_starter_yearly',
			'jetpack_starter_monthly',
		);
	}

	/**
	 * Checks whether product is a bundle.
	 *
	 * @return boolean True
	 */
	public static function is_bundle_product() {
		return true;
	}

	/**
	 * Return all the products it contains.
	 *
	 * @return Array Product slugs
	 */
	public static function get_supported_products() {
		return array( 'backup', 'anti-spam' );
	}

	/**
	 * Get the URL where the user manages the product
	 *
	 * @return ?string
	 */
	public static function get_manage_url() {
		return '';
	}
}
