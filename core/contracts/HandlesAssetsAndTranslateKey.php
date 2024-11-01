<?php
namespace UniqueHoverSliderPlus\Contracts;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

interface HandlesAssetsAndTranslateKey
{
    public function get_translate_key();
    public function asset($file);
}