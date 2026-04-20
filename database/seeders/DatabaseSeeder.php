<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Inventory;
use App\Models\Device;
use App\Models\Asset;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'IT Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        $itDept = Inventory::create(['name' => 'Information Technology']);

        $laptopDev = Device::create([
            'inventory_id' => $itDept->id,
            'name' => 'Laptop',
            'specification_fields' => [
                ['name' => 'RAM', 'type' => 'select', 'options' => '8GB, 16GB, 32GB, 64GB'],
                ['name' => 'Processor', 'type' => 'text'],
                ['name' => 'Storage Size', 'type' => 'text']
            ],
            'include_operating_system' => true,
            'other_data_fields' => [
                ['name' => 'Warranty End Date', 'type' => 'date'],
                ['name' => 'Condition Notes', 'type' => 'text']
            ]
        ]);

        $monitorDev = Device::create([
            'inventory_id' => $itDept->id,
            'name' => 'Monitor',
            'specification_fields' => [
                ['name' => 'Screen Size', 'type' => 'text'],
                ['name' => 'Resolution', 'type' => 'select', 'options' => '1080p, 1440p, 4K'],
                ['name' => 'Has Speakers', 'type' => 'select', 'options' => 'Yes, No']
            ],
            'include_operating_system' => false,
            'other_data_fields' => null
        ]);

        // Seed some fake Laptops
        for($i = 1; $i <= 5; $i++) {
            Asset::create([
                'inventory_id' => $itDept->id,
                'device_id' => $laptopDev->id,
                'name' => "Developer Mac " . $i,
                'serial_number' => "MAC-00" . $i,
                'specifications' => [
                    'RAM' => '32GB',
                    'Processor' => 'M2 Pro',
                    'Storage Size' => '1TB SSD'
                ]
            ]);
        }

        // Seed some fake Monitors
        for($i = 1; $i <= 3; $i++) {
            Asset::create([
                'inventory_id' => $itDept->id,
                'device_id' => $monitorDev->id,
                'name' => "Dell UltraSharp " . $i,
                'serial_number' => "DIS-00" . $i,
                'specifications' => [
                    'Screen Size' => '27 inch',
                    'Resolution' => '4K',
                    'Has Speakers' => 'No'
                ]
            ]);
        }
    }
}
