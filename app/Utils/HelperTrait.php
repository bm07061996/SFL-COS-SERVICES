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
}
