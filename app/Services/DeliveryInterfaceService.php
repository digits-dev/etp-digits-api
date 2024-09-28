<?php

namespace App\Services;

use App\Models\Delivery;

class DeliveryInterfaceService
{
    public function getProcessingDeliveryLines(){
        return Delivery::getProcessingLines()->get()->toArray();
    }

    public function getProcessingDelivery(){
        return Delivery::getProcessing()->get()->toArray();
    }

    public function getPendingDotrDeliveryLines(){
        return Delivery::getPendingDotrLines()->get()->toArray();
    }

    public function getPendingDotrDelivery(){
        return Delivery::getPendingDotr()->get()->toArray();
    }
}
