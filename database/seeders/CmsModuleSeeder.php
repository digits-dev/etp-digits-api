<?php

namespace Database\Seeders;

use App\Models\CmsModule;
use Illuminate\Database\Seeder;

class CmsModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $modules = [
            [
                'name'         => 'Reason',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'reasons',
                'table_name'   => 'reasons',
                'controller'   => 'AdminReasonsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Transaction Type',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'transaction_types',
                'table_name'   => 'transaction_types',
                'controller'   => 'AdminTransactionTypesController',
                'is_protected' => 0,
                'is_active'    => 0
            ]
        ];

        foreach ($modules as $module) {
            CmsModule::updateOrInsert(['name' => $module['name']], $module);
        }

        $this->command->info('Seeder finished seeding modules.');
    }
}
