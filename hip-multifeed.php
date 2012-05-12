<?php
/*
Plugin Name: Hippies Multi-Feed
Description: A plugin for displaying feeds from multiple sources
Author: Christian Bolstad
Author URI: http://www.hippies.se/
Plugin URI: http://www.hippies.se/plugins/hip-multifeed/
Version: 1.0
*/

/*
-=[ Copyright Notice ]=-

	This plugin is based on KNR Multifeed by Nitin Reddy, http://www.nitinkatkam.com

	Copyright 2011 Christian Bolstad, christian@carnaby.se 
    Copyright 2009 Nitin Reddy  (email : k_nitin_r {at} antispamyahoo.co.in , k.nitin.r {at} antispamgmail.com)
                                    Replace the {at} with @ and remove the antispam for my email address
                                    

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
	
	WARRANTY AND CUSTOMIZATION
	Warranty and customization for this software is available. Contact the
	author for more details.
*/

include_once(dirname(__FILE__).'/'.'hip_feedreader.php');

function hip_multifeed_process($urllines, $itemlimit, $selecttype, $display_output = true) {
	$itemArray = array();
	
	foreach(split("\n", $urllines) as $iterUrl) {
		$iterFr = new FeedReader(trim($iterUrl));
		$iterFr->fetchItems();
		$itemArray = array_merge($itemArray,
			//array_slice(
				$iterFr->getItems()
			//,0,$itemlimit)
		);
	}

	$sorter = new NewsItemSorter($itemArray);
	if ($selecttype == 'Random')
		$sorter->Shuffle($itemArray);
	elseif ($selecttype == 'Chronological')
		$sorter->SortByDate($itemArray);
	//shuffle($itemArray);
	$itemArray = array_slice($itemArray, 0, $itemlimit);
	
	if ($display_output)
		FeedReader::renderAsList($itemArray);
	else
		return $itemArray;
}

class KnrMultiFeeds extends WP_Widget {
	static function heredoc($arg) { return $arg; }
	static $heredoc = 'heredoc';

	public function KnrMultiFeeds() {
		parent::WP_Widget(false, 'KNR Multi-Feed');
	}
	
	public function widget($args, $instance) {
		extract($args);
		echo $before_widget;
		
		$title = apply_filters('widget_title', $instance['title']);
		if ($title) $title = (trim($title) == '') ? null : $title;
		if ($title) echo $before_title.$title.$after_title;
		
		$urllines = $instance['urllines'];
		$itemlimit = $instance['itemlimit'];
		$selecttype = $instance['selecttype'];
		
		if (isset($urllines) && strlen($urllines)>0) {
			$itemArray = array();
			
			hip_multifeed_process($urllines, $itemlimit, $selecttype);
			/*
			foreach(split("\n", $urllines) as $iterUrl) {
				$iterFr = new FeedReader(trim($iterUrl));
				$iterFr->fetchItems();
				$itemArray = array_merge($itemArray,
					//array_slice(
						$iterFr->getItems()
					//,0,$itemlimit)
				);
			}
			
			$sorter = new NewsItemSorter($itemArray);
			if ($selecttype == 'Random')
				$sorter->Shuffle($itemArray);
			elseif ($selecttype == 'Chronological')
				$sorter->SortByDate($itemArray);
			//shuffle($itemArray);
			$itemArray = array_slice($itemArray, 0, $itemlimit);
			FeedReader::renderAsList($itemArray);
			*/
		}
		
		/*
		$fr = new FeedReader();
		$fr->fetchItems();
		$fr->shuffleItems();
		$fr->truncateItemArray(5);
		$fr->renderItems();
		*/
		
		echo $after_widget;
	}
	public function update($new_instance, $old_instance) {
		$instance = $old_instance;
		
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['urllines'] = strip_tags($new_instance['urllines']);
		$instance['itemlimit'] = strip_tags($new_instance['itemlimit']);
		$instance['selecttype'] = strip_tags($new_instance['selecttype']);
		
		return $instance;
	}
		
	public function form($instance) {
		if (isset($instance) && isset($instance['title'])) $title = esc_attr($instance['title']);
		$title = '';
		$title_fieldId = $this->get_field_id('title');
		$title_fieldName = $this->get_field_name('title');

		if (isset($instance) && isset($instance['urllines'])) $urllines = esc_attr($instance['urllines']);
		$urllines = '';
		$urllines_fieldId = $this->get_field_id('urllines');
		$urllines_fieldName = $this->get_field_name('urllines');

		if (isset($instance) && isset($instance['itemlimit'])) $itemlimit = esc_attr($instance['itemlimit']);
		$itemlimit = 10;
		$itemlimit_fieldId = $this->get_field_id('itemlimit');
		$itemlimit_fieldName = $this->get_field_name('itemlimit');
		
		if (isset($instance) && isset($instance['selecttype'])) $selecttype = esc_attr($instance['selecttype']);
		$selecttype = null;
		$selecttype_fieldId = $this->get_field_id('selecttype');
		$selecttype_fieldName = $this->get_field_name('selecttype');
		
		$selectedStringSelectionTypeRandom = $selecttype == 'Random' ? ' selected=\"selected\"' : '';
		$selectedStringSelectionTypeChronological = $selecttype == 'Chronological' ? ' selected=\"selected\"' : '';
		
		echo "
<p>
	<label>Title</label>
	<input type=\"text\" name=\"${title_fieldName}\" id=\"${title_fieldId}\" value=\"${title}\" />
</p>
<p>
	<label>URLs (1 per line)</label>
	<textarea name=\"${urllines_fieldName}\" id=\"${urllines_fieldId}\">${urllines}</textarea>
</p>
<p>
	<label>No. of Items To Display</label>
	<input type=\"text\" name=\"${itemlimit_fieldName}\" id=\"${itemlimit_fieldId}\" value=\"${itemlimit}\" />
</p>
<p>
	<label>Item Selection Type</label>
	<select name=\"${selecttype_fieldName}\" id=\"${selecttype_fieldId}\">
		<option value=\"Random\"$selectedStringSelectionTypeRandom>Random</option>
		<option value=\"Chronological\"$selectedStringSelectionTypeChronological>Chronological</option>
	</select>
</p>
";
	}
}

add_action('widgets_init', create_function('', 'return register_widget(\'KnrMultiFeeds\');'));

class KnrMultiFeedShortcode {
	function main($atts, $content=null) {
		if (null == $content) return;
		$content = trim(strip_tags($content));
		if ('' == $content) return;
		
		$params = shortcode_atts( array(
		'itemlimit' => 20,
		'selecttype' => 'Chronological'
		), $atts );
	
		$items = hip_multifeed_process($content, $params['itemlimit'], $params['selecttype'], false);
		
		$markup = '';
		$markup .= '<ul>'."\n";
		foreach($items as $iterItem) {
			$markup .= '	<li>'."\n";
			
			$markup .= '		<a href="'.$iterItem->hyperlink.'">'."\n";	//Open A HREF tag
			$markup .= '		'.TextUtility::fix_smartchar($iterItem->title)."\n";	//Title
			$markup .= '		</a>'."\n";	//Close A HREF tag
			
			$markup .= '	</li>'."\n";
		}
		$markup .= '</ul>'."\n";
		
		return $markup;
	}
}

add_shortcode('hipmultifeed', array('KnrMultiFeedShortcode', 'main'));