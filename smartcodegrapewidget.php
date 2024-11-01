<?php 
	/*
	Plugin Name: Smart CodeGrape Widget
	Plugin URI: http://www.flashbluedesign.com
	Description: A simple and powerful WordPress plugin with which you can display CodeGrape items as a WordPress widget. Several smart options are provided for selecting and ordering. You can select CodeGrape latest items or items from one or more specific users. Optionally, you can connect items with your affiliate links as well.
	Author: flashblue
	Version: 1.7.0
	Author URI: https://www.codegrape.com/user/flashblue
	*/	
	
	/*  
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	*/
	
	define('SCW_PLUGIN_DIR', trailingslashit(plugin_dir_path(__FILE__)));
	define('SCW_PLUGIN_URI', trailingslashit(plugin_dir_url(__FILE__)));
	define ('SM_CG_WIDGET_VER', '1.7.0');
	
	/* Initialize Widget */
	if(!function_exists('scw_widget_init')):
		function scw_widget_init() {
			require_once(SCW_PLUGIN_DIR.'includes/widget.class.php');
			register_widget('SM_CodeGrape_Widget');
		}
	endif;
	
	add_action('widgets_init','scw_widget_init');
	
	/* Load text domain */
	function sm_load_cg_widget_text_domain() {
		load_plugin_textdomain('smart', false, dirname(plugin_basename(__FILE__)).'/languages/');
	}
	
	add_action('plugins_loaded', 'sm_load_cg_widget_text_domain');
?>