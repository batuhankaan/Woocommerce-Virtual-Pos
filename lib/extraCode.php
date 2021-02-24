<?php


if ( ! defined( 'ABSPATH' ) ) { return; }

add_action( 'admin_enqueue_scripts', 'add_page_scripts_enqueue_script' );

/**
 * Enqueue the Code Editor and JS
 *
 * @param string $hook
 */
function add_page_scripts_enqueue_script( $hook ) {
    global $post;

    if ( ! $post ) { return; }

    if ( ! 'page' === $post->post_type ) { return; }
 
    if( 'post.php' === $hook || 'post-new.php' === $hook ) {
        wp_enqueue_code_editor( array( 'type' => 'text/html' ) );
        wp_enqueue_script( 'js-code-editor', plugin_dir_url( __FILE__ ) . '/code-editor.js', array( 'jquery' ), '', true );
    }
}

add_action( 'add_meta_boxes', 'add_page_scripts' );

function add_page_scripts() {
    add_meta_box( 'page-scripts', __( 'Page Scripts & Styles', 'textdomain' ), 'add_page_metabox_scripts_html', 'page', 'advanced' );
}

function add_page_metabox_scripts_html( $post ) {
    $post_id = $post->ID;
    $page_scripts = get_post_meta( $post_id, 'page_scripts', true );
    if ( ! $page_scripts ) {
        $page_scripts = array(
            'page_head' => '',
            'js'        => '',
            'css'       => '',
        );
    }
    ?>
    <fieldset>
        <h3>Head Scripts</h3>
        <p class="description">Enter scripts and style with the tags such as <code>&lt;script&gt;</code></p>
        <textarea id="code_editor_page_head" rows="5" name="page_scripts[page_head]" class="widefat textarea"><?php echo wp_unslash( $page_scripts['page_head'] ); ?></textarea>   
    </fieldset>
    
    <fieldset>
        <h3>Only JavaScript</h3>
        <p class="description">Just write javascript.</p>
        <textarea id="code_editor_page_js" rows="5" name="page_scripts[js]" class="widefat textarea"><?php echo wp_unslash( $page_scripts['js'] ); ?></textarea>   
    </fieldset>

    <fieldset>
        <h3>Only CSS</h3>
        <p class="description">Do your CSS magic</p>
        <textarea id="code_editor_page_css" rows="5" name="page_scripts[css]" class="widefat textarea"><?php echo wp_unslash( $page_scripts['css'] ); ?></textarea>   
    </fieldset>
    <?php
}