<?php

namespace App\Services;

use App\Models\OracleDual;

class TransactionInterfaceService
{
    public function getTransactionNextValue() {
        return OracleDual::getTransactionNextValue();
    }
}
