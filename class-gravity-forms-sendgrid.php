<?php
/**
 * The SendGrid Add-on
 *
 * phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
 * phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 *
 * @since 1.0.0
 *
 * @package Gravity_Forms_SendGrid
 * @author  Vladimir Contreras
 */

GFForms::include_addon_framework();

/**
 * The SendGrid Add-on Class
 *
 * @since 1.0.0
 * @author  Vladimir Contreras
 */
class Gravity_Forms_SendGrid extends GFAddOn {
	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0.0
	 *
	 * @access private
	 * @var    Gravity_Forms_SendGrid $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the SendGrid Add-On.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_version Contains the version, defined from gravity-forms-sendgrid.php
	 */
	protected $_version = GRAVITY_FORMS_SENDGRID_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.12';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravity-forms-sendgrid';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravity-forms-sendgrid/gravity-forms-sendgrid.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://github.com/vlasscontreras/gravity-forms-sendgrid';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = null;

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1-0-0
	 *
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'SendGrid';

	/**
	 * Contains an instance of the SendGrid API library.
	 *
	 * @since  1.0.0
	 *
	 * @access protected
	 * @var    Gravity_Forms_SendGrid_API $api Contains an instance of the SendGrid API library.
	 */
	public $api = null;

	/**
	 * Add-on constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->_title = __( 'Gravity Forms: SendGrid Add-on', 'gravity-forms-sendgrid' );
	}

	/**
	 * Get an instance of this class.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @return Gravity_Forms_SendGrid
	 */
	public static function get_instance() {
		if ( null === self::$_instance ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Autoload the required libraries.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @uses GFAddOn::is_gravityforms_supported()
	 */
	public function pre_init() {
		parent::pre_init();

		if ( $this->is_gravityforms_supported() && ! class_exists( 'Gravity_Forms_SendGrid_API' ) ) {
			require_once 'includes/class-gravity-forms-sendgrid-api.php';
		}
	}

	/**
	 * Remove unneeded settings.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 */
	public function uninstall() {
		parent::uninstall();

		GFCache::delete( 'sendgrid_plugin_settings' );
		delete_option( 'gravity_forms_sendgrid_settings' );
		delete_option( 'gravity_forms_sendgrid_version' );
	}

	// # PLUGIN SETTINGS --------------------------------------------

	/**
	 * Configures the settings which should be rendered on the add-on
	 * settings tab.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'description' => '<p>' . sprintf(
					// Translators: 1 opening anchor tag, 2 closing anchor tag.
					esc_html__( 'Delivering your transactional and marketing emails through the world\'s largest cloud-based email delivery platform. Send with confidence. If you don\'t have a SendGrid account, you can %1$ssign up for one here.%2$s', 'gravity-forms-sendgrid' ),
					'<a href="http://sendgrid.com/" target="_blank" rel="noopener noreferrer">',
					'</a>'
				) . '</p>',
				'fields'      => array(
					array(
						'name'              => 'api_key',
						'label'             => esc_html__( 'SendGrid API Key', 'gravity-forms-sendgrid' ),
						'type'              => 'text',
						'class'             => 'medium',
						'feedback_callback' => array( $this, 'init_api' ),
					),
				),
			),
		);
	}

	// # HELPERS ----------------------------------------------------

	/**
	 * Initializes SendGrid API if credentials are valid.
	 *
	 * @since  1.0.0
	 *
	 * @uses GFAddOn::get_plugin_setting()
	 * @uses GFAddOn::log_debug()
	 * @uses GFAddOn::log_error()
	 * @uses Gravity_Forms_SendGrid_API::valid_key()
	 *
	 * @access public
	 * @param string $api_key SendGrid API key.
	 * @return bool|null
	 */
	public function init_api( $api_key = null ) {
		// If the API is already initialized, return true.
		if ( ! is_null( $this->api ) ) {
			return true;
		}

		if ( rgblank( $api_key ) ) {
			$api_key = $this->get_plugin_setting( 'api_key' );
		}

		// If the API key is blank, do not run a validation check.
		if ( rgblank( $api_key ) ) {
			return null;
		}

		$this->log_debug( __METHOD__ . ': Validating API key.' );

		try {
			// Assign API library to class.
			$this->api = new Gravity_Forms_SendGrid_API( $api_key );

			if ( $this->api->valid_key() ) {
				// Log that authentication test passed.
				$this->log_debug( __METHOD__ . ': SendGrid successfully authenticated.' );

				return true;
			} else {
				// Log that authentication test passed.
				$this->log_debug( __METHOD__ . ': Unable to authenticate with SendGrid.' );

				return false;
			}
		} catch ( Exception $e ) {
			// Log that authentication test failed.
			$this->log_error( __METHOD__ . ': Unable to authenticate with SendGrid; ' . $e->getMessage() );

			return false;
		}
	}
}
