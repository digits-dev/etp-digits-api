<?php

namespace Database\Seeders;

use App\Models\CmsMenu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CmsMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $submaster = CmsMenu::where('name','Submaster')->value('id');
        $pulloutMenu = CmsMenu::where('name','Create Pullout')->value('id');
        $historyMenu = CmsMenu::where('name','History')->value('id');
        $approvalMenu = CmsMenu::where('name','Approvals')->value('id');

        $menus = [
            [
                'name'              => 'Submaster',
                'type'              => 'URL',
                'path'              => '#',
                'color'             => 'normal',
                'icon'              => 'fa fa-list',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 3
            ],
            [
                'name'              => 'Channel',
                'type'              => 'Route',
                'path'              => 'AdminChannelsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 2
            ],
            [
                'name'              => 'Items',
                'type'              => 'Route',
                'path'              => 'AdminItemsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 3
            ],
            [
                'name'              => 'Order Status',
                'type'              => 'Route',
                'path'              => 'AdminOrderStatusesControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 4
            ],
            [
                'name'              => 'Problems',
                'type'              => 'Route',
                'path'              => 'AdminProblemsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 5
            ],
            [
                'name'              => 'Reason',
                'type'              => 'Route',
                'path'              => 'AdminReasonsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 6
            ],
            [
                'name'              => 'Stores',
                'type'              => 'Route',
                'path'              => 'AdminStoreMastersControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 7
            ],
            [
                'name'              => 'Transaction Type',
                'type'              => 'Route',
                'path'              => 'AdminTransactionTypesControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 8
            ],
            [
                'name'              => 'Transport Type',
                'type'              => 'Route',
                'path'              => 'AdminTransportTypesControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 9
            ],
            [
                'name'              => 'Deliveries',
                'type'              => 'Route',
                'path'              => 'AdminDeliveriesControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text-o',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
            ],
            [
                'name'              => 'Pullouts',
                'type'              => 'Route',
                'path'              => 'AdminPulloutsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 2
            ],
            [
                'name'              => 'Create STS',
                'type'              => 'Route',
                'path'              => 'AdminStoreTransfersControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text',
                'parent_id'         => $pulloutMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
            ],
            [
                'name'              => 'Create STW/STR',
                'type'              => 'Route',
                'path'              => 'AdminStorePulloutsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text',
                'parent_id'         => $pulloutMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 2
            ],
            [
                'name'              => 'Create Pullout',
                'type'              => 'URL',
                'path'              => '#',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 3
            ],
            [
                'name'              => 'History',
                'type'              => 'URL',
                'path'              => '#',
                'color'             => 'normal',
                'icon'              => 'fa fa-history',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 7
            ],
            [
                'name'              => 'STS History',
                'type'              => 'Route',
                'path'              => 'AdminStsHistoryControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text-o',
                'parent_id'         => $historyMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
            ],
            [
                'name'              => 'STW/STR History',
                'type'              => 'Route',
                'path'              => 'AdminPulloutHistoryControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text-o',
                'parent_id'         => $historyMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 2
            ],
            [
                'name'              => 'Approvals',
                'type'              => 'URL',
                'path'              => '#',
                'color'             => 'normal',
                'icon'              => 'fa fa-thumbs-up',
                'parent_id'         => 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 4
            ],
            [
                'name'              => 'STW Approval',
                'type'              => 'Route',
                'path'              => 'AdminStwApprovalControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-thumbs-up',
                'parent_id'         => $approvalMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 3
            ],
            [
                'name'              => 'STR Approval',
                'type'              => 'Route',
                'path'              => 'AdminStrApprovalControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-thumbs-up',
                'parent_id'         => $approvalMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 2
            ],
            [
                'name'              => 'STS Approval',
                'type'              => 'Route',
                'path'              => 'AdminStsApprovalControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-thumbs-up',
                'parent_id'         => $approvalMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
            ],
            [
                'name'              => 'STS Confirmation',
                'type'              => 'Route',
                'path'              => 'AdminStsConfirmationControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-file-text',
                'parent_id'         => $pulloutMenu ?? 0,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 3
            ],

            [
                'name'              => 'Approval Matrix Settings',
                'type'              => 'Route',
                'path'              => 'AdminApprovalMatrixControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
            ],
            [
                'name'              => 'Transfer Groupings',
                'type'              => 'Route',
                'path'              => 'AdminTransferGroupsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 10
            ],

        ];

        foreach ($menus as $menu) {
            CmsMenu::updateOrCreate(['name' => $menu['name']], $menu);
        }

        $this->command->info('Seeder finished seeding menus.');
    }
}
