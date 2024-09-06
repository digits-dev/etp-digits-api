<?php

namespace App\Services;

use App\Models\OracleDual;

class HeaderInterfaceService
{
    public function getHeaderNextValue() {
        return OracleDual::getHeaderNextValue()->nextval;
    }
}
