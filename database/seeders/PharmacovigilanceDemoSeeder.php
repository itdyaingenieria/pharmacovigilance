<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Medication;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class PharmacovigilanceDemoSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::query()->updateOrCreate(
            ['username' => 'pv_admin'],
            [
                'email' => 'pv_admin@example.com',
                'full_name' => 'Pharmacovigilance Admin',
                'password' => Hash::make('123456'),
                'user_status_id' => 1,
            ]
        );

        if (!$adminUser->hasRole('admin')) {
            $adminUser->assignRole('admin');
        }

        $customers = collect([
            ['name' => 'Diego Yama Andrade', 'email' => 'diegoyamaa@gmail.com', 'phone' => '+573006662511'],
            ['name' => 'Juan Mora', 'email' => 'juan.mora@dfya.com', 'phone' => '+57922222222'],
            ['name' => 'Camila Mora', 'email' => 'camila.mora@dfya.com', 'phone' => '+57933333333'],
            ['name' => 'Daniel Mora', 'email' => 'daniel.mora@dfya .com', 'phone' => '+57944444444'],
        ])->map(fn(array $payload) => Customer::query()->updateOrCreate(['email' => $payload['email']], $payload));

        $medications = [
            Medication::query()->updateOrCreate(
                ['name' => 'Ibuprofeno 400mg', 'lot_number' => '951357'],
                ['description' => 'Non-steroidal anti-inflammatory medication']
            ),
            Medication::query()->updateOrCreate(
                ['name' => 'Paracetamol 500mg', 'lot_number' => '882110'],
                ['description' => 'Analgesic and antipyretic medication']
            ),
            Medication::query()->updateOrCreate(
                ['name' => 'Amoxicilina 500mg', 'lot_number' => '951357'],
                ['description' => 'Broad-spectrum antibiotic']
            ),
            Medication::query()->updateOrCreate(
                ['name' => 'Loratadina 10mg', 'lot_number' => '665412'],
                ['description' => 'Antihistamine medication']
            ),
        ];

        $recentDates = [
            Carbon::now()->subDays(2),
            Carbon::now()->subDays(5),
            Carbon::now()->subDays(12),
            Carbon::now()->subDays(21),
            Carbon::now()->subDays(35),
        ];

        foreach ($customers as $index => $customer) {
            $order = Order::query()->create([
                'customer_id' => $customer->id,
                'purchase_date' => $recentDates[$index] ?? Carbon::now()->subDays(10),
            ]);

            $primaryMedication = $medications[$index % count($medications)];
            OrderItem::query()->create([
                'order_id' => $order->id,
                'medication_id' => $primaryMedication->id,
                'quantity' => 1,
            ]);

            // Ensure several orders include the required lot 951357.
            if ($index < 3 && $primaryMedication->lot_number !== '951357') {
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'medication_id' => $medications[0]->id,
                    'quantity' => 2,
                ]);
            }
        }
    }
}
