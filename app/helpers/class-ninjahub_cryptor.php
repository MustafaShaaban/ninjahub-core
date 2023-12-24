<?php
    /**
     * @Filename: class-ninjahub_cryptor.php
     * @Description:
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 5/13/2023
     */

    namespace NINJAHUB\APP\HELPERS;

    use Exception;

    /**
     * Description...
     *
     * @class Ninjahub_Cryptor
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author Mustafa Shaaban
     */
    class Ninjahub_Cryptor
    {
        // DECLARE THE REQUIRED VARIABLES
        public static $ENC_METHOD = "AES-256-CBC";      // THE ENCRYPTION METHOD.
        public static $ENC_KEY    = "nYUgczuK@JB5b*bX"; // ENCRYPTION KEY
        public static $ENC_IV     = "CeHHq3!@Uy!8-QET"; // ENCRYPTION IV.
        public static $ENC_SALT   = "xS$";              // THE SALT FOR PASSWORD ENCRYPTION ONLY.

        // DECLARE  REQUIRED VARIABLES TO CLASS CONSTRUCTOR
        function __construct($METHOD = NULL, $KEY = NULL, $IV = NULL, $SALT = NULL)
        {
            // Setting up the Encryption Method when needed.
            self::$ENC_METHOD = (!empty($METHOD)) ? $METHOD : self::$ENC_METHOD;
            // Setting up the Encryption Key when needed.
            self::$ENC_KEY = (!empty($KEY)) ? $KEY : self::$ENC_KEY;
            // Setting up the Encryption IV when needed.
            self::$ENC_IV = (!empty($IV)) ? $IV : self::$ENC_IV;
            // Setting up the Encryption IV when needed.
            self::$ENC_SALT = (!empty($SALT)) ? $SALT : self::$ENC_SALT;
        }

        /**
         * This function is responsible for decrypt url
         *
         * @param $url
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return array|string
         */
        public static function DecryptURL($url): array|string
        {
            if (empty(sanitize_text_field($url))) {
                return [];
            }

            $decrypted_url = self::Decrypt($url);
            parse_str($decrypted_url, $url_data);
            return $url_data;

        }

        /**
         *  This function is responsible for decrypt the encrypted strings
         *
         * @param $string
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return string|bool
         */
        public static function Decrypt($string): string|bool
        {
            if (empty(sanitize_text_field($string))) {
                return FALSE;
            }

            // hash
            $key = hash('sha256', self::$ENC_KEY);
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv = substr(hash('sha256', self::$ENC_IV), 0, 16);
            return openssl_decrypt(base64_decode(sanitize_text_field($string)), self::$ENC_METHOD, $key, 0, $iv);

        }

        /**
         * This function is responsible for encrypting passwords one way.
         *
         * @param $Input
         *
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         * @return string
         */
        public function EncryptPassword($Input): string
        {
            if (empty(sanitize_text_field($Input))) {
                return FALSE;
            }

            // GENERATE AN ENCRYPTED PASSWORD SALT
            $SALT = $this->Encrypt(self::$ENC_SALT);
            $SALT = md5($SALT);
            // PERFORM MD5 ENCRYPTION ON PASSWORD SALT.
            // ENCRYPT PASSWORD
            $Input = md5($this->Encrypt(md5($Input)));
            $Input = $this->Encrypt($Input);
            $Input = md5($Input);
            // PERFORM ANOTHER ENCRYPTION FOR THE ENCRYPTED PASSWORD + SALT.
            $Encrypted = $this->Encrypt($SALT) . $this->Encrypt($Input);
            $Encrypted = sha1($Encrypted . $SALT);
            // RETURN THE ENCRYPTED PASSWORD AS MD5
            return md5($Encrypted);
        }

        /**
         * This function is responsible for encrypting strings
         *
         * @param $string
         *
         * @return string|bool
         * @version 1.0
         * @since 1.0.0
         * @package NinjaHub
         * @author Mustafa Shaaban
         */
        public static function Encrypt($string): string|bool
        {
            if (empty(sanitize_text_field($string))) {
                return FALSE;
            }

            $key = hash('sha256', self::$ENC_KEY);
            // iv - encrypt method AES-256-CBC expects 16 bytes - else you will get a warning
            $iv     = substr(hash('sha256', self::$ENC_IV), 0, 16);
            $output = openssl_encrypt(sanitize_text_field($string), self::$ENC_METHOD, $key, 0, $iv);
            return base64_encode($output);
        }
    }