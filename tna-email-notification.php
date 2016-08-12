<?php
/**
 * Submit page for review email notification
 * Sends an email notifying Editors that a devolved editor has made a change
 *
 */

add_action( 'post_submitbox_misc_actions', 'adds_tell_us_what_changes_textarea' );
function adds_tell_us_what_changes_textarea(){
	global $post;
	$status = get_post_status( $post->ID );
	if ( $status == 'draft' || $status == 'auto-draft' ) { ?>
	<div class="misc-pub-section changes-comment">
		<label for="my-changes">Tell us what changes you have made:</label><br />
		<textarea id="my-changes" name="my-changes"></textarea>
	</div>
	<?php }
}

function wp_mail_set_text_body($phpmailer) {
	if (empty($phpmailer->AltBody)) {
		$phpmailer->AltBody = strip_tags($phpmailer->Body);
	}
}
add_action('phpmailer_init','wp_mail_set_text_body');

function html_message() {

	// Variables
	global $post;
	global $current_user;
	$comments = get_user_changes_comments( filter_input(INPUT_POST, 'my-changes', FILTER_SANITIZE_SPECIAL_CHARS) );

	// Greeting
	$greetings = array( 'Hello', 'G&lsquo;day', 'Hey', 'Buna', 'Kon&lsquo;nichiwa', 'Bonjour', 'Hola', 'Ciao' );

	// HTML Email
	$html_message = '<html><head><title>Editorial review: ' . $current_user->display_name . ' submitted a page for review</title>';
	$html_message .= '<style type="text/css">a{color:#0073aa;}</style></head>';
	$html_message .= '<body style="font-family:Arial,sans-serif;font-size:16px;">';
	$html_message .= $greetings[array_rand($greetings, 1)] . ' web editor,';
	$html_message .= '<h3><strong style="color:#0073aa;">' . $current_user->display_name . '</strong> has submitted ';
	$html_message .= '<strong style="color:#0073aa;">' . get_the_title() . '</strong> for review</h3>';
	$html_message .= '<p>Page title: ' . get_the_title() . ' ';
	$html_message .= '<small>( <a href="' . wp_get_shortlink() . '&preview=true">Preview</a> | ';
	$html_message .= '<a href="' . get_edit_post_link() . '">Edit</a> )</small></p>';
	$html_message .= '<p>Page hierarchy: ' . get_permalink() . '</p>';
	$html_message .= '<p>Page ID: ' . $post->ID . '</p>';
	$html_message .= '<p>Modified: ' . get_the_modified_date($d = 'l d M Y, G:i') . '</p>';
	$html_message .= '<p>' . $current_user->display_name . ' comments:</p>';
	$html_message .= '<p>' . $comments . '</p><hr>';
	$html_message .= '</body></html>';

	return $html_message;
}

function notify_editor_of_pending( $post ) {

	// Current user
	$current_user = wp_get_current_user();

	// Send email to these email addresses
	$to = array( get_web_editor_email( get_userdata(22) ), $current_user->user_email );

	// Email Subject
	$subject = 'Editorial review: ' . $current_user->display_name . ' submitted a page for review';

	// Email message
	$message = html_message();

	wp_mail( $to, $subject, $message );
}
add_action( 'new_to_pending', 'notify_editor_of_pending' );
add_action( 'draft_to_pending', 'notify_editor_of_pending' );
add_action( 'auto-draft_to_pending', 'notify_editor_of_pending' );
