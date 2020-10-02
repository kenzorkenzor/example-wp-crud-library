<?php

namespace Plugin\Example\Lib\Crud;

/**
 * CRUD delete form.
 *
 * @since 0.1.0
 */
abstract class Delete_Form extends Form {
    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        parent::__construct( 'delete' );
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

            <button type="submit" name="submit_action" value="delete" class="crud-btn crud-submit-btn crud-danger-btn">
                <?php esc_html_e( 'Delete', 'text-domain' ); ?>
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
}
