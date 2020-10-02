<?php

namespace Plugin\Example\Lib\Crud;

/**
 * Page action model.
 *
 * @since 0.1.0
 */
class Page_Action {
    /**
     * @since 0.1.0
     * @var string Identifier.
     */
    public $id;

    /**
     * @since 0.1.0
     * @var string Label.
     */
    public $label;

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $id
     * @param string $label
     */
    public function __construct( $id, $label ) {
        $this->id    = $id;
        $this->label = $label;
    }

    /**
     * Get the URL for the action.
     *
     * @since 0.1.0
     *
     * @param Page $page
     * @return string Returns the URL for the action.
     */
    public function get_url( Page $page ) {
        $url = add_query_arg( [
            'action'    => $this->id,
            'return_to' => urlencode( $page->get_url() ),
        ], $page->get_url() );

        /**
         * Filters the URL of an action for a page.
         *
         * @since 0.1.0
         *
         * @param string $url
         * @param Page   $page
         */
        return apply_filters( 'crud_page_action_url', $url, $page );
    }

    /**
     * Get HTML for button.
     *
     * @since 0.1.0
     *
     * @param Page $page
     * @return string
     */
    public function get_button_html( Page $page ) {
        $classes = [ 'crud-page-action-btn', 'crud-page-action-btn-' . sanitize_key( $this->id ) ];
        $html    = sprintf(
            '<a href="%s" class="%s">%s</a>',
            $this->get_url( $page ),
            implode( ' ', $classes ),
            esc_html( $this->label )
        );

        /**
         * Filters the HTML of a page action button.
         *
         * @since 0.1.0
         *
         * @param string      $html
         * @param Page_Action $action
         * @param Page        $page
         */
        return apply_filters( 'crud_page_action_button_html', $html, $this, $page );
    }
}
