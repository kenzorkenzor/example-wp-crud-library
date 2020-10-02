<?php
namespace Plugin\Example\Members\Admin;

use Plugin\Example\Lib\Crud\Page_Action;
use Plugin\Example\Models\Crud\Table_Column;
use Plugin\Example\Lib\Crud\Page as Crud_Page;
use Plugin\Example\Models\Content_Page;

/**
 * Member management.
 *
 * @since 0.1.0
 */
class Admin_Page extends Crud_Page {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct(
            'member_admin',
            __( 'Member Administration', 'text-domain' ),
            __( 'Page for administrators to manage members.', 'text-domain' )
        );

        $this->template_dir  = 'frontend/members/admin/';

        $this->set_table( $this->build_list_table() );
        $this->set_edit_form( $this->build_edit_form() );
        $this->set_delete_form( $this->build_delete_form() );

        $this->page_actions[] = new Page_Action( 'create', __( 'Create', 'text-domain' ) );

        // Display form header
        add_action( 'crud_before_content', [ $this, 'output_page_header' ] );
    }

    /**
     * Build list table.
     *
     * @since 0.1.0
     *
     * @return Member_List_Table
     */
    protected function build_list_table() {
        $table = new Member_List_Table();

        $table->set_base_url( $this->get_url() );

        $table->add_column( new Table_Column( 'id', __( 'ID', 'text-domain' ) ) );
        $table->add_column( new Table_Column( 'name', __( 'Name', 'text-domain' ) ) );

        return $table;
    }

    /**
     * Build edit form.
     *
     * @since 0.1.0
     *
     * @return Member_Edit_Form
     */
    protected function build_edit_form() {
        $form = new Member_Edit_Form();

        $form->set_cancel_url( $this->get_url() );

        return $form;
    }

    /**
     * Build delete form.
     *
     * @since 0.1.0
     *
     * @return Member_Delete_Form
     */
    protected function build_delete_form() {
        $form = new Member_Delete_Form();

        $form->set_cancel_url( $this->get_url() );

        return $form;
    }

    /**
     * Check if user has permission to view the page.
     *
     * TODO: implement this.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     * @return bool
     */
    public function check_permissions( Content_Page $page ) {
        return is_user_logged_in();
    }

    /**
     * Check if user has permission to edit an item.
     *
     * TODO: Implement this.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @param int   $user_id Optional. Default: null.
     * @return bool
     */
    public function user_can_edit_item( $item_id, $user_id = null ) {
        $user_id = $user_id ? $user_id : get_current_user_id();

        return $user_id ? true : false;
    }

    /**
     * Check if item ID is valid.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @return bool
     */
    public function is_valid_item_id( $item_id ) {
        $item = $this->get_current_edit_item();

        return $item !== false;
    }

    /**
     * Get an item to edit.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @return \WP_User|false Returns item if found, otherwise false.
     */
    public function get_item( $item_id ) {
        return get_user_by( 'id', (int) $item_id );
    }

    /**
     * Update the item with data from form.
     *
     * TODO: Complete this.
     *
     * @since 0.1.0
     *
     * @return bool Returns true if updated, otherwise false.
     */
    public function update_item() {
        $item_id = (int) $this->get_current_item_id();
        $form    = $this->get_edit_form();

        $result = wp_update_user( [
            'ID'         => $item_id,
            'first_name' => $form->get_field( 'name' ),
        ] );

        return ! is_wp_error( $result );
    }

    /**
     * Delete the item.
     *
     * TODO: Implement this.
     *
     * @since 0.1.0
     *
     * @return bool Returns true if updated, otherwise false.
     */
    public function delete_item() {
        // $item_id = (int) $this->get_current_item_id();

        return false;
    }

    /**
     * Create the item.
     *
     * TODO: Implement this.
     *
     * @since 0.1.0
     *
     * @return bool Returns true if created, otherwise false.
     */
    public function create_item() {
        // $item_id = (int) $this->get_current_item_id();

        return false;
    }

    /**
     * Output page header.
     *
     * @since 0.1.0
     *
     * @param string $action Current action.
     */
    public function output_page_header( $action ) {
        $title = '';

        switch ( $action ) {
            case 'create':
                $title = __( 'Add Member', 'text-domain' );
                break;

            case 'edit':
                $title = __( 'Edit Member', 'text-domain' );
                break;

            case 'delete':
                $title = __( 'Delete Member', 'text-domain' );
                break;
        }

        if ( ! $title ) {
            return;
        }

        echo '<h2 class="crud-page-action-title">' . esc_html( $title ) . '</h2>';
    }
}
