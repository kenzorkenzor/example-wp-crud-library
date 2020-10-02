<?php

namespace Plugin\Example\Lib\Crud;

use Plugin\Example\Models\Crud\Table_Column;
use Plugin\Example\Models\Crud\Table_Row;

/**
 * Helper for implementing CRUD lists.
 *
 * @since 0.1.0
 */
abstract class List_Table {
    /**
     * @since 0.1.0
     * @var string Base URL for list actions.
     */
    protected $base_url = '';

    /**
     * @since 0.1.0
     * @var Table_Column[] List of columns.
     */
    protected $columns = [];

    /**
     * @since 0.1.0
     * @var Table_Row[] List of rows.
     */
    protected $rows = [];

    /**
     * @since 0.1.0
     * @var Table_Row_Action[] List of actions that can be performed on each row.
     */
    protected $row_actions = [];

    /**
     * @since 0.1.0
     * @var int Total number of items in list.
     */
    protected $total_items = 0;

    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        $this->row_actions[] = new Table_Edit_Row_Action();
        $this->row_actions[] = new Table_Delete_Row_Action();
    }

    /**
     * Prepare items in list.
     *
     * This should also set the total items.
     *
     * @since 0.1.0
     *
     * @param int $page     Page number to fetch (starting at 1).
     * @param int $per_page Items per page to fetch.
     */
    abstract function prepare_items( $page, $per_page );

    /**
     * Get all columns.
     *
     * @since 0.1.0
     *
     * @return Table_Column[]
     */
    public function get_columns() {
        return $this->columns;
    }

    /**
     * Get all rows.
     *
     * @since 0.1.0
     *
     * @return Table_Row[]
     */
    public function get_rows() {
        return $this->rows;
    }

    /**
     * Check if this table has any row actions.
     *
     * @since 0.1.0
     *
     * @return boolean
     */
    public function has_row_actions() {
        /**
         * Filters if a list table has row actions.
         *
         * @since 0.1.0
         *
         * @param bool       $has_actions
         * @param List_Table $table
         */
        return apply_filters( 'crud_list_table_row_actions', ! empty( $this->row_actions ), $this );
    }

    /**
     * Get row actions.
     *
     * @since 0.1.0
     *
     * @param Table_Row $row
     * @return Table_Row_Action[]
     */
    public function get_row_actions( Table_Row $row ) {
        /**
         * Filters the table row actions for a specific row.
         *
         * @since 0.1.0
         *
         * @param Table_Row_Action[] $actions
         * @param Table_Row          $row
         * @param List_Table         $table
         */
        return apply_filters( 'crud_list_table_row_actions', $this->row_actions, $row, $this );
    }

    /**
     * Add table column.
     *
     * @since 0.1.0
     *
     * @param Table_Column $column
     */
    public function add_column( Table_Column $column ) {
        $this->columns[] = $column;
    }

    /**
     * Get the CSS class for a column.
     *
     * @since 0.1.0
     *
     * @param Table_Column $column
     * @return string
     */
    public function get_column_css_class( Table_Column $column ) {
        $classes = [ 'crud-col-' . sanitize_key( $column->id ) ];

        /**
         * Filters the table column CSS class.
         *
         * @since 0.1.0
         *
         * @param string[]     $classes
         * @param Table_Column $column
         * @param List_Table   $table
         */
        $classes = apply_filters( 'crud_list_table_column_css_class', $classes, $column, $this );

        return implode( ' ', $classes );
    }

    /**
     * Get the HTML for a column.
     *
     * @since 0.1.0
     *
     * @param Table_Column $column
     * @return string
     */
    public function get_header_column_html( Table_Column $column ) {
        $html = esc_html( $column->name );

        /**
         * Filters the table column header HTML.
         *
         * @since 0.1.0
         *
         * @param string       $html
         * @param Table_Column $column
         * @param List_Table   $table
         */
        return apply_filters( 'crud_list_table_header_column_html', $html, $column, $this );
    }

    /**
     * Get the HTML for a column in a row.
     *
     * @since 0.1.0
     *
     * @param Table_Row    $row
     * @param Table_Column $column
     * @return string
     */
    public function get_row_column_html( Table_Row $row, Table_Column $column ) {
        $data = $row->get_column( $column->id );
        $html = esc_html( $data );

        /**
         * Filters the table row column HTML.
         *
         * @since 0.1.0
         *
         * @param string       $html
         * @param Table_Row    $row
         * @param Table_Column $column
         * @param List_Table   $table
         */
        return apply_filters( 'crud_list_table_row_column_html', $html, $row, $column, $this );
    }

    /**
     * Get the HTML for a row action.
     *
     * @since 0.1.0
     *
     * @param Table_Row        $row
     * @param Table_Row_Action $action
     * @return string
     */
    public function get_row_action_html( Table_Row $row, Table_Row_Action $action ) {
        $url = esc_attr( $action->get_url( $this, $row ) );

        if ( $url ) {
            $class = 'crud-action- ' . sanitize_key( $action->id );
            $html  = sprintf( '<a href="%s" class="%s">%s</a>', $url, $class, esc_html( $action->label ) );
        } else {
            $html = '';
        }

        /**
         * Filters the table row action HTML.
         *
         * @since 0.1.0
         *
         * @param string           $html
         * @param Table_Row        $row
         * @param Table_Row_Action $action
         * @param string           $url
         * @param List_Table       $table
         */
        return apply_filters( 'crud_list_table_row_action_html', $html, $row, $action, $url, $this );
    }

    /**
     * Set the URL of the page the table is being displayed on.
     *
     * @since 0.1.0
     *
     * @param string $url
     */
    public function set_base_url( $url ) {
        $this->base_url = $url;
    }

    /**
     * Get the URL of the page the table is on.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_base_url() {
        return $this->base_url;
    }

    /**
     * Get the URL to return to after an action has been completed.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_action_return_to_url() {
        global $wp;

        return home_url( add_query_arg( [], $wp->request ) );
    }

    /**
     * Get total number of items in list.
     *
     * @since 0.1.0
     *
     * @return int
     */
    public function get_total_items() {
        return $this->total_items;
    }
}
