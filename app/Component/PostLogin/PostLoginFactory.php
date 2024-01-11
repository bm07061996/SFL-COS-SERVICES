<?php

namespace App\Component\PostLogin;

use App\Component\PostLogin\PostLoginFactoryInterface;

use Illuminate\Support\Str;


class PostLoginFactory implements PostLoginFactoryInterface
{       
	public static function create($className, $namespace, $data = [])
	{
        $className = Str::ucfirst($className);
        $class = "App\Component\PostLogin\\" . $namespace . "\\Modules\\" . $className;
        return class_exists($class) ? new $class($data) : null;
	}
}
