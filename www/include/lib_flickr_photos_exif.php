<?php

	#################################################################

	loadlib("flickr_photos");
	loadlib("exif_tools");
	
	#################################################################

	function flickr_photos_exif_has_exif(&$photo){

		# THIS IS NOT IDEAL. Maybe store a hasexif flag against
		# the database on import? (20111118/straup) Anyway, for
		# now it's just for photo owners. Or at least caching or
		# something...
 
		$rsp = flickr_photos_exif_read($photo);
		return $rsp['ok'];
	}

	#################################################################

	function flickr_photos_exif_read(&$photo){

		$map = flickr_photos_media_map();

		if ($map[$photo['media']] == 'video'){
			return not_ok("video does not contain EXIF data");
		}

		$fname = "{$photo['id']}_{$photo['originalsecret']}_o.{$photo['originalformat']}";
		$froot = $GLOBALS['cfg']['flickr_static_path'] . flickr_photos_id_to_path($photo['id']);

		$path = "{$froot}/{$fname}";

		if (! preg_match("/\.jpe?g$/i", $path)){
			return not_ok("not a JPEG photo");
		}

		if (! file_exists($path)){
			return not_ok("original photo not found");
		}

		# TO DO: cache me?

		$exif = exif_read_data($path);

		if (! $exif){
			return not_ok("failed to read EXIF data");
		}

		# TO DO: expand EXIF tag values

		$to_simplejoin = array(
			'SubjectLocation',
			'GPSLatitude',
			'GPSLongitude',
			'GPSTimeStamp',
		);

		foreach ($to_simplejoin as $tag){

			if (isset($exif[$tag])){
				$exif[$tag] = implode(",", $exif[$tag]);
			}
		}

		# TO DO: work out how/where individual EXIF tags get
		# "prettified" ...

		ksort($exif);

		return ok(array("rows" => $exif));
	}

	#################################################################

	function exif_tools_explode_gps_altitude($altitude, $ref=null){

		$altitude = exif_tools_rational2float($altitude);

		# 1 = Below Sea Level

		if ($ref){
			$altitude = - $altitude;
		}

		return $altitude;
	}

	#################################################################

	function exif_tools_explode_gps_img_direction($direction, $ref=null){

		$direction = exif_tools_rational2float($direction);

		if ($ref == 'M'){
			# uh...
		}

		return $direction;
	}

	#################################################################
?>