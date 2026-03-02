<?php

declare(strict_types=1);

namespace AMF\Taxonomy;

/**
 * Fluent interface builder for taxonomies
 */
class TaxonomyBuilder
{
    private array $config = [];

    public function __construct(string $key)
    {
        $this->config['key'] = $key;
    }

    public function labels(array $labels): self
    {
        $this->config['labels'] = $labels;
        return $this;
    }

    public function forPostTypes(array $postTypes): self
    {
        $this->config['post_types'] = $postTypes;
        return $this;
    }

    public function public(bool $public = true): self
    {
        $this->config['public'] = $public;
        return $this;
    }

    public function showUi(bool $show = true): self
    {
        $this->config['show_ui'] = $show;
        return $this;
    }

    public function showInRest(bool $show = true): self
    {
        $this->config['show_in_rest'] = $show;
        return $this;
    }

    public function restBase(string $base): self
    {
        $this->config['rest_base'] = $base;
        return $this;
    }

    public function hierarchical(bool $hierarchical = true): self
    {
        $this->config['hierarchical'] = $hierarchical;
        return $this;
    }

    public function rewrite(array $rewrite): self
    {
        $this->config['rewrite'] = $rewrite;
        return $this;
    }

    public function showAdminColumn(bool $show = true): self
    {
        $this->config['show_admin_column'] = $show;
        return $this;
    }

    public function metaBox($metaBox): self
    {
        $this->config['meta_box'] = $metaBox;
        return $this;
    }

    public function with(array $args): self
    {
        $this->config = array_merge($this->config, $args);
        return $this;
    }

    public function register(): Register
    {
        $register = Register::getInstance();
        $register->register($this->config);
        return $register;
    }
}
