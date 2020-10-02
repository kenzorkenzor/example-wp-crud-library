<?php

namespace Plugin\Example\Lib\Crud;

/**
 * CRUD edit form.
 *
 * @since 0.1.0
 */
abstract class Edit_Form extends Form {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct( 'edit' );
    }

    /**
     * Output form footer.
     *
     * @since 0.1.0
     */
    public function end() {
        $cancel_url = $this->cancel_url ? esc_attr( $this->cancel_url ) : '';
        ?>

        <div class="crud-submit">

            <button type="submit" name="submit_action" value="save" class="crud-btn crud-submit-btn">
                <?php esc_html_e( 'Save', 'text-domain' ); ?>
            </button>

            <?php if ( $cancel_url ): ?>

                <a href="<?php echo $cancel_url ?>" class="crud-btn curd-cancel-btn">
                    <?php esc_html_e( 'Cancel', 'text-domain' ); ?>
                </a>

            <?php endif; ?>

        </div>

        <?php
        parent::end();
    }

    /**
     * Check if item exists or are we creating a new item.
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function item_exists() {
        if ( ! isset( $this->data['fields'] ) ) {
            return false;
        }

        return ! empty( $this->data['fields']['id'] );
    }

    /**
     * Populate form data from POST.
     *
     * @since 0.1.0
     *
     * @param mixed $item Optional. Existing item. Default: null.
     */
    abstract public function populate_from_post_data( $item = null );

    /**
     * Validate form data.
     *
     * @since 0.1.0
     */
    abstract public function validate();
}
