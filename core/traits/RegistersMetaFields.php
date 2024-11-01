<?php
namespace UniqueHoverSliderPlus\Traits;

require_once(__DIR__ . '/../vendor/MultiPostThumbnails/MultiPostThumbnails.php');

use MultiPostThumbnails;
use WP_Query;

// Exit if accessed directly
if (! defined('ABSPATH')) {
    exit('No direct script access allowed');
}

trait RegistersMetaFields {
    /**
     * ... Generates nonced and field names.
     * @return void
     */
    public function generate_nonces_and_field_names()
    {
        foreach ($this->meta_fields as $field => $props)
        {
            // Decide if we use class name or slug.
            $id = ( property_exists($this, 'type') && $this->type === 'post_type' ? $this->name : $this->slug );

            // We also want to add some generated properties to the property,
            // so that we can access them later when saving the post.
            $props['meta_field_name'] = $id . '_' . $field;
            $props['nonce'] = $id . '_' . $field;
            $props['nonce_field_name'] = $props['meta_field_name'] . '_nonce';
            $props['id'] = $field . '_metabox';
            $props['title'] = __($props['title'], $this->translate_key);
            $props['post_type'] = ( array_key_exists('post_type', $props) ? $props['post_type'] : $this->name );
            $props['context'] = ( array_key_exists('context', $props) ? $props['context'] : 'advanced' );
            $props['priority'] = ( array_key_exists('priority', $props) ? $props['priority'] : 'default' );
            $props['description'] = ( array_key_exists('description', $props) ? $props['description'] : '' );

            $this->meta_fields[$field] = $props;
        }
    }

    /**
     * Registers meta fields, big whoop yo.
     * @return void
     */
    public function register_meta_fields()
    {
        // Loop through all the given meta fields to register them.
        foreach ($this->meta_fields as $field => $props) {
            // If we're adding user meta we can skip this step, since user meta has
            // to be hooked somewhere entirely else. Would be cool if WP kept this part
            // similar, but sadly we can't simply add meta boxes to users :(.
            if (!array_key_exists('post_type', $props) || $props['post_type'] !== 'user') {
                // If a type is set for the given meta field, there is a chance that
                // we have a custom method to manage the registration. If such a method
                // exists, we'll call that instead and asume that they will deal with it.
                if (
                    array_key_exists('type', $props) &&
                    $props['type'] !== 'image' &&
                    method_exists($this, 'register_' . $props['type'] . '_field')
                ) {
                    $this->{'register_' . $props['type'] . '_field'}($field, $props);

                // If the method doesn't exist however, we'll just register it as a normal
                // meta property.
                } else {
                    // Image fields have a special exception, because fuck this MultiPostThumbnail
                    // bullshit script that fucks everything over.
                    if (!array_key_exists('type', $props) || $props['type'] !== 'image') {
                        // The callback is always our render meta field method.
                        $callback = [$this, 'render_meta_field'];

                        // Within our callback we need to know which template we should render,
                        // since we call the same method for every meta field.
                        $callback_args = $this->meta_fields[$field];
                        $callback_args['field'] = $field;

                        // Register the meta box in WP.
                        add_meta_box(
                            $props['id'],
                            $props['title'],
                            $callback,
                            $props['post_type'],
                            $props['context'],
                            $props['priority'],
                            $callback_args
                        );
                    }
                }
            }
        }
    }

    /**
     * Our custom handler for image meta properties, using the MultiPostThumbnails
     * script.
     * @param  string $field
     * @param  array  $props
     * @return void
     */
    public function register_meta_images()
    {
        // Loop through all the given meta fields to register them...
        foreach ($this->meta_fields as $field => $props) {
            // But only if they are images.
            if (array_key_exists('type', $props) && $props['type'] === 'image') {
                $args = [
                    'label' => $props['title'],
                    'id' => $field,
                    'post_type' => $props['post_type'],
                    // 'priority' => $props['priority'],
                    // 'context' => $props['context'],
                ];

                new MultiPostThumbnails($args);
            }
        }
    }

    /**
     * Renders the meta field from the given template.
     * @param  WP_Post $post
     * @param  array   $metabox
     * @return void
     */
    public function render_meta_field($post, $metabox)
    {
        $post_meta = self::retrieve_meta($post->ID);

        // Render the template through the plugin core.
        echo $this->render_template(
            $metabox['args']['template'],
            [
                'meta' => ( array_key_exists($metabox['args']['field'], $post_meta) ? $post_meta[$metabox['args']['field']] : '' ),
                'meta_field_name' => $metabox['args']['meta_field_name'],
                'nonce' => wp_create_nonce($metabox['args']['nonce']),
                'nonce_field_name' => $metabox['args']['nonce_field_name'],
            ]
        );
    }

    /**
     * Whatever class implements this trait should add an action that calls
     * this method on 'show_user_profile' and 'edit_user_profile'.
     * @param  WP_User $user
     * @return void
     */
    public function render_user_meta($user)
    {
        // Render separator.
        if (property_exists($this, 'user_meta_separator')) {
            echo '<h2>' . __($this->user_meta_separator, $this->translate_key) . '</h2>';
        }

        // Render the template for each piece of user meta.
        foreach ($this->meta_fields as $field => $props) {
            if (array_key_exists('post_type', $props) && $props['post_type'] === 'user') {
                // Pre-define the attributes that we'll send to the template so we can modify it later.
                $attributes = [
                    'title' => $props['title'],
                    'meta' => esc_attr(get_the_author_meta($field, $user->ID)),
                    'meta_field_name' => $props['meta_field_name'],
                    'nonce' => wp_create_nonce($props['nonce']),
                    'nonce_field_name' => $props['nonce_field_name'],
                    'description' => $props['description']
                ];

                // If a roles property was defined, we'll check if the user has
                // said role.
                $role_valid = true;
                if (array_key_exists('roles', $props)) {
                    $role_valid = false;
                    foreach ($props['roles'] as $role) {
                        if (in_array($role, $user->roles)) {
                            $role_valid = true;
                        }
                    }
                }

                // If the meta field is of the relation type, we'll attach a query
                // to the template attributes.
                if (array_key_exists('type', $props) && $props['type'] === 'relation') {
                    $attributes['query'] = new WP_Query([
                        'post_type' => $props['related']
                    ]);
                }

                if ($role_valid) {
                    echo $this->render_template($props['template'], $attributes);
                }
            }
        }
    }

    /**
     * Triggers when the post is saved.
     * @param  integer $post_id
     * @param  WP_Post $post
     * @return void
     */
    public function save_meta($post_id, $post)
    {
        // Make sure the current user is allowed to edit the post.
        if (!current_user_can('edit_post', $post->ID)) {
            return $post->ID;
        }

        // If we're in the correct post type, we'll loop through all
        // registered meta fields.
        foreach ($this->meta_fields as $field => $props) {
            // Verify the nonce we set as a hidden input.
            if (
                isset($_POST[$props['nonce_field_name']]) &&
                !wp_verify_nonce($_POST[$props['nonce_field_name']], $props['nonce'])
            ) {
                continue;
            }

            // If a post type is set in the properties, we want to compare
            // that one to the given post type. If they don't match, we skip.
            if (array_key_exists('post_type', $props)) {
                if ($props['post_type'] !== $post->post_type) {
                    continue;
                }
            // If the post type isn't set in properties however, we'll asume
            // we're using the current name property as post type.
            } else if ($post->post_type !== $this->name) {
                continue;
            }

            // Fetch the meta property from the global $_POST.
            $value = '';
            if (isset($_POST[$props['meta_field_name']])) {
                $value = $_POST[$props['meta_field_name']];
            }

            // Try and sanitize the value.
            if (isset($props['sanitize_method'])) {
                $value = call_user_func($props['sanitize_method'], $value);
            } else {
                // If no sanitization method is defined, we'll force the default
                // sanitize_text_field.
                $value = sanitize_text_field($value);
            }

            // Update the meta value.
            if (get_post_meta($post->ID, $field, false)) {
                update_post_meta($post->ID, $field, $value);
            } else {
                add_post_meta($post->ID, $field, $value);
            }
        }
    }

    /**
     * Whatever class implements this trait should add an action that calls
     * this method on 'personal_options_update' and 'edit_user_profile_update'.
     * @param  integer $user_id
     * @return void
     */
    public function save_user_meta($user_id)
    {
        if (!current_user_can('edit_user', $user_id)) {
            // No.
            return false;
        }

        // Go through all user meta fields.
        foreach ($this->meta_fields as $field => $props) {
            if (array_key_exists('post_type', $props) && $props['post_type'] === 'user') {
                // Verify the nonce we set as a hidden input.
                if (
                    isset($_POST[$props['nonce_field_name']]) &&
                    !wp_verify_nonce($_POST[$props['nonce_field_name']], $props['nonce'])
                ) {
                    continue;
                }

                // Fetch the meta property from the global $_POST.
                $value = '';
                if (isset($_POST[$props['meta_field_name']])) {
                    $value = $_POST[$props['meta_field_name']];

                    if (is_array($value)) {
                        $value = implode(',', $value);
                    }
                }

                // Try and sanitize the value.
                if (isset($props['sanitize_method'])) {
                    $value = call_user_func($props['sanitize_method'], $value);
                } else {
                    // If no sanitization method is defined, we'll force the default
                    // sanitize_text_field.
                    $value = sanitize_text_field($value);
                }

                // Update the meta value.
                update_user_meta($user_id, $field, $value);
            }
        }
    }
}