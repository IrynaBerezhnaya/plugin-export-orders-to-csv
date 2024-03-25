<?php
/**
 * Connecting scripts and styles to admin page
 */

function ib_callback_for_setting_up_scripts() {
	wp_enqueue_style( 'admin-style', plugins_url( 'admin/assets/css/style.css', __FILE__ ) );
	wp_enqueue_script( 'main-js', plugins_url( 'admin/assets/js/main.js', __FILE__ ), array( 'jquery' ), null, true );

	wp_enqueue_script( 'vue', 'https://cdn.jsdelivr.net/npm/vue@2.7.8/dist/vue.js', array(), false );

	wp_enqueue_style( 'vue-multiselect', 'https://unpkg.com/vue-multiselect@2.1.6/dist/vue-multiselect.min.css', array(), false );
	wp_enqueue_script( 'vue-multiselect', 'https://unpkg.com/vue-multiselect@2.1.6', array(), false );
}
add_action('admin_enqueue_scripts', 'ib_callback_for_setting_up_scripts');
