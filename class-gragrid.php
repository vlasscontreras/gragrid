<?php
/**
 * The SendGrid Add-on
 *
 * phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 *
 * @since 1.0.0
 *
 * @package Gragrid
 * @author  Vladimir Contreras
 */

GFForms::include_feed_addon_framework();

require_once 'includes/concerns/class-gragrid-converts-case.php';

/**
 * The SendGrid Add-on Class
 *
 * @since 1.0.0
 * @author  Vladimir Contreras
 */
class Gragrid extends GFFeedAddOn {
	use Gragrid_Converts_Case;

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @var    Gragrid $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the SendGrid Add-On.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_version Contains the version, defined from gragrid.php
	 */
	protected $_version = GRAGRID_VERSION;

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
	protected $_slug = 'gragrid';

	/**
	 * Defines the main plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gragrid/gragrid.php';

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
	protected $_url = 'https://github.com/vlasscontreras/gragrid';

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
	 * @var    Gragrid_API|null $api Contains an instance of the SendGrid API library.
	 */
	protected $api = null;

	/**
	 * Add-on constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		parent::__construct();

		$this->_title = __( 'Gravity Forms: SendGrid Add-on', 'gragrid' );
	}

	/**
	 * Get an instance of this class.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return Gragrid
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

		if ( $this->is_gravityforms_supported() && ! class_exists( 'Gragrid_API' ) ) {
			require_once 'includes/class-gragrid-api.php';
		}
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
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						// Translators: 1 open anchor tag, 2 close anchor tag, 3 open anchor tag, 4 close anchor tag.
						esc_html__( 'SendGrid makes it easy to reliably send email notifications. If you don\'t have a SendGrid account, you can %1$ssign up for one here%2$s. Once you have signed up, you can %3$sfind your API keys here%4$s.', 'gragrid' ),
						'<a href="https://sendgrid.com" target="_blank" rel="noopener noreferrer">',
						'</a>',
						'<a href="https://app.sendgrid.com/settings/api_keys" target="_blank" rel="noopener noreferrer">',
						'</a>'
					)
				),
				'fields'      => array(
					array(
						'name'              => 'api_key',
						'label'             => esc_html__( 'SendGrid API Key', 'gragrid' ),
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
	 * @since 1.1.0 Added conditional feed setting.
	 * @since 2.1.0 Added custom field mapping.
	 *
	 * @access public
	 * @return array
	 */
	public function feed_settings_fields() {
		$custom_fields    = $this->sengrid_custom_fields_map();
		$custom_field_map = null;

		if ( ! count( $custom_fields ) > 0 ) {
			$this->log_error( __METHOD__ . ': API retured empty set of custom fields.' );
		} else {
			$custom_field_map = array(
				'name'      => 'mappedCustomFields',
				'label'     => esc_html__( 'Custom Fields', 'gragrid' ),
				'type'      => 'field_map',
				'field_map' => $custom_fields,
				'tooltip'   => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Custom Fields', 'gragrid' ),
					esc_html__( 'Associate your custom SendGrid fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gragrid' )
				),
			);
		}

		$fields = array(
			array(
				'name'     => 'feedName',
				'label'    => esc_html__( 'Name', 'gragrid' ),
				'type'     => 'text',
				'required' => true,
				'class'    => 'medium',
				'tooltip'  => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Name', 'gragrid' ),
					esc_html__( 'Enter a feed name to uniquely identify this setup.', 'gragrid' )
				),
			),
			array(
				'name'     => 'sendgrid_list',
				'label'    => esc_html__( 'SendGrid Contact List', 'gragrid' ),
				'type'     => 'sendgrid_list',
				'required' => true,
				'tooltip'  => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'SendGrid Contact List', 'gragrid' ),
					esc_html__( 'Select the contact list you would like to add emails s to.', 'gragrid' )
				),
			),
			array(
				'name'      => 'mappedFields',
				'label'     => esc_html__( 'Map Fields', 'gragrid' ),
				'type'      => 'field_map',
				'field_map' => $this->sengrid_field_map(),
				'tooltip'   => sprintf(
					'<h6>%s</h6>%s',
					esc_html__( 'Map Fields', 'gragrid' ),
					esc_html__( 'Associate the SendGrid fields to the appropriate Gravity Form fields by selecting the appropriate form field from the list.', 'gragrid' )
				),
			),
			$custom_field_map,
			array(
				'type'           => 'feed_condition',
				'name'           => 'enabled',
				'label'          => __( 'Conditional logic', 'gragrid' ),
				'checkbox_label' => __( 'Enable', 'gragrid' ),
				'instructions'   => __( 'Send this lead to SendGrid if', 'gragrid' ),
			),
			array( 'type' => 'save' ),
		);

		$settings = array(
			array(
				'title'  => esc_html__( 'SendGrid Feed Settings', 'gragrid' ),
				'fields' => array_filter( $fields ),
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
			$this->log_debug( __METHOD__ . '(): Retrieving contact lists.' );

			$lists = $this->api->get_lists();
		} catch ( Exception $e ) {
			$this->log_error( __METHOD__ . ': Could not retrieve the contact lists ' . $e->getMessage() );

			printf(
				// Translators: 1 line break, 2 error message.
				esc_html__( 'Could not load the contact lists. %1$sError: %2$s', 'gragrid' ),
				'<br/>',
				$e->getMessage()
			); // phpcs:ignore: XSS ok.

			return;
		}

		if ( ! count( $lists['result'] ) > 0 ) {
			$this->log_error( __METHOD__ . ': API retured empty set of lists.' );

			printf( esc_html__( 'You don\'t have contact lists in your account. Please create one first and try again.', 'gragrid' ) );

			return;
		}

		// Initialize select options.
		$options = array(
			array(
				'label' => esc_html__( 'Select a SendGrid list', 'gragrid' ),
				'value' => '',
			),
		);

		foreach ( $lists['result'] as $list ) {
			$options[] = array(
				'label' => esc_html( $list['name'] . ' (' . $list['contact_count'] . ')' ),
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
	 * Return an array of SendGrid list/audience fields which can be mapped to the Form fields/entry meta.
	 *
	 * @since 1.0.0
	 * @since 2.0.0 Added address fields.
	 * @since 2.1.0 Added more default SendGrid fields.
	 *
	 * @access public
	 * @return array
	 */
	public function sengrid_field_map() {
		return array(
			'email'                 => array(
				'name'       => 'email',
				'label'      => esc_html__( 'Email Address', 'gragrid' ),
				'required'   => true,
				'field_type' => array( 'email', 'hidden' ),
			),
			'first_name'            => array(
				'name'       => 'first_name',
				'label'      => esc_html__( 'First Name', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'name', 'text', 'hidden' ),
			),
			'last_name'             => array(
				'name'       => 'last_name',
				'label'      => esc_html__( 'Last Name', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'name', 'text', 'hidden' ),
			),
			'phone_number'          => array(
				'name'       => 'phone_number',
				'label'      => esc_html__( 'Phone Number', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'phone', 'text', 'hidden' ),
			),
			'address_line_1'        => array(
				'name'       => 'address_line_1',
				'label'      => esc_html__( 'Address Line 1', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'address_line_2'        => array(
				'name'       => 'address_line_2',
				'label'      => esc_html__( 'Address Line 2', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'city'                  => array(
				'name'       => 'city',
				'label'      => esc_html__( 'City', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'state_province_region' => array(
				'name'       => 'state_province_region',
				'label'      => esc_html__( 'State/Province/Region', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'postal_code'           => array(
				'name'       => 'postal_code',
				'label'      => esc_html__( 'Postal Code', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'country'               => array(
				'name'       => 'country',
				'label'      => esc_html__( 'Country', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'address', 'text', 'hidden' ),
			),
			'whatsapp'              => array(
				'name'       => 'whatsapp',
				'label'      => esc_html__( 'WhatsApp', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'phone', 'text', 'hidden' ),
			),
			'line'                  => array(
				'name'       => 'line',
				'label'      => esc_html__( 'Line', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'phone', 'text', 'hidden' ),
			),
			'facebook'              => array(
				'name'       => 'facebook',
				'label'      => esc_html__( 'Facebook', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'website', 'text', 'hidden' ),
			),
			'unique_name'           => array(
				'name'       => 'unique_name',
				'label'      => esc_html__( 'Unique Name', 'gragrid' ),
				'required'   => false,
				'field_type' => array( 'name', 'text', 'hidden' ),
			),
		);
	}

	/**
	 * Map custom SendGrid fields
	 *
	 * @since 2.1.0
	 *
	 * @return array
	 */
	public function sengrid_custom_fields_map() {
		$fields        = array();
		$custom_fields = (array) rgar( $this->api->get_custom_fields(), 'custom_fields' );
		$custom_fields = array_filter( $custom_fields );

		foreach ( $custom_fields as $custom_field ) {
			$fields[ $custom_field['id'] ] = array(
				'name'     => $custom_field['id'],
				'label'    => $this->snake_to_title( $custom_field['name'] ),
				'required' => false,
			);
		}

		return $fields;
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
			'feedName'      => esc_html__( 'Name', 'gragrid' ),
			'sendgrid_list' => esc_html__( 'SendGrid List', 'gragrid' ),
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

	// # FEED PROCESSING -----------------------------------------------------------------------------------------------

	/**
	 * Process the feed e.g. subscribe the user to a list.
	 *
	 * @since 1.0.0
	 * @since 2.1.0 Added custom fields to the request data.
	 *
	 * @param array $feed The feed object to be processed.
	 * @param array $entry The entry object currently being processed.
	 * @param array $form The form object currently being processed.
	 * @return bool|void
	 */
	public function process_feed( $feed, $entry, $form ) {
		if ( ! $this->init_api() ) {
			$this->add_feed_error( esc_html__( 'Unable to process feed because API could not be initialized.', 'gragrid' ), $feed, $entry, $form );

			return $entry;
		}

		$contact = array();

		// Map reserved/standard/default fields.
		$fields = $this->get_field_map_fields( $feed, 'mappedFields' );

		foreach ( $fields as $name => $field_id ) {
			$contact[ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		// Map custom fields.
		$custom_fields = $this->get_field_map_fields( $feed, 'mappedCustomFields' );

		foreach ( $custom_fields as $name => $field_id ) {
			$contact['custom_fields'][ $name ] = $this->get_field_value( $form, $entry, $field_id );
		}

		$contact_params = array(
			'list_ids' => array( rgars( $feed, 'meta/sendgrid_list' ) ),
			'contacts' => array( $contact ),
		);

		/**
		 * Contact parameters
		 *
		 * @since 2.1.0
		 *
		 * @param array $contact_params Contact parameters.
		 * @param array $entry          The entry object currently being processed.
		 * @param array $form           The form object currently being processed.
		 */
		$contact_params = apply_filters( 'gragrid_contact_params', $contact_params, $entry, $form );

		try {
			// Save the contacts.
			$response = $this->api->add_contacts( $contact_params );

			if ( is_wp_error( $response ) ) {
				// Translators: %s error message.
				$this->add_feed_error( sprintf( esc_html__( 'Unable to add the contact: %s', 'gragrid' ), $response->get_error_message() ), $feed, $entry, $form );

				return $entry;
			}

			$this->add_note(
				$entry['id'],
				sprintf(
					// Translators: %s SendGrid list ID.
					esc_html__( 'Gragrid successfully passed the lead details to the SendGrid list #%s.', 'gragrid' ),
					rgars( $feed, 'meta/sendgrid_list' )
				),
				'success'
			);

			return $entry;
		} catch ( Exception $e ) {
			// Translators: %s error message.
			$this->add_feed_error( sprintf( esc_html__( 'Unable to add recipient to list: %s', 'gragrid' ), $e->getMessage() ), $feed, $entry, $form );

			return $entry;
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
	 * @uses Gragrid_API::valid_key()
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

		$this->log_debug( __METHOD__ . '(): Validating API key.' );

		try {
			$this->api = new Gragrid_API( $api_key );

			if ( $this->api->valid_key() ) {
				$this->log_debug( __METHOD__ . '(): SendGrid successfully authenticated.' );

				return true;
			} else {
				$this->log_debug( __METHOD__ . '(): Unable to authenticate with SendGrid.' );

				return false;
			}
		} catch ( Exception $e ) {
			$this->log_error( __METHOD__ . '(): Unable to authenticate with SendGrid; ' . $e->getMessage() );

			return false;
		}
	}
}
