<?php

use Plugin\Example\Lib\Crud\Page;

// Get the template data
$data = apply_filters( 'example_content_page_template_data', null );

/** @var Page $crud */
$crud = $data['_crud'];
$form = $crud->get_edit_form();

// Display success/error message
echo $crud->get_template_message_html();

$form->start();
$form->content();
$form->end();
