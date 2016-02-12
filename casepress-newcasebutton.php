<?php
/**
 * @package Casepress-newcasebutton
 * @version 1.0
 */
/*
Plugin Name: Casepress new case fixed button
Plugin URI: -
Description: A fixed button for a new case for casepress
Author: Petro-1
Version: 1.0
Author URI: -
*/
//add a button and popups
function showbutton() {
	
	if (get_post_type() == 'cases') {
		echo '<link rel="stylesheet" type="text/css" href="' . site_url() . '/wp-content/plugins/casepress-newcasebutton/style.css">';
		?>
		<a onclick="PopUpShow()";><div class="fixed_button"><span class="pencil">✎</span></div></a>
		<div class="b-popup" id="popup">
			<div class="b-popup-content" id="popupchild" onclick="">
				
			</div>
		</div>
		<script>
		var formloaded;
		var PopUpShow = function() {
			document.getElementById("popup").style.left = "-3%";
		}
		var PopUpHide = function() {
			document.getElementById("popup").style.left = "-103%";
		}
		var popup = document.getElementById('popup');
		popup.addEventListener('click', PopUpHide);
		popupchild.addEventListener('click', function(event) {
			event.stopPropagation();
		}); 

		
		
		</script>
		<?php
		
	}
}

add_action( 'get_footer', 'showbutton' );

//AJAX
function addformj() {
  wp_enqueue_script( 'script-name', site_url() . '/wp-content/plugins/casepress-newcasebutton/ajax.js', array('jquery'), '1.0.0', true );
  wp_localize_script( 'script-name', 'MyAjax', array(
    // URL to wp-admin/admin-ajax.php to process the request
    'ajaxurl' => admin_url( 'admin-ajax.php' ),
	'formurl' => home_url() . '/wp-content/plugins/casepress-newcasebutton/newcaseform.php',

    // generate a nonce with a unique ID "myajax-post-comment-nonce"
    // so that you can check it later when an AJAX request is sent
    'security' => wp_create_nonce( 'my-special-string' )
  ));
}
add_action( 'wp_enqueue_scripts', 'addformj' );



?>
