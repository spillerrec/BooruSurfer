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
	require_once "lib/Index.php";
	
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
		
		//Create a <time> element
		public function time( $unix_time ){
			$time = new htmlObject( 'time', $this->format_date( $unix_time ) );
			$time->attributes['datetime'] = date( DATE_ISO8601 );
			return $time;
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
		public function tag_list( $tags, $title=NULL ){
			//Do nothing if no tags
			if( !$tags )
				return NULL;
			
			//Add title if set
			$h3 = ( $title ) ? new htmlObject( 'h3', $title ) : NULL;
			
			//Add tags in <ul>
			$list = new htmlList();
			foreach( $tags as $tag )
				$list->addItem( $this->tag( $tag ) );
			
			return array( $h3, $list );
		}
		
		//Returns a link to a tag and possibly other info
		public function note( $note, $post ){
			$image = $post->get_image();
			$block = new htmlObject( 'div' );
			
			$x = $note->x() / $image->width * 100;
			$y = $note->y() / $image->height * 100;
			$width = $note->width() / $image->width * 100;
			$height = $note->height() / $image->height * 100;
			$style = "left:$x%;top:$y%;width:$width%;height:$height%";
			
			$block->attributes['style'] = $style;
			$block->content = new htmlObject( 'div', $note->body() );
			//TODO: add body
			
			return $block;
		}
		
		//Returns a comment
		public function comment( $comment ){
			$block = new htmlObject( 'article' );
			
			$block->content[] = new htmlObject( 'h3', $comment->creator() );
			$block->content[] = $this->time( $comment->created_at() );
			$block->content[] = new htmlObject( 'p', $comment->body() );
			
			return $block;
		}
		
		//Returns a human readable rating, or NULL if none
		public function post_rating( $post ){
			switch( $post->rating() ){
				case DTPost::SAFE: return 'safe';
				case DTPost::QUESTIONABLE: return 'questionable';
				case DTPost::ADULT: return 'explict';
				default: return NULL;
			}
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
			$link = new htmlLink( $url, $img );
			
			//Add classes
			if( $post->is_pending() )
				$link->addClass( 'is_pending' );
			if( $post->is_flagged() )
				$link->addClass( 'is_flagged' );
			if( $post->has_children() )
				$link->addClass( 'has_children' );
			if( $post->parent_id() )
				$link->addClass( 'has_parent' );
			
			return $link;
		}
		
		//Returns a series of <p> which contains post info
		public function post_info( $post, $extended=false ){
			$info = array();
			$image = $post->get_image();
			
			//create a p with em
			$add = function( $title, $content ){
				return new htmlObject( "p", array(
						new htmlObject( 'em', "$title:" )
					,	$content
					) );
			};
			
			//Add creation time
			if( $date = $post->added() )
				$info[] = $add( 'Posted'
					,	$this->format_date( $date )
					);
			
			//Add user
			if( $extended && $user = $post->author() )
				$info[] = $add( 'By', $user );
			
			//Add the dimentions
			if( $image->width && $image->height )
				$info[] = $add( 'Dimensions'
					,	$image->width . "x" . $image->height
					);
			
			//Add the filesize
			if( $image->filesize )
				$info[] = $add( 'Size'
					,	$this->format_filesize( $image->filesize )
					);
			
			//Add rating
			$rating = $this->post_rating( $post );
			if( $rating )
				$info[] = $add( 'Rating', $rating );
			
			//Add source
			if( $extended && $source = $post->source() )
				$info[] = $add( 'Source', $source );
			
			return $info;
		}
		
		//Returns a section element containing details about the post
		public function post_details( $post ){
			$details = new htmlObject( "section", NULL, toClass("details") );
			
			$details->content[] = $this->post_info( $post );
			
			//Add tags
			$tag_details = new htmlObject( "p" );
			$tag_details->content[] = new htmlObject( 'em', 'Tags:' );
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
		function page_index_nav( $index, $page ){
			$amount = $index->get_page_amount();
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
				$list->addItem( new htmlLink( $this->site->index_link( 1, $index->get_search() ), '1' ) );
				if( $min > 2 )
					$list->addItem( "..." );
			}
			
			//Add pages from min to max
			for( $i=$min; $i<=$max; $i++ ){
				if( $i == $page )
					$list->addItem( $page );
				else
					$list->addItem( new htmlLink( $this->site->index_link( $i, $index->get_search() ), $i ) );
			}
			
			//If last page is not included, add it
			if( $max < $amount ){
				$list->addItem( "..." );
				$list->addItem( new htmlLink( $this->site->index_link( $amount, $index->get_search() ), $amount ) );
			}
			
			//Encase the list in a nav
			$nav = new htmlObject( "nav", NULL, array( 'class'=>'page_nav' ) );
			$nav->content[] = $list;
			return $nav;
		}
		
	}
?>