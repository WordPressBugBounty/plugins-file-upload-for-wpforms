<?php
/*
Plugin Name: File Upload For WPForms - Filenzo
Description: Adds a file upload field to WPForms.
Version: 1.1.0
Author: WPDebugLog
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 6.6
Requires PHP: 7.0
Text Domain: file-upload-for-wpforms
*/

if ( ! defined( 'ABSPATH' ) ) exit;  

add_action('wpforms_loaded', 'fileupfo_wpform_upload_input', 99);
function fileupfo_wpform_upload_input(){
    require plugin_dir_path( __FILE__ ) . '/upload.php';
    require plugin_dir_path( __FILE__ ) . '/move-queue.php';
}


function fileupfo_wpform_upload_on_activate(){
    global $wp_filesystem;

    $upload_dir    = wp_upload_dir();
    $dirpath = $upload_dir['basedir'].'/wpxform-uploads';
    if ( ! file_exists( $dirpath ) ) {
        wp_mkdir_p( $dirpath );
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }
        WP_Filesystem();
        $wp_filesystem->put_contents( $dirpath.'/index.php', '<?php //Silence is golden.', FS_CHMOD_FILE );
    }
}
register_activation_hook( __FILE__, 'fileupfo_wpform_upload_on_activate' );


add_action('admin_notices', 'fileupfo_check_wpforms_installed');
function fileupfo_check_wpforms_installed() { 

    if( function_exists('wpforms') )
        return;

    $error_message = sprintf(
        /* translators: 1. open anchor tag, 2. close */
        esc_html__( 'File Upload For WPForms requires %1$sWPForms%2$s installed & activated!' , 'file-upload-for-wpforms' ),
        '<a target="_blank" href="https://wordpress.org/plugins/wpforms-lite/">',
        '</a>',
    );
    
    $message  = '<div class="error">';
    $message .= sprintf( '<p>%s</p>', $error_message );
    $message .= '</div>';

    echo wp_kses_post( $message );
}


add_action( 'admin_notices', 'fileupfo_admin_notice' );
add_action('admin_init', 'fileupfo_view_ignore_notice' );

function fileupfo_admin_notice() {

    $install_date = get_option( 'fileupfo_view_install_date', '');

    if( empty( $install_date ) ){ 
        $install_date = date('Y-m-d G:i:s');
        add_option( 'fileupfo_view_install_date', $install_date );
    }

    $install_date = date_create( $install_date );
    $date_now     = date_create( date('Y-m-d G:i:s') );
    $date_diff    = date_diff( $install_date, $date_now );

    if ( $date_diff->format("%d") < 7 ) {

        return false;
    }

    if ( ! get_option( 'fileupfo_view_ignore_notice' ) ) {

        echo '<div class="updated"><p>';

        printf(
        __( 'Thanks for using <a href="https://wordpress.org/plugins/file-upload-for-wpforms/" target="_blank">File Upload For WPForms</a> for over a week! 🙌 Enjoying it? Please consider a <strong>5-star rating</strong> on WordPress.  ⭐ <a href="%2$s" target="_blank">Yes, happy to!</a> | <a href="%1$s">Already did</a> | <a href="%1$s">Not yet</a>', 
            'file-upload-for-wpforms' 
        ),       
        add_query_arg('fileupfo-ignore-notice', 0, admin_url()),
            'https://wordpress.org/support/plugin/file-upload-for-wpforms/reviews/?filter=5'
        );
        echo "</p></div>";
    }
}

function fileupfo_view_ignore_notice() {

    if ( isset($_GET['fileupfo-ignore-notice']) && '0' == $_GET['fileupfo-ignore-notice'] ) {

        update_option( 'fileupfo_view_ignore_notice', 'true' );
    }
    if ( !empty($_GET['fileupfo-ignore-field-notice'])) {

        update_option( 'fileupfo_view_ignore_field_notice', 'true' );
    }
}

