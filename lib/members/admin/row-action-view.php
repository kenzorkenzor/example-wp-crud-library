<?php

namespace Plugin\Example\Lib\Members\Admin;

use Plugin\Example\Lib\Crud\List_Table;
use Plugin\Example\Lib\Crud\Table_Row_Action;
use Plugin\Example\Models\Crud\Table_Row;

/**
 * Member table view row action model.
 *
 * @since 0.1.0
 */
class Member_Table_View_Row_Action extends Table_Row_Action {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct( 'view', __( 'View', 'text-domain' ) );
    }

    /**
     * Get the URL for the action.
     *
     * @since 0.1.0
     *
     * @param List_Table $table
     * @param Table_Row  $row
     * @return string Returns the URL for the action if user found, otherwise empty string.
     */
    public function get_url( List_Table $table, Table_Row $row ) {
        $user = $this->get_user_from_row( $row );

        if ( ! $user ) {
            return '';
        }

        if ( ! function_exists( 'buddypress' ) ) {
            return '';
        }

        return bp_core_get_user_domain( $user->ID, $user->user_nicename, $user->user_login );
    }

    /**
     * Get the user for a row.
     *
     * @since 0.1.0
     *
     * @param Table_Row $row
     * @return false|\WP_User
     */
    private function get_user_from_row( Table_Row $row ) {
        $user = $row->get_meta( 'user' );

        if ( ! $user ) {
            $id = (int) $row->get_column( 'id' );

            if ( ! $id ) {
                return false;
            }

            $user = get_user_by( 'id', $id );
        }

        return $user;
    }
}
