<?php

namespace App\Imports;

use App\Models\Reason;
use App\Models\TransactionType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class ReasonImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use Importable, SkipsFailures;
    public function model(array $row) {

        $type = Cache::remember("type_{$row['transaction_type']}", 3600, function() use ($row){
            return TransactionType::where('transaction_type', $row['transaction_type'])->value('id');
        });

        return Reason::firstOrCreate(['pullout_reason' => $row['pullout_reason']],[
            'pullout_reason' => $row['pullout_reason'],
            'transaction_types_id' => $type,
            'bea_so_reason' => $row['so_reason'],
            'bea_mo_reason' => $row['mo_reason'],
            'status' => $row['status'],
        ]);
    }

    public function rules(): array {
        return [
            'pullout_reason' => 'required',
            'transaction_type' => 'required|exists:transaction_types,transaction_type',
            'so_reason' => 'required',
            'mo_reason' => 'required',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ];
    }

    public function onFailure(Failure ...$failures) {
        $errors = [];
        Log::error("Reason import failed!");
        foreach ($failures as $failure) {
            $row = $failure->row();
            $message = json_encode($failure->errors());
            $attribute = $failure->attribute();
            Log::error("Failed at row# {$row} on column {$attribute} => {$message}");
            $errors[] = "Failed at row# {$row} => {$message}";
        }
    }
}
