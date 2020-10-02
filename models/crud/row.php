<?php
namespace Plugin\Example\Models\Crud;

/**
 * Table row model.
 *
 * @since 0.1.0
 */
class Table_Row {
    /**
     * @since 0.1.0
     * @var array Column data.
     */
    public $data = [];

    /**
     * @since 0.1.0
     * @var array Additional data.
     */
    public $meta = [];

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param array $data Optional. Default: [].
     * @param array $meta Optional. Default: [].
     */
    public function __construct( $data = [], $meta = [] ) {
        $this->data = $data;
        $this->meta = $meta;
    }

    /**
     * Get column data.
     *
     * @since 0.1.0
     *
     * @param string $column_id
     * @return mixed Returns column value if found, otherwise null.
     */
    public function get_column( $column_id ) {
        if ( ! $this->data ) {
            return null;
        }

        if ( ! is_array( $this->data ) ) {
            return null;
        }

        if ( ! isset( $this->data[ $column_id ] ) ) {
            return null;
        }

        return $this->data[ $column_id ];
    }

    /**
     * Get meta data.
     *
     * @since 0.1.0
     *
     * @param string $key
     * @return mixed Returns meta value if found, otherwise null.
     */
    public function get_meta( $key ) {
        if ( ! $this->meta ) {
            return null;
        }

        if ( ! is_array( $this->meta ) ) {
            return null;
        }

        if ( ! isset( $this->meta[ $key ] ) ) {
            return null;
        }

        return $this->meta[ $key ];
    }
}
