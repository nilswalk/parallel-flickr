<?php

	#################################################################

	function flickr_galleries_import_for_nsid($nsid){

		$flickr_user = flickr_users_get_by_nsid($nsid);
		$user = users_get_by_id($flickr_user['user_id']);

		if (! $user){
			return not_okay("invalid user");
		}

		$method = "flickr.galleries.getList";

		$args = array(
			'user_id' => $flickr_user['nsid'],
			'auth_token' => $flickr_user['auth_token'],
			'page' => 1
		);

		$pages = null;

		while ((! isset($pages)) || ($pages >= $args['page'])){

			$rsp = flickr_api_call($method, $args);

			if (! $rsp['ok']){
				return $rsp;
			}

			if (! isset($pages)){
				$pages = $rsp['rsp']['photos']['pages'];
			}

			$galleries = $rsp['rsp']['galleries']['gallery'];

			if (! count($galleries)){
				# do something...
			}

			foreach ($galleries as $g){
				flickr_galleries_import_gallery($g);
			}

			$args['page'] += 1;
		}

		return okay();
	}

	#################################################################

	function flickr_galleries_import_gallery(&$gallery){

		# This is my fault. I'm sorry. (20120105/straup)

		list($user_id, $gallery_id) = explode("-", $gallery['id'], 2);

		$owner = $gallery['owner'];
		$title = $gallery['title']['_content'];
		$description = $gallery['title']['_description'];
		$created = $gallery['date_create'];
		$primary = $gallery['primary_photo_id'];

		# all photos in galleries are public/safe so there's no
		# need for an auth token (20120105/straup)

		$method = "flickr.galleries.getPhotos";

		$args = array(
			'gallery_id' => $gallery['id'],
			'extras' => $GLOBALS['cfg']['flickr_api_spr_extras'],
		);

		$rsp = flickr_api_call($method, $args);

		# do stuff here
	}

	#################################################################
?>
