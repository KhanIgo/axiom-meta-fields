<?php

declare(strict_types=1);

namespace AMF\API\REST;

/**
 * Post Types Controller - REST API endpoints for post type operations
 */
class PostTypesController
{
    private string $namespace = 'amf/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // List all post types
        register_rest_route($this->namespace, '/post-types', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostTypes'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Get specific post type
        register_rest_route($this->namespace, '/post-types/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getPostType'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Register new post type (admin only)
        register_rest_route($this->namespace, '/post-types', [
            'methods' => 'POST',
            'callback' => [$this, 'createPostType'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
        ]);

        // Update post type (admin only)
        register_rest_route($this->namespace, '/post-types/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'updatePostType'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete post type (admin only)
        register_rest_route($this->namespace, '/post-types/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deletePostType'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Get all post types
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getPostTypes(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_types = get_post_types([], 'objects');
        $amf_post_types = \AMF\PostType\Register::getInstance()->all();

        $result = [];
        foreach ($post_types as $key => $post_type) {
            $result[$key] = [
                'key' => $key,
                'name' => $post_type->label,
                'singular_name' => $post_type->labels->singular_name,
                'public' => $post_type->public,
                'show_ui' => $post_type->show_ui,
                'show_in_rest' => $post_type->show_in_rest,
                'rest_base' => $post_type->rest_base,
                'menu_icon' => $post_type->menu_icon,
                'supports' => $post_type->supports,
                'is_custom' => isset($amf_post_types[$key]),
            ];
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get specific post type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getPostType(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');

        if (!post_type_exists($key)) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Post type not found', 'amf'),
            ]);
        }

        $post_type = get_post_type_object($key);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'name' => $post_type->label,
                'singular_name' => $post_type->labels->singular_name,
                'public' => $post_type->public,
                'show_ui' => $post_type->show_ui,
                'show_in_rest' => $post_type->show_in_rest,
                'rest_base' => $post_type->rest_base,
                'menu_icon' => $post_type->menu_icon,
                'supports' => $post_type->supports,
            ],
        ]);
    }

    /**
     * Create new post type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createPostType(\WP_REST_Request $request): \WP_REST_Response
    {
        $config = $request->get_json_params();

        if (empty($config['key'])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Post type key is required', 'amf'),
            ]);
        }

        $register = \AMF\PostType\Register::getInstance();
        $register->register($config);
        $register->saveConfigurations();

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $config['key'],
                'message' => __('Post type registered successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Update post type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function updatePostType(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');
        $config = $request->get_json_params();

        $saved = get_option('amf_post_types', []);

        if (!isset($saved[$key])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Custom post type not found', 'amf'),
            ]);
        }

        $config['key'] = $key;
        $saved[$key] = $config;
        update_option('amf_post_types', $saved);

        // Re-register with updated config
        $register = \AMF\PostType\Register::getInstance();
        $register->register($config);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'message' => __('Post type updated successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Delete post type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deletePostType(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');

        $saved = get_option('amf_post_types', []);

        if (!isset($saved[$key])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Custom post type not found', 'amf'),
            ]);
        }

        unset($saved[$key]);
        update_option('amf_post_types', $saved);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'message' => __('Post type deleted successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Check permissions
     *
     * @return bool
     */
    public function checkPermissions(): bool
    {
        return current_user_can('read');
    }

    /**
     * Check admin permissions
     *
     * @return bool
     */
    public function checkAdminPermissions(): bool
    {
        return current_user_can('manage_options');
    }
}
