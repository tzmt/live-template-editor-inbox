<?php 
	
	// get variables
	
	$tabs = ['messages','contacts'];
	
	$currentTab = ( !empty($this->tab) && in_array($this->tab,$tabs) ? $this->tab : 'messages' );
	
	$message_width = '97%';
		
	// output message
	
	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}

	// ------------- output panel --------------------
	
	echo'<style>';
	
		echo'
		.badge {
			border-radius: 8px;
			padding: 4px 8px;
			text-transform: uppercase;
			font-size: .7142em;
			line-height: 12px;
			background-color: transparent;
			border: 1px solid;
			margin-bottom: 5px;
			border-radius: .875rem;
		}
		.bg-green {
			background-color: #50d38a !important;
			color: #fff;
		}
		.bg-blush {
			background-color: #ff758e !important;
			color: #fff;
		}
		.bg-amber {
			background-color: #FFC107 !important;
			color: #fff;
		}
		.bg-red {
			background-color: #ec3b57 !important;
			color: #fff;
		}
		.bg-blue {
			background-color: #60bafd !important;
			color: #fff;
		}
		.card {
			background: #fff;
			margin-bottom: 30px;
			transition: .5s;
			border: 0;
			border-radius: .1875rem;
			display: inline-block;
			position: relative;
			width: 100%;
			box-shadow: none;
		}
		.inbox .action_bar .delete_all {
			margin-bottom: 0;
			margin-top: 8px;
		}

		.inbox .action_bar .btn,
		.inbox .action_bar .search {
			margin: 0;
		}

		.inbox .mail_list .list-group-item {
			border: 0;
			padding: 15px;
			margin-bottom: 1px
		}

		.inbox .mail_list .list-group-item:hover {
			background: #eceeef;
		}

		.inbox .mail_list .list-group-item .media {
			margin: 0;
			width: 100%;
		}

		.inbox .mail_list .list-group-item .controls {
			display: inline-block;
			margin-right: 10px;
			vertical-align: top;
			text-align: center;
			margin-top: 4px;
		}

		.inbox .mail_list .list-group-item .controls .checkbox {
			display: inline-block;
		}

		.inbox .mail_list .list-group-item .controls .checkbox label {
			margin: 0;
			padding: 10px;
		}

		.inbox .mail_list .list-group-item .controls .favourite {
			margin-left: 10px;
		}

		.inbox .mail_list .list-group-item .thumb {
			display: inline-block;
		}

		.inbox .mail_list .list-group-item .thumb img {
			width: 40px;
			border-radius: 250px;
			margin: 7px;
		}

		.inbox .mail_list .list-group-item .media-heading a {
			color: #555;
			font-weight: normal;
		}

		.inbox .mail_list .list-group-item .media-heading a:hover,
		.inbox .mail_list .list-group-item .media-heading a:focus {
			text-decoration: none;
		}

		.inbox .mail_list .list-group-item .media-heading time {
			font-size: 13px;
			margin-right: 10px;
		}

		.inbox .mail_list .list-group-item .media-heading .badge {
			margin-bottom: 0;
			border-radius: 50px;
			font-weight: normal
		}

		.inbox .mail_list .list-group-item .msg {
			margin-bottom: 0px;
		}

		.inbox .mail_list .unread {
			border-left: 2px solid;
		}

		.inbox .mail_list .unread .media-heading a {
			color: #333;
			font-weight: 700;
		}

		.inbox .btn-group {
			box-shadow: none;
		}

		.inbox .bg-gray {
			background: #e6e6e6;
		}

		@media only screen and (max-width: 767px) {
			.inbox .mail_list .list-group-item .controls {
				margin-top: 3px;
			}
		}
		
		';	
	
	echo'</style>';
	
	echo'<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">';
	
	echo'<div id="panel">';

		echo'<div class="col-xs-3 col-sm-2" style="padding:0;">';
		
			echo'<ul class="nav nav-tabs tabs-left">';
				
				echo'<li class="gallery_type_title">Inbox</li>';
				
				echo'<li'.( $currentTab == 'messages' ? ' class="active"' : '' ).'><a style="cursor:pointer;" href="'.$this->parent->urls->inbox . '">Messages</a></li>';

				//echo'<li'.( $currentTab == 'contacts' ? ' class="active"' : '' ).'><a href="'.$this->parent->urls->inbox . '?tab=contacts">Contacts</a></li>';
				
			echo'</ul>';
			
		echo'</div>';

		echo'<div class="col-xs-9 col-sm-10 library-content" style="border-left: 1px solid #ddd;background:#fff;padding-bottom:15px;min-height:700px;">';
			
			echo'<div class="tab-content">';

				if( $currentTab == 'messages' ){
					
					if( $this->cid > 0 ){
						
						
					}
					else{
					
						echo'<div class="bs-callout bs-callout-primary">';
						
							echo'<h4>';
							
								echo'Messages';
								
							echo'</h4>';
						
							echo'<p>';
							
								echo 'List of conversations started with others.';
							
							echo'</p>';	

						echo'</div>';
					}					
					
					echo'<div class="tab-content row" style="padding:0 15px;">';
				
						echo'<div class="row clearfix inbox">';
						
							echo'<div class="col-md-12 col-lg-12 col-xl-12">';
								
								if( $this->cid > 0 ){
									
									// display conversation
									
									if( !empty($this->messages) ){
										
										// get iframe
										
										$iframe_url = add_query_arg('output','widget',$this->parent->urls->current);
										
										echo'<div class="loadingIframe" style="height:100px;width:100%;position:absolute;background-position: 50% center;background-repeat: no-repeat;background-image:url(\''. $this->parent->server->url .'/c/p/live-template-editor-server/assets/loader.gif\');"></div>';
											
										echo'<iframe id="conversationIframe" style="position:relative;background:transparent;height:650px;overflow:hidden;margin-top:25px;width:100%;border:0;" src="'.$iframe_url.'" ></iframe>';
									}
								}
								else{
									
									$conversations = $this->get_user_conversations();
									
									echo'<ul class="mail_list list-group list-unstyled">';
										
										if( !empty($conversations) ){
											
											foreach( $conversations as $conversation ){
												
												if( $conversation['status'] == 'publish' ){
													
													echo'<li class="list-group-item'.( $conversation['last_unread'] ? ' unread' : '' ).'">';
														
														echo'<a class="media" title="Open conversation" style="display:block;color:#000;" href="' . $this->parent->urls->inbox . 'messages/' . $conversation['id'].'/">';
															
																echo'<div class="pull-left">';                               
																	echo'<div class="controls">';
																		/*
																		echo'<div class="checkbox">';
																			echo'<input type="checkbox" id="basic_checkbox_1">';
																			echo'<label for="basic_checkbox_1"></label>';
																		echo'</div>';
																		*/
																		//echo'<a href="javascript:void(0);" class="favourite '.( $conversation['in_favourite'] ? 'col-amber' : 'text-muted' ).' hidden-sm-down" data-toggle="active"><i class="zmdi '.( $conversation['in_favourite'] ? 'zmdi-star' : 'zmdi-star-outline' ).'"></i></a>';
																	echo'</div>';
																	echo'<div class="thumb hidden-sm-down m-r-20"> <img src="'.$conversation['avatars'][0].'" class="rounded-circle" alt=""> </div>';
																echo'</div>';
																echo'<div class="media-body">';
																	echo'<div class="media-heading">';
																		
																		echo'<b class="m-r-10">'.$conversation['names'].'</b>';
																		
																		echo ( !empty($conversation['badge']) ? ' <span class="badge bg-'.$conversation['badge'].'">'.$this->get_badge_title($conversation['badge']).'</span> ' : '' );
																		
																		echo'<small class="float-right text-muted">';
																		
																			echo' <time class="hidden-sm-down">'.$conversation['last_time'].'</time> ';
																			
																			echo ( !empty($conversation['last_has_att']) ? '<i class="zmdi zmdi-attachment-alt"></i>' : '' );
																			
																		echo'</small>';
																		
																	echo'</div>';
																	
																	echo'<p class="msg">'.$conversation['last_excerpt'].'</p>';
																	
																echo'</div>';
															
														echo'</a>';
													echo'</li>';
												}
											}
										}
										else{
											
											echo'<li class="list-group-item">';
												echo'<div class="media">';
													echo'<div class="media-body">';
													
													echo'<p class="msg">You don\'t have any conversations yet.</p>';
													
													echo'</div>';
												echo'</div>';													
											echo'</li>';
										}
										
									echo'</ul>';
									
									// pagination
									
									/*
									echo'<div class="card m-t-5">
										<div class="body">
											<ul class="pagination pagination-primary m-b-0">
												<li class="page-item"><a class="page-link" href="javascript:void(0);">Previous</a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">1</a></li>
												<li class="page-item active"><a class="page-link" href="javascript:void(0);">2</a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">3</a></li>
												<li class="page-item"><a class="page-link" href="javascript:void(0);">Next</a></li>
											</ul>
										</div>
									</div>'
									*/
								}
								
							echo'</div>';
						echo'</div>';		
		
					echo'</div>';	
				}
				elseif( $currentTab == 'contacts' ){
					
					echo'<div class="bs-callout bs-callout-primary">';
					
						echo'<h4>';
						
							echo'Contacts';
							
						echo'</h4>';
					
						echo'<p>';
						
							echo 'List of contacts';
						
						echo'</p>';	

					echo'</div>';	
					
					echo'<div class="tab-content row" style="padding:0 15px;">';
		
		
					echo'</div>';	
				}

			echo'</div>';
			
		echo'</div>	';

	echo'</div>';
	
	// script
	
	echo'<script>' . PHP_EOL;

		echo';(function($){' . PHP_EOL;	
			
			echo'$(document).ready(function(){' . PHP_EOL;

				
				
			echo'});' . PHP_EOL;
			
		echo'})(jQuery);' . PHP_EOL;

	echo'</script>' . PHP_EOL;