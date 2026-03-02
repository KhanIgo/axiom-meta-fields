<?php

declare(strict_types=1);

namespace AMF\API\REST;

/**
 * Fields Controller - REST API endpoints for field operations
 */
class FieldsController
{
    private string $namespace = 'amf/v1';

    /**
     * Register routes
     *
     * @return void
     */
    public function registerRoutes(): void
    {
        // List all field types
        register_rest_route($this->namespace, '/field-types', [
            'methods' => 'GET',
            'callback' => [$this, 'getFieldTypes'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Get specific field type
        register_rest_route($this->namespace, '/field-types/(?P<type>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getFieldType'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'type' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Get field configuration for post
        register_rest_route($this->namespace, '/fields/(?P<post_type>[a-zA-Z0-9_-]+)/(?P<id>\d+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getFieldsForPost'],
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

        // List all meta boxes
        register_rest_route($this->namespace, '/meta-boxes', [
            'methods' => 'GET',
            'callback' => [$this, 'getMetaBoxes'],
            'permission_callback' => [$this, 'checkPermissions'],
        ]);

        // Get specific meta box
        register_rest_route($this->namespace, '/meta-boxes/(?P<id>[a-zA-Z0-9_-]+)', [
            'methods' => 'GET',
            'callback' => [$this, 'getMetaBox'],
            'permission_callback' => [$this, 'checkPermissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Create meta box (admin only)
        register_rest_route($this->namespace, '/meta-boxes', [
            'methods' => 'POST',
            'callback' => [$this, 'createMetaBox'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
        ]);

        // Update meta box (admin only)
        register_rest_route($this->namespace, '/meta-boxes/(?P<id>[a-zA-Z0-9_-]+)', [
            'methods' => 'PUT',
            'callback' => [$this, 'updateMetaBox'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);

        // Delete meta box (admin only)
        register_rest_route($this->namespace, '/meta-boxes/(?P<id>[a-zA-Z0-9_-]+)', [
            'methods' => 'DELETE',
            'callback' => [$this, 'deleteMetaBox'],
            'permission_callback' => [$this, 'checkAdminPermissions'],
            'args' => [
                'id' => [
                    'required' => true,
                    'sanitize_callback' => 'sanitize_text_field',
                ],
            ],
        ]);
    }

    /**
     * Get all field types
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getFieldTypes(\WP_REST_Request $request): \WP_REST_Response
    {
        $factory = \AMF\Fields\FieldFactory::getInstance();

        $types = [
            'text' => ['label' => __('Text', 'amf'), 'category' => 'basic'],
            'textarea' => ['label' => __('Textarea', 'amf'), 'category' => 'basic'],
            'wysiwyg' => ['label' => __('WYSIWYG Editor', 'amf'), 'category' => 'basic'],
            'number' => ['label' => __('Number', 'amf'), 'category' => 'basic'],
            'email' => ['label' => __('Email', 'amf'), 'category' => 'basic'],
            'url' => ['label' => __('URL', 'amf'), 'category' => 'basic'],
            'phone' => ['label' => __('Phone', 'amf'), 'category' => 'basic'],
            'password' => ['label' => __('Password', 'amf'), 'category' => 'basic'],
            'color' => ['label' => __('Color Picker', 'amf'), 'category' => 'basic'],
            'hidden' => ['label' => __('Hidden', 'amf'), 'category' => 'basic'],

            'date' => ['label' => __('Date', 'amf'), 'category' => 'date_time'],
            'time' => ['label' => __('Time', 'amf'), 'category' => 'date_time'],
            'datetime' => ['label' => __('Date & Time', 'amf'), 'category' => 'date_time'],
            'date_range' => ['label' => __('Date Range', 'amf'), 'category' => 'date_time'],

            'select' => ['label' => __('Select', 'amf'), 'category' => 'selection'],
            'checkbox' => ['label' => __('Checkbox', 'amf'), 'category' => 'selection'],
            'checkbox_list' => ['label' => __('Checkbox List', 'amf'), 'category' => 'selection'],
            'radio' => ['label' => __('Radio', 'amf'), 'category' => 'selection'],
            'radio_list' => ['label' => __('Radio List', 'amf'), 'category' => 'selection'],
            'switch' => ['label' => __('Switch', 'amf'), 'category' => 'selection'],
            'slider' => ['label' => __('Slider', 'amf'), 'category' => 'selection'],

            'file' => ['label' => __('File', 'amf'), 'category' => 'media'],
            'image' => ['label' => __('Image', 'amf'), 'category' => 'media'],
            'gallery' => ['label' => __('Gallery', 'amf'), 'category' => 'media'],
            'video' => ['label' => __('Video', 'amf'), 'category' => 'media'],
            'audio' => ['label' => __('Audio', 'amf'), 'category' => 'media'],

            'post' => ['label' => __('Post', 'amf'), 'category' => 'relationship'],
            'taxonomy' => ['label' => __('Taxonomy', 'amf'), 'category' => 'relationship'],
            'user' => ['label' => __('User', 'amf'), 'category' => 'relationship'],
            'relationship' => ['label' => __('Relationship', 'amf'), 'category' => 'relationship'],

            'group' => ['label' => __('Group', 'amf'), 'category' => 'complex'],
            'repeater' => ['label' => __('Repeater', 'amf'), 'category' => 'complex'],
            'tab' => ['label' => __('Tab', 'amf'), 'category' => 'complex'],
            'divider' => ['label' => __('Divider', 'amf'), 'category' => 'complex'],
            'heading' => ['label' => __('Heading', 'amf'), 'category' => 'complex'],
            'code' => ['label' => __('Code Editor', 'amf'), 'category' => 'complex'],
            'map' => ['label' => __('Map', 'amf'), 'category' => 'complex'],
            'range' => ['label' => __('Range', 'amf'), 'category' => 'complex'],
        ];

        return rest_ensure_response([
            'success' => true,
            'data' => $types,
        ]);
    }

    /**
     * Get specific field type
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getFieldType(\WP_REST_Request $request): \WP_REST_Response
    {
        $type = $request->get_param('type');
        $factory = \AMF\Fields\FieldFactory::getInstance();

        if (!$factory->exists($type)) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Field type not found', 'amf'),
            ]);
        }

        $field = $factory->create($type);
        $settings = $field ? $field->getSettings() : [];

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'type' => $type,
                'settings' => $settings,
            ],
        ]);
    }

    /**
     * Get fields for a post
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getFieldsForPost(\WP_REST_Request $request): \WP_REST_Response
    {
        $post_id = (int) $request->get_param('id');
        $post_type = $request->get_param('post_type');

        $register = \AMF\MetaBox\Register::getInstance();
        $metaBoxes = $register->getByPostType($post_type);

        $fields = [];
        foreach ($metaBoxes as $id => $config) {
            foreach ($config['fields'] as $field) {
                $field_id = $field['id'] ?? '';
                $value = get_post_meta($post_id, $field_id, true);

                $fields[] = [
                    'id' => $field_id,
                    'name' => $field['name'] ?? '',
                    'type' => $field['type'] ?? 'text',
                    'value' => $value,
                    'meta_box' => $id,
                ];
            }
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $fields,
        ]);
    }

    /**
     * Get all meta boxes
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getMetaBoxes(\WP_REST_Request $request): \WP_REST_Response
    {
        $register = \AMF\MetaBox\Register::getInstance();
        $metaBoxes = $register->all();

        return rest_ensure_response([
            'success' => true,
            'data' => $metaBoxes,
        ]);
    }

    /**
     * Get specific meta box
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function getMetaBox(\WP_REST_Request $request): \WP_REST_Response
    {
        $id = $request->get_param('id');

        $register = \AMF\MetaBox\Register::getInstance();
        $metaBox = $register->get($id);

        if (!$metaBox) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Meta box not found', 'amf'),
            ]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $metaBox,
        ]);
    }

    /**
     * Create meta box
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function createMetaBox(\WP_REST_Request $request): \WP_REST_Response
    {
        $config = $request->get_json_params();

        if (empty($config['id'])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Meta box ID is required', 'amf'),
            ]);
        }

        $register = \AMF\MetaBox\Register::getInstance();
        $register->register($config);
        $register->saveConfigurations();

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'id' => $config['id'],
                'message' => __('Meta box created successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Update meta box
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function updateMetaBox(\WP_REST_Request $request): \WP_REST_Response
    {
        $id = $request->get_param('id');
        $config = $request->get_json_params();

        $saved = get_option('amf_meta_boxes', []);

        if (!isset($saved[$id])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Meta box not found', 'amf'),
            ]);
        }

        $config['id'] = $id;
        $saved[$id] = $config;
        update_option('amf_meta_boxes', $saved);

        // Re-register with updated config
        $register = \AMF\MetaBox\Register::getInstance();
        $register->register($config);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'id' => $id,
                'message' => __('Meta box updated successfully', 'amf'),
            ],
        ]);
    }

    /**
     * Delete meta box
     *
     * @param \WP_REST_Request $request
     * @return \WP_REST_Response
     */
    public function deleteMetaBox(\WP_REST_Request $request): \WP_REST_Response
    {
        $id = $request->get_param('id');

        $saved = get_option('amf_meta_boxes', []);

        if (!isset($saved[$id])) {
            return rest_ensure_response([
                'success' => false,
                'message' => __('Meta box not found', 'amf'),
            ]);
        }

        unset($saved[$id]);
        update_option('amf_meta_boxes', $saved);

        return rest_ensure_response([
            'success' => true,
            'data' => [
                'id' => $id,
                'message' => __('Meta box deleted successfully', 'amf'),
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
