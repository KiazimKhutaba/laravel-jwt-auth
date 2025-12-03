<?php

namespace Devkit2026\JwtAuth\Attributes;

use Attribute;

/**
 * Marks a method as emitting/dispatching an event.
 * 
 * This attribute can be used to document which events a method emits,
 * making it easier to discover event emission points in the codebase.
 * 
 * @example
 * #[EmitsEvent(UserRegistered::class, 'Dispatched after user creation')]
 * public function registerUser() { ... }
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class EmitsEvent
{
    /**
     * @param string $eventClass The fully qualified class name of the event
     * @param string|null $description Optional description of when/why the event is emitted
     */
    public function __construct(
        public readonly string $eventClass,
        public readonly ?string $description = null
    ) {}
}
