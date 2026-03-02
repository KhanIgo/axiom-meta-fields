<?php

declare(strict_types=1);

namespace AMF\Fields\Types;

use AMF\Fields\FieldAbstract;

/**
 * Post Field - Select posts
 */
class PostField extends FieldAbstract
{
    protected string $type = 'post';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'post_type' => 'post',
        'query_args' => [],
        'display_field' => 'post_title',
        'multiple' => false,
        'placeholder' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        // Ensure value is array for multiple
        if ($options['multiple'] && !is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        // Get posts
        $posts = $this->getPosts($options);

        echo '<select';
        echo ' id="' . esc_attr($options['id']) . '"';

        if ($options['multiple']) {
            echo ' name="' . esc_attr($name) . '[]"';
            echo ' multiple="multiple"';
        } else {
            echo ' name="' . esc_attr($name) . '"';
        }

        echo ' class="amf-post-select ' . esc_attr($options['class']) . '"';

        if (!empty($options['placeholder'])) {
            echo ' data-placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        echo '>';

        if (!$options['multiple'] && !empty($options['placeholder'])) {
            echo '<option value="">' . esc_html($options['placeholder']) . '</option>';
        }

        foreach ($posts as $post) {
            $selected = is_array($value) ? in_array((string) $post->ID, $value, true) : (string) $post->ID === (string) $value;

            echo '<option value="' . esc_attr($post->ID) . '"';
            if ($selected) {
                echo ' selected="selected"';
            }
            echo '>' . esc_html($this->getPostTitle($post, $options)) . '</option>';
        }

        echo '</select>';
    }

    /**
     * Get posts based on query args
     *
     * @param array $options
     * @return array
     */
    private function getPosts(array $options): array
    {
        $defaults = [
            'post_type' => $options['post_type'],
            'posts_per_page' => -1,
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ];

        $query_args = wp_parse_args($options['query_args'], $defaults);

        $query = new \WP_Query($query_args);

        return $query->posts ?? [];
    }

    /**
     * Get post title for display
     *
     * @param \WP_Post $post
     * @param array $options
     * @return string
     */
    private function getPostTitle(\WP_Post $post, array $options): string
    {
        $display_field = $options['display_field'] ?? 'post_title';

        if ($display_field === 'post_title') {
            return $post->post_title;
        }

        return get_post_meta($post->ID, $display_field, true) ?: $post->post_title;
    }
}

/**
 * Taxonomy Field - Select terms
 */
class TaxonomyField extends FieldAbstract
{
    protected string $type = 'taxonomy';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'taxonomy' => 'category',
        'query_args' => [],
        'hierarchical' => true,
        'multiple' => false,
        'placeholder' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        // Ensure value is array for multiple
        if ($options['multiple'] && !is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        // Get terms
        $terms = $this->getTerms($options);

        if ($options['hierarchical'] && !$options['multiple']) {
            // Use wp_dropdown_categories for hierarchical single select
            $this->renderHierarchicalDropdown($options, $value, $name, $terms);
        } else {
            // Use regular select
            $this->renderSelect($options, $value, $name, $terms);
        }
    }

    /**
     * Get terms
     *
     * @param array $options
     * @return array
     */
    private function getTerms(array $options): array
    {
        $defaults = [
            'taxonomy' => $options['taxonomy'],
            'hide_empty' => false,
            'orderby' => 'name',
            'order' => 'ASC',
        ];

        $query_args = wp_parse_args($options['query_args'], $defaults);

        return get_terms($query_args) ?: [];
    }

    /**
     * Render hierarchical dropdown
     *
     * @param array $options
     * @param mixed $value
     * @param string $name
     * @param array $terms
     * @return void
     */
    private function renderHierarchicalDropdown(array $options, $value, string $name, array $terms): void
    {
        $defaults = [
            'taxonomy' => $options['taxonomy'],
            'hide_empty' => false,
            'name' => $name,
            'id' => $options['id'],
            'selected' => $value,
            'show_option_none' => $options['placeholder'] ?: '',
            'class' => $options['class'],
        ];

        wp_dropdown_categories($defaults);
    }

    /**
     * Render regular select
     *
     * @param array $options
     * @param mixed $value
     * @param string $name
     * @param array $terms
     * @return void
     */
    private function renderSelect(array $options, $value, string $name, array $terms): void
    {
        echo '<select';
        echo ' id="' . esc_attr($options['id']) . '"';

        if ($options['multiple']) {
            echo ' name="' . esc_attr($name) . '[]"';
            echo ' multiple="multiple"';
        } else {
            echo ' name="' . esc_attr($name) . '"';
        }

        echo ' class="' . esc_attr($options['class']) . '"';

        if (!empty($options['placeholder'])) {
            echo ' data-placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        echo '>';

        if (!$options['multiple'] && !empty($options['placeholder'])) {
            echo '<option value="">' . esc_html($options['placeholder']) . '</option>';
        }

        foreach ($terms as $term) {
            $selected = is_array($value) ? in_array((string) $term->term_id, $value, true) : (string) $term->term_id === (string) $value;

            echo '<option value="' . esc_attr($term->term_id) . '"';
            if ($selected) {
                echo ' selected="selected"';
            }
            echo '>' . esc_html($term->name) . '</option>';
        }

        echo '</select>';
    }
}

/**
 * User Field - Select users
 */
class UserField extends FieldAbstract
{
    protected string $type = 'user';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'role' => '',
        'query_args' => [],
        'display_field' => 'display_name',
        'multiple' => false,
        'placeholder' => '',
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $name = $this->getFieldName($options);

        // Ensure value is array for multiple
        if ($options['multiple'] && !is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        // Get users
        $users = $this->getUsers($options);

        echo '<select';
        echo ' id="' . esc_attr($options['id']) . '"';

        if ($options['multiple']) {
            echo ' name="' . esc_attr($name) . '[]"';
            echo ' multiple="multiple"';
        } else {
            echo ' name="' . esc_attr($name) . '"';
        }

        echo ' class="' . esc_attr($options['class']) . '"';

        if (!empty($options['placeholder'])) {
            echo ' data-placeholder="' . esc_attr($options['placeholder']) . '"';
        }

        echo '>';

        if (!$options['multiple'] && !empty($options['placeholder'])) {
            echo '<option value="">' . esc_html($options['placeholder']) . '</option>';
        }

        foreach ($users as $user) {
            $selected = is_array($value) ? in_array((string) $user->ID, $value, true) : (string) $user->ID === (string) $value;

            echo '<option value="' . esc_attr($user->ID) . '"';
            if ($selected) {
                echo ' selected="selected"';
            }
            echo '>' . esc_html($this->getUserDisplay($user, $options)) . '</option>';
        }

        echo '</select>';
    }

    /**
     * Get users
     *
     * @param array $options
     * @return array
     */
    private function getUsers(array $options): array
    {
        $defaults = [
            'role' => $options['role'],
            'orderby' => 'display_name',
            'order' => 'ASC',
        ];

        $query_args = wp_parse_args($options['query_args'], $defaults);

        $query = new \WP_User_Query($query_args);

        return $query->get_results() ?: [];
    }

    /**
     * Get user display name
     *
     * @param \WP_User $user
     * @param array $options
     * @return string
     */
    private function getUserDisplay(\WP_User $user, array $options): string
    {
        $display_field = $options['display_field'] ?? 'display_name';

        if ($display_field === 'display_name') {
            return $user->display_name;
        }

        if ($display_field === 'user_login') {
            return $user->user_login;
        }

        if ($display_field === 'user_email') {
            return $user->user_email;
        }

        return get_user_meta($user->ID, $display_field, true) ?: $user->display_name;
    }
}

/**
 * Relationship Field - Bidirectional relation
 */
class RelationshipField extends FieldAbstract
{
    protected string $type = 'relationship';

    protected array $defaults = [
        'id' => '',
        'name' => '',
        'desc' => '',
        'std' => '',
        'required' => false,
        'class' => '',
        'sanitize' => 'text',
        'validate' => [],
        'save_field' => true,
        'post_type' => 'post',
        'query_args' => [],
        'multiple' => true,
        'admin_column' => true,
    ];

    public function render(array $options): void
    {
        $options = $this->getOptions($options);
        $value = $this->getFieldValue($options);
        $field_id = esc_attr($options['id']);

        if (!is_array($value)) {
            $value = $value !== '' ? [$value] : [];
        }

        echo '<div class="amf-relationship-wrapper">';
        echo '<input type="hidden" name="' . esc_attr($options['name']) . '" value="" class="amf-relationship-value" data-field="' . $field_id . '" />';

        // Selected items
        echo '<ul class="amf-relationship-selected">';
        foreach ($value as $post_id) {
            if (empty($post_id)) {
                continue;
            }
            $post = get_post((int) $post_id);
            if ($post) {
                echo '<li data-id="' . esc_attr($post_id) . '">';
                echo esc_html($post->post_title);
                echo '<button type="button" class="amf-relationship-remove dashicons dashicons-no"></button>';
                echo '</li>';
            }
        }
        echo '</ul>';

        // Search input
        echo '<div class="amf-relationship-search">';
        echo '<input type="text" class="amf-relationship-search-input" placeholder="' . esc_attr__('Search...', 'amf') . '" data-field="' . $field_id . '" />';
        echo '</div>';

        // Available items
        echo '<div class="amf-relationship-available">';
        echo '<ul class="amf-relationship-list">';
        $posts = $this->getPosts($options);
        foreach ($posts as $post) {
            if (in_array((string) $post->ID, $value, true)) {
                continue;
            }
            echo '<li data-id="' . esc_attr($post->ID) . '" class="amf-relationship-item">';
            echo esc_html($post->post_title);
            echo '<button type="button" class="button amf-relationship-add">' . esc_html__('Add', 'amf') . '</button>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';

        echo '</div>';
    }

    /**
     * Get posts
     *
     * @param array $options
     * @return array
     */
    private function getPosts(array $options): array
    {
        $defaults = [
            'post_type' => $options['post_type'],
            'posts_per_page' => 20,
            'post_status' => 'publish',
        ];

        $query_args = wp_parse_args($options['query_args'], $defaults);

        $query = new \WP_Query($query_args);

        return $query->posts ?? [];
    }

    public function enqueueScripts(): void
    {
        // Relationship field needs custom JS
    }
}
