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
	
	require_once "lib/html.php";
	require_once "lib/Booru.php";
	require_once "lib/SiteInfo.php";
	
	/* Styler creates the HTML markup for all basic objects.
	 * In time you should be able to override this class
	 * and be able to modify the returned markup in order
	 * to custimize the look and feel of the pages.
	 */
	class Styler{
	//Stuff needed for the class to function properly
		
		private $site;
		private $code;
		public function __construct( $site ){
			$this->site = $site;
			if( $this->site )
				$this->code = $this->site->get_api()->get_code();
		}
		
		
	//All general stuff, like string formating
		
		//Formats the size of a file
		public function format_filesize( $bytes ){
			$endings = array( 'bytes', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB' );
			
			//Keep making it smaller, until a unit has been found
			foreach( $endings as $end ){
				if( $bytes >= 1024 )
					$bytes /= 1024;
				else{
					//$bytes is below 1024, select amount of decimals
					if( $bytes >= 100 )
						$decimals = 0;
					else if( $bytes >= 10 )
						$decimals = 1;
					else
						$decimals = 2;
					
					//Now format and return result
					return sprintf( "%.$decimals".'f', $bytes ) . " $end";
				}
			}
			
			//Oh god...
			return 'will crash your computer';
		}
		
		//Format a date
		public function format_date( $unix_time ){
			date_default_timezone_set( 'Europe/Copenhagen' );
			return date( 'H:i d/m/Y', $unix_time );
		}
		
		
	//Page elements like headers
		
		//Creates a form to input a search
		public function tag_search(){
			//Create tag input
			$input = new htmlObject( 'input' );
			$input->attributes['id'] = 'search';
			$input->attributes['type'] = 'search';
			$input->attributes['name'] = 'tags';
			$input->attributes['placeholder'] = 'Tag query';
			
			$fields = new htmlObject( 'fieldset', $input );
			
			//Create form
			$form = new htmlObject( 'form', $fields );
			$form->attributes['action'] = '/' . $this->code . '/';
			$form->attributes['method'] = 'GET';
			
			return $form;
		}
		
		//The navigation bar
		public function main_navigation( $search=NULL ){
			//Init
			$sites = SiteInfo::sites();
			$links = new htmlList();
			//Create a dropdown menu with all sites
			$sub = new htmlList();
			foreach( $sites as $key=>$sub_site ){
				$sub->addItem(
						new htmlLink( $this->site->site_index_link( $key, 1, $search ), $sub_site )
					);
			}
			
			//Add this under the current site name
			$links->addItem( array( new fakeObject( $this->site->get_api()->get_name() ), $sub ) );
			
			
			//Add other pages
			$links->addItem( new htmlLink( $this->site->index_link(), 'Index' ) );
			$links->addItem( new htmlLink( '/manage/', 'Settings' ) );
			
			//Add search
			$links->addItem( $this->tag_search() );
			
			return $links;
		}
		
		
	//Formating of DataTables like DTPost and DTTag
		
		//Returns a link to a tag and possibly other info
		public function tag( $tag ){
			$url = $this->site->index_link( 1, $tag->name() );
			
			$title = str_replace( "_", " ", $tag->name() );
			$count = $tag->real_count ? $tag->real_count : $tag->get_count();
			if( $count )
				$title .= " (" . $count . ")";
			
			$link = new htmlLink( $url, $title );
			
			if( $tag->get_type() )
				$link->addClass( "tagtype" . $tag->get_type() );
			
			return $link;
		}
		
		//Return an ul list with tags
		public function tag_list( $tags ){
			$list = new htmlList();
			foreach( $tags as $tag )
				$list->addItem( $this->tag( $tag ) );
			
			return $list;
		}
		
		//A large preview of the image, possibly the original image
		//if no preview exist
		public function post_preview( $post ){
			$preview = $post->get_image( 'preview' );
			$image = $post->get_image();
			if( pathinfo( $image->url, PATHINFO_EXTENSION ) == "swf" ){
				return array(
						new htmlObject( 'object', " ", array(
								'type'=>'application/x-shockwave-flash',
								'data'=>$image->url,
								'width'=>$image->width,
								'height'=>$image->height
							) ),
						new htmlLink( $image->url, "Direct link" )
					);
			}
			else{
				$img = new htmlImage( $preview->url, 'preview' );
				return new htmlLink( $image->url, $img );
			}
		}
		
		//Returns a link to the post with an image thumbnail of the post
		public function post_thumb( $post ){
			//Add link with thumbnail
			$thumb = $post->get_image( 'thumb' );
			$img = new htmlImage( $thumb->url, 'thumbnail' );
			
			//Create link
			$url = $this->site->post_link( $post->id() );
			return new htmlLink( $url, $img );
		}
		
		//Returns a section element containing details about the post
		public function post_details( $post ){
			$image = $post->get_image();
			$details = new htmlObject( "section", NULL, toClass("details") );
			
			//Ad the dimentions
			$details->content[] = new htmlObject( "p", $image->width . "x" . $image->height, toClass("img_size") );
			
			//Add the filesize
			if( $image->filesize ){
				$size = $this->format_filesize( $image->filesize );
				$details->content[] = new htmlObject( "p", $size, toClass("img_filesize") );
			}
			
			//Add tags
			$tag_details = new htmlObject( "p", NULL, toClass("img_tag") );
			foreach( $post->get_tags() as $tag ){
				if( $tag->get_type() ){
					//Enclose it in a span
					$t = new htmlObject( 'span', $tag->name() );
					$t->addClass( "tagtype" . $tag->get_type() );
					
					$tag_details->content[] = $t;
					$tag_details->content[] = new fakeObject( " " );
				}
				else
					$tag_details->content[] = new fakeObject( $tag->name() . ' ' );
			}
			$details->content[] = $tag_details;
			
			return $details;
		}
		
		
	//Page specific stuff
		
		//Pagenation
		function page_nav( $search, $page ){
			$amount = $this->site->get_page_amount();
			if( !$amount )
				return NULL;
			
			//Calculate min and max page to show
			$min = $page - 3;
			$max = $page + 3;
			if( $min < 1 )
				$min = 1;
			if( $max > $amount )
				$max = $amount;
			
			
			$list = new htmlList();
			//If first page is not included, add it
			if( $min > 1 ){
				$list->addItem( new htmlLink( $this->site->index_link( 1, $search ), '1' ) );
				if( $min > 2 )
					$list->addItem( "..." );
			}
			
			//Add pages from min to max
			for( $i=$min; $i<=$max; $i++ ){
				if( $i == $page )
					$list->addItem( $page );
				else
					$list->addItem( new htmlLink( $this->site->index_link( $i, $search ), $i ) );
			}
			
			//If last page is not included, add it
			if( $max < $amount ){
				$list->addItem( "..." );
				$list->addItem( new htmlLink( $this->site->index_link( $amount, $search ), $amount ) );
			}
			
			//Encase the list in a nav
			$nav = new htmlObject( "nav", NULL, array( 'class'=>'page_nav' ) );
			$nav->content[] = $list;
			return $nav;
		}
		
	}
?>