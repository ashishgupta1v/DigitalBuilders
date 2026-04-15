<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Library\Domain;

use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Domain\Exceptions\InvalidEmailException;
use App\Modules\Library\Domain\Exceptions\InvalidPhoneException;
use App\Modules\Library\Domain\Exceptions\InvalidProjectTypeException;
use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;
use PHPUnit\Framework\TestCase;

class LeadEntityTest extends TestCase
{
    public function test_create_lead_entity(): void
    {
        $lead = Lead::create(
            name: 'John Doe',
            email: new LeadEmail('john@example.com'),
            phone: new LeadPhone('+91 90870 21592'),
            projectType: new ProjectType('web_app'),
            description: 'Need a fast website.',
        );

        $this->assertNull($lead->id());
        $this->assertSame('John Doe', $lead->name());
        $this->assertSame('john@example.com', $lead->email()->value());
        $this->assertSame('+91 90870 21592', $lead->phone()->value());
        $this->assertSame('web_app', $lead->projectType()->value());
        $this->assertSame('Web Application', $lead->projectType()->label());
        $this->assertSame('Need a fast website.', $lead->description());
        $this->assertInstanceOf(\DateTimeImmutable::class, $lead->createdAt());
    }

    public function test_lead_without_description(): void
    {
        $lead = Lead::create(
            name: 'Jane Doe',
            email: new LeadEmail('jane@example.com'),
            phone: new LeadPhone('+91 12345 67890'),
            projectType: new ProjectType('ai_solutions'),
        );

        $this->assertNull($lead->description());
    }

    public function test_from_persistence(): void
    {
        $lead = Lead::fromPersistence(
            id: 42,
            name: 'Test User',
            email: new LeadEmail('test@test.com'),
            phone: new LeadPhone('1234567890'),
            projectType: new ProjectType('saas'),
            description: null,
            createdAt: new \DateTimeImmutable('2025-01-01 12:00:00'),
        );

        $this->assertSame(42, $lead->id());
        $this->assertSame('Test User', $lead->name());
    }
}
