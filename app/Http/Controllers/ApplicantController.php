<?php
namespace App\Http\Controllers;
use App\Models\Applicant;

class ApplicantController extends CrudController
{
    protected string $model = Applicant::class;
    protected array $with = ['jobOpening'];
    protected array $search = ['name', 'email'];
    protected function rules(?int $id = null): array
    {
        return [
            'job_opening_id' => 'required|exists:job_openings,id', 'name' => 'required|string|max:150',
            'email' => 'required|email', 'phone' => 'nullable|string|max:30',
            'stage' => 'required|in:applied,screening,interview,offer,hired,rejected', 'notes' => 'nullable|string',
        ];
    }
}
