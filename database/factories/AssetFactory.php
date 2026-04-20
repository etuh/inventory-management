<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $deviceTypes = [
            'Laptop' => [
                'names' => ['MacBook Pro', 'ThinkPad T14', 'Dell XPS 15', 'HP EliteBook'],
                'has_network' => true,
            ],
            'Monitor' => [
                'names' => ['Dell UltraSharp 27', 'LG UltraGear', 'BenQ 4K', 'Samsung Odyssey'],
                'has_network' => false,
            ],
            'Phone' => [
                'names' => ['iPhone 14', 'Samsung Galaxy S23', 'Google Pixel 7', 'iPhone SE'],
                'has_network' => true,
            ],
            'Network Device' => [
                'names' => ['Cisco Switch', 'Ubiquiti Router', 'Netgear Access Point'],
                'has_network' => true,
            ],
            'Printer' => [
                'names' => ['HP LaserJet', 'Brother MFC', 'Epson EcoTank'],
                'has_network' => true,
            ],
        ];

        $deviceName = fake()->randomElement(array_keys($deviceTypes));
        $deviceInfo = $deviceTypes[$deviceName];
        $hasOS = $deviceInfo['has_network'];

        return [
            'inventory_id' => \App\Models\Inventory::factory(),
            'device_id' => \App\Models\Device::factory()->state([
                'name' => $deviceName,
                'include_operating_system' => $hasOS,
                'other_data_fields' => $hasOS ? [
                    ['name' => 'Warranty End Date', 'type' => 'date', 'options' => ''],
                    ['name' => 'Condition Notes', 'type' => 'text', 'options' => ''],
                ] : null,
            ]),
            'category_id' => null,
            'name' => fake()->randomElement($deviceInfo['names']),
            'serial_number' => fake()->unique()->bothify('SN-????-####'),
            'condition' => fake()->randomElement(['new', 'ok', 'outdated']),
            'status' => fake()->randomElement(['available', 'assigned', 'maintenance', 'retired']),

            'assigned_user_id' => \App\Models\User::factory(),
            'department' => fake()->randomElement(['IT', 'HR', 'Finance', 'Marketing', 'Operations', 'Sales']),
            'brand_model' => fake()->word() . ' ' . fake()->numerify('####'),
            'model_no' => fake()->bothify('??-####'),
            'operating_system' => $hasOS ? [
                'name' => fake()->randomElement(['Windows 11 Pro', 'Ubuntu 22.04 LTS', 'macOS Sonoma']),
                'version' => fake()->bothify('##H#'),
                'status' => 'Licensed',
                'partial_key' => fake()->bothify('????#')
            ] : null,
            'specifications' => [
                'Processor' => fake()->randomElement(['Intel Core i5', 'Intel Core i7', 'AMD Ryzen 5', 'Apple M2']),
                'RAM' => fake()->randomElement(['8GB', '16GB', '32GB']),
                'Storage' => fake()->randomElement(['256GB SSD', '512GB SSD', '1TB NVMe'])
            ],
            'other_data' => $hasOS ? [
                'Warranty End Date' => fake()->dateTimeBetween('now', '+3 years')->format('Y-m-d'),
                'Condition Notes' => fake()->sentence()
            ] : null,
            'purchase_date' => fake()->dateTimeBetween('-3 years', 'now')->format('Y-m-d'),
        ];
    }
}
