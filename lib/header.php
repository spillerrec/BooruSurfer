<?php
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
