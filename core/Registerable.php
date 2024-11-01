<?php
namespace UniqueHoverSliderPlus;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

// Require the parent theme class for type checks.
require_once('contracts/HandlesAssetsAndTranslateKey.php');
require_once('traits/WordpressHelpers.php');

use UniqueHoverSliderPlus\Contracts\HandlesAssetsAndTranslateKey;
use UniqueHoverSliderPlus\Traits\WordpressHelpers;

abstract class Registerable {

    use WordpressHelpers;

    /**
     * The type of registerable; either post_type or taxonomy.
     * @var string
     */
    public $type = '';

    /**
     * Only required if type is a taxonomy.
     * @var string
     */
    public $parent_post_type = '';

    /**
     * The post type name.
     * @var string
     */
    public $name = '';

    /**
     * Automatically translated labels.
     * @var array
     */
    public $labels = [];

    /**
     * Options besides the labels.
     * @var array
     */
    public $opts = [];

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
     * Directories that the user can extend.
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
     * Defualt hooks.
     * @var array
     */
    protected $_hooks = [];

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
     * If the post type is registered or not.
     * @var boolean
     */
    protected $registered = false;

    /**
     * The key used to mark translations.
     * @var string
     */
    protected $translate_key = '';

    /**
     * Slug copied from parent, in the case of Registerables only used to
     * determine the path to directories.
     * @var string
     */
    protected $slug = '';

    /**
     * Root copied from parent, in the case of Registerables only used to
     * determine the path to directories.
     * @var string
     */
    protected $root = '';

    /**
     * The parent class if applicable.
     * @var HandlesAssetsAndTranslateKey
     */
    protected $parent;

    /**
     * Options that should be resolved as callbacks.
     * @var array
     */
    protected $callback_opts = [];

    /**
     * Options that should be resolved as assets.
     * @var array
     */
    protected $asset_opts = [];

    /**
     * The possible types.
     * @var string
     */
    const POST_TYPE = 'post_type';
    const TAXONOMY = 'taxonomy';

    /**
     * Initializes the post type.
     */
    public function __construct(HandlesAssetsAndTranslateKey $parent = null)
    {
        // Set a default translate key if none is set.
        if (!$this->translate_key) {
            $this->translate_key = $this->name;
        }

        // If a valid theme was passed, we can set it as our parent theme.
        if ($parent) {
            $this->set_parent($parent);
        }

        // Some options have to be marked for translation before the plugin
        // is ready to register.
        $this->translate_text_opts();

        // Other options are assets and will have to be wrapped in an asset
        // call so that they point to the correct url.
        $this->wrap_asset_opts();

        // There's also some options that work as callbacks, and we'd like to have
        // the option to call a method of our class for those. In order to allow for
        // that, we need to wrap them as an array to include the class context.
        $this->wrap_callback_opts();

        // Register the directories, actions and filters to WP.
        $this->register_directories();
        $this->register_actions();

        // Call the register method to let WP know that we have a new post type.
        $this->register();

        // Trigger an action so that the user can hook into our post-registration
        // event.
        do_action($this->name . '_' . $this->type .  '_registered');
    }

    /**
     * Attaches a parent theme as property.
     * @param Theme $theme
     */
    public function set_parent(HandlesAssetsAndTranslateKey $parent)
    {
        $this->parent = $parent;

        // If the parent has a translate key, we copy it.
        if ($parent->get_translate_key()) {
            $this->translate_key = $parent->get_translate_key();
        }

        // Same goes for the slug.
        if ($parent->get_slug()) {
            $this->slug = $parent->get_slug();
        }

        // And for the root property.
        if ($parent->get_root()) {
            $this->root = $parent->get_root();
        }
    }

    /**
     * Registers the post type.
     * @return void
     */
    public function register()
    {
        // We only need to register the post type once.
        if (!$this->registered) {
            if ($this->is_post_type()) {
                register_post_type($this->name, $this->build_config());
            }

            if ($this->is_taxonomy()) {
                register_taxonomy($this->name, $this->parent_post_type, $this->build_config());
            }
            $this->registered = true;
        }
    }

    /**
     * Builds the WP config array for the post type.
     * @return array
     */
    public function build_config()
    {
        $config = $this->opts;
        $config['labels'] = $this->labels;

        return $config;
    }

    /**
     * Translates all properties that have to be marked for translation.
     * @return void
     */
    public function translate_text_opts()
    {
        // Translate all labels.
        foreach($this->labels as $key => $label) {
            $this->labels[$key] = __($label, $this->translate_key);
        }
    }

    /**
     * Wraps string callbacks to make sure that they're called as part of
     * this class. In case you actually want to call a stand-alone function;
     * tough luck, I won't allow you.
     * @return void
     */
    public function wrap_callback_opts()
    {
        foreach ($this->callback_opts as $opt) {
            if (
                array_key_exists($opt, $this->opts) &&
                is_string($this->opts[$opt])
            ) {
                $this->opts[$opt] = [$this, $this->opts[$opt]];
            }
        }
    }

    /**
     * Wraps assets with the parent's asset method.
     * @return void
     */
    public function wrap_asset_opts()
    {
        if ($this->parent) {
            foreach ($this->asset_opts as $opt) {
                if (
                    array_key_exists($opt, $this->opts) &&
                    is_string($this->opts[$opt])
                ) {
                    // Make sure the option has an image extention.
                    $exts = ['.png', '.jpg', '.jpeg', '.svg', '.gif'];
                    foreach ($exts as $ext) {
                        // Check if the position of the given extention is in the string, and at the very end.
                        if (strpos($this->opts[$opt], $ext) === strlen($this->opts[$opt]) - strlen($ext)) {
                            $this->opts[$opt] = $this->asset($this->opts[$opt]);
                        }
                    }
                }
            }
        }
    }

    /**
     * Are we a post type?
     * @return boolean
     */
    public function is_post_type()
    {
        return $this->type === self::POST_TYPE;
    }

    /**
     * Or are we taxonomy?
     * @return boolean
     */
    public function is_taxonomy()
    {
        return $this->type === self::TAXONOMY;
    }
}