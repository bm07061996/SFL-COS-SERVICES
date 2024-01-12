<?php

namespace App\Repositories\Eloquent;

use App\Entities\FdPincodeMaster;
use Prettus\Repository\Eloquent\BaseRepository;

class FdPincodeRepository extends BaseRepository
{
    public function model()
    {
        return FdPincodeMaster::class;
    }

    public function searchByPincode($pincode)
    {
        return $this->model->where('pincode', $pincode)->WhereIn('flag', ['L','R'])->get();
    }
}
