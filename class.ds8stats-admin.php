<?php

class DS8Stats_Admin {
  
	private static $initiated = false;
	private static $notices   = array();

	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		// The standalone stats page was removed in 3.0 for an all-in-one config and stats page.
		// Redirect any links that might have been bookmarked or in browser history.
		if ( isset( $_GET['page'] ) && 'ds8stats-stats-display' == $_GET['page'] ) {
			wp_safe_redirect( esc_url_raw( self::get_page_url( 'stats' ) ), 301 );
			die;
		}

		self::$initiated = true;
                self::ds8_on_init();

		add_action( 'admin_init', array( 'DS8Stats_Admin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'DS8Stats_Admin', 'admin_menu' ), 5 );
		add_action( 'admin_enqueue_scripts', array( 'DS8Stats_Admin', 'load_resources' ) );
		add_filter( 'plugin_action_links', array( 'DS8Stats_Admin', 'plugin_action_links' ), 10, 2 );
		//add_filter( 'plugin_action_links_'.plugin_basename( plugin_dir_path( __FILE__ ) . 'ds8stats.php'), array( 'DS8Stats_Admin', 'admin_plugin_settings_link' ) );
		//add_filter( 'all_plugins', array( 'DS8Stats_Admin', 'modify_plugin_description' ) );
                
                add_action('admin_notices', array( 'DS8Stats_Admin','general_admin_notice'));
	}
        
        public static function general_admin_notice(){
            global $pagenow;
            if ( $pagenow == 'admin.php' && isset($_GET['fname'])) {
              $path = DS8STATS_PLUGIN_DIR . '_tablas/';
              if (file_exists($path.$_GET['fname'])){
                $result = unlink($path.$_GET['fname']);
                if ($result){
                  echo '<div class="notice notice-success is-dismissible">
                      <p>Se ha borrado exitosamente el archivo '.$path.$_GET['fname'].'</p>
                  </div>';
                }else{
                  echo '<div class="notice notice-warning is-dismissible">
                      <p>No se ha logrado borrar el archivo '.$path.$_GET['fname'].'</p>
                  </div>';
                }
              }
            }
            
            if (isset($GLOBALS['upload_result'])){
               echo '<div class="notice notice-success is-dismissible">
                      <p>Se ha cargado exitosamente el archivo</p>
                  </div>';
            }
        }

	public static function admin_init() {
		if ( get_option( 'Activated_DS8Stats' ) ) {
			delete_option( 'Activated_DS8Stats' );
			if ( ! headers_sent() ) {
				wp_redirect( add_query_arg( array( 'page' => 'ds8stats-key-config', 'view' => 'start' ), class_exists( 'Jetpack' ) ? admin_url( 'admin.php' ) : admin_url( 'options-general.php' ) ) );
			}
		}
                
                // JLMA - FEATURE 01-09-2022
                if(isset($_POST) && isset($_POST['option_page']) &&  $_POST['option_page'] === 'ds8-settings-group') {
                    update_option('plugin_permalinks_flushed', 0);
                }
                
                //NOT USED - register_setting('ds8-settings-group', 'ds8_stats_page');
		load_plugin_textdomain( 'ds8stats' );
	}

	public static function admin_menu() {
			self::load_menu();
	}

	public static function admin_head() {
		if ( !current_user_can( 'manage_options' ) )
			return;
	}
	
	public static function admin_plugin_settings_link( $links ) { 
  		$settings_link = '<a href="'.esc_url( self::get_page_url() ).'">'.__('Settings', 'ds8stats').'</a>';
  		array_unshift( $links, $settings_link ); 
  		return $links; 
	}

	public static function load_menu() {
          add_menu_page(__('DS8 Stats', 'ds8stats'), __('DS8 Stats', 'ds8stats'), 'manage_options', 'ds8stats-key-config', array( 'DS8Stats_Admin', 'display_page' ), "dashicons-chart-pie", 10);
          //add_submenu_page( 'ds8stats-key-config', __('Stats Import', 'ds8stats'), __('Import', 'ds8stats'), 'manage_options', 'import-stat', array( 'DS8Stats_Admin', 'ds8stats_view' ));
          //$hook = add_options_page( __('DS8 Stats', 'ds8stats'), __('DS8 Stats', 'ds8stats'), 'manage_options', 'ds8stats-key-config', array( 'DS8Stats_Admin', 'ds8stats_view' ) );
	}
        
        // Hook into WordPress init; this function performs report generation when the admin form is submitted
        public static function ds8_on_init() {
                global $pagenow;
                // Check if we are in admin and on the report page
                if (!is_admin())
                        return;
                if ( ! function_exists( 'wp_handle_upload' ) ) {
                    require_once( ABSPATH . 'wp-admin/includes/file.php' );
                }
                //admin.php when is options
                if ($pagenow == 'admin.php' && !empty($_POST['uploadds8']) && ($_POST['uploadds8'] === "Upload" || $_POST['uploadds8'] === "Cargar") ) {
                    // Upload file
                    if(isset($_POST['uploadds8'])){
                        if($_FILES['file']['name'] != ''){
                            $uploadedfile = $_FILES['file'];
                            $upload_overrides = array( 'test_form' => false );
                            //$uploaded = wp_handle_upload( $uploadedfile, $upload_overrides );
                            $success = move_uploaded_file($uploadedfile['tmp_name'],DS8STATS_PLUGIN_DIR . '_tablas/'.$uploadedfile['name']);
                            if ($success === FALSE) { //if (is_wp_error($uploaded)) {
                                //echo "Error uploading file: " . $uploaded->get_error_message();
                                $GLOBALS['upload_result'] = $success;
                            } else {
                                //echo "File upload successful!";
                                //self::get_parsed_excel($uploaded);
                                $GLOBALS['upload_result'] = $success;
                            }
                        }
                    }
                }
        }
        
        public static function ds8stats_view() {
            ?>
            <div class="wrap">
                <h1><?php _e('Import Stats (CSV Format)', 'ds8stats'); ?></h1>

                <?php
                $url = add_query_arg(array(
                'page'=> basename(__FILE__),
                'page'=>'massive-excel-inv'
               ), admin_url('admin.php'));
                ?>

                <?php  self::ds8_on_init(); ?>

                <form method="post" action="<?php echo $_SERVER['REQUEST_URI'] ?>" enctype='multipart/form-data'>
                    <table>
                            <tr>
                                    <td><?php _e('Load new Stats', 'ds8stats'); ?></td>
                                    <td><input type='file' name='file' onchange="ValidateSingleInput(this)"></td>
                            </tr>
                            <tr>
                                    <td>&nbsp;</td>
                                    <td><?php submit_button(__('Upload', 'ds8stats'), '', 'uploadds8', false); ?></td>
                            </tr>
                    </table>
                </form>
            </div>
        <?php
        }
        
        public static function display_page() {
		if ( ( isset( $_GET['view'] ) && $_GET['view'] == 'start'  ) || $_GET['page'] == 'ds8stats-key-config' ){
                  
                  require_once( DS8STATS_PLUGIN_DIR . 'includes/class.statsfile.php' );
                  
			//self::display_start_page();
                        //DS8Stats::view( 'start' );
                        // FEATURE JLMA 29-08-2022
                        $options = array(
                            array("name" => "Página tabla",
                                "desc" => "Para la creación y validación de las URL's del shortcode",
                                "id" => "ds8_stats_page",
                                "type" => "select-page",
                                "std" => ""
                            )
                        );
                        DS8Stats::view( 'start', array(
                                'front_page_elements' => null,
                                'options' => array()//$options
                        ) );
                }
	}

	public static function load_resources() {
		global $hook_suffix;

		if ( in_array( $hook_suffix, apply_filters( 'ds8stats_admin_page_hook_suffixes', array(
			'toplevel_page_ds8stats-key-config'
		) ) ) ) {
                        $screen = get_current_screen();
                  
			wp_register_style( 'ds8stats.css', plugin_dir_url( __FILE__ ) . 'assets/css/ds8stats.css', array(), DS8STATS_VERSION );
			wp_enqueue_style( 'ds8stats.css');
                        
                        /*wp_enqueue_style( 'jquery-ui-smoothness', // wrapped for brevity
                        '//code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css', [], null );*/

                        /*if( is_object( $screen ) && 'stat' == $screen->post_type ){
                          wp_register_script( 'ds8stats.js', plugin_dir_url( __FILE__ ) . '_inc/ds8stats.js', array('jquery','jquery-ui-datepicker'), DS8STATS_VERSION );
                          wp_enqueue_script( 'ds8stats.js' );
                        }*/
		}
	}	

	public static function plugin_action_links( $links, $file ) {
		if ( $file == plugin_basename( plugin_dir_url( __FILE__ ) . '/ds8stats.php' ) ) {
			$links[] = '<a href="' . esc_url( self::get_page_url() ) . '">'.esc_html__( 'Settings' , 'ds8stats').'</a>';
		}

		return $links;
	}

	public static function display_alert() {
		DS8Stats::view( 'notice', array(
			'type' => 'alert',
			'code' => (int) get_option( 'ds8stats_alert_code' ),
			'msg'  => get_option( 'ds8stats_alert_msg' )
		) );
	}
        
        public static function get_page_url( $page = 'config' ) {

		$args = array( 'page' => 'edit.php?post_type=stat&page=import-stat');//'ds8stats-key-config' );
		$url = add_query_arg( $args,  admin_url( 'options-general.php' ) );
		return $url;
	}
        
        public static function plugin_deactivation( ) {
          
        }
        
        public static function create_form($options) {
            foreach ($options as $value) {
                switch ($value['type']) {
                    case "textarea";
                        self::create_section_for_textarea($value);
                        break;
                    case "text";
                        self::create_section_for_text($value);
                        break;
                    case "select":
                        self::create_section_for_taxonomy_select($value);
                        break;
                    case "select-page":
                        self::combo_select_page_callback($value);
                        break;
                }
            }
        }
        
        public static function ds8_get_formatted_page_array() {

            $ret = array();
            $pages = get_pages();
            if ($pages != null) {
                foreach ($pages as $page) {
                    $ret[$page->ID] = array("name" => $page->post_title, "id" => $page->ID);
                }
            }

            return $ret;
        }

        public static function combo_select_page_callback($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';
            echo '<td>';

            echo "<select id='" . $value['id'] . "' class='post_form' name='" . $value['id'] . "'>\n";
            echo "<option value='0'>-- Select page --</option>";

            $pages = get_pages();

            foreach ($pages as $page) {
                $checked = ' ';

                if (get_option($value['id']) == $page->ID) {
                    $checked = ' selected="selected" ';
                } else if (get_option($value['id']) === FALSE && $value['std'] == $page->ID) {
                    $checked = ' selected="selected" ';
                } else {
                    $checked = '';
                }

                echo '<option value="' . $page->ID . '" ' . $checked . '/>' . $page->post_title . "</option>\n";
            }
            echo "</select>";
            echo "</td>";
            echo '</tr>';
        }

        public static function create_section_for_taxonomy_select($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';
            echo '<td>';

            echo "<select id='" . $value['id'] . "' class='post_form' name='" . $value['id'] . "'>\n";
            echo "<option value='0'>-- Seleccione --</option>";

            foreach ($value['options'] as $option_value => $option_list) {
                $checked = ' ';

                if (get_option($value['id']) == $option_value) {
                    $checked = ' selected="selected" ';
                } else if (get_option($value['id']) === FALSE && $value['std'] == $option_list) {
                    $checked = ' selected="selected" ';
                } else {
                    $checked = '';
                }

                echo '<option value="' . $option_value . '" ' . $checked . '/>' . $option_list . "</option>\n";
            }
            echo "</select>";
            echo "</td>";
            echo '</tr>';
        }

        public static function create_section_for_textarea($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';

            $text = "";
            if (get_option($value['id']) === FALSE) {
                $text = $value['std'];
            } else {
                $text = get_option($value['id']);
            }

            echo '<td><textarea rows="6" cols="80" id="' . $value['id'] . '" name="' . $value['id'] . '">'.strip_tags($text).'</textarea></td>';
            echo '</tr>';
        }

        public static function create_section_for_text($value) {
            echo '<tr valign="top">';
            echo '<th scope="row">' . $value['name'] . '</th>';

            $text = "";
            if (get_option($value['id']) === FALSE) {
                $text = $value['std'];
            } else {
                $text = get_option($value['id']);
            }

            echo '<td><input type="text" id="' . $value['id'] . '" name="' . $value['id'] . '" value="' . $text . '" /></td>';
            echo '</tr>';
        }
	
}
