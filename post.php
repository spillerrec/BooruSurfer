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
	
	include "lib/header.php";
	
	require_once "lib/Booru.php";
	require_once "lib/Styler.php";
	$site = new Booru( $_GET['site'] );
	$styler = new Styler( $site );
	
	//Retrive post
	$id = $_GET["id"];
	$post = $site->post( $id );
	
	if( $post ){
		$layout = new mainLayout();
		$layout->navigation = $styler->main_navigation();
		$layout->page->html->title = 'Post: ' . $post->name();
		$layout->main->addClass( 'post' );
		$layout->sidebar->addClass( 'post_info' );
		$thumbnail = new htmlObject( 'link' );
		
		$preview = $post->get_image( 'preview' );
		$image = $post->get_image();
		
		//Add favicon
		$favicon = new htmlObject( 'link' );
		$favicon->attributes[ 'rel' ] = 'icon';
		$favicon->attributes[ 'href' ] = '/' . $_GET['site'] . '/favicon/post/';
		$layout->page->html->head->content[] = $favicon;
		
		//Add opera speeddial thumbnail
		$speeddial = new htmlObject( 'link' );
		$speeddial->attributes[ 'rel' ] = 'image_src';
		$speeddial->attributes[ 'href' ] = $preview['url'];
		$layout->page->html->head->content[] = $speeddial;
		
		$image_container = new htmlObject( 'div', NULL, toClass( 'container' ) );
		$image_container->content[] = $styler->post_preview( $post, 'image' );
		
		
		//Notes
		$notes = $site->notes( $post );
		if( $notes )
			foreach( $notes as $note )
				$image_container->content[] = $styler->note( $note, $post );
		
		$layout->main->content[] = $image_container;
		
		
		//Comments
		$comments = $site->comments( $post );
		if( $comments ){
			$container = new htmlObject( 'div' );
			$container->addClass( 'comments' );
			$layout->all->addClass( 'has_comments' );
			
			foreach( $comments as $comment )
				$container->content[] = $styler->comment( $comment, $post );
			
			$layout->all->content[] = $container;
		}
		
		//Fill sidebar
		$layout->sidebar->content[] = new htmlObject( "h3", "Info:" );
		$layout->sidebar->content[] = $styler->post_info( $post, true );
		
//		$layout->sidebar->content[] = new htmlLink( post_siteurl( $id ), "On-site link" );
		
		
		if( $post->parent_id() ){
			$parent = $site->post( $post->parent_id() );
			
			$img = new htmlObject( "section", NULL, toClass( "post_parent") );
			$img->content[] = $styler->post_thumb( $parent );
			
			$layout->sidebar->content[] = new htmlObject( "h3", "Parent:" );
			$layout->sidebar->content[] = $img;
			
		}
		
		if( $post->has_children() ){
			$index_child = $site->index( "parent:" . $post->id() );
			$children = $index_child->get_page( 1 );
			
			$layout->sidebar->content[] = new htmlObject( "h3", "Children:" );
			$img = new htmlObject( "section", NULL, toClass( "post_children") );
			
			//Add children
			foreach( $children as $child ){
				if( $child->id() != $post->id() ){
					$img->content[] = $styler->post_thumb( $child );
				}
			}
			
			$layout->sidebar->content[] = $img;
		}
		
		$layout->sidebar->content[] = new htmlObject( "h3", "Tags:" );
		
		
		$tags = $post->get_tags();
		$layout->sidebar->content[] = $styler->tag_list( $tags );
		
		$layout->page->write();
	}
	else{
		//No post to display : \
		echo "The post was not found";
		
		//TODO: error page
	}
	
?>