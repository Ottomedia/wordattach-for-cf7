<?php
/*
 * Plugin Name:       WordAttach for cf7
 * Plugin URI:        https://bitbucket.org/ottomedia/wordattach-for-cf7/
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Stefano Garuti
 * Requires Plugins:  contact-form-7
 * Version:           1.0.0
 */

add_action( 'plugins_loaded', 'autoload_library', 0 );
function autoload_library(){
	require_once __DIR__ . '/vendor/autoload.php';
}


add_action('wpcf7_before_send_mail', 'cv_word_doc');
function cv_word_doc($WPCF7_ContactForm) {

	$template_dir = wp_upload_dir();
	$template_dir = $template_dir['basedir'] . '/wpcf7-templates/';

	$template_name = 'template-form-' . $WPCF7_ContactForm->id() . '.docx';

	if( file_exists( $template_dir . $template_name ) ) {

		//Get current form
		$wpcf7 = WPCF7_ContactForm::get_current();

		// get current SUBMISSION instance
		$submission = WPCF7_Submission::get_instance();

		if ($submission) {

			// get submission data
			$data = $submission->get_posted_data();

			// nothing's here... do nothing...
			if (empty($data))
				return;

			// PHPWord stuff...
			$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor( $template_dir . $template_name );

			$templateProcessor->setValues($data);

			// setup upload directory and name the file
			$upload_dir = $template_dir . 'merged/';
			$fileName = 'documento-' . time() .'.docx';

			$templateProcessor->saveAs( $upload_dir . $fileName );

			// add upload to e-mail

			$submission->add_extra_attachments( $upload_dir . $fileName );
			// $submission->add_extra_attachments( $upload_dir . $fileName, 'mail_2' );  //per inviare anche a email2

			// carry on with cf7
			return $wpcf7;

		}
	}
}

/*
add_action(
	'shutdown',
	'wpcf7_cleanup_upload_files',
	20, 0
);
*/
/**
 * Cleans up files in the temporary directory for uploaded files.
 *
 * @param int $seconds Files older than this are removed. Default 60.
 * @param int $max Maximum number of files to be removed in a function call.
 *                 Default 100.

function wpcf7_cleanup_upload_files( $seconds = 60, $max = 100 ) {
	$dir = trailingslashit( wpcf7_upload_tmp_dir() );

	if ( ! is_dir( $dir )
	or ! is_readable( $dir )
	or ! wp_is_writable( $dir ) ) {
		return;
	}

	$seconds = absint( $seconds );
	$max = absint( $max );
	$count = 0;

	if ( $handle = opendir( $dir ) ) {
		while ( false !== ( $file = readdir( $handle ) ) ) {
			if ( '.' == $file
			or '..' == $file
			or '.htaccess' == $file ) {
				continue;
			}

			$mtime = @filemtime( path_join( $dir, $file ) );

			if ( $mtime and time() < $mtime + $seconds ) { // less than $seconds old
				continue;
			}

			wpcf7_rmdir_p( path_join( $dir, $file ) );
			$count += 1;

			if ( $max <= $count ) {
				break;
			}
		}

		closedir( $handle );
	}
}
*/
