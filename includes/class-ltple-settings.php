<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Inbox_Settings {

	/**
	 * The single instance of LTPLE_Inbox_Settings.
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
		
		$this->plugin 		 	= new stdClass();
		$this->plugin->slug  	= 'live-template-editor-inbox';
		
		// add plugin to addons
		
		add_action('ltple_admin_addons', array($this, 'plugin_info' ) );
		
		// add settings
		
		add_action('ltple_plugin_settings', array($this, 'settings_fields' ) );
		
		// add menu
		
		add_action( 'ltple_admin_menu' , array( $this, 'add_menu_items' ) );

		// add tabs
		
		add_filter( 'ltple_admin_tabs', array( $this, 'add_tabs'), 1 );		
	}
	
	public function plugin_info(){
		
		$this->parent->settings->addons['live-template-editor-inbox'] = array(
			
			'title' 		=> 'Live Template Editor Inbox',
			'addon_link' 	=> 'https://github.com/rafasashi/live-template-editor-inbox',
			'addon_name' 	=> 'live-template-editor-inbox',
			'source_url' 	=> 'https://github.com/rafasashi/live-template-editor-inbox/archive/master.zip',
			'description'	=> 'Inbox addon for Live Template Editor allowing users to be contacted and reply from their own message box.',
			'author' 		=> 'Rafasashi',
			'author_link' 	=> 'https://profiles.wordpress.org/rafasashi/',
		);		
	}

	/**
	 * Build settings fields
	 * @return array Fields to be displayed on settings page
	 */
	public function settings_fields () {
		
		$settings = [];
		
		// add url to urls tab
		
		$settings['urls']['fields'][] = array(
		
			'id' 			=> 'inboxSlug',
			'label'			=> __( 'Inbox' , $this->plugin->slug ),
			'description'	=> '[ltple-client-inbox]',
			'type'			=> 'slug',
			'placeholder'	=> __( 'inbox', $this->plugin->slug )
		);
		
		// add new setting tab
		
		/*
		$settings['test'] = array(
			'title'					=> __( 'Test', $this->plugin->slug ),
			'description'			=> '',
			'fields'				=> array(
				
				array(
					'id' 			=> 'addon_url',
					'label'			=> __( 'Addon Url' , $this->plugin->slug ),
					'description'	=> '',
					'type'			=> 'text',
					'default'		=> '',
					'placeholder'	=> __( 'http://', $this->plugin->slug )
				),				
			)
		);
		*/
		
		if( !empty($settings) ){
		
			foreach( $settings as $slug => $data ){
				
				if( isset($this->parent->settings->settings[$slug]['fields']) && !empty($data['fields']) ){
					
					$fields = $this->parent->settings->settings[$slug]['fields'];
					
					$this->parent->settings->settings[$slug]['fields'] = array_merge($fields,$data['fields']);
				}
				else{
					
					$this->parent->settings->settings[$slug] = $data;
				}
			}
		}
	}
	
	/**
	 * Add settings page to admin menu
	 * @return void
	 */
	public function add_menu_items () {
		
		//add menu in wordpress dashboard
		/*
		add_submenu_page(
			'live-template-editor-client',
			__( 'Addon test', $this->plugin->slug ),
			__( 'Addon test', $this->plugin->slug ),
			'edit_pages',
			'edit.php?post_type=post'
		);
		*/
	}
	
	public function add_tabs() {
		
		// add in default contents
		
		//$this->parent->settings->tabs['default-contents']['addon-post-type-here'] = array( 'name' => 'Tab name here');
		
		// add in user contents
		
		//$this->parent->settings->tabs['user-contents']['addon-post-type-here'] = array( 'name' => 'Tab name here');
		
		// add in gallery settings
		
		//$this->parent->settings->tabs['gallery-settings']['addon-post-type-here'] = array( 'name' => 'Tab name here');
		
		// add in services apps
		
		//$this->parent->settings->tabs['services-apps']['addon-post-type-here'] = array( 'name' => 'Tab name here');
	}
}
