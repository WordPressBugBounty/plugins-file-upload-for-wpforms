<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Upload file input field.
 *
 * @since 1.0.0
 */
class FILEUPFO_WPForms_Field_File extends WPForms_Field {

	public $time_now = '';
	public $attachments = [];

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function init() {
		$this->name  = esc_html__( 'Upload File', 'file-upload-for-wpforms' );
		$this->type  = 'file';
		$this->icon  = 'fa-upload';
		$this->order = 1000;

        add_filter('wpforms_get_form_fields_allowed', [$this, 'form_fields_allowed']);
        add_action('wpforms_ajax_submit_before_processing', [$this, 'before_processing']);
		add_filter('wpforms_emails_send_email_data', [$this, 'add_attachments']);

	}


    public function before_processing(){

		$this->time_now = time().bin2hex( random_bytes(5) );
		$upload_dir     = wp_upload_dir();
		$uplod_dirpath  = $upload_dir['baseurl'].'/wpxform-uploads';

		$files = isset($_FILES['wpforms']['name']['fields']) ? $_FILES['wpforms']['name']['fields'] : [];

		foreach($files  as $field_id => $value){
			if( empty( $value ) ) 
				continue;
			$value    = sanitize_file_name( $value );
			$new_file = $uplod_dirpath.'/'.$this->time_now.'-'.$field_id.'-'.$value;
			$_POST['wpforms']['fields'][ $field_id ] = $new_file;
		}
    }


    public function form_fields_allowed( $fields ){
        $fields[] = 'file';
        return $fields;
    }
 

	/**
	 * Field options panel inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_options( $field ) {
		/*
		 * Basic field options.
		 */

		// Options open markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'open',
			]
		);

		// Label.
		$this->field_option( 'label', $field );

		// Description.
		$this->field_option( 'description', $field );

        $this->upload_file_options( 'accepted_file_types', $field );

        $this->upload_file_options( 'max_size', $field );

        $this->upload_file_options( 'add_to_email', $field );

		// Required toggle.
		$this->field_option( 'required', $field );

		// Options close markup.
		$this->field_option(
			'basic-options',
			$field,
			[
				'markup' => 'close',
			]
		);



		/*
		 * Advanced field options.
		 */

		// Options open markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'open',
			]
		);       

		// Custom CSS classes.
		$this->field_option( 'css', $field );

		// Hide label.
		$this->field_option( 'label_hide', $field );

		// Options close markup.
		$this->field_option(
			'advanced-options',
			$field,
			[
				'markup' => 'close',
			]
		);

		$ignore_field_notice = get_option('fileupfo_view_ignore_field_notice');
		if( empty($ignore_field_notice)){
			echo '<div id="filenzo-notice" style="display:flex; align-items:center; justify-content:space-between; margin:20px; padding:15px; border-radius:7px; background:#E0E8F0">
				<p style="margin:0;">Unlock the full power of <strong>Filenzo Pro</strong> — 
					<a href="https://wpdebuglog.com/get-filenzo-pro/" target="_blank" style="text-decoration:none">
						<span>Upgrade for free today!</span>
					</a>
				</p>
				<a href="' . $this->get_dismiss_admin_url() . '" class="wpforms-dismiss-button" style="display:inline-flex; align-items:center; justify-content:center; width:24px; height:24px; cursor:pointer; padding-left:5px;text-decoration:none"></a>
			</div>';
		}


	}

	private function get_dismiss_admin_url() {
		$scheme = is_ssl() ? 'https://' : 'http://';
		$host = $_SERVER['HTTP_HOST'];
		$request = $_SERVER['REQUEST_URI']; 
		return $scheme . $host . $request . '&fileupfo-ignore-field-notice=1';
	}

	/**
	 * Field preview inside the builder.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field Field settings.
	 */
	public function field_preview( $field ) {

		// Define data.
		$placeholder   = ! empty( $field['placeholder'] ) ? $field['placeholder'] : '';
		$default_value = ! empty( $field['default_value'] ) ? $field['default_value'] : '';

		// Label.
		$this->field_preview_option( 'label', $field );

		// Primary input.
		echo '<input type="file" placeholder="' . esc_attr( $placeholder ) . '" value="' . esc_attr( $default_value ) . '" class="primary-input" readonly>';

		// Description.
		$this->field_preview_option( 'description', $field );

	}


    public function upload_file_options( $option, $field ){

        switch( $option ){
            case 'accepted_file_types':

                $lbl = $this->field_element(
                    'label',
                    $field,
                    [
                        'slug'    => 'supported_files',
                        'value'   => esc_html__( 'Acceptable file types', 'file-upload-for-wpforms' ),
                        'tooltip' => esc_html__( 'Pipe-separated file types list.', 'file-upload-for-wpforms' ),
                    ],
                    false
                );

                $fld = $this->field_element(
                    'text',
                    $field,
                    [
                        'slug'    => 'supported_files',
                        'value'   => empty( $field['supported_files'] ) ? 'jpg|jpeg|png|gif|pdf|doc|docx|ppt|pptx|odt|avi|ogg|m4a|mov|mp3|mp4|mpg|wav|wmv' : $field['supported_files'],
                    ],
                    false
                );
            
                $args = [
                    'slug'    => 'supported_files',
                    'content' => $lbl . $fld,
                ];
            
                $this->field_element( 'row', $field, $args, true );
                break;

            case 'max_size':

                $lbl = $this->field_element(
                    'label',
                    $field,
                    [
                        'slug'    => 'max_size',
                        'value'   => esc_html__( 'File size limit', 'file-upload-for-wpforms' ),
                        'tooltip' => esc_html__( 'In bytes. You can use kb and mb suffixes.', 'file-upload-for-wpforms' ),
                    ],
                    false
                );

                $fld = $this->field_element(
                    'text',
                    $field,
                    [
                        'slug'    => 'max_size',
                        'value'   => empty( $field['max_size'] ) ? '1mb' : $field['max_size'],
                    ],
                    false
                );
            
                $args = [
                    'slug'    => 'max_size',
                    'content' => $lbl . $fld,
                ];
            
                $this->field_element( 'row', $field, $args, true );
                break;
			
			case 'add_to_email':
				$value   = isset( $field['add_to_email'] ) ? esc_attr( $field['add_to_email'] ) : 1;
				$tooltip = esc_html__( 'Check this box to include the uploaded file in email notifications.', 'file-upload-for-wpforms' );

				$output = $this->field_element(
					'toggle',
					$field,
					[
						'slug'    => 'add_to_email',
						'value'   => $value,
						'desc'    => esc_html__( 'Add to Email Attachment', 'file-upload-for-wpforms' ),
						'tooltip' => $tooltip,
					],
					false
				);

				$this->field_element(
					'row',
					$field,
					[
						'slug'    => 'required',
						'content' => $output,
					],
					true
				);
				break;
        }
    }

	/**
	 * Field display on the form front-end.
	 *
	 * @since 1.0.0
	 *
	 * @param array $field      Field settings.
	 * @param array $deprecated Deprecated.
	 * @param array $form_data  Form data and settings.
	 */
	public function field_display( $field, $deprecated, $form_data ) {

		// Define data.
		$primary = $field['properties']['inputs']['primary'];

		if ( isset( $field['limit_enabled'] ) ) {
			$limit_count = isset( $field['limit_count'] ) ? absint( $field['limit_count'] ) : 0;
			$limit_mode  = isset( $field['limit_mode'] ) ? sanitize_key( $field['limit_mode'] ) : 'characters';

			$primary['data']['form-id']  = $form_data['id'];
			$primary['data']['field-id'] = $field['id'];
		}

		// Primary field.
		printf(
			'<input type="file" %s %s>',
			wpforms_html_attributes( $primary['id'], $primary['class'], $primary['data'], $primary['attr'] ),
			$primary['required'] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

 
	/**
	 * Validate field on form submit.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $field_id     Field ID.
	 * @param mixed $field_submit Submitted field value (raw data).
	 * @param array $form_data    Form data and settings.
	 */
	public function validate( $field_id, $field_submit, $form_data ) {

        $files = $_FILES;

        if ( empty( $form_data['fields'][ $field_id ] ) ) {
			return;
		}

		$field                = $form_data['fields'][ $field_id ];
		$max_size             = (int) $this->get_size_option( $field['max_size'] );  
		$max_size_limit       = $this->get_size_option( $field['max_size'] );  
		$file_name            = $files['wpforms']['name']['fields'][$field_id] ?? '';
		$file_name            = sanitize_file_name( $file_name );
		$tmp_name             = $files['wpforms']['tmp_name']['fields'][$field_id] ?? '';
		$acceptable_filetypes = explode( '|', $field['supported_files']);

        if( empty($field['required']) && empty($file_name) ){
            return; 
        }

        if( ( ! empty($field['required']) ) && empty($file_name) ){
            wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = sprintf( __( 'This field is required', 'file-upload-for-wpforms' ));
            return; 
        }

        if( $max_size < $files['wpforms']['size']['fields'][$field_id]){
			/* translators: %s is the maximum allowed file size. */
            wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = sprintf( __( 'Files size can\'t exceed %s .', 'file-upload-for-wpforms' ), esc_attr( $max_size_limit ) );
            return;
        }

        $last_period_pos = strrpos( $file_name, '.' );

        if ( false === $last_period_pos ) { 
            wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = __( 'You are not allowed to upload files of this type.', 'file-upload-for-wpforms' );
            return;
        }

        $suffix = strtolower( substr( $file_name, $last_period_pos ) );
        $suffix = str_replace( '.', '', $suffix );

        if ( ! in_array( $suffix, $acceptable_filetypes, true ) ) {
            wpforms()->obj( 'process' )->errors[ $form_data['id'] ][ $field_id ] = __( 'You are not allowed to upload files of this type.', 'file-upload-for-wpforms' );
            return;
        }

        $upload_dir    = wp_upload_dir();
        $uplod_dirpath = $upload_dir['basedir'].'/wpxform-uploads';
		$new_file      = $uplod_dirpath.'/'.$this->time_now.'-'.$field_id.'-'.$file_name;

		$queue = FILEUPFO_Move_Queue::getInstance();
		$queue->form_id = $form_data['id'];
		$queue_files = $queue->get_files();
		$queue_files[ $field_id ] = [ $tmp_name, $new_file];
		$queue->update_files( $queue_files );

		if( !empty( $field['add_to_email'] ) ){
			$this->attachments[] = $new_file;
		}
	}

	public function add_attachments( $data ){
		$attachments = $data['attachments'] ?? [];
		$attachments = array_merge($attachments, $this->attachments);
		$data['attachments'] = $attachments;
		return $data;
	}


    public function get_size_option( $val, $default_value = MB_IN_BYTES ) {
		$pattern = '/^([1-9][0-9]*)([kKmM]?[bB])?$/';
        $matches = false;

        preg_match( $pattern, $val, $matches );

        if ( $matches ) {
			$size = (int) $matches[1];

			if ( ! empty( $matches[2] ) ) {
				$kbmb = strtolower( $matches[2] );

				if ( 'kb' === $kbmb ) {
					$size *= KB_IN_BYTES;
				} elseif ( 'mb' === $kbmb ) {
					$size *= MB_IN_BYTES;
				}
			}

			return $size;
		}

		return (int) $default_value;
	}

}

add_action('init', 'fileupfo_init_instance');

function fileupfo_init_instance(){
    new FILEUPFO_WPForms_Field_File();
}