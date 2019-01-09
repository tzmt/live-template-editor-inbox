<?php

	$message_width = '97%';
		
	if(!empty($this->parent->message)){ 
	
		//output message
	
		echo $this->parent->message;
	}
	
	if( $this->tab == 'messages' && $this->cid > 0 ){
	
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
			
			.inbox .message {      
				padding: 18px 20px;
				line-height: 26px;
				font-size: 16px;
				border-radius: 7px;
				margin-top: 20px !important;
				margin-bottom: 30px !important;
				position: relative;
			}
			
			.inbox .message a {  
				
				color: #6200ff;
			}
			
			.inbox .message:after {
				bottom: 100%;
				left: 7%;
				border: solid transparent;
				content: " ";
				height: 0;
				width: 0;
				position: absolute;
				pointer-events: none;
				border-bottom-color: '.$this->parent->settings->mainColor . '99;
				border-width: 10px;
				margin-left: -10px;
			}
			
			.inbox .other-message {
				background: '.$this->parent->settings->mainColor . '99;
				color: #fff;
			}
			
			.inbox .my-message {
				background: #fff;
				border: 1px solid '.$this->parent->settings->borderColor . ';
				color: '.$this->parent->settings->mainColor . ';
			}
			
			.inbox .my-message:after {
				border-bottom-color: '.$this->parent->settings->mainColor . ';
				left: 93%;
			}
			
			.inbox #conversation::-webkit-scrollbar-track{
				
				-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.3);
				border-radius: 10px;
				background-color: #fff;
			}

			.inbox #conversation::-webkit-scrollbar{
				
				width: 10px;
				background-color: #fff;
			}

			.inbox #conversation::-webkit-scrollbar-thumb{
				
				border-radius: 10px;
				-webkit-box-shadow: inset 0 0 6px rgba(0,0,0,.3);
				background-color: '.$this->parent->settings->mainColor . '99;
			}
			
			.inbox #conversationWrapper {
				position: relative;
			}
			
			.inbox #conversationWrapper .fadeout {
				width:'.$message_width.';
				position: absolute; 
				bottom: 0;
				height: 10px;
				background: -webkit-linear-gradient(
					rgba(255, 255, 255, 0) 0%,
					rgba(255, 255, 255, 1) 100%
				); 
				background-image: -moz-linear-gradient(
					rgba(255, 255, 255, 0) 0%,
					rgba(255, 255, 255, 1) 100%
				);
				background-image: -o-linear-gradient(
					rgba(255, 255, 255, 0) 0%,
					rgba(255, 255, 255, 1) 100%
				);
				background-image: linear-gradient(
					rgba(255, 255, 255, 0) 0%,
					rgba(255, 255, 255, 1) 100%
				);
				background-image: -ms-linear-gradient(
					rgba(255, 255, 255, 0) 0%,
					rgba(255, 255, 255, 1) 100%
				);
			} 
			
			';	
		
		echo'</style>';	
			
		echo'<div class="clearfix inbox">';
			
			echo'<div id="conversationWrapper">';
				
				echo'<div id="conversation" style="opacity:0;background:#fff;height:300px;overflow-y:scroll;overflow-x:hidden;">'; 
				
				echo'<ul id="messages" style="margin:0;" class="mail_list list-group list-unstyled">';
					
					foreach( $this->messages as $message ){
						
						$is_me = ( $message->post_author == $this->parent->user->ID ? true : false );
						
						$profile_url = $this->parent->urls->profile . $message->post_author . '/';
						
						echo'<li id="message_'.$message->ID . '" class="list-group-item" style="width:'.$message_width.';padding:15px 30px;">';
							
							echo'<div class="media">';
								
								echo'<div class="'. ( $is_me ? 'pull-right' : 'pull-left' ) .'">';                               
									
									echo'<div class="thumb hidden-sm-down m-r-20"><a title="Go to ' . $message->user->nickname .'\'s profile" target="_blank" href="'.$profile_url.'"><img src="' . $message->user->avatar . '" class="rounded-circle" alt=""/></a> </div>';
								
								echo'</div>';
								
								echo'<div class="media-body">';
									
									echo'<div class="media-heading'. ( $is_me ? ' text-right' : '' ) .'">';
										
										echo'<a style="font-weight:bold;" title="Go to ' . $message->user->nickname .'\'s profile" target="_blank" href="'.$profile_url.'">' . $message->user->nickname . '</a>';
										
										echo'<small class="float-right text-muted">';
										
											echo' <time class="hidden-sm-down">' . $message->post_date . '</time> ';
											
											echo ( !empty($conversation['last_has_att']) ? '<i class="zmdi zmdi-attachment-alt"></i>' : '' );
											
										echo'</small>';
										
									echo'</div>';
									
									echo'<div class="msg message col-sm-5 '. ( $is_me ? 'my-message pull-right' : 'other-message' ) .'">' . str_replace(PHP_EOL,'<br>',$message->post_content) . '</div>';
									
								echo'</div>';
							echo'</div>';
						echo'</li>';										
					}
					
				echo'</ul>';

				echo'</div>';
				
				echo'<div class="fadeout"></div>';
			
			echo'</div>';
			
			// get input key

			$key = $this->get_input_key();
			
			echo '<div class="well" style="display:inline-block;width:100%;margin-top:10px;">';
				
				echo '<form id="replyForm" action="' . $this->parent->urls->current . '" method="post">';
					
					// message
					
					echo '<label>Message</label>';
					
					echo '<textarea id="replyContent" style="height:80px;margin-bottom:20px;" class="form-control" name="'.$key.'message" placeholder="My message" required></textarea>';

					// output
					
					echo '<input type="hidden" name="output" value="widget">';
					
					// time token
					
					echo '<input type="hidden" name="'.$key.'token" value="'.$this->parent->ltple_encrypt_str(time()).'">';
					
					// button
					
					echo '<button id="replyBtn" class="pull-right btn btn-primary">Reply</button>';
					
				echo '</form>';
				
			echo '</div>';

		echo '</div>';
		
		echo'<script>' . PHP_EOL;

			echo';(function($){' . PHP_EOL;	

				echo'function time(){' . PHP_EOL;
					
					echo'var timestamp = Math.floor(new Date().getTime() / 1000);' . PHP_EOL;
					
					echo'return timestamp;' . PHP_EOL;
					
				echo'}' . PHP_EOL;
			
				echo'function refreshConversation(){' . PHP_EOL;

					echo'$.get( "'.$this->parent->urls->current.'", function( data ) {' . PHP_EOL;
						
						echo'$messages = $(data).find(".list-group-item");' . PHP_EOL;
						
						echo'appendMessages($messages);' . PHP_EOL;
						
					echo'});' . PHP_EOL;
					
				echo'}' . PHP_EOL;
				
				echo'function appendMessages($messages){' . PHP_EOL;
					
					echo'$messages.each( function( i ) {' . PHP_EOL;
						
						echo'var id = "#" + $( this ).attr("id");' . PHP_EOL;
						
						echo'if( $(id).length == 0 ){' . PHP_EOL;

							echo'$( this ).appendTo( $("#messages") );' . PHP_EOL;
							
							// scroll conversation down
							
							echo'var $conversation = $("#conversation");' . PHP_EOL;
							echo'$conversation.scrollTop($conversation.prop("scrollHeight"));' . PHP_EOL;
															
						echo'}' . PHP_EOL;
						
					echo'});' . PHP_EOL;					
				
				echo'}' . PHP_EOL;
				
				echo'$(document).ready(function(){' . PHP_EOL;

					// scroll conversation down
					
					echo'var $conversation = $("#conversation");' . PHP_EOL;
					echo'$conversation.scrollTop($conversation.prop("scrollHeight"));' . PHP_EOL;
					
					echo'$conversation.css("opacity",1);' . PHP_EOL;
					
					// refresh content
					
					echo'self.setInterval(refreshConversation, 10000);' . PHP_EOL; // every 10sec
					
					// bind reply

					echo'$("#replyForm").submit(function(e) {' . PHP_EOL;

						echo'e.preventDefault();' . PHP_EOL;
						
						echo'var form = $(this);' . PHP_EOL;
						echo'var url = form.attr("action");' . PHP_EOL;
						
						echo'$.ajax({' . PHP_EOL;
						
							echo'type 	 	: "POST",' . PHP_EOL;
							echo'url 	 	: url,' . PHP_EOL;
							echo'data 	 	: form.serialize(),' . PHP_EOL;
							echo'beforeSend : function(xhr){' . PHP_EOL;
							
								
							
							echo'},' . PHP_EOL;
							echo'success 	: function(data){' . PHP_EOL;
								
								echo'$messages = $(data).find(".list-group-item");' . PHP_EOL;
								
								echo'appendMessages($messages);' . PHP_EOL;
								
								// replace form inputs with new token
								
								echo'newForm = $(data).find("#replyForm");' . PHP_EOL;
								
								echo'form.html(newForm.html());' . PHP_EOL;

							echo'}' . PHP_EOL;
						
						echo'});' . PHP_EOL;
					
					echo'});' . PHP_EOL;
					
				echo'});' . PHP_EOL;
				
			echo'})(jQuery);' . PHP_EOL;

		echo'</script>' . PHP_EOL;			
	}
