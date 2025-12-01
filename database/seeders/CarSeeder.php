<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Car;
use App\Models\Dealer;
use App\Models\CarMedia;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $dealers = Dealer::all();

        $cars = [
            [
                'dealer_id' => $dealers[0]->id,
                'title' => '2020 Toyota Camry SE',
                'brand' => 'Toyota',
                'model' => 'Camry',
                'year' => 2020,
                'price' => 24500,
                'mileage' => 35000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Silver',
                'description' => 'Well-maintained Toyota Camry with low mileage. One owner, clean title.',
                'is_featured' => true,
            ],
            [
                'dealer_id' => $dealers[1]->id,
                'title' => '2021 BMW 3 Series',
                'brand' => 'BMW',
                'model' => '3 Series',
                'year' => 2021,
                'price' => 42000,
                'mileage' => 18000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Black',
                'description' => 'Luxury sedan with premium features. Excellent condition.',
                'is_featured' => true,
            ],
            [
                'dealer_id' => $dealers[0]->id,
                'title' => '2019 Honda Accord Sport',
                'brand' => 'Honda',
                'model' => 'Accord',
                'year' => 2019,
                'price' => 22000,
                'mileage' => 42000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'White',
                'description' => 'Sporty and reliable Honda Accord. Great fuel economy.',
            ],
            [
                'dealer_id' => $dealers[2]->id,
                'title' => '2018 Ford Focus SE',
                'brand' => 'Ford',
                'model' => 'Focus',
                'year' => 2018,
                'price' => 14500,
                'mileage' => 55000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Blue',
                'description' => 'Affordable compact car, perfect for city driving.',
            ],
            [
                'dealer_id' => $dealers[1]->id,
                'title' => '2022 Mercedes-Benz C-Class',
                'brand' => 'Mercedes-Benz',
                'model' => 'C-Class',
                'year' => 2022,
                'price' => 48000,
                'mileage' => 12000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Gray',
                'description' => 'Premium luxury sedan with advanced technology.',
                'is_featured' => true,
            ],
            [
                'dealer_id' => $dealers[0]->id,
                'title' => '2020 Mazda CX-5 Grand Touring',
                'brand' => 'Mazda',
                'model' => 'CX-5',
                'year' => 2020,
                'price' => 28000,
                'mileage' => 30000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Red',
                'description' => 'Stylish SUV with great handling and features.',
            ],
            [
                'dealer_id' => $dealers[2]->id,
                'title' => '2017 Chevrolet Malibu LT',
                'brand' => 'Chevrolet',
                'model' => 'Malibu',
                'year' => 2017,
                'price' => 16000,
                'mileage' => 60000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'Black',
                'description' => 'Comfortable midsize sedan with modern features.',
            ],
            [
                'dealer_id' => $dealers[1]->id,
                'title' => '2021 Audi A4 Premium',
                'brand' => 'Audi',
                'model' => 'A4',
                'year' => 2021,
                'price' => 39000,
                'mileage' => 20000,
                'condition' => 'used',
                'transmission' => 'Automatic',
                'fuel_type' => 'Gasoline',
                'color' => 'White',
                'description' => 'Sophisticated luxury sedan with quattro all-wheel drive.',
            ],
        ];

        foreach ($cars as $carData) {
            $car = Car::create($carData);

            // Add stock images based on car model
            $images = $this->getImagesForCar($carData['brand'], $carData['model']);

            foreach ($images as $imageUrl) {
                CarMedia::create([
                    'car_id' => $car->id,
                    'file_path' => $imageUrl,
                    'file_type' => 'image',
                    'is_primary' => $imageUrl === $images[0],
                ]);
            }
        }
    }

    private function getImagesForCar(string $brand, string $model): array
    {
        // Realistic Unsplash images for specific car types
        $images = [
            'Toyota' => [
                'https://images.unsplash.com/photo-1621007947382-bb3c3968e3bb?auto=format&fit=crop&w=800&q=80', // Camry
                'https://images.unsplash.com/photo-1590362891991-f776e747a588?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1489824904134-891ab64532f1?auto=format&fit=crop&w=800&q=80', // Dashboard
                'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?auto=format&fit=crop&w=800&q=80', // Side View
            ],
            'BMW' => [
                'https://images.unsplash.com/photo-1555215695-3004980adade?auto=format&fit=crop&w=800&q=80', // 3 Series
                'https://images.unsplash.com/photo-1556189250-72ba954e606d?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1607853202273-797f1c22a38e?auto=format&fit=crop&w=800&q=80', // Wheel
                'https://images.unsplash.com/photo-1556800572-1b8aeef2c54f?auto=format&fit=crop&w=800&q=80', // Rear
            ],
            'Honda' => [
                'https://images.unsplash.com/photo-1592750475338-74b7b21085ab?auto=format&fit=crop&w=800&q=80', // Accord
                'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?auto=format&fit=crop&w=800&q=80', // Front
                'https://images.unsplash.com/photo-1599912027806-cfec9f5944b6?auto=format&fit=crop&w=800&q=80', // Detail
            ],
            'Ford' => [
                'https://images.unsplash.com/photo-1551830820-330a71b99659?auto=format&fit=crop&w=800&q=80', // Focus
                'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=800&q=80', // Action
                'https://images.unsplash.com/photo-1469285994282-454ceb49e63c?auto=format&fit=crop&w=800&q=80', // Sunset
            ],
            'Mercedes-Benz' => [
                'https://images.unsplash.com/photo-1617788138017-80ad40651399?auto=format&fit=crop&w=800&q=80', // C-Class
                'https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1605559424843-9e4c228bf1c2?auto=format&fit=crop&w=800&q=80', // Front Grille
                'https://images.unsplash.com/photo-1563720223185-11003d516935?auto=format&fit=crop&w=800&q=80', // Side
            ],
            'Mazda' => [
                'https://images.unsplash.com/photo-1575650466523-229c21226568?auto=format&fit=crop&w=800&q=80', // CX-5
                'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1502877338535-766e1452684a?auto=format&fit=crop&w=800&q=80', // Road
                'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', // Detail
            ],
            'Chevrolet' => [
                'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=800&q=80', // Malibu
                'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=800&q=80', // Classic
                'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=800&q=80', // Speed
            ],
            'Audi' => [
                'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?auto=format&fit=crop&w=800&q=80', // A4
                'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=800&q=80', // Interior
                'https://images.unsplash.com/photo-1603584173870-7f23fdae1b7a?auto=format&fit=crop&w=800&q=80', // Front
                'https://images.unsplash.com/photo-1503376763036-066120622c74?auto=format&fit=crop&w=800&q=80', // Dark
            ],
        ];

        return $images[$brand] ?? [
            'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=800&q=80', // Generic Car
            'https://images.unsplash.com/photo-1542282088-fe8426682b8f?auto=format&fit=crop&w=800&q=80', // Generic Interior
            'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=800&q=80', // Generic 3
            'https://images.unsplash.com/photo-1489824904134-891ab64532f1?auto=format&fit=crop&w=800&q=80', // Generic 4
        ];
    }
}
