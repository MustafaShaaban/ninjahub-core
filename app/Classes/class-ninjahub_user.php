<?php
    /**
     * @Filename: class-ninjahub_user.php
     * @Description: This file contains the definition of the Ninjahub_User class.
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 1/4/2023
     */


    namespace NINJAHUB\APP\CLASSES;

    use NINJAHUB\APP\HELPERS\Ninjahub_Cryptor;
    use NINJAHUB\APP\HELPERS\Ninjahub_Hooks;
    use NINJAHUB\APP\HELPERS\Ninjahub_Mail;
    use NINJAHUB\APP\MODELS\FRONT\MODULES\Ninjahub_Profile;
    use NINJAHUB\Ninjahub;
    use WP_Error;
    use WP_User;

    /**
     * Description...
     *
     * @class Ninjahub_User
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author Mustafa Shaaban
     */
    class Ninjahub_User
    {
        /**
         * Default Meta
         */
        const USER_DEFAULTS = [
            // User Profile ID
            'profile_id'                  => 0,
            // Profile Picture URL
            'avatar_id'                   => 0,
            // If account verified at all
            'account_verification_status' => 0,
            // Default user language
            'site_language'               => 'en'
        ];
        /**
         * USER ROLES
         */
        const ADMIN       = 'administrator';
        const CMS         = 'cmsmanager';
        /**
         * NINJAHUB USER INSTANCE
         *
         * @var object|null
         */
        private static ?object $instance = NULL;
        /**
         * The User ID
         *
         * @since 1.0.0
         * @var int
         */
        public int $ID = 0;
        /**
         * The User Username
         *
         * @since 1.0.0
         * @var string
         */
        public string $username = '';
        /**
         * The User Password
         *
         * @since 1.0.0
         * @var string
         */
        public string $password = '';
        /**
         * The User Email
         *
         * @since 1.0.0
         * @var string
         */
        public string $email = '';
        /**
         * The User First name
         *
         * @since 1.0.0
         * @var string
         */
        public string $first_name = '';
        /**
         * The User Last name
         *
         * @since 1.0.0
         * @var string
         */
        public string $last_name = '';
        /**
         * The User Nickname
         *
         * @since 1.0.0
         * @var string
         */
        public string $nickname = '';
        /**
         * The User Displayed name
         *
         * @since 1.0.0
         * @var string
         */
        public string $display_name = '';
        /**
         * The User Avatar url
         *
         * @since 1.0.0
         * @var array|null|string
         */
        public string|array|null $avatar;
        /**
         * The User single role as
         *
         * @since 1.0.0
         * @var string
         */
        public string $role = '';
        /**
         * The User Status (Active or Not)
         *
         * @since 1.0.0
         * @var int
         */
        public int $status = 0;
        /**
         * The User Registered date
         *
         * @since 1.0.0
         * @var string
         */
        public string $registered = '0000-00-00 00:00:00';
        /**
         * The User Activation key
         *
         * @since 1.0.0
         * @var string
         */
        public string $activation_key = '';

        /**
         * @var \NINJAHUB\APP\MODELS\FRONT\MODULES\Ninjahub_Profile
         */
        public Ninjahub_Profile $profile;

        /**
         * The User Meta data
         *
         * @since 1.0.0
         * @var array|string[]
         */
        public array $user_meta = [
            'first_name',
            'last_name',
            'nickname',
            'phone_number',
            'reset_password_key',
            'verification_key',
            'verification_expire_date',
        ];

        /**
         * Constructs a new instance of the Ninjahub_User class.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function __construct()
        {
            // Reformat class metadata
            foreach ($this->user_meta as $k => $meta) {
                $this->user_meta[$meta] = '';
                unset($this->user_meta[$k]);
            }
        }

        /**
         * Magic method to retrieve the value of a property.
         *
         * @param string $name The name of the property.
         *
         * @return mixed The value of the property if it exists, or FALSE otherwise.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function __get($name)
        {
            return property_exists($this, $name) ? $this->{$name} : FALSE;
        }

        /**
         * Magic method to set the value of a property.
         *
         * @param string $name The name of the property.
         * @param mixed  $value The value to set.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function __set($name, $value)
        {
            $this->{$name} = sanitize_text_field($value);
        }

        /**
         * Retrieves the instance of the Ninjahub_User class.
         *
         * @return Ninjahub_User The instance of the Ninjahub_User class.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public static function get_instance(): Ninjahub_User
        {
            $class = __CLASS__;
            if (!self::$instance instanceof $class) {
                self::$instance = new $class;
            }

            return self::$instance;
        }

        /**
         * Checks if a user has a specific role.
         *
         * @param string $role_name The name of the role to check.
         * @param int    $id The ID of the user. Defaults to 0, which indicates the current user.
         *
         * @return bool True if the user has the role, False otherwise.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public static function is_role(string $role_name, int $id = 0): bool
        {
            return in_array($role_name, self::get_user_role($id, FALSE));
        }

        /**
         * Retrieves the role(s) of a user.
         *
         * @param int  $id The ID of the user. Defaults to 0, which indicates the current user.
         * @param bool $single Whether to return a single role or an array of roles.
         *
         * @return string|array The role(s) of the user.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public static function get_user_role(int $id = 0, bool $single = TRUE): string|array
        {
            global $user_ID;
            $ID   = ($id !== 0 && is_numeric($id)) ? $id : $user_ID;
            $role = [];
            if (!empty($ID) && is_numeric($ID)) {
                $user_meta = get_userdata($ID);
                return $role = ($single) ? $user_meta->roles[0] : $user_meta->roles;
            }
            return $role;
        }

        /**
         * Retrieves the current user as a Ninjahub_User object.
         *
         * @return Ninjahub_User The current user.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public static function get_current_user(): Ninjahub_User
        {
            global $current_user;
            return self::get_user($current_user);
        }

        /**
         * Inserts a new user into the database.
         *
         * @return Ninjahub_User|int|WP_Error The inserted user object or an error object.
         * @throws \Exception
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function insert(): Ninjahub_User|int|WP_Error
        {
            $error = new WP_Error(); // Create a new WordPress error object.

            if (username_exists($this->username)) { // Check if the username already exists.
                $error->add('username_exists', __('Sorry, this phone number already exists!', 'ninja'), [
                    'status'  => FALSE,
                    'details' => [ 'username' => $this->username ]
                ]); // Add an error message to the error object.
                return $error; // Return the error object.
            }

            if (email_exists($this->email)) { // Check if the email already exists.
                $error->add('email_exists', __('Sorry, that email already exists!', 'ninja'), [
                    'status'  => FALSE,
                    'details' => [ 'email' => $this->email ]
                ]); // Add an error message to the error object.
                return $error; // Return the error object.
            }

            $user_id = wp_insert_user([
                'user_login'   => $this->username,
                'user_pass'    => $this->password,
                'user_email'   => $this->email,
                'first_name'   => $this->first_name,
                'last_name'    => $this->last_name,
                'display_name' => $this->display_name,
                'role'         => $this->role
            ]); // Insert a new user into the system and get the user ID.

            if (is_wp_error($user_id)) { // Check if there was an error during user insertion.
                return $user_id; // Return the error object.
            }

            $this->ID = $user_id; // Set the user ID for the current object.

            $avatar = $this->set_avatar(); // Set the avatar for the user.

            if ($avatar->has_errors()) { // Check if there were errors setting the avatar.
                return $avatar; // Return the error object.
            }

            $user_meta = array_merge($this->user_meta, self::USER_DEFAULTS); // Merge the user meta data with default values.

            foreach ($user_meta as $key => $value) { // Loop through each user meta data.
                $value = property_exists($this, $key) ? $this->{$key} : $value; // Get the value from the current object property or use the default value.
                add_user_meta($this->ID, $key, $value); // Add user meta data for the current user.
            }

            $profile         = new Ninjahub_Profile(); // Create a new Ninjahub_Profile object.
            $profile->title  = $this->display_name; // Set the profile title.
            $profile->author = $this->ID; // Set the profile author.
            $profile->insert(); // Insert the profile into the system.

            update_user_meta($this->ID, 'profile_id', $profile->ID); // Update user meta data with the profile ID.

            $this->setup_verification(); // Set up user verification.

            $cred = [
                'user_login'    => $this->username,
                'user_password' => $this->password
            ]; // Set the credentials for signing in.

            $login = wp_signon($cred); // Sign in the user.

            if (is_wp_error($login)) { // Check if there was an error during sign in.
                $error->add('invalid_register_signOn', __($login->get_error_message(), 'ninja'), [
                    'status'  => FALSE,
                    'details' => [
                        'user_login' => $this->username,
                        'password'   => $this->password
                    ]
                ]); // Add an error message to the error object.
                return $error; // Return the error object.
            }

            do_action(Ninjahub::_DOMAIN_NAME . "_after_create_user", $this); // Trigger an action after user creation.

            return $this; // Return the current user object.
        }

        /**
         * Updates the user's information.
         *
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_User|\WP_Error The updated user object or an error object.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function update(): Ninjahub_User|WP_Error
        {
            global $current_user;

            $error = new WP_Error(); // Create a new WordPress error object.

            if (strtolower($current_user->data->user_login) !== strtolower($this->username)) {
                // Check if the current user's username is different from the username being updated.

                if (username_exists($this->username)) { // Check if the new username already exists.
                    $error->add('username_exists', __('Sorry, this phone number already exists!', 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'username' => $this->username ]
                    ]); // Add an error message to the error object.
                    return $error; // Return the error object.
                }

                global $wpdb;

                // Update the user's username in the WordPress database using $wpdb.
                $wpdb->update($wpdb->users, [ 'user_login' => $this->username ], [ 'user_login' => $current_user->data->user_login ]);
            }

            $user_id = wp_update_user([
                'ID'           => $this->ID,
                'user_email'   => $this->email,
                'first_name'   => ucfirst(strtolower($this->first_name)),
                'last_name'    => ucfirst(strtolower($this->last_name)),
                'display_name' => ucfirst(strtolower($this->first_name)) . ' ' . ucfirst(strtolower($this->last_name)),
                'role'         => $this->role
            ]); // Update the user's information using wp_update_user function.

            if (is_wp_error($user_id)) { // Check if there was an error during user update.
                return $user_id; // Return the error object.
            }

            if (is_array($this->avatar) && !empty($this->avatar)) {
                // Check if the avatar property is an array and not empty.

                $avatar = $this->set_avatar(); // Set the avatar for the user.

                if ($avatar->has_errors()) { // Check if there were errors setting the avatar.
                    return $avatar;
                }
            }

            $this->profile->title = $this->display_name; // Update the profile title.
            $this->profile->update(); // Update the profile information.

            foreach ($this->user_meta as $key => $value) {
                update_user_meta($this->ID, $key, $value); // Update the user meta data.
            }

            return $this; // Return the current user object.
        }

        /**
         * Uploading user profile picture and setting it as metadata.
         *
         * @return \WP_Error The error object if an error occurs during avatar upload, otherwise returns an empty error object.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        private function set_avatar(): WP_Error
        {
            $error = new WP_Error(); // Create a new WordPress error object.

            if (is_array($this->avatar) && !empty($this->avatar)) {
                // Check if the avatar property is an array and not empty.

                $mimes = [
                    'jpe'  => 'image/jpeg',
                    'jpeg' => 'image/jpeg',
                    'jpg'  => 'image/jpeg',
                    'png'  => 'image/png'
                ]; // Define an array of allowed mime types.

                $overrides = [
                    'mimes'     => $mimes,
                    'test_form' => FALSE
                ]; // Set the overrides for the file upload.

                // Upload the avatar file using wp_handle_upload function.
                $upload = wp_handle_upload($this->avatar, $overrides);

                if (isset($upload['error'])) {
                    $error->add('invalid_image', __($upload['error'], 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'file' => $this->avatar ]
                    ]); // Add an error message to the error object if an error occurs during upload.
                    return $error;
                }

                $image_url  = $upload['url']; // Get the URL of the uploaded image.
                $upload_dir = wp_upload_dir(); // Get the upload directory information.
                $image_data = file_get_contents($image_url); // Get the image data.
                $filename   = basename($image_url); // Get the filename from the image URL.

                if (wp_mkdir_p($upload_dir['path'])) {
                    $file = $upload_dir['path'] . '/' . $filename;
                } else {
                    $file = $upload_dir['basedir'] . '/' . $filename;
                } // Set the file path based on the upload directory.

                // Save the image data to the file.
                file_put_contents($file, $image_data);

                $wp_filetype = wp_check_filetype($filename, NULL); // Get the file type.
                $attachment  = [
                    'post_mime_type' => $wp_filetype['type'],
                    'post_title'     => sanitize_file_name($filename),
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                ]; // Set the attachment details.

                // Insert the attachment into the database.
                $attachment_id = wp_insert_attachment($attachment, $file);

                if (is_wp_error($attachment_id)) {
                    return $attachment_id; // Return the error object if there was an error during attachment insertion.
                }

                // Generate and update the attachment metadata.
                $attach_data = wp_generate_attachment_metadata($attachment_id, $file);
                wp_update_attachment_metadata($attachment_id, $attach_data);

                // Set the user meta data for 'avatar_id' with the attachment ID.
                $this->set_user_meta('avatar_id', $attachment_id);

                // Get the URL of the avatar using the attachment ID.
                $this->avatar = wp_get_attachment_image_url($attachment_id, 'thumbnail');

            } else {
                // Set a default avatar if no avatar is provided.
                $this->avatar = Ninjahub_Hooks::PATHS['public']['img'] . '/default-profile.png';
            }

            return $error; // Return the error object.
        }

        /**
         * Sets the user meta data.
         *
         * @param string       $name The name of the user meta data.
         * @param string|array $value The value of the user meta data.
         * @param bool         $update Whether to update the user meta data in the database.
         *
         * @return bool Returns true if the user meta data is successfully set, otherwise false.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function set_user_meta(string $name, string|array $value, bool $update = FALSE): bool
        {
            // Check if the user meta key exists in the user meta array.
            if (array_key_exists($name, $this->user_meta)) {
                $this->user_meta[$name] = $value;

                // Update the user meta data in the database if $update is true.
                if ($update) {
                    update_user_meta($this->ID, $name, $value);
                }

                return TRUE;
            }

            return FALSE;
        }

        /**
         * Initiates the forgot password process for a user.
         *
         * @param string $user_email_phone The email or phone of the user.
         *
         * @throws \Exception
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_User|\WP_Error|$this The current user object, an error object, or $this.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function forgot_password(string $user_email_phone): Ninjahub_User|WP_Error
        {
            // Get the user by email.
            $user = get_user_by('email', $user_email_phone);

            if (!$user) {
                // If user not found by email, get the user by login.
                $user = get_user_by('login', $user_email_phone);
            }

            if ($user) {
                // Generate the forgot password data for the user.
                $generate_forgot_data = $this->generate_forgot_password_data($user);

                // Send the forgot password email.
                $email = Ninjahub_Mail::init()
                                ->to($user->user_email)
                                ->subject('Forgot Password')
                                ->template('forgot-password/body', [
                                    'data' => [
                                        'user'      => $user,
                                        'url_query' => $generate_forgot_data['reset_link']
                                    ]
                                ])
                                ->send();


            }
            return $this; // Return the current user object.
        }

        /**
         * Generates forgot password data for a user.
         *
         * @param object $user The user object.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @return array The generated forgot password data.
         */
        public function generate_forgot_password_data($user): array
        {
            $reset_key = wp_generate_password(20, FALSE);
            // Generate a reset key.

            $reset_data = [
                'user_id'         => $user->ID,
                'reset_key'       => $reset_key,
                'expiration_time' => time() + (1 * 3600)
                // 1 hour
            ]; // Set the reset data.

            $encrypted_data = Ninjahub_Cryptor::Encrypt(serialize($reset_data));
            // Encrypt the reset data.

            $reset_link = add_query_arg([
                'user' => $user,
                'key'  => $encrypted_data
            ], site_url('my-account/reset-password'));
            // Generate the reset link.

            update_user_meta($user->ID, 'reset_password_key', $reset_data);
            // Update the user meta data with the reset data.

            return [
                'reset_data' => $reset_data,
                'reset_link' => $reset_link
            ]; // Return the generated forgot password data.
        }

        /**
         * Changes the user's password.
         *
         * @return bool|\WP_Error Returns true if the password is changed successfully, otherwise returns an error object.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function change_password(): bool|WP_Error
        {
            $error         = new WP_Error(); // Create a new WordPress error object.
            $form_data     = $_POST['data']; // Get the form data.
            $user_password = sanitize_text_field($form_data['user_password']); // Sanitize the user password.
            $key           = sanitize_text_field($form_data['user_key']); // Sanitize the reset key.

            if (!is_wp_error(self::check_reset_code($key))) {
                // Check if the reset code is valid.
                $decrypt_data = Ninjahub_Cryptor::Decrypt($key);
                // Decrypt the reset key.

                if ($decrypt_data) {

                    $reset_data = unserialize($decrypt_data);
                    // Unserialize the decrypted reset data.

                    // Change user password
                    wp_set_password($user_password, $reset_data['user_id']);
                    // Change the user's password.

                    // Remove reset key
                    update_user_meta($reset_data['user_id'], 'reset_password_key', '');
                    // Remove the reset password key from user meta data.

                    return TRUE; // Return true indicating successful password change.

                } else {
                    $error->add('failed_decryption', __("Your reset key is invalid!.", 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'key' => $key ]
                    ]); // Add an error message to the error object if decryption fails.
                    return $error; // Return the error object.
                }
            } else {
                $error->add('invalid_key', __("Your reset key is invalid!.", 'ninja'), [
                    'status'  => FALSE,
                    'details' => [ 'key' => $key ]
                ]); // Add an error message to the error object if the reset key is invalid.
                return $error; // Return the error object.
            }
        }


        /**
         * Check the reset code validity
         *
         * @param $key : The reset code
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return bool|\WP_Error Returns true if the reset code is valid, otherwise returns a WP_Error object
         */
        public static function check_reset_code($key): bool|WP_Error
        {
            $error        = new WP_Error(); // Create a new WP_Error object
            $decrypt_data = Ninjahub_Cryptor::Decrypt($key); // Decrypt the reset code

            if ($decrypt_data && is_serialized($decrypt_data)) {
                $reset_data = unserialize($decrypt_data); // Unserialize the decrypted data
                $user       = self::get_user_by('ID', (int)$reset_data['user_id']); // Get the user associated with the reset code

                if (!is_wp_error($user)) {
                    if (is_array($user->user_meta['reset_password_key']) && !empty($user->user_meta['reset_password_key'])) {
                        if ($reset_data['reset_key'] === $user->user_meta['reset_password_key']['reset_key']) {
                            $current_timestamp = time(); // Get the current Unix timestamp

                            if ($reset_data['expiration_time'] >= $current_timestamp) {
                                return TRUE; // The reset code is valid
                            } else {
                                // The reset key has expired
                                $error->add('expire_date', __("Your reset key is expired.", 'ninja'), [
                                    'status'  => FALSE,
                                    'details' => [ 'time' => $reset_data['expiration_time'] ]
                                ]);
                                return $error; // Return the WP_Error object
                            }
                        } else {
                            // The reset key is invalid
                            $error->add('invalid_key', __("Your reset key is invalid!.", 'ninja'), [
                                'status'  => FALSE,
                                'details' => [ 'key' => $reset_data['reset_key'] ]
                            ]);
                            return $error; // Return the WP_Error object
                        }
                    } else {
                        // The reset key is empty or invalid
                        $error->add('empty_key', __("Your reset key is invalid!.", 'ninja'), [
                            'status'  => FALSE,
                            'details' => [ 'key' => $reset_data['reset_key'] ]
                        ]);
                        return $error; // Return the WP_Error object
                    }
                } else {
                    // The user associated with the reset key is invalid
                    $error->add('invalid_user', __("Your reset key is invalid!.", 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'user' => $reset_data['user_id'] ]
                    ]);
                    return $error; // Return the WP_Error object
                }
            } else {
                // The reset key could not be decrypted
                $error->add('failed_decryption', __("Your reset key is invalid!.", 'ninja'), [
                    'status'  => FALSE,
                    'details' => [ 'key' => $key ]
                ]);
                return $error; // Return the WP_Error object
            }
        }

        /**
         * Get user as a Nh User object
         *
         * @param \WP_User $user The WP_User object
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_User The Nh User object
         */
        public static function get_user(WP_User $user): Ninjahub_User
        {
            $class          = __CLASS__;
            self::$instance = new $class();

            return self::$instance->convert($user);
        }

        /**
         * Get user by a specific field and value
         *
         * @param string $field The field to search by (e.g., 'ID', 'login', 'email')
         * @param string $value The value to search for
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_User|\WP_Error The Nh User object if found, otherwise returns a WP_Error object
         */
        public static function get_user_by(string $field, string $value): Ninjahub_User|WP_Error
        {
            $error = new WP_Error(); // Create a new WP_Error object
            $user  = get_user_by($field, $value); // Get the user by the specified field and value

            if ($user) {
                return self::get_user($user); // Get the Nh User object
            } else {
                // The user does not exist
                $error->add('invalid_user', __("This user does not exist!.", 'ninja'), [
                    'status'  => FALSE,
                    'details' => [
                        'user'  => $value,
                        'field' => $field
                    ]
                ]);
                return $error; // Return the WP_Error object
            }
        }

        /**
         * Convert the default WP user object to a Nh User object
         *
         * @param \WP_User $user The WP_User object to convert
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return \NINJAHUB\APP\CLASSES\Ninjahub_User The converted Nh User object
         */
        private function convert(WP_User $user): Ninjahub_User
        {
            $class    = __CLASS__;
            $new_user = new $class(); // Create a new Nh User object

            $new_user->ID             = $user->ID;
            $new_user->username       = $user->data->user_login;
            $new_user->password       = $user->data->user_pass;
            $new_user->email          = $user->data->user_email;
            $new_user->first_name     = $this->first_name;
            $new_user->last_name      = $this->last_name;
            $new_user->nickname       = $this->nickname;
            $new_user->display_name   = $user->data->display_name;
            $new_user->role           = $user->roles[0];
            $new_user->status         = $user->data->user_status;
            $new_user->registered     = $user->data->user_registered;
            $new_user->activation_key = $user->data->user_activation_key;

            $new_user->user_meta = array_merge($new_user->user_meta, self::USER_DEFAULTS);

            foreach ($new_user->user_meta as $key => $meta) {
                $new_user->user_meta[$key] = get_user_meta($user->ID, $key, TRUE);
            }

            $new_user->first_name = $new_user->user_meta['first_name'];
            $new_user->last_name  = $new_user->user_meta['last_name'];
            $new_user->nickname   = $new_user->user_meta['nickname'];
            $new_user->avatar     = $new_user->get_avatar();

            if (class_exists('\NINJAHUB\APP\MODELS\FRONT\MODULES\Ninjahub_Profile')) {
                $profile_obj       = new Ninjahub_Profile();
                $new_user->profile = $profile_obj;
                $profile           = $profile_obj->get_by_id((int)$new_user->user_meta['profile_id']);
                if (!is_wp_error($profile)) {
                    $new_user->profile = $profile_obj->get_by_id((int)$new_user->user_meta['profile_id']);
                }
            }

            return $new_user;
        }

        /**
         * Assign WP_User properties to Ninjahub_User
         *
         * @param \WP_User $user The WP_User object to assign properties from
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return void
         */
        private function assign(WP_User $user): void
        {
            $this->ID             = $user->ID;
            $this->username       = $user->data->user_login;
            $this->password       = $user->data->user_pass;
            $this->email          = $user->data->user_email;
            $this->display_name   = $user->data->display_name;
            $this->role           = $user->roles[0];
            $this->status         = $user->data->user_status;
            $this->registered     = $user->data->user_registered;
            $this->activation_key = $user->data->user_activation_key;

            $this->user_meta = array_merge($this->user_meta, self::USER_DEFAULTS);

            foreach ($this->user_meta as $key => $meta) {
                $this->user_meta[$key] = get_user_meta($user->ID, $key, TRUE);
            }

            $this->first_name = $this->user_meta['first_name'];
            $this->last_name  = $this->user_meta['last_name'];
            $this->nickname   = $this->user_meta['nickname'];
            $this->avatar     = $this->get_avatar();
        }

        /**
         * Perform login
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @return \WP_Error|\static Returns a WP_Error object if the login is invalid, otherwise returns $this
         * @throws \Exception
         */
        protected function login(): Ninjahub_User|WP_Error
        {
            $error         = new WP_Error(); // Create a new WP_Error object
            $form_data     = $_POST['data']; // Get the form data from the POST request
            $user_login    = sanitize_text_field($form_data['user_login']); // Sanitize the user login field
            $user_password = sanitize_text_field($form_data['user_password']); // Sanitize the user password field

            $user = get_user_by('login', $user_login); // Get the user by login

            if (empty($user)) {
                $user = get_user_by('email', $user_login); // Get the user by email
                if (empty($user)) {
                    // The login credentials are invalid
                    $error->add('invalid_username', __("Your login credentials are invalid.", 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'username' => $user_login ]
                    ]);
                    return $error; // Return the WP_Error object
                }
            }

            if (!empty($user)) {
                $this->assign($user); // Assign properties from WP_User to Ninjahub_User

                $check_pwd = wp_check_password($user_password, $this->password, $this->ID); // Check if the password is valid

                if (!$check_pwd) {
                    // The login credentials are invalid
                    $error->add('invalid_password', __("Your login credentials are invalid.", 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'password' => $user_password ]
                    ]);
                    return $error; // Return the WP_Error object
                }

                $cred = [
                    'user_login'    => $user_login,
                    'user_password' => $user_password
                ];

                if (!empty($form_data['rememberme'])) {
                    $cred['remember'] = $form_data['rememberme'];
                }

                $login = wp_signon($cred); // Sign in the user

                if (is_wp_error($login)) {
                    // The sign-on process failed
                    $error->add('invalid_signOn', __($login->get_error_message(), 'ninja'), [
                        'status'  => FALSE,
                        'details' => [
                            'user_login' => $user_login,
                            'password'   => $user_login
                        ]
                    ]);
                    return $error; // Return the WP_Error object
                }

                if (!$this->is_confirm()) {
                    // The account is not confirmed, send verification code
                    $this->setup_verification();
                    $error->add('account_verification', __("Your account is pending! Please check your E-mail to activate your account.", 'ninja'), [
                        'status'  => FALSE,
                        'details' => [ 'email' => $this->user_meta['account_verification_status'] ]
                    ]);
                    return $error; // Return the WP_Error object
                }


                $profile_id = get_user_meta($login->ID, 'profile_id', TRUE);
                if (!$profile_id) {
                    // The account is disabled or blocked
                    $error->add('invalid_profile', __("This account is temporarily disabled or blocked. Please contact us.", 'ninja'), [
                        'status' => FALSE
                    ]);
                    return $error; // Return the WP_Error object
                }
            }

            return $this;
        }

        /**
         * Sets up the verification process.
         *
         *
         * @throws \Exception
         * @return \WP_Error|bool The WP_Error object or a boolean value indicating the success of the verification setup.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public function setup_verification(): WP_Error|bool
        {
            $error = new WP_Error(); // Create a new WordPress error object.
            // For other verification types, send the email OTP code.
            $verification = $this->send_email_code();

            if (!$verification) {
                // If sending the email OTP code failed, add the error to the error object and return it.
                $error->add('email_error', __("The verification code didn't send!", 'ninja'), [
                    'status'  => FALSE,
                    'details' => [
                        'email_error' => 'email error',
                    ]
                ]);
                return $error;
            }

            return $verification; // Return the verification result.
        }

        /**
         * Sends the email OTP code.
         *
         * @param string $type The type of OTP code.
         *
         * @return bool The boolean value indicating the success of sending the OTP code.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @throws \Exception
         */
        public function send_email_code(): bool
        {
            $randomNumber = mt_rand(1000, 9999); // Generate a random OTP code.


            // If the type is verification, update the user meta data and send the verification email.
            $this->set_user_meta('account_verification_status', 0, TRUE);
            $this->set_user_meta('verification_key', $randomNumber, TRUE);
            $this->set_user_meta('verification_expire_date', time() + (5 * 60), TRUE);

            $email = Ninjahub_Mail::init()
                            ->to($this->email)
                            ->subject('Welcome to Nh - Please Verify Your Email')
                            ->template('account-verification/body', [
                                'data' => [
                                    'user'   => $this,
                                    'digits' => $randomNumber
                                ]
                            ])
                            ->send();


            return $email; // Return the result of sending the email.
        }

        /**
         * Returns the avatar URL for the user.
         *
         * @return string The URL of the avatar image.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        private function get_avatar(): string
        {
            $url = wp_get_attachment_image_url($this->user_meta['avatar_id'], 'thumbnail');
            return empty($url) ? Ninjahub_Hooks::PATHS['public']['img'] . '/default-profile.webp' : $url;
        }

        /**
         * Checks if the user is confirmed.
         *
         * @return bool The boolean value indicating if the user is confirmed.
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        private function is_confirm(): bool
        {
            if (empty($this->user_meta['account_verification_status']) || !boolval($this->user_meta['account_verification_status'])) {
                // If the account verification status is empty or false, the user is not confirmed.
                return FALSE;
            }

            return TRUE; // Otherwise, the user is confirmed.
        }

    }
