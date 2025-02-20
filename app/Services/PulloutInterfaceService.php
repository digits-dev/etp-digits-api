<?php

namespace App\Services;

use App\Models\Pullout;

class PulloutInterfaceService
{
    public function getPending(){
        return Pullout::getPending()->get()->toArray();
    }

    public function getPendingLines(){
        return Pullout::getPendingLines()->get()->toArray();
    }
}
