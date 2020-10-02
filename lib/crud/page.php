<?php
namespace Plugin\Example\Lib\Crud;

use Plugin\Example\Models\Content_Page;

/**
 * Helper for implementing CRUD pages.
 *
 * @since 0.1.0
 */
abstract class Page {
    /**
     * @since 0.1.0
     * @var string Current action determined from request.
     */
    protected $current_action = null;

    /**
     * @since 0.1.0
     * @var mixed|null|false Current item being edited. If null, item has not been fetched. If false, fetch failed.
     */
    protected $current_edit_item = null;

    /**
     * @since 0.1.0
     * @var string Default action to execute when none defined and GET request.
     */
    protected $default_action = 'list';

    /**
     * @since 0.1.0
     * @var string URL of page.
     */
    protected $url;

    /**
     * @since 0.1.0
     * @var string Template directory (with trailing slash).
     */
    protected $template_dir = '';

    /**
     * @since 0.1.0
     * @var array List of valid actions and their request types.
     */
    protected $valid_actions = [
        'list'   => 'GET',
        'create' => [ 'GET', 'POST' ],
        'edit'   => [ 'GET', 'POST' ],
        'delete' => [ 'GET', 'POST' ],
    ];

    /**
     * @since 0.1.0
     * @var Page_Action[] List of actions that can be performed on page.
     */
    protected $page_actions = [];

    /**
     * @since 0.1.0
     * @var array Data for use in templates.
     */
    public $template_data = [];

    /**
     * @since 0.1.0
     * @var List_Table Table for displaying list of items.
     */
    public $list_table;

    /**
     * @since 0.1.0
     * @var Edit_Form Form for editing a single item.
     */
    public $edit_form;

    /**
     * @since 0.1.0
     * @var Delete_Form Form for deleting a single item.
     */
    public $delete_form;

    /**
     * @since 0.1.0
     * @var array {
     *      Message to display at top of page.
     *
     *      @type string $type    'success', 'error' or 'info'.
     *      @type string $message Message to display.
     * }
     */
    public $template_message;

    /**
     * @since 0.1.0
     * @var int Maximum number of items per page.
     */
    public $max_per_page = 500;

    /**
     * @since 0.1.0
     * @var int Number of items per page.
     */
    public $per_page = 50;

    /**
     * @since 0.1.0
     * @var int Current page number.
     */
    public $page = 1;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $id          Content page ID.
     * @param string $label       Content page label.
     * @param string $description Content page description.
     */
    public function __construct( $id, $label, $description = '' ) {
        $page = new Content_Page( $id, $label, $description );

        $page->display_callback    = [ $this, 'display' ];
        $page->screen_callback     = [ $this, 'screen' ];
        $page->data_callback       = [ $this, 'get_data' ];
        $page->permission_callback = [ $this, 'check_permissions' ];

        plugin_name()->content_pages->register_page( $page );

        // Set page URL
        $this->url = plugin_name()->content_pages->get_url( $page );

        // Set no permission template
        add_filter( 'crud_no_permission_template', [ $this, 'no_permission_template' ], 10, 2 );

        // Setup paging
        $this->setup_pagination();
    }

    /**
     * Build a URL with optional query args for this page.
     *
     * @since 0.1.0
     *
     * @param array $query_args
     * @return string
     */
    public function build_url( $query_args = [] ) {
        $url = $this->url;

        if ( ! empty( $query_args ) ) {
            add_query_arg( $query_args, $url );
        }

        return $url;
    }

    /**
     * Get the current page action.
     *
     * @since 0.1.0
     *
     * @return string|null
     */
    public function get_current_action() {
        if ( $this->current_action !== null ) {
            return $this->current_action;
        }

        // Check post vars
        $action = filter_input( INPUT_POST, 'action', FILTER_SANITIZE_STRING );

        if ( $action && ! $this->is_valid_action( $action, 'POST' ) ) {
            $action = null;
        }

        // Check get vars
        $action = filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING );

        if ( $action && ! $this->is_valid_action( $action, 'GET' ) ) {
            $action = null;
        }

        if ( ! $action && $this->is_get_request() ) {
            $action = $this->default_action;
        }

        return $action;
    }

    /**
     * Get the item ID from the URL query arguments.
     *
     * @since 0.1.0
     *
     * @return mixed|false|null Returns false if validation fails, null if not set, otherwise the query value.
     */
    public function get_current_item_id() {
        $item_id = filter_input( INPUT_POST, 'id' );

        if ( $item_id === false || $item_id === null ) {
            $item_id = filter_input( INPUT_GET, 'id' );
        }

        return $item_id;
    }

    /**
     * Is POST request?
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function is_post_request() {
        return (bool) ( strtoupper( $_SERVER['REQUEST_METHOD'] ) === 'POST' );
    }

    /**
     * Is GET request?
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function is_get_request() {
        return (bool) ( strtoupper( $_SERVER['REQUEST_METHOD'] ) === 'GET' );
    }

    /**
     * Check action is allowed.
     *
     * @since 0.1.0
     *
     * @param string $action
     * @param string $method
     * @return bool
     */
    public function is_valid_action( $action, $method = 'GET' ) {
        if ( ! isset( $this->valid_actions[ $action ] ) ) {
            return false;
        }

        if ( is_array( $this->valid_actions[ $action ] ) ) {
            return in_array( $method, $this->valid_actions[ $action ] );
        }

        return $this->valid_actions[ $action ] === $method;
    }

    /**
     * Display page.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page Page being displayed.
     */
    public function display( Content_Page $page ) {
        $action = $this->get_current_action();

        /**
         * Executes before content is displayed on CRUD page.
         *
         * @since 0.1.0
         *
         * @param string       $action
         * @param Page         $crud_page
         * @param Content_Page $content_page
         */
        do_action( 'crud_before_content', $action, $this, $page );

        if ( $action ) {
            /**
             * Executes before action content is displayed on CRUD page.
             *
             * @since 0.1.0
             *
             * @param Page         $crud_page
             * @param Content_Page $content_page
             */
            do_action( "crud_before_{$action}_content", $this, $page );
        }

        $located = plugin_name()->get_template_part( $this->template_dir . $action );

        if ( ! $located ) {
            plugin_name()->get_template_part( 'frontend/crud/' . $action );
        }
    }

    /**
     * Page screen execution.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page Page about to be displayed.
     */
    public function screen( Content_Page $page ) {
        $this->setup_template_message();

        $action = $this->get_current_action();

        if ( ! $action ) {
            return;
        }

        $method = "screen_action_{$action}";

        if ( method_exists( $this, $method ) ) {
            $this->$method( $page );
        }
    }

    /**
     * Execute edit.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     */
    public function screen_action_edit( Content_Page $page ) {
        $item_id = $this->get_current_item_id();
        $form    = $this->get_edit_form();

        if ( ! $item_id || ! $form ) {
            return;
        }

        // Check item id is valid
        if ( ! $this->is_valid_item_id( $item_id ) ) {
            $this->not_found_redirect();
            return;
        }

        // Check user permission on item
        if ( ! $this->user_can_edit_item( $item_id ) ) {
            $this->no_permission_redirect();
            return;
        }

        $item = $this->get_current_edit_item();

        if ( $this->is_post_request() ) {
            // Form submitted, validate nonce
            $this->validate_action_nonce( 'edit', $item_id );

            // Update item with data from POST
            $form->populate_from_post_data( $item );

            // Validate form
            if ( ! $form->is_valid() ) {
                return;
            }

            // Update item
            if ( ! $this->update_item() ) {
                $this->set_message( __( 'Sorry, there was an error updating the item.', 'text-domain' ) );
                return;
            }

            $this->item_updated_redirect();
            return;
        }

        // Displaying form, populate
        $form->populate( $item );
    }

    /**
     * Execute delete.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     */
    public function screen_action_delete( Content_Page $page ) {
        $item_id = $this->get_current_item_id();
        $form    = $this->get_delete_form();

        if ( ! $item_id || ! $form ) {
            return;
        }

        // Check item id is valid
        if ( ! $this->is_valid_item_id( $item_id ) ) {
            $this->not_found_redirect();
            return;
        }

        // Check user permission on item
        if ( ! $this->user_can_edit_item( $item_id ) ) {
            $this->no_permission_redirect();
            return;
        }

        $item = $this->get_current_edit_item();

        if ( $this->is_post_request() ) {
            // Form submitted, validate nonce
            $this->validate_action_nonce( 'delete', $item_id );

            $submit = filter_input( INPUT_POST, 'submit_action' );

            if ( $submit !== 'delete' ) {
                return;
            }

            // Update item
            if ( ! $this->delete_item() ) {
                $this->set_message( __( 'Sorry, the item could not be deleted.', 'text-domain' ) );
                return;
            }

            $this->item_deleted_redirect();
            return;
        }

        // Displaying form, populate
        $form->populate( $item );
    }

    /**
     * Execute create.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     */
    public function screen_action_create( Content_Page $page ) {
        $form = $this->get_edit_form();

        if ( ! $form ) {
            return;
        }

        if ( $this->is_post_request() ) {
            // Form submitted, validate nonce
            $this->validate_action_nonce( 'create' );

            // Set item data from POST
            $form->populate_from_post_data();

            // Validate form
            if ( ! $form->is_valid() ) {
                return;
            }

            // Create item
            if ( ! $this->create_item() ) {
                $this->set_message( __( 'Sorry, there was an error saving the item.', 'text-domain' ) );
                return;
            }

            $this->item_created_redirect();
            return;
        }

        // Displaying form, populate
        $form->populate();
    }

    /**
     * Verify form nonce.
     *
     * @since 0.1.0
     *
     * @param string $action
     * @param mixed  $item_id Optional. Default: null.
     */
    public function validate_action_nonce( $action, $item_id = null ) {
        $nonce               = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
        $edit_nonce_action   = 'crud-action-' . $action . sanitize_key( $item_id );
        $create_nonce_action = 'crud-action-edit';

        if ( ! wp_verify_nonce( $nonce, $edit_nonce_action ) ) {
            if ( $item_id ) {
                $this->no_permission_redirect();
            } elseif ( ! wp_verify_nonce( $nonce, $create_nonce_action ) ) {
                $this->no_permission_redirect();
            }
        }
    }

    /**
     * Check if current user has permission to view the page.
     *
     * By default all CRUD pages do not give permission.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     * @return bool
     */
    public function check_permissions( Content_Page $page ) {
        return false;
    }

    /**
     * Check if user has permission to edit an item.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @param int   $user_id Optional. Default: null.
     * @return bool
     */
    public function user_can_edit_item( $item_id, $user_id = null ) {
        return false;
    }

    /**
     * Check if item ID is valid.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @return bool
     */
    public function is_valid_item_id( $item_id ) {
        return false;
    }

    /**
     * Get template data.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page Page about to be displayed.
     * @return array
     */
    public function get_data( Content_Page $page ) {
        return $this->prepare_template_data( $this->template_data );
    }

    /**
     * Set value in template data.
     *
     * @since 0.1.0
     *
     * @param string $key
     * @param mixed $value
     */
    public function set( $key, $value ) {
        $this->template_data[ $key ] = $value;
    }

    /**
     * Set the list table.
     *
     * @since 0.1.0
     *
     * @param List_Table $table
     */
    public function set_table( List_Table $table ) {
        $this->list_table = $table;
    }

    /**
     * Set the edit form.
     *
     * @since 0.1.0
     *
     * @param Edit_Form $form
     */
    public function set_edit_form( Edit_Form $form ) {
        $this->edit_form = $form;
    }

    /**
     * Get the edit form.
     *
     * @since 0.1.0
     *
     * @return Edit_Form $form
     */
    public function get_edit_form() {
        return $this->edit_form;
    }

    /**
     * Set the delete form.
     *
     * @since 0.1.0
     *
     * @param Delete_Form $form
     */
    public function set_delete_form( delete_Form $form ) {
        $this->delete_form = $form;
    }

    /**
     * Get the delete form.
     *
     * @since 0.1.0
     *
     * @return Delete_Form $form
     */
    public function get_delete_form() {
        return $this->delete_form;
    }

    /**
     * Modify template data before returning to template.
     *
     * @since 0.1.0
     *
     * @param array $data
     * @return array
     */
    protected function prepare_template_data( $data ) {
        $action = $this->get_current_action();

        if ( $action ) {
            if ( method_exists( $this, "prepare_{$action}_template_data" ) ) {
                call_user_func( [ $this, "prepare_{$action}_template_data" ] );
            }
        }

        $data['_crud'] = $this;

        return $data;
    }

    /**
     * Prepare template data when viewing list.
     *
     * @since 0.1.0
     */
    protected function prepare_list_template_data() {
        if ( isset( $this->list_table ) ) {
            $this->list_table->prepare_items( $this->page, $this->per_page );
        }
    }

    /**
     * Set no permission template.
     *
     * @since 0.1.0
     *
     * @param string       $template
     * @param Content_Page $page
     * @return string
     */
    public function no_permission_template( $template, Content_Page $page ) {
        return 'frontend/crud/no-permission';
    }

    /**
     * Get the page URL.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_url() {
        return $this->url;
    }

    /**
     * Get the current item being edited.
     *
     * @since 0.1.0
     *
     * @return false|mixed Returns current edit item, otherwise false.
     */
    public function get_current_edit_item() {
        if ( $this->current_edit_item !== null ) {
            return $this->current_edit_item;
        }

        $item_id = $this->get_current_item_id();

        if ( ! $item_id ) {
            return false;
        }

        $this->current_edit_item = $this->get_item( $item_id );

        return $this->current_edit_item;
    }

    /**
     * Redirect to a URL setting the template message cookie.
     *
     * @since 0.1.0
     *
     * @param string $message
     * @param string $message_type 'success', 'error' or 'info'.
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function message_redirect( $message, $message_type, $url = '' ) {
        $this->set_message( $message, $message_type );

        if ( ! $url ) {
            $url = $this->build_url();
        }

        if ( wp_safe_redirect( $url ) ) {
            exit;
        }
    }

    /**
     * Redirect to a URL setting the no permission error message.
     *
     * @since 0.1.0
     *
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function no_permission_redirect( $url = '' ) {
        $this->message_redirect( __( 'Sorry, you do not have permission to perform the action.', 'text-domain' ), $url );
    }

    /**
     * Redirect to a URL setting the not found error message.
     *
     * @since 0.1.0
     *
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function not_found_redirect( $url = '' ) {
        $this->message_redirect( __( 'The requested item could not be found.', 'text-domain' ), $url );
    }

    /**
     * Redirect to a URL setting the item updated message.
     *
     * @since 0.1.0
     *
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function item_updated_redirect( $url = '' ) {
        $this->message_redirect( __( 'Item updated.', 'text-domain' ), $url );
    }

    /**
     * Redirect to a URL setting the item created message.
     *
     * @since 0.1.0
     *
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function item_created_redirect( $url = '' ) {
        $this->message_redirect( __( 'Item created.', 'text-domain' ), $url );
    }

    /**
     * Redirect to a URL setting the item deleted message.
     *
     * @since 0.1.0
     *
     * @param string $url Optional. If empty, base URL is used. Default: ''.
     */
    public function item_deleted_redirect( $url = '' ) {
        $this->message_redirect( __( 'Item deleted.', 'text-domain' ), $url );
    }

    /**
     * Set message to display at top of page.
     *
     * @since 0.1.0
     *
     * @param string $message
     * @param string $type
     */
    public function set_message( $message, $type = 'success' ) {
        $this->template_message = [
            'type'    => $type,
            'message' => $message,
        ];

        // Send the values to the cookie for page reload display.
        @setcookie( 'crud-message',      $message, time() + 60 * 60 * 24, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
        @setcookie( 'crud-message-type', $type,    time() + 60 * 60 * 24, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
    }

    /**
     * Get the top page message.
     *
     * @since 0.1.0
     *
     * @return array|null
     */
    public function get_template_message() {
        return $this->template_message;
    }

    /**
     * Get HTML for displaying page message.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_template_message_html() {
        if ( ! $this->template_message ) {
            return '';
        }

        return sprintf(
            '<div class="crud-message crud-message-%s">%s</div>',
            $this->template_message['type'],
            $this->template_message['message']
        );
    }

    /**
     * Setup template message from cookies.
     *
     * @since 0.1.0
     */
    public function setup_template_message() {
        if ( empty( $this->template_message ) ) {
            if ( isset( $_COOKIE['crud-message'] ) ) {
                $this->template_message = [
                    'type'    => 'success',
                    'message' => stripslashes( $_COOKIE['crud-message'] ),
                ];
            }

            if ( isset( $_COOKIE['crud-message-type'] ) ) {
                $this->template_message['type'] = stripslashes( $_COOKIE['crud-message-type'] );
            }
        }

        if ( isset( $_COOKIE['crud-message'] ) ) {
            @setcookie( 'crud-message', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
        }

        if ( isset( $_COOKIE['crud-message-type'] ) ) {
            @setcookie( 'crud-message-type', false, time() - 1000, COOKIEPATH, COOKIE_DOMAIN, is_ssl() );
        }
    }

    /**
     * Get the page actions.
     *
     * @since 0.1.0
     *
     * @return Page_Action[]
     */
    public function get_page_actions() {
        return $this->page_actions;
    }

    /**
     * Setup pagination variables from query args.
     *
     * @since 0.1.0
     */
    public function setup_pagination() {
        $page     = (int) filter_input( INPUT_GET, 'crud_page', FILTER_SANITIZE_NUMBER_INT );
        $per_page = (int) filter_input( INPUT_GET, 'crud_per_page', FILTER_SANITIZE_NUMBER_INT );

        if ( $page >= 1 ) {
            $this->page = $page;
        }

        if ( $per_page > 1 && $per_page <= $this->max_per_page ) {
            $this->per_page = $per_page;
        }
    }

    /**
     * Get pagination HTML.
     *
     * @see paginate_links()
     * @since 0.1.0
     *
     * @param array Pagination arguments.
     * @return string|array|void
     */
    public function get_pagination_html( $args = [] ) {
        $r = wp_parse_args( $args, [
            'base'     => $this->get_url() . '%_%',
            'total'    => ceil( $this->list_table->get_total_items() / $this->per_page ),
            'current'  => $this->page,
            'format'   => '?crud_page=%#%&crud_per_page=' . $this->per_page,
            'end_size' => 5,
            'mid_size' => 5,
        ] );

        $html = '<div class="crud-pagination">';
        $html .= paginate_links( $r );
        $html .= '</div>';

        return $html;
    }

    /**
     * Get an item to edit.
     *
     * @since 0.1.0
     *
     * @param mixed $item_id
     * @return mixed|false Returns item if found, otherwise false.
     */
    abstract public function get_item( $item_id );

    /**
     * Update the item with data from form.
     *
     * @since 0.1.0
     *
     * @return bool Returns true if updated, otherwise false.
     */
    abstract public function update_item();

    /**
     * Delete the item.
     *
     * @since 0.1.0
     *
     * @return bool Returns true if updated, otherwise false.
     */
    abstract public function delete_item();
}
