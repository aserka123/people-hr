<?php
namespace App\Http\Controllers;
use App\Models\JobOpening;

class JobOpeningController extends CrudController
{
    protected string $model = JobOpening::class;
    protected array $with = ['department'];
    protected array $search = ['title'];
    protected function rules(?int $id = null): array
    {
        return [
            'title' => 'required|string|max:150', 'department_id' => 'nullable|exists:departments,id',
            'description' => 'nullable|string', 'vacancies' => 'required|integer|min:1', 'status' => 'required|in:open,closed',
        ];
    }
}
