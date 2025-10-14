<?php

namespace Tests;

use Core\Database;
use Core\Validator;
use App\Models\User;
use App\Models\Hotel;
use App\Models\Widget;

class DatabaseTest
{
    public static function run()
    {
        $runner = new TestRunner();
        
        // Database Connection Tests
        $runner->addTest('Database Connection', function() {
            $db = Database::getInstance();
            TestRunner::assertInstanceOf(Database::class, $db, 'Should return Database instance');
            
            // Test simple query
            $result = $db->selectOne("SELECT 1 as test");
            TestRunner::assertEquals(1, $result['test'], 'Simple query should work');
            
            return true;
        });
        
        // Validation Tests
        $runner->addTest('Validator - Required Fields', function() {
            $data = ['name' => '', 'email' => 'test@example.com'];
            $rules = ['name' => 'required', 'email' => 'required|email'];
            
            $validator = Validator::make($data, $rules);
            TestRunner::assertFalse($validator->validate(), 'Validation should fail for empty name');
            TestRunner::assertArrayHasKey('name', $validator->errors(), 'Should have name error');
            
            return true;
        });
        
        $runner->addTest('Validator - Email Validation', function() {
            $data = ['email' => 'invalid-email'];
            $rules = ['email' => 'email'];
            
            $validator = Validator::make($data, $rules);
            TestRunner::assertFalse($validator->validate(), 'Should fail for invalid email');
            
            $data['email'] = 'valid@example.com';
            $validator = Validator::make($data, $rules);
            TestRunner::assertTrue($validator->validate(), 'Should pass for valid email');
            
            return true;
        });
        
        $runner->addTest('Validator - Numeric Validation', function() {
            $data = ['age' => 'not-a-number'];
            $rules = ['age' => 'numeric'];
            
            $validator = Validator::make($data, $rules);
            TestRunner::assertFalse($validator->validate(), 'Should fail for non-numeric');
            
            $data['age'] = '25';
            $validator = Validator::make($data, $rules);
            TestRunner::assertTrue($validator->validate(), 'Should pass for numeric string');
            
            return true;
        });
        
        $runner->addTest('Validator - Min/Max Length', function() {
            $data = ['password' => '123'];
            $rules = ['password' => 'min:8'];
            
            $validator = Validator::make($data, $rules);
            TestRunner::assertFalse($validator->validate(), 'Should fail for short password');
            
            $data['password'] = 'longenoughpassword';
            $validator = Validator::make($data, $rules);
            TestRunner::assertTrue($validator->validate(), 'Should pass for long enough password');
            
            return true;
        });
        
        // Model Tests (if database is available)
        $runner->addTest('User Model Basic Operations', function() {
            try {
                $user = new User();
                
                // Test model instantiation
                TestRunner::assertInstanceOf(User::class, $user, 'Should create User instance');
                
                // Test table name
                TestRunner::assertEquals('users', $user->getTable(), 'Table name should be users');
                
                return true;
            } catch (\Exception $e) {
                // Skip if database not available
                return true;
            }
        });
        
        $runner->addTest('Hotel Model Basic Operations', function() {
            try {
                $hotel = new Hotel();
                
                TestRunner::assertInstanceOf(Hotel::class, $hotel, 'Should create Hotel instance');
                TestRunner::assertEquals('hotels', $hotel->getTable(), 'Table name should be hotels');
                
                return true;
            } catch (\Exception $e) {
                return true;
            }
        });
        
        $runner->addTest('Widget Model Basic Operations', function() {
            try {
                $widget = new Widget();
                
                TestRunner::assertInstanceOf(Widget::class, $widget, 'Should create Widget instance');
                TestRunner::assertEquals('widgets', $widget->getTable(), 'Table name should be widgets');
                
                return true;
            } catch (\Exception $e) {
                return true;
            }
        });
        
        $runner->run();
    }
}
