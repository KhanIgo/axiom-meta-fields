<?php

declare(strict_types=1);

namespace AMF\MetaBox;

/**
 * Fluent interface builder for meta boxes
 */
class MetaBoxBuilder
{
    /**
     * @var array
     */
    private array $config = [];

    /**
     * Constructor
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->config['id'] = $id;
    }

    /**
     * Set meta box title
     *
     * @param string $title
     * @return self
     */
    public function title(string $title): self
    {
        $this->config['title'] = $title;
        return $this;
    }

    /**
     * Set post types
     *
     * @param array $post_types
     * @return self
     */
    public function forPostTypes(array $post_types): self
    {
        $this->config['post_types'] = $post_types;
        return $this;
    }

    /**
     * Set context
     *
     * @param string $context
     * @return self
     */
    public function context(string $context): self
    {
        $this->config['context'] = $context;
        return $this;
    }

    /**
     * Set priority
     *
     * @param string $priority
     * @return self
     */
    public function priority(string $priority): self
    {
        $this->config['priority'] = $priority;
        return $this;
    }

    /**
     * Set capability
     *
     * @param string $capability
     * @return self
     */
    public function capability(string $capability): self
    {
        $this->config['capability'] = $capability;
        return $this;
    }

    /**
     * Set visibility conditions
     *
     * @param array|callable $visible
     * @return self
     */
    public function visible($visible): self
    {
        $this->config['visible'] = $visible;
        return $this;
    }

    /**
     * Set fields
     *
     * @param array $fields
     * @return self
     */
    public function fields(array $fields): self
    {
        $this->config['fields'] = $fields;
        return $this;
    }

    /**
     * Enable/disable save on post
     *
     * @param bool $save
     * @return self
     */
    public function savePost(bool $save = true): self
    {
        $this->config['save_post'] = $save;
        return $this;
    }

    /**
     * Enable/disable autosave
     *
     * @param bool $autosave
     * @return self
     */
    public function autosave(bool $autosave = false): self
    {
        $this->config['autosave'] = $autosave;
        return $this;
    }

    /**
     * Enable/disable revisions
     *
     * @param bool $revision
     * @return self
     */
    public function revision(bool $revision = false): self
    {
        $this->config['revision'] = $revision;
        return $this;
    }

    /**
     * Set additional arguments
     *
     * @param array $args
     * @return self
     */
    public function with(array $args): self
    {
        $this->config = array_merge($this->config, $args);
        return $this;
    }

    /**
     * Register the meta box
     *
     * @return Register
     */
    public function register(): Register
    {
        $register = Register::getInstance();
        $register->register($this->config);
        return $register;
    }
}
