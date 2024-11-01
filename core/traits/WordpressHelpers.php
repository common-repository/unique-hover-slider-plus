<?php
namespace UniqueHoverSliderPlus\Traits;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

trait WordpressHelpers {
    // ooo        ooooo               .   oooo                        .o8
    // `88.       .888'             .o8   `888                       "888
    //  888b     d'888   .ooooo.  .o888oo  888 .oo.    .ooooo.   .oooo888   .oooo.o
    //  8 Y88. .P  888  d88' `88b   888    888P"Y88b  d88' `88b d88' `888  d88(  "8
    //  8  `888'   888  888ooo888   888    888   888  888   888 888   888  `"Y88b.
    //  8    Y     888  888    .o   888 .  888   888  888   888 888   888  o.  )88b
    // o8o        o888o `Y8bod8P'   "888" o888o o888o `Y8bod8P' `Y8bod88P" 8""888P'
    /**
     * Returns the translation key or null if not set.
     * @return string
     */
    public function get_translate_key()
    {
        if (property_exists($this, 'translate_key')) {
            return $this->translate_key;
        }

        return null;
    }

    /**
     * Returns the slug or null if not set.
     * @return string
     */
    public function get_slug()
    {
        if (property_exists($this, 'slug')) {
            return $this->slug;
        }

        return null;
    }

    /**
     * Returns the root or null if not set.
     * @return string
     */
    public function get_root()
    {
        if (property_exists($this, 'root')) {
            return $this->root;
        }

        return null;
    }

    /**
     * Checks if the function associated with the given plugin exists
     * in the current function list.
     * @param  string  $key
     * @return boolean
     */
    public function has_plugin($key)
    {
        if (array_key_exists($key, $this->plugins)) {
            if (array_key_exists('function', $this->plugins[$key])) {
                return function_exists($this->plugins[$key]['function']);
            }
        }

        return false;
    }

    /**
     * Adds an error to the error message stack.
     * @param  string $key
     * @param  string $message
     * @return void
     */
    public function admin_error($key, $message)
    {
        $this->admin_errors[$key] = $message;
    }

    /**
     * Adds a notice to the notice message stack.
     * @param  string $key
     * @param  string $message
     * @return void
     */
    public function admin_notice($key, $message)
    {
        $this->admin_notices[$key] = $message;
    }

    // ooooo              o8o      .
    // `888'              `"'    .o8
    //  888  ooo. .oo.   oooo  .o888oo
    //  888  `888P"Y88b  `888    888
    //  888   888   888   888    888
    //  888   888   888   888    888 .
    // o888o o888o o888o o888o   "888"
    /**
     * Boots all trait magic.
     * @return void
     */
    public function init_wp_helpers()
    {
        // Lots of things go through translations, let's make
        // sure our key is set for it.
        $this->check_translation_key();

        // Register basic properties.
        $this->register_directories();
        $this->register_uris();
        $this->register_options();
        $this->register_actions();

        // Autoload directories.
        $this->autoload_dirs();

        // When autoloading is done we can register things like
        // post-types, taxonomies, shortcodes, etc.
        $this->register_post_types();
        $this->register_taxonomies();
        $this->register_shortcodes();
    }

    /**
     * Sets messages for listed plugins that aren't activated.
     * @return void
     */
    public function register_plugins()
    {
        if (isset($this->plugins) && count($this->plugins) > 0) {
            foreach ($this->plugins as $key => $plugin) {
                if (!$this->has_plugin($key)) {
                    // If the plugin is required, we'll show an error.
                    if (array_key_exists('required', $plugin) && $plugin['required']) {
                        // If the message is specifically suppressed by the user, we won't display it.
                        if (array_key_exists('show_message', $plugin) && $plugin['show_message'] === false) {
                            continue;
                        }

                        $this->admin_error($key, $this->name . ' requires you to install the plugin "' . $plugin['name'] . '".');
                    // If it's just recommended, we'll show a dismissable notice.
                    } else {
                        // If the message is specifically suppressed by the user, we won't display it.
                        if (array_key_exists('show_message', $plugin) && $plugin['show_message'] === false) {
                            continue;
                        }

                        $this->admin_notice($key, $this->name . ' recommends that you install the plugin "' . $plugin['name'] . '".');
                    }
                }
            }
        }
    }

    /**
     * Registers the directory properties.
     * @return void
     */
    public function register_directories()
    {
        if (isset($this->directories) && isset($this->_directories)) {
            $this->directories = array_merge($this->_directories, $this->directories);
        }
    }

    /**
     * Registers the URI properties.
     * @return void
     */
    public function register_uris()
    {
        if (isset($this->uris) && isset($this->_uris)) {
            $this->uris = array_merge($this->_uris, $this->uris);
        }
    }

    /**
     * Registers the option properties.
     * @return void
     */
    public function register_options()
    {
        if (isset($this->options) && isset($this->_options)) {
            $this->options = array_merge($this->_options, $this->options);
        }

        // Loop through the options to register them in WP.
        foreach ($this->options as $key => $value) {
            add_option(str_replace('-', '_', $this->prefix($key)), $value);
        }
    }

    /**
     * Registers all the hooks.
     * @return void
     */
    public function register_actions()
    {
        if (isset($this->hooks) && isset($this->_hooks)) {
            $this->hooks = array_merge($this->_hooks, $this->hooks);
        }

        if (isset($this->filters) && isset($this->_filters)) {
            $this->filters = array_merge($this->_filters, $this->filters);
        }

        foreach ($this->hooks as $hook) {
            call_user_func_array([$this, 'add_action'], $hook);
        }

        foreach ($this->filters as $filter) {
            call_user_func_array([$this, 'add_filter'], $filter);
        }
    }

    /**
     * Includes a pre-defined array of directories using require_once.
     * @return void
     */
    public function autoload_dirs()
    {
        foreach ($this->autoload as $dir) {
            if ($this->has_dir($dir)) {
                $this->include_dir($dir);
            }
        }
    }

    /**
     * Registers all shortcodes.
     * @return void
     */
    public function register_shortcodes()
    {
        $this->_register($this->shortcodes);
    }

    /**
     * Registers all listed post types by creating
     * instances of the given classes.
     * @return void
     */
    public function register_post_types()
    {
        $this->_register($this->post_types);
    }

    /**
     * Registers all listed taxonomies by creating
     * instances of the given classes.
     * @return void
     */
    public function register_taxonomies()
    {
        $this->_register($this->taxonomies);
    }

    /**
     * Initializes all given classes by initializing them, sending
     * the current theme context to the constructor.
     * @param  array $classes
     * @return void
     */
    public function _register($classes)
    {
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $this->_registered[$class] = new $class($this);
            }
        }
    }

    /**
     * Initializes the language theme domain.
     * @return void
     */
    public function init_language()
    {
        if ($this->has_dir('languages')) {
            load_theme_textdomain($this->translate_key, $this->get_dir('languages'));
        }
    }

    /**
     * Registers all errors and notices as actions.
     * @return void
     */
    public function register_messages()
    {
        if ($this->has_template('admin/error.php')) {
            foreach ($this->admin_errors as $key => $error) {
                add_action('admin_notices', function() use ($key, $error) {
                    echo $this->render_template('admin/error.php', ['key' => $key, 'error' => __($error, $this->translate_key)]);
                });
            }
        }

        if ($this->has_template('admin/notice.php')) {
            foreach ($this->admin_notices as $key => $notice) {
                add_action('admin_notices', function() use ($key, $notice) {
                    echo $this->render_template('admin/notice.php', ['key' => $key, 'notice' => __($notice, $this->translate_key)]);
                });
            }
        }
    }

    //       .o.             .o8                     o8o                   ooo        ooooo
    //      .888.           "888                     `"'                   `88.       .888'
    //     .8"888.      .oooo888  ooo. .oo.  .oo.   oooo  ooo. .oo.         888b     d'888   .ooooo.  ooo. .oo.   oooo  oooo
    //    .8' `888.    d88' `888  `888P"Y88bP"Y88b  `888  `888P"Y88b        8 Y88. .P  888  d88' `88b `888P"Y88b  `888  `888
    //   .88ooo8888.   888   888   888   888   888   888   888   888        8  `888'   888  888ooo888  888   888   888   888
    //  .8'     `888.  888   888   888   888   888   888   888   888        8    Y     888  888    .o  888   888   888   888
    // o88o     o8888o `Y8bod88P" o888o o888o o888o o888o o888o o888o      o8o        o888o `Y8bod8P' o888o o888o  `V88V"V8P'
    /**
     * Generates a default menu array.
     * @return array
     */
    public function generate_default_menu()
    {
        return [
            'page_title' => $this->name,
            'menu_title' => $this->short_name,
            'capability' => $this->capability,
            'menu_slug' => $this->slug,
            'icon' => 'dashicons-admin-post',
            'position' => null,
            'children' => []
        ];
    }

    /**
     * Generates a default submenu array.
     * @return array
     */
    public function generate_default_submenu()
    {
        return [
            'parent_slug' => 'edit.php',
            'page_title' => $this->name,
            'menu_title' => $this->short_name,
            'capability' => $this->capability,
            'menu_slug' => $this->slug,
        ];
    }

    /**
     * Renders pre-defined menus. Automatically called on 'admin_menu' hook.
     * @hook   admin_menu
     * @return void
     */
    public function menus()
    {
        // Remap the menu pages to load any default settings.
        $renderable_admin_menu_pages = array_map(function ($menu_page) {
            // Let's first merge it with a default generated menu
            // so that any missing properties will be filled in
            // with default values.
            $menu_page = array_merge(
                $this->generate_default_menu(),
                $menu_page
            );

            // Lets use this loop to map the submenu pages as well.
            if (count($menu_page['children']) > 0) {
                $menu_page['children'] = array_map(function ($submenu_page) use ($menu_page) {
                    // Fill the submenu page with defaults.
                    $submenu_page = array_merge(
                        $this->generate_default_submenu(),
                        $submenu_page
                    );

                    return [
                        $menu_page['menu_slug'],
                        __($submenu_page['page_title'], $this->translate_key),
                        __($submenu_page['menu_title'], $this->translate_key),
                        $submenu_page['capability'],
                        $submenu_page['menu_slug'],
                        [$this, $submenu_page['method']],
                    ];
                }, $menu_page['children']);
            }

            // Now we want to order the attributes as a non-indexed
            // array to prepare it for a function call.
            $icon_is_asset = (strpos('.', $menu_page['icon']) !== false);
            return [
                'menu_page' => [
                    __($menu_page['page_title'], $this->translate_key),
                    __($menu_page['menu_title'], $this->translate_key),
                    $menu_page['capability'],
                    $menu_page['menu_slug'],
                    [$this, $menu_page['method']],
                    ($icon_is_asset ? $this->asset($menu_page['icon']) : $menu_page['icon']),
                    $menu_page['position'],
                ],
                'admin_submenu_pages' => $menu_page['children'],
            ];
        }, $this->admin_menu_pages);

        // Then we can loop through them to actually add them as
        // menu pages.
        foreach ($renderable_admin_menu_pages as $menu_item) {
            call_user_func_array('add_menu_page', $menu_item['menu_page']);

            // We also have to go through any potential submenus.
            foreach ($menu_item['admin_submenu_pages'] as $submenu_item) {
                call_user_func_array('add_submenu_page', $submenu_item);
            }
        }

        // Remap any additional submenus as well.
        $renderable_admin_submenu_pages = array_map(function ($submenu_page) {
            // Merge it with the default submenu array.
            $submenu_page = array_merge(
                $this->generate_default_submenu(),
                $submenu_page
            );

            return [
                $submenu_page['parent_slug'],
                __($submenu_page['page_title'], $this->translate_key),
                __($submenu_page['menu_title'], $this->translate_key),
                $submenu_page['capability'],
                $submenu_page['menu_slug'],
                [$this, $submenu_page['method']],
            ];
        }, $this->admin_submenu_pages);

        foreach ($renderable_admin_submenu_pages as $submenu_item) {
            call_user_func_array('add_submenu_page', $submenu_item);
        }
    }

    // ooooo   ooooo           oooo
    // `888'   `888'           `888
    //  888     888   .ooooo.   888  oo.ooooo.   .ooooo.  oooo d8b  .oooo.o
    //  888ooooo888  d88' `88b  888   888' `88b d88' `88b `888""8P d88(  "8
    //  888     888  888ooo888  888   888   888 888ooo888  888     `"Y88b.
    //  888     888  888    .o  888   888   888 888    .o  888     o.  )88b
    // o888o   o888o `Y8bod8P' o888o  888bod8P' `Y8bod8P' d888b    8""888P'
    //                                888
    //                               o888o
    /**
     * Automatically sets the translate key if none was set.
     * @return void
     */
    private function check_translation_key()
    {
        if (!$this->translate_key) {
            // Set the translate key.
            $this->translate_key = $this->slug;
        }
    }

    /**
     * Wrapper for isset $_POST.
     * @param  string  $key
     * @return boolean
     */
    public function has_input($key)
    {
        return (isset($_POST[$key]));
    }

    /**
     * Wrapper for $_POST[$key].
     * @param  string $key
     * @return mixed
     */
    public function get_input($key)
    {
        if ($this->has_input($key)) {
            return $_POST[$key];
        }

        return null;
    }

    /**
     * Looks for a POST param called 'event' and fires an action
     * registered to the given event if we have a hook registered
     * to it.
     * @return void
     */
    public function handle_input_events()
    {
        // If there was any kind of input under the 'event'
        // key, we'll try to handle it.
        if ($this->has_input('event')) {
            $this->handle_event($this->get_input('event'));
        }
    }

    /**
     * Calls the custom hook registered to the given event.
     * @param  string $event
     * @return void
     */
    public function handle_event($event)
    {
        // Make sure we actually have a hook for the
        // given event.
        if ($this->has_action_hook($event)) {
            // Call the event as an action.
            do_action($event);
        }
    }

    /**
     * If the given action has a registered hook.
     * @param  string  $action
     * @return boolean
     */
    public function has_action_hook($action)
    {
        $hook_exists = false;

        // Check all actions to see if it contains the one we want.
        foreach ($this->hooks as $hook) {
            // The actual action name is the first index of the array.
            if ($hook[0] === $action) {
                $hook_exists = true;
            }
        }

        return $hook_exists;
    }

    /**
     * Prepends the plugin slug to the given string.
     * @param  string $str
     * @return string
     */
    public function prefix($str)
    {
        return "{$this->slug}-{$str}";
    }

    /**
     * Check if the given dir exists within our directory presets.
     * @param  string  $dir
     * @return boolean
     */
    public function has_dir($dir)
    {
        return array_key_exists($dir, $this->directories);
    }

    /**
     * Retrieves the given directory as a full path.
     * @param  string $dir
     * @return string
     */
    public function get_dir($dir)
    {
        // Retrieve the directory from the dirlist if it exists.
        if (array_key_exists($dir, $this->directories)) {
            $dir = $this->directories[$dir];
        }

        // Prepend full dir path.
        return $this->full_path($this->trim_prepended_slash($dir));
    }

    /**
     * Retrieves the full path to the given directory.
     * @param  string $dir
     * @return string
     */
    public function full_path($dir)
    {
        return ABSPATH . "wp-content/{$this->root}/{$this->slug}/{$dir}";
    }

    /**
     * Prepends a slash to a string if it doesn't contain one yet.
     * @param  string $str
     * @return string
     */
    public function trim_prepended_slash($str)
    {
        if (strpos($str, '/') === 0) {
            return substr($str, 1);
        }

        return $str;
    }

    /**
     * Retrieves the given directory as a full URI.
     * @param  string $uri
     * @return string
     */
    public function get_uri($uri)
    {
        // Retrieve the uri from the urilist if it exists.
        if (array_key_exists($uri, $this->uris)) {
            $uri = $this->uris[$uri];
        }

        // Prepend full uri path.
        return $this->full_uri($this->trim_prepended_slash($uri));
    }

    /**
     * Retrieves the full uri to the given directory.
     * @param  string $uri
     * @return string
     */
    public function full_uri($uri)
    {
        return content_url("{$this->root}/{$this->slug}/{$uri}");
    }

    /**
     * Returns the full uri to the given asset file.
     * @param  string $file
     * @return string
     */
    public function asset($file)
    {
        $file = $this->trim_prepended_slash($file);
        return $this->get_uri('assets') . "/{$file}";
    }

    /**
     * Returns the full uri to the given image file.
     * @param  string $file
     * @return string
     */
    public function image($file)
    {
        $file = $this->trim_prepended_slash($file);
        return $this->get_uri('images') . "/{$file}";
    }

    /**
     * Retrieves the full path to a given template.
     * @param  string $file
     * @return string
     */
    public function template($file)
    {
        $file = $this->trim_prepended_slash($file);
        return $this->get_dir('templates') . "/{$file}";
    }

    /**
     * Checks if a given template exists.
     * @param  string  $file
     * @return boolean
     */
    public function has_template($file)
    {
        return file_exists($this->get_dir('templates') . "/{$file}");
    }

    /**
     * Renders the given template using ob_get_contents.
     * @param  string $file
     * @return string
     */
    public function render_template($file, $attributes = [])
    {
        extract($attributes);

        // Resolve to full file path.
        $file = $this->template($file);

        ob_start();
        include $file;
        $template = ob_get_contents();
        ob_end_clean();

        return $template;
    }

    /**
     * Simply executes an include_once on all files in the given directory.
     * @param  string $dir
     * @return void
     */
    private function include_dir($dir)
    {
        // If we have the directory as a preset, we'll load that.
        // If that isn't the case however, we'll asume that the given
        // dir is a full directory path already.
        if ($this->has_dir($dir)) {
            $dir = $this->get_dir($dir);
        }

        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), array('..', '.', '.gitkeep'));

            foreach ($files as $file) {
                require_once($dir . '/' . $file);
            }
        }
    }

    // oooooo   oooooo     oooo ooooooooo.        oooooo   oooooo     oooo
    //  `888.    `888.     .8'  `888   `Y88.       `888.    `888.     .8'
    //   `888.   .8888.   .8'    888   .d88'        `888.   .8888.   .8'   oooo d8b  .oooo.   oo.ooooo.  oo.ooooo.   .ooooo.  oooo d8b  .oooo.o
    //    `888  .8'`888. .8'     888ooo88P'          `888  .8'`888. .8'    `888""8P `P  )88b   888' `88b  888' `88b d88' `88b `888""8P d88(  "8
    //     `888.8'  `888.8'      888                  `888.8'  `888.8'      888      .oP"888   888   888  888   888 888ooo888  888     `"Y88b.
    //      `888'    `888'       888                   `888'    `888'       888     d8(  888   888   888  888   888 888    .o  888     o.  )88b
    //       `8'      `8'       o888o                   `8'      `8'       d888b    `Y888""8o  888bod8P'  888bod8P' `Y8bod8P' d888b    8""888P'
    //                                                                                         888        888
    //                                                                                        o888o      o888o
    /**
     * Enqueue script wrapper that automatically prepends slug to handle
     * and generates a proper uri for the given file.
     * @param  string  $handle
     * @param  string  $file
     * @param  array   $deps
     * @param  string  $version
     * @param  boolean $in_footer
     * @return void
     */
    public function enqueue_script($handle, $file, $deps = [], $version = null, $in_footer = false)
    {
        if (!$version) {
            $version = $this->version;
        }

        wp_enqueue_script(
            "{$this->slug}-{$handle}",
            $this->asset($file),
            $deps,
            $version,
            $in_footer
        );
    }

    /**
     * Enqueue style wrapper that automatically prepends slug to handle
     * and generates a proper uri for the given file.
     * @param  string $handle
     * @param  string $file
     * @param  array  $deps
     * @param  string $version
     * @param  string $media
     * @return void
     */
    public function enqueue_style($handle, $file, $deps = [], $version = null, $media = 'all')
    {
        if (!$version) {
            $version = $this->version;
        }

        wp_enqueue_style(
            "{$this->slug}-{$handle}",
            $this->asset($file),
            $deps,
            $version,
            $media
        );
    }

    /**
     * Wraps the WP add_action function by always calling it with
     * an array as Callable.
     * @param string $event
     * @param string $method
     * @return void
     */
    protected function add_action($event, $method, $priority = 10, $accepted_args = 1)
    {
        add_action($event, [&$this, $method], $priority, $accepted_args);
    }

    /**
     * Wraps the WP add_filter function by always calling it with
     * an array as Callable.
     * @param string $event
     * @param string $method
     */
    protected function add_filter($event, $method, $priority = 10, $accepted_args = 1)
    {
        add_filter($event, [&$this, $method], $priority, $accepted_args);
    }

    /**
     * Wraps the WP add_shortcode function by always calling it with
     * an array as Callable.
     * @param string $code
     * @param string $method
     * @return void
     */
    protected function add_shortcode($code, $method)
    {
        add_shortcode($code, [&$this, $method]);
    }

    /**
     * get_theme_dir makes more sense to me than get_template_directory.
     * @return string
     */
    private function get_theme_dir()
    {
        return get_template_directory();
    }

    /**
     * Same as above.
     * @return string
     */
    private function get_theme_uri()
    {
        return get_template_directory_uri();
    }

    /**
     * Same as above.
     * @return string
     */
    private function get_child_theme_dir()
    {
        return get_stylesheet_directory();
    }

    /**
     * Same as above.
     * @return string
     */
    private function get_child_theme_uri()
    {
        return get_stylesheet_directory_uri();
    }

    /**
     * Retrieves all meta for a single post.
     * @param  integer $id
     * @return array
     */
    public static function retrieve_meta($id)
    {
        // I even specify that the meta should return SINGULAR values as the third
        // parameter is supposed to indicate but nooooo, if you request all of the
        // meta as singular it simply DOESN'T FOOKEN WORK. Very helpful parameter.
        $shitty_meta = get_post_meta($id, '', true);
        $non_shitty_meta = [];

        // Fuck everything about meta what the fuck.
        foreach ($shitty_meta as $key => $shit) {
            if (count($shit) > 0) {
                $non_shitty_meta[$key] = $shit[0];
            }
        }

        return $non_shitty_meta;
    }
}