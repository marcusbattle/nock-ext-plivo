<?php
/*
Plugin Name: Nock API Extension - Plivo
Version: 0.1.0
Author: Marcus Battle
Description: API for Nock - Private Social Network
*/

class Nock_API_Ext_Plivo {

	protected static $single_instance = null;

	static function init() {

		if ( self::$single_instance === null ) {
			self::$single_instance = new self();
		}

		return self::$single_instance;

	}

	public function __construct() {

	}

	public function hooks() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {

		// POST Message
		register_rest_route( 'social-api/v1', '/plivo/sms', array(
	        'methods' => 'POST',
	        'callback' => array( $this, 'POST_sms' ),
	    ) );

	}

	public function POST_sms( $data ) {

		$params = $data->get_params();

		$params = wp_parse_args( $params, array(
			'From'	=> '',
			'To'	=> '',
			'Text'	=> '',
		) );

		if ( empty( $params['Text'] ) ) {
			return array( 'error' => 'empty_message' );
		}

		$args = array(
			'post_type' 	=> 'message',
			'post_status' 	=> 'publish',
			'post_title'	=> wp_trim_words( $params['Text'], 30, '' ),
			'post_content'	=> $params['Text'],
			'post_author'	=> 1,
		);

		$message_id = wp_insert_post( $args );

		update_post_meta( $message_id, 'message_from', $params['From'] );
		update_post_meta( $message_id, 'message_to', $params['To'] );
		update_post_meta( $message_id, 'params', $params );

		$update_args = array(
			'ID'		=> $message_id,
			'post_name'	=> $message_id,
		);

		wp_update_post( $update_args );

		return array( 'message_id' => $message_id );

	}

}

add_action( 'plugins_loaded', array( Nock_API_Ext_Plivo::init(), 'hooks' ) );


