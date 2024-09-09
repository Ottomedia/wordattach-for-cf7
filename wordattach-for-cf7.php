<?php
/*
 * Plugin Name:       WordAttach for cf7
 * Plugin URI:        https://bitbucket.org/ottomedia/wordattach-for-cf7/
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Stefano Garuti
 * Requires Plugins:  contact-form-7
 * Version:           1.1.0
 */

add_action( 'plugins_loaded', 'autoload_library', 0 );
function autoload_library(){
	require_once __DIR__ . '/vendor/autoload.php';
}


add_action('wpcf7_before_send_mail', 'cv_word_doc');
function cv_word_doc($WPCF7_ContactForm) {

	// error_log( 'Istanza ' . var_export( $WPCF7_ContactForm, true ) );

	$template_dir = wp_upload_dir();
	$template_dir = $template_dir['basedir'] . '/wpcf7-templates/';

	$template_name = 'template-form-' . $WPCF7_ContactForm->id() . '.docx';

	if( file_exists( $template_dir . $template_name ) ) {

	// error_log( 'form id' . var_export( $WPCF7_ContactForm->id(), true ) );

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

			// error_log( 'DATA: ' . var_export( $data, true ) );

			// TemplateProcessor can only substitute strings. But CF7's dropdowns, checkboxes, etc sends arrays
			// here we implode them.
			foreach($data as $key=>$item){
				if( is_array( $item ) ){
					if( count( $item ) == 1 ){
						$data[$key] = $item[0];
					}
					else{
						$data[$key] = implode( ', ', $item );
					}
				}
			}

			// Transform uppercase fields according to directives
			$uppercase_directives = $wpcf7->additional_setting('wt_uppercase', 100);
			foreach( $uppercase_directives as $key){
				$key = str_replace( '[', '', $key );
				$key = str_replace( ']', '', $key );
				$data[$key] = strtoupper($data[$key]);
			}
			// Transform lowercase fields according to directives
			$lowercase_directives = $wpcf7->additional_setting('wt_lowercase', 100);
			foreach( $lowercase_directives as $key){
				$key = str_replace( '[', '', $key );
				$key = str_replace( ']', '', $key );
				$data[$key] = strtolower($data[$key]);
			}
			$ucfirst_directives = $wpcf7->additional_setting('wt_ucfirst', 100);
			foreach( $ucfirst_directives as $key){
				$key = str_replace( '[', '', $key );
				$key = str_replace( ']', '', $key );
				$data[$key] = ucfirst($data[$key]);
			}
			$ucwords_directives = $wpcf7->additional_setting('wt_ucwords', 100);
			foreach( $ucwords_directives as $key){
				$key = str_replace( '[', '', $key );
				$key = str_replace( ']', '', $key );
				$data[$key] = ucwords($data[$key]);
			}

			// Format date
			$format_date_directives = $wpcf7->additional_setting('wt_format_date', 100);
			foreach( $format_date_directives as $key){
				$key = str_replace( '[', '', $key );
				$key = str_replace( ']', '', $key );
				$info = explode( "|", $key );
				$key = $info[0];
				$format = $info[1];
				$data[$key] = date( $format, strtotime($data[$key]));
			}

			// PHPWord stuff...
			$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor( $template_dir . $template_name );

			// In the templates let's search for tags delimited by [...]
			// $templateProcessor->setMacroChars('[', ']'); /** BUG in phpWord waiting to be fixed */

			$templateProcessor->setValues($data);

			// setup upload directory and name the file
			$upload_dir = $template_dir . 'merged/';

			$default_filename = 'document-' . time() . '-' . wp_date( get_option( 'date_format' ), time() );
			$default_filename = sanitize_file_name( $default_filename );

			$fileName = $default_filename;

			if(!empty($wpcf7->pref('wt_filename'))){
				$file = sanitize_file_name( wpcf7_mail_replace_tags( $wpcf7->pref('wt_filename') ) );
				if( !empty( $file ) ) $fileName = $file;
			}

			$fileName .= '.docx';

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
