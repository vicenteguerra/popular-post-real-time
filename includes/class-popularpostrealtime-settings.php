<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class PopularPostRealTime_Settings {

	/**
	 * The single instance of PopularPostRealTime_Settings.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * The main plugin object.
	 * @var 	object
	 * @access  public
	 * @since 	1.0.0
	 */
	public $parent = null;

	/**
	 * Prefix for plugin settings.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $base = '';

	/**
	 * Available settings for plugin.
	 * @var     array
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = array();

	public function __construct ( $parent ) {
		$this->parent = $parent;

		$this->base = 'pprt_';

		// Initialise settings
		add_action( 'init', array( $this, 'init_settings' ), 11 );

		// Register plugin settings
		add_action( 'admin_init' , array( $this, 'register_settings' ) );

		// Add settings page to menu
		add_action( 'admin_menu' , array( $this, 'add_menu_item' ) );


		// Add settings link to plugins page
		add_filter( 'plugin_action_links_' . plugin_basename( $this->parent->file ) , array( $this, 'add_settings_link' ) );

	}



	/**
	 * Initialise settings
	 * @return void
	 */
	public function init_settings () {
		$this->settings = $this->settings_fields();
	}

	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_item () {
		$page = add_options_page( __( 'Popular Post RT', 'popularpostrealtime' ) , __( 'Popular Post RT', 'popularpostrealtime' ) , 'manage_options' , $this->parent->_token . '_settings' ,  array( $this, 'settings_page' ) );
		add_action( 'admin_print_styles-' . $page, array( $this, 'settings_assets' ) );
	}


	/**
	 * Load settings JS & CSS
	 * @return void
	 */
	public function settings_assets () {

		// We're including the farbtastic script & styles here because they're needed for the colour picker
		// If you're not including a colour picker field then you can leave these calls out as well as the farbtastic dependency for the wpt-admin-js script below
		wp_enqueue_style( 'farbtastic' );
    	wp_enqueue_script( 'farbtastic' );

    	// We're including the WP media scripts here because they're needed for the image upload field
    	// If you're not including an image upload then you can leave this function call out
    	wp_enqueue_media();

    	wp_register_script( $this->parent->_token . '-settings-js', $this->parent->assets_url . 'js/settings' . $this->parent->script_suffix . '.js', array( 'farbtastic', 'jquery' ), '1.0.0' );
    	wp_enqueue_script( $this->parent->_token . '-settings-js' );
	}

	/**
	 * Add settings link to plugin list table
	 * @param  array $links Existing links
	 * @return array 		Modified links
	 */
	public function add_settings_link ( $links ) {
		$settings_link = '<a href="options-general.php?page=' . $this->parent->_token . '_settings">' . __( 'Settings', 'popularpostrealtime' ) . '</a>';
  		array_push( $links, $settings_link );
  		return $links;
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	private function settings_fields () {

		$ga = new GoogleAnalyticsAPI('service');

		$ga->auth->setClientId(get_option($this->base . "client_id")); // From the APIs console
		$ga->auth->setEmail(get_option($this->base . "email")); // From the APIs console

		$private_key =  get_option($this->base . "path_private_key");

		if(file_exists($private_key)){
			$ga->auth->setPrivateKey($private_key); // Path to the .p12 file
			$auth = $ga->auth->getAccessToken();

			// Try to get the AccessToken
			if ($auth['http_code'] == 200) {
					$accessToken = $auth['access_token'];
					$tokenExpires = $auth['expires_in'];
					$tokenCreated = time();
			} else {
					error_log("Problema al verificar el AccessToken de Google Analytics API" . PHP_EOL, 3, get_template_directory() ."/theme_log/error.log");
			}

			// Ejecutar para saber el Account ID
			//getAccountId($ga,$accessToken);die;

			// Set the accessToken and Account-Id
			$ga->setAccessToken($accessToken);
			$ga->setAccountId(get_option($this->base . "account_id")); // Replace with real Account ID (Use getAccountId function)

			// Set the default params. For example the start/end dates and max-results

			$params = array(
					'metrics' => 'rt:activeUsers',
					'dimensions' => 'rt:pagePath',
					'sort' => '-rt:activeUsers',
					'max-results' => 10
			);
			$visits = $ga->query($params);
			$des = json_encode($visits);
		}else{
			echo "No existe";
		}

		$settings['standard'] = array(
			'title'					=> __( 'Standard', 'popularpostrealtime' ),
			'description'			=> __( $des, 'popularpostrealtime' ),
			'fields'				=> array(
				array(
					'id' 			=> 'client_id',
					'label'			=> __( 'Client ID' , 'popularpostrealtime' ),
					'description'	=> __( 'From the Google APIs console', 'popularpostrealtime' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( '', 'popularpostrealtime' )
				),
				array(
					'id' 			=> 'email',
					'label'			=> __( 'Email' , 'popularpostrealtime' ),
					'description'	=> __( 'From the APIs console (Google Service Account)', 'popularpostrealtime' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'youraccount@example.iam.gserviceaccount.com', 'popularpostrealtime' )
				),
				array(
					'id' 			=> 'account_id',
					'label'			=> __( 'Account ID' , 'popularpostrealtime' ),
					'description'	=> __( 'Google Analytics Account ID with format ga:xxxxxxxxx', 'popularpostrealtime' ),
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'ga:xxxxxxxxx', 'popularpostrealtime' )
				),
				array(
					'id' 			=> 'path_private_key',
					'label'			=> __( 'Path Private Key' , 'popularpostrealtime' ),
					'description'	=> __( '.p12 file from Google Service Account ', 'popularpostrealtime' ),
					'type'			=> 'text',
					'default'		=> $_SERVER['HOME'] . "/.ssh/google_key.p12",
					'placeholder'	=> __( 'full/path/example.p12', 'popularpostrealtime' )
				)
			)
		);

		$settings = apply_filters( $this->parent->_token . '_settings_fields', $settings );

		return $settings;
	}

	/**
	 * Register plugin settings
	 * @return void
	 */
	public function register_settings () {
		if ( is_array( $this->settings ) ) {

			// Check posted/selected tab
			$current_section = '';
			if ( isset( $_POST['tab'] ) && $_POST['tab'] ) {
				$current_section = $_POST['tab'];
			} else {
				if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
					$current_section = $_GET['tab'];
				}
			}

			foreach ( $this->settings as $section => $data ) {

				if ( $current_section && $current_section != $section ) continue;

				// Add section to page
				add_settings_section( $section, $data['title'], array( $this, 'settings_section' ), $this->parent->_token . '_settings' );

				foreach ( $data['fields'] as $field ) {

					// Validation callback for field
					$validation = '';
					if ( isset( $field['callback'] ) ) {
						$validation = $field['callback'];
					}

					// Register field
					$option_name = $this->base . $field['id'];
					register_setting( $this->parent->_token . '_settings', $option_name, $validation );

					// Add field to page
					add_settings_field( $field['id'], $field['label'], array( $this->parent->admin, 'display_field' ), $this->parent->_token . '_settings', $section, array( 'field' => $field, 'prefix' => $this->base ) );
				}

				if ( ! $current_section ) break;
			}
		}
	}

	public function settings_section ( $section ) {
		$html = '<p> ' . $this->settings[ $section['id'] ]['description'] . '</p>' . "\n";
		echo $html;
	}

	/**
	 * Load settings page content
	 * @return void
	 */
	public function settings_page () {

		// Build page HTML
		$html = '<div class="wrap" id="' . $this->parent->_token . '_settings">' . "\n";
			$html .= '<h2>' . __( 'Popular Post Real Time Settings' , 'popularpostrealtime' ) . '</h2>' . "\n";

			$tab = '';
			if ( isset( $_GET['tab'] ) && $_GET['tab'] ) {
				$tab .= $_GET['tab'];
			}

			// Show page tabs
			if ( is_array( $this->settings ) && 1 < count( $this->settings ) ) {

				$html .= '<h2 class="nav-tab-wrapper">' . "\n";

				$c = 0;
				foreach ( $this->settings as $section => $data ) {

					// Set tab class
					$class = 'nav-tab';
					if ( ! isset( $_GET['tab'] ) ) {
						if ( 0 == $c ) {
							$class .= ' nav-tab-active';
						}
					} else {
						if ( isset( $_GET['tab'] ) && $section == $_GET['tab'] ) {
							$class .= ' nav-tab-active';
						}
					}

					// Set tab link
					$tab_link = add_query_arg( array( 'tab' => $section ) );
					if ( isset( $_GET['settings-updated'] ) ) {
						$tab_link = remove_query_arg( 'settings-updated', $tab_link );
					}

					// Output tab
					$html .= '<a href="' . $tab_link . '" class="' . esc_attr( $class ) . '">' . esc_html( $data['title'] ) . '</a>' . "\n";

					++$c;
				}

				$html .= '</h2>' . "\n";
			}

			$html .= '<form method="post" action="options.php" enctype="multipart/form-data">' . "\n";

				// Get settings fields
				ob_start();
				settings_fields( $this->parent->_token . '_settings' );
				do_settings_sections( $this->parent->_token . '_settings' );
				$this->register_uploaded_file();
				$html .= ob_get_clean();

				$html .= '<p class="submit">' . "\n";
					$html .= '<input type="hidden" name="tab" value="' . esc_attr( $tab ) . '" />' . "\n";
					$html .= '<input name="Submit" type="submit" class="button-primary" value="' . esc_attr( __( 'Save Settings' , 'popularpostrealtime' ) ) . '" />' . "\n";
				$html .= '</p>' . "\n";
			$html .= '</form>' . "\n";
		$html .= '</div>' . "\n";

		echo $html;
	}

	public function register_uploaded_file(){
	        // First check if the file appears on the _FILES array
	        if(isset($_FILES['upload_file'])){
	                $pdf = $_FILES['upload_file'];

	                // Use the wordpress function to upload
	                // test_upload_pdf corresponds to the position in the $_FILES array
	                // 0 means the content is not associated with any other posts
	                $uploaded=media_handle_upload('upload_file', 0);
	                // Error checking using WP functions
	                if(is_wp_error($uploaded)){
                      echo "Error uploading file: " . $uploaded->get_error_message();
	                }else{
										  $path =  get_attached_file($uploaded);
											echo '<p id="_path_private_key">' . $path . "</p><br>";
                      echo "Key upload successful!, don´t forget save your settings";
	                }
	        }
	}




	/**
	 * Main PopularPostRealTime_Settings Instance
	 *
	 * Ensures only one instance of PopularPostRealTime_Settings is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see PopularPostRealTime()
	 * @return Main PopularPostRealTime_Settings instance
	 */
	public static function instance ( $parent ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $parent );
		}
		return self::$_instance;
	} // End instance()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __clone()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->parent->_version );
	} // End __wakeup()

}
