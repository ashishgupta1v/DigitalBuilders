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

    public function rules(): array
    {
        return [
            'name'         => ['required', 'string', 'max:255'],
            'email'        => ['required', 'email', 'max:255'],
            'phone'        => ['required', 'string', 'max:20'],
            'project_type' => ['required', Rule::in(ProjectType::allowed())],
            'description'  => ['nullable', 'string', 'max:2000'],
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
