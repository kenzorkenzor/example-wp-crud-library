<?php
namespace Plugin\Example\Members\Admin;

use Plugin\Example\Lib\Crud\Delete_Form;

/**
 * Member delete form.
 *
 * @since 0.1.0
 */
class Member_Delete_Form extends Delete_Form {
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
        plugin_name()->get_template_part( 'frontend/members/admin/form-delete' );
    }

    /**
     * Populate form data from data source.
     *
     * @since 0.1.0
     *
     * @param \WP_User $item Optional. Default: null.
     */
    public function populate( $item = null ) {
        $this->set_fields( [
            'id'   => $item->ID,
            'name' => $item->display_name,
        ] );
    }
}
