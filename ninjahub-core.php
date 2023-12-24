<?php

    /**
     * The plugin bootstrap file
     *
     * This file is read by WordPress to generate the plugin information in the plugin
     * admin area. This file also includes all of the dependencies used by the plugin,
     * registers the activation and deactivation functions, and defines a function
     * that starts the plugin.
     *
     * @link              https://https://github.com/MustafaShaaban/ninjahub-pro
     * @since             1.0.0
     * @package           Ninjahub_Core
     *
     * @wordpress-plugin
     * Plugin Name:       NinjaHub Core
     * Plugin URI:        https://https://github.com/MustafaShaaban/ninjahub-pro
     * Description:       NinjaHub Core is a plugin that has all core functions for ninjahub theme
     * Version:           1.0.0
     * Author:            Mustafa Shaaban
     * Author URI:        https://https://github.com/MustafaShaaban/ninjahub-pro/
     * License:           GPL-2.0+
     * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
     * Text Domain:       ninjahub-core
     * Domain Path:       /languages
     */

    // If this file is called directly, abort.
    if (!defined('WPINC')) {
        die;
    }

    /**
     * Currently plugin version.
     * Start at version 1.0.0 and use SemVer - https://semver.org
     * Rename this for your plugin and update it as you release new versions.
     */
    define('NINJAHUB_CORE_VERSION', '1.0.0');

    define('NINJAHUB_lANG', defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : 'en');

    define('NINJAHUB_CORE_PLUGIN_URL', plugin_dir_url(__FILE__));

    define('NINJAHUB_CORE_PLUGIN_PATH', plugin_dir_path(__FILE__));

    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/class-ninjahub-core-activator.php
     */
    function activate_ninjahub_core(): void
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-ninjahub-core-activator.php';
        Ninjahub_Core_Activator::activate();
    }

    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/class-ninjahub-core-deactivator.php
     */
    function deactivate_ninjahub_core(): void
    {
        require_once plugin_dir_path(__FILE__) . 'includes/class-ninjahub-core-deactivator.php';
        Ninjahub_Core_Deactivator::deactivate();
    }

    register_activation_hook(__FILE__, 'activate_ninjahub_core');
    register_deactivation_hook(__FILE__, 'deactivate_ninjahub_core');

    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path(__FILE__) . 'includes/class-ninjahub-core.php';

    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_ninjahub_core(): void
    {

        $plugin = new Ninjahub_Core();
        $plugin->run();

    }

    run_ninjahub_core();

