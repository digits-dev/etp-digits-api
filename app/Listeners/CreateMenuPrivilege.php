<?php

namespace App\Listeners;

use App\Events\MenuCreated;
use App\Models\CmsMenuPrivilege;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CreateMenuPrivilege
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  \App\Events\MenuCreated  $event
     * @return void
     */
    public function handle(MenuCreated $event)
    {
        // create a access privilege for the created menu
        CmsMenuPrivilege::create([
            'id_cms_menus' => $event->menu->id,
            'id_cms_privileges' => 1, //for superadmin only
        ]);
    }
}
