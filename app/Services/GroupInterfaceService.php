<?php

namespace App\Services;

use App\Models\OracleDual;

class GroupInterfaceService
{
    public function getGroupNextValue() {
        return OracleDual::getGroupNextValue()->nextval;
    }
}
