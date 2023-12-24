<?php
    /**
     * @Filename: class-ninjahub_init.php
     * @Description:
     * @User: NINJA MASTER - Mustafa Shaaban
     * @Date: 1/4/2023
     */

    namespace NINJAHUB\APP\CLASSES;

    use Exception;
    use stdClass;

    /**
     * Description...
     *
     * @class Ninjahub_Init
     * @version 1.0
     * @since 1.0.0
     * @package NinjaHub
     * @author Mustafa Shaaban
     */
    class Ninjahub_Init
    {
        /**
         * @var array
         */
        public static array $obj = [];
        /**
         * @var null
         */
        protected static $instance = NULL;
        /**
         * @var \string[][][]
         */
        protected array $class_name = [];

        public function __construct()
        {
            $this->class_name = [
                'core'   => [
                    'Hooks'         => [
                        'type'      => 'helper',
                        'namespace' => 'NINJAHUB\APP\HELPERS',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/helpers/class-ninjahub_hooks.php'
                    ],
                    'Forms'         => [
                        'type'      => 'helper',
                        'namespace' => 'NINJAHUB\APP\HELPERS',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/helpers/class-ninjahub_forms.php'
                    ],
                    'Ajax_Response' => [
                        'type'      => 'helper',
                        'namespace' => 'NINJAHUB\APP\HELPERS',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/helpers/class-ninjahub_ajax_response.php'
                    ],
                    'Mail'          => [
                        'type'      => 'helper',
                        'namespace' => 'NINJAHUB\APP\HELPERS',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/helpers/class-ninjahub_mail.php'
                    ],
                    'Cryptor'       => [
                        'type'      => 'helper',
                        'namespace' => 'NINJAHUB\APP\HELPERS',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/helpers/class-ninjahub_cryptor.php'
                    ],
                    'Cron'          => [
                        'type'      => 'class',
                        'namespace' => 'NINJAHUB\APP\CLASSES',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/Classes/class-ninjahub_cron.php'
                    ],
                    'Post'          => [
                        'type'      => 'class',
                        'namespace' => 'NINJAHUB\APP\CLASSES',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/Classes/class-ninjahub_post.php'
                    ],
                    'Module'        => [
                        'type'      => 'abstract',
                        'namespace' => 'NINJAHUB\APP\CLASSES',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/Classes/class-ninjahub_module.php'
                    ],
                    'User'          => [
                        'type'      => 'class',
                        'namespace' => 'NINJAHUB\APP\CLASSES',
                        'path'      => NINJAHUB_CORE_PLUGIN_PATH . 'app/Classes/class-ninjahub_user.php'
                    ],

                ],
                'admin'  => [],
                'public' => []
            ];
        }

        /**
         * @return mixed|null
         */
        public static function get_instance()
        {
            $class = __CLASS__;
            if (!self::$instance instanceof $class) {
                self::$instance = new $class;
            }

            return self::$instance;
        }

        /**
         * @param $type
         * @param $class
         *
         * @return mixed|\stdClass
         */
        public static function get_obj($type, $class)
        {
            return array_key_exists($class, self::$obj[$type]) ? self::$obj[$type][$class] : new stdClass();
        }

        /**
         * @param $type
         *
         * @throws \Exception
         */
        public function run($type): void
        {
            echo "[SECOND]";
            if (array_key_exists($type, $this->class_name)) {
                foreach ($this->class_name[$type] as $class => $value) {
                    try {
                        if (!file_exists($value['path'])) {
                            throw new Exception("Your class path is invalid.");
                        }

                        require_once $value['path'];

                        if ('abstract' === $value['type'] || 'helper' === $value['type'] || 'widget' === $value['type']) {
                            continue;
                        }

                        $class_name = $value['namespace'] . "\Ninjahub_" . $class;
                        $class_name .= $type === 'admin' ? "_Admin" : "";

                        if (!class_exists("$class_name")) {
                            throw new Exception("Your class is not exists.");
                        }

                        self::$obj[$class] = new $class_name();

                    } catch (Exception $e) {
                        echo "<code>" . $e->getMessage() . "</code>";
                    }
                }
            }
        }
    }
