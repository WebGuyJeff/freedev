<?php
namespace BigupWeb\Freedev;

/**
 * Initialise Freedev Theme.
 *
 * Setup styles, functions, configuration and all dependencies for this theme.
 *
 * @package freedev
 */
class Theme_Setup {


	/**
	 * Setup all actions, filters and call functions.
	 */
	public function all() {

		// Setup settings page(s).
		new Settings_Parent();
		$Settings = new Settings();
		add_action( 'admin_init', array( new Settings_Tab_Identity(), 'init' ), 10, 0 );
		add_action( 'admin_init', array( new Settings_Tab_Verification(), 'init' ), 10, 0 );
		add_action( 'admin_menu', array( &$Settings, 'register_admin_menu' ), 99, 0 );
		add_action( 'bigup_settings_dashboard_entry', array( &$Settings, 'echo_settings_link_callback' ), 10, 0 );

		// Gut' way to load stylesheet to match editor to frontend... (doesn't seem to override .editor-styles-wrapper).
		add_editor_style( FREEDEV_URL . 'build/css/freedev.css' );

		// Methods in this class.
		add_action( 'wp_enqueue_scripts', array( $this, 'register_front_end_scripts_and_styles' ), 10, 0 );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts_and_styles' ), 10, 0 );
		add_action( 'enqueue_block_editor_assets', array( $this, 'register_editor_scripts_and_styles' ), 10, 0 );
		add_action( 'wp_head', array( $this, 'add_pingback_header' ), 10, 0 );
		add_action( 'wp_head', array( new Head_Inject(), 'print_head_markup' ), 5, 0 );
		add_action( 'after_setup_theme', array( $this, 'theme_supports_and_features' ), 10, 0 );
		add_action( 'init', array( $this, 'register_taxonomy_for_default_posts' ), 10, 0 );
		self::remove_head_bloat();

		add_action( 'init', array( new Patterns(), 'register_categories' ), 10, 0 );
		add_filter( 'safe_style_css', fn( $styles ) => Escape::get_safe_styles( $styles ), 10, 1 );
	}


	/**
	 * Register front end scripts and styles.
	 */
	public function register_front_end_scripts_and_styles() {
		if ( $GLOBALS['pagenow'] !== 'wp-login.php' ) {
			wp_enqueue_style( 'freedev_css', FREEDEV_URL . 'build/css/freedev.css', array(), filemtime( FREEDEV_PATH . 'build/css/freedev.css' ), 'all' );
			wp_register_script( 'gsap', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js', array(), '3.12.2', true );
			wp_register_script( 'gsap_scrolltrigger', 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js', array( 'gsap' ), '3.12.2', true );
			wp_enqueue_script( 'freedev_js', FREEDEV_URL . 'build/js/freedev.js', array( 'gsap', 'gsap_scrolltrigger' ), filemtime( FREEDEV_PATH . 'build/js/freedev.js' ), true );
		}
		if ( current_user_can( 'manage_options' ) && FREEDEV_DEBUG ) {
			wp_enqueue_style( 'freedev_dev_css', FREEDEV_URL . 'build/css/freedev-dev.css', array(), filemtime( FREEDEV_PATH . 'build/css/freedev-dev.css' ), 'all' );
		}
	}


	/**
	 * Register admin scripts and styles.
	 */
	public function register_admin_scripts_and_styles() {
		wp_enqueue_style( 'freedev_admin_css', FREEDEV_URL . 'build/css/freedev-admin.css', array(), filemtime( FREEDEV_PATH . 'build/css/freedev-admin.css' ), 'all' );

		// Development styles.
		if ( current_user_can( 'manage_options' ) && FREEDEV_DEBUG ) {
			wp_enqueue_style( 'freedev_dev_css', FREEDEV_URL . 'build/css/freedev-dev.css', array(), filemtime( FREEDEV_PATH . 'build/css/freedev-dev.css' ), 'all' );
		}
	}


	/**
	 * Register editor scripts and styles.
	 */
	public function register_editor_scripts_and_styles() {
		wp_enqueue_style( 'freedev_editor_css', FREEDEV_URL . 'build/css/freedev-editor.css', array(), filemtime( FREEDEV_PATH . 'build/css/freedev-editor.css' ), 'all' );
	}


	/**
	 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
	 */
	public function add_pingback_header() {
		if ( is_singular() && pings_open() ) {
			printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
		}
	}


	/**
	 * Setup theme defaults and register support for WordPress features.
	 */
	public function theme_supports_and_features() {
		load_theme_textdomain( 'freedev', get_template_directory() . '/languages' );
		add_theme_support( 'editor-styles' );
		add_theme_support( 'block-template-parts' );
		add_theme_support( 'wp-block-styles' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'align-wide' );
		add_theme_support( 'responsive-embeds' );
		add_theme_support( 'custom-spacing' );
		add_theme_support( 'border' );
		add_theme_support( 'link-color' );
		add_theme_support(
			'html5',
			array(
				'search-form',
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);
		add_theme_support(
			'custom-logo',
			array(
				'height'      => 1000,
				'width'       => 1000,
				'flex-width'  => true,
				'flex-height' => true,
			)
		);

		// Custom image sizes.
		add_image_size( 'page-hero', 1920, 1080, true );
		add_image_size( 'service-cards', 700, 500, true );
	}


	/**
	 * Enable taxonomy for default post types.
	 */
	public function register_taxonomy_for_default_posts() {
		register_taxonomy_for_object_type( 'post_tag', 'page' );
		register_taxonomy_for_object_type( 'category', 'page' );
	}


	/**
	 * Remove unwanted content from the wp_head hook.
	 */
	public static function remove_head_bloat() {
		remove_action( 'wp_head', 'rsd_link' );
		remove_action( 'wp_head', 'wlwmanifest_link' );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_head', 'index_rel_link' );
		remove_action( 'wp_head', 'wp_site_icon', 99, 0 );
		remove_action( 'wp_head', 'start_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'parent_post_rel_link', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link', 10, 0 );
		remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0 );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'wp_shortlink_wp_head', 10, 0 );
	}
}
