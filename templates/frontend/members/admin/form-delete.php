<?php
// Get the template data
use Plugin\Example\Members\Admin\Admin_Page;

$data = apply_filters( 'example_content_page_template_data', null );

/** @var Admin_Page $page */
$page = $data['_crud'];
$form = $page->get_delete_form();
?>

<div class="crud-field-display">
    <div class="crud-field-name"><?php esc_html_e( 'Name', 'text-domain' ); ?></div>
    <div class="crud-field-value"><?php echo esc_html( $form->get_field( 'name' ) ); ?></div>
</div>
