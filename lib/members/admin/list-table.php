<?php
namespace Plugin\Example\Members\Admin;

use Plugin\Example\Lib\Crud\List_Table;
use Plugin\Example\Lib\Members\Admin\Member_Table_View_Row_Action;
use Plugin\Example\Models\Crud\Table_Row;

/**
 * Member table.
 *
 * @since 0.1.0
 */
class Member_List_Table extends List_Table {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct();

        $this->row_actions[] = new Member_Table_View_Row_Action();
    }

    /**
     * Prepare items in list.
     *
     * @since 0.1.0
     *
     * @param int $page     Page number to fetch (starting at 1).
     * @param int $per_page Items per page to fetch.
     */
    public function prepare_items( $page, $per_page ) {
        // Get users
        $users = new \WP_User_Query( [
            'offset' => ( $page - 1 ) * $per_page,
            'number' => $per_page,
        ] );

        // Set total
        $this->total_items = $users->get_total();

        // Populate rows
        foreach ( $users->get_results() as $user ) {
            $this->rows[] = new Table_Row( [
                'id'   => $user->ID,
                'name' => $user->display_name,
            ], [
                'user' => $user
            ] );
        }
    }
}
