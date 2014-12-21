<?php

/*
Widget code.
Written by Dustin Bolton, Ronald Huereca, and Chris Jean for iThemes.com
Version 1.3.0

Version History
	1.3.0 - 2012-12-01 - Chris Jean
		Starting at version 1.3.0 to match the plugin release version.
		Misc code cleanup.
		Added title support.
		Added support for WordPress standard widget output.
*/


class widget_pluginbuddy_loopbuddy extends WP_Widget {
	/**
	 * Default constructor.
	 *
	 * @return void
	 */
	function widget_pluginbuddy_loopbuddy() {
		global $pluginbuddy_loopbuddy;
		$this->_parent = &$pluginbuddy_loopbuddy;
		
		$widget_ops = array( 'classname' => 'pb_loopbuddy_widget', 'description' => $this->_parent->_widget );
		$control_ops = array( 'width' => 350 );
		
		$this->WP_Widget( $this->_parent->_var, $this->_parent->_name, $widget_ops, $control_ops );
		
		
		add_action( $this->_parent->_var . '-widget', array( &$this->_parent, 'widget' ), 10, 3 );
	}
	
	
	/**
	 * widget()
	 *
	 * Display public widget.
	 *
	 * @param	array	$args		Widget arguments -- currently not in use.
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function widget( $args, $instance ) {
		do_action( $this->_parent->_var . '-widget', $instance, true, $args );
	}
	
	
	/**
	 * update()
	 *
	 * Save widget form settings.
	 *
	 * @param	array	$new_instance	NEW instance data including title, group id, etc.
	 * @param	array	$old_instance	PREVIOUS instance data including title, group id, etc.
	 * @return	array					Instance data to save for this widget.
	 */
	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
	
	
	/**
	 * form()
	 *
	 * Display widget control panel.
	 *
	 * @param	array	$instance	Instance data including title, group id, etc.
	 * @return	void
	 */
	function form( $instance ) {
		global $pluginbuddy_loopbuddy;
		$instance = array_merge( (array) $pluginbuddy_loopbuddy->_widgetdefaults, (array) $instance );
		$this->_parent->widget_form( $instance, $this );
	}
}

function widget_pluginbuddy_loopbuddy_init() {
	register_widget( 'widget_pluginbuddy_loopbuddy' );
}
add_action( 'widgets_init', 'widget_pluginbuddy_loopbuddy_init' );
