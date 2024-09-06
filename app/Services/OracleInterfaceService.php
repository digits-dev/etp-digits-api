<?php

namespace App\Services;

use App\Models\OracleDual;

class OracleInterfaceService
{
    public function getGroupNextValue() {
        return OracleDual::getGroupNextValue()->nextval;
    }

    public function getHeaderNextValue() {
        return OracleDual::getHeaderNextValue()->nextval;
    }

    public function getSysdate() {
        return OracleDual::getSysdate()->sysdate;
    }

    public function getTransactionNextValue() {
        return OracleDual::getTransactionNextValue()->nextval;
    }

    public function getMaterialTransactionNextValue() {
        return OracleDual::getMaterialTransactionNextValue()->nextval;
    }
}
