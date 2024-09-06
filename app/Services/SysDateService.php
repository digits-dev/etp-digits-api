<?php

namespace App\Services;

use App\Models\OracleDual;

class SysDateService
{
    public function getSysdate() {
        return OracleDual::getSysdate()->sysdate;
    }
}
