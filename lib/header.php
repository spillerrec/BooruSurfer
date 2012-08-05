<?php
	/*	This file is part of BooruSurfer.

		BooruSurfer is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.

		BooruSurfer is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.

		You should have received a copy of the GNU General Public License
		along with BooruSurfer.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	include_once "html.php";
	
	
	class mainLayout{
		public $page;
		public $navigation;
		public $sidebar;
		public $main;
		
		
		function __construct(){
			$this->page = new htmlPage();
			$this->page->html->head->content[] = new fakeObject( ("<!--[if lt IE 9]><script src=\"/style/ie8.js\"></script><![endif]-->") );
			$this->page->html->addStylesheet( "/style/main.css" );
			
			//Create header
			$nav = new htmlObject( "nav" );
			$header = new htmlObject( "header" );
			$header->content[] = $nav;
			$this->page->html->body->content[] = $header;
			
			$this->navigation =& $nav->content;
			
			
			//Add content areas
			$content = new htmlObject( "div" );
			$content->attributes['id'] = "container";
			$this->sidebar = new htmlObject( "aside" );
			$this->main = new htmlObject( "section" );
			$content->content[] =& $this->main;
			$content->content[] =& $this->sidebar;
			$this->page->html->body->content[] = $content;
			
		}
	}
?>
