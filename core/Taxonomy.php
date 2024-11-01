<?php
namespace UniqueHoverSliderPlus;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

// Require the parent theme class for type checks.
require_once('Registerable.php');
use UniqueHoverSliderPlus\Registerable;

abstract class Taxonomy extends Registerable {
    /**
     * The type of registerable; either post_type or taxonomy.
     * @var string
     */
    public $type = 'taxonomy';

    /**
     * Options that should be resolved as callbacks.
     * @var array
     */
    protected $callback_opts = [
        'meta_box_cb',
        'update_count_callback',
    ];
}