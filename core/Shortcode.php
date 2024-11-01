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
use WP_Query;

abstract class Shortcode {
    use WordpressHelpers;

    /**
     * The shortcode name.
     * @var string
     */
    public $name = '';

    /**
     * The callback method that will render the shortcode.
     * @var string
     */
    public $callback = 'render';

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
     * Default URI's.
     * @var array
     */
    protected $_uris = [];

    /**
     * URI's that the user can extend.
     * @var array
     */
    protected $uris = [
        'root' => '/',
        'assets' => '/assets',
        'styles' => '/assets/css',
        'fonts' => '/assets/fonts',
        'images' => '/assets/images',
        'scripts' => '/assets/js',
    ];

    /**
     * Defualt hooks.
     * @var array
     */
    protected $_hooks = [
        ['vc_before_init', 'vc_map'],
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
     * Plugins that are required / recommended for the theme. You can check
     * them at any point using $this->has_plugin($key).
     * @var array
     */
    protected $plugins = [
        'visual_composer' => [
            'name' => 'Visual Composer',
            'function' => 'vc_map',
            'required' => false,
            'show_message' => false,
        ],
    ];

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
     * The parent class if applicable.
     * @var HandlesAssetsAndTranslateKey
     */
    protected $parent;

    /**
     * Visual composer options that should be marked for translation.
     * @var array
     */
    protected $vc_text_options = [
        'name',
        'description',
        'category',
        'heading',
    ];

    /**
     * Visual composer options that should be fetched from assets/images.
     * @var array
     */
    protected $vc_image_options = [
        'icon',
    ];

    /**
     * Query params that cannot be overwritten through the shortcode.
     * @var array
     */
    public $_query = [];

    /**
     * Query params that can be overwritten through the shortcode.
     * @var array
     */
    public $query = [];

    /**
     * Default values of attributes if they're not passed as param.
     * @var array
     */
    public $default_attributes = [];

    /**
     * Initializes the shortcode.
     * @event '*_shortcode_registered'
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

        // Check all of the plugins required for this theme.
        $this->register_plugins();

        // Register the directories to the class.
        $this->register_directories();
        $this->register_actions();

        // Add the shortcode to WP.
        $this->add_shortcode($this->name, 'pre_callback');

        // Register notices and errorsat the end of everything, so that by
        // default the initialization notices will already be set, and will
        // always be displayed correctly.
        $this->register_messages();

        // Trigger an action so that the user can hook into our post-registration
        // event.
        do_action($this->name . '_shortcode_registered');
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
     * Maps the shortcode to visual composer if the props are set.
     * @return void
     */
    public function vc_map()
    {
        // Make sure we actually have some settings to map.
        if (!empty($this->vc_options)) {
            // Parse the options.
            $this->translate_text_opts();
            $this->replace_image_opts();

            // Map the shortcode to visual composer.
            if ($this->has_plugin('visual_composer')) {
                vc_map($this->vc_options);
            }
        }
    }

    /**
     * Marks all visual composer options that actually need translations.
     * @return void
     */
    public function translate_text_opts()
    {
        foreach ($this->vc_options as $key => $opt) {
            if (in_array($key, $this->vc_text_options)) {
                $this->vc_options[$key] = __($opt, $this->translate_key);
            }
        }

        foreach ($this->vc_options['params'] as $i => $param) {
            foreach ($param as $param_key => $param_opt) {
                if (in_array($param_key, $this->vc_text_options)) {
                    $this->vc_options['params'][$i][$param_key] = __($param_opt, $this->translate_key);
                }
            }
        }
    }

    /**
     * Replaces images within the VC options.
     * @return void
     */
    public function replace_image_opts()
    {
        foreach ($this->vc_options as $key => $opt) {
            if (in_array($key, $this->vc_image_options)) {
                $this->vc_options[$key] = $this->image($opt);
            }
        }
    }

    /**
     * The actual shortcode callback, will call the user callback after
     * preparing the WP_Query.
     * @param  array  $attributes
     * @param  string $content
     * @return string
     */
    public function pre_callback($attributes, $content)
    {
        // If no attributes are set, we have to default to an empty array.
        if (!$attributes) {
            $attributes = [];
        }

        // Make sure certain attributes have a required default value.
        $attributes = array_merge($this->default_attributes, $attributes);

        // If we don't have any query params, we have nothing to process.
        // Let's just go to the user callback directly.
        if (count($this->_query) === 0 && count($this->query) === 0) {
            // Make sure all attributes are correct before sending them to the template.
            $attributes = $this->validate_attributes($attributes);
            return call_user_func_array([$this, $this->callback], [$attributes, $content]);
        }

        // Generates a query from the attributes. Note that the $query and $_query
        // properties decide if we should have a query at all.
        $query = $this->generate_query_from_attributes($attributes);

        // Make sure all attributes are correct before sending them to the template.
        // Note that we do this after generating the query, because the query generator
        // will call the validation on just the query params, and doing double validation
        // could mess up the attributes.
        $attributes = $this->validate_attributes($attributes);

        return call_user_func_array([$this, $this->callback], [$attributes, $content, $query]);
    }

    /**
     * Generates a query using the $attributes passed through the shortcode,
     * comparing them to the $query values that are writeable, and merging
     * them with the static $_query values.
     * @param  array    $attributes
     * @return WP_Query
     */
    public function generate_query_from_attributes($attributes)
    {
        // If we do have query params however, we'll start building the WP Query by
        // merging the params together.
        $query_params = [];
        foreach ($this->query as $param => $default_value) {
            // If the given param exists in the attribute set,
            // we'll overwrite the default value.
            $value = $default_value;
            if (array_key_exists($param, $attributes)) {
                $value = $attributes[$param];
            }

            // Attach it as a query param.
            $query_params[$param] = $value;
        }

        // Merge the required params over our query params.
        $query_params = array_merge($query_params, $this->_query);

        // Validate the query params separate to make sure that our
        // WP Query call will be valid.
        $query_params = $this->validate_attributes($query_params);

        // Now we should be certain that the query params won't break a
        // query call, so let's make a query and attach it to the callback.
        return new WP_Query($query_params);
    }

    /**
     * Validates all parameters if the validation function
     * for them exists.
     * @param  array $params
     * @return array
     */
    public function validate_attributes($attributes)
    {
        array_walk($attributes, function(&$value, $attribute) {
            // If we have a validation method for the given param,
            // we'll overwrite it's value.
            if (method_exists($this, 'validate_' . $attribute)) {
                $value = $this->{'validate_' . $attribute}($value);
            }
        });

        return $attributes;
    }

    /**
     * Validates the count query param.
     * @param  string  $value
     * @return integer
     */
    public function validate_count($value)
    {
        // Count has to be numeric.
        if (!is_numeric($value)) {
            $value = -1;
        }

        // If we passed the numeric check we can safely cast it as integer.
        $value = (int) $value;

        // Value cannot be less than -1.
        if ($value < -1) {
            $value = -1;
        }

        return $value;
    }

    /**
     * Orderby can only be a set of pre-defined properties, as dicated
     * by Wordpress.
     * @param  string $value
     * @return string
     */
    public function validate_orderby($value)
    {
        if (!in_array($value, [
            'none',
            'ID',
            'author',
            'title',
            'name',
            'type',
            'date',
            'modified',
            'parent',
            'rand',
            'comment_count',
            'menu_order',
            'meta_value',
            'meta_value_num',
            'post__in',
            'post_name__in',
        ])) {
            $value = 'none';
        }

        return $value;
    }

    /**
     * Order can be either ascending or descending.
     * @param  string $value
     * @return string
     */
    public function validate_order($value)
    {
        if (!in_array($value, ['ASC', 'DESC'])) {
            $value = 'DESC';
        }

        return $value;
    }
}