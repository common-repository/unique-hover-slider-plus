<?php
// Namespaced to prevent class conflict. **RENAME FOR PLUGIN**
namespace UniqueHoverSliderPlus;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

require_once('traits/RegistersMetaFields.php');
require_once('traits/WordpressHelpers.php');
require_once('contracts/HandlesAssetsAndTranslateKey.php');

use UniqueHoverSliderPlus\Contracts\HandlesAssetsAndTranslateKey;
use UniqueHoverSliderPlus\Traits\WordpressHelpers;
use UniqueHoverSliderPlus\Traits\RegistersMetaFields;

abstract class Plugin implements HandlesAssetsAndTranslateKey
{
    use WordpressHelpers, RegistersMetaFields;

    /**
     * The plugin name.
     * @var string
     */
    public $name = 'My Plugin';

    /**
     * A shortened name for menu displays.
     * @var string
     */
    public $short_name = 'MP';

    /**
     * The plugin slug.
     * @var string
     */
    public $slug = 'my-plugin';

    /**
     * The theme version.
     * @var string
     */
    public $version = '0.0.0';

    /**
     * The user capability required to edit this plugin.
     * @var string
     */
    public $capability = 'manage_options';

    /**
     * The translation key used to mark strings for translation.
     * @var string
     */
    protected $translate_key = '';

    /**
     * The directory we should use as main directory.
     * @var string
     */
    protected $root = 'plugins';

        /**
     * Top level menu pages to add.
     * @var array
     */
    protected $admin_menu_pages = [];

    /**
     * Submenu pages to add.
     * @var array
     */
    protected $admin_submenu_pages = [];

    /**
     * Default options.
     * @var array
     */
    protected $_options = [];

    /**
     * Options to automatically register for the plugin.
     * @var array
     */
    protected $options = [];

    /**
     * Defualt hooks.
     * @var array
     */
    protected $_hooks = [
        ['admin_menu', 'menus'],
        ['init', 'init_language'],
        ['wp_enqueue_scripts', 'assets'],
        ['add_meta_boxes', 'register_meta_fields'],
        ['plugins_loaded', 'check_plugins'],
    ];

    /**
     * A list of hooks and their method registration.
     * @var array
     */
    protected $hooks = [];

    /**
     * Default filters.
     * @var array
     */
    protected $_filters = [];

    /**
     * A list of filters and their method registration.
     * @var array
     */
    protected $filters = [];

    /**
     * Default directories.
     * @var array
     */
    protected $_directories = [
        'root' => '/',
        'assets' => '/assets',
        'styles' => '/assets/css',
        'fonts' => '/assets/fonts',
        'images' => '/assets/images',
        'scripts' => '/assets/js',

        'languages' => '/languages',
        'includes' => '/includes',
        'templates' => '/templates',
        'framework' => '/framework',
        'helpers' => '/framework/helpers',
        'admin' => '/framework/admin',
        'post_types' => '/framework/post-types',
        'taxonomies' => '/framework/taxonomies',
        'shortcodes' => '/framework/shortcodes',
        'loops' => '/framework/loops',
        'integrations' => '/framework/integrations',
    ];

    /**
     * A list of directories.
     * @var array
     */
    protected $directories = [];

    /**
     * The default URI's, can be extended by the uris
     * property below it.
     * @var array
     */
    protected $_uris = [
        'plugin' => '/',
        'assets' => '/assets',
        'images' => '/images',
    ];

    /**
     * A list of URI's.
     * @var array
     */
    protected $uris = [];

    /**
     * Directories of which all files should automatically be included.
     * @var array
     */
    protected $autoload = [
        'helpers',
        'post_types',
        'taxonomies',
        'shortcodes',
        'loops',
        'integrations',
    ];

    /**
     * Post type classes to register.
     * @var array
     */
    protected $post_types = [];

    /**
     * Taxonomy classes to register.
     * @var array
     */
    protected $taxonomies = [];

    /**
     * Shortcodes to be registered.
     * @var string
     */
    protected $shortcodes = [];

    /**
     * All registered class instances.
     * @var array
     */
    protected $_registered = [];

    /**
     * Meta fields to register.
     * @var array
     */
    protected $meta_fields = [];

    /**
     * Plugins that are required / recommended for the theme. You can check
     * them at any point using $this->has_plugin($key).
     * @var array
     */
    protected $plugins = [];

    /**
     * Errors to display in the admin panel.
     * @var array
     */
    protected $admin_errors = [];

    /**
     * Notices to display in the admin panel.
     * @var array
     */
    protected $admin_notices = [];

    /**
     * Merges the URI and directory listings. Register all options,
     * actions and shortcodes. When finished call the boot method to
     * allow the user to take over post-initialization.
     */
    public function __construct()
    {
        // Generate the nonces for meta fields.
        $this->generate_nonces_and_field_names();

        // Boot the WP Helper trait.
        $this->init_wp_helpers();

        // Call a boot method to indicate that we're ready.
        $this->boot();
    }

    /**
     * Check all plugins when they're done loading.
     * @return void
     */
    public function check_plugins()
    {
        // Check all of the plugins required for this theme.
        $this->register_plugins();

        // Register notices and errors at the end of everything, so that by
        // default the initialization notices will already be set, and will
        // always be displayed correctly.
        $this->register_messages();
    }

    /**
     * Method that has to be called to add assets. Automatically
     * called on 'wp_enqueue_scripts' hook.
     * @hook   wp_enqueue_scripts
     * @return void
     */
    public function assets() {
        // For the user to implement.
    }

    /**
     * Will be called after the theme is done setting things up. Can
     * be used as an extention of __construct() of sorts.
     * @return void
     */
    public function boot() {
        // For the user to implement.
    }
}
