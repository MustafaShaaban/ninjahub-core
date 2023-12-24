<?php
    /**
     * @Filename: class-ninjahub_admin.php
     * @Description:
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 1/4/2023
     */


    namespace NINJAHUB\APP\MODELS\ADMIN;

    use NINJAHUB\APP\CLASSES\Ninjahub_Init;
    use NINJAHUB\APP\CLASSES\Ninjahub_User;
    use NINJAHUB\APP\HELPERS\Ninjahub_Hooks;
    use NINJAHUB\Ninjahub;

    /**
     * Description...
     *
     * @class Ninjahub_Admin
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author Mustafa Shaaban
     */
    class Ninjahub_Admin
    {

        /**
         * @var \NINJAHUB\APP\HELPERS\Ninjahub_Hooks
         */
        private Ninjahub_Hooks $hooks;


        /**
         * @param \NINJAHUB\APP\HELPERS\Ninjahub_Hooks $hooks
         */
        public function __construct(Ninjahub_Hooks $hooks)
        {
            $this->hooks = $hooks;
            $this->actions();
            $this->filters();
            Ninjahub_Init::get_instance()
                   ->run('admin');
        }

        public function actions(): void
        {
            $this->hooks->add_action('admin_enqueue_scripts', $this, 'enqueue_styles');
            $this->hooks->add_action('admin_enqueue_scripts', $this, 'enqueue_scripts');
            $this->hooks->run();
        }

        public function filters()
        {
            $this->hooks->add_filter('gglcptch_add_custom_form', $this, 'add_custom_recaptcha_forms', 10, 1);
            $this->hooks->run();
        }

        public function enqueue_styles(): void
        {
            $this->hooks->add_style(Ninjahub::_DOMAIN_NAME . '-admin-style-main', Ninjahub_Hooks::PATHS['admin']['css'] . '/style');
        }

        public function enqueue_scripts(): void
        {
            $this->hooks->add_script(Ninjahub::_DOMAIN_NAME . '-admin-script-main', Ninjahub_Hooks::PATHS['admin']['js'] . '/main', [ 'jquery' ]);
            $this->hooks->add_localization(Ninjahub::_DOMAIN_NAME . '-admin-script-main', 'nhGlobals', [
                'domain_key' => Ninjahub::_DOMAIN_NAME,
                'ajaxUrl'    => admin_url('admin-ajax.php'),
                'phrases'     => [
                    'default'        => __("This field is required.", "ninjahub"),
                    'email'          => __("Please enter a valid email address.", "ninjahub"),
                    'number'         => __("Please enter a valid number.", "ninjahub"),
                    'equalTo'        => __("Please enter the same value again.", "ninjahub"),
                    'maxlength'      => __("Please enter no more than {0} characters.", "ninjahub"),
                    'minlength'      => __("Please enter at least {0} characters.", "ninjahub"),
                    'max'            => __("Please enter a value less than or equal to {0}.", "ninjahub"),
                    'min'            => __("Please enter a value greater than or equal to {0}.", "ninjahub"),
                    'pass_regex'     => __("Your password must contain at least one lowercase letter, one uppercase letter, one digit, and one special character from the following: ! @ # $ % ^ & *.", "ninja"),
                    'phone_regex'    => __("Please enter a valid Phone number.", "ninja"),
                    'intlTelNumber'  => __("Please enter a valid International Telephone Number.", "ninja"),
                    'email_regex'    => __("Please enter a valid email address.", "ninja"),
                    'file_extension' => __("Please upload a file with a valid extension.", "ninja"),
                    'file_max_size'  => __("File size must be less than {0} KB", "ninja"),
                    'choices_select' => __("Press to select", "ninja"),
                    'noChoicesText'  => __("'No choices to choose from'", "ninja"),
                    'time_regex'     => __("Invalid time range format. Please use HH:mm AM/PM - HH:mm AM/PM", "ninja"),
                    'englishOnly'   => __("Only English text is allowed.", "ninja"),
                    'arabicOnly'   => __("Only Arabic text is allowed.", "ninja"),
                ]
            ]);
            $this->hooks->run();

        }

        /**
         * Description...
         *
         * @param $forms
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function add_custom_recaptcha_forms($forms)
        {
            $forms['platform_login']           = [ "form_name" => "Platform Login" ];
            $forms['platform_registration']    = [ "form_name" => "Platform Registration" ];
            $forms['platform_reset_password']  = [ "form_name" => "Platform Reset Password" ];
            $forms['platform_forgot_password'] = [ "form_name" => "Platform Forgot Password" ];
            $forms['attachment_handler']       = [ "form_name" => "Attachments Handler" ];
            return $forms;
        }

    }
