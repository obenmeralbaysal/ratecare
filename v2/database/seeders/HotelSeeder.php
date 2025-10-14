<?php

use Core\Hash;

return new class {
    /**
     * Run the seeder
     */
    public function run($db)
    {
        // Get demo customer
        $customer = $db->selectOne("SELECT id FROM users WHERE email = ?", ['customer@demo.com']);
        
        if (!$customer) {
            echo "  - Demo customer not found, skipping hotel seeder\n";
            return;
        }
        
        $userId = $customer['id'];
        
        // Create demo hotels
        $hotels = [
            [
                'name' => 'Grand Palace Hotel',
                'code' => Hash::hotelCode('GPL'),
                'address' => '123 Main Street',
                'city' => 'New York',
                'country' => 'USA',
                'phone' => '+1-555-0123',
                'email' => 'info@grandpalace.com',
                'website' => 'https://grandpalace.com',
                'star_rating' => 5,
                'description' => 'Luxury hotel in the heart of New York City',
                'currency' => 'USD',
                'language' => 'en'
            ],
            [
                'name' => 'Seaside Resort',
                'code' => Hash::hotelCode('SSR'),
                'address' => '456 Beach Boulevard',
                'city' => 'Miami',
                'country' => 'USA',
                'phone' => '+1-555-0456',
                'email' => 'info@seasideresort.com',
                'website' => 'https://seasideresort.com',
                'star_rating' => 4,
                'description' => 'Beautiful beachfront resort with ocean views',
                'currency' => 'USD',
                'language' => 'en'
            ]
        ];
        
        foreach ($hotels as $hotel) {
            $exists = $db->selectOne("SELECT id FROM hotels WHERE name = ? AND user_id = ?", [$hotel['name'], $userId]);
            
            if (!$exists) {
                $db->insert(
                    "INSERT INTO hotels (user_id, name, code, address, city, country, phone, email, website, star_rating, description, currency, language, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())",
                    [
                        $userId,
                        $hotel['name'],
                        $hotel['code'],
                        $hotel['address'],
                        $hotel['city'],
                        $hotel['country'],
                        $hotel['phone'],
                        $hotel['email'],
                        $hotel['website'],
                        $hotel['star_rating'],
                        $hotel['description'],
                        $hotel['currency'],
                        $hotel['language']
                    ]
                );
                
                echo "  - Created hotel: {$hotel['name']}\n";
            }
        }
    }
};
