<?php
/**
 * Gravity Forms: SendGrid Add-On
 *
 * @package           Gravity_Forms_SendGrid
 * @author            Vladimir Contreras
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Gravity Forms: SendGrid Add-On
 * Plugin URI:        https://github.com/vlasscontreras/gravity-forms-sendgrid
 * Description:       Integrates Gravity Forms with SendGrid, allowing form submissions to be automatically sent to your SendGrid contact lists.
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Vladimir Contreras
 * Author URI:        https://github.com/vlasscontreras
 * Text Domain:       gravity-forms-sendgrid
 * Domain Path:       /languages
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Current add-on version
 */
define( 'GRAVITY_FORMS_SENDGRID_VERSION', '1.0.0' );

/**
 * If the Feed Add-On Framework exists, SendGrid Add-On is loaded.
 *
 * @since 1.0.0
 */
function gravity_forms_sendgrid_load() {
	if ( ! method_exists( 'GFForms', 'include_feed_addon_framework' ) ) {
		return;
	}

	require_once 'class-gravity-forms-sendgrid.php';

	GFAddOn::register( 'Gravity_Forms_SendGrid' );
}
add_action( 'gform_loaded', 'gravity_forms_sendgrid_load', 5 );
