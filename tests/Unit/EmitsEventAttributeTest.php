<?php

namespace Devkit2026\JwtAuth\Tests\Unit;

use Devkit2026\JwtAuth\Attributes\EmitsEvent;
use Devkit2026\JwtAuth\Events\UserRegistered;
use Devkit2026\JwtAuth\Services\AuthService;
use Devkit2026\JwtAuth\Http\Controllers\VerificationController;
use Illuminate\Auth\Events\Verified;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

class EmitsEventAttributeTest extends TestCase
{
    public function test_emits_event_attribute_can_be_read_from_auth_service()
    {
        $reflection = new ReflectionClass(AuthService::class);
        $method = $reflection->getMethod('registerByEmailPassword');
        
        $attributes = $method->getAttributes(EmitsEvent::class);
        
        $this->assertCount(1, $attributes);
        
        /** @var EmitsEvent $attribute */
        $attribute = $attributes[0]->newInstance();
        
        $this->assertEquals(UserRegistered::class, $attribute->eventClass);
        $this->assertEquals('Dispatched after user is created', $attribute->description);
    }

    public function test_emits_event_attribute_can_be_read_from_verification_controller()
    {
        $reflection = new ReflectionClass(VerificationController::class);
        $method = $reflection->getMethod('verify');
        
        $attributes = $method->getAttributes(EmitsEvent::class);
        
        $this->assertCount(1, $attributes);
        
        /** @var EmitsEvent $attribute */
        $attribute = $attributes[0]->newInstance();
        
        $this->assertEquals(Verified::class, $attribute->eventClass);
        $this->assertEquals('Dispatched when email is successfully verified', $attribute->description);
    }

    public function test_emits_event_attribute_is_repeatable()
    {
        // Create a test class with multiple EmitsEvent attributes
        $testClass = new class {
            #[EmitsEvent(UserRegistered::class, 'First event')]
            #[EmitsEvent(Verified::class, 'Second event')]
            public function testMethod() {}
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('testMethod');
        
        $attributes = $method->getAttributes(EmitsEvent::class);
        
        $this->assertCount(2, $attributes);
        
        $firstAttribute = $attributes[0]->newInstance();
        $this->assertEquals(UserRegistered::class, $firstAttribute->eventClass);
        $this->assertEquals('First event', $firstAttribute->description);
        
        $secondAttribute = $attributes[1]->newInstance();
        $this->assertEquals(Verified::class, $secondAttribute->eventClass);
        $this->assertEquals('Second event', $secondAttribute->description);
    }

    public function test_emits_event_attribute_description_is_optional()
    {
        // Create a test class without description
        $testClass = new class {
            #[EmitsEvent(UserRegistered::class)]
            public function testMethod() {}
        };

        $reflection = new ReflectionClass($testClass);
        $method = $reflection->getMethod('testMethod');
        
        $attributes = $method->getAttributes(EmitsEvent::class);
        $attribute = $attributes[0]->newInstance();
        
        $this->assertEquals(UserRegistered::class, $attribute->eventClass);
        $this->assertNull($attribute->description);
    }
}
