<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class FdPincodeMaster extends Model {

    protected $table = 'fd_pincode_master';
    
    protected $fillable = [
        'ref_id', 'pincode', 'area', 'state', 'country', 'flag', 'taluk'
    ];

}
