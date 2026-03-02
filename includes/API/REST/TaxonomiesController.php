<?php

declare(strict_types=1);

namespace AMF\API\REST;

/**
 * Taxonomies Controller - REST API endpoints for taxonomy operations
 */
class TaxonomiesController
{
    private string $namespace = 'amf/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // List all taxonomies
        register_rest_route($this->namespace, '/taxonomies', [
            'methods' => 'GET',
            'callback' => [$this, 'getTaxonomies'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Get specific taxonomy
        register_rest_route($this->namespace, '/taxonomies/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getTaxonomy'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Register new taxonomy (admin only)
        register_rest_route($this->namespace, '/taxonomies', [
            'methods' => 'POST',
            'callback' => [$this, 'createTaxonomy'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
        ]);

        // Update taxonomy (admin only)
        register_rest_route($this->namespace, '/taxonomies/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'updateTaxonomy'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
            'args' => [
                'key' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete taxonomy (admin only)
        register_rest_route($this->namespace, '/taxonomies/(?P<key>[a-zA-Z0-9_-]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteTaxonomy'],
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
     * Get all taxonomies
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getTaxonomies(\WP_REST_Request $request): \WP_REST_Response
    {
        $taxonomies = get_taxonomies([], 'objects');
        $amf_taxonomies = \AMF\Taxonomy\Register::getInstance()->all();

        $result = [];
        foreach ($taxonomies as $key => $taxonomy) {
            $result[$key] = [
                'key' => $key,
                'name' => $taxonomy->label,
                'singular_name' => $taxonomy->labels->singular_name,
                'public' => $taxonomy->public,
                'show_ui' => $taxonomy->show_ui,
                'show_in_rest' => $taxonomy->show_in_rest,
                'rest_base' => $taxonomy->rest_base,
                'hierarchical' => $taxonomy->hierarchical,
                'post_types' => $taxonomy->object_type,
                'is_custom' => isset($amf_taxonomies[$key]),
            ];
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Get specific taxonomy
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getTaxonomy(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');

        if (!taxonomy_exists($key)) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Taxonomy not found', 'amf'),
            ]);
        }

        $taxonomy = get_taxonomy($key);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'name' => $taxonomy->label,
                'singular_name' => $taxonomy->labels->singular_name,
                'public' => $taxonomy->public,
                'show_ui' => $taxonomy->show_ui,
                'show_in_rest' => $taxonomy->show_in_rest,
                'rest_base' => $taxonomy->rest_base,
                'hierarchical' => $taxonomy->hierarchical,
                'post_types' => $taxonomy->object_type,
            ],
        ]);
    }

    /**
     * Create new taxonomy
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createTaxonomy(\WP_REST_Request $request): \WP_REST_Response
    {
        $config = $request->get_json_params();

        if (empty($config['key'])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Taxonomy key is required', 'amf'),
            ]);
        }

        $register = \AMF\Taxonomy\Register::getInstance();
        $register->register($config);
        $register->saveConfigurations();

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $config['key'],
                'message' => __('Taxonomy registered successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Update taxonomy
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function updateTaxonomy(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');
        $config = $request->get_json_params();

        $saved = get_option('amf_taxonomies', []);

        if (!isset($saved[$key])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Custom taxonomy not found', 'amf'),
            ]);
        }

        $config['key'] = $key;
        $saved[$key] = $config;
        update_option('amf_taxonomies', $saved);

        // Re-register with updated config
        $register = \AMF\Taxonomy\Register::getInstance();
        $register->register($config);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'message' => __('Taxonomy updated successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Delete taxonomy
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteTaxonomy(\WP_REST_Request $request): \WP_REST_Response
    {
        $key = $request->get_param('key');

        $saved = get_option('amf_taxonomies', []);

        if (!isset($saved[$key])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Custom taxonomy not found', 'amf'),
            ]);
        }

        unset($saved[$key]);
        update_option('amf_taxonomies', $saved);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'key' => $key,
                'message' => __('Taxonomy deleted successfully', 'amf'),
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
