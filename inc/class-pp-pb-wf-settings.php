<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * WF Settings Class
 *
 * @class WF_Settings
 * @version	6.0.0
 * @since 6.0.0
 * @package	WF
 * @author Matty
 */
class PP_PB_WF_Settings {
	/**
	 * The token.
	 * @var	 string
	 * @access  private
	 * @since   6.0.0
	 */
	private $_token;

	/**
	 * The settings.
	 * @var	 object
	 * @access  public
	 * @since   6.0.0
	 */
	private $settings;

	/**
	 * The settings sections.
	 * @var	 object
	 * @access  public
	 * @since   6.0.0
	 */
	private $_sections;

	/**
	 * The settings fields.
	 * @var	 object
	 * @access  public
	 * @since   6.0.0
	 */
	private $_fields;

	/**
	 * The field generator.
	 * @var	 object
	 * @access  private
	 * @since   6.0.0
	 */
	private $_field_obj;

	public $page_hook;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function __construct () {
		$this->_token = 'wf';

		$this->_field_obj = new PP_PB_WF_Fields_Settings();

		$options = pp_pb_add_theme_options( array() );
		$this->_field_obj->init( $options );

		$this->_field_obj->__set( 'token', 'woo' );

		$this->_fields = $this->_field_obj->__get( 'fields' );

		add_action( 'admin_menu', array( $this, 'register_settings_screen' ), 110 );

	} // End __construct()

	/**
	 * Register the WooFramework admin menu.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function register_settings_screen () {

		// Load validation and save logic for the settings screen.
		add_action( 'load-' . $this->page_hook, array( $this, 'settings_screen_logic' ) );

	} // End register_settings_screen()

	/**
	 * Run logic on the WooFramework settings screen.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function settings_screen_logic () {
		if ( isset( $_REQUEST['tab'] ) && $_REQUEST['tab'] == 'styling' && ! empty( $_POST ) ) {

			$data = $_POST;

			$data = $this->_field_obj->validate_fields( $data, 'page-builder' );

			do_action_ref_array( 'wf_settings_save_before', $data, $this );

			//Save WooFramework Settings
			$update_tracker = $this->update_woo_options( $data );

			do_action_ref_array( 'wf_settings_save_after', $data, $this );

			// Store the status of the updates, so we can report back.
			set_transient( $this->_field_obj->__get( 'token' ) . 'update_tracker', $update_tracker, 5 );

			// Redirect on settings save, and exit.
			$this->redirect_after_saved();
			exit;
		}
	} // End settings_screen_logic()

	/**
	 * Saves woo_options
	 *
	 * @param array $data
	 * @return array $update_tracker
	 */
	public function update_woo_options( $data ){

		$options_collection = get_option( 'woo_options', array() );
		$update_tracker = array();
		if ( ! empty( $data ) ) {
			foreach ( $data as $k => $v ) {
				// Skip over the theme option if it's one of a selection of fields allowing unfiltered HTML, and the user can't edit it.
				if (
					! current_user_can( 'unfiltered_html' )
					&&
					in_array( $k, woo_disabled_if_not_unfiltered_html_option_keys() )
				) { continue; }

				$this->woo_options_from_data( $options_collection, $update_tracker, $k, $v );
			}

			// Update the options collection in the database.
			update_option( 'woo_options', $options_collection );
		}
		return $update_tracker;
	}

	/**
	 * Gets woo_options from data
	 *
	 * @param array $options_collection
	 * @param array $update_tracker
	 * @param string $k Key in loop
	 * @param string $v Value in loop
	 */
	public function woo_options_from_data( &$options_collection, &$update_tracker, $k, $v ){
		// Handle the saving of the setting.
		if ( true == apply_filters( 'wf_use_theme_mods', false ) ) {
			$update_tracker[$k] = set_theme_mod( esc_attr( $k ), $v );
		} else {
			$update_tracker[$k] = update_option( esc_attr( $k ), $v );
		}

		// Update the options collection, in case any products still use it.
		$options_collection[$k] = $v;
	}

	/**
	 * Gets page and tab for settings_screen_logic()
	 *
	 * @param array $data
	 * @return array
	 */
	public function get_page_and_tab( $data ){

		$page = 'page_builder';
		if ( isset( $data['page'] ) ) {
			$page = $data['page'];
			unset( $data['page'] );
		}

		$tab = '';
		if ( isset( $data['tab'] ) ) {
			$tab = $data['tab'];
			unset( $data['tab'] );
		}

		return array( $page, $tab );
	}

	/**
	 * Redirects the page after settings are saved
	 */
	public function redirect_after_saved(){

		list( $page, $tab ) = $this->get_page_and_tab( $_POST );

		$url = add_query_arg( 'page', $page );
		if ( '' != $tab ) {
			$url = add_query_arg( 'tab', $tab, $url );
		}
		$url = add_query_arg( 'updated', 'true', $url );

		wp_safe_redirect( $url );

	}

	/**
	 * Output markup for the settings screen.
	 * @access  public
	 * @since   6.0.0
	 * @return  void
	 */
	public function settings_screen () {
		$hidden_fields = array( 'page' => 'page_builder' );
		if ( isset( $_GET['tab'] ) && '' != $_GET['tab'] ) $hidden_fields['tab'] = sanitize_title_with_dashes( $_GET['tab'] );

		$this->_field_obj->__set( 'has_tabs', true );
		$this->_field_obj->__set( 'extra_hidden_fields', $hidden_fields );
		$this->_field_obj->init_tabs();
		$this->_field_obj->render();
	} // End settings_screen()

	/**
	 * Get the data for a single field.
	 * @access  public
	 * @since   6.0.0
	 * @param   string $key	  The key for which to retrieve the field data.
	 * @return  mixed/boolean	False if no field is found.
	 */
	public function get_field ( $key ) {
		$fields = $this->get_fields();
		if ( isset( $fields[$key] ) ) return $fields[$key];
		return false;
	} // End get_field()

	/**
	 * Update the data for a single field.
	 * @access  public
	 * @since   6.0.0
	 * @param   string $key	 The key for which to update the field data.
	 * @param 	array  $data 	Updated data for the specified field.
	 * @return  mixed/boolean	False if no field is found.
	 */
	public function update_field ( $key, $data = array() ) {
		$fields = $this->get_fields();
		$response = false;
		if ( isset( $fields[$key] ) ) {
			if ( is_array( $data ) && 0 < count( $data ) ) {
				foreach ( $data as $k => $v ) {
					// If attempting to update the field type, make sure it's an allowed field type.
					if ( 'type' == $k && ! in_array( $k, $this->get_supported_fields() ) ) continue;

					$this->_fields[$key][$k] = $v;
					$response = true;
				}
			}
		}
		return $response;
	} // End update_field()

	/**
	 * Remove a single field.
	 * @access  public
	 * @since   6.0.0
	 * @param   string $key	 The key for which to remove the field data.
	 * @return  mixed/boolean	False if no field is found.
	 */
	public function remove_field ( $key ) {
		$fields = $this->get_fields();
		if ( isset( $fields[$key] ) ) unset( $fields[$key] ); return true;
		return false;
	} // End remove_field()

	/**
	 * Return an array of the stored settings, as key => value pairs.
	 * @access  public
	 * @since   6.0.0
	 * @return  array
	 */
	public function get_all () {
		$settings = array();
		$fields = $this->get_fields();

		if ( 0 < count( $fields ) ) {
			foreach ( $fields as $k => $v ) {
				if ( 'multi_field' == $v['type'] && isset( $v['multi_fields'] ) && is_array( $v['multi_fields'] ) ) {
					if ( 0 < count( $v['multi_fields'] ) ) {
						foreach ( $v['multi_fields'] as $i => $j ) {
							$settings[$i] = $this->_process_single_field( $i, $j );
						}
					}
				} else {
					$settings[$k] = $this->_process_single_field( $k, $v );
				}
			}
		}

		return $settings;
	} // End get_all()

	/**
	 * Process a single field, when running get_all().
	 * @access  private
	 * @since   6.0.0
	 * @param   string 		 $k The field key.
	 * @param   string/array $v The stored value.
	 * @return  string/array	The stored value, sanitized.
	 */
	private function _process_single_field ( $k, $v ) {
		$default = '';
		if ( isset( $v['std'] ) ) {
			$default = $v['std'];
		}
		$value = $this->_field_obj->get_value( esc_attr( $k ), $default );

		if ( in_array( $v['type'], $this->_field_obj->get_array_field_types() ) && is_array( $value ) ) {
			$value = wp_parse_args( $value, $default );
			// Treat this as an array
			$value = array_map( 'esc_attr', $value );
		}

		return $value;
	} // End _process_single_field()

	/**
	 * Retrieve the fields.
	 * @access  public
	 * @since   6.0.0
	 * @param   string $section The section to search for fields in ( optional ).
	 * @return  array		   An array of the detected fields.
	 */
	public function get_fields ( $section = '' ) {
		$fields = array();
		foreach ( $this->_field_obj->__get( 'fields' ) as $k => $v ) {
			if ( '' != $section ) {
				if ( $section == $v['section'] ) {
					$fields[$k] = $v;
				}
			} else {
				$fields[$k] = $v;
			}
		}
		return $fields;
	} // End get_fields()
} // End Class
?>