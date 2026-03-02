<?php

declare(strict_types=1);

namespace AMF\API\REST;

/**
 * Meta Controller - REST API endpoints for meta operations
 */
class MetaController
{
    /**
     * @var string
     */
    private string $namespace = 'amf/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // Get all meta for a post
        register_rest_route($this->namespace, '/meta/(?P<post_type>[a-zA-Z0-9_-]+)/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getAllMeta'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'post_type' => [
                    'required' => true,
                    'validate_callback' => fn($param) => post_type_exists($param),
                ],
                'id' => [
                    'required' => true,
                    'validate_callback' => fn($param) => is_numeric($param),
                ],
            ],
        ]);

        // Get specific meta value
        register_rest_route($this->namespace, '/meta/(?P<post_type>[a-zA-Z0-9_-]+)/(?P<id>\d+)/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getMeta'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'post_type' => [
                    'required' => true,
                    'validate_callback' => fn($param) => post_type_exists($param),
                ],
                'id' => [
                    'required' => true,
                    'validate_callback' => fn($param) => is_numeric($param),
                ],
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Create/update meta (batch)
        register_rest_route($this->namespace, '/meta/(?P<post_type>[a-zA-Z0-9_-]+)/(?P<id>\d+)', [
            'methods' => 'POST',
            'callback' => [$this, 'updateMeta'],
            'permission_callback' => [$this, 'checkEditPermissions'],
            'args' => [
                'post_type' => [
                    'required' => true,
                    'validate_callback' => fn($param) => post_type_exists($param),
                ],
                'id' => [
                    'required' => true,
                    'validate_callback' => fn($param) => is_numeric($param),
                ],
                'meta' => [
                    'required' => true,
                    'validate_callback' => fn($param) => is_array($param),
                ],
            ],
        ]);

        // Delete specific meta
        register_rest_route($this->namespace, '/meta/(?P<post_type>[a-zA-Z0-9_-]+)/(?P<id>\d+)/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteMeta'],
            'permission_callback' => [$this, 'checkEditPermissions'],
            'args' => [
                'post_type' => [
                    'required' => true,
                    'validate_callback' => fn($param) => post_type_exists($param),
                ],
                'id' => [
                    'required' => true,
                    'validate_callback' => fn($param) => is_numeric($param),
                ],
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Get all meta for a post
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getAllMeta(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = (int) $request->get_param('id');
        $post_type = $request->get_param('post_type');

        // Verify post exists
        $post = get_post($post_id);
        if (!$post || $post->post_type !== $post_type) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Post not found', 'amf'),
            ]);
        }

        // Get all meta
        $all_meta = get_post_meta($post_id);

        // filter by prefix if needed
        $amf_meta = [];
        foreach ($all_meta as $key => $value) {
            $amf_meta[$key] = maybe_unserialize($value[0]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $amf_meta,
            'meta' => [
                'post_id' => $post_id,
                'post_type' => $post_type,
                'timestamp' => current_time('mysql'),
            ],
        ]);
    }

    /**
     * Get specific meta value
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getMeta(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = (int) $request->get_param('id');
        $key = $request->get_param('key');

        $value = get_post_meta($post_id, $key, true);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'value' => $value,
            ],
        ]);
    }

    /**
     * Update meta (batch)
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function updateMeta(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = (int) $request->get_param('id');
        $meta = $request->get_param('meta');

        $updated = [];
        $failed = [];

        foreach ($meta as $key => $value) {
            $result = update_post_meta($post_id, $key, $value);

            if ($result !== false) {
                $updated[] = $key;
            } else {
                $failed[] = $key;
            }
        }

        return rest_ensure_response([
            'success' => empty($failed),
            'data' => [
                'updated' => $updated,
                'failed' => $failed,
            ],
        ]);
    }

    /**
     * Delete meta
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteMeta(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = (int) $request->get_param('id');
        $key = $request->get_param('key');

        $result = delete_post_meta($post_id, $key);

        return rest_ensure_response([
            'success' => $result !== false,
            'data' => [
                'key' => $key,
                'deleted' => $result !== false,
            ],
        ]);
    }

    /**
     * Check permissions
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function checkPermissions(\WP_REST_Request $request): bool
    {
        return current_user_can('read');
    }

    /**
     * Check edit permissions
     *
     * @param \WP_REST_Request $request
     * @return bool
     */
    public function checkEditPermissions(\WP_REST_Request $request): bool
    {
        $post_id = (int) $request->get_param('id');
        return current_user_can('edit_post', $post_id);
    }
}
