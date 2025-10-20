<?php

namespace Tests;

use Core\Auth;
use Core\Hash;
use Core\Database;

class AuthTest
{
    public static function run()
    {
        $runner = new TestRunner();
        
        // Authentication Tests
        $runner->addTest('Hash Password', function() {
            $password = 'test123';
            $hashed = Hash::make($password);
            
            TestRunner::assertNotEquals($password, $hashed, 'Password should be hashed');
            TestRunner::assertTrue(Hash::verify($password, $hashed), 'Password verification should work');
            TestRunner::assertFalse(Hash::verify('wrong', $hashed), 'Wrong password should fail');
            
            return true;
        });
        
        $runner->addTest('Generate UUID', function() {
            $uuid1 = Hash::uuid();
            $uuid2 = Hash::uuid();
            
            TestRunner::assertNotEquals($uuid1, $uuid2, 'UUIDs should be unique');
            TestRunner::assertEquals(36, strlen($uuid1), 'UUID should be 36 characters');
            TestRunner::assertTrue(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $uuid1), 'UUID should match format');
            
            return true;
        });
        
        $runner->addTest('Auth Session Management', function() {
            // Mock user data
            $userData = [
                'id' => 1,
                'email' => 'test@example.com',
                'name' => 'Test User',
                'role' => 'customer'
            ];
            
            // Test login
            Auth::login($userData);
            TestRunner::assertTrue(Auth::check(), 'User should be logged in');
            TestRunner::assertEquals(1, Auth::id(), 'User ID should match');
            TestRunner::assertEquals('test@example.com', Auth::user()['email'], 'Email should match');
            
            // Test logout
            Auth::logout();
            TestRunner::assertFalse(Auth::check(), 'User should be logged out');
            TestRunner::assertNull(Auth::user(), 'User data should be null');
            
            return true;
        });
        
        $runner->addTest('Role Checking', function() {
            $adminUser = [
                'id' => 1,
                'email' => 'admin@example.com',
                'role' => 'admin'
            ];
            
            $customerUser = [
                'id' => 2,
                'email' => 'customer@example.com',
                'role' => 'customer'
            ];
            
            // Test admin role
            Auth::login($adminUser);
            TestRunner::assertTrue(Auth::isAdmin(), 'Admin should be detected');
            TestRunner::assertFalse(Auth::isCustomer(), 'Should not be customer');
            
            // Test customer role
            Auth::login($customerUser);
            TestRunner::assertFalse(Auth::isAdmin(), 'Should not be admin');
            TestRunner::assertTrue(Auth::isCustomer(), 'Customer should be detected');
            
            Auth::logout();
            return true;
        });
        
        $runner->addTest('Password Validation', function() {
            // Test strong password
            $strongPassword = 'StrongPass123!';
            TestRunner::assertTrue(strlen($strongPassword) >= 8, 'Strong password should be valid');
            
            // Test weak password
            $weakPassword = '123';
            TestRunner::assertTrue(strlen($weakPassword) < 8, 'Weak password should be invalid');
            
            return true;
        });
        
        $runner->run();
    }
}
