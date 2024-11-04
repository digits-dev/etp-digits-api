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
            [
                'name'         => 'Create STS',
                'icon'         => 'fa fa-file-text',
                'path'         => 'store_transfers',
                'table_name'   => 'store_transfers',
                'controller'   => 'AdminStoreTransfersController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Create STW/STR',
                'icon'         => 'fa fa-file-text',
                'path'         => 'store_pullouts',
                'table_name'   => 'store_pullouts',
                'controller'   => 'AdminStorePulloutsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Transport Type',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'transport_types',
                'table_name'   => 'transport_types',
                'controller'   => 'AdminTransportTypesController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Problems',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'problems',
                'table_name'   => 'problems',
                'controller'   => 'AdminProblemsController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STS History',
                'icon'         => 'fa fa-file-text-o',
                'path'         => 'sts_history',
                'table_name'   => 'sts_history',
                'controller'   => 'AdminStsHistoryController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STW/STR History',
                'icon'         => 'fa fa-file-text-o',
                'path'         => 'pullout_history',
                'table_name'   => 'store_pullouts',
                'controller'   => 'AdminPulloutHistoryController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Approval Matrix Settings',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'approval_matrix',
                'table_name'   => 'approval_matrix',
                'controller'   => 'AdminApprovalMatrixController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STS Approval',
                'icon'         => 'fa fa-thumbs-o-up',
                'path'         => 'sts_approval',
                'table_name'   => 'store_transfers',
                'controller'   => 'AdminStsApprovalController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STW Approval',
                'icon'         => 'fa fa-thumbs-o-up',
                'path'         => 'stw_approval',
                'table_name'   => 'store_pullouts',
                'controller'   => 'AdminStwApprovalController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STR Approval',
                'icon'         => 'fa fa-thumbs-o-up',
                'path'         => 'str_approval',
                'table_name'   => 'store_pullouts',
                'controller'   => 'AdminStrApprovalController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'STS Confirmation',
                'icon'         => 'fa fa-file-text',
                'path'         => 'sts_confirmation',
                'table_name'   => 'store_transfers',
                'controller'   => 'AdminStsConfirmationController',
                'is_protected' => 0,
                'is_active'    => 0
            ],
            [
                'name'         => 'Transfer Groupings',
                'icon'         => 'fa fa-circle-o',
                'path'         => 'transfer_groups',
                'table_name'   => 'transfer_groups',
                'controller'   => 'AdminTransferGroupsController',
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
