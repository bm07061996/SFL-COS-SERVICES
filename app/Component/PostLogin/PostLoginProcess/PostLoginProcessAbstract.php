<?php

namespace App\Component\PostLogin\PostLoginProcess;

abstract class PostLoginProcessAbstract
{

    public $response = [];

    public $subscription_status = false;

    protected $data = [];
    
	public function __construct(array $data)
	{
        $this->data = $data;
	}

    abstract public function process();
}
