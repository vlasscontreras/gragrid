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

GFForms::include_feed_addon_framework();

/**
 * The SendGrid Add-on Class
 *
 * @since 1.0.0
 * @author  Vladimir Contreras
 */
class Gravity_Forms_SendGrid extends GFFeedAddOn {
	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    Gravity_Forms_SendGrid $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the SendGrid Add-On.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_version Contains the version, defined from gravity-forms-sendgrid.php
	 */
	protected $_version = GRAVITY_FORMS_SENDGRID_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9.12';

	/**
	 * Defines the plugin slug.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravity-forms-sendgrid';

	/**
	 * Defines the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravity-forms-sendgrid/gravity-forms-sendgrid.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'https://github.com/vlasscontreras/gravity-forms-sendgrid';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = null;

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since 1-0-0
	 *
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'SendGrid';

	/**
	 * Contains an instance of the SendGrid API library.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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

	// # FEED SETTINGS ----------------------------------------------

	/**
	 * Configures the settings which should be rendered on the feed edit page.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function feed_settings_fields() {
		$settings = array(
			array(
				'title'  => esc_html__( 'Mailchimp Feed Settings', 'gravity-forms-sendgrid' ),
				'fields' => array(
					array(
						'name'     => 'feedName',
						'label'    => esc_html__( 'Name', 'gravity-forms-sendgrid' ),
						'type'     => 'text',
						'required' => true,
						'class'    => 'medium',
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Name', 'gravity-forms-sendgrid' ),
							esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gravity-forms-sendgrid' )
						),
					),
					array(
						'name'     => 'sendgrid_list',
						'label'    => esc_html__( 'SendGrid Contact List', 'gravity-forms-sendgrid' ),
						'type'     => 'sendgrid_list',
						'required' => true,
						'tooltip'  => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'SendGrid Contact List', 'gravity-forms-sendgrid' ),
							esc_html__( 'Select the contact list you would like to add emails s to.', 'gravity-forms-sendgrid' )
						),
					),
				),
			),
			array(
				'fields' => array(
					array(
						'name'      => 'mapped_fields',
						'label'     => esc_html__( 'Map Fields', 'gravity-forms-sendgrid' ),
						'type'      => 'field_map',
						'field_map' => $this->sengrid_field_map(),
						'tooltip'   => sprintf(
							'<h6>%s</h6>%s',
							esc_html__( 'Map Fields', 'gravity-forms-sendgrid' ),
							esc_html__( 'Associate the SendGrid fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gravity-forms-sendgrid' )
						),
					),
					array( 'type' => 'save' ),
				),
			),
		);

		return $settings;
	}

	/**
	 * Define the markup for the sendgrid_list type field.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $field The field properties.
	 * @param bool  $echo  Should the setting markup be echoed. Defaults to true.
	 * @return string
	 */
	public function settings_sendgrid_list( $field, $echo = true ) {
		if ( ! $this->init_api() ) {
			return;
		}

		try {
			$this->log_debug( __METHOD__ . ': Retrieving contact lists.' );

			$lists = $this->api->get_lists();
		} catch ( Exception $e ) {
			$this->log_error( __METHOD__ . ': Could not retrieve the contact lists ' . $e->getMessage() );

			printf(
				// Translators: 1 line break, 2 error message.
				esc_html__( 'Could not load the contact lists. %1$sError: %2$s', 'gravity-forms-sendgrid' ),
				'<br/>',
				$e->getMessage()
			); // phpcs:ignore: XSS ok.

			return;
		}

		if ( ! count( $lists['lists'] ) > 0 ) {
			$this->log_error( __METHOD__ . ': API retured empty set of lists.' );

			printf( esc_html__( 'You don\'t have contact lists in your account. Please create one first and try again.', 'gravity-forms-sendgrid' ) );

			return;
		}

		// Initialize select options.
		$options = array(
			array(
				'label' => esc_html__( 'Select a SendGrid list', 'gravity-forms-sendgrid' ),
				'value' => '',
			),
		);

		foreach ( $lists['lists'] as $list ) {
			$options[] = array(
				'label' => esc_html( $list['name'] . ' (' . $list['recipient_count'] . ')' ),
				'value' => esc_attr( $list['id'] ),
			);

		}

		// Add select field properties.
		$field['type']    = 'select';
		$field['choices'] = $options;

		// Generate select field.
		$html = $this->settings_select( $field, false );

		if ( $echo ) {
			echo $html; // phpcs:ignore: XSS ok.
		}

		return $html;
	}

	/**
	 * Return an array of Mailchimp list/audience fields which can be mapped to the Form fields/entry meta.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function sengrid_field_map() {
		return array(
			'email'      => array(
				'name'       => 'email',
				'label'      => esc_html__( 'Email Address', 'gravity-forms-sendgrid' ),
				'required'   => true,
				'field_type' => array( 'email', 'hidden' ),
			),
			'first_name' => array(
				'name'       => 'text',
				'label'      => esc_html__( 'First Name', 'gravity-forms-sendgrid' ),
				'required'   => false,
				'field_type' => array( 'name', 'text', 'hidden' ),
			),
			'last_name'  => array(
				'name'       => 'text',
				'label'      => esc_html__( 'Last Name', 'gravity-forms-sendgrid' ),
				'required'   => false,
				'field_type' => array( 'name', 'text', 'hidden' ),
			),
		);
	}

	/**
	 * Prevent feeds being listed or created if the API key isn't valid.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return bool
	 */
	public function can_create_feed() {
		return $this->init_api();
	}

	/**
	 * Allow the feed to be duplicated.
	 *
	 * @since 1.0.0
	 *
	 * @param array|int $id The ID of the feed to be duplicated or the feed object when duplicating a form.
	 * @return bool
	 */
	public function can_duplicate_feed( $id ) {
		return true;
	}

	/**
	 * Configures which columns should be displayed on the feed list page.
	 *
	 * @since  1.0.0
	 *
	 * @access public
	 * @return array
	 */
	public function feed_list_columns() {
		return array(
			'feedName'      => esc_html__( 'Name', 'gravity-forms-sendgrid' ),
			'sendgrid_list' => esc_html__( 'SendGrid List', 'gravity-forms-sendgrid' ),
		);
	}

	/**
	 * Get the name of the SendGrid list for the feed table view.
	 *
	 * @since 1.0.0
	 *
	 * @param array $feed The feed being included in the feed list.
	 * @return string
	 */
	public function get_column_value_sendgrid_list( $feed ) {
		if ( ! $this->init_api() ) {
			return rgars( $feed, 'meta/sendgrid_list' );
		}

		try {
			$list = $this->api->get_list( rgars( $feed, 'meta/sendgrid_list' ) );

			return $list['name'];
		} catch ( Exception $e ) {
			$this->log_error( __METHOD__ . ': Could not retrieve the contact list: ' . $e->getMessage() );

			return rgars( $feed, 'meta/sendgrid_list' );
		}
	}

	// # HELPERS ----------------------------------------------------

	/**
	 * Initializes SendGrid API if credentials are valid.
	 *
	 * @since 1.0.0
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
