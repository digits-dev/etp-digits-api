<?php

namespace App\Imports;

use App\Models\Channel;
use App\Models\CmsPrivilege;
use App\Models\CmsUser;
use App\Models\StoreMaster;
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

        $priv = Cache::remember("user_{$row['privilege']}", 3600, function() use ($row){
            return CmsPrivilege::where('name', $row['privilege'])->value('id');
        });

        $channel = Cache::remember("channel_{$row['channel']}", 3600, function() use ($row){
            return Channel::where('channel_description', $row['channel'])->value('id');
        });

        $store = Cache::remember("store_{$row['store']}", 3600, function() use ($row){
            return StoreMaster::where('bea_so_store_name', $row['store'])->value('id');
        });

        return CmsUser::updateOrCreate(['email' => $row['email']],[
            'name' => $row['name'],
            'email' => $row['email'],
            'id_cms_privileges' => $priv,
            'channels_id' => $channel,
            'store_masters_id' => $store,
            'password' => bcrypt('qwerty'),
            'status' => $row['status'],
        ]);
    }

    public function rules(): array {
        return [
            'name' => 'required',
            'privilege' => 'required|exists:cms_privileges,name',
            'channel' => 'required|exists:channels,channel_description',
            'store' => 'required|exists:store_masters,bea_so_store_name',
            'email' => 'required|email|unique:cms_users,email',
            'status' => 'required|in:ACTIVE,INACTIVE'
        ];
    }

    public function onFailure(Failure ...$failures) {
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
