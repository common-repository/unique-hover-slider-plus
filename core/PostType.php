<?php
namespace UniqueHoverSliderPlus;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

// Require the parent theme class for type checks.
require_once('Registerable.php');
require_once('traits/RegistersMetaFields.php');

use UniqueHoverSliderPlus\Registerable;
use UniqueHoverSliderPlus\Traits\RegistersMetaFields;

abstract class PostType extends Registerable {
    use RegistersMetaFields;

    /**
     * The type of registerable; either post_type or taxonomy.
     * @var string
     */
    public $type = 'post_type';

    /**
     * Options that should be resolved as callbacks.
     * @var array
     */
    protected $callback_opts = [
        'register_meta_box_cb',
    ];

    /**
     * Options that should be resolved as assets.
     * @var array
     */
    protected $asset_opts = [
        'menu_icon',
    ];

    /**
     * Defualt hooks.
     * @var array
     */
    protected $_hooks = [
        ['save_post', 'save_meta', 1, 2],
    ];

    /**
     * Meta fields to register.
     * @var array
     */
    protected $meta_fields = [];

    /**
     * We override wrap_callback_opts to bind our own 'register_meta_box_cb'
     * if the user added any meta fields.
     * @return void
     */
    public function wrap_callback_opts()
    {
        if (count($this->meta_fields) > 0) {
            // Before we register the meta fields, we want to generate a
            // nonce and proper field name for all of the fields.
            $this->generate_nonces_and_field_names();

            // Meta fields can be registered during the post type callback for it.
            $this->opts['register_meta_box_cb'] = 'register_meta_fields';

            // Images for some reason have to listen to our custom registered
            // hook, which makes no sense as to why they cannot be registered
            // with the other meta fields, but fuck it.
            array_push($this->_hooks, [$this->name . '_post_type_registered', 'register_meta_images']);
        }

        parent::wrap_callback_opts();
    }
}