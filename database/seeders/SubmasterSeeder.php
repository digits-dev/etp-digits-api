<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use App\Models\TransportType;
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
            'LOGISTICS',
            'HAND CARRY'
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

        $createdBy = [
            'status' => 'ACTIVE',
            'created_by' => 0
        ];

        foreach ($transportTypes as $value) {
            $newValue['transport_type'] = $value;
            TransportType::updateOrInsert(['transport_type' => $value],
                array_merge($createdBy,$newValue));
        }

        foreach ($transactionTypes as $value) {
            $newValue['transaction_type'] = $value;
            TransactionType::updateOrInsert(['transaction_type' => $value],
                array_merge($createdBy,$newValue));
        }

        foreach ($problems as $value) {
            $newValue['problem_details'] = $value;
            TransactionType::updateOrInsert(['problem_details' => $value],
                array_merge($createdBy,$newValue));
        }
    }
}
