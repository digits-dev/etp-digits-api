<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OracleDual extends Model
{
    use HasFactory;

    protected $connection = 'oracle';
    protected $table = 'dual';

    public function scopeGetSysDate(){
        return $this->select('sysdate')->first();
    }

    public function scopeGetHeaderNextValue(){
        return $this->select('rcv_headers_interface_s.nextval')->first();
    }

    public function scopeGetTransactionNextValue(){
        return $this->select('rcv_transactions_interface_s.nextval')->first();
    }

    public function scopeGetGroupNextValue(){
        return $this->select('rcv_interface_groups_s.nextval')->first();
    }

    public function scopeGetMaterialTransactionNextValue(){
        return $this->select('mtl_material_transactions_s.nextval')->first();
    }
}
