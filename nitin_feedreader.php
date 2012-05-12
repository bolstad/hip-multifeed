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

define('FEEDURL', 'http://feeds.feedburner.com/KrishnaConnect?format=xml');

//The PHP_OS constant is the platform the sourcecode was built on... php_uname is the currently running OS
if (strtolower(substr(php_uname('s'), 0, 7)) == 'windows') {
	define('FILESYS_SLASH', '\\');
	define('EOL_CHR', "\r\n");
} else {
	define('FILESYS_SLASH', '/');
	define('EOL_CHR', "\n");
}
	
	
class NewsItem {
	var $title;
	var $publishdate;
	var $hyperlink;
	var $description;
}

class NewsItemSorter {
	var $itemArr;
	function NewsItemSorter($aArr) {
		$this->itemArr = $aArr;
	}
	function CompareIt($a,$b) {
		return strtotime ($a->publishdate) == strtotime ($b->publishdate) ? 0 : (strtotime ($a->publishdate) > strtotime ($b->publishdate) ? 1 : -1);
	}
	function SortByDate() {
		usort($this->itemArr, array('NewsItemSorter', 'CompareIt'));
		return $this->itemArr;
	}
	function Shuffle() {
		shuffle($this->itemArr);
	}
}

class TextUtility {
	public static function fix_smartchar($atxt) {
		$atxt = str_replace('’', '\'', $atxt); //smart closing quote (apostrophe)
		$atxt = str_replace('–', '-', $atxt); //smart hyphen?
		return $atxt;
	}
}

class FeedReader {
	private $domdoc;
	private $itemarray;
	public $feedurl;
	
	public function FeedReader($afeedurl = FEEDURL) {
		$this->feedurl = $afeedurl;
	}

	private function getXml() {	
		$file_w_path = sys_get_temp_dir() .FILESYS_SLASH.sha1($this->feedurl); 
		
		//echo $file_w_path;
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
		//echo '<p>Intxt Strlen: '.strlen($txt).'</p>';
		$this->domdoc->loadXML($txt);
		//$this->domdoc->load($this->feedurl);
	}
	
	private function parseDom() {
		$this->loadDom();
		
		$this->itemarray = array();

		if ($this->domdoc->firstChild->nodeName == 'rss') {
			foreach($this->domdoc->getElementsByTagName('item') as $iterFeedItem) {
				$iterNewsItem = new NewsItem();
				$iterNewsItem->title = $iterFeedItem->getElementsByTagName('title')->item(0)->nodeValue;
				$iterNewsItem->publishdate = $iterFeedItem->getElementsByTagName('pubDate')->item(0)->nodeValue;
				$iterNewsItem->hyperlink = $iterFeedItem->getElementsByTagName('link')->item(0)->nodeValue;
				$iterNewsItem->description = $iterFeedItem->getElementsByTagName('description')->item(0)->nodeValue;
				$this->itemarray[] = $iterNewsItem;
			}
		} else if ($this->domdoc->firstChild->nodeName == 'feed') {
			foreach($this->domdoc->getElementsByTagName('entry') as $iterFeedItem) {
				$iterNewsItem = new NewsItem();
				$iterNewsItem->title = $iterFeedItem->getElementsByTagName('title')->item(0)->nodeValue;
				$iterNewsItem->publishdate = $iterFeedItem->getElementsByTagName('updated')->item(0)->nodeValue;
				$iterNewsItem->hyperlink = $iterFeedItem->getElementsByTagName('link')->item(0)->nodeValue;
				$iterNewsItem->description = $iterFeedItem->getElementsByTagName('summary')->item(0)->nodeValue;
				$this->itemarray[] = $iterNewsItem;
			}
		} else {
			//no items
		}
		
		//echo '<p>Item count: '.count($this->itemarray).'</p>';
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
			
		//echo '<p>Found items: '.count($this->itemarray).'</p>';
		
		self::renderAsList($this->itemarray);
	}
	
	public static function renderAsList($aItems) {
		echo '<ul>'.EOL_CHR;
		foreach($aItems as $iterItem) {
			echo '	<li>'.EOL_CHR;
			
			echo '		<a href="'.$iterItem->hyperlink.'">'.EOL_CHR;	//Open A HREF tag
			echo '		'.TextUtility::fix_smartchar($iterItem->title).EOL_CHR;	//Title
			echo '		</a>'.EOL_CHR;	//Close A HREF tag
			
			echo '	</li>'.EOL_CHR;
		}
		echo '</ul>'.EOL_CHR;
	}
}

