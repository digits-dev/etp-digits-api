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
            [
                'transport_type' => 'LOGISTICS',
                'status' => 'ACTIVE',
                'created_by' => 0
            ],[
                'transport_type' => 'HAND CARRY',
                'status' => 'ACTIVE',
                'created_by' => 0
            ]
        ];

        $transactionTypes = [
            [
                'transaction_type' => 'STW',
                'status' => 'ACTIVE',
                'created_by' => 0
            ],
            [
                'transaction_type' => 'STR',
                'status' => 'ACTIVE',
                'created_by' => 0
            ],
            [
                'transaction_type' => 'STS',
                'status' => 'ACTIVE',
                'created_by' => 0
            ]
        ];

        foreach ($transportTypes as $value) {
            TransportType::updateOrInsert(['transport_type' => $value['transport_type']], $value);
        }

        foreach ($transactionTypes as $value) {
            TransactionType::updateOrInsert(['transaction_type' => $value['transaction_type']], $value);
        }
    }
}
