<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload file input field.
 *
 * @since 1.0.8
 */
class FILEUPFO_Move_Queue {

    private static $instance = null;

    private $files = [];

    public $form_id;

    private function __construct() {

        add_action( 'wpforms_process_entry_save',  [$this, 'process_files'] );

    }


    public static function getInstance(): FILEUPFO_Move_Queue {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }



    public function update_files( $files ){
        $this->files = $files;
    }


    public function get_files(){
        return $this->files;
    }


    public function process_files( $fields ){

        global $wp_filesystem;

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		WP_Filesystem();

		if ( ! $wp_filesystem ) {
			return new WP_Error( 'filesystem_error', 'Could not access filesystem.' );
		}
        
        $form_id = $this->form_id;

        foreach($this->files as $field_id => $file_val ){

            $tmp_name = $file_val[0]; 
            $new_file = $file_val[1]; 

            if ( ! $wp_filesystem->move( $tmp_name, $new_file ) ) {
                wpforms()->obj( 'process' )->errors[ $form_id ][ $field_id ] = __( 'Uploading error.', 'file-upload-for-wpforms' );
                return;
            }

            $wp_filesystem->chmod( $new_file, FS_CHMOD_FILE );
        }

    }


}

