<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OracleDual extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'dual';

    public function scopeGetSysDate(){
        return $this->query()
            ->select(DB::raw('SYSDATE'))
            ->whereRaw('ROWNUM = 1')
            ->first();
    }

    public function scopeGetHeaderNextValue(){
        $nextVal = DB::connection('oracle')->select(DB::raw("SELECT rcv_headers_interface_s.nextval AS nextval FROM DUAL WHERE ROWNUM = 1"));

        if (!empty($nextVal)) {
            $sequenceValue = $nextVal[0]->nextval; // Get the next value
        } else {
            $sequenceValue = null; // Or any default value you want to set
        }
        return $sequenceValue;
    }

    public function scopeGetTransactionNextValue(){
        $nextVal = DB::connection('oracle')->select(DB::raw("SELECT rcv_transactions_interface_s.nextval AS nextval FROM DUAL WHERE ROWNUM = 1"));

        if (!empty($nextVal)) {
            $sequenceValue = $nextVal[0]->nextval; // Get the next value
        } else {
            $sequenceValue = null; // Or any default value you want to set
        }
        return $sequenceValue;

    }

    public function scopeGetGroupNextValue(){
        $nextVal = DB::connection('oracle')->select(DB::raw("SELECT rcv_interface_groups_s.nextval AS nextval FROM DUAL WHERE ROWNUM = 1"));

        if (!empty($nextVal)) {
            $sequenceValue = $nextVal[0]->nextval; // Get the next value
        } else {
            $sequenceValue = null; // Or any default value you want to set
        }
        return $sequenceValue;
    }

    public function scopeGetMaterialTransactionNextValue(){
        $nextVal = DB::connection('oracle')->select(DB::raw("SELECT mtl_material_transactions_s.nextval AS nextval FROM DUAL WHERE ROWNUM = 1"));

        if (!empty($nextVal)) {
            $sequenceValue = $nextVal[0]->nextval; // Get the next value
        } else {
            $sequenceValue = null; // Or any default value you want to set
        }
        return $sequenceValue;
    }
}
