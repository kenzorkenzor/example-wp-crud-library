<?php

namespace Plugin\Example\Lib\Crud;

/**
 * Helper for implementing CRUD forms.
 *
 * @since 0.1.0
 */
abstract class Form {
    /**
     * @since 0.1.0
     * @var string $action
     */
    protected $action;

    /**
     * @since 0.1.0
     * @var string $id
     */
    protected $id;

    /**
     * @since 0.1.0
     * @var string Submit method.
     */
    protected $method = 'POST';

    /**
     * @since 0.1.0
     * @var string Submit URL.
     */
    protected $url = '';

    /**
     * @since 0.1.0
     * @var string URL to return to when cancel button is pressed.
     */
    protected $cancel_url = '';

    /**
     * @since 0.1.0
     * @var array Form data.
     */
    protected $data = [];

    /**
     * @since 0.1.0
     * @var array Validation errors.
     */
    protected $errors = [];

    /**
     * Constructor.
     *
     * @since 0.1.0
     *
     * @param string $action The CRUD action (eg. 'edit')
     * @param string $id     HTML element id suffix.
     */
    public function __construct( $action, $id = null ) {
        $this->action = $action;
        $this->id     = $id;
    }

    /**
     * Set the submit URL.
     *
     * @since 0.1.0
     *
     * @param string $url
     */
    public function set_url( $url ) {
        $this->url = $url;
    }

    /**
     * Set the cancel URL.
     *
     * @since 0.1.0
     *
     * @param string $url
     */
    public function set_cancel_url( $url ) {
        $this->cancel_url = $url;
    }

    /**
     * Output form header.
     *
     * @since 0.1.0
     */
    public function start() {
        /**
         * Executes before start of CRUD form.
         *
         * @since 0.1.0
         *
         * @param Form $form
         */
        do_action( 'crud_before_form', $this );

        /**
         * Filters the form URL.
         *
         * @since 0.1.0
         *
         * @param string $url
         * @param Form   $form
         */
        $url = apply_filters( 'crud_form_url', $this->url, $this );

        // Build css classes
        $classes = [ 'crud-' . sanitize_key( $this->action ) ];

        if ( $this->id ) {
            $classes[] = $classes[0] . '-' . sanitize_key( $this->id );
        }

        /**
         * Filters the form CSS classes.
         *
         * @since 0.1.0
         *
         * @param array $classes
         * @param Form  $form
         */
        $classes = apply_filters( 'crud_form_css_classes', $classes, $this );

        // Build element id
        $element_id = 'crud-' . sanitize_key( $this->action );

        if ( $this->id ) {
            $element_id .= '-' . sanitize_key( $this->id );
        }
        ?>

            <form action="<?php echo esc_attr( $url ); ?>"
                  method="<?php echo esc_attr( $this->method ); ?>"
                  class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
                  id="<?php echo esc_attr( $element_id ); ?>">

        <?php
        $nonce_action = 'crud-action-' . $this->action;
        $id           = $this->get_field( 'id' );

        if ( $id ) {
            $nonce_action .= sanitize_key( $id );
        }

        wp_nonce_field( $nonce_action );

        if ( $this->has_errors() ):
        ?>

            <div class="crud-errors">
                <?php esc_html_e( 'Please fix the errors below.', 'text-domain' ); ?>
            </div>

        <?php
        endif;

        /**
         * Executes before start of CRUD form content.
         *
         * @since 0.1.0
         *
         * @param Form $form
         */
        do_action( 'crud_before_form_content', $this );
    }

    /**
     * Output form footer.
     *
     * @since 0.1.0
     */
    public function end() {
        /**
         * Executes after CRUD form content.
         *
         * @since 0.1.0
         *
         * @param Form $form
         */
        do_action( 'crud_after_form_content', $this );
        ?>

            </form>

        <?php
        /**
         * Executes after CRUD form.
         *
         * @since 0.1.0
         *
         * @param Form $form
         */
        do_action( 'crud_after_form', $this );
    }

    /**
     * Get validation error HTML for a field.
     *
     * @since 0.1.0
     *
     * @param string $field_name
     * @return string
     */
    public function get_field_error_html( $field_name ) {
        if ( ! isset( $this->errors[ $field_name ] ) ) {
            return '';
        }

        $html = '<div class="crud-field-errors">';

        foreach ( $this->errors[ $field_name ] as $msg ) {
            $html .= sprintf(
                '<div class="crud-field-error">%s</div>',
                $msg
            );
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * Get label for indicating a field is required.
     *
     * @since 0.1.0
     *
     * @return string
     */
    public function get_required_label() {
        return '<span>*</span>';
    }

    /**
     * Check if form is valid.
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function is_valid() {
        $this->errors = [];
        $this->validate();

        return ! $this->has_errors();
    }

    /**
     * Check if form has any errors.
     *
     * @since 0.1.0
     *
     * @return bool
     */
    public function has_errors() {
        return ! empty( $this->errors );
    }

    /**
     * Set a field value.
     *
     * @since 0.1.0
     *
     * @param string $name
     * @param mixed  $value
     */
    public function set_field( $name, $value ) {
        if ( ! isset( $this->data['fields'] ) ) {
            $this->data['fields'] = [];
        }

        $this->data[ $name ] = $value;
    }

    /**
     * Set a field value.
     *
     * @since 0.1.0
     *
     * @param string $name
     * @return Value if present, otherwise null.
     */
    public function get_field( $name ) {
        if ( ! isset( $this->data['fields'] ) ) {
            return null;
        }

        return isset( $this->data['fields'][ $name ] ) ? $this->data['fields'][ $name ] : null;
    }

    /**
     * Get all field values.
     *
     * @since 0.1.0
     *
     * @return array
     */
    public function get_fields() {
        return isset( $this->data['fields'] ) ? $this->data['fields'] : [];
    }

    /**
     * Set multiple fields.
     *
     * @since 0.1.0
     *
     * @param array $fields
     * @param bool  $clear Optional. Clear all fields. Default: false.
     */
    public function set_fields( $fields, $clear = false ) {
        if ( $clear ) {
            $this->data['fields'] = [];
        }

        if ( ! is_array( $fields ) ) {
            return;
        }

        foreach ( $fields as $field => $value ) {
            $this->data['fields'][ $field ] = $value;
        }
    }

    /**
     * Sanitize a field value.
     *
     * Calls sanitize_{$field}_value function on this class if it exists.
     *
     * @param string $field
     * @param mixed  $value
     * @return mixed
     */
    public function sanitize_field_value( $field, $value ) {
        $key    = sanitize_key( $field );
        $method = "sanitize_{$key}_field";

        if ( method_exists( $this, $method ) ) {
            $value = $this->$method( $value );
        }

        return $value;
    }

    /**
     * Add validation error.
     *
     * @since 0.1.0
     *
     * @param string $field_name
     * @param string $error_msg
     */
    public function add_error( $field_name, $error_msg ) {
        if ( ! isset( $this->errors[ $field_name ] ) ) {
            $this->errors[ $field_name ] = [];
        }

        $this->errors[ $field_name ][] = $error_msg;
    }

    /**
     * Output form content.
     *
     * @since 0.1.0
     */
    abstract public function content();

    /**
     * Populate form data from data source.
     *
     * @since 0.1.0
     *
     * @param mixed $item Optional. Default: null.
     */
    abstract public function populate( $item = null );
}
