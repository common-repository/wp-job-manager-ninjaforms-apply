<?php
/**
 * Plugin Name: WP Job Manager - Apply With Ninja Forms
 * Plugin URI:  https://github.com/Astoundify/wp-job-manager-ninja-forms-apply/
 * Description: Apply to jobs that have added an email address via Ninja Forms
 * Author:      Astoundify, JustinSainton
 * Author URI:  http://astoundify.com
 * Version:     1.0.1
 * Text Domain: job_manager_ninja_apply
 */

// Exit if accessed directly
if ( ! defined( 'WPINC' ) )  {
	exit;
}

class Astoundify_Job_Manager_Apply_Ninja {

	/**
	 * @var $instance
	 */
	private static $instance;

	/**
	 * @var $jobs_form_id
	 */
	private $jobs_form_id;

	/**
	 * @var $resumes_form_id
	 */
	private $resumes_form_id;

	/**
	 * @var $_proper_ninja_email
	 */
	private $_proper_ninja_email;

	/**
	 * Make sure only one instance is only running.
	 */
	public static function get_instance() {
		if ( ! defined( 'NINJA_FORMS_DIR' ) ) {
			return;
		}

		if ( ! isset ( self::$instance ) ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Start things up.
	 *
	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 */
	public function __construct() {
		$this->jobs_form_id    = get_option( 'job_manager_job_apply'   , 0 );
		$this->resumes_form_id = get_option( 'job_manager_resume_apply', 0 );

		$this->setup_actions();
		$this->setup_globals();
		$this->load_textdomain();
	}

	/**
	 * Set some smart defaults to class variables. Allow some of them to be
	 * filtered to allow for early overriding.
	 *
	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 *
	 * @return void
	 */
	private function setup_globals() {
		$this->file       = __FILE__;

		$this->basename   = plugin_basename( $this->file );
		$this->plugin_dir = plugin_dir_path( $this->file );
		$this->plugin_url = plugin_dir_url ( $this->file );

		$this->lang_dir   = trailingslashit( $this->plugin_dir . 'languages' );
		$this->domain     = 'wp-job-manager-ninja-forms-apply';
	}

	/**
	 * Loads the plugin language files
	 *
 	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 */
	public function load_textdomain() {
		$locale        = apply_filters( 'plugin_locale', get_locale(), $this->domain );
		$mofile        = sprintf( '%1$s-%2$s.mo', $this->domain, $locale );

		$mofile_local  = $this->lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/' . $this->domain . '/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			return load_textdomain( $this->domain, $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			return load_textdomain( $this->domain, $mofile_local );
		}

		return false;
	}

	/**
	 * Setup the default hooks and actions
	 *
	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 *
	 * @return void
	 */
	private function setup_actions() {
		add_filter( 'job_manager_settings'    , array( $this, 'job_manager_settings' ) );
		add_action( 'ninja_forms_email_admin', array( $this, 'notification_email' ) );
	}

	/**
	 * Add a setting in the admin panel to enter the ID of the Gravity Form to use.
	 *
	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 *
	 * @param array $settings
	 * @return array $settings
	 */
	public function job_manager_settings( $settings ) {
		$settings[ 'job_listings' ][1][] = array(
			'name'    => 'job_manager_job_apply',
			'std'     => null,
			'type'    => 'select',
			'options' => self::get_forms(),
			'label'   => __( 'Jobs Ninja Form', 'wp-job-manager-ninja-forms-apply' ),
			'desc'    => __( 'The Ninja Form you created for contacting employers.', 'wp-job-manager-ninja-forms-apply' ),
		);

		if ( class_exists( 'WP_Resume_Manager' ) ) {
			$settings[ 'job_listings' ][1][] = array(
				'name'  => 'job_manager_resume_apply',
				'std'   => null,
				'type'    => 'select',
				'options' => self::get_forms(),
				'label' => __( 'Resumes Ninja Form', 'wp-job-manager-ninja-forms-apply' ),
				'desc'  => __( 'The Ninja Form you created for contacting employees.', 'wp-job-manager-ninja-forms-apply' ),
			);
		}

		return $settings;
	}

	private static function get_forms() {
		$forms  = array( 0 => __( 'Please select a form', 'wp-job-manager-ninja-forms-apply' ) );
		$_forms = ninja_forms_get_all_forms();

		if ( ! empty( $_forms ) ) {

			foreach ( $_forms as $_form ) {
				$forms[ $_form['id'] ] = $_form['data']['form_title'];
			}
		}

		return $forms;
	}

	/**
	 * Set the notification email when sending an email.
	 *
	 * @since WP Job Manager - Apply with Ninja Forms 1.0
	 *
	 * @return string The email to notify.
	 */
	public function notification_email() {
		global $ninja_forms_processing;

		$form_id = $ninja_forms_processing->get_form_ID();

		if ( $form_id !== absint( $this->jobs_form_id ) && $form_id !== absint( $this->resumes_form_id ) ) {
			return;
		}

		$object = $field_id = null;
		$fields = $ninja_forms_processing;

		foreach ( $fields->data[ 'field_data' ] as $field ) {
			if ( 'application_email' == $field[ 'data' ][ 'label' ] ) {
				$field_id = $field[ 'id' ];

				break;
			}
		}

		$object = get_post( $ninja_forms_processing->get_field_value( $field_id ) );

		$this->_proper_ninja_email = $form_id == $this->jobs_form_id ? $object->_application : $object->_candidate_email;

		add_filter( 'wp_mail', array( $this, 'proper_email' ) );
	}

	function proper_email( $mail ) {
		if ( filter_var( $this->_proper_ninja_email, FILTER_VALIDATE_EMAIL ) ) {
			$mail[ 'to' ] = $this->_proper_ninja_email;
		}

		remove_filter( 'wp_mail', array( $this, 'proper_email' ) );

		return $mail;
	}
}
add_action( 'init', array( 'Astoundify_Job_Manager_Apply_Ninja', 'get_instance' ) );