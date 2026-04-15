<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Library\Domain;

use App\Modules\Library\Domain\Exceptions\InvalidEmailException;
use App\Modules\Library\Domain\Exceptions\InvalidPhoneException;
use App\Modules\Library\Domain\Exceptions\InvalidProjectTypeException;
use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;
use PHPUnit\Framework\TestCase;

class ValueObjectsTest extends TestCase
{
    public function test_valid_email(): void
    {
        $email = new LeadEmail('test@example.com');
        $this->assertSame('test@example.com', $email->value());
        $this->assertSame('test@example.com', (string) $email);
    }

    public function test_invalid_email_throws(): void
    {
        $this->expectException(InvalidEmailException::class);
        new LeadEmail('not-an-email');
    }

    public function test_email_too_long_throws(): void
    {
        $this->expectException(InvalidEmailException::class);
        new LeadEmail(str_repeat('a', 250) . '@example.com');
    }

    public function test_valid_phone(): void
    {
        $phone = new LeadPhone('+91 90870 21592');
        $this->assertSame('+91 90870 21592', $phone->value());
    }

    public function test_empty_phone_throws(): void
    {
        $this->expectException(InvalidPhoneException::class);
        new LeadPhone('');
    }

    public function test_phone_too_long_throws(): void
    {
        $this->expectException(InvalidPhoneException::class);
        new LeadPhone(str_repeat('1', 21));
    }

    public function test_valid_project_type(): void
    {
        $type = new ProjectType('web_app');
        $this->assertSame('web_app', $type->value());
        $this->assertSame('Web Application', $type->label());
    }

    public function test_invalid_project_type_throws(): void
    {
        $this->expectException(InvalidProjectTypeException::class);
        new ProjectType('invalid_type');
    }

    public function test_all_allowed_project_types(): void
    {
        $expected = ['web_app', 'mobile_app', 'erp_crm', 'saas', 'ai_solutions', 'other'];
        $this->assertSame($expected, ProjectType::allowed());
    }
}
