<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Dealer;

class DealerSeeder extends Seeder
{
    public function run(): void
    {
        // Create Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@automarket.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        // Create Dealer 1
        $dealer1User = User::create([
            'name' => 'John Smith',
            'email' => 'john@speedmotors.com',
            'password' => bcrypt('password'),
            'role' => 'dealer',
        ]);

        Dealer::create([
            'user_id' => $dealer1User->id,
            'shop_name' => 'Speed Motors',
            'description' => 'Premium used cars with warranty',
            'address' => '123 Main St, New York, NY 10001',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'phone' => '+1-555-0101',
            'is_verified' => true,
        ]);

        // Create Dealer 2
        $dealer2User = User::create([
            'name' => 'Sarah Johnson',
            'email' => 'sarah@luxuryautos.com',
            'password' => bcrypt('password'),
            'role' => 'dealer',
        ]);

        Dealer::create([
            'user_id' => $dealer2User->id,
            'shop_name' => 'Luxury Autos',
            'description' => 'High-end luxury vehicles',
            'address' => '456 Park Ave, New York, NY 10022',
            'latitude' => 40.7614,
            'longitude' => -73.9776,
            'phone' => '+1-555-0102',
            'is_verified' => true,
        ]);

        // Create Dealer 3
        $dealer3User = User::create([
            'name' => 'Mike Davis',
            'email' => 'mike@budgetcars.com',
            'password' => bcrypt('password'),
            'role' => 'dealer',
        ]);

        Dealer::create([
            'user_id' => $dealer3User->id,
            'shop_name' => 'Budget Cars',
            'description' => 'Affordable reliable cars',
            'address' => '789 Broadway, New York, NY 10003',
            'latitude' => 40.7282,
            'longitude' => -73.9942,
            'phone' => '+1-555-0103',
            'is_verified' => true,
        ]);

        // Create a Customer
        User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);
    }
}
