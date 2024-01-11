<?php

namespace App\Util;

use Closure;
use Illuminate\Support\Facades\Redis;

trait RedisHelper
{
	public $redis;

	public function redis()
	{
		if (is_null($this->redis)) {
			$this->redis = Redis::connection();
		}

		return $this;
	}

	public function jsonDecode($index, $decode = false)
	{
		$data = $this->redis->get($index);
		if ($decode === true) {
			$data = json_decode($data, true);
		}

		return $data;
	}

	public function jsonEncode($index)
	{
		$value = $this->redis->get($index);
		return empty($value) === false ? json_encode($value) : null;
	}

	public function setJsonEncoded($index, $data)
	{
		$this->redis->set($index, json_encode($data));
	}

	public function mgetMatchedValue(array $indexs)
	{
		$data = $this->mget($indexs);

		foreach ($data as $key => $value) {
			if (empty($value) === false) {
				return json_decode($value, true);
			}
		}
	}

	public function mget(array $indexs)
	{
		return $this->redis->mget($indexs);
	}

	public function set($index, $data)
	{
		$this->redis->set($index, json_encode($data));
	}

	public function remember($key, $minutes = 14400, Closure $callback)
	{
		$value = $this->redis()->jsonDecode($key, true);

        if (!is_null($value)) {
            
            return $value;
        }

        $value = $callback();

        $this->redis()->redis->setex($key, $minutes, json_encode($value));

        return $value;
	}
	
}