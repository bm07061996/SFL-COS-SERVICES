<?php

namespace App\Component\PostLogin\PostLoginProcess;

abstract class PostLoginProcessAbstract
{

	/**array
     * All of the Response.
     *
     * @var 
     */
    public $response = [];

    /**
     * All of the Data from the request as well as internal data injection.
     *
     * @var array
     */
    protected $data = [];
    
    public $subscription_status = false;

    /**
     * Register a set of routes with a set of shared attributes.
     *
     * @param  array  $data
     * @return void
     */

	public function __construct(array $data)
	{
        $this->data = $data;
	}

    abstract public function process();
}
