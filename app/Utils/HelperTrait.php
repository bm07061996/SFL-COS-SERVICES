<?php

namespace App\Utils;

use Illuminate\Support\Arr;

trait HelperTrait
{
    public function sanitizeEmptyVariable($array, $key)
	{
		$value = Arr::get($array, $key);
		return empty(trim($value)) === false ? trim($value) : null;
	}

	public function generateUUID()
	{
		$prefix = sprintf('%s-%s-%s', microtime(true), getmypid(), gethostname());
		return md5(uniqid($prefix, true));
	}
}
