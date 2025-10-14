<?php

use Core\Hash;

return new class {
    /**
     * Run the seeder
     */
    public function run($db)
    {
        // Create admin user
        $adminExists = $db->selectOne("SELECT id FROM users WHERE email = ?", ['admin@hoteldigilab.com']);
        
        if (!$adminExists) {
            $db->insert(
                "INSERT INTO users (namesurname, email, password, is_admin, reseller_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    'System Administrator',
                    'admin@hoteldigilab.com',
                    Hash::make('admin123'),
                    1,
                    0
                ]
            );
            
            echo "  - Created admin user: admin@hoteldigilab.com\n";
        }
        
        // Create demo reseller
        $resellerExists = $db->selectOne("SELECT id FROM users WHERE email = ?", ['reseller@demo.com']);
        
        if (!$resellerExists) {
            $db->insert(
                "INSERT INTO users (namesurname, email, password, is_admin, reseller_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    'Demo Reseller',
                    'reseller@demo.com',
                    Hash::make('reseller123'),
                    0,
                    0
                ]
            );
            
            echo "  - Created demo reseller: reseller@demo.com\n";
        }
        
        // Create demo customer
        $customerExists = $db->selectOne("SELECT id FROM users WHERE email = ?", ['customer@demo.com']);
        
        if (!$customerExists) {
            $resellerId = $db->selectOne("SELECT id FROM users WHERE email = ?", ['reseller@demo.com'])['id'];
            
            $db->insert(
                "INSERT INTO users (namesurname, email, password, is_admin, reseller_id, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    'Demo Customer',
                    'customer@demo.com',
                    Hash::make('customer123'),
                    0,
                    $resellerId
                ]
            );
            
            echo "  - Created demo customer: customer@demo.com\n";
        }
    }
};
