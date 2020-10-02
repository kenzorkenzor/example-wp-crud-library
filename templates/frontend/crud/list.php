<?php

use Plugin\Example\Lib\Crud\Page;

// Get the template data
$data = apply_filters( 'example_content_page_template_data', null );

/** @var Page $crud */
$crud = $data['_crud'];

// Display success/error message
echo $crud->get_template_message_html();

$page_actions    = $crud->get_page_actions();
$table           = $crud->list_table;
$columns         = $table->get_columns();
$rows            = $table->get_rows();
$has_row_actions = $table->has_row_actions();
?>

    <?php if ( ! empty( $page_actions ) ): ?>

        <div class="crud-page-actions">

            <?php
            foreach ( $page_actions as $action ) {
                echo $action->get_button_html( $crud );
            }
            ?>

        </div>

    <?php endif; ?>

    <table class="crud-list">
        <thead>
        <tr>
            <?php
            foreach ( $columns as $column ):
                $element_id = esc_attr( 'crud-col-header-' . sanitize_key( $column->id ) );
                $class      = $table->get_column_css_class( $column );
                $html       = $table->get_header_column_html( $column );
                ?>

                <th class="<?php echo esc_attr( $class ); ?>" id="<?php echo $element_id; ?>">
                    <?php echo $html; ?>
                </th>

            <?php endforeach; ?>

            <?php if ( $has_row_actions ): ?>
                <th class="crud-row-actions" id="crud-col-header-row-actions"></th>
            <?php endif; ?>

        </tr>
        </thead>
        <tbody>

        <?php
        foreach ( $rows as $row ):
            $row_id          = $row->get_column( 'id' );
            $element_id      = null;
            $data_attributes = '';

            if ( $row_id ) {
                $element_id      = 'crud-row-' . $row_id;
                $data_attributes = 'data-id="' . esc_attr( $row_id ) . '"';
            }
            ?>

            <tr
                <?php if ( $element_id ): ?>id="<?php echo esc_attr( $element_id ); ?>"<?php endif; ?>
                <?php echo $data_attributes; ?>
            >

                <?php
                foreach ( $columns as $column ):
                    $class = $table->get_column_css_class( $column );
                    $html  = $table->get_row_column_html( $row, $column );
                    ?>

                    <td class="<?php echo esc_attr( $class ); ?>">
                        <?php echo $html; ?>
                    </td>

                <?php endforeach; ?>

                <?php
                if ( $has_row_actions ):
                    $actions = $table->get_row_actions( $row );
                    ?>

                    <td class="crud-col-row-actions">

                        <?php
                        foreach ( $actions as $action ) {
                            echo $table->get_row_action_html( $row, $action );
                        }
                        ?>

                    </td>

                <?php endif; ?>

            </tr>

        <?php endforeach; ?>

        </tbody>
    </table>

<?php
echo $crud->get_pagination_html();
