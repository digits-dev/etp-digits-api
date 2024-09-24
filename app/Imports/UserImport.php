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
        $priv = Cache::remember($row['privilege'], 3600, function() use ($row){
            return CmsPrivilege::where('name', $row['privilege'])->value('id');
        });

        Log::info("rows {$row}");

        return new CmsUser([
            'name' => $row['name'],
            'email' => $row['email'],
            'id_cms_privileges' => $priv,
            'status' => $row['status'],
        ]);
    }

    // Define the validation rules for the import
    public function rules(): array
    {
        return [
            'name' => 'required',
            'privilege' => 'required',
            'email' => 'required|email|unique:cms_users,email', // Validating the email
            'status' => 'required|in:ACTIVE,INACTIVE'
        ];
    }

    // Optionally, customize failure handling
    public function onFailure(Failure ...$failures)
    {
        // Handle failures (log them, notify, etc.)
        foreach ($failures as $failure) {
            // Log the failure (row number and error message)
            Log::error('Failed row: ' . $failure->row());
            Log::error('Error: ' . json_encode($failure->errors()));
        }
    }
}
