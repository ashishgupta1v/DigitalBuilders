<?php

declare(strict_types=1);

namespace App\Modules\Library\Infrastructure\Persistence\Repositories;

use App\Modules\Library\Domain\Entities\Lead;
use App\Modules\Library\Domain\Repositories\LeadRepositoryInterface;
use App\Modules\Library\Domain\ValueObjects\LeadEmail;
use App\Modules\Library\Domain\ValueObjects\LeadPhone;
use App\Modules\Library\Domain\ValueObjects\ProjectType;
use App\Modules\Library\Infrastructure\Persistence\Models\LeadModel;

final class EloquentLeadRepository implements LeadRepositoryInterface
{
    public function save(Lead $lead): Lead
    {
        $model = LeadModel::create([
            'name'         => $lead->name(),
            'email'        => $lead->email()->value(),
            'phone'        => $lead->phone()->value(),
            'project_type' => $lead->projectType()->value(),
            'description'  => $lead->description(),
        ]);

        return $this->toDomain($model);
    }

    public function findById(int $id): ?Lead
    {
        $model = LeadModel::find($id);

        return $model ? $this->toDomain($model) : null;
    }

    /** @return Lead[] */
    public function all(array $filters = []): array
    {
        $query = LeadModel::orderByDesc('created_at');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['search'])) {
            $term = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', $term)
                  ->orWhere('email', 'like', $term);
            });
        }

        return $query->get()
            ->map(fn (LeadModel $m) => $this->toDomain($m))
            ->all();
    }

    public function updateStatus(int $id, string $status): void
    {
        LeadModel::where('id', $id)->update(['status' => $status]);
    }

    private function toDomain(LeadModel $model): Lead
    {
        return Lead::fromPersistence(
            id: $model->id,
            name: $model->name,
            email: new LeadEmail($model->email),
            phone: new LeadPhone($model->phone),
            projectType: new ProjectType($model->project_type),
            description: $model->description,
            createdAt: new \DateTimeImmutable($model->created_at->toDateTimeString()),
            status: $model->status ?? 'new',
        );
    }
}
