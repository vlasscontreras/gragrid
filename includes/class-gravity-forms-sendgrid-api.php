<?php
/**
 * Gravity Forms SendGrid API Library
 *
 * @since 1.0.0
 *
 * @package Gravity_Forms_SendGrid
 * @author  Vladimir Contreras
 */

/**
 * Gravity Forms SendGrid API Library.
 *
 * @since 1.0.0
 *
 * @package Gravity_Forms_SendGrid
 * @author  Vladimir Contreras
 */
class Gravity_Forms_SendGrid_API {

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
	 * @return array
	 */
	public function get_lists() {
		$response = $this->request( '/contactdb/lists' );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( __METHOD__, $response );
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
	 * @return array
	 */
	public function get_list( $list_id ) {
		$response = $this->request( '/contactdb/lists/' . $list_id );

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( __METHOD__, $response );
		}

		return $response['body'];
	}

	/**
	 * Add new recipient.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $params Request parameters.
	 * @return array
	 */
	public function add_recipient( $params ) {
		$params = array( $params );

		$response = $this->request( '/contactdb/recipients', $params, 'POST' );

		if ( 201 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( __METHOD__, $response );
		}

		return $response['body'];
	}

	/**
	 * Add new recipient to contact list.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @param array $list_id      Contact list ID.
	 * @param array $recipient_id Recipient ID.
	 * @return array
	 */
	public function add_list_recipient( $list_id, $recipient_id ) {
		$response = $this->request( '/contactdb/lists/' . $list_id . '/recipients/' . $recipient_id, null, 'POST' );

		if ( 201 !== wp_remote_retrieve_response_code( $response ) ) {
			return new WP_Error( __METHOD__, $response );
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
			return new WP_Error( __METHOD__, __( 'API key must be defined to process an API request.', 'gravity-forms-sendgrid' ) );
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
		$args = apply_filters( 'gravity_forms_sendgrid_request_args', $args, $path );

		// Execute request.
		$response = wp_remote_request( $request_url, $args );

		// If request was not successful, return the error.
		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response['body'] = json_decode( $response['body'], true );

		return $response;
	}
}
