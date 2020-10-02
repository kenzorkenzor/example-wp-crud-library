<?php
namespace Plugin\Example\Members\Admin;

use Plugin\Example\Lib\Crud\Edit_Form;

/**
 * Member edit form.
 *
 * @since 0.1.0
 */
class Member_Edit_Form extends Edit_Form {
    public function __construct() {
        parent::__construct();

        $this->id = 'member';
    }

    /**
     * Output form content.
     *
     * @since 0.1.0
     */
    public function content() {
        plugin_name()->get_template_part( 'frontend/members/admin/form-edit' );
    }

    /**
     * Populate form data from POST.
     *
     * @since 0.1.0
     *
     * @param \WP_User $item Optional. Existing item. Default: null.
     */
    public function populate_from_post_data( $item = null ) {
        if ( $item ) {
            $this->set_field( 'id', $item->ID );
        }

        $name = trim( filter_input( INPUT_POST, 'field_name', FILTER_SANITIZE_STRING ) );

        $this->set_fields( [
            'name' => $this->sanitize_field_value( 'name', $name ),
        ] );
    }

    /**
     * Populate form data from data source.
     *
     * @since 0.1.0
     *
     * @param \WP_User $item Optional. Default: null.
     */
    public function populate( $item = null ) {
        if ( ! $item ) {
            return;
        }

        $this->set_fields( [
            'id'   => $item->ID,
            'name' => $item->first_name,
        ] );
    }

    /**
     * Validate form data.
     *
     * @since 0.1.0
     */
    public function validate() {
        $this->validate_name_field();
    }

    /**
     * Validate name field.
     *
     * @since 0.1.0
     */
    public function validate_name_field() {
        $name = $this->get_field( 'name' );

        if ( ! $name ) {
            $this->add_error( 'name', __( 'Name is required', 'text-domain' ) );
            return;
        }
    }
}
