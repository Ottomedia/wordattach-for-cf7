<?php
/*
 * Plugin Name:       Word Template Attachments for CF7
 * Description:		  Fills Word templates with form data and send them via email
 * Plugin URI:        https://bitbucket.org/ottomedia/wordattach-for-cf7/
 * Requires at least: 6.5
 * Tested up to:      6.8.3
 * Requires PHP:      8.0
 * Author:            Stefano Garuti
 * Requires Plugins:  contact-form-7
 * Version:           1.5.1
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

	// Init debug context from directives
	wacf7_debug_bootstrap( $wpcf7 );
	wacf7_debug_log( 'start: form_id=' . $WPCF7_ContactForm->id() );

	//GET TEMPLATE FILENAME
	$template_paths = $wpcf7->additional_setting('wt_template', 100);
	$had_template_directive = !empty($template_paths);

	if( !$template_paths ){
		$template_dir = wacf7_get_template_dir();
		$template_name = 'template-form-' . $WPCF7_ContactForm->id() . '.docx';
		$template_paths[] = $template_dir . $template_name;
	}

	wacf7_debug_log( 'template_paths=' . implode(' | ', array_map('strval', (array) $template_paths) ) );

	$any_template_found = false;
	foreach( $template_paths as $template_path ){
		wacf7_debug_log( 'check_template_exists path=' . $template_path );
		if( file_exists( $template_path ) ) {
			$any_template_found = true;

			// error_log( 'form id' . var_export( $WPCF7_ContactForm->id(), true ) );

				// get current SUBMISSION instance
				$submission = WPCF7_Submission::get_instance();
				// error_log( 'SUBMISSION ' . var_export( $submission, true ) );
				if ($submission) {

					// get submission data
					$data = $submission->get_posted_data();

					// nothing's here... do nothing...
					if (empty($data)){
						wacf7_debug_log( 'no_posted_data' );
						wacf7_debug_maybe_flush_mail( $wpcf7 );
						return;
					}

					wacf7_debug_log( 'posted_keys=' . implode(',', array_keys((array)$data)) );

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
					wacf7_debug_log( 'coerced_arrays_done' );

					// Transform uppercase fields according to directives
					$uppercase_directives = $wpcf7->additional_setting('wt_uppercase', 100);
					foreach( $uppercase_directives as $key){
						$key = str_replace( '[', '', $key );
						$key = str_replace( ']', '', $key );
						if ( isset($data[$key]) ) { $data[$key] = strtoupper($data[$key]); }
					}
					// Transform lowercase fields according to directives
					$lowercase_directives = $wpcf7->additional_setting('wt_lowercase', 100);
					foreach( $lowercase_directives as $key){
						$key = str_replace( '[', '', $key );
						$key = str_replace( ']', '', $key );
						if ( isset($data[$key]) ) { $data[$key] = strtolower($data[$key]); }
					}
					$ucfirst_directives = $wpcf7->additional_setting('wt_ucfirst', 100);
					foreach( $ucfirst_directives as $key){
						$key = str_replace( '[', '', $key );
						$key = str_replace( ']', '', $key );
						if ( isset($data[$key]) ) { $data[$key] = ucfirst($data[$key]); }
					}
					$ucwords_directives = $wpcf7->additional_setting('wt_ucwords', 100);
					foreach( $ucwords_directives as $key){
						$key = str_replace( '[', '', $key );
						$key = str_replace( ']', '', $key );
						if ( isset($data[$key]) ) { $data[$key] = ucwords($data[$key]); }
					}
					wacf7_debug_log( 'transforms: upper=' . count($uppercase_directives) . '; lower=' . count($lowercase_directives) . '; ucfirst=' . count($ucfirst_directives) . '; ucwords=' . count($ucwords_directives) );

					// Format date
					$format_date_directives = $wpcf7->additional_setting('wt_format_date', 100);
					foreach( $format_date_directives as $key){
						$key = str_replace( '[', '', $key );
						$key = str_replace( ']', '', $key );
						$info = explode( "|", $key );
						$key = trim( $info[0] ?? '' );
						$format = trim( $info[1] ?? '' );
						if ( $key !== '' && $format !== '' && !empty($data[$key]) ) {
							$data[$key] = date( $format, strtotime($data[$key]));
						}
					}

					$upload_dir = wacf7_get_merged_upload_dir();
					if ( ! wp_is_writable( $upload_dir ) ) {
						wacf7_debug_log( 'upload_dir_not_writable dir=' . $upload_dir );
					}

					$merge_ok = false;
					$merge_err = '';
					try {
						$templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor( $template_path );
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
						wacf7_debug_log( 'saveAs name=' . $fileName . ' dir=' . $upload_dir );
						$templateProcessor->saveAs( path_join( $upload_dir, $fileName ) );
						$merge_ok = true;
					} catch (\PhpOffice\PhpWord\Exception\Exception $e) {
						$merge_err = 'PhpWordException: ' . $e->getMessage();
						wacf7_debug_log( $merge_err );
					} catch (\Throwable $t) {
						$merge_err = 'Throwable: ' . $t->getMessage();
						wacf7_debug_log( $merge_err );
					}

					if ( $merge_ok ) {
						$attachment_path = path_join( $upload_dir, $fileName );
						$submission->add_extra_attachments( $attachment_path );
						wacf7_debug_log( 'attached=' . $attachment_path );
					} else {
						wacf7_debug_log( 'merge_failed' );
					}
					// $submission->add_extra_attachments( $upload_dir . $fileName, 'mail_2' );  //per inviare anche a email2

					// carry on with cf7
					wacf7_debug_maybe_flush_mail( $wpcf7 );
					return $wpcf7;

				}
			} else {
				$norm = function_exists('wp_normalize_path') ? wp_normalize_path($template_path) : $template_path;
				$dir = dirname($template_path);
				$dir_exists = file_exists($dir) ? '1' : '0';
				$dir_writable = file_exists($dir) ? ( wp_is_writable($dir) ? '1' : '0' ) : '-';
				$real = @realpath($template_path);
				$uploads = wp_upload_dir();
				$under_uploads = '0';
				if ( isset($uploads['basedir']) ) {
					$base = function_exists('wp_normalize_path') ? wp_normalize_path($uploads['basedir']) : $uploads['basedir'];
					$under_uploads = ( str_starts_with($norm, rtrim($base,'/')) ? '1' : '0' );
				}
				$source = $had_template_directive ? 'directive' : 'default';
				wacf7_debug_log( 'template_missing source=' . $source . ' path_norm=' . $norm );
				wacf7_debug_log( 'template_dir exists=' . $dir_exists . ' writable=' . $dir_writable . ' dir=' . $dir );
				if ( $real === false ) { wacf7_debug_log( 'realpath_unresolved' ); } else { wacf7_debug_log( 'realpath=' . $real ); }
				wacf7_debug_log( 'under_uploads=' . $under_uploads . ' uploads_basedir=' . ( $uploads['basedir'] ?? '' ) );

				// Hints to help users fix directive paths
				if ( $source === 'directive' ) {
					$templates_dir = wacf7_get_template_dir();
					$given_name = basename( (string) $template_path );
					$use_name = ( strtolower(pathinfo($given_name, PATHINFO_EXTENSION)) === 'docx' && $given_name !== '' )
						? $given_name
						: ( 'template-form-' . $WPCF7_ContactForm->id() . '.docx' );
					$suggested_path = trailingslashit( $templates_dir ) . $use_name;
					wacf7_debug_log( 'hint_suggested_path=' . $suggested_path );
					if ( preg_match( '/\s/', (string) $template_path ) ) {
						wacf7_debug_log( 'hint_spaces_in_path use_quotes="' . $template_path . '"' );
					}
					if ( $dir_exists === '1' && $dir_writable === '0' ) {
						wacf7_debug_log( 'hint_dir_permissions directory_not_writable=' . $dir );
					}
				}
			}
	} // end foreach template paths

		if ( ! $any_template_found ) {
			wacf7_debug_log( 'no_valid_template_found count=' . count($template_paths) . ' suggested_dir=' . wacf7_get_template_dir() );
		}

	// No template processed, flush debug if requested
	wacf7_debug_maybe_flush_mail( $wpcf7 );

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

/* Debug helpers */

function wacf7_debug_bootstrap( $wpcf7 ){
	static $bootstrapped = false;
	if ( $bootstrapped ) return;
	$bootstrapped = true;
	$ctx = array(
		'enabled' => false,
		'to' => 'log',
		'level' => 'basic',
		'buffer' => array(),
		'flushed' => false,
	);
	$enabled = $wpcf7->pref('wt_debug');
	if ( is_string($enabled) ) { $enabled = wacf7_parse_truthy($enabled); }
	$ctx['enabled'] = (bool) $enabled;
	$to = $wpcf7->pref('wt_debug_to');
	$to = is_string($to) ? strtolower(trim($to)) : '';
	if ( in_array($to, array('mail','both','log'), true) ) { $ctx['to'] = $to; }
	$level = $wpcf7->pref('wt_debug_level');
	$ctx['level'] = $level ? strtolower(trim($level)) : 'basic';
	$env = wacf7_debug_env_snapshot();
	$ctx['buffer'][] = '[env] ' . $env;
	$GLOBALS['wacf7_debug_ctx'] = $ctx;
}

function wacf7_debug_log( $message ){
	$ctx = isset($GLOBALS['wacf7_debug_ctx']) ? $GLOBALS['wacf7_debug_ctx'] : null;
	if ( ! $ctx || empty($ctx['enabled']) ) return;
	$line = '[wacf7] ' . $message;
	if ( $ctx['to'] === 'log' || $ctx['to'] === 'both' ) {
		error_log( $line );
	}
	$ctx['buffer'][] = $message;
	$GLOBALS['wacf7_debug_ctx'] = $ctx;
}

function wacf7_debug_maybe_flush_mail( $wpcf7 ){
	$ctx = isset($GLOBALS['wacf7_debug_ctx']) ? $GLOBALS['wacf7_debug_ctx'] : null;
	if ( ! $ctx || empty($ctx['enabled']) || $ctx['flushed'] ) return;
	if ( $ctx['to'] !== 'mail' && $ctx['to'] !== 'both' ) return;
	$mail = $wpcf7->prop('mail');
	$is_html = ! empty( $mail['use_html'] );
	$mail['body'] .= wacf7_debug_format_buffer( $ctx['buffer'], $is_html );
	$wpcf7->set_properties( array( 'mail' => $mail ) );
	$ctx['flushed'] = true;
	$GLOBALS['wacf7_debug_ctx'] = $ctx;
}

function wacf7_parse_truthy( $value ){
	$v = strtolower( trim( (string) $value ) );
	return in_array( $v, array('1','on','true','yes'), true );
}

function wacf7_debug_env_snapshot(){
	$parts = array();
	$parts[] = 'php=' . PHP_VERSION;
	if ( function_exists('get_bloginfo') ) {
		$parts[] = 'wp=' . get_bloginfo('version');
	}
	if ( defined('WPCF7_VERSION') ) {
		$parts[] = 'cf7=' . WPCF7_VERSION;
	}
	if ( class_exists('PhpOffice\\PhpWord\\PhpWord') ) {
		$parts[] = 'phpword=on';
	}
	$uploads = wp_upload_dir();
	$parts[] = 'uploads.basedir=' . ( isset($uploads['basedir']) ? $uploads['basedir'] : '' );
	$parts[] = 'uploads.baseurl=' . ( isset($uploads['baseurl']) ? $uploads['baseurl'] : '' );
	if ( function_exists('wpcf7_upload_tmp_dir') ) {
		$parts[] = 'cf7.tmp=' . wpcf7_upload_tmp_dir();
	}
	$parts[] = 'wacf7.templates=' . wacf7_get_template_dir();
	if ( defined('ABSPATH') ) { $parts[] = 'ABSPATH=' . ABSPATH; }
	if ( defined('WP_CONTENT_DIR') ) { $parts[] = 'WP_CONTENT_DIR=' . WP_CONTENT_DIR; }
	if ( defined('WP_PLUGIN_DIR') ) { $parts[] = 'WP_PLUGIN_DIR=' . WP_PLUGIN_DIR; }
	return implode('; ', $parts);
}

function wacf7_debug_format_buffer( array $buffer, $html = false ){
	if ( $html ) {
		$out  = '<hr><div style="font-family:monospace;font-size:12px;line-height:1.4">';
		$out .= '<strong>Debug report (wacf7)</strong><br/>';
		$envLine = '';
		if ( isset($buffer[0]) && str_starts_with($buffer[0], '[env] ') ) {
			$envLine = array_shift($buffer);
		}
		if ( $envLine ) {
			$rawEnv = substr($envLine, 6); // remove prefix '[env] '
			$envItems = array_filter( array_map( 'trim', explode(';', $rawEnv ) ) );
			$out .= '<div><strong>Environment</strong></div>';
			$out .= '<ul style="margin:4px 0 4px 16px;padding:0">';
			foreach( $envItems as $item ){
				$out .= '<li>' . esc_html($item) . '</li>';
			}
			$out .= '</ul>';
		}
		if ( $buffer ) {
			$out .= '<ul style="margin:4px 0 0 16px;padding:0">';
			foreach ( $buffer as $line ) {
				$out .= '<li>' . esc_html($line) . '</li>';
			}
			$out .= '</ul>';
		}
		$out .= '</div>';
		return $out;
	}
	// Plain text fallback
	$txt  = "\n\n--- Debug report (wacf7) ---\n";
	if ( isset($buffer[0]) && str_starts_with($buffer[0], '[env] ') ) {
		$envLine = array_shift($buffer);
		$rawEnv = substr($envLine, 6);
		$envItems = array_filter( array_map( 'trim', explode(';', $rawEnv ) ) );
		$txt .= "Environment:\n";
		foreach( $envItems as $item ){
			$txt .= '  - ' . $item . "\n";
		}
	}
	if ( $buffer ) {
		$txt .= "Steps:\n";
		foreach ( $buffer as $line ) {
			$txt .= '  - ' . $line . "\n";
		}
	}
	return $txt;
}
