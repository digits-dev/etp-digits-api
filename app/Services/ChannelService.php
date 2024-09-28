<?php

namespace App\Services;

use App\Models\Channel;

class ChannelService
{
    public function getChannels(){
        return Channel::active()->get();
    }
}
