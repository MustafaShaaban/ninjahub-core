<?php
    /**
     * @Filename: class-ninjahub_file_uploader.php
     * @Description:
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 29/6/2023
     */

    namespace NINJAHUB\APP\CLASSES;

    use NINJAHUB\APP\HELPERS\Ninjahub_Ajax_Response;
    use NINJAHUB\APP\HELPERS\Ninjahub_Cryptor;
    use NINJAHUB\APP\HELPERS\Ninjahub_Hooks;
    use NINJAHUB\Ninjahub;

    /**
     * Class to handle file uploads using ajax cross all the platform
     * @requirenments [Ninjahub_Cryptor, Ninjahub_Forms, Recaptcha, JS Files]
     *
     * @class Ninjahub_File_Uploader
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author  - Mustafa Shaaban
     */
    class Ninjahub_File_Uploader
    {
        private Ninjahub_Hooks $hooks;

        const MAX_SIZE_UPLOAD = 5242880;


        public function __construct()
        {
            $this->hooks = new Ninjahub_Hooks();
            $this->actions();
            $this->filters();
            $this->hooks->run();
        }

        protected function actions(): void
        {
            // TODO: Implement actions() method.
            $this->hooks->add_action('wp_enqueue_scripts', $this, 'enqueue_scripts', 10);
            $this->hooks->add_action('wp_ajax_' . Ninjahub::_DOMAIN_NAME . '_upload_attachment', $this, 'upload');
            $this->hooks->add_action('wp_ajax_' . Ninjahub::_DOMAIN_NAME . '_remove_attachment', $this, 'remove');
        }

        protected function filters(): void
        {
            // TODO: Implement filters() method.
        }

        public function enqueue_scripts(): void
        {
            if (is_page([
                'create-team',
                'fitting-room',
            ])) {
                $this->hooks->add_script(Ninjahub::_DOMAIN_NAME . '-public-script-uploader', Ninjahub_Hooks::PATHS['public']['js'] . '/uploader-front', [Ninjahub::_DOMAIN_NAME . '-public-script-main']);
                $this->hooks->add_localization(Ninjahub::_DOMAIN_NAME . '-public-script-uploader', 'nhUploadGlobals', [
                    'max_upload_size'  => self::MAX_SIZE_UPLOAD,
                    'phrases'     => [
                    ]
                ]);

            }
            $this->hooks->run();

        }

        public function upload(): void
        {
            $file                          = $_FILES;

            if (empty($_POST['g-recaptcha-response'])) {
                new Ninjahub_Ajax_Response(FALSE, __('The reCaptcha verification failed. Please try again.', 'ninja'));/* the reCAPTCHA answer  */
            }

            $check_result = apply_filters('gglcptch_verify_recaptcha', TRUE, 'string', 'attachment_handler');

            if ($check_result !== TRUE) {
                new Ninjahub_Ajax_Response(FALSE, __($check_result, 'ninja'));/* the reCAPTCHA answer  */
            }

            if (!empty($file)) {

                if (($_FILES['file']['size'] >= self::MAX_SIZE_UPLOAD) || ($_FILES["file"]["size"] == 0)) {
                    new Ninjahub_Ajax_Response(FALSE, __("File too large. File must be less than 5 megabytes.", 'ninja'));
                }

                $acceptable = apply_filters('ninjahub_file_uploader_accepted_extensions', [
                    'image/jpeg',
                    'image/jpg',
                    'image/png'
                ]);
                
                if ((!in_array($_FILES['file']['type'], $acceptable)) && (!empty($_FILES["file"]["type"]))) {
                    new Ninjahub_Ajax_Response(FALSE, __("Invalid file type.", 'ninja'));
                }
                $upload     = wp_upload_bits($file['file']['name'], NULL, file_get_contents($file['file']['tmp_name']));


                if (!empty($upload['error'])) {
                    new Ninjahub_Ajax_Response(FALSE, __($upload['error'], 'ninja'));
                }

                $wp_filetype = wp_check_filetype(basename($upload['file']), NULL);

                $wp_upload_dir = wp_upload_dir();

                $attachment = [
                    'guid'           => $wp_upload_dir['baseurl'] . _wp_relative_upload_path($upload['file']),
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => preg_replace('/\.[^.]+$/', '', basename($upload['file'])),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ];

                $attach_id = wp_insert_attachment($attachment, $upload['file']);

                $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);

                wp_update_attachment_metadata($attach_id, $attach_data);

                wp_upload_bits($file['file']["name"], NULL, file_get_contents($file['file']["tmp_name"]));

                new Ninjahub_Ajax_Response(TRUE, __('Attachment has been uploaded successfully.', 'ninja'), [
                    'attachment_ID' => Ninjahub_Cryptor::Encrypt($attach_id)
                ]);
            } else {
                new Ninjahub_Ajax_Response(FALSE, __("Can't upload empty file", 'ninja'));
            }
        }

        public function remove(): void
        {
            $attachment_id = Ninjahub_Cryptor::Decrypt(sanitize_text_field($_POST['attachment_id']));

            if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
                new Ninjahub_Ajax_Response(FALSE, __('The reCaptcha verification failed. Please try again.', 'ninja'));/* the reCAPTCHA answer  */
            }

            $check_result = apply_filters('gglcptch_verify_recaptcha', TRUE, 'string', 'attachment_handler');

            if ($check_result !== TRUE) {
                new Ninjahub_Ajax_Response(FALSE, __($check_result, 'ninja'));/* the reCAPTCHA answer  */
            }

            $deleted = wp_delete_attachment($attachment_id);

            if (!$deleted) {
                new Ninjahub_Ajax_Response(FALSE, __("Can't remove attachment", 'ninja'));
            } else {
                new Ninjahub_Ajax_Response(TRUE, __("Attachment has been removed successfully", 'ninja'));
            }
        }
    }
