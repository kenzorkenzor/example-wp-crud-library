<?php

use Plugin\Example\Lib\Crud\Page;

// Get the template data
$data = apply_filters( 'example_content_page_template_data', null );

/** @var Page $crud */
$crud = $data['_crud'];
$form = $crud->get_delete_form();

// Display success/error message
echo $crud->get_template_message_html();
?>

<h3 class="crud-delete-confirm">
    <?php esc_html_e( 'Are you sure you want to delete this item?', 'text-domain' ); ?>
</h3>

<?php
$form->start();
$form->content();
$form->end();
