<?php

namespace Database\Seeders;

use App\Enums\ListingType;
use App\Enums\PropertyStatus;
use App\Enums\PropertyType;
use App\Enums\RentPeriod;
use App\Models\Property;
use Illuminate\Database\Seeder;

class PropertySeeder extends Seeder
{
    /**
     * Seed a set of demo listings that use remote placeholder photography (skips listings that already exist).
     */
    public function run(): void
    {
        $photo = fn (string $id): string => "https://images.unsplash.com/photo-{$id}?auto=format&fit=crop&w=1600&q=80";

        $listings = [
            [
                'title' => 'The Palms — Contemporary 5 Bedroom Villa',
                'listing_type' => ListingType::Sale, 'property_type' => PropertyType::House,
                'price' => 850_000_000, 'address' => '14 Admiralty Way', 'city' => 'Lekki', 'state' => 'Lagos',
                'bedrooms' => 5, 'bathrooms' => 6, 'area_sqm' => 620, 'is_featured' => true,
                'features' => ['Swimming pool', 'Smart home', 'Cinema room', '24/7 security', 'Boys quarters', 'Solar power'],
                'images' => ['1600596542815-ffad4c1539a9', '1600607687939-ce8a6c25118c', '1600566753190-17f0baa2a6c3', '1600210492486-724fe5c67fb0'],
            ],
            [
                'title' => 'Ivory Court — Classic Family Home',
                'listing_type' => ListingType::Sale, 'property_type' => PropertyType::Duplex,
                'price' => 420_000_000, 'address' => '7 Bourdillon Road', 'city' => 'Ikoyi', 'state' => 'Lagos',
                'bedrooms' => 4, 'bathrooms' => 5, 'area_sqm' => 480, 'is_featured' => true,
                'features' => ['Landscaped garden', 'Fitted kitchen', 'Parking for 4', 'Staff quarters'],
                'images' => ['1600585154340-be6161a56a0c', '1600047509807-ba8f99d2cdde', '1600566753190-17f0baa2a6c3'],
            ],
            [
                'title' => 'Skyline Residences — Penthouse Apartment',
                'listing_type' => ListingType::Rent, 'property_type' => PropertyType::Apartment, 'rent_period' => RentPeriod::Year,
                'price' => 35_000_000, 'address' => '2 Ozumba Mbadiwe Avenue', 'city' => 'Victoria Island', 'state' => 'Lagos',
                'bedrooms' => 3, 'bathrooms' => 4, 'area_sqm' => 260, 'is_featured' => true,
                'features' => ['Ocean view', 'Gym', 'Concierge', 'Elevator', 'Backup power'],
                'images' => ['1502672260266-1c1ef2d93688', '1522708323590-d24dbb6b0267', '1560448204-e02f11c3d0e2'],
            ],
            [
                'title' => 'Maple Grove — Modern Townhouse',
                'listing_type' => ListingType::Sale, 'property_type' => PropertyType::Townhouse,
                'price' => 180_000_000, 'address' => '21 Aminu Kano Crescent', 'city' => 'Wuse II', 'state' => 'Abuja',
                'bedrooms' => 3, 'bathrooms' => 3, 'area_sqm' => 240,
                'features' => ['Gated estate', 'Children play area', 'Private balcony'],
                'images' => ['1570129477492-45c003edd2be', '1600607687939-ce8a6c25118c'],
            ],
            [
                'title' => 'Garden Terrace — 2 Bedroom Apartment',
                'listing_type' => ListingType::Rent, 'property_type' => PropertyType::Apartment, 'rent_period' => RentPeriod::Year,
                'price' => 6_500_000, 'address' => '5 Adeola Odeku Street', 'city' => 'Victoria Island', 'state' => 'Lagos',
                'bedrooms' => 2, 'bathrooms' => 2, 'area_sqm' => 130,
                'features' => ['Serviced', 'Swimming pool', 'Parking'],
                'images' => ['1493809842364-78817add7ffb', '1560448204-e02f11c3d0e2'],
            ],
            [
                'title' => 'Crestview Manor — Luxury Estate Home',
                'listing_type' => ListingType::Sale, 'property_type' => PropertyType::House, 'status' => PropertyStatus::UnderOffer,
                'price' => 1_200_000_000, 'address' => '3 Lake View Drive', 'city' => 'Maitama', 'state' => 'Abuja',
                'bedrooms' => 6, 'bathrooms' => 7, 'area_sqm' => 900, 'is_featured' => true,
                'features' => ['Swimming pool', 'Tennis court', 'Wine cellar', 'Home office', 'Guest chalet'],
                'images' => ['1613490493576-7fde63acd811', '1580587771525-78b9dba3b914', '1600210492486-724fe5c67fb0'],
            ],
            [
                'title' => 'Harbour Point — Grade A Office Floor',
                'listing_type' => ListingType::Rent, 'property_type' => PropertyType::Office, 'rent_period' => RentPeriod::Year,
                'price' => 48_000_000, 'address' => '9 Kofo Abayomi Street', 'city' => 'Victoria Island', 'state' => 'Lagos',
                'bedrooms' => null, 'bathrooms' => 4, 'area_sqm' => 650,
                'features' => ['Open plan', 'Fibre internet', 'Backup power', 'Ample parking'],
                'images' => ['1486406146926-c627a92ad1ab'],
            ],
            [
                'title' => 'Willow Lane — Charming Bungalow',
                'listing_type' => ListingType::Sale, 'property_type' => PropertyType::House, 'status' => PropertyStatus::Sold,
                'price' => 95_000_000, 'address' => '11 Willow Lane', 'city' => 'Ibadan', 'state' => 'Oyo',
                'bedrooms' => 3, 'bathrooms' => 2, 'area_sqm' => 210,
                'features' => ['Large compound', 'Borehole', 'Fenced'],
                'images' => ['1564013799919-ab600027ffc6', '1512917774080-9991f1c4c750'],
            ],
            [
                'title' => 'Seaside Loft — Furnished Studio',
                'listing_type' => ListingType::Rent, 'property_type' => PropertyType::Apartment, 'rent_period' => RentPeriod::Month,
                'price' => 850_000, 'address' => '40 Freedom Way', 'city' => 'Lekki', 'state' => 'Lagos',
                'bedrooms' => 1, 'bathrooms' => 1, 'area_sqm' => 70,
                'features' => ['Fully furnished', 'Wi-Fi', 'Housekeeping'],
                'images' => ['1583608205776-bfd35f0d9f83', '1522708323590-d24dbb6b0267'],
            ],
        ];

        foreach ($listings as $index => $listing) {
            $images = $listing['images'];
            unset($listing['images']);

            if (Property::where('title', $listing['title'])->exists()) {
                continue;
            }

            $property = Property::create([
                'status' => PropertyStatus::Published,
                'description' => $this->description($listing['title']),
                ...$listing,
            ]);

            $property->published_at = now()->subDays(count($listings) - $index);
            $property->save();

            foreach ($images as $order => $id) {
                $property->images()->create(['path' => $photo($id), 'sort_order' => $order]);
            }
        }
    }

    private function description(string $title): string
    {
        return "{$title} offers refined living with thoughtful design throughout. Bright, generously proportioned rooms flow naturally from one to the next, finished with quality materials and an eye for detail.\n\n"
            ."The home sits in a sought-after, secure neighbourhood within easy reach of schools, shopping and business districts. Outdoor spaces have been designed for both quiet mornings and entertaining guests.\n\n"
            .'Engage with this listing to request a private viewing, ask questions or receive the full information pack from our team.';
    }
}
