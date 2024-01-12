<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class FdPincodeMaster extends Model implements Transformable
{
    use TransformableTrait;

    protected $table = 'fd_pincode_master';

    protected $fillable = ['ref_id', 'pincode', 'area', 'city', 'state', 'country', 'taluk', 'flag'];
}