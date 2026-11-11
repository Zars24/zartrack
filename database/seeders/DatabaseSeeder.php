<?php

namespace Database\Seeders;

use App\Models\Container;
use App\Models\Customer;
use App\Models\Driver;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Truck;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $customers = collect([
            ['name' => 'PT Nusantara Cargo Mandiri', 'contact_person' => 'Rina Wijaya', 'email' => 'ops@nusantaracargo.co.id', 'phone' => '021-5567788', 'city' => 'Jakarta Utara', 'province' => 'DKI Jakarta', 'npwp' => '01.234.567.8-901.000'],
            ['name' => 'PT Sinar Elektronik Jaya', 'contact_person' => 'Hendra Kusuma', 'email' => 'logistik@sinarelektronik.co.id', 'phone' => '021-4433221', 'city' => 'Bekasi', 'province' => 'Jawa Barat', 'npwp' => '02.345.678.9-012.000'],
            ['name' => 'CV Anugerah Tekstil', 'contact_person' => 'Siti Aminah', 'email' => 'purchasing@anugerahtekstil.id', 'phone' => '022-7788990', 'city' => 'Bandung', 'province' => 'Jawa Barat', 'npwp' => '03.456.789.0-123.000'],
        ])->map(fn ($data) => Customer::create($data + ['status' => 'active']));

        $admins = [
            ['Super Admin', 'super@zartrack.id', 'super_admin'],
            ['Andi Pratama', 'ops@zartrack.id', 'ops_admin'],
            ['Dewi Lestari', 'doc@zartrack.id', 'doc_admin'],
            ['Rudi Hartono', 'finance@zartrack.id', 'finance'],
        ];

        foreach ($admins as [$name, $email, $role]) {
            User::create([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'is_active' => true,
                'password' => Hash::make('password'),
            ]);
        }

        User::create([
            'name' => 'Rina Wijaya',
            'email' => 'customer@zartrack.id',
            'role' => 'customer',
            'customer_id' => $customers[0]->id,
            'is_active' => true,
            'password' => Hash::make('password'),
        ]);

        $driverUsers = [
            ['Budi Santoso', 'driver@zartrack.id', 'B 9021 KTR', 'trailer'],
            ['Joko Widodo', 'driver2@zartrack.id', 'B 8812 KTR', 'tronton'],
        ];

        $drivers = collect();
        $trucks = collect();

        foreach ($driverUsers as [$dname, $email, $plate, $type]) {
            $user = User::create([
                'name' => $dname,
                'email' => $email,
                'role' => 'driver',
                'is_active' => true,
                'password' => Hash::make('password'),
            ]);

            $drivers->push(Driver::create([
                'user_id' => $user->id,
                'name' => $dname,
                'phone' => '0812'.rand(10000000, 99999999),
                'license_number' => 'SIM'.rand(1000000, 9999999),
                'license_expiry' => now()->addYears(2),
                'status' => 'available',
            ]));

            $trucks->push(Truck::create([
                'plate_number' => $plate,
                'type' => $type,
                'brand' => 'Hino',
                'model' => '500 FM',
                'capacity_kg' => 20000,
                'status' => 'available',
            ]));
        }

        $routes = [
            ['Tanjung Priok', 'Cikarang', '20ft', 4200000, 'in_transit'],
            ['Tanjung Priok', 'Bandung', '40ft', 7800000, 'assigned'],
            ['Tanjung Perak', 'Sidoarjo', '20ft', 3900000, 'completed'],
            ['Belawan', 'Medan Industrial Park', '40hc', 5600000, 'pending'],
            ['Tanjung Priok', 'Tangerang', '20ft', 3500000, 'pickup'],
        ];

        foreach ($routes as $i => [$origin, $destination, $size, $price, $status]) {
            $customer = $customers[$i % $customers->count()];

            $order = Order::create([
                'customer_id' => $customer->id,
                'service_type' => 'trucking',
                'origin' => $origin,
                'destination' => $destination,
                'pickup_date' => now()->subDays(3)->addDays($i),
                'delivery_date' => now()->addDays($i + 1),
                'status' => $status,
                'total_price' => $price,
                'created_by' => 2,
            ]);

            $container = Container::create([
                'order_id' => $order->id,
                'container_number' => 'TCLU '.rand(100, 999).' '.rand(100, 999).' '.rand(0, 9),
                'size' => $size,
                'seal_number' => 'SL'.rand(100000, 999999),
                'weight' => rand(8000, 24000),
                'status' => 'pending',
                'driver_id' => $status === 'pending' ? null : $drivers[$i % 2]->id,
                'truck_id' => $status === 'pending' ? null : $trucks[$i % 2]->id,
            ]);

            $flow = [
                'pending' => ['pending'],
                'pickup' => ['pending', 'confirmed', 'assigned', 'pickup'],
                'assigned' => ['pending', 'confirmed', 'assigned'],
                'in_transit' => ['pending', 'confirmed', 'assigned', 'pickup', 'in_transit'],
                'completed' => ['pending', 'confirmed', 'assigned', 'pickup', 'in_transit', 'delivered', 'completed'],
            ];

            $time = now()->subDays(4);
            foreach ($flow[$status] as $state) {
                $container->timelines()->create([
                    'status' => $state,
                    'note' => 'Status diperbarui oleh operasional',
                    'user_id' => 2,
                    'created_at' => $time->copy()->addHours(3),
                ]);
                $time->addHours(3);
            }

            $container->update(['status' => $status]);

            if (in_array($status, ['in_transit', 'pickup'], true)) {
                foreach (range(1, 4) as $step) {
                    $container->locations()->create([
                        'driver_id' => $container->driver_id,
                        'lat' => -6.12 + ($step * 0.012),
                        'lng' => 106.88 - ($step * 0.02),
                        'speed' => rand(35, 70),
                        'recorded_at' => now()->subMinutes(30 - $step * 5),
                    ]);
                }
            }

            if (in_array($status, ['in_transit', 'completed', 'assigned'], true)) {
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-'.now()->format('ymd').'-'.str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT),
                    'order_id' => $order->id,
                    'issue_date' => now()->subDays(2),
                    'due_date' => now()->addDays(12),
                    'subtotal' => $price,
                    'tax' => $price * 0.11,
                    'total' => $price * 1.11,
                    'paid_amount' => 0,
                    'status' => 'unpaid',
                ]);

                if ($status === 'completed') {
                    $invoice->payments()->create([
                        'amount' => $invoice->total,
                        'method' => 'transfer',
                        'reference' => 'TRX'.rand(100000, 999999),
                        'paid_at' => now()->subDay(),
                        'recorded_by' => 4,
                    ]);
                }
            }
        }

        Quote::create([
            'name' => 'Wahyu Setiawan',
            'company' => 'PT Mitra Dagang Sejahtera',
            'email' => 'wahyu@mitradagang.co.id',
            'phone' => '081345678901',
            'service_type' => 'trucking',
            'origin' => 'Tanjung Priok',
            'destination' => 'Surabaya',
            'cargo_details' => '2 container 40ft, elektronik',
            'message' => 'Butuh penawaran untuk pengiriman rutin bulanan.',
        ]);
    }
}
