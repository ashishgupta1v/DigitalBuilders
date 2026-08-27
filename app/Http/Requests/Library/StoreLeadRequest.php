<?php

declare(strict_types=1);

namespace App\Http\Requests\Library;

use App\Modules\Library\Domain\ValueObjects\ProjectType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // public form
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'source'       => $this->input('source') ?: 'contact',
            'utm_source'   => $this->input('utm_source') ?: $this->query('utm_source'),
            'utm_medium'   => $this->input('utm_medium') ?: $this->query('utm_medium'),
            'utm_campaign' => $this->input('utm_campaign') ?: $this->query('utm_campaign'),
            'utm_content'  => $this->input('utm_content') ?: $this->query('utm_content'),
            'utm_term'     => $this->input('utm_term') ?: $this->query('utm_term'),
        ]);
    }

    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:255'],
            'email'           => ['required', 'email', 'max:255'],
            'phone'           => ['required', 'string', 'max:20'],
            'project_type'    => ['required', Rule::in(ProjectType::allowed())],
            'description'     => ['nullable', 'string', 'max:2000'],
            'source'          => ['nullable', 'string', 'max:50'],
            'region'          => ['nullable', 'string', 'max:20'],
            'stage'           => ['nullable', 'string', 'max:50'],
            'estimated_value' => ['nullable', 'string', 'max:100'],
            'utm_source'      => ['nullable', 'string', 'max:100'],
            'utm_medium'      => ['nullable', 'string', 'max:100'],
            'utm_campaign'    => ['nullable', 'string', 'max:100'],
            'utm_content'     => ['nullable', 'string', 'max:100'],
            'utm_term'        => ['nullable', 'string', 'max:100'],
            '_hp_company'     => ['nullable', 'string', 'max:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'         => 'Please enter your full name.',
            'email.required'        => 'Please enter a valid email address.',
            'email.email'           => 'Please enter a valid email address.',
            'phone.required'        => 'Please enter your phone number.',
            'project_type.required' => 'Please select a project type.',
            'project_type.in'       => 'Please select a valid project type.',
            'description.max'       => 'Description must not exceed 2000 characters.',
        ];
    }
}
