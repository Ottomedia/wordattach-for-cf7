<?php
/*
 * Plugin Name:       Word Template Attachments for CF7
 * Description:		  Fills Word templates with form data and send them via email
 * Plugin URI:        https://bitbucket.org/ottomedia/wordattach-for-cf7/
 * Requires at least: 6.5
 * Requires PHP:      8.0
 * Author:            Stefano Garuti
 * Requires Plugins:  contact-form-7
 * Version:           1.3.0
 */

define( 'WACF7_ABSPATH', dirname( __FILE__ ) );

add_action( 'plugins_loaded', 'wacf7_autoload_library', 0 );
function wacf7_autoload_library(){
	require (WACF7_ABSPATH . '/plugin-update-checker/plugin-update-checker.php');

	$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
		'https://bitbucket.org/ottomedia/wordattach-for-cf7/',
		__FILE__,
		'wordattach-for-cf7'
	);

	require_once __DIR__ . '/vendor/autoload.php';
}

add_action('wpcf7_before_send_mail', 'wacf7_template_parse_attach');
function wacf7_template_parse_attach($WPCF7_ContactForm) {

	// error_log( 'Istanza ' . var_export( $WPCF7_ContactForm, true ) );

	//Get current form
	$wpcf7 = WPCF7_ContactForm::get_current();

	//GET TEMPLATE FILENAME
	$template_paths = $wpcf7->additional_setting('wt_template', 100);

	if( !$template_paths ){
		$template_dir = wacf7_get_template_dir();
		$template_name = 'template-form-' . $WPCF7_ContactForm->id() . '.docx';
		$template_paths[] = $template_dir . $template_name;
	}

	foreach( $template_paths as $template_path ){
		if( file_exists( $template_path ) ) {

			// error_log( 'form id' . var_export( $WPCF7_ContactForm->id(), true ) );

				// get current SUBMISSION instance
				$submission = WPCF7_Submission::get_instance();
				// error_log( 'SUBMISSION ' . var_export( $submission, true ) );
				if ($submission) {

					// get submission data
					$data = $submission->get_posted_data();

					// nothing's here... do nothing...
					if (empty($data))
						return;

					error_log( 'DATA: ' . var_export( $data, true ) );

					// Add context information not passed from Cf7
					// Add submission date
					$data['_date'] = wp_date(get_option( 'date_format' ));


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
					$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor( $template_path );

					// In the templates let's search for tags delimited by [...]
					// $templateProcessor->setMacroChars('[', ']'); /** BUG in phpWord waiting to be fixed */

					$templateProcessor->setValues($data);

					// Setup the filename
					$default_filename = 'document-' . time() . '-' . wp_date( get_option( 'date_format' ), time() );
					$default_filename = sanitize_file_name( $default_filename );

					$fileName = $default_filename;

					if(!empty($wpcf7->pref('wt_filename'))){
						$file = sanitize_file_name( wpcf7_mail_replace_tags( $wpcf7->pref('wt_filename') ) );
						if( !empty( $file ) ) $fileName = $file;
					}
					$fileName .= '.docx';

					// setup upload directory
					$upload_dir = wacf7_get_merged_upload_dir();

					$templateProcessor->saveAs( path_join( $upload_dir, $fileName ) );

					// add upload to e-mail
					$submission->add_extra_attachments( path_join( $upload_dir, $fileName ) );
					// $submission->add_extra_attachments( $upload_dir . $fileName, 'mail_2' );  //per inviare anche a email2

					// carry on with cf7
					return $wpcf7;

				}
			}
	}


}


// returns the folder where the templates are stored
function wacf7_get_template_dir(){
	$template_dir = wp_upload_dir();
	$template_dir = $template_dir['basedir'] . '/wpcf7-templates/';
	return $template_dir;
}

// returns the temp folder where the merged files are stored waiting to be sent
function wacf7_get_merged_upload_dir(){
	$dir = path_join( wpcf7_upload_tmp_dir(), 'wacf7_merged' );
	wp_mkdir_p( $dir );
	return $dir;
}
