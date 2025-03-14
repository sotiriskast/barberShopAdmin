<?php


namespace App\Modules\Shop\Database\Seeders;

use App\Modules\Shop\Models\Shop;
use App\Modules\User\Models\User;
use Illuminate\Database\Seeder;

class ShopSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create shop owners if they don't exist
        $shopOwners = User::where('role', 'shop_owner')->take(3)->get();

        if ($shopOwners->isEmpty()) {
            $shopOwners = [];

            for ($i = 1; $i <= 3; $i++) {
                $shopOwners[] = User::create([
                    'name' => "Shop Owner $i",
                    'email' => "shop_owner{$i}@example.com",
                    'password' => bcrypt('password'),
                    'role' => 'shop_owner',
                ]);
            }
        }

        // Sample shops data
        $shopsData = [
            [
                'name' => 'Classic Cuts Barbershop',
                'description' => 'A traditional barbershop with a modern twist. We specialize in classic cuts, hot towel shaves, and beard trims.',
                'address' => '123 Main St',
                'city' => 'New York',
                'state' => 'NY',
                'postal_code' => '10001',
                'country' => 'USA',
                'latitude' => 40.7128,
                'longitude' => -74.0060,
                'phone' => '212-555-1234',
                'email' => 'info@classiccuts.com',
                'website' => 'https://classiccuts.com',
                'is_active' => true,
                'owner_id' => $shopOwners[0]->id ?? 1,
            ],
            [
                'name' => 'Modern Styles',
                'description' => 'Urban barbershop offering trendy cuts, fades, and styling for all hair types.',
                'address' => '456 Market Ave',
                'city' => 'Los Angeles',
                'state' => 'CA',
                'postal_code' => '90007',
                'country' => 'USA',
                'latitude' => 34.0522,
                'longitude' => -118.2437,
                'phone' => '323-555-5678',
                'email' => 'info@modernstyles.com',
                'website' => 'https://modernstyles.com',
                'is_active' => true,
                'owner_id' => $shopOwners[1]->id ?? 2,
            ],
            [
                'name' => 'Gentleman\'s Quarters',
                'description' => 'Premium barbershop and grooming lounge for the modern gentleman.',
                'address' => '789 Oak Blvd',
                'city' => 'Chicago',
                'state' => 'IL',
                'postal_code' => '60607',
                'country' => 'USA',
                'latitude' => 41.8781,
                'longitude' => -87.6298,
                'phone' => '312-555-9012',
                'email' => 'info@gentlemansquarters.com',
                'website' => 'https://gentlemansquarters.com',
                'is_active' => true,
                'owner_id' => $shopOwners[2]->id ?? 3,
            ],
            [
                'name' => 'The Barber\'s Den',
                'description' => 'Family-friendly barbershop offering quality haircuts at affordable prices.',
                'address' => '321 Pine St',
                'city' => 'Seattle',
                'state' => 'WA',
                'postal_code' => '98101',
                'country' => 'USA',
                'latitude' => 47.6062,
                'longitude' => -122.3321,
                'phone' => '206-555-3456',
                'email' => 'info@barbersden.com',
                'website' => 'https://barbersden.com',
                'is_active' => true,
                'owner_id' => $shopOwners[0]->id ?? 1,
            ],
            [
                'name' => 'Precision Cuts',
                'description' => 'High-end barbershop specializing in precision haircuts and styling.',
                'address' => '654 Elm St',
                'city' => 'Miami',
                'state' => 'FL',
                'postal_code' => '33101',
                'country' => 'USA',
                'latitude' => 25.7617,
                'longitude' => -80.1918,
                'phone' => '305-555-7890',
                'email' => 'info@precisioncuts.com',
                'website' => 'https://precisioncuts.com',
                'is_active' => true,
                'owner_id' => $shopOwners[1]->id ?? 2,
            ],
        ];

        // Create shops
        foreach ($shopsData as $shopData) {
            Shop::updateOrCreate(
                [
                    'name' => $shopData['name'],
                    'owner_id' => $shopData['owner_id'],
                ],
                $shopData
            );
        }
    }
}
