<?php

class DSLC_Tabs extends DSLC_Module {

	var $module_id;
	var $module_title;
	var $module_icon;
	var $module_category;

	function __construct() {

		$this->module_id = 'DSLC_Tabs';
		$this->module_title = __( 'Tabs', 'dslc_string' );
		$this->module_icon = 'list';
		$this->module_category = 'elements';

	}

	function options() {	

		$dslc_options = array(
			array(
				'label' => __( '(hidden) Tabs Content', 'dslc_string' ),
				'id' => 'tabs_content',
				'std' => '',
				'type' => 'textarea',
				'visibility' => 'hidden',
				'section' => 'styling',
			),
			array(
				'label' => __( '(hidden) Tabs Nav', 'dslc_string' ),
				'id' => 'tabs_nav',
				'std' => '',
				'type' => 'textarea',
				'visibility' => 'hidden',
				'section' => 'styling',
			),

			/**
			 * Tabs Nav
			 */

			array(
				'label' => __( ' BG Color', 'dslc_string' ),
				'id' => 'css_nav_bg_color',
				'std' => '#fbfbfb',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'background-color',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Border Color', 'dslc_string' ),
				'id' => 'css_nav_border_color',
				'std' => '#e8e8e8',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'border-color',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Border Width', 'dslc_string' ),
				'id' => 'css_nav_border_width',
				'std' => '1',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'border-width',
				'section' => 'styling',
				'ext' => 'px',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Borders', 'dslc_string' ),
				'id' => 'css_nav_border_trbl',
				'std' => 'top right bottom left',
				'type' => 'checkbox',
				'choices' => array(
					array(
						'label' => __( 'Top', 'dslc_string' ),
						'value' => 'top'
					),
					array(
						'label' => __( 'Right', 'dslc_string' ),
						'value' => 'right'
					),
					array(
						'label' => __( 'Bottom', 'dslc_string' ),
						'value' => 'bottom'
					),
					array(
						'label' => __( 'Left', 'dslc_string' ),
						'value' => 'left'
					),
				),
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'border-style',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Border Radius - Top', 'dslc_string' ),
				'id' => 'css_nav_border_radius_top',
				'std' => '3',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'border-top-left-radius,border-top-right-radius',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Border Radius - Bottom', 'dslc_string' ),
				'id' => 'css_nav_border_radius_bottom',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'border-bottom-left-radius,border-bottom-right-radius',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( ' Color', 'dslc_string' ),
				'id' => 'css_nav_color',
				'std' => '#8d8d8d',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'color',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Font Size', 'dslc_string' ),
				'id' => 'css_nav_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'font-size',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Font Weight', 'dslc_string' ),
				'id' => 'css_nav_font_weight',
				'std' => '400',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'font-weight',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
				'ext' => '',
				'min' => 100,
				'max' => 900,
				'increment' => 100
			),
			array(
				'label' => __( 'Font Family', 'dslc_string' ),
				'id' => 'css_nav_font_family',
				'std' => 'Open Sans',
				'type' => 'font',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'font-family',
				'section' => 'styling',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Padding Vertical', 'dslc_string' ),
				'id' => 'css_nav_padding_vertical',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'styling',
				'ext' => 'px',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Padding Horizontal', 'dslc_string' ),
				'id' => 'css_nav_padding_horizontal',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'styling',
				'ext' => 'px',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			array(
				'label' => __( 'Spacing - Items', 'dslc_string' ),
				'id' => 'css_nav_item_margin_right',
				'std' => '-1',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'margin-left',
				'section' => 'styling',
				'ext' => 'px',
				'tab' => __( 'Navigation', 'dslc_string' ),
				'min' => -10,
				'max' => 100
			),
			array(
				'label' => __( 'Spacing - Nav and Content', 'dslc_string' ),
				'id' => 'css_nav_content_spacing',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'styling',
				'ext' => 'px',
				'tab' => __( 'Navigation', 'dslc_string' ),
			),
			
			/**
			 * Tabs Nav - Active
			 */

			array(
				'label' => __( 'BG Color', 'dslc_string' ),
				'id' => 'css_nav_active_bg_color',
				'std' => '#ffffff',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook.dslc-active',
				'affect_on_change_rule' => 'background-color',
				'section' => 'styling',
				'tab' => __( 'Navigation Active', 'dslc_string' ),
			),
			array(
				'label' => __( 'Border Color', 'dslc_string' ),
				'id' => 'css_nav_border_color_active',
				'std' => '#e8e8e8',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook.dslc-active',
				'affect_on_change_rule' => 'border-left-color,border-right-color,border-top-color',
				'section' => 'styling',
				'tab' => __( 'Navigation Active', 'dslc_string' ),
			),
			array(
				'label' => __( 'Border Bottom Color', 'dslc_string' ),
				'id' => 'css_nav_border_bottom_color_active',
				'std' => '#ffffff',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook.dslc-active',
				'affect_on_change_rule' => 'border-bottom-color',
				'section' => 'styling',
				'tab' => __( 'Navigation Active', 'dslc_string' ),
			),
			array(
				'label' => __( 'Borders', 'dslc_string' ),
				'id' => 'css_nav_border_trbl_active',
				'std' => 'top bottom right left',
				'type' => 'checkbox',
				'choices' => array(
					array(
						'label' => __( 'Top', 'dslc_string' ),
						'value' => 'top'
					),
					array(
						'label' => __( 'Right', 'dslc_string' ),
						'value' => 'right'
					),
					array(
						'label' => __( 'Bottom', 'dslc_string' ),
						'value' => 'bottom'
					),
					array(
						'label' => __( 'Left', 'dslc_string' ),
						'value' => 'left'
					),
				),
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook.dslc-active',
				'affect_on_change_rule' => 'border-style',
				'section' => 'styling',
				'tab' => __( 'Navigation Active', 'dslc_string' ),
			),
			array(
				'label' => __( 'Color', 'dslc_string' ),
				'id' => 'css_nav_active_color',
				'std' => '#8d8d8d',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook.dslc-active',
				'affect_on_change_rule' => 'color',
				'section' => 'styling',
				'tab' => __( 'Navigation Active', 'dslc_string' ),
			),

			/**
			 * Tabs Content
			 */

			array(
				'label' => __( ' BG Color', 'dslc_string' ),
				'id' => 'css_content_bg_color',
				'std' => '#ffffff',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'background-color',
				'section' => 'styling',
			),
			array(
				'label' => __( 'Border Color', 'dslc_string' ),
				'id' => 'css_content_border_color',
				'std' => '#e8e8e8',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'border-color',
				'section' => 'styling',
			),
			array(
				'label' => __( 'Border Width', 'dslc_string' ),
				'id' => 'css_content_border_width',
				'std' => '1',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'border-width',
				'section' => 'styling',
				'ext' => 'px',
			),
			array(
				'label' => __( 'Borders', 'dslc_string' ),
				'id' => 'css_content_border_trbl',
				'std' => 'top right bottom left',
				'type' => 'checkbox',
				'choices' => array(
					array(
						'label' => __( 'Top', 'dslc_string' ),
						'value' => 'top'
					),
					array(
						'label' => __( 'Right', 'dslc_string' ),
						'value' => 'right'
					),
					array(
						'label' => __( 'Bottom', 'dslc_string' ),
						'value' => 'bottom'
					),
					array(
						'label' => __( 'Left', 'dslc_string' ),
						'value' => 'left'
					),
				),
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'border-style',
				'section' => 'styling',
			),
			array(
				'label' => __( 'Border Radius - Top', 'dslc_string' ),
				'id' => 'css_content_border_radius_top',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'border-top-left-radius,border-top-right-radius',
				'section' => 'styling',
				'ext' => 'px'
			),
			array(
				'label' => __( 'Border Radius - Bottom', 'dslc_string' ),
				'id' => 'css_content_border_radius_bottom',
				'std' => '3',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'border-bottom-left-radius,border-bottom-right-radius',
				'section' => 'styling',
				'ext' => 'px'
			),
			array(
				'label' => __( ' Color', 'dslc_string' ),
				'id' => 'css_content_color',
				'std' => '#8d8d8d',
				'type' => 'color',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'color',
				'section' => 'styling',
			),
			array(
				'label' => __( 'Font Size', 'dslc_string' ),
				'id' => 'css_content_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'font-size',
				'section' => 'styling',
				'ext' => 'px'
			),
			array(
				'label' => __( 'Font Weight', 'dslc_string' ),
				'id' => 'css_content_font_weight',
				'std' => '400',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'font-weight',
				'section' => 'styling',
				'ext' => '',
				'min' => 100,
				'max' => 900,
				'increment' => 100
			),
			array(
				'label' => __( 'Font Family', 'dslc_string' ),
				'id' => 'css_content_font_family',
				'std' => 'Open Sans',
				'type' => 'font',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'font-family',
				'section' => 'styling',
			),
			array(
				'label' => __( 'Line Height', 'dslc_string' ),
				'id' => 'css_content_line_height',
				'std' => '23',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'line-height',
				'section' => 'styling',
				'ext' => 'px'
			),
			array(
				'label' => __( 'Margin Bottom', 'dslc_string' ),
				'id' => 'css_margin_bottom',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'styling',
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Vertical', 'dslc_string' ),
				'id' => 'css_content_padding_vertical',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'styling',
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Horizontal', 'dslc_string' ),
				'id' => 'css_content_padding_horizontal',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'styling',
				'ext' => 'px',
			),

			/**
			 * Responsive Tablet
			 */

			array(
				'label' => __( 'Responsive', 'dslc_string' ),
				'id' => 'css_res_t',
				'std' => 'disabled',
				'type' => 'select',
				'choices' => array(
					array(
						'label' => __( 'Disabled', 'dslc_string' ),
						'value' => 'disabled'
					),
					array(
						'label' => __( 'Enabled', 'dslc_string' ),
						'value' => 'enabled'
					),
				),
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
			),
			array(
				'label' => __( 'Font Size', 'dslc_string' ),
				'id' => 'css_res_t_content_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'font-size',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Line Height', 'dslc_string' ),
				'id' => 'css_res_t_content_line_height',
				'std' => '23',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'line-height',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Margin Bottom', 'dslc_string' ),
				'id' => 'css_res_t_margin_bottom',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Vertical', 'dslc_string' ),
				'id' => 'css_res_t_content_padding_vertical',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Horizontal', 'dslc_string' ),
				'id' => 'css_res_t_content_padding_horizontal',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Font Size', 'dslc_string' ),
				'id' => 'css_res_t_nav_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'font-size',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Nav - Padding Vertical', 'dslc_string' ),
				'id' => 'css_res_t_nav_padding_vertical',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Padding Horizontal', 'dslc_string' ),
				'id' => 'css_res_t_nav_padding_horizontal',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Spacing', 'dslc_string' ),
				'id' => 'css_res_t_nav_item_margin_right',
				'std' => '-1',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'margin-left',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
				'min' => -10,
				'max' => 100
			),
			array(
				'label' => __( 'Spacing - Nav and Content', 'dslc_string' ),
				'id' => 'css_res_t_nav_content_spacing',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'responsive',
				'tab' => __( 'tablet', 'dslc_string' ),
				'ext' => 'px',
			),

			/**
			 * Responsive Phone
			 */

			array(
				'label' => __( 'Responsive', 'dslc_string' ),
				'id' => 'css_res_p',
				'std' => 'disabled',
				'type' => 'select',
				'choices' => array(
					array(
						'label' => __( 'Disabled', 'dslc_string' ),
						'value' => 'disabled'
					),
					array(
						'label' => __( 'Enabled', 'dslc_string' ),
						'value' => 'enabled'
					),
				),
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
			),
			array(
				'label' => __( 'Font Size', 'dslc_string' ),
				'id' => 'css_res_p_content_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'font-size',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Line Height', 'dslc_string' ),
				'id' => 'css_res_p_content_line_height',
				'std' => '23',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'line-height',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Margin Bottom', 'dslc_string' ),
				'id' => 'css_res_p_margin_bottom',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-content',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Vertical', 'dslc_string' ),
				'id' => 'css_res_p_content_padding_vertical',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Padding Horizontal', 'dslc_string' ),
				'id' => 'css_res_p_content_padding_horizontal',
				'std' => '35',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-tab-content',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Font Size', 'dslc_string' ),
				'id' => 'css_res_p_nav_font_size',
				'std' => '13',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'font-size',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px'
			),
			array(
				'label' => __( 'Nav - Padding Vertical', 'dslc_string' ),
				'id' => 'css_res_p_nav_padding_vertical',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-top,padding-bottom',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Padding Horizontal', 'dslc_string' ),
				'id' => 'css_res_p_nav_padding_horizontal',
				'std' => '10',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'padding-left,padding-right',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),
			array(
				'label' => __( 'Nav - Spacing', 'dslc_string' ),
				'id' => 'css_res_p_nav_item_margin_right',
				'std' => '-1',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav-hook',
				'affect_on_change_rule' => 'margin-left',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
				'min' => -10,
				'max' => 100
			),
			array(
				'label' => __( 'Spacing - Nav and Content', 'dslc_string' ),
				'id' => 'css_res_p_nav_content_spacing',
				'std' => '0',
				'type' => 'slider',
				'refresh_on_change' => false,
				'affect_on_change_el' => '.dslc-tabs-nav',
				'affect_on_change_rule' => 'margin-bottom',
				'section' => 'responsive',
				'tab' => __( 'phone', 'dslc_string' ),
				'ext' => 'px',
			),


		);

		$dslc_options = array_merge( $dslc_options, $this->shared_options('animation_options') );
		$dslc_options = array_merge( $dslc_options, $this->presets_options() );

		return apply_filters( 'dslc_module_options', $dslc_options, $this->module_id );

	}

	function output( $options ) {

		global $dslc_active;

		if ( $dslc_active && is_user_logged_in() && current_user_can( DS_LIVE_COMPOSER_CAPABILITY ) )
			$dslc_is_admin = true;
		else
			$dslc_is_admin = false;		

		$this->module_start( $options );

		/* Module output stars here */ 

			$tabs_nav = explode( '(dslc_sep)', trim( $options['tabs_nav'] ) );
			$tabs_content = explode( '(dslc_sep)', trim( $options['tabs_content'] ) );

		?>

			<div class="dslc-tabs">

				<div class="dslc-tabs-nav dslc-clearfix">
					
					<?php if ( is_array( $tabs_nav ) && count( $tabs_nav ) > 1 ) : ?>

						<?php foreach ( $tabs_nav as $tab_nav ) : ?>
							<span class="dslc-tabs-nav-hook">
								<span class="dslc-tabs-nav-hook-title" <?php if ( $dslc_is_admin ) echo 'contenteditable'; ?>><?php echo stripslashes( $tab_nav ); ?></span>
								<?php if ( $dslc_is_admin ) : ?>
									<span class="dslca-delete-tab-hook"><span class="dslca-icon dslc-icon-remove"></span></span>
								<?php endif; ?>
							</span>
						<?php endforeach; ?>

					<?php else : ?>
						<span class="dslc-tabs-nav-hook">
							<span class="dslc-tabs-nav-hook-title" <?php if ( $dslc_is_admin ) echo 'contenteditable'; ?>><?php _e( 'Click to edit', 'dslc_string' ); ?></span>
							<?php if ( $dslc_is_admin ) : ?>
								<span class="dslca-delete-tab-hook"><span class="dslca-icon dslc-icon-remove"></span></span>
							<?php endif; ?>
						</span>

					<?php endif; ?>

					<?php if ( $dslc_is_admin ) : ?>

						<span class="dslca-add-new-tab-hook">
							<span class="dslca-icon dslc-icon-plus"></span>
						</span>

					<?php endif; ?>

				</div><!-- .dslc-tabs-nav -->

				<div class="dslc-tabs-content">

					<?php if ( is_array( $tabs_content ) && count( $tabs_content ) > 1 ) : $count = 0; ?>

						<?php foreach( $tabs_content as $tab_content ) : ?>

							<div class="dslc-tabs-tab-content">
								<h4 class="dslc-tabs-nav-hook"><?php echo $tabs_nav[$count]; ?></h4>
								<div class="dslca-editable-content">
									<?php 
										$tab_content_output = stripslashes( $tab_content ); 
										$tab_content_output = str_replace( '<lctextarea', '<textarea', $tab_content_output );
										$tab_content_output = str_replace( '</lctextarea', '</textarea', $tab_content_output );
										echo do_shortcode( $tab_content_output );
									?>
								</div>
								<?php if ( $dslc_is_admin ) : ?>
									<div class="dslca-wysiwyg-actions-edit"><span class="dslca-wysiwyg-actions-edit-hook"><?php _e( 'Edit Content', 'dslc_string' ); ?></span></div>
								<?php endif; ?>
							</div><!-- .dslc-tabs-tab-content -->

						<?php $count++; endforeach; ?>

					<?php else : ?>

						<div class="dslc-tabs-tab-content">
							<h4 class="dslc-tabs-nav-hook">CLICK TO EDIT</h4>
							<div class="dslca-editable-content">
								<?php _e( 'This is just placeholder text.', 'dslc_string' ); ?>
							</div>
							<?php if ( $dslc_is_admin ) : ?>
								<div class="dslca-wysiwyg-actions-edit"><span class="dslca-wysiwyg-actions-edit-hook"><?php _e( 'Edit Content', 'dslc_string' ); ?></span></div>
							<?php endif; ?>
						</div><!-- .dslc-tabs-tab-content -->

					<?php endif; ?>

				</div><!-- .dslc-tabs-content -->

			</div><!-- .dslc-tabs -->

		<?php /* Module output ends here */

		$this->module_end( $options );

	}

}