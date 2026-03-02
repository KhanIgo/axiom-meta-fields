<?php

declare(strict_types=1);

namespace AMF\Fields;

/**
 * Field Interface
 */
interface FieldInterface
{
    /**
     * Render the field
     *
     * @param array $options
     * @return void
     */
    public function render(array $options): void;

    /**
     * Get field value
     *
     * @param int $object_id
     * @param bool $single
     * @return mixed
     */
    public function getValue(int $object_id, bool $single = true);

    /**
     * Update field value
     *
     * @param int $object_id
     * @param mixed $value
     * @return bool
     */
    public function updateValue(int $object_id, $value): bool;

    /**
     * Delete field value
     *
     * @param int $object_id
     * @return bool
     */
    public function deleteValue(int $object_id): bool;

    /**
     * Get field settings
     *
     * @return array
     */
    public function getSettings(): array;

    /**
     * Enqueue field scripts
     *
     * @return void
     */
    public function enqueueScripts(): void;

    /**
     * Enqueue field styles
     *
     * @return void
     */
    public function enqueueStyles(): void;
}
