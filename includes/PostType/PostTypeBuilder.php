<?php

declare(strict_types=1);

namespace AMF\PostType;

/**
 * Fluent interface builder for post types
 */
class PostTypeBuilder
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

    public function showInMenu(bool $show = true): self
    {
        $this->config['show_in_menu'] = $show;
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

    public function menuPosition(int $position): self
    {
        $this->config['menu_position'] = $position;
        return $this;
    }

    public function menuIcon(string $icon): self
    {
        $this->config['menu_icon'] = $icon;
        return $this;
    }

    public function supports(array $supports): self
    {
        $this->config['supports'] = $supports;
        return $this;
    }

    public function taxonomies(array $taxonomies): self
    {
        $this->config['taxonomies'] = $taxonomies;
        return $this;
    }

    public function hasArchive(bool $has = true): self
    {
        $this->config['has_archive'] = $has;
        return $this;
    }

    public function rewrite(array $rewrite): self
    {
        $this->config['rewrite'] = $rewrite;
        return $this;
    }

    public function capabilityType(string $type): self
    {
        $this->config['capability_type'] = $type;
        return $this;
    }

    public function hierarchical(bool $hierarchical = true): self
    {
        $this->config['hierarchical'] = $hierarchical;
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
