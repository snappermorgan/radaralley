<?php

/*
/* WYSIWYG widget module
============================================*/

class WPZOOM_Wyswig extends WP_Widget {

	public function __construct() {

		parent::__construct(
			'wpzoom-wysiwyg',
			__( 'WYSIWYG', 'zoom-builder' ),
			array(
				'description' => __( 'Arbitrary text or HTML with visual editor', 'zoom-builder' ),
				'wpzlb_widget' => true
			),
			array(
				'width' => 600,
				'height' => 500
			)
		);

	}

    public function widget( $args, $instance ) {
        if ( get_option( 'embed_autourls' ) ) {
            $wp_embed = $GLOBALS['wp_embed'];
            add_filter( 'widget_text', array( $wp_embed, 'run_shortcode' ), 8 );
            add_filter( 'widget_text', array( $wp_embed, 'autoembed' ), 8 );
        }

        extract( $args );

        /* User-selected settings. */
        $title = apply_filters('widget_title', $instance['title'] );
        $text = apply_filters( 'widget_text', $instance['text'] );

        if( function_exists( 'icl_t' )) {
            $title = icl_t( "Widgets", 'widget title - ' . md5 ( $title ), $title );
            $text = icl_t( "Widgets", 'widget body - ' . $this->id_base . '-' . $this->number, $text );
        }

        $text = do_shortcode( $text );

        /* Before widget (defined by themes). */
        echo $before_widget;

        /* Title of widget (before and after defined by themes). */
        if ( $title )
            echo $before_title . $title . $after_title; ?>

            <div class="textwidget"><?php echo $text; ?></div>

        <?php
        /* After widget (defined by themes). */
        echo $after_widget;
    }


    public function update( $new_instance, $old_instance ) {
        $instance = $old_instance;

        /* Strip tags (if needed) and update the widget settings. */
        $instance['title'] = strip_tags( $new_instance['title'] );

        if ( current_user_can( 'unfiltered_html' ) ) {
            $instance['text'] =  $new_instance['text'];
        } else {
            $instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) ); // wp_filter_post_kses() expects slashed
        }

        $instance['type'] = strip_tags( $new_instance['type'] );

        if ( function_exists( 'icl_register_string' ) ) {
            //icl_register_string( "Widgets", 'widget title - ' . $this->id_base . '-' . $this->number /* md5 ( apply_filters( 'widget_title', $instance['title'] ))*/, apply_filters( 'widget_title', $instance['title'] )); // This is handled automatically by WPML
            icl_register_string( "Widgets", 'widget body - ' . $this->id_base . '-' . $this->number  /* md5 ( apply_filters( 'widget_text', $instance['text'] ))*/, apply_filters( 'widget_text', $instance['text'] ));
        }

        return $instance;
    }

    public function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'type' => 'visual' ) );

        $title = $instance['title'];
        $text = esc_textarea( $instance['text'] );
        $type = $instance['type'];

        ?>

        <input id="<?php echo $this->get_field_id( 'type' ); ?>" name="<?php echo $this->get_field_name( 'type' ); ?>" type="hidden" value="<?php echo esc_attr( $type ); ?>" />
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'zoom-builder' ); ?></label>

        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
        <div class="editor_toggle_buttons hide-if-no-js wp-toggle-buttons">
            <a id="widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-html"<?php if ( $type == 'html' ) {?> class="active"<?php }?>><?php _e( 'HTML', 'zoom-builder' ); ?></a>
            <a id="widget-<?php echo $this->id_base; ?>-<?php echo $this->number; ?>-visual"<?php if ( $type == 'visual' ) {?> class="active"<?php }?>><?php _e(' Visual', 'zoom-builder' ); ?></a>
        </div>
        <div class="editor_media_buttons hide-if-no-js wp-media-buttons">
            <?php do_action( 'media_buttons' ); ?>
        </div>
        <div class="wysiwyg_editor_container">
            <textarea class="widefat" rows="20" cols="40" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name('text'); ?>"><?php echo $text; ?></textarea>
        </div>

        <?php
    }

    public static function admin_init($force) {
        if ( !ZOOM_Builder_Utils::screen_is_builder() || !current_user_can( 'edit_posts' ) ) return;

        add_action( 'admin_head', array( 'WPZOOM_Wyswig', 'load_tinymce' ) );
        add_action( 'admin_print_scripts', array( 'WPZOOM_Wyswig', 'scripts' ) );
        add_action( 'admin_print_footer_scripts', array( 'WPZOOM_Wyswig', 'footer_scripts' ) );
        add_action( 'admin_print_styles', array( 'WPZOOM_Wyswig', 'styles' ) );
    }

    public static function load_tinymce() {
            // Remove filters added from "After the deadline" plugin, to avoid conflicts
            remove_filter( 'mce_external_plugins', 'add_AtD_tinymce_plugin' );
            remove_filter( 'mce_buttons', 'register_AtD_button' );
            remove_filter( 'tiny_mce_before_init', 'AtD_change_mce_settings' );

            // Thinkbox
            add_thickbox();
            wp_enqueue_media();
    }

    public static function scripts() {
        wp_enqueue_script( 'media-upload' );
        wp_enqueue_script( 'wplink' );
        wp_enqueue_script( 'wpdialogs-popup' );

        if ( user_can_richedit() ) {
            wp_enqueue_script( 'wpzoom-wysiwyg', plugins_url( '/wysiwyg.js', __FILE__ ), array('jquery'), ZOOM_Builder::$version );
        }
    }

    public static function footer_scripts() {
        wp_editor('', 'wpzoom-wysiwyg-widget');
    }

    public static function styles() {
        wp_enqueue_style( 'wp-jquery-ui-dialog' );
        wp_print_styles( 'editor-buttons ');
        wp_enqueue_style( 'wpzoom-wysiwyg', plugins_url( '/wysiwyg.css', __FILE__ ), array(), ZOOM_Builder::$version );
    }
}

function wpzoom_register_wy_widget() {
    register_widget( 'WPZOOM_Wyswig' );
}
add_action( 'widgets_init', 'wpzoom_register_wy_widget' );

add_action( 'admin_init', array( 'WPZOOM_Wyswig', 'admin_init' ) );