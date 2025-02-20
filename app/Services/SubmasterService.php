<?php

namespace App\Services;

use App\Models\Channel;
use App\Models\TransferGroup;

class SubmasterService
{
    public function getChannels(){
        return Channel::active()->get();
    }

    public function getTransferGroups(){
        return TransferGroup::active()->get();
    }
}
