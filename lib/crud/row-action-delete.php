<?php
namespace Plugin\Example\Lib\Crud;

/**
 * Table delete row action model.
 *
 * @since 0.1.0
 */
class Table_Delete_Row_Action extends Table_Row_Action {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct( 'delete', __( 'Delete', 'text-domain' ) );
    }
}
