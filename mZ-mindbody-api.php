<?php
/**
Plugin Name: Advanced mZoo Mindbody Interface - Schedule, Events, Staff Display
Description: Interface Wordpress with MindbodyOnline data with Bootstrap Responsive Layout
Version: 2.1.0
Author: mZoo.org
Author URI: http://www.mZoo.org/
Plugin URI: http://www.mzoo.org/mz-mindbody-wp
Text Domain: mz-mindbody-api
Domain Path: /languages
Utilizing on API written by Devin Crossman.
*/

if ( !defined( 'WPINC' ) ) {
    die;
}

//define plugin path and directory
define( 'MZ_MINDBODY_SCHEDULE_DIR', plugin_dir_path( __FILE__ ) );
define( 'MZ_MINDBODY_SCHEDULE_URL', plugin_dir_url( __FILE__ ) );

//register activation and deactivation hooks
register_activation_hook(__FILE__, 'mZ_mindbody_schedule_activation');
register_deactivation_hook(__FILE__, 'mZ_mindbody_schedule_deactivation');

class MZ_Mindbody_API_Admin {
    
    protected $version;
 
    public function __construct( $version ) {
        $this->version = $version;
        $this->load_sections();
        }
        
    public function load_sections() {
        require_once MZ_MINDBODY_SCHEDULE_DIR .'lib/sections.php';
        }
}

class MZ_Mindbody_API_Loader {
 
protected $actions;
 
    protected $filters;
 
    public function __construct() {
 
        $this->actions = array();
        $this->filters = array();
     
    }
 
    public function add_action( $hook, $component, $callback ) {
        $this->actions = $this->add( $this->actions, $hook, $component, $callback );
    }
 
    public function add_filter( $hook, $component, $callback ) {
        $this->filters = $this->add( $this->filters, $hook, $component, $callback );
    }
 
    private function add( $hooks, $hook, $component, $callback ) {
 
        $hooks[] = array(
            'hook'      => $hook,
            'component' => $component,
            'callback'  => $callback
        );
 
        return $hooks;
 
    }
 
    public function run() {
 
        foreach ( $this->filters as $hook ) {
            add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ) );
        }
 
        foreach ( $this->actions as $hook ) {
            add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ) );
        }
 
    }
 
}

class MZ_Mindbody_API {
 
    protected $loader;
 
    protected $plugin_slug;
 
    protected $version;
 
    public function __construct() {
 
        $this->plugin_slug = 'mz-mindbody-api';
        $this->version = '2.1.0';
 
        $this->load_dependencies();
        $this->define_main_hooks();
        $this->add_shortcodes();
 
    }
 
    private function load_dependencies() {
    
 		//Advanced Includes
		foreach ( glob( plugin_dir_path( __FILE__ )."advanced/*.php" ) as $file )
        	include_once $file;
        	
        include_once(dirname( __FILE__ ) . '/mindbody-php-api/MB_API.php');

		foreach ( glob( plugin_dir_path( __FILE__ )."inc/*.php" ) as $file )
			include_once $file;
			
		if (phpversion() >= 5.3) {
			include_once('php_variants/sort_newer.php');
			}else{
			include_once('php_variants/sort_older.php');
			}
	
		//Functions

		require_once MZ_MINDBODY_SCHEDULE_DIR .'lib/functions.php';
        	
        $this->loader = new MZ_Mindbody_API_Loader();
    }
 
    private function define_admin_hooks() {
 
        $admin = new MZ_Mindbody_API_Admin( $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $admin, 'enqueue_styles' );
        $this->loader->add_action( 'add_meta_boxes', $admin, 'add_meta_box' );
    }
    
    private function define_main_hooks() {
 
        $this->loader->add_action( 'init', $this, 'myStartSession' );
        $this->loader->add_action( 'wp_logout', $this, 'myStartSession' );
        $this->loader->add_action( 'wp_login', $this, 'myEndSession' );
        
        }
        
        /*
        add_action('init', 'myStartSession', 1);
    	add_action('wp_logout', 'myEndSession');
    	add_action('wp_login', 'myEndSession');
    	*/

    public function myStartSession() {
			if ((function_exists('session_status') && session_status() !== PHP_SESSION_ACTIVE) || !session_id()) {
				  session_start();
				}
		}

    private function myEndSession() {
			session_destroy ();
		}
 
 	private function add_shortcodes() {
 	
 		$schedule_display = new MZ_Mindbody_Schedule_Display();
 		$mz_staff = new MZ_MBO_Staff();
 		$mz_events = new MZ_MBO_Events();
 		$mz_clients = new MZ_MBO_Clients();
 		
        add_shortcode('mz-mindbody-show-schedule', array($schedule_display, 'mZ_mindbody_show_schedule'));
        add_shortcode('mz-mindbody-staff-list', array($mz_staff, 'mZ_mindbody_staff_listing'));
        add_shortcode('mz-mindbody-show-events', array($mz_events, 'mZ_mindbody_show_events'));
        add_shortcode('mz-mindbody-login', array($mz_clients, 'mZ_mindbody_login'));
        add_shortcode('mz-mindbody-logout', array($mz_clients, 'mZ_mindbody_logout'));
        add_shortcode('mz-mindbody-signup', array($mz_clients, 'mZ_mindbody_signup'));

    }
 
    public function run() {
        $this->loader->run();
    }
 
    public function get_version() {
        return $this->version;
    }
 
}

function mZ_MBO_load_plugin_textdomain() {
	load_plugin_textdomain('mz-mindbody-api',false,dirname(plugin_basename(__FILE__)) . '/languages');
	}
add_action( 'plugins_loaded', 'mZ_MBO_load_plugin_textdomain' );

function mZ_mindbody_schedule_activation() {
	//Don't know if there's anything we need to do here.
}

function mZ_mindbody_schedule_deactivation() {
	//Don't know if there's anything we need to do here.
}

//register uninstaller
register_uninstall_hook(__FILE__, 'mZ_mindbody_schedule_uninstall');

function mZ_mindbody_schedule_uninstall(){
	//actions to perform once on plugin uninstall go here
	delete_option('mz_mindbody_options');
}

function mz_mbo_enqueue($hook) {
    if ( 'settings_page_mz-mindbody-api/mZ-mindbody-api' != $hook ) {
        return;
    }
    wp_register_style( 'mz_mbo_admin_css', plugin_dir_url( __FILE__ ) . 'css/mbo_style_admin.css', false, '1.0.0' );
        wp_enqueue_style( 'mz_mbo_admin_css' );

}
add_action( 'admin_enqueue_scripts', 'mz_mbo_enqueue' );

//TODO Deal with conflict when $mb class get's called twice
add_action('widgets_init', 'mZ_mindbody_schedule_register_widget');

function mZ_mindbody_schedule_register_widget() {
    register_widget( 'mZ_Mindbody_day_schedule');
}

if (!function_exists( 'mZ_latest_jquery' )){
	function mZ_latest_jquery(){
		//	Use latest jQuery release
		if( !is_admin() ){
			wp_deregister_script('jquery');
			wp_register_script('jquery', ("http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"), false, '');
			wp_enqueue_script('jquery');
		}
	}
	add_action('wp_enqueue_scripts', 'mZ_latest_jquery');
}


class mZ_Mindbody_day_schedule extends WP_Widget {

    function mZ_Mindbody_day_schedule() {
        $widget_ops = array(
            'classname' => 'mZ_Mindbody_day_schedule_class',
            'description' => __('Display class schedule for current day.', 'mz-mindbody-api')
            );
        $this->WP_Widget('mZ_Mindbody_day_schedule', __('Today\'s MindBody Schedule', 'mz-mindbody-api'),
                            $widget_ops );
    } 
    
    function form($instance){
        $defaults = array('title' => __('Today\'s Classes', 'mz-mindbody-api'));
        $instance = wp_parse_args( (array) $instance, $defaults);
        $title = $instance['title'];
        ?>
           <p>Title: <input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>"  
           type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <?php
    }
    
    //save the widget settings
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        return $instance;
    }
    
    function widget($args, $instance){
        extract($args);
        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        $arguments[__('type', 'mz-mindbody-api')] = __('day', 'mz-mindbody-api');
        if (!empty($title) ) 
            { echo $before_title . $title . $after_title; };
            echo(mZ_mindbody_show_schedule($arguments, $account=0));
        echo $after_widget;
    }
}


    
if ( is_admin() )
{     
	$admin_backend = new MZ_Mindbody_API_Admin('2.1.0');
}
else
{// non-admin enqueues, actions, and filters

function run_mz_mindbody_schedule_api() {
 
    $mz_mbo = new MZ_Mindbody_API();
    $mz_mbo->run();
 
}
 
run_mz_mindbody_schedule_api();
	
/*	function load_jquery() {
		wp_enqueue_script( 'jquery' );
	}
	add_action( 'wp_enqueue_script', 'load_jquery' );
	*/
	
}//EOF Not Admin


?>
