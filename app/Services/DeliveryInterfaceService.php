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

    public function getProcessingDotrDeliveryLines(){
        return Delivery::getProcessingDotrLines()->get()->toArray();
    }

    public function getProcessingDotrDelivery(){
        return Delivery::getProcessingDotr()->get()->toArray();
    }

    public function getProcessingSitLines(){
        return Delivery::getProcessingSitLines()->get()->toArray();
    }

    public function getProcessingSit(){
        return Delivery::getProcessingSit()->get()->toArray();
    }
}
