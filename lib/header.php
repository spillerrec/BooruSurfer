<?php
	include_once "html.php";
	
	
	class mainLayout{
		public $page;
		public $navigation;
		public $sidebar;
		public $main;
		
		
		function __construct( $site_name, $code = NULL ){
			$this->page = new htmlPage();
			$this->page->html->addStylesheet( "/style/main.css" );
			
			$nav = new htmlObject( "nav" );
			
			$nav->content[] = new htmlList();
			$this->navigation =& $nav->content[0];
			$this->navigation->addItem( $site_name );
			if( $code )
				$this->navigation->addItem( new htmlLink( "/$code/index/", "Posts" ) );
			
			//TODO: add search bar
			
			
			$header = new htmlObject( "header" );
			$header->content[] = $nav;
			$this->page->html->body->content[] = $header;
			
			
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
	
	function thumbnail( $post, $link, $alt = NULL ){ //Add some settings
		return new htmlLink( $link, $post->get_image( 'thumb' )->to_html( $alt ) );
	}
?>
