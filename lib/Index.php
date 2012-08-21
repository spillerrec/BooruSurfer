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
	
	require_once "lib/Database.php";
	require_once "lib/DTPost.php";
	require_once "lib/DTTag.php";
	
	/*	Manages searches and related tags.
	 *	
	 *	Ordered indexes and post counts are cached to
	 *	reduce the amount of API calls.
	 *	
	 *	Related tags are cached if the API provides it
	 *	otherwise they are calculated from the posts.
	 *	This is still WIP/to be done.
	 */
	class Index{
		//Table names
		private $post;
		private $list;
		private $prefix;
		
		//Search parameters
		private $site;
		private $search;
		
		//Current seach info
		private $id = NULL; //For index_list
		private $ordered; //TODO: ...
		private $index; //hmm, don't like this one...
		
		//Other
		const limit = 100; //Maximal fetch limit
		
		public function __construct( $site, $search ){
			$this->site = $site;
			$this->search = $search;
			
			$this->prefix = $site->get_api()->get_code();
			$this->list = $this->prefix . "_index_list";
			$this->post = $this->prefix . "_index_post";
			
			//Create tables if missing
			$db = Database::get_instance();
			if( !$db->table_exists( $this->list ) )
				$db->db->query( "CREATE TABLE $this->list ( "
					.	"id INTEGER PRIMARY KEY AUTOINCREMENT, "
					.	"search TEXT, "
					.	"count INT, "
					.	"next_update INT, "
					.	"ordered INT, "
					.	"related_tags TEXT, "
					.	"related_counts TEXT, "
					.	"locked INT )" 
					);
			if( !$db->table_exists( $this->post ) )
				$db->db->query( "CREATE TABLE $this->post ( "
					.	"list INT NOT NULL, "
					.	"offset INT, "
					.	"post INT, "
					.	"PRIMARY KEY( list, offset ), "
					.	"FOREIGN KEY(list) REFERENCES $this->list (id) ON DELETE CASCADE )" 
					);
			new DTPost( $this->prefix ); //make sure it exists too
			//TODO: standalize search
			
			//Get search, or create it
			$this->lookup_search();
			if( $this->id === NULL )
				$this->create_search();
		}
		
		
	//Access functions
		//Current search as a string
		public function get_search(){ return $this->search; }
		
		//The time when it needs updating in unix time
		public function next_update(){
			return $this->index['next_update'];
		}
		
		//The amount of posts for the current search
		public function get_count(){
			return $this->index['count'];
		}
		
		//The amount of pages required to hold the posts
		//for this search using the default fetch amount
		public function get_page_amount(){
			return $this->get_count() ? ceil( $this->get_count() / $this->site->get_fetch_amount() ) : NULL;
		}
		
		//Retrive the contents of a page in the index
		//Returns the posts as an array (subject to change)
		public function get_page( $page ){
			//Update if too old
			if( $this->next_update() <= time() ){
				//TODO: with sankaku, check count before fetching
				//index_count( $search )
				$count = $this->site->get_api()->index_count( $this->search );
				if( $count !== NULL ){
					//We have fetched it explicitly
					$this->set_count( $count );
					$this->refresh_next_update();
					//TODO: move refreshing of next update!
				}
				else
					$this->fetch_and_save( 1, Index::limit );
			}
			
			//Get it from the DB
			return $this->fetch_from_db( $page );
		}
		
		//Retrive a set of related tags.
		//Will use (cached) API or database depending on
		//the situation.
		public function related_tags(){
			if( !$this->search ){
				//If no search just show most common tags
				$tag = new DTTag( $this->prefix );
				return $tag->most_used();
			}
			else{
				//TODO: decide whether it should calculate
				//or fetch from API. API is used for now.
				
			//Use the API to get the tags
				//Check cache first
				$tags = $this->get_tags();
				if( $tags != NULL )
					return $tags;
				
				//Fetch from API
				$data = $this->site->get_api()->related_tags( $this->search );
				if( !$data )
					return NULL;
				
				//Build array of tags to relate too
				$tags_list = array();
				foreach( $data as $key => $value ){
					//Create related tags
					$related = array();
					foreach( $value as $tag_data ){
						$tag = new DTTag( $this->prefix, $tag_data );
						$tag->db_read( $tag->name() );
						$tag->real_count = $tag_data['count'];
						$related[] = $tag;
					}
					
					$tags_list[$key] = $related;
				}
				
				//Use the first one
				$tags = array_shift( $tags_list );
				
				//Cache it and return
				$this->save_tags( $tags );
				return $tags;
			}
		}
		
	//Update functions
		//Set a index_list field to a new value
		private function change_field( $field, $value ){
			//Update object cache
			$this->index[$field] = $value;
			
			//Avoid unescaped strings
			$db = Database::get_instance()->db;
			if( is_string( $value ) )
				$value = $db->quote( $value );
			
			//Update db
			$db->query(
					"UPDATE $this->list SET $field = "
				.	$value . " WHERE id = "
				.	(int)$this->id
				);
		}
		
		//Calculates a new time for the next update
		private function refresh_next_update(){
			$new_time = time() + 5 * 60; //TODO:
			$this->change_field( 'next_update', $new_time );
		}
		
		//Sets the count
		private function set_count( $count ){
			//Fix the offsets in index_post
			$offset = $count - $this->get_count();
			$this->update_offsets( $offset );
			
			//Update the field in index_list
			$this->change_field( 'count', (int)$count );
		}
		
		
	//Database handling of posts
		//Update the posts offsets for this search
		private function update_offsets( $diff ){
			if( $diff != 0 ){
				$db = Database::get_instance()->db;
				$db->query(
						"UPDATE $this->post SET offset = offset + "
					.	(int)$diff . " WHERE list = "
					.	(int)$this->id
					);
			}
		}
		
		//Retrive a page from the site and save it in the database
		//Corrects offsets if 'count' is provided by the site
		private function fetch_and_save( $page, $limit ){
			$data = $this->site->get_api()->index( $this->search, $page, $limit );
			
			//Avoid journaling overhead
			$db = Database::get_instance()->db;
			$db->beginTransaction();
			
			//Fix offsets
			if( isset( $data['count'] ) )
				$this->set_count( $data['count'] );
			
			//Calculate initial offset
			$offset = ($page-1) * $limit;
			
			//Save all posts
			foreach( $data as $post )
				//Do not process extra properties like 'count'
				if( gettype( $post ) == "array" ){
					//Save post data
					$p = new DTPost( $this->prefix, $post );
					$p->db_save();
					
					//Save offset data
					$db->query( "REPLACE INTO $this->post VALUES ( "
						.	(int)$this->id . ", "
						.	(int)$offset . ", "
						.	(int)$p->id() . " )"
						);
					
					//Increment offset
					$offset++;
				}
			
			//Set new update time, if count was refreshed
			if( isset( $data['count'] ) )
				$this->refresh_next_update();
			
			$db->commit();
		}
		
		//Retrive a page from the database.
		//If it is not there, it tries to fetch it from the site
		//and calls itself recursively.
		//If $limit is NULL the default limit is used.
		private function fetch_from_db( $page, $limit=NULL, $recursion=0 ){
			//Prevent it from recursing endlessy
			$recursion++;
			if( $recursion > 2 )
				die( "Not possible to fetch page : (" );
			
		//Grap everything from the database
			//Prepare the query
			$db = Database::get_instance()->db;
			$stmt = $db->prepare( "SELECT * FROM $this->post "
				.	"LEFT JOIN " . $this->prefix . '_post '
				.	"ON post = id "
				.	"WHERE offset >= :range_min AND offset < :range_max "
				.	"AND list = :id"
				);
			
			//Get default fetch amount if unset
			if( $limit == NULL )
				$limit = $this->site->get_fetch_amount();
			
			//Calculate min/max
			$min = ($page-1) * $limit;
			$max = $page * $limit;
			if( $max > $this->get_count() )
				$max = $this->get_count();
			
			//Finish the query and execute
			$stmt->execute( array(
					'range_min'	=>	$min
				,	'range_max'	=>	$max
				,	'id'	=>	$this->id
				) );
			$data = $stmt->fetchAll();
			
		//Try to retrive the data
			if( count( $data ) == $max-$min ){
				//All post are known, convert and return them
				$posts = array();
				foreach( $data as $post )
					$posts[] = new DTPost( $this->prefix, $post );
				return $posts;
			}
			else{
				//Not all are fetched, fetch and try again
				$this->fetch_and_save(
						(int)(($page-1)/3) + 1
					,	$limit * 3
					);
				return $this->fetch_from_db( $page, $limit, $recursion );
			}
		}
		
		
	//Database handling of searches and related tags
		//Initialize this object with search-data form the database
		private function lookup_search(){
			//Get data
			$db = Database::get_instance()->db;
			$stmt = $db->query( "SELECT * FROM $this->list WHERE search = " . $db->quote( $this->search ) );
			$this->index = $stmt ? $stmt->fetch() : NULL;
			
			//Save ID
			$this->id = $this->index ? $this->index['id'] : NULL;
		}
		
		//Store a new search in the database
		private function create_search(){
			//Prepare values to insert
			$search = $this->search;
			$ordered = false; //TODO: check this
			
			//Save in db
			$db = Database::get_instance()->db;
			$stmt = $db->query(
					"INSERT INTO $this->list ( "
				.	"search, ordered"
				.	" ) VALUES ("
				.	$db->quote( $search ) . ", "
				.	(int)$ordered
				.	" )"
				);
				
			//Find it again and fetch values
			//Stupid, but whatever...
			$this->lookup_search();
		}
		
		//Store a set of related tags
		private function save_tags( $tags ){
			//Calculate fields
			$names = $counts = "";
			foreach( $tags as $tag ){
				$names .= $tag->name() . " ";
				$counts .= $tag->real_count . " ";
			}
			
			//Update fields
			$this->change_field( 'related_tags', $names );
			$this->change_field( 'related_counts', $counts );
		}
		
		//Retrive a set of cached tags
		private function get_tags(){
			//fail if not cached
			if( !$this->index['related_tags'] )
				return NULL;
			
			//Fetch tags and combine them in a single array
			$tags = array();
			$raw_names = explode( ' ', $this->index['related_tags'] );
			$raw_counts = explode( ' ', $this->index['related_counts'] );
			$raw = array_combine( $raw_names, $raw_counts );
			
			//Create all tags
			foreach( $raw as $name => $count )
				if( $name ){
					$t = new DTTag( $this->prefix );
					$t->db_read( $name );
					$t->real_count = $count;
					$tags[] = $t;
				}
			
			return $tags;
		}
	}
	
?>