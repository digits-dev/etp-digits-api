<?php

namespace App\Imports;

use App\Models\CmsPrivilege;
use App\Models\CmsUser;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

class UserImport implements ToModel, WithHeadingRow, SkipsOnFailure, WithValidation
{
    use Importable, SkipsFailures;
    public function model(array $row) {
        // Map Excel row data to your User model
        $priv = Cache::remember("user_{$row['privilege']}", 3600, function() use ($row){
            return CmsPrivilege::where('name', $row['privilege'])->value('id');
        });

        $json = json_encode($row);
        Log::info("rows {$json}");

        return CmsUser::firstOrCreate(['name' => $row['name']],[
            'name' => $row['name'],
            'email' => $row['email'],
            'id_cms_privileges' => $priv,
            'password' => bcrypt('qwerty'),
            'status' => $row['status'],
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => 'required',
            'privilege' => 'required',
            'email' => 'required|email|unique:cms_users,email',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ];
    }

    public function onFailure(Failure ...$failures)
    {
        $errors = [];
        Log::error("User import failed!");
        foreach ($failures as $failure) {
            $row = $failure->row();
            $message = json_encode($failure->errors());
            $attribute = $failure->attribute();
            Log::error("Failed at row# {$row} on column {$attribute} => {$message}");
            $errors[] = "Failed at row# {$row} => {$message}";
        }
    }
}
