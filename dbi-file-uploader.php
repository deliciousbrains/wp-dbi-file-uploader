<?php
/**
 * Plugin Name: DBI File Uploader
 * Description: Upload large files using the JavaScript FileReader API
 * Author: Delicious Brains Inc
 * Version: 1.0
 * Author URI: http://deliciousbrains.com
 */

class DBI_File_Uploader {

	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_dashboard_setup', array( $this, 'add_dashboard_widget' ) );
		add_action( 'wp_ajax_dbi_upload_file', array( $this, 'ajax_upload_file' ) );
	}

	public function enqueue_scripts() {
		$src = plugins_url( 'dbi-file-uploader.js', __FILE__ );
		wp_enqueue_script( 'dbi-file-uploader', $src, array( 'jquery' ), false, true );
		wp_localize_script( 'dbi-file-uploader', 'dbi_vars', array(
			'upload_file_nonce' => wp_create_nonce( 'dbi-file-upload' ),
			)
		);
	}

	public function add_dashboard_widget() {
		wp_add_dashboard_widget( 'dbi_file_upload', 'DBI File Upload', array( $this, 'render_dashboard_widget' ) );
	}

	public function render_dashboard_widget() {
		?>
		<form>
			<p id="dbi-upload-progress">Please select a file and click "Upload" to continue.</p>

    		<input id="dbi-file-upload" type="file" name="dbi_import_file" /><br><br>

    		<input id="dbi-file-upload-submit" class="button button-primary" type="submit" value="Upload" />
		</form>
		<?php
	}

	public function ajax_upload_file() {
		check_ajax_referer( 'dbi-file-upload', 'nonce' );

		$wp_upload_dir = wp_upload_dir();
		$file_path     = trailingslashit( $wp_upload_dir['path'] ) . $_POST['file'];
		$file_data     = $this->decode_chunk( $_POST['file_data'] );

		if ( false === $file_data ) {
			wp_send_json_error();
		}

		file_put_contents( $file_path, $file_data, FILE_APPEND );

		wp_send_json_success();
	}

	public function decode_chunk( $data ) {
		$data = explode( ';base64,', $data );

		if ( ! is_array( $data ) || ! isset( $data[1] ) ) {
			return false;
		}

		$data = base64_decode( $data[1] );
		if ( ! $data ) {
			return false;
		}

		return $data;
	}

}

new DBI_File_Uploader();
