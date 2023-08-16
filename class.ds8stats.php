<?php

class DS8Stats {

        private static $initiated = false;

	public static function init() {
		if ( ! self::$initiated ) {
                        //add_filter( 'load_textdomain_mofile', array('DS8Stats','ds8leyesnormativa_textdomain'), 10, 2 );
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;
                self::set_locale();
                
                //add_filter('query_vars', array('DS8Stats', 'ds8stats_register_query_var') );
                add_rewrite_tag('%pyear%', '([^&]+)');
                add_rewrite_rule('^estadisticas/([^/]*)/?','index.php?pagename=estadisticas&pyear=$matches[1]','top');
                
                add_action('wp_enqueue_scripts', array('DS8Stats', 'ds8_stats_javascript'), 10);
                add_shortcode( 'ds8stats', array('DS8Stats', 'ds8stats_shortcode_fn') );
                
                //add_filter( 'body_class', array('DS8Stats','ds8_modify_body_classes'), 10, 2 );
                //DEPRECATED add_filter('get_image_tag_class', array('DS8Stats','ds8_add_image_class'));
                //add_filter( 'post_thumbnail_html', array('DS8Stats','wpdev_filter_post_thumbnail_html'), 10, 5 );
                //add_action( 'pre_get_posts', array('DS8Stats','set_posts_per_page_for_stats') );
                
                //add_filter('single_template', array('DS8Stats','load_cpt_template'), 10, 1);
                //add_filter('archive_template', array('DS8Stats','get_custom_post_type_template'), 10, 1);
	}
        public static function ds8stats_register_query_var($query_vars){
            $query_vars[] = 'pyear';
            return $query_vars;
        }
        
        private static function csv_to_multidimension_array($filename='', $delimiter=','){
            if(!file_exists($filename) || !is_readable($filename)) {
                return false;
            }

            $data = array();

            if (($handle = fopen($filename, 'r')) !== false) {
                while (($row = fgetcsv($handle, 1000, $delimiter)) !== false ) {
                    $data[] = $row;
                }
                fclose($handle);
            }
            return $data;
        }
        
        public static function get_file_stat($year){
            $php_files = glob(DS8STATS_PLUGIN_DIR . '_tablas/*.csv');
            
            foreach($php_files as $file) {
              $file_info = pathinfo($file);
              $file_extension = $file_info['extension'];
              $str = basename($file);
              
              preg_match_all('/'.$year.'/', $str, $matches);

              if ($matches[0] != null || $matches[0] != false) {
                return $str;
              }
            }
            
            return '';
        }
        
        public static function ds8stats_shortcode_fn($atts) {
          
          if (is_admin()) return;
          
          extract( shortcode_atts( array(
              'type' => 'stat',
              'perpage' => 3
          ), $atts ) );
          
          $php_files = glob(DS8STATS_PLUGIN_DIR . '_tablas/*.csv');
          $list = array();
          
          $pyear = get_query_var( 'pyear' );
          
          foreach($php_files as $file) {
            $file_info = pathinfo($file);
            $file_extension = $file_info['extension'];
            $str = basename($file);
            preg_match_all('/\d{4}\+?/', $str, $matches);
            
            if ($matches[0] != null || $matches[0] != false) {
              $list[] = $matches[0][0];
            }
          }
          rsort($list);
          
          $output = '<div class="form-group">
            <select id="listofoptions" class="form-control" id="exampleFormControlSelect1">';
          foreach ($list as $value){
            $output .= '<option value="'.$value.'" '.($value==$pyear ? 'selected' : '').'>'.$value.'</option>';
          }
          $output .= '</select>
          </div>';
          
          if ( !empty($pyear) ) {
            $file_name = self::get_file_stat($pyear);
          }else{
            $file_name = 'stats_2023.csv';
          }
          
          $main_dir = DS8STATS_PLUGIN_DIR . '_tablas/'.$file_name; //stats_2023.csv';
          $table = self::csv_to_multidimension_array($main_dir);
          $output .= self::build_table($table);
          //$tabla = stripslashes(file_get_contents($main_dir));
          
          return $output;
          
        }
        
        private static function build_table($table){
          $html = '<div class="table-responsive"><table class="table">';
          // header row
          $html .= '<thead><tr>';
          foreach($table[0] as $key=>$value){
                  $html .= '<th '.($key != 0 ? ($key == 2 ? 'class="row-text-right"' : 'class="row-text-center"') : '').' >' . htmlspecialchars($value) . '</th>';
              }
          $html .= '</tr></thead>';
          unset($table[0]);

          // data rows
          $html .= '<tbody>';
          foreach( $table as $key=>$value){
              $html .= '<tr>';
              foreach($value as $key2=>$value2){
                  //if ( strtoupper(trim($value2)) == 'SI'){
                  if ( ($key2==6 || $key2==7 || $key2==8) && !empty(trim($value2))){
                    $html .= '<td class="row-btn '.($key2 != 0 ? ($key2 == 2 ? 'row-text-right' : 'row-text-center') : '').'"><button data-test="button" type="button" class="btn-default btn Ripple-parent btn-estadistica" data-link="'.$value2.'"><div data-test="waves" class="Ripple " style="top: 0px; left: 0px; width: 0px; height: 0px;"></div></button></td>';
                  }else{
                    $html .= '<td '.($key2 != 0 ? ($key2 == 2 ? 'class="row-text-right"' : 'class="row-text-center"') : '').' >' . $value2 . '</td>';
                  }
              }
              $html .= '</tr>';
          }

          // finish table and return it

          $html .= '</tbody></table></div>';
          return $html;
        }
        
        public static function set_posts_per_page_for_stats( $query ) {
            if ( !is_admin() && $query->is_main_query() && is_post_type_archive( 'stat' ) ) {
              $query->set( 'posts_per_page', '3' );
            }
        }
        
        public static function ds8_modify_body_classes( $classes, $class ) {
            // Modify the array $classes to your needs
            if( is_archive() && is_post_type_archive('stat') )
            {
                $classes[] = 'woocommerce';
                $classes[] = 'woocommerce-page';
            }    
            return $classes;
        }
        

        /**
        * Link thumbnails to their posts based on attr
        *
        * @param $html
        * @param int $pid
        * @param int $post_thumbnail_id
        * @param int $size
        * @param array $attr
        *
        * @return string
        */
        public static function wpdev_filter_post_thumbnail_html( $html, $pid, $post_thumbnail_id, $size, $attr ) {

                if ( ! empty( $attr[ 'itemprop' ] ) && $attr['itemprop'] === 'image' ) {
                      
                      $image = wp_get_attachment_image_src( $post_thumbnail_id, "full" );

                      if ($image !== false){
                        $html = sprintf(
                                '<span data-src="%s" title="%s" class="custom-lightbox">%s</span>',
                                $image[0], //get_permalink( $pid ),
                                esc_attr( get_the_title( $pid ) ),
                                $html
                        );
                      }
                      else{
                        return;
                      }
                }

               return $html;
        }
        
        public static function ds8_add_image_class($class){
            if ('stats' == get_post_type()) $class .= ' additional-class';
            return $class;
        }
        
        public static function update_edit_form() {
            echo ' enctype="multipart/form-data"';
        }        
        
        /**
	 * Define the locale for this plugin for internationalization.
	 *
	 * @since    1.0
	 */
	private static function set_locale() {
		load_plugin_textdomain( 'ds8stats', false, plugin_dir_path( dirname( __FILE__ ) ) . '/languages/' );

	}
        
        public static function ds8leyesnormativa_textdomain( $mofile, $domain ) {
                if ( 'ds8stats' === $domain && false !== strpos( $mofile, WP_LANG_DIR . '/plugins/' ) ) {
                        $locale = apply_filters( 'plugin_locale', determine_locale(), $domain );
                        $mofile = WP_PLUGIN_DIR . '/' . dirname( plugin_basename( __FILE__ ) ) . '/languages/' . $domain . '-' . $locale . '.mo';
                }
                return $mofile;
        }
        
        
        /**
	 * Check if plugin is active
	 *
	 * @since    1.0
	 */
	private static function is_plugin_active( $plugin_file ) {
		return in_array( $plugin_file, apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) );
	}

        public static function ds8_stats_javascript(){

            //wp_enqueue_style('stats-css', plugin_dir_url( __FILE__ ) . 'assets/css/stats.css', array(), DS8STATS_VERSION);
            
            global $post;
            if( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'ds8stats') ) {
              wp_register_script( 'stats.js', plugin_dir_url( __FILE__ ) . 'assets/js/stats.js', array('jquery'), DS8STATS_VERSION, true );
              wp_enqueue_script( 'stats.js' );
            }

        }

        public static function view( $name, array $args = array() ) {
                $args = apply_filters( 'ds8stats_view_arguments', $args, $name );

                foreach ( $args AS $key => $val ) {
                        $$key = $val;
                }

                load_plugin_textdomain( 'ds8stats' );

                $file = DS8STATS_PLUGIN_DIR . 'views/'. $name . '.php';

                include( $file );
	}
        
        public static function plugin_deactivation( ) {
            unregister_post_type( 'stat' );
            flush_rewrite_rules();
        }

        /**
	 * Attached to activate_{ plugin_basename( __FILES__ ) } by register_activation_hook()
	 * @static
	 */
	public static function plugin_activation() {
		if ( version_compare( $GLOBALS['wp_version'], DS8STATS_MINIMUM_WP_VERSION, '<' ) ) {
			load_plugin_textdomain( 'ds8stats' );
                        
			$message = '<strong>'.sprintf(esc_html__( 'FD Stats %s requires WordPress %s or higher.' , 'ds8stats'), DS8STATS_VERSION, DS8STATS_MINIMUM_WP_VERSION ).'</strong> '.sprintf(__('Please <a href="%1$s">upgrade WordPress</a> to a current version, or <a href="%2$s">downgrade to version 2.4 of the Akismet plugin</a>.', 'ds8stats'), 'https://codex.wordpress.org/Upgrading_WordPress', 'https://wordpress.org/extend/plugins/ds8stats/download/');

			DS8Stats::bail_on_activation( $message );
		} elseif ( ! empty( $_SERVER['SCRIPT_NAME'] ) && false !== strpos( $_SERVER['SCRIPT_NAME'], '/wp-admin/plugins.php' ) ) {
                        flush_rewrite_rules();
			add_option( 'Activated_DS8Stats', true );
		}
	}

        private static function bail_on_activation( $message, $deactivate = true ) {
?>
<!doctype html>
<html>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<style>
* {
	text-align: center;
	margin: 0;
	padding: 0;
	font-family: "Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif;
}
p {
	margin-top: 1em;
	font-size: 18px;
}
</style>
</head>
<body>
<p><?php echo esc_html( $message ); ?></p>
</body>
</html>
<?php
		if ( $deactivate ) {
			$plugins = get_option( 'active_plugins' );
			$ds8leyesnormativa = plugin_basename( DS8CALENDAR__PLUGIN_DIR . 'ds8stats.php' );
			$update  = false;
			foreach ( $plugins as $i => $plugin ) {
				if ( $plugin === $ds8leyesnormativa ) {
					$plugins[$i] = false;
					$update = true;
				}
			}

			if ( $update ) {
				update_option( 'active_plugins', array_filter( $plugins ) );
			}
		}
		exit;
	}

}