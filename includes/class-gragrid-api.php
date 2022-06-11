<?php
/**
 * Gravity Forms SendGrid API Library
 *
 * @since 1.0.0
 *
 * @package Gragrid
 * @author  Vladimir Contreras
 */

/**
 * Gravity Forms SendGrid API Library.
 *
 * @since 1.0.0
 *
 * @package Gragrid
 * @author  Vladimir Contreras
 */
class Gragrid_API {
	/**
	 * SendGrid API key.
	 *
	 * @since 1.0.0
	 *
	 * @access protected
	 * @var    string $api_key SendGrid account API key.
	 */
	protected $api_key;

	/**
	 * Initialize API library.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param string $api_key SendGrid API key.
	 */
	public function __construct( $api_key = '' ) {
		$this->api_key = $api_key;
	}

	/**
	 * Get all SendGrid contact lists.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function get_lists() {
		$response = $this->request(
			'/marketing/lists',
			array( 'page_size' => 1000 )
		);

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}

	/**
	 * Get a SendGrid contact list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param int $list_id SendGrid contact list ID.
	 * @return array|WP_Error
	 */
	public function get_list( $list_id ) {
		$response = $this->request( '/marketing/lists/' . $list_id );

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}

	/**
	 * Get SendGrid custom fields.
	 *
	 * @since 2.1.0
	 *
	 * @access public
	 * @return array|WP_Error
	 */
	public function get_custom_fields() {
		$response = $this->request( '/marketing/field_definitions' );

		if ( ! $this->is_valid_response( $response, 200 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}

	/**
	 * Add new contacts.
	 *
	 * @since 2.0.0
	 *
	 * @access public
	 * @param array $params Request parameters.
	 * @return array|WP_Error
	 */
	public function add_contacts( $params ) {
		$response = $this->request( '/marketing/contacts', $params, 'PUT' );

		if ( ! $this->is_valid_response( $response, 202 ) ) {
			return $this->set_error( $response );
		}

		return $response['body'];
	}

	/**
	 * Validate the API key
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function valid_key() {
		return ! is_wp_error( $this->get_lists() );
	}

	/**
	 * Process SendGrid API request.
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @param string $path   Request path.
	 * @param array  $data   Request data.
	 * @param string $method Request method.
	 * @return array
	 */
	private function request( $path = '', $data = array(), $method = 'GET' ) {
		if ( rgblank( $this->api_key ) ) {
			return new WP_Error( __METHOD__, esc_html__( 'API key must be defined to process an API request.', 'gragrid' ) );
		}

		$request_url = 'https://api.sendgrid.com/v3' . $path;

		// Add request URL parameters if needed.
		if ( 'GET' === $method && ! empty( $data ) ) {
			$request_url = add_query_arg( $data, $request_url );
		}

		// Request specification.
		$args = array(
			'method'   => $method,
			'headers'  => array(
				'Accept'        => 'application/json',
				'Authorization' => 'Bearer ' . $this->api_key,
				'Content-Type'  => 'application/json',
			),
		);

		// Add data to arguments if needed.
		if ( 'GET' !== $method ) {
			$args['body'] = wp_json_encode( $data );
		}

		/**
		 * Filters the SendGrid request arguments.
		 *
		 * @since 1.0.0
		 *
		 * @param array  $args The request arguments sent to SendGrid.
		 * @param string $path The request path.
		 * @return array
		 */
		$args = apply_filters( 'gragrid_request_args', $args, $path );

		// Execute request.
		$response = wp_remote_request( $request_url, $args );

		// If request was not successful, return the error.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response['body'] = json_decode( $response['body'], true );

		return $response;
	}

	/**
	 * Check if the response is valid.
	 *
	 * @param array $response Request response.
	 * @param int   $code     Expected response code.
	 * @return bool
	 */
	private function is_valid_response( $response, $code ) {
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( wp_remote_retrieve_response_code( $response ) !== $code ) {
			return false;
		}

		return true;
	}

	/**
	 * Set an standardized errror
	 *
	 * @since 1.0.0
	 *
	 * @param array $response API response.
	 * @return WP_Error
	 */
	private function set_error( $response ) {
		if ( is_wp_error( $response ) ) {
			return $response;
		} elseif ( isset( $response['body']['errors'][0]['message'] ) ) {
			return new WP_Error( __METHOD__, $response['body']['errors'][0]['message'] );
		} else {
			return new WP_Error( __METHOD__, $response );
		}
	}
}
