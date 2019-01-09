<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class LTPLE_Inbox {

	/**
	 * The single instance of LTPLE_Inbox.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Constructor function.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	
	var $slug;
	var $tab;
	var $cid;
	var $messages = null;
	
	private $users = array();
	 
	public function __construct ( $file='', $parent, $version = '1.0.0' ) {

		$this->parent = $parent;
	
		$this->_version = $version;
		$this->_token	= md5($file);
		
		$this->message = '';
		
		// Load plugin environment variables
		
		$this->file 		= $file;
		$this->dir 			= dirname( $this->file );
		$this->views   		= trailingslashit( $this->dir ) . 'views';
		$this->vendor  		= WP_CONTENT_DIR . '/vendor';
		$this->assets_dir 	= trailingslashit( $this->dir ) . 'assets';
		$this->assets_url 	= esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );
		
		//$this->script_suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$this->script_suffix = '';

		register_activation_hook( $this->file, array( $this, 'install' ) );
		
		// Load frontend JS & CSS
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Load admin JS & CSS
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ), 10, 1 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_styles' ), 10, 1 );
		
		$this->settings = new LTPLE_Inbox_Settings( $this->parent );
		
		$this->admin = new LTPLE_Inbox_Admin_API( $this );

		if ( !is_admin() ) {

			// Load API for generic admin functions
			
			add_action( 'wp_head', array( $this, 'header') );
			add_action( 'wp_footer', array( $this, 'footer') );
		}
		
		// Handle localisation
		
		$this->load_plugin_textdomain();
		
		add_action( 'init', array( $this, 'load_localisation' ), 0 );
		
		//init addon 
		
		add_action( 'wp_loaded', array( $this, 'init' ));	
	
		// send message from profile page
	
		add_action( 'template_redirect', array( $this, 'handle_send_message' ),9998);	

		// Custom template path
		
		add_filter( 'template_include', array( $this, 'template_path'), 1 );
		
		// add user attributes
		
		add_filter( 'ltple_user_loaded', array( $this, 'add_user_attribute'));			

		// hangle user logs
		
		add_filter( 'ltple_first_log_ever', array( $this, 'handle_first_log_ever'));			
		
		add_filter( 'ltple_first_log_today', array( $this, 'handle_first_log_today'));
		
		// add query vars
		
		add_filter('query_vars', array( $this, 'add_query_vars'), 1);

		// add panel url
		
		add_filter( 'ltple_urls', array( $this, 'get_panel_url'));
		
		// add url parameters
		
		add_filter( 'template_redirect', array( $this, 'get_url_parameters'));		
			
		// add privacy settings
				
		add_filter('ltple_privacy_settings',array($this,'set_privacy_fields'));
		
		// add panel shortocode
		
		add_shortcode('ltple-client-inbox', array( $this , 'get_panel_shortcode' ) );
			
		// add notification settings
		
		add_filter( 'ltple_notification_settings', array( $this, 'get_notification_settings'));
		
		// add notification event
		
		add_action( 'ltple_inbox_notification_event', array( $this, 'send_recipient_notification'),1,3);
		
		// add link to theme menu
		
		add_filter( 'ltple_view_my_profile', array( $this, 'add_theme_menu_link'));	
				
		// add button to navbar
				
		add_filter( 'ltple_left_navbar', array( $this, 'add_left_navbar_button'));	
		add_filter( 'ltple_right_navbar', array( $this, 'add_right_navbar_button'));	
						
		// add profile tabs		

		add_filter( 'ltple_profile_tabs', array( $this, 'add_profile_tabs'));	
						
		// add layer fields

		add_filter( 'ltple_layer_options', array( $this, 'add_layer_options'),10,1);
		add_filter( 'ltple_layer_plan_fields', array( $this, 'add_layer_plan_fields'),10,2);
		add_action( 'ltple_save_layer_fields', array( $this, 'save_layer_fields' ),10,1);			
					
		// add layer colums
		
		add_filter( 'ltple_layer_type_columns', array( $this, 'add_layer_columns'));
		add_filter( 'ltple_layer_range_columns', array( $this, 'add_layer_columns'));
		add_filter( 'ltple_layer_option_columns', array( $this, 'add_layer_columns'));
							
		add_filter( 'ltple_layer_column_content', array( $this, 'add_layer_column_content'),10,2);
		
		// handle plan
		
		add_filter( 'ltple_api_layer_plan_option', array( $this, 'add_api_layer_plan_option'),10,1);	
		add_filter( 'ltple_api_layer_plan_option_total', array( $this, 'add_api_layer_plan_option_total'),10,2);
		
		add_filter( 'ltple_plan_shortcode_attributes', array( $this, 'add_plan_shortcode_attributes'),10,2);
		add_filter( 'ltple_plan_subscribed', array( $this, 'handle_subscription_plan'),10);
		
		add_filter( 'ltple_user_plan_option_total', array( $this, 'add_user_plan_option_total'),10,2);
		add_filter( 'ltple_user_plan_info', array( $this, 'add_user_plan_info'),10,1);
		
		// addon post types
		
		$this->parent->register_post_type( 'user-message', __( 'User Message', 'live-template-editor-inbox' ), __( 'User Messages', 'live-template-editor-inbox' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu' 			=> 'user-message',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> true,
			'show_in_rest' 			=> false,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail' ),
			'supports' 				=> array('editor', 'author', 'thumbnail', 'page-attributes'),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));
		
		$this->parent->register_post_type( 'inbox-notification', __( 'Inbox Notification', 'live-template-editor-inbox' ), __( 'Inbox Notifications', 'live-template-editor-inbox' ), '', array(

			'public' 				=> false,
			'publicly_queryable' 	=> false,
			'exclude_from_search' 	=> true,
			'show_ui' 				=> true,
			'show_in_menu'		 	=> 'email-invitation',
			'show_in_nav_menus' 	=> false,
			'query_var' 			=> true,
			'can_export' 			=> true,
			'rewrite' 				=> false,
			'capability_type' 		=> 'post',
			'has_archive' 			=> false,
			'hierarchical' 			=> false,
			'show_in_rest' 			=> true,
			//'supports' 			=> array( 'title', 'editor', 'author', 'excerpt', 'comments', 'thumbnail','page-attributes' ),
			'supports' 				=> array( 'title', 'editor', 'author' ),
			'menu_position' 		=> 5,
			'menu_icon' 			=> 'dashicons-admin-post',
		));	
	
	} // End __construct ()
	
	public function template_path( $template_path ){
		
		return $template_path;
	}
	
	public function init(){	 
		
		register_taxonomy_for_object_type( 'user-contact', 'user-message' );
	}
	
	public function get_input_key(){
		
		$key = md5($this->parent->profile->id) . '_';
		
		return $key; 
	}
	
	public function set_privacy_fields(){
		
		$this->parent->profile->privacySettings['contact-me'] = array(

			'id' 			=> $this->parent->_base . 'policy_' . 'contact-me',
			'label'			=> 'Contact Me',
			'description'	=> 'Anyone can contact me via My Profile page',
			'type'			=> 'switch',
			'default'		=> 'on',
		);
	}
	
	public function handle_send_message(){	 
		
		if( $this->parent->profile->id > 0 && $this->parent->profile->tab == 'contact-me' ){
			
			$key = $this->get_input_key();
			
			if( !empty($_POST[$key.'message']) && !empty($_POST[$key.'token']) ){
				
				if( $to_user = get_user_by( 'id', $this->parent->profile->id ) ){
					
					// get recipient email
					
					$to_email 	= $to_user->user_email;
					
					// get sender message
					
					$message = $_POST[$key.'message'];
					
					// get send time
					
					$time = intval($this->parent->ltple_decrypt_str($_POST[$key.'token']));
					
					if( !empty($time) && $time < time() ){
						
						// get sender email
						
						$from_email = $from_nickname = '';
						
						$valid_request = false;
						
						if( $this->parent->user->loggedin ){
							
							$from_email 	= $this->parent->user->user_email;
							$from_nickname 	= $this->parent->user->nickname;

							$valid_request = true;
						}
						elseif( !empty($_POST[$key.'email']) && !empty($_POST[$key.'nickname']) ){
						
							$from_email 	= sanitize_email($_POST[$key.'email']);
							$from_nickname 	= $_POST[$key.'nickname'];
							
							if( email_exists($from_email) ){
								
								$this->parent->message .= '<div class="alert alert-warning">This email is already registered, please <a href="' . wp_login_url($this->parent->urls->current) . '">login</a></div>';
							}
							else{
								
								$valid_request = true;
							}
						}
						
						if($valid_request){

							if( !empty($from_email) && !empty($from_nickname) ){
								
								$status = 'publish';
								
								// get from user
								
								if( !$from_user = get_user_by('email',$from_email) ){
									
									// handle new user
									
									$status = 'pending';
									
									if( $new_user = $this->parent->email->insert_user($from_email,false) ){
										
										if( $from_user = get_user_by('email',$new_user['email']) ){
										
											$this->parent->update_user_channel($from_user->ID,'User Profile');
											
											// send new user notification
								
											wp_new_user_notification( $from_user->ID, NULL, 'user' );
										}
										else{
											
											$this->parent->message .= '<div class="alert alert-danger">Error retrieving new user account...</div>';
										}
									}
									else{
										
										$this->parent->message .= '<div class="alert alert-danger">Error inserting a new user...</div>';
									}
								}
								
								if( !empty($from_user->ID) ){
									
									// get contact ids
									
									$emails = array($from_email,$to_email);
									
									if( $contact_ids = $this->get_user_contact_ids($emails) ){
										
										//get parent conversation
										
										$conversation = $this->start_conversation($contact_ids,$status);
										
										if( !empty($conversation['id']) ){
										
											$post_title = md5(json_encode($contact_ids).$message.$time);
											
											require_once( ABSPATH . 'wp-admin/includes/post.php' );
										
											if( !$post_id = post_exists( $post_title )){
											
												if( $post_id = wp_insert_post(array(
												
													'post_title' 	=> $post_title,
													'post_type' 	=> 'user-message',
													'post_content' 	=> $message,
													'post_status' 	=> 'publish',
													'post_parent' 	=> $conversation['id'],
													'post_author' 	=> $from_user->ID,
													
												))){
													
													// set user contacts
													
													wp_set_post_terms( $post_id, $contact_ids, 'user-contact', false );
													
													// mark sender conversation
													
													$this->mark_conversation_as_read($conversation['id'],$from_user->ID);
													
													// mark recipient conversation
													
													$this->mark_conversation_as_unread($conversation['id'],$to_user->ID);
																																					
													if( $status == 'pending' ){
														
														// output message
													
														$this->parent->message .= '<div class="alert alert-success">Your message will be transmitted to '.ucfirst($to_user->nickname).' after confirmation of your email. Please check your inbox.</div>';														
													}
													else{
													
														// notify recipient

														$this->schedule_recipient_notification($from_user,$to_user,$conversation['id'],$message);
													
														// output message
													
														$this->parent->message .= '<div class="alert alert-success">Your message was transmitted to '.ucfirst($to_user->nickname).' thanks!</div>';
													}
												}
												else{
													
													$this->parent->message .= '<div class="alert alert-danger">Error creating new message...</div>';
												}
											}
											else{
												
												//$this->parent->message .= '<div class="alert alert-warning">This message was already sent</div>';
											}
										}
										else{
											
											$this->parent->message .= '<div class="alert alert-danger">Error retrieving the conversation...</div>';
										}
									}
									else{
										
										$this->parent->message .= '<div class="alert alert-danger">Error getting the contact ids...</div>';
									}
								}
								else{
									
									$this->parent->message .= '<div class="alert alert-danger">Error retrieving sender information...</div>';
								}
							}
							else{
								
								$this->parent->message .=  '<div class="alert alert-danger">Sender email not valid...</div>';
							}
						}
					}
					else{
						
						$this->parent->message .=  '<div class="alert alert-danger">Wrong token, please refresh the page...</div>';
					}
				}
				else{
					
					$this->parent->message .=  '<div class="alert alert-danger">This recipient doesn\'t exists...</div>';
				}
			}
		}
	}
	
	public function handle_send_reply(){
		
		if( $this->parent->user->loggedin && $this->cid > 0 && $this->tab == 'messages' ){
			
			$key = $this->get_input_key();
			
			if( !empty($_POST[$key.'message']) && !empty($_POST[$key.'token']) ){
				
				// get sender message
				
				$message = $_POST[$key.'message'];
				
				// get send time
				
				$time = intval($this->parent->ltple_decrypt_str($_POST[$key.'token']));
				
				if( !empty($time) && $time < time() ){
					
					$members = $this->get_conversation_members( $this->cid );
					
					// get contact ids
					
					$emails = array_keys($members);
					
					$contact_ids = $this->get_user_contact_ids($emails);
	
					// get post title
								
					$post_title = md5(json_encode($contact_ids).$message.$time);
					
					require_once( ABSPATH . 'wp-admin/includes/post.php' );
				
					if( !$post_id = post_exists( $post_title )){
					
						if( $post_id = wp_insert_post(array(
						
							'post_title' 	=> $post_title,
							'post_type' 	=> 'user-message',
							'post_content' 	=> $message,
							'post_status' 	=> 'publish',
							'post_parent' 	=> $this->cid,
							'post_author' 	=> $this->parent->user->ID,
							
						))){

							// set user contacts
												
							wp_set_post_terms( $post_id, $contact_ids, 'user-contact', false );																		
						
							// notify recipients
							
							foreach( $members as $member ){
								
								if( $this->parent->user->ID != $member->ID ){
									
									$this->mark_conversation_as_unread($this->cid,$member->ID);
								
									$this->schedule_recipient_notification($this->parent->user,$member,$this->cid,$message);
								}
							}
						}
						else{
							
							$this->parent->message .= '<div class="alert alert-danger">Error creating new message...</div>';
						}
					}
					else{
						
						//$this->parent->message .= '<div class="alert alert-warning">This message was already sent</div>';
					}
				}
			}
		}
	}
	
	public function get_user_contact_ids($emails){
		
		$term_ids = array();

		foreach( $emails as $email ){
			
			if( !$term = get_term_by('name',$email,'user-contact') ){

				$term = wp_insert_term( $email, 'user-contact' );
			}
			
			if( is_array($term) && !empty($term['term_id']) ){
				
				$term_ids[] = intval($term['term_id']);
			}
			elseif( is_object($term) && !empty($term->term_id) ){
				
				$term_ids[] = intval($term->term_id);
			}
			else{
				
				return false;
			}
		}
		
		return $term_ids;
	}

	public function start_conversation($contact_ids,$status='publish'){
		
		sort($contact_ids);
		
		$md5 = md5(json_encode($contact_ids));
		
		$conversation = array(
		
			'id' 			=> 0,
			'md5' 			=> $md5,
			'members'		=> $contact_ids,
			'messages'		=> array(),
		);
							
		if( $parent = get_posts( array(
		
			'post_type' 		=> 'user-message',
			'title'		 		=> $md5,
			'post_status' 		=> 'publish',
			'numberposts' 		=> 1,
			'post_parent' 		=> 0,
			'tax_query' 		=> array(
			
				array(
				
				  'taxonomy' 	=> 'user-contact',
				  'field' 		=> 'id',
				  'terms' 		=> $contact_ids
				)
			)
		))){
			
			$conversation['id'] = $parent[0]->ID;
		}
		else{
			
			$conversation['id'] = wp_insert_post(array(
								
				'post_title' 	=> $md5,
				'post_type' 	=> 'user-message',
				'post_status' 	=> $status,
				'post_content' 	=> '',
				'post_author' 	=> 0,
			));
			
			wp_set_post_terms( $conversation['id'], $contact_ids, 'user-contact', false );
		}

		return $conversation;
	}
	
	public function send_recipient_notification($conversation_id,$from_user_id,$to_user_id){
		
		$from_user = get_user_by('id',$from_user_id);
		
		$to_user = get_user_by('id',$to_user_id);
		
		if( !empty($to_user->user_email) && !empty($from_user->user_email) && $to_user->user_email != $from_user->user_email ){
			
			$notification_settings = $this->parent->users->get_user_notification_settings($to_user->ID);
			
			if( !empty($notification_settings['inbox']) && $notification_settings['inbox'] === 'true' ){
				
				if( $this->is_conversation_unread($conversation_id,$to_user) && !$this->is_notification_sent($conversation_id,$to_user->ID) ){
					
					$company = ucfirst(get_bloginfo('name'));
					
					$from_nickname 	= ucfirst($from_user->nickname);
					$to_nickname 	= ucfirst($to_user->nickname);
					
					$Email_title 	= 'A message from ' . $from_nickname . ' is waiting for you on '.$company.'!';
								
					$sender_email 	= 'please-reply@'.$domain;
					
					$message 		= $this->get_recipient_notification($from_nickname,$conversation_id);
					$message	 	= $this->parent->email->do_shortcodes($message, $to_user);
					
					$headers   = [];
					$headers[] = 'From: ' . $company . ' <'.$sender_email.'>';
					//$headers[] = 'MIME-Version: 1.0';
					$headers[] = 'Content-type: text/html';
					
					$preMessage = "<html><body><div style='width:100%;padding:5px;margin:auto;font-size:14px;line-height:18px'>" . apply_filters('the_content', $message) . "<div style='clear:both'></div>".$this->parent->email->get_footer($to_user,'inbox')."<div style='clear:both'></div></div></body></html>";
					
					if(!wp_mail($to_user->user_email, $Email_title, $preMessage, $headers)){
						
						global $phpmailer;
						
						wp_mail($this->parent->settings->options->emailSupport, 'Error sending inbox notification to ' . $to_user->user_email, print_r($phpmailer->ErrorInfo,true));			
					}
					else{
						
						// mark notification as sent
						
						$this->mark_notification_as_sent($conversation_id,$to_user->ID);

						return true;
					}
				}
			}
		}
		
		return false;
	}

	public function schedule_recipient_notification( $from_user, $to_user, $conversation_id, $content ){
		
		if( !empty($to_user->user_email) && !empty($from_user->user_email) && !empty($content) && $to_user->user_email != $from_user->user_email ){
			
			$notification_settings = $this->parent->users->get_user_notification_settings($to_user->ID);
			
			if( !empty($notification_settings['inbox']) && $notification_settings['inbox'] === 'true' && !$this->is_notification_sent($conversation_id,$to_user->ID) ){
				
				$m = 5; // in 5 minutes from now
				
				$args = [$conversation_id,$from_user->ID,$to_user->ID];
				
				wp_schedule_single_event( ( time() + ( 60 * $m ) ), 'ltple_inbox_notification_event' , $args );								

				// update email sent
				
				//wp_mail($this->parent->settings->options->emailSupport, 'New inbox message from ' . $from_user->nickname . ' to ' . $to_user->nickname , $content);
								
				return true;
			}
		}
		
		return false;
	}
	
	public function get_recipient_notification($from_nickname,$conversation_id){
		
		$inbox_url = $this->parent->urls->inbox . 'messages/' . $conversation_id . '/';
		
		$company = ucfirst(get_bloginfo('name'));
		
		$notification = '<table style="width: 100%; max-width: 100%; min-width: 320px; background-color: #f1f1f1;margin:0;padding:40px 0 45px 0;margin:0 auto;text-align:center;border:0;">';
					
			$notification .= '<tr>';
				
				$notification .= '<td>';
					
					$notification .= '<table style="width: 100%; max-width: 600px; min-width: 320px; background-color: #FFFFFF;border-radius:5px 5px 0 0;-moz-border-radius:5px 5px 0 0;-ms-border-radius:5px 5px 0 0;-o-border-radius:5px 5px 0 0;-webkit-border-radius:5px 5px 0 0;text-align:center;border:0;margin:0 auto;font-family: Arial, sans-serif;">';
						
						$notification .= '<tr>';
							
							$notification .= '<td style="font-family: Arial, sans-serif;padding:15px 0;line-height:30px;font-size:19px;color:#888888;font-weight:bold;border-bottom:1px solid #cccccc;text-align:center;background-color:#FFFFFF;">';
								
								$notification .= 'You have received a message from ' . ucfirst($from_nickname) . ' on ' . $company;
								
							$notification .= '</td>';
						
						$notification .= '</tr>';
						
						$notification .= '<tr>';	

							$notification .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:20px;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
								
								$notification .= 'Hello *|NAME|*,' . PHP_EOL . PHP_EOL;
								
								$notification .= 'A message from ' . ucfirst($from_nickname) . ' is waiting for you in your '.$company.' inbox!' . PHP_EOL . PHP_EOL;
								
							$notification .=  '</td>';
										
						$notification .= '</tr>';
						
						/*
						if( !empty($excerpt) ){
						
							$notification .= '<tr>';	

								$notification .= '<td style="line-height: 25px;font-family: Arial, sans-serif;padding:10px 20px ;font-size:15px;color:#666666;text-align:left;font-weight: normal;border:0;background-color:#FFFFFF;">';
																						
									$notification .= 'Preview' . PHP_EOL;
										
								$notification .=  '</td>';
										
							$notification .= '</tr>';

							$notification .= '<tr>';													
										
								$notification .= '<td style="background: rgb(248, 248, 248);display:block;padding:20px;margin:20px;text-align:left;border-left: 5px solid #888;">';
										
									$notification .= $excerpt;
								
								$notification .=  '</td>';
										
							$notification .= '</tr>';														
						}
						*/

						$notification .= '<tr>';	

							$notification .= '<td style="font-family: Arial, sans-serif;height:150px;font-size:16px;color:#666666;text-align:center;border:0;background-color:#FFFFFF;">';
																							
								$notification .=  '<a style="background: ' . $this->parent->settings->mainColor . ';color: #fff;padding: 17px;text-decoration: none;border-radius: 5px;font-weight: bold;font-size: 20px;" href="'.$inbox_url.'">Let\'s read it! </a>' . PHP_EOL . PHP_EOL;

							$notification .=  '</td>';
							
						$notification .=  '</tr>';
						
					$notification .=  '</table>';
					
				$notification .=  '<td>';
			$notification .=  '<tr>';
		$notification .=  '</table>';
		
		$notification = str_replace(PHP_EOL,'<br/>',$notification);
		
		return $notification;
	}
	
	public function header(){
		
		//echo '<link rel="stylesheet" href="https://raw.githubusercontent.com/dbtek/bootstrap-vertical-tabs/master/bootstrap.vertical-tabs.css">';	
	}
	
	public function footer(){
		
		
	}
	
	public function add_user_attribute(){
		
		// add user attribute
			
		//$this->parent->user->userAttribute = new LTPLE_Inbox_User( $this->parent );	
	}
	
	public function handle_first_log_ever(){
		
		$from_user = $this->parent->user;
		
		$conversations = $this->get_user_conversations($from_user);
		
		foreach( $conversations as $conversation ){
			
			wp_update_post( array(
			
				'ID'    		=>  $conversation['id'],
				'post_status'   =>  'publish'
			));
			
			// notify recipients
			
			foreach( $conversation['members'] as $to_user ){
				
				$this->schedule_recipient_notification( $from_user, $to_user, $conversation['id'], 'A new user started a conversation' );		
			}
		}
	}
	
	public function handle_first_log_today(){
		

	}
	
	public function get_panel_shortcode(){
		
		if($this->parent->user->loggedin){
			
			$this->handle_send_reply();
			
			$this->set_conversation_messages();
			
			if( !empty($_REQUEST['output']) && $_REQUEST['output'] == 'widget' ){
				
				include($this->views . '/widget.php');
			}
			else{
			
				include($this->parent->views . '/navbar.php');
			
				include($this->views . '/panel.php');
			}
		}
		else{
			
			echo'<div style="font-size:20px;padding:20px;margin:0;" class="alert alert-warning">';
				
				echo'You need to log in first...';
				
				echo'<div class="pull-right">';

					echo'<a style="margin:0 2px;" class="btn-lg btn-success" href="'. wp_login_url( $this->parent->request->proto . $_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'] ) .'">Login</a>';
					
					echo'<a style="margin:0 2px;" class="btn-lg btn-info" href="'. wp_login_url( $this->parent->urls->editor ) .'&action=register">Register</a>';
				
				echo'</div>';
				
			echo'</div>';
		}				
	}
	 
	public function get_conversation_messages($conversation_id,$mark_as_read=false){
		
		$messages = array();
		
		if( $messages = get_posts(array(
				
			'post_type' 	=> 'user-message',
			'post_status' 	=> 'publish',
			'numberposts' 	=> -1,
			'post_parent' 	=> $conversation_id,
			'orderby'       => 'date',
			'order'         => 'DESC',
		))){
			
			$members = $this->get_conversation_members($conversation_id);
			
			foreach( $messages as $message ){
				
				// get user
				
				foreach( $members as $member ){
					
					if( $member->ID == $message->post_author ){
						
						$message->user = $member;

						break;
					}
				}
				
				// parse content
				
				$message->post_content = $this->parse_message_content($message->post_content);
				
				// get date
						
				$message->post_date = human_time_diff( strtotime($message->post_date), current_time( 'timestamp',1 ) ) . ' ago';	
				
				// mark last as read
				
				if( $mark_as_read == true ){
					
					$this->mark_conversation_as_read($conversation_id,$this->parent->user->ID);
					
					$mark_as_read = false;
				}		
			}
			
			$messages = array_reverse($messages,true);
		}
		
		return $messages;		
	}
	
	public function parse_message_content($content){
				
		$content = strip_tags($content);
		
		$content = preg_replace('/((http|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?)/', '<a target="_blank" href="\1">\1</a>', $content);
		
		return $content;
	}
	
	public function set_conversation_messages(){
		
		if( $this->cid > 0 ){
			
			$this->messages = $this->get_conversation_messages($this->cid,true);		
		}
	}
	
	public function mark_conversation_as_read($conversation_id,$member_id){
		
		// mark notification as unsent
		
		$this->mark_notification_as_unsent($conversation_id,$member_id);
		
		// mark conversation as read
		
		return update_post_meta($conversation_id,'unread_' . $member_id,'false');
	}
	
	public function mark_conversation_as_unread($conversation_id,$member_id){
		
		// mark conversation as unread
		
		return update_post_meta($conversation_id,'unread_' . $member_id,'true');
	}
	
	public function mark_notification_as_sent($conversation_id,$member_id){
		
		return update_post_meta($conversation_id,'notified_' . $member_id,'true');
	}
	
	public function mark_notification_as_unsent($conversation_id,$member_id){

		return update_post_meta($conversation_id,'notified_' . $member_id,'false');
	}
	
	public function get_user_conversations( $user = null ){
						
		if( is_null($user) && !empty($this->parent->user) ){
			
			$user = $this->parent->user;
		}
		
		$conversations = array();
		
		if( !empty($user->user_email) ){		
			
			if( $contact_ids = $this->get_user_contact_ids(array($user->user_email)) ){

				if( $items = get_posts(array(
				
					'post_type' 		=> 'user-message',
					'post_status' 		=> array('publish','pending'),
					'numberposts' 		=> -1,
					'post_parent' 		=> 0,
					'tax_query' 		=> array(
					
						array(
						
						  'taxonomy' 	=> 'user-contact',
						  'field' 		=> 'id',
						  'terms' 		=> $contact_ids
						)
					),				
				))){

					foreach( $items as $conversation ){
						
						// get members
						
						if( $members = $this->get_conversation_members($conversation->ID) ){
							
							$count = count($members);
							
							if( $count > 1 ){
									
								// get names
								
								$names 		= array();
								$avatars	= array();
								
								foreach( $members as $email => $member ){
									
									if( $email != $this->parent->user->user_email ){
										
										$names[] 	 = ucfirst($member->nickname);
										$avatars[]	 = $this->parent->image->get_avatar_url($member->ID);
									}
								}
								
								$names = implode(', ',$names);
							}
							else{
								
								// get names
								
								$key = key($members);
								
								$names 		= 'Me';
								$avatars	= array($this->parent->image->get_avatar_url($members[$key]->ID));
							}
							
							// get last message
							
							$last = $this->get_conversation_last_message($conversation->ID,$user);							
							
							$conversations[] = array(
								
								'id' 			=> $conversation->ID,
								'status' 		=> $conversation->post_status,
								'names' 		=> $names,
								'avatars' 		=> $avatars,
								'badge' 		=> '',
								'in_favourite' 	=> false,
								'last_excerpt' 	=> $last->post_excerpt,
								'last_time' 	=> $last->post_date,
								'last_has_att' 	=> $last->has_att,
								'last_unread' 	=> $last->unread,
								'members' 		=> $members,
							);							
						}
					}
				}
			}
		}
								
		return $conversations;
	}

	public function count_unread_conversations( $user = null ){
		
		$unread_count = 0;
		
		if( $conversations = get_posts( array(
		
			'post_type' 		=> 'user-message',
			'post_status' 		=> 'publish',
			'numberposts' 		=> -1,
			'post_parent' 		=> 0,
			'tax_query' 		=> array(
			
				array(
				
				  'taxonomy' 	=> 'user-contact',
				  'field' 		=> 'name',
				  'terms' 		=> array($user->user_email)
				)
			),
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key'     	=> 'unread_'.$user->ID,
					'value'   	=> 'true',
					'compare' 	=> '=',
				),
				array(
					'key'     	=> 'unread_'.$user->ID,
					'compare' 	=> 'NOT EXISTS'
				),
			)
		))){
			
			$unread_count = count($conversations);
		}

		return $unread_count;
	}
			
	public function get_conversation_members( $conversation_id ){
		
		$members = array();
		
		if( $terms = wp_get_post_terms($conversation_id,'user-contact') ){
			
			foreach( $terms as  $term ){
				
				$email = $term->name;
				
				if( empty( $this->users[$email]) ){
				
					$user = get_user_by('email',$email);
					
					$user->avatar = $this->parent->image->get_avatar_url($user->ID);
					
					$this->users[$email] = $user;
				}
				
				$members[$email] = $this->users[$email];
			}
		}

		return $members;
	}
	
	public function get_conversation_last_message( $conversation_id, $member ){
		
		if( $message = get_posts(array(
				
			'post_type' 	=> 'user-message',
			'post_status' 	=> 'publish',
			'numberposts' 	=> 1,
			'post_parent' 	=> $conversation_id,
			'orderby'       => 'date',
			'order'         => 'DESC',
		))){
			
			$message = $message[0];
			
			// get excerpt
			
			if( empty($message->post_excerpt) ){
				
				$excerpt 		= strip_shortcodes( $message->post_content );
				$excerpt 		= apply_filters( 'the_content', $excerpt );
				$excerpt 		= str_replace(']]>', ']]&gt;', $excerpt);
				
				$excerpt_length = apply_filters( 'excerpt_length', 55 );
				$excerpt_more 	= apply_filters( 'excerpt_more', ' ' . '[&hellip;]' );
				
				$excerpt 		= wp_trim_words( $excerpt, $excerpt_length, $excerpt_more );
				
				$message->post_excerpt = $excerpt;
			}
			
			// get date
			
			$message->post_date = human_time_diff( strtotime($message->post_date), current_time( 'timestamp',1 ) ) . ' ago';
		
			// has attachment
			
			$message->has_att = false;
			
			// is unread

			$message->unread = $this->is_conversation_unread($conversation_id,$member);
		}
		
		return $message;
	}
	
	public function is_conversation_unread($conversation_id,$by_member){
		
		$unread = true;
		
		if( $member_unread = get_post_meta($conversation_id,'unread_'.$by_member->ID,true) ){
			
			if( $member_unread == 'false' ){
				
				$unread = false;
			}			
		}
		
		return 	$unread;
	}
	
	public function is_notification_sent($conversation_id,$member_id){
		
		$sent = false;
		
		if( $member_notified = get_post_meta($conversation_id,'notified_'.$member_id,true) ){
			
			if( $member_notified == 'true' ){
				
				$sent = true;
			}			
		}
		
		return 	$sent;
	}
			
	public function get_badge_title( $badge = 'blue' ){
		
		$badges = array(
		
			'blue' 	=> 'Family',
			'amber' => 'Shop',
			'red' 	=> 'Google',
			'blush' => 'Themeforest',
			'green' => 'Work',
		);
		
		return $badges[$badge];
	}

	public function add_query_vars( $query_vars ){
		
		if(!in_array('tab',$query_vars)){
		
			$query_vars[] = 'tab';
		}
		
		if(!in_array('cid',$query_vars)){
		
			$query_vars[] = 'cid';
		}
		
		return $query_vars;	
	}
	
	public function get_panel_url(){
		
		$this->slug = get_option( $this->parent->_base . 'inboxSlug' );
		
		if( empty( $this->slug ) ){
			
			$post_id = wp_insert_post( array(
			
				'post_title' 		=> 'Inbox',
				'post_type'     	=> 'page',
				'comment_status' 	=> 'closed',
				'ping_status' 		=> 'closed',
				'post_content' 		=> '[ltple-client-inbox]',
				'post_status' 		=> 'publish',
				'menu_order' 		=> 0
			));
			
			$this->slug = update_option( $this->parent->_base . 'inboxSlug', get_post($post_id)->post_name );
		}
		
		$this->parent->urls->inbox = $this->parent->urls->home . '/' . $this->slug . '/';	
	
		// add rewrite rules

		add_rewrite_rule(
		
			$this->slug . '/([^/]+)/?$',
			'index.php?pagename=' . $this->slug . '&tab=$matches[1]',
			'top'
		);
		
		add_rewrite_rule(
		
			$this->slug . '/([^/]+)/([0-9]+)/?$',
			'index.php?pagename=' . $this->slug . '&tab=$matches[1]&cid=$matches[2]',
			'top'
		);
	}
	
	public function get_url_parameters(){

		// get tab name

		if( !$this->tab = get_query_var('tab') ){
			
			$this->tab = 'messages';
		}

		if( $this->tab == 'messages' ){
			
			// get conversation id
			
			$this->cid = intval(get_query_var('cid'));
		}
	}
	
	public function get_notification_settings(){

		$this->parent->email->notification_settings['inbox'] = array(
		
			'default' 		=> 'true',
			'description' 	=> 'Receive an email when a new message is waiting in your Inbox',
		);
	}
	
	public function add_theme_menu_link(){

		// add theme menu link
		
		/*
		echo'<li style="position:relative;">';
			
			echo '<a href="'. $this->parent->urls->addon . '"><span class="glyphicon glyphicon-link" aria-hidden="true"></span> Addon Panel</a>';

		echo'</li>';
		*/
	}
	
	public function add_left_navbar_button(){
		
		$unread_count = $this->count_unread_conversations($this->parent->user);
		
		echo'<div class="pull-left">';

			echo'<a style="margin-left:6px;background:#FFF;border:1px solid ' . $this->parent->settings->mainColor . ';color:' . $this->parent->settings->mainColor . ';" class="btn btn-sm" href="' . $this->parent->urls->inbox . '" role="button" data-html="true" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-title="Inbox" data-content="The inbox allows you to receive and send private messages, a good way to keep in touch with your network.">';
				
				echo'Inbox';
				
				if( $unread_count > 0 ){
				
					echo'<span class="badge" style="font-size: 10px;margin: 0px 0px 0px 2px;background: ' . $this->parent->settings->mainColor . ';padding: 1px 4px;line-height: 10px;border-radius: 250px;">'.$unread_count.'</span>';
				}
			
			echo'</a>';
		
		echo'</div>';			
	}
	
	public function add_right_navbar_button(){
		
		
		
	}
	
	public function add_profile_tabs(){
		
		$policy = get_user_meta( $this->parent->profile->user->ID, $this->parent->_base . 'policy_contact-me', true );
		
		if( $policy != 'off' ){
		
			// get input key
			
			$key = $this->get_input_key();
			
			// get tab position
			
			$this->parent->profile->tabs['contact-me']['position'] = 3;
			
			// get tab name
			
			$this->parent->profile->tabs['contact-me']['name'] 		= 'Contact Me';
			
			// get contact form
			
			$this->parent->profile->tabs['contact-me']['content'] 	= '<div class="well" style="display:inline-block;width:100%;margin-top:10px;">';
				
				$this->parent->profile->tabs['contact-me']['content'] 	.= '<form action="' . $this->parent->urls->current . '" method="post">';
					
					if( !$this->parent->user->loggedin ){
					
						// email address
					
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<label>Email Address</label>';
						
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<input style="max-width:250px;" type="email" class="form-control" id="email" name="'.$key.'email" placeholder="my-email@gmail.com" required>';
						
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<div style="font-size: 11px;font-style: italic;margin-bottom: 10px;">Your address will remain secret</div>';
						
						// email nickname
						
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<label>Nickname</label>';
						
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<input style="max-width:250px;" type="text" class="form-control" id="nickname" name="'.$key.'nickname" placeholder="My Nickname" required>';
						
						$this->parent->profile->tabs['contact-me']['content'] 	.= '<div style="font-size: 11px;font-style: italic;margin-bottom: 10px;">Your Nickname will be seen by ' . ucfirst($this->parent->profile->user->nickname) . '</div>';
					}
					
					// message
					
					$this->parent->profile->tabs['contact-me']['content'] 	.= '<label>Message</label>';
					
					$this->parent->profile->tabs['contact-me']['content'] 	.= '<textarea style="height:150px;" class="form-control" name="'.$key.'message" placeholder="My message" required></textarea>';

					$this->parent->profile->tabs['contact-me']['content'] 	.= '<div style="font-size: 11px;font-style: italic;margin-bottom: 10px;">Your first message will be validated by ' . ucfirst($this->parent->profile->user->nickname) . ' and the next ones will be added to the inbox conversation</div>';
					
					// time token
					
					$this->parent->profile->tabs['contact-me']['content'] 	.= '<input type="hidden" name="'.$key.'token" value="'.$this->parent->ltple_encrypt_str(time()).'">';
					
					// button
					
					$this->parent->profile->tabs['contact-me']['content'] 	.= '<button id="contactBtn" class="pull-right btn btn-primary">Send</button>';
					
				$this->parent->profile->tabs['contact-me']['content'] 	.= '</form>';
				
			$this->parent->profile->tabs['contact-me']['content'] 	.= '</div>';
		}
	}
	
	public function add_layer_options($term_slug){
		
		/*
		
		if(!$addon_amount = get_option('addon_amount_' . $term_slug)){
			
			$addon_amount = 0;
		}

		$this->parent->layer->options = array(
			
			'addon_amount' 	=> $addon_amount,
		);
		*/
	}
	
	public function add_layer_plan_fields( $taxonomy, $term_slug = '' ){
		
		/*
		
		$data = [];

		if( !empty($term_slug) ){
		
			$data['addon_amount'] = get_option('addon_amount_' . $term_slug); 
			$data['addon_period'] = get_option('addon_period_' . $term_slug); 
		}

		echo'<div class="form-field" style="margin-bottom:15px;">';
			
			echo'<label for="'.$taxonomy.'-addon-amount">Addon plan attribute</label>';

			echo $this->get_layer_addon_fields($taxonomy,$data);
			
		echo'</div>';
		
		*/
	}
	
	public function get_layer_addon_fields( $taxonomy_name, $args = [] ){
		
		/*
		
		//get periods
		
		$periods = $this->parent->plan->get_price_periods();
		
		//get price_amount
		
		$amount = 0;
		
		if(isset($args['addon_amount'])){
			
			$amount = $args['addon_amount'];
		}

		//get period
		
		$period = '';
		
		if(isset($args['addon_period'])&&is_string($args['addon_period'])){
			
			$period = $args['addon_period'];
		}
		
		//get fields
		
		$fields='';

		$fields.='<div class="input-group">';

			$fields.='<span class="input-group-addon" style="color: #fff;padding: 5px 10px;background: #9E9E9E;">$</span>';
			
			$fields.='<input type="number" step="0.1" min="-1000" max="1000" placeholder="0" name="'.$taxonomy_name.'-addon-amount" id="'.$taxonomy_name.'-addon-amount" style="width: 60px;" value="'.$amount.'"/>';
			
			$fields.='<span> / </span>';
			
			$fields.='<select name="'.$taxonomy_name.'-addon-period" id="'.$taxonomy_name.'-addon-period">';
				
				foreach($periods as $k => $v){
					
					$selected = '';
					
					if($k == $period){
						
						$selected='selected';
					}
					elseif($period=='' && $k=='month'){
						
						$selected='selected';
					}
					
					$fields.='<option value="'.$k.'" '.$selected.'> '.$v.' </option>';
				}
				
			$fields.='</select>';					
			
		$fields.='</div>';
		
		$fields.='<p class="description">The '.str_replace(array('-','_'),' ',$taxonomy_name).' addon used in table pricing & plans </p>';
		
		return $fields;
		*/
	}
	
	public function save_layer_fields($term){
		
		/*
		if( isset($_POST[$term->taxonomy .'-addon-amount']) && is_numeric($_POST[$term->taxonomy .'-addon-amount']) ){

			update_option('addon_amount_' . $term->slug, round(intval(sanitize_text_field($_POST[$term->taxonomy . '-addon-amount'])),1));			
		}
		*/		
	}
	
	public function add_layer_columns(){
		
		//$this->parent->layer->columns['addon-column'] = 'Addon columns';
	}
	
	public function add_layer_column_content($column_name, $term){
		
		/*
		if( $column_name === 'addon') {

			$this->parent->layer->column .= 'addon column content';
		}
		*/
	}
	
	public function add_api_layer_plan_option ($terms){
		
		/*
		$this->parent->admin->html .= '<td style="width:150px;">';
		
			foreach($terms as $term){
				
				$this->parent->admin->html .= '<span style="display:block;padding:1px 0 3px 0;margin:0;">';
					
					if($term->options['addon_amount']==1){
						
						$this->parent->admin->html .= '+'.$term->options['addon_amount'].' dom';
					}
					elseif($term->options['addon_amount']>0){
						
						$this->parent->admin->html .= '+'.$term->options['addon_amount'].' doms';
					}	
					else{
						
						$this->parent->admin->html .= $term->options['addon_amount'].' doms';
					}					
			
				$this->parent->admin->html .= '</span>';
			}
		
		$this->parent->admin->html .= '</td>';
		*/
	}
	
	public function sum_addon_amount( &$total_addon_amount=0, $options){
		
		/*
		$total_addon_amount = $total_addon_amount + $options['addon_amount'];
		
		return $total_addon_amount;
		*/
	}
	
	public function add_api_layer_plan_option_total($taxonomies,$plan_options){

		/*
	
		$total_addon_amount = 0;
	
		foreach ( $taxonomies as $taxonomy => $terms ) {
	
			foreach($terms as $term){

				if ( in_array( $term->slug, $plan_options ) ) {
					
					$total_addon_amount 	= $this->sum_addon_amount( $total_addon_amount, $term->options);
				}
			}
		}
		
		$this->parent->admin->html .= '<td style="width:150px;">';
		
			if($total_addon_amount==1){
				
				$this->parent->admin->html .= '+'.$total_addon_amount.' addon';
			}
			elseif($total_addon_amount>0){
				
				$this->parent->admin->html .= '+'.$total_addon_amount.' addons';
			}									
			else{
				
				$this->parent->admin->html .= $total_addon_amount.' addons';
			}		
		
		$this->parent->admin->html .= '</td>';
		*/
	}
	
	public function add_plan_shortcode_attributes($taxonomies,$plan_options){
		
		//$this->parent->plan->shortcode .= 'addon attributes';		
	}
		
	public function handle_subscription_plan(){
				
		
	}
	
	public function add_user_plan_option_total( $user_id, $options ){
		
		//$this->parent->plan->user_plans[$user_id]['info']['total_addon_amount'] 	= $this->sum_addon_amount( $this->parent->plan->user_plans[$user_id]['info']['total_addon_amount'], $options);
	}
	
	public function add_user_plan_info( $user_id ){
		

	}
	
	/**
	 * Wrapper function to register a new post type
	 * @param  string $post_type   Post type name
	 * @param  string $plural      Post type item plural name
	 * @param  string $single      Post type item single name
	 * @param  string $description Description of post type
	 * @return object              Post type class object
	 */
	public function register_post_type ( $post_type = '', $plural = '', $single = '', $description = '', $options = array() ) {

		if ( ! $post_type || ! $plural || ! $single ) return;

		$post_type = new LTPLE_Client_Post_Type( $post_type, $plural, $single, $description, $options );

		return $post_type;
	}

	/**
	 * Wrapper function to register a new taxonomy
	 * @param  string $taxonomy   Taxonomy name
	 * @param  string $plural     Taxonomy single name
	 * @param  string $single     Taxonomy plural name
	 * @param  array  $post_types Post types to which this taxonomy applies
	 * @return object             Taxonomy class object
	 */
	public function register_taxonomy ( $taxonomy = '', $plural = '', $single = '', $post_types = array(), $taxonomy_args = array() ) {

		if ( ! $taxonomy || ! $plural || ! $single ) return;

		$taxonomy = new LTPLE_Client_Taxonomy( $taxonomy, $plural, $single, $post_types, $taxonomy_args );

		return $taxonomy;
	}

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		
		//wp_register_style( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'css/frontend.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-frontend' );
	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		
		//wp_register_script( $this->_token . '-frontend', esc_url( $this->assets_url ) . 'js/frontend' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-frontend' );
	} // End enqueue_scripts ()

	/**
	 * Load admin CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_styles ( $hook = '' ) {
		
		//wp_register_style( $this->_token . '-admin', esc_url( $this->assets_url ) . 'css/admin.css', array(), $this->_version );
		//wp_enqueue_style( $this->_token . '-admin' );
	} // End admin_enqueue_styles ()

	/**
	 * Load admin Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function admin_enqueue_scripts ( $hook = '' ) {
		
		//wp_register_script( $this->_token . '-admin', esc_url( $this->assets_url ) . 'js/admin' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
		//wp_enqueue_script( $this->_token . '-admin' );
	} // End admin_enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		
		load_plugin_textdomain( $this->settings->plugin->slug, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
		
	    $domain = $this->settings->plugin->slug;

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main LTPLE_Inbox Instance
	 *
	 * Ensures only one instance of LTPLE_Inbox is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see LTPLE_Inbox()
	 * @return Main LTPLE_Inbox instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()
}
