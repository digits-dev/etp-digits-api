<?php

namespace Database\Seeders;

use App\Models\CmsMenu;
use Illuminate\Database\Seeder;

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
                'name'              => 'Reason',
                'type'              => 'Route',
                'path'              => 'AdminReasonsControllerGetIndex',
                'color'             => 'normal',
                'icon'              => 'fa fa-circle-o',
                'parent_id'         => $submaster,
                'is_active'         => 1,
                'is_dashboard'      => 0,
                'id_cms_privileges' => 1,
                'sorting'           => 1
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
                'sorting'           => 2
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
        ];

        foreach ($menus as $menu) {
            CmsMenu::updateOrInsert(['name' => $menu['name']], $menu);
        }

        $this->command->info('Seeder finished seeding menus.');
    }
}