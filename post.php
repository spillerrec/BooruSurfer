<?php
	include "lib/header.php";
	
	require_once "lib/Booru.php";
	$site = new Booru( $_GET['site'] );
	
	//Retrive post
	$id = $_GET["id"];
	$post = $site->post( $id );
	
	if( $post ){
		$layout = new mainLayout( "Konachan", "kona" );
		$layout->page->html->title = "Post: " . $post->get( 'tags' );
		$layout->main->attributes['class'] = "post";
		$layout->sidebar->attributes['class'] = "post_info";
		$thumbnail = new htmlObject( "link" );
		
		$preview = $post->get_image( 'preview' );
		$image = $post->get_image();
		
		//Add opera speeddial thumbnail
		$speeddial = new htmlObject( 'link' );
		$speeddial->attributes[ 'rel' ] = "image_src";
		$speeddial->attributes[ 'href' ] = $preview->url;
		$layout->page->html->head->content[] = $speeddial;
		
		$image_container = new htmlObject( 'div', NULL, toClass( 'container' ) );
		$img = $preview->to_html( "preview" );
		if( pathinfo( $preview->url, PATHINFO_EXTENSION ) == "swf" ){
			$image_container->content[] = $img;
			$image_container->content[] = new htmlLink( $image->url, "Direct link" );
		}
		else
			$image_container->content[] = new htmlLink( $image->url, $img );
		
		
		/*Notes
		$notes = get_notes( $id );
		if( $notes )
			foreach( $notes as $note ){
				$note_info = get_note_info( $note );
				
				//Write note
				$block = new htmlObject( 'div' );
				$x = $note->x / $image->width * 100;
				$y = $note->y / $image->height * 100;
				$width = $note->width / $image->width * 100;
				$height = $note->height / $image->height * 100;
				$style = "left:$x%;top:$y%;width:$width%;height:$height%";
				
				$block->attributes['style'] = $style;
				$block->content = new htmlObject( 'div', $note_info->body );
				//TODO: add body
				
				$image_container->content[] = $block;
			}
		//*/
		$layout->main->content[] = $image_container;
		
		//Fill sidebar
		$layout->sidebar->content[] = new htmlObject( "p", "Dimension: $image->width" . "x$image->height" );
		$layout->sidebar->content[] = new htmlObject( "p", "Filesize: $image->filesize bytes" );//TODO: show nicely
		
		//Date
		date_default_timezone_set( 'Europe/Copenhagen' );
		if( $date = $post->get( 'creation_date' ) )
			$layout->sidebar->content[] = new htmlObject( "p", "Posted: " . date( 'H:i d/m/Y', $date ) );
		
//		$layout->sidebar->content[] = new htmlLink( post_siteurl( $id ), "On-site link" );
		
		
		if( $post->parent_id() ){
			$parent = $site->post( $post->parent_id() );
			
			$img = new htmlObject( "section", NULL, toClass( "post_parent") );
			$img->content[] = thumbnail( $parent, $site->post_link( $parent->id() ) );
			
			$layout->sidebar->content[] = new htmlObject( "h3", "Parent:" );
			$layout->sidebar->content[] = $img;
			
		}
		
		if( $post->has_children() ){
			$index_child = $site->index( "parent:" . $post->id() );
			
			$layout->sidebar->content[] = new htmlObject( "h3", "Children:" );
			$img = new htmlObject( "section", NULL, toClass( "post_children") );
			
			//Add children
			foreach( $index_child as $child ){
				if( $child->id() != $post->id() ){
					$img->content[] = thumbnail( $child, $site->post_link( $child->id() ) );
				}
			}
			
			$layout->sidebar->content[] = $img;
		}
		
		$layout->sidebar->content[] = new htmlObject( "h3", "Tags:" );
		
		$tags = explode( " ", $post->get( 'tags' ) );
		$list = new htmlList();
		foreach( $tags as $tag )
			if( $tag )
				$list->addItem( new htmlLink( $site->index_link( 1, $tag ), str_replace( "_", " ", $tag ) ) );
		$layout->sidebar->content[] = $list;
		
		$layout->page->write();
	}
	else{
		//No post to display : \
		//write_header( "Could not find post" );
		
		echo "The post was not found";
		
		//TODO: error page
	}
	
?>