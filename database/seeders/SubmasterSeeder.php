<?php

namespace Database\Seeders;

use App\Models\OrderStatus;
use App\Models\TransactionType;
use App\Models\TransportType;
use App\Models\Problem;
use Illuminate\Database\Seeder;

class SubmasterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $transportTypes = [
            'LOGISTICS'=>'<span class="label label-primary">LOGISTICS</span>',
            'HAND CARRY'=>'<span class="label label-info">HAND</span>',
        ];

        $transactionTypes = [
            'STW',
            'STR',
            'STS'
        ];

        $problems = [
            'AESTHETICS - DISCOLORATION',
            'AESTHETICS - MISSING PARTS / COMPONENTS',
            'AESTHETICS - SCRATCHES / DENTS',
            'AESTHETICS - WEAK ADHESIVE',
            'BATTERY - BLOATED',
            'BATTERY - EASILY DRAINED',
            'BATTERY - NOT CHARGING',
            'BATTERY - OVERHEATING',
            'CAMERA - FOCUS ISSUES',
            'CONNECTIVITY - SIGNAL',
            'CONNECTIVITY - SYNC',
            'CONNECTIVITY - WIFI',
            'DISPLAY - CRACKED SCREEN',
            'DISPLAY - FLICKERING SCREEN',
            'DISPLAY - TOUCH MALFUNCTIONING',
            'INPUT - BUTTON MALFUNCTIONING',
            'MICROPHONE - NO SOUND INPUT',
            'OTHERS',
            'POWER - INTERMITTENT CHARGING',
            'POWER - NOT CHARGING',
            'POWER - NOT OPENING',
            'SCREEN - DEAD PIXELS',
            'SOFTWARE - HANGING',
            'SOFTWARE - VIRUS',
            'SOUND - DISTORTED/STATIC',
            'SOUND - NO VOLUME'
        ];

        $orderStatus = [
            'PENDING'=>'<span class="label label-warning">PENDING</span>',
            'APPROVED'=>'<span class="label label-primary">APPROVED</span>',
            'PROCESSING'=>'<span class="label label-info">PROCESSING</span>',
            'RECEIVED'=>'<span class="label label-success">RECEIVED</span>',
            'REJECTED'=>'<span class="label label-danger">REJECTED</span',
            'FOR RECEIVING'=>'<span class="label label-primary">FOR RECEIVING</span>',
            'FOR SCHEDULE'=>'<span class="label label-warning">FOR SCHEDULE</span>',
            'CLOSED'=>'<span class="label label-danger">CLOSED</span>',
            'VOID'=>'<span class="label label-danger">VOID</span>',
            'FOR CONFIRMATION'=>'<span class="label label-warning">FOR CONFIRMATION</span>',
            'FOR APPROVAL'=>'<span class="label label-warning">FOR APPROVAL</span>',
            'CREATE IN POS'=>'<span class="label label-primary">CREATE IN POS</span>',
            'CONFIRMED'=>'<span class="label label-primary">CONFIRMED</span>',
            'PROCESSING-DOTR'=>'<span class="label label-info">PROCESSING-DOTR</span>'
        ];

        $createdBy = [
            'status' => 'ACTIVE',
            'created_by' => 1,
            'created_at' => now()
        ];

        foreach ($transportTypes as $key => $value) {
            $newTransportValue['transport_type'] = $key;
            $newTransportValue['style'] = $value;
            TransportType::updateOrInsert(['transport_type' => $key],
                array_merge($createdBy,$newTransportValue));
        }

        foreach ($transactionTypes as $value) {
            $newTransactionValue['transaction_type'] = $value;
            TransactionType::updateOrInsert(['transaction_type' => $value],
                array_merge($createdBy,$newTransactionValue));
        }

        foreach ($problems as $value) {
            $newProblemValue['problem_details'] = $value;
            Problem::updateOrInsert(['problem_details' => $value],
                array_merge($createdBy,$newProblemValue));
        }

        foreach ($orderStatus as $key => $value) {
            $newOrderStatusValue['order_status'] = $key;
            $newOrderStatusValue['style'] = $value;
            OrderStatus::updateOrInsert(['order_status' => $key],
                array_merge($createdBy,$newOrderStatusValue));
        }
    }
}
