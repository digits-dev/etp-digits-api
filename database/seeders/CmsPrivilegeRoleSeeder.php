<?php

namespace Database\Seeders;

use App\Models\CmsModule;
use App\Models\CmsPrivilegeRole;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsPrivilegeRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roles = [
            'is_visible' => 1,
            'is_create' => 1,
            'is_read' => 1,
            'is_edit' => 1,
            'is_delete' => 1
        ];

        $modules = CmsModule::where('is_active',0)->get();
        $privileges = DB::table('cms_privileges')->get();

        foreach ($privileges as $privilege) {
            $roles['id_cms_privileges'] = $privilege->id;

            foreach ($modules as $module) {
                $roles['id_cms_moduls'] = $module->id;

                CmsPrivilegeRole::updateOrInsert([
                    'id_cms_privileges' => $privilege->id,
                    'id_cms_moduls' => $module->id
                ], $roles);
            }
        }

        $this->command->info('Seeder finished seeding privilege roles.');
    }
}
