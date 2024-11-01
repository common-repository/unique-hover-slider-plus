<?php
namespace UniqueHoverSliderPlus\Shortcodes;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

require_once(__DIR__ . '/../../core/Shortcode.php');
require_once(__DIR__ . '/../post-types/Slide.php');
use UniqueHoverSliderPlus\Shortcode;
use UniqueHoverSliderPlus\PostTypes\Slide;
use UniqueHoverSliderPlus\PostTypes\SlidePage;

class Slider extends Shortcode
{
    /**
     * The shortcode name.
     * @var string
     */
    public $name = 'uhsp';

    /**
     * Options to map the shortcode to visual composer.
     * @var array
     */
    public $vc_options = [
        'name' => 'Unique Hover Slider Plus',
        'base' => 'uhsp',
        'class' => '',
        'category' => 'Content',
        'params' => [
            [
                'type' => 'textfield',
                'heading' => 'Slider',
                'param_name' => 'id',
                'value' => '',
                'description' => 'The id of the slider to insert. Create your own slider in the "UHSP Slider" section of the menu.',
            ],

        ]
    ];

    /**
     * Renders the comparator as HTML.
     * @return string
     */
    public function render($attributes, $content)
    {
        // ID will be available in the list of attributes.
        extract($attributes);

        // Retrieve the slides belonging to the slide_page.
        $slides = Slide::query_slides_of_slider($id);

        // Retrieve the extra meta.
        $meta = SlidePage::get_formatted_option($id);

        return $this->render_template('slider.php', ['slides' => $slides, 'meta' => $meta]);
    }
}