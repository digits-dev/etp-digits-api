<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MenuCreated
{
    use Dispatchable, SerializesModels;

    public $menu;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($menu)
    {
        $this->menu = $menu;
    }
}
