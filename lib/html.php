<?php
	class htmlPage{
		private $html_type;
		
		public $html;
		
		function __construct(){
			$this->html_type = "xhtml";
			$this->html = new htmlBody();
		}
		
		function write(){
			//TODO: html5
			$this->html->writeXhtml();
		}
	}
	
	class htmlBody extends htmlObject{
		public $head;
		public $title;
		
		public $body;
		
		function __construct(){
			parent::__construct( "html" );
			$this->attributes['lang'] = 'en';
			$this->attributes['dir'] = 'ltr';
			//$this->attributes['xmlns'] = 'http://www.w3.org/1999/xhtml';
			$this->content[] = new htmlObject( "head" );
			$this->content[] = new htmlObject( "body" );
			$this->head =& $this->content[0];
			$this->body =& $this->content[1];
			
			$this->head->content[] = new htmlObject( "title" );
			$this->title =& $this->head->content[0]->content;
		}
		
		function writeXhtml(){
			//header('Content-type: application/xhtml+xml; charset=utf-8');
			echo "<!DOCTYPE html>\r\n";
			parent::writeXhtml();
		}
		
		function addStylesheet( $href, $media=NULL ){
			$link = new htmlObject( "link" );
			$link->attributes['href'] = $href;
			$link->attributes['rel'] = "stylesheet";
			$link->attributes['media'] = $media;
			
			$this->head->content[] = $link;
		}
		
		function addSequence( $type, $url ){
			$link = new htmlObject( "link" );
			$link->attributes['rel'] = $type;
			$link->attributes['href'] = $url;
			
			$this->head->content[] = $link;
		}
	}
	
	class htmlObject{
		private $type;
		public $attributes;
		public $content;
		
		
		function __construct( $type, $content=NULL, $attributes=NULL ){
			$this->type = $type;
			$this->attributes = $attributes;
			$this->content = $content;
		}
		
		private function escape( $text, $attribute=false ){
			$text = str_replace( "&", "&amp;", $text );
			$text = str_replace( "<", "&lt;", $text );
			$text = str_replace( ">", "&gt;", $text );
			if( $attribute ){
				$text = str_replace( '"', "&quot;", $text );
				$text = str_replace( "'", "&apos;", $text );
			}
			return $text;
		}
		
		public function writeHtml(){
		
		}
		public function writeXhtml(){
			if( $this->type ){
				//Write type and attributes
				echo "<$this->type";
				if( $this->attributes ){
					foreach( $this->attributes as $attribute=>$value )
						if( $value ){
							$value = $this->escape( $value, true );
							echo " $attribute=\"$value\"";
						}
				}
				
				//Write body of element
				if( $this->content ){
					echo ">";
					
					if( is_object( $this->content ) )
						$this->content = array( $this->content );
					
					if( is_array( $this->content ) )
						foreach( $this->content as $child )
							$child->writeXhtml();
					else
						echo $this->escape( $this->content );
					
					echo "</$this->type>";
				}
				else
					echo "/>";
			}
		}
		
		function addClass( $class ){
			if( isset( $this->attributes['class'] ) )
				$this->attributes['class'] .= " $class";
			else
				$this->attributes['class'] = $class;
		}
		function setID( $id ){
			$this->attributes['id'] = $id;
		}
	}
	
	class htmlLink extends htmlObject{
		public $href;
		
		function __construct( $link, $title ){
			parent::__construct( "a" );
			$this->attributes['href'] = $link;
			$this->href =& $this->attributes['href'];
			
			$this->content = $title;
		}
	}
	
	class htmlImage extends htmlObject{
		public $src;
		public $alt;
		
		function __construct( $link, $alt = NULL ){
			parent::__construct( "img" );
			$this->attributes['src'] = $link;
			$this->attributes['alt'] = $alt;
			$this->src =& $this->attributes['src'];
			$this->alt =& $this->attributes['alt'];
		}
	}
	
	class htmlList extends htmlObject{
		function __construct( $ordered=false ){
			if( $ordered )
				parent::__construct( "ol" );
			else
				parent::__construct( "ul" );
		}
		
		function addItem( $item ){
			return $this->content[] = new htmlObject( "li", $item );
		}
	}
	
	function toClass( $title ){
		return array( 'class'=>$title );
	}
	function toId( $id ){
		return array( 'id'=>$id );
	}
?>