<?php

namespace App\Component\PostLogin;

use App\Component\PostLogin\PostLoginFactoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PostLoginFactory implements PostLoginFactoryInterface
{       
    public static function create($data)
	{
		$type = Str::lower($data['type']);
		$action = Str::lower($data['action']);
		$class = null;
		if($type == 'mydeposit') {
			$class = config(sprintf("depositservices.$action"));
		} else if($type == 'myloan') {
			$class = config(sprintf("loanservices.$action"));
		}
        return class_exists($class) ? new $class($data) : null;
	}
}
