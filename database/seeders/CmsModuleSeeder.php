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
                'name'         => 'Order Status',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'order_statuses',
                'table_name'   => 'order_statuses',
                'controller'   => 'AdminOrderStatusesController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
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
            ],
            [
                'name'         => 'Deliveries',
                'icon'         => 'fa fa-file-text-o',
                'path'         => 'deliveries',
                'table_name'   => 'deliveries',
                'controller'   => 'AdminDeliveriesController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Pullouts',
                'icon'         => 'fa fa-file-text',
                'path'         => 'pullouts',
                'table_name'   => 'pullouts',
                'controller'   => 'AdminPulloutsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Items',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'items',
                'table_name'   => 'items',
                'controller'   => 'AdminItemsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Stores',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'store_masters',
                'table_name'   => 'store_masters',
                'controller'   => 'AdminStoreMastersController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Channel',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'channels',
                'table_name'   => 'channels',
                'controller'   => 'AdminChannelsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],

        ];

        foreach ($modules as $module) {
            CmsModule::updateOrInsert(['name' => $module['name']], $module);
        }

        $this->command->info('Seeder finished seeding modules.');
    }
}
