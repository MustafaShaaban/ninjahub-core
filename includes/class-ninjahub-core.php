    <?php

        /**
         * The file that defines the core plugin class
         *
         * A class definition that includes attributes and functions used across both the
         * public-facing side of the site and the admin area.
         *
         * @link       https://https://github.com/MustafaShaaban/ninjahub-pro
         * @since      1.0.0
         *
         * @package    Ninjahub_Core
         * @subpackage Ninjahub_Core/includes
         */

        use NINJAHUB\APP\CLASSES\Ninjahub_Init;
        use NINJAHUB\APP\HELPERS\Ninjahub_Hooks;

        /**
         * The core plugin class.
         *
         * This is used to define internationalization, admin-specific hooks, and
         * public-facing site hooks.
         *
         * Also maintains the unique identifier of this plugin as well as the current
         * version of the plugin.
         *
         * @since      1.0.0
         * @package    Ninjahub_Core
         * @subpackage Ninjahub_Core/includes
         * @author     Mustafa Shaaban <Mustafashaaban22@gmail.com>
         */
        class Ninjahub_Core
        {
            private static NULL|Ninjahub_Core $instance = NULL;

            /**
             * The loader that's responsible for maintaining and registering all hooks that power
             * the plugin.
             *
             * @since    1.0.0
             * @access   protected
             * @var      Ninjahub_Hooks $loader Maintains and registers all hooks for the plugin.
             */
            protected Ninjahub_Hooks $loader;

            /**
             * The unique identifier of this plugin.
             *
             * @since    1.0.0
             * @access   protected
             * @var      string $plugin_name The string used to uniquely identify this plugin.
             */
            protected string $plugin_name;

            /**
             * The current version of the plugin.
             *
             * @since    1.0.0
             * @access   protected
             * @var      string $version The current version of the plugin.
             */
            protected string $version;

            /**
             * Define the core functionality of the plugin.
             *
             * Set the plugin name and the plugin version that can be used throughout the plugin.
             * Load the dependencies, define the locale, and set the hooks for the admin area and
             * the public-facing side of the site.
             *
             * @since    1.0.0
             */
            public function __construct()
            {
                if (defined('NINJAHUB_CORE_VERSION')) {
                    $this->version = NINJAHUB_CORE_VERSION;
                } else {
                    $this->version = '1.0.0';
                }
                $this->plugin_name = 'ninjahub-core';

                $this->load_dependencies();
                $this->set_locale();

            }

            public static function getInstance()
            {
                if (self::$instance === NULL) {
                    self::$instance = new Ninjahub_Core();
                }
                return self::$instance;
            }

            /**
             * Load the required dependencies for this plugin.
             *
             * Include the following files that make up the plugin:
             *
             * - Ninjahub_Core_Loader. Orchestrates the hooks of the plugin.
             * - Ninjahub_Core_i18n. Defines internationalization functionality.
             * - Ninjahub_Core_Admin. Defines all hooks for the admin area.
             * - Ninjahub_Core_Public. Defines all hooks for the public side of the site.
             *
             * Create an instance of the loader which will be used to register the hooks
             * with WordPress.
             *
             * @since    1.0.0
             * @access   private
             */
            private function load_dependencies(): void
            {

                /**
                 * The class responsible for defining internationalization functionality
                 * of the plugin.
                 */
                require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-ninjahub-core-i18n.php';

                /**
                 * The class responsible for init all classes.
                 */
                require_once plugin_dir_path(dirname(__FILE__)) . 'app/Classes/class-ninjahub_init.php';

                /**
                 * The Basic Class
                 */
                require_once plugin_dir_path(dirname(__FILE__)) . 'app/class-ninjahub.php';

                /**
                 * The class responsible for defining all actions that occur in the admin area.
                 */
                require_once plugin_dir_path(dirname(__FILE__)) . 'app/Models/admin/class-ninjahub_admin.php';

                /**
                 * The class responsible for defining all actions that occur in the public-facing
                 * side of the site.
                 */
                require_once plugin_dir_path(dirname(__FILE__)) . 'app/Models/public/class-ninjahub_public.php';

                Ninjahub_Init::get_instance()
                             ->run('core');

                $this->loader = new Ninjahub_Hooks();
            }

            /**
             * Define the locale for this plugin for internationalization.
             *
             * Uses the Ninjahub_Core_i18n class in order to set the domain and to register the hook
             * with WordPress.
             *
             * @since    1.0.0
             * @access   private
             */
            private function set_locale(): void
            {

                $plugin_i18n = new Ninjahub_Core_i18n();

                $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');

            }


            /**
             * Run the loader to execute all of the hooks with WordPress.
             *
             * @since    1.0.0
             */
            public function run(): void
            {
                $this->loader->run();
            }

            /**
             * The name of the plugin used to uniquely identify it within the context of
             * WordPress and to define internationalization functionality.
             *
             * @since     1.0.0
             * @return    string    The name of the plugin.
             */
            public function get_plugin_name(): string
            {
                return $this->plugin_name;
            }

            /**
             * Retrieve the version number of the plugin.
             *
             * @since     1.0.0
             * @return    string    The version number of the plugin.
             */
            public function get_version(): string
            {
                return $this->version;
            }

        }
