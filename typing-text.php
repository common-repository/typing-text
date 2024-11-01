<?php

/**
 * Plugin Name:     Typing Text
 * Description:     Make Your Website Interactive With Typing Text Animation
 * Version:         1.2.7
 * Author:          WPDeveloper
 * Author URI:      https://wpdeveloper.net
 * License:         GPL-3.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:     typing-text
 *
 * @package         typing-text
 */

/**
 * Registers all block assets so that they can be enqueued through the block editor
 * in the corresponding context.
 *
 * @see https://developer.wordpress.org/block-editor/tutorials/block-tutorial/applying-styles-with-stylesheets/
 */

require_once __DIR__ . '/includes/font-loader.php';
require_once __DIR__ . '/includes/post-meta.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/lib/style-handler/style-handler.php';

function create_block_typing_text_block_init() {

    define( 'TYPING_TEXT_BLOCKS_VERSION', "1.2.7" );
    define( 'TYPING_TEXT_BLOCKS_ADMIN_URL', plugin_dir_url( __FILE__ ) );
    define( 'TYPING_TEXT_BLOCKS_ADMIN_PATH', dirname( __FILE__ ) );

    $script_asset_path = TYPING_TEXT_BLOCKS_ADMIN_PATH . "/dist/index.asset.php";
    if ( ! file_exists( $script_asset_path ) ) {
        throw new Error(
            'You need to run `npm start` or `npm run build` for the "typing-text/typing-text-block" block first.'
        );
    }
    $index_js         = TYPING_TEXT_BLOCKS_ADMIN_URL . 'dist/index.js';
    $script_asset     = require $script_asset_path;
    $all_dependencies = array_merge( $script_asset['dependencies'], [
        'wp-blocks',
        'wp-i18n',
        'wp-element',
        'wp-block-editor',
        'typing-text-blocks-controls-util',
        'essential-blocks-eb-animation'
    ] );

    wp_register_script(
        'typing-text-block-editor-js',
        $index_js,
        $all_dependencies,
        $script_asset['version']
    );

    $load_animation_js = TYPING_TEXT_BLOCKS_ADMIN_URL . 'assets/js/eb-animation-load.js';
    wp_register_script(
        'essential-blocks-eb-animation',
        $load_animation_js,
        [],
        TYPING_TEXT_BLOCKS_VERSION,
        true
    );

    $animate_css = TYPING_TEXT_BLOCKS_ADMIN_URL . 'assets/css/animate.min.css';
    wp_register_style(
        'essential-blocks-animation',
        $animate_css,
        [],
        TYPING_TEXT_BLOCKS_VERSION
    );

    $style_css = TYPING_TEXT_BLOCKS_ADMIN_URL . 'dist/style.css';
    wp_register_style(
        'typing-text-block-frontend-style',
        $style_css,
        ["essential-blocks-animation"],
        filemtime( TYPING_TEXT_BLOCKS_ADMIN_PATH . '/dist/style.css' )
    );

    $typed_js = TYPING_TEXT_BLOCKS_ADMIN_URL . 'assets/js/typed.min.js';
    wp_register_script(
        'typig-text-blocks-typedjs',
        $typed_js,
        ["jquery"],
        true
    );

    $frontend_js_path = include_once dirname( __FILE__ ) . "/dist/frontend/index.asset.php";
    $frontend_js      = "dist/frontend/index.js";
    wp_register_script(
        'eb-typing-text-frontend',
        plugins_url( $frontend_js, __FILE__ ),
        array_merge( ["typig-text-blocks-typedjs", "jquery", "essential-blocks-eb-animation"], $frontend_js_path['dependencies'] ),
        $frontend_js_path['version'],
        true
    );

    if ( ! WP_Block_Type_Registry::get_instance()->is_registered( 'essential-blocks/typing-text' ) ) {
        register_block_type(
            Typing_Text_Helper::get_block_register_path( 'typing-text/typing-text-block', TYPING_TEXT_BLOCKS_ADMIN_PATH ),
            [
                'editor_script' => 'typing-text-block-editor-js',
                'style'         => 'typing-text-block-frontend-style',
                'script'        => 'eb-typing-text-frontend'
            ]
        );
    }
}

add_action( 'init', 'create_block_typing_text_block_init', 99 );
