<?php
namespace Plugin\Example\Lib\Crud;

/**
 * Table edit row action model.
 *
 * @since 0.1.0
 */
class Table_Edit_Row_Action extends Table_Row_Action {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct( 'edit', __( 'Edit', 'text-domain' ) );
    }
}
