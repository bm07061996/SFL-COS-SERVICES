<?php

namespace App\Util;

use Log;

trait Utils
{
	public function urlGenerator($string) 
	{
	    $string = preg_replace("/[^a-z0-9_\s-]/", "", strtolower($string));
	    $string = preg_replace("/[\s-]+/", " ", $string);
	    $string = preg_replace("/[\s_]/", "-", $string);
    
    	return trim($string, "-");
	}

	public function slugFormator($string)
	{
		return camel_case(preg_replace("/[\s_\/]/", "-", strtolower($string)));
	}

	public function multiSort($data, $index , $order = SORT_ASC)
	{
		array_multisort(array_column($data, $index), $order, $data);

		return $data;
	}

	public function allowedMimeTypeForResume() {
		return ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
	}

}