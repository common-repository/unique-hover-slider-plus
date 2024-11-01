<?php
// Namespaced to prevent class conflict.
namespace UniqueHoverSliderPlus\PostTypes;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

require_once(__DIR__ . '/../../core/PostType.php');
require_once(__DIR__ . '/../../core/Taxonomy.php');
require_once(__DIR__ . '/../vendor/MultiPostThumbnails/MultiPostThumbnails.php');

use UniqueHoverSliderPlus\PostType;
use UniqueHoverSliderPlus\Taxonomy;
use MultiPostThumbnails;
use WP_Query;

/**
 * The slides for our slider.
 */
class Slide extends PostType
{
    /**
     * The post type name.
     * @var string
     */
    public $name = 'slide';

    /**
     * Automatically translated labels.
     * @var array
     */
    public $labels = [
        'add_new'            => 'Add New Slide',
        'add_new_item'       => 'Add New Slide',
        'all_items'          => 'Edit Slides',
        'edit_item'          => 'Edit Slide',
        'menu_name'          => 'UHSP Slider',
        'name'               => 'Slides',
        'not_found'          => 'Not found',
        'not_found_in_trash' => 'Not found in Trash',
        'parent_item_colon'  => 'Parent Slide:',
        'search_items'       => 'Search Slide',
        'singular_name'      => 'Slide',
        'update_item'        => 'Update Slide',
        'view_item'          => 'View Slide',
    ];

    /**
     * Options besides the labels.
     * @var array
     */
    public $opts = [
        'exclude_from_search' => true,
        'has_archive' => false,
        'hierarchical' => false,
        'menu_icon' => 'images/icon.svg',
        'menu_position' => 100,
        'public' => false,
        'publicly_queriable' => true,
        'query_var' => true,
        'rewrite' => false,
        'show_in_menu' => true,
        'show_in_nav_menus' => false,
        'show_ui' => true,
        'supports' => ['title', 'editor', 'thumbnail', 'page-attributes'],
    ];

    /**
     * Automatically register actions with their callback.
     * @var array
     */
    protected $hooks = [
        ['slide_post_type_registered', 'boot'],
    ];

    /**
     * Automatically register filters with their callback.
     * @var array
     */
    protected $filters = [
        ['manage_edit-slide_columns', 'table_heading'],
        ['manage_slide_posts_custom_column', 'table_column'],
        ['manage_edit-slide_sortable_columns','table_sortable'],
    ];

    /**
     * Automatically called when the post type is registered.
     * @return void
     */
    public function boot()
    {
        // Add another thumbnail option for the foreground icon.
        new MultiPostThumbnails([
            'label' => 'Foreground Icon',
            'id' => 'foreground-icon',
            'post_type' => $this->name
        ]);
    }

    /**
     * Adds the order of the slides into the overview table.
     * @param  array $headings
     * @return array
     */
    public function table_heading($headings)
    {
        $headings[$this->name . '_order'] = __('Order', $this->translate_key);
        $headings[$this->name . '_slider'] = __('Slider', $this->translate_key);
        return $headings;
    }

    /**
     * Inserts the order into the post type table columns.
     * @param  string  $col_name
     * @return integer
     */
    public function table_column($col_name)
    {
        global $post;

        if ($col_name === $this->name . '_order') {
            echo $post->menu_order;
        }

        if ($col_name == $this->name . '_slider') {
            $terms = wp_get_post_terms($post->ID, 'slide_page');
            $terms = array_map(function($term) {
                return $term->name;
            }, $terms);
            echo implode(', ', $terms);
        }
    }

    /**
     * Allow the table to be sortable by menu_order.
     * @param  array $columns
     * @return array
     */
    public function table_sortable($columns)
    {
        $columns[$this->name . '_order'] = 'menu_order';
        return $columns;
    }

    /**
     * Queries sliders belonging to a given slider.
     * @param  integer  $id
     * @return WP_Query
     */
    public static function query_slides_of_slider($id)
    {
        $args = [
            'posts_per_page' => 5,
            'no_found_rows' => true,
            'post_type' => 'slide',
            'orderby' => 'menu_order',
            'tax_query' => [
                [
                    'taxonomy' => 'slide_page',
                    'field' => 'id',
                    'terms' => $id,
                ]
            ]
        ];
        return new WP_Query($args);
    }
}

/**
 * Slides have to be registered to a slide page in order to be visible.
 * A slide page holds up to 5 slides.
 */
class SlidePage extends Taxonomy
{
    /**
     * The taxonomy name.
     * @var string
     */
    public $name = 'slide_page';

    /**
     * The post type our taxonomy should be attached to.
     * @var string
     */
    public $parent_post_type = 'slide';

    /**
     * Automatically translated labels.
     * @var array
     */
    public $labels = [
        'add_new_item'               => 'Add Slider',
        'add_or_remove_items'        => 'Add or remove sliders',
        'all_items'                  => 'All Sliders',
        'choose_from_most_used'      => 'Choose from the most used sliders',
        'edit_item'                  => 'Edit Slider',
        'menu_name'                  => 'Add or Edit Sliders',
        'name'                       => 'Sliders',
        'new_item_name'              => 'New Slider Name',
        'not_found'                  => 'Not Found',
        'search_items'               => 'Search Sliders',
        'separate_items_with_commas' => 'Separate sliders with commas',
        'singular_name'              => 'Slider',
        'update_item'                => 'Update Slider',
    ];

    /**
     * Options besides the labels.
     * @var array
     */
    public $opts = [
        'hierarchical' => true,
        'label' => 'Slider',
        'query_var' => true,
        'rewrite' => false,
        'show_in_nav_menus' => false,
        'show_tagcloud' => false,
    ];

    /**
     * Automatically register actions with their callback.
     * @var array
     */
    protected $hooks = [
        ['slide_page_add_form_fields', 'add_form_fields'],
        ['slide_page_edit_form_fields', 'edit_form_fields'],
        ['create_slide_page', 'save_custom_meta'],
        ['edited_slide_page', 'save_custom_meta'],
    ];

    /**
     * Automatically register filters with their callback.
     * @var array
     */
    protected $filters = [
        ['manage_edit-slide_page_columns' , 'table_heading'],
        ['manage_slide_page_custom_column', 'table_column', 5, 3],
    ];

    /**
     * The default overlay opacity.
     * @var string
     */
    const DEFAULT_OPACITY = '75%';

    /**
     * The default overlay color.
     * @var string
     */
    const DEFAULT_OVERLAY_COLOR = '#334D5C';

    /**
     * The default overlay color.
     * @var string
     */
    const DEFAULT_TITLE_COLOR = '#F5C949';

    /**
     * The default overlay color.
     * @var string
     */
    const DEFAULT_SUBTITLE_COLOR = '#FFFFFF';

    /**
     * Parses the input to make proper values all the time.
     * @param  array $input
     * @return array
     */
    public function parse_input($input)
    {
        // The arrow_buttons property should be a tinyint property.
        $arrow_buttons = (int) array_key_exists('arrow_buttons', $input);

        // Color has to be a valid hex color code.
        $overlay_color = strtoupper($input['overlay_color']);
        if (!preg_match("/^\#?[A-F0-9]{6}$/", $overlay_color)) $overlay_color = static::DEFAULT_OVERLAY_COLOR;
        if (substr($overlay_color, 0, 1) !== '#') $overlay_color = '#' . $overlay_color;

        $title_color = strtoupper($input['title_color']);
        if (!preg_match("/^\#?[A-F0-9]{6}$/", $title_color)) $title_color = static::DEFAULT_TITLE_COLOR;
        if (substr($title_color, 0, 1) !== '#') $title_color = '#' . $title_color;

        $subtitle_color = strtoupper($input['subtitle_color']);
        if (!preg_match("/^\#?[A-F0-9]{6}$/", $subtitle_color)) $subtitle_color = static::DEFAULT_SUBTITLE_COLOR;
        if (substr($subtitle_color, 0, 1) !== '#') $subtitle_color = '#' . $subtitle_color;

        // Opacity has to be between 0 - 100 with a percentage sign.
        $overlay_opacity = ( strlen($input['overlay_opacity']) > 0 ? $input['overlay_opacity'] : static::DEFAULT_OPACITY );
        $overlay_opacity = (int) str_replace('%', '', $overlay_opacity);
        if ($overlay_opacity < 0) $overlay_opacity = 0;
        if ($overlay_opacity > 100) $overlay_opacity = 100;
        $overlay_opacity = (string) $overlay_opacity . '%';

        return [
            'title_color' => $title_color,
            'subtitle_color' => $subtitle_color,
            'arrow_buttons' => $arrow_buttons,
            'overlay_color' => $overlay_color,
            'overlay_opacity' => $overlay_opacity,
        ];
    }

    /**
     * Adds extra form fields to the create new taxonomy page.
     * @return void
     */
    public function add_form_fields()
    {
        echo $this->render_template('slide_page_add_form_fields.php');
    }

    /**
     * Adds extra form fields to the edit existing taxonomy page.
     * @return void
     */
    public function edit_form_fields($slide_page)
    {
        $id = $slide_page->term_id;
        $meta = static::get_option($id);

        echo $this->render_template('slide_page_edit_form_fields.php', ['meta' => $meta, 'id' => $id]);
    }

    /**
     * Stores custom meta data for the given ID.
     * @param  integer $id
     * @return void
     */
    public function save_custom_meta($id)
    {
        if (isset($_POST[$this->name . '_meta'])) {
            // Parse received input.
            $input = $this->parse_input($_POST[$this->name . '_meta']);

            $meta = static::get_option($id);

            foreach ($input as $key => $value) {
                $meta[$key] = $value;
            }

            static::update_option($id, $meta);
        }
    }

    /**
     * Inserts the slider ID into the taxonomy table heading.
     * @param  array $headings
     * @return array
     */
    public function table_heading($headings)
    {
        $cb = array_splice($headings, 0, 1);
        $headings = $cb + [$this->name . '_shortcode' => __('Shortcode', $this->translate_key)] + $headings;
        return $headings;
    }

    /**
     * Inserts the slider ID into the taxonomy table columns.
     * @param  mixed   $value
     * @param  string  $col_name
     * @param  integer $id
     * @return integer
     */
    public function table_column($value, $col_name, $id)
    {
        if ($col_name === $this->name . '_shortcode') {
            return "<pre style=\"display: inline-block; background-color: white; margin: 1px 5px\">[uhsp id=\"{$id}\"]</pre>";
        }
    }

    /**
     * Updates the meta belonging to the given slider id.
     * @param  integer $id
     * @param  array   $meta
     * @return void
     */
    public static function update_option($id, $meta)
    {
        $opt = 'taxonomy_slide_page_' . $id;
        update_option($opt, $meta);
    }

    /**
     * Retrieves the option belonging to the given slider id.
     * @param  integer $id
     * @return array
     */
    public static function get_option($id)
    {
        $opt = 'taxonomy_slide_page_' . $id;
        return get_option($opt);
    }

    /**
     * Formats the options array to be used in a template.
     * @param  integer $id
     * @return array
     */
    public static function get_formatted_option($id)
    {
        $meta = static::get_option($id);

        // Replace properties to be used within HTML / CSS.
        $meta['overlay_opacity'] = (int) str_replace('%', '', $meta['overlay_opacity']) / 100;

        return $meta;
    }
}