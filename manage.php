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
	
	require_once 'lib/header.php';
	require_once 'lib/SiteInfo.php';
	require_once 'lib/Styler.php';
	
	//Get site, if available
	require_once "lib/Booru.php";
	$site = isset( $_GET['site'] ) ? new Booru( $_GET['site'] ) : NULL;
	$styler = new Styler( $site );
	
	$info = new SiteInfo();
	
	if( $site ){
		//Show site specific stuff
		$info->db_read( $_GET['site'] );
		
		//If any actions were requested, do those actions
		if( isset( $_GET['set'] ) && isset( $_POST['action'] ) ){
			switch( $_POST['action'] ){
				case 'set_user':
						$info->set_user( $_POST['user'], $_POST['pass'] );
					break;
				
				case 'update_tags':
						$site->refresh_tags();
					break;
				
				case 'activate':
						$info->activate();
					break;
				
				case 'disable':
						$info->activate( false );
					break;
			}
			
			//TODO: do redirect
		}
		//Always show site actions
		
		$layout = new mainLayout( 'Administration of site' );
		$layout->page->html->title = 'Administrate this';
		
		
		//Make a fieldset containing this action
		function action_field( $action, $title=NULL ){
			$fieldset = new htmlObject('fieldset');
			$input = new htmlObject( 'input' );
			$input->attributes['type'] = 'text';
			$input->attributes['name'] = 'action';
			$input->attributes['hidden'] = 'hidden';
			$input->attributes['value'] = $action;
			$fieldset->content[] = $input;
			
			//Add title
			if( $title )
				$fieldset->content[] = new htmlObject( 'h3', $title );
			
			return $fieldset;
		}
		
		//Make a form to the action fieldset
		function add_form( $fieldset ){
			global $info, $layout;
			$fieldset = new htmlObject( 'form', $fieldset );
			$fieldset->attributes['action'] = '/'.$info->get_id().'/manage/set/';
			$fieldset->attributes['method'] = 'post';
			$layout->main->content[] = $fieldset;
		}
		
	//The password form
		
		//Text
		$user = $info->has_user();
		$pass_text = ( $user ? 'Change' : 'Set' ) . ' password';
		$fieldset = action_field( 'set_user', $pass_text );
		
		//User input
		$input_user = new htmlObject( 'input' );
		$input_user->attributes['name'] = 'user';
		$input_user->attributes['type'] = 'text';
		$input_user->attributes['value'] = $user;
		$input_user->attributes['placeholder'] = 'Username';
		$fieldset->content[] = $input_user;
		
		//password input
		$input_pass = new htmlObject( 'input' );
		$input_pass->attributes['name'] = 'pass';
		$input_pass->attributes['type'] = 'password';
		$input_pass->attributes['placeholder'] = 'Password';
		$fieldset->content[] = $input_pass;
		
		//button
		$input_submit = new htmlObject( 'input' );
		$input_submit->attributes['type'] = 'submit';
		$fieldset->content[] = $input_submit;
		
		//Add password form
		add_form( $fieldset );
		
		
	//Add update tags
		$update_field = action_field( 'update_tags', 'Update tags' );
		
		//Add time on last update
		$time = $info->get_tags_updated();
		if( $time > 0 )
			$time = 'Last updated: '.$styler->format_date( $time );
		else
			$time = 'No tags in DB!';
		$update_field->content[] = new htmlObject( 'p', $time );
		
		
		//Add submit and form
		$update_submit = new htmlObject( 'input' );
		$update_submit->attributes['type'] = 'submit';
		$update_field->content[] = $update_submit;
		add_form( $update_field );
		
		
	//Add active/deactivate
		if( !$info->is_active() )
			$activate = action_field( 'activate', 'Enable site' );
		else
			$activate = action_field( 'disable', 'Disable site' );
		
		$activate_submit = new htmlObject( 'input' );
		$activate_submit->attributes['type'] = 'submit';
		$activate->content[] = $activate_submit;
		add_form( $activate );
		
		
		
		$layout->page->write();
	}
	else{
		//Show overview over all sites
		
		$layout = new mainLayout( 'Administration' );
		$layout->page->html->title = 'Administrate sites';
		
		//Make sure all possible sites are in the db
		//TODO: make some fancy plugin thing...
		$info->add_site( 'dan', 'DanbooruApi' );
		$info->add_site( 'neko', 'NekobooruApi' );
		$info->add_site( 'vector', 'VectorbooruApi' );
		$info->add_site( 'yukkuri', 'YukkuriApi' );
		$info->add_site( 'e621', 'E621Api' );
		$info->add_site( 'threedee', 'ThreeDeeBooruApi' );
		$info->add_site( 'san', 'SankakuChannelApi' );
		$info->add_site( 'idol', 'IdolComplexApi' );
		$info->add_site( 'kona', 'KonachanApi' );
		$info->add_site( 'yandere', 'YandereApi' );
		$info->add_site( 'gel', 'GelbooruApi' );
		$info->add_site( 'rule34', 'rule34Api' );
		$info->add_site( 'xbooru', 'XbooruApi' );
		$info->add_site( 'safe', 'SafebooruApi' );
		$info->add_site( 'furry', 'FurryBooruApi' );
		$info->add_site( 'size', 'SizeBooruApi' );
		
		//ShimmieRSS
		$info->add_site( 'meme', 'MemeFolderApi' );
		$info->add_site( 'katawa', 'KatawaShoujoApi' );
		$info->add_site( 'tradio', 'TouhouRadioApi' );
		
		//Show a header
		$header = new htmlObject( 'tr' );
		$header->content[] = new htmlObject( 'th', 'Site' );
		$header->content[] = new htmlObject( 'th', 'Code' );
		$header->content[] = new htmlObject( 'th', 'Active' );
		$header->content[] = new htmlObject( 'th', 'User' );
		$header->content[] = new htmlObject( 'th', 'Tags Loaded' );
		
		//Show a list over all sites
		$rows = array();
		$sites = SiteInfo::all();
		foreach( $sites as $site ){
			//Prepare info
			$id = $site->get_id();
			$name = new htmlLink( "/$id/manage/", $site->get_name() );
			$active = $site->is_active() ? 'true' : 'false';
			
			//Prepare date
			$time = $site->get_tags_updated();
			if( $time )
				$time = $styler->format_date( $time );
			else if( $site->get_api( $id )->supports_all_tags() )
				//Site supports tags, but hasn't loaded it yet
				$time = 'Not loaded!';
			else
				$time = NULL;
			
			//Add content in HTML
			$row = new htmlObject( 'tr' );
			$row->content[] = new htmlObject( 'td', $name );
			$row->content[] = new htmlObject( 'td', $id );
			$row->content[] = new htmlObject( 'td', $active );
			$row->content[] = new htmlObject( 'td', $site->has_user() );
			$row->content[] = new htmlObject( 'td', $time );
			$rows[] = $row;
		}
		
		$layout->main->content[] = new htmlObject( 'table', array( $header, $rows ) );
		
		$layout->page->write();
	}
	
?>

