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
	
	require_once "plugins/sites/san.php";
	
	class IdolComplexApi extends SankakuChannelApi{
		public function __construct(){
			$this->url = "http://idol.sankakucomplex.com/";
		}
		
		public function get_name(){ return "Idol Complex"; }
		public function get_code(){ return "idol"; }
	}
	
	
?>