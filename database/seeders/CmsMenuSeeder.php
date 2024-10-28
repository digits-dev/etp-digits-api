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
                'sorting'           => 1
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
                'sorting'           => 2
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
                'sorting'           => 3
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
                'sorting'           => 4
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
                'sorting'           => 5
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
                'sorting'           => 6
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
                'sorting'           => 7
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
        ];

        foreach ($menus as $menu) {
            CmsMenu::updateOrCreate(['name' => $menu['name']], $menu);
        }

        $this->command->info('Seeder finished seeding menus.');
    }
}
