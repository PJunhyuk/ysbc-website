<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// Don't duplicate this class
if ( !class_exists( 'Tag_Generator' ) ) :
    /**
     * A set of helper methods to create tags.
     */
    class Tag_Generator {
        /**
         * Turn an array of styles into an inline css rules
         *
         * @param array $styles Array of styles
         *
         * @return string String with inline css rules
         */
        public function generate_inline_styles ( array $styles ) {
            $result = '';

            // A list of dimension-type attributes
            $dimensions = array( 'font-size', 'width', 'height' );

            /*
             * Loop through the all title style attributes and assign a value
             *
             * For example:
             * font-weight: 700; font-size: 16px; color: #000000;
             *
             */
            foreach ( $styles as $key => $value ) {
                // Only if value is set
                if ( !empty( $value ) ) {
                    // If it's a dimension
                    if ( in_array( $key, $dimensions ) ) {
                        $result .= $key . ': ' . esc_attr( $this->handle_dimensions( $value ) ) . '; ';
                    } else {
                        $result .= $key . ': ' . esc_attr( $value ) . '; ';
                    }
                }
            }

            return $result;
        }

        /**
         * Checks if value is numeric, when it's being handled as value in pixels,
         * but it's not a numeric value, take it "as is"
         *
         * @param mixed $size Size value
         *
         * @return string Converted size
         */
        protected function handle_dimensions ( $size ) {
            // Check if value is numeric
            if ( is_numeric( $size ) ) {
                // Turn it into integer and add 'px' to the end
                return intval( $size ) . 'px';
            } else {
                // It's a string, pass value "as is"
                return $size ;
            }
        }

        /**
         * Turn an array of properties into a string, so that it could be
         *
         * @param array $properties Array of properties to combine
         *
         * @return string String with inline css rules
         */
        public function combine_properties ( array $properties ) {
            $result = implode( ' ', $properties );

            return $result;
        }

        /**
         * Turn an array of attributes into string
         *
         * @param array $attr Array of attributes
         *
         * @return string String with inline css rules
         */
        public function generate_tag_attributes( array $attr ) {
            $result = '';

            /*
             * Loop through the all attributes and assign a value
             *
             * For example:
             * src="http://example.com/path/to/image.png"
             * width="600"
             * height="500"
             *
             */
            foreach ( $attr as $key => $value ) {
                // Is it a nested array of properties?
                if ( is_array( $value ) ) {
                    // If it's an array of inline styles
                    if ( $key == 'style' ) {
                        // Generate inline styles and assign to 'style' property
                        $result .= $this->html_tag_property( $key, $this->generate_inline_styles( $value ) );
                    } else {
                        // Join values and assign to the property
                        $result .= $this->html_tag_property( $key, $this->combine_properties( $value ) );
                    }
                } else {
                    // Assign value to the property
                    $result .= $this->html_tag_property( $key, $value );
                }
            }

            return $result;
        }

        /**
         * Make an html tag attribute
         *
         * @param string $key Name of the property
         * @param string $value Value
         *
         * @return string Formatted string
         */
        protected function html_tag_property( $key, $value ) {
            $result = '';

            // A list of attributes, that will be included if they're empty
            $not_empty = array( 'style', 'class', 'id', 'width', 'height', 'target' );

            // Check if attributes are not empty
            if ( ! ( empty( $value ) && in_array( $key, $not_empty ) ) ) {
                $result .= $key . '="' . esc_attr( $value ) . '" ';
            }

            return $result;
        }
    }
endif;