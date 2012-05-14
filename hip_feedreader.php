<?php
/*
-=[ Copyright Notice ]=-
	
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

define('FEEDURL', 'http://wordpress.org/news/feed/');

	
class HipNewsItem {
	var $title;
	var $publishdate;
	var $hyperlink;
	var $description;
}

class HipNewsItemSorter {
	var $itemArr;
	function HipNewsItemSorter($aArr) {
		$this->itemArr = $aArr;
	}
	function CompareIt($a,$b) {
		return strtotime ($a->publishdate) == strtotime ($b->publishdate) ? 0 : (strtotime ($a->publishdate) > strtotime ($b->publishdate) ? 1 : -1);
	}
	function SortByDate() {
		usort($this->itemArr, array('HipNewsItemSorter', 'CompareIt'));
		return $this->itemArr;
	}
	function Shuffle() {
		shuffle($this->itemArr);
	}
}

class HipTextUtility {
	public static function fix_smartchar($atxt) {
		$atxt = str_replace('’', '\'', $atxt); //smart closing quote (apostrophe)
		$atxt = str_replace('–', '-', $atxt); //smart hyphen?
		return $atxt;
	}
}

class HipFeedReader {
	private $domdoc;
	private $itemarray;
	public $feedurl;
	
	public function HipFeedReader($afeedurl = FEEDURL) {
		$this->feedurl = $afeedurl;
	}

	private function getXml() {	
		$file_w_path = sys_get_temp_dir() .'/'.sha1($this->feedurl); 
		
		if (file_exists($file_w_path)) {
			if (time() - filemtime($file_w_path) < 60*30) { //dont fetch again for 30 mins
				$outtxt = file_get_contents($file_w_path);
				if ($outtxt && trim(strlen($outtxt))>0) return $outtxt;
			}
		}
		
		$outtxt = wp_remote_retrieve_body( wp_remote_get($this->feedurl) );
		
		$fd = fopen($file_w_path, 'w');
		fwrite($fd, $outtxt);
		fflush($fd); //.NET requires a flush before a close to prevent data loss; is PHP the same?
		fclose($fd);
						
		return $outtxt;
	}

	private function loadDom() {
		$this->domdoc = new DOMDocument();
		$txt = $this->getXml();
		if (!empty($txt)) $this->domdoc->loadXML($txt);
	}
	
	private function parseDom() {
		$this->loadDom();
		
		$this->itemarray = array();

		if ($this->domdoc->firstChild->nodeName == 'rss') {
			foreach($this->domdoc->getElementsByTagName('item') as $iterFeedItem) {
				$iterNewsItem = new HipNewsItem();
				$iterNewsItem->title = $iterFeedItem->getElementsByTagName('title')->item(0)->nodeValue;
				$iterNewsItem->publishdate = $iterFeedItem->getElementsByTagName('pubDate')->item(0)->nodeValue;
				$iterNewsItem->hyperlink = $iterFeedItem->getElementsByTagName('link')->item(0)->nodeValue;
				$iterNewsItem->description = $iterFeedItem->getElementsByTagName('description')->item(0)->nodeValue;
				$this->itemarray[] = $iterNewsItem;
			}
		} else if ($this->domdoc->firstChild->nodeName == 'feed') {
			foreach($this->domdoc->getElementsByTagName('entry') as $iterFeedItem) {
				$iterNewsItem = new HipNewsItem();
				$iterNewsItem->title = $iterFeedItem->getElementsByTagName('title')->item(0)->nodeValue;
				$iterNewsItem->publishdate = $iterFeedItem->getElementsByTagName('updated')->item(0)->nodeValue;
				$iterNewsItem->hyperlink = $iterFeedItem->getElementsByTagName('link')->item(0)->nodeValue;
				$iterNewsItem->description = $iterFeedItem->getElementsByTagName('summary')->item(0)->nodeValue;
				$this->itemarray[] = $iterNewsItem;
			}
		} else {
			//no items
		}
		
	}
	
	public function fetchItems() {
		$this->parseDom();
	}
	
	public function shuffleItems() {
		shuffle($this->itemarray);
	}
	
	public function truncateItemArray($aItemCount) {
		if (count($this->itemarray) > $aItemCount)
			$this->itemarray = array_slice($this->itemarray, 0, $aItemCount);
	}
	
	public function getItems() {
		return $this->itemarray;
	}
	
	public function renderItems() {
		if (count($this->itemarray)==0)
			$this->parseDom();
		self::renderAsList($this->itemarray);
	}
	
	public static function renderAsList($aItems,$stringtemplate ='',$datestring = 'Y-m-d') {
		echo '<ul>';
		foreach($aItems as $iterItem) 
		{	
			
			if ($stringtemplate != '')
				{				
					if (($timestamp = strtotime($iterItem->publishdate)) === false) {	
					} 
					else {		
					     $datestamp = date($datestring, $timestamp);
					}
					$output = $stringtemplate;
					$output = str_replace("%url%",$iterItem->hyperlink,$output);
					$output = str_replace("%title%",$iterItem->title,$output);					
					$output = str_replace("%date%",$datestamp,$output);										
					echo "<li>$output</li>";					
				}
				else
				echo '	<li><a href="'.$iterItem->hyperlink.'">'. HipTextUtility::fix_smartchar($iterItem->title) .'</a></li>';
				
		}
		echo '</ul>';
	}
}

