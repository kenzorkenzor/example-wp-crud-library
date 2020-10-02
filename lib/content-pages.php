<?php
namespace Plugin\Example\Lib;

use Plugin\Example\Admin\Settings_Store;
use Plugin\Example\Models\Content_Page;

/**
 * Features relating to displaying plugin content on specific pages.
 *
 * @since 0.1.0
 */
class Content_Pages {
    /**
     * @var Content_Page[] List of registered pages.
     * @since 0.1.0
     */
    public $pages = [];

    /**
     * @var Content_Page Current page being displayed.
     * @since 0.1.0
     */
    public $current_page;

    /**
     * @var mixed Template data set by registered pages.
     * @since 0.1.0
     */
    public $template_data;

    /**
     * Constructor.
     *
     * @since 0.1.0
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Init hooks.
     *
     * @since 0.1.0
     */
    public function init_hooks() {
        add_filter( 'the_content', [ $this, 'display_page' ] );
        add_filter( 'template_redirect', [ $this, 'execute_screen' ] );
        add_filter( 'example_content_page_template_data', [ $this, 'template_data' ] );
    }

    /**
     * Register a new content page.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     */
    public function register_page( Content_Page $page ) {
        $this->pages[] = $page;
    }

    /**
     * Get registered pages.
     *
     * @since 0.1.0
     *
     * @return Content_Page[]
     */
    public function get_registered() {
        return $this->pages;
    }

    /**
     * Get the post ID of the WP page saved in settings.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     * @return int|false
     */
    public function get_assigned_post_id( Content_Page &$page ) {
        if ( $page->post_id ) {
            return $page->post_id;
        }

        $post_id = Settings_Store::get( 'page_' . $page->id );

        if ( ! $post_id ) {
            return false;
        }

        $page->post_id = (int) $post_id;

        return $page->post_id;
    }

    /**
     * Get the current page being displayed.
     *
     * @since 0.1.0
     *
     * @return Content_Page|false
     */
    public function get_current_page() {
        if ( $this->current_page ) {
            return $this->current_page;
        }

        if ( ! is_page() ) {
            return false;
        }

        foreach ( $this->get_registered() as &$page ) {
            $post_id = $this->get_assigned_post_id( $page );

            if ( ! $post_id ) {
                continue;
            }

            if ( is_page( $post_id ) ) {
                $this->current_page = $page;

                return $page;
            }
        }

        unset( $page );

        return false;
    }

    /**
     * Is the current page a content page?
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function is_content_page() {
        return $this->get_current_page() ? true : false;
    }

    /**
     * Maybe display one of our content pages.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function display_page() {
        $current_page = $this->get_current_page();

        if ( ! $current_page ) {
            return '';
        }

        if ( ! $current_page->display_callback || ! is_callable( $current_page->display_callback ) ) {
            return '';
        }

        if ( $current_page->permission_callback && is_callable( $current_page->permission_callback ) ) {
            if ( ! call_user_func( $current_page->permission_callback, $current_page ) ) {
                return $this->get_no_permission_content( $current_page );
            }
        }

        ob_start();

        call_user_func( $current_page->display_callback, $current_page );

        return ob_get_clean();
    }

    /**
     * Get content of no permission template.
     *
     * @param Content_Page $current_page
     * @return string
     */
    public function get_no_permission_content( $current_page ) {
        /**
         * Filters the template to display when a user does not have permission to view content page.
         *
         * @since 0.1.0
         *
         * @param string       $template
         * @param Content_Page $current_page
         */
        $template = apply_filters( 'example_no_permission_template', null, $current_page );

        if ( ! $template ) {
            return '';
        }

        ob_start();

        plugin_name()->get_template_part( $template );

        // Get the output buffer contents.
        $output = ob_get_clean();

        if ( ! $output ) {
            return '';
        }

        return $output;
    }

    /**
     * Maybe execute screen callback.
     *
     * @since 0.1.0
     */
    public function execute_screen() {
        $current_page = $this->get_current_page();

        if ( ! $current_page ) {
            return;
        }

        if ( ! $current_page->screen_callback || ! is_callable( $current_page->screen_callback ) ) {
            return;
        }

        call_user_func( $current_page->screen_callback, $current_page );
    }

    /**
     * Get the current page template data.
     *
     * @since 0.1.0
     *
     * @return mixed|void
     */
    public function template_data() {
        $current_page = $this->get_current_page();

        if ( ! $current_page ) {
            return;
        }

        if ( ! $current_page->data_callback || ! is_callable( $current_page->data_callback ) ) {
            return;
        }

        return call_user_func( $current_page->data_callback, $current_page );
    }

    /**
     * Get URL of a page.
     *
     * @since 0.1.0
     *
     * @param Content_Page $page
     * @return string
     */
    public function get_url( Content_Page $page ) {
        $post_id = $this->get_assigned_post_id( $page );

        return get_permalink( $post_id );
    }
}
