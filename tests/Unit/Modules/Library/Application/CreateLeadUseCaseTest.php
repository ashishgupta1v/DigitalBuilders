<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Library\Application;

use App\Modules\Library\Application\DTOs\CreateLeadDTO;
use App\Modules\Library\Application\DTOs\LeadDTO;
use App\Modules\Library\Application\Interfaces\LeadNotifierInterface;
use App\Modules\Library\Application\UseCases\CreateLeadUseCase;
use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;
use App\Modules\Library\Domain\Services\LeadFactory;
use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;
use PHPUnit\Framework\TestCase;

class CreateLeadUseCaseTest extends TestCase
{
    public function test_execute_creates_and_persists_lead(): void
    {
        $savedLead = Lead::fromPersistence(
            id: 1,
            name: 'Ashish Gupta',
            email: new LeadEmail('hello@digitalbuilders.in'),
            phone: new LeadPhone('+91 90870 21592'),
            projectType: new ProjectType('web_app'),
            description: 'Enterprise platform needed.',
            createdAt: new \DateTimeImmutable('2025-06-01 10:00:00'),
        );

        $repo = $this->createMock(LeadRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('save')
            ->willReturn($savedLead);

        $notifier = $this->createMock(LeadNotifierInterface::class);
        $notifier->expects($this->once())->method('notify');

        $useCase = new CreateLeadUseCase($repo, new LeadFactory(), $notifier);

        $dto = new CreateLeadDTO(
            name: 'Ashish Gupta',
            email: 'hello@digitalbuilders.in',
            phone: '+91 90870 21592',
            projectType: 'web_app',
            description: 'Enterprise platform needed.',
        );

        $result = $useCase->execute($dto);

        $this->assertInstanceOf(LeadDTO::class, $result);
        $this->assertSame(1, $result->id);
        $this->assertSame('Ashish Gupta', $result->name);
        $this->assertSame('Web Application', $result->projectTypeLabel);
    }
}
