<?php

namespace App\Services;

use App\Models\OracleDual;

class OracleInterfaceService
{
    public function getGroupNextValue() {
        return OracleDual::getGroupNextValue();
    }

    public function getHeaderNextValue() {
        return OracleDual::getHeaderNextValue();
    }

    public function getSysdate() {
        return OracleDual::getSysdate()->sysdate;
    }

    public function getTransactionNextValue() {
        return OracleDual::getTransactionNextValue();
    }

    public function getMaterialTransactionNextValue() {
        return OracleDual::getMaterialTransactionNextValue();
    }
}
