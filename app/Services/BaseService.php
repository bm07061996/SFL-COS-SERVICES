<?php

namespace App\Services;

use App\Utils\HelperTrait;
use App\Utils\RestServiceTrait;
use Laravel\Lumen\Routing\Controller as BaseController;

class BaseService extends BaseController
{
    use RestServiceTrait, HelperTrait;
}
