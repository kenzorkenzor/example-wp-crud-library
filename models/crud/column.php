<?php
namespace Plugin\Example\Models\Crud;

/**
 * Table column model.
 *
 * @since 0.1.0
 */
class Table_Column {
    /**
     * @since 0.1.0
     * @var string Identifier.
     */
    public $id;

    /**
     * @since 0.1.0
     * @var string Name.
     */
    public $name;

    /**
     * Constructor.
     *
     * @param string $id
     * @param string $name
     */
    public function __construct( $id, $name ) {
        $this->id   = $id;
        $this->name = $name;
    }
}
