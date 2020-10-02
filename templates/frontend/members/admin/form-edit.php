<?php
// Get the template data
use Plugin\Example\Members\Admin\Admin_Page;

$data = apply_filters( 'example_content_page_template_data', null );

/** @var Admin_Page $page */
$page = $data['_crud'];
$form = $page->get_edit_form();
?>

<?php
$value = esc_attr( $form->get_field( 'name' ) );
?>
<div class="crud-field">

    <div class="crud-input crud-text-field">

        <label for="crud-field-name">
            <?php esc_html_e( 'Name', 'text-domain' ); ?>
            <?php echo $form->get_required_label(); ?>
        </label>

        <input type="text" name="field_name" value="<?php echo $value; ?>" class="crud-field" id="crud-field-name" />

    </div>

    <?php echo $form->get_field_error_html( 'name' ); ?>
</div>
