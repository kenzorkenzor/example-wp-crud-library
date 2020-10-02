<?php

namespace Plugin\Example\Lib\Crud;

use Plugin\Example\Models\Crud\Table_Row;

/**
 * Table row action model.
 *
 * @since 0.1.0
 */
abstract class Table_Row_Action {
    /**
     * @since 0.1.0
     * @var string Identifier.
     */
    public $id;

    /**
     * @since 0.1.0
     * @var string Label.
     */
    public $label;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $id
     * @param string $label
     */
    public function __construct( $id, $label ) {
        $this->id    = $id;
        $this->label = $label;
    }

    /**
     * Get the URL for the action.
     *
     * @since 0.1.0
     *
     * @param List_Table $table
     * @param Table_Row  $row
     * @return string Returns the URL for the action. A hash will be returned if the row does not have an 'id' column.
     */
    public function get_url( List_Table $table, Table_Row $row ) {
        $url = add_query_arg( [
            'action'    => $this->id,
            'id'        => $row->get_column( 'id' ),
            'return_to' => urlencode( $table->get_action_return_to_url() ),
        ], $table->get_base_url() );

        /**
         * Filters the URL of an action for a row.
         *
         * @since 0.1.0
         *
         * @param string     $url
         * @param List_Table $table
         * @param Table_Row  $row
         */
        return apply_filters( 'crud_list_table_row_action_url', $url, $table, $row );
    }
}
