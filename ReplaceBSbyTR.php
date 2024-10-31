<?php
/*
Plugin Name: ReplaceBSbyTR
Plugin URI: http://wordpress.org/extend/plugins/replacebsbytr/
Description: Replace BlogSearch results by Technorati on the Dashboard
Version: 1.0.2
Author: burningHat
Author URI: http://blog.burninghat.net

Copyright 2007  Emmanuel Ostertag alias burningHat (email : webmaster _at_ burninghat.net)

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

require_once (ABSPATH . WPINC . '/rss.php');

function ReplaceBlogSearch_Admin_Footer(){
	$rss_feed = apply_filters('dashboard_incoming_links_feed', 'http://feeds.technorati.com/cosmos/rss/?url='. trailingslashit(get_option('home')) .'&partner=wordpress');
	$more_link = apply_filters('dashboard_incoming_links_link', 'http://www.technorati.com/search/'. trailingslashit(get_option('home')) .'?partner=wordpress');
	
	$rss = @fetch_rss($rss_feed);
	
	// détermine le chemin d'installation
	$path = basename(str_replace('\\','/',dirname(__FILE__)));
	$install_url = get_option('siteurl') . '/wp-content/plugins';
	if ( $path != 'plugins' ) {
			$install_url .= '/' . $path . '/';
	}
	
	if ( isset($rss->items) && 1 < count($rss->items) ){
		$content = "<h3><img src=\"" . $install_url . "technorati.gif\" alt=\"logo Technorati\" /> ".__('Incoming Links')." <cite><a href=\"". htmlspecialchars($more_link) ."\">". __('More &raquo;') ."</a></cite></h3>";
		$content .= "<ul>";
		
		$rss->items = array_slice($rss->items, 0, 10);
		foreach ( $rss->items as $item ){
			$content .= "<li><a href=\"". wp_filter_kses($item['link']) ."\">". wptexturize(wp_specialchars($item['title'])) ."</a></li>";
		}
		
		$content .= "</ul>";
	}
	return $content;
}

$dashboard = get_option("dashboard");

if ( function_exists('add_action') ){
	$admin = dirname($_SERVER['SCRIPT_FILENAME']);
	$admin = substr($admin, strrpos($admin, '/')+1);
	
	if ($admin == 'wp-admin' && basename($_SERVER['SCRIPT_FILENAME']) == 'index.php') {		
		function test(){
			$test = "<h1>Message de test</h1>\n";
			$test .= "<p>coucou</p>\n";
			echo $test;
		}
		
		add_action('admin_head', 'RBS_start');
		
		function RBS_wipe($buffer){
			if ( $buffer = str_replace("jQuery('#incominglinks').load('index-extra.php?jax=incominglinks');", "jQuery('#incominglinks').html('".ReplaceBlogSearch_Admin_Footer()."');", $buffer) ){
				
			}
			echo $buffer;
		}
		
		function RBS_start($buffer){
			ob_start();
			add_action('admin_footer', 'RBS_end');
		}
		
		function RBS_end($buffer){
			$buffer = ob_get_contents();
			ob_end_clean();
			RBS_wipe($buffer);
		}
	}
}

?>
