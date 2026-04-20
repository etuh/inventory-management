<?php

namespace Database\Factories;

use App\Models\Device;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Device>
 */
class DeviceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasOS = fake()->boolean();

        return [
            'inventory_id' => \App\Models\Inventory::factory(),
            'name' => fake()->unique()->word() . ' device',
            'specification_fields' => [
                ['name' => 'Processor', 'type' => 'text', 'options' => ''],
                ['name' => 'RAM', 'type' => 'select', 'options' => '4GB, 8GB, 16GB, 32GB, 64GB'],
                ['name' => 'Storage', 'type' => 'text', 'options' => ''],
            ],
            'include_operating_system' => $hasOS,
            'other_data_fields' => $hasOS ? [
                ['name' => 'Warranty End Date', 'type' => 'date', 'options' => ''],
                ['name' => 'Bios Date', 'type' => 'date', 'options' => ''],
                ['name' => 'Manufacturing Date', 'type' => 'date', 'options' => ''],
            ] : null,
        ];
    }
}
