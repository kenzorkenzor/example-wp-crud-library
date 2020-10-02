<?php

namespace Plugin\Example\Models;

/**
 * Plugin content page.
 *
 * @since 0.1.0
 */
class Content_Page {
    /**
     * @var string Page identifier.
     * @since 0.1.0
     */
    public $id;

    /**
     * @var string Label for settings.
     * @since 0.1.0
     */
    public $label;

    /**
     * @var string Description for settings.
     * @since 0.1.0
     */
    public $description;

    /**
     * @var callable Function to call when content is to be displayed.
     * @since 0.1.0
     */
    public $display_callback;

    /**
     * @var callable Function to call before content is displayed.
     * @since 0.1.0
     */
    public $screen_callback;

    /**
     * @var callable Function to call when template is requesting data.
     * @since 0.1.0
     */
    public $data_callback;

    /**
     * @var callable Function to call to check if user has permission to view.
     * @since 0.1.0
     */
    public $permission_callback;

    /**
     * @var int WordPress post ID.
     * @since 0.1.0
     */
    public $post_id;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $id
     * @param string $label
     * @param string $description
     */
    public function __construct( $id, $label, $description = '' ) {
        $this->id          = $id;
        $this->label       = $label;
        $this->description = $description;
    }
}
