<?php
namespace App\Http\Controllers;
use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentController extends CrudController
{
    protected string $model = Department::class;
    protected array $search = ['name'];
    protected function rules(?int $id = null): array
    {
        return ['name' => ['required', 'string', 'max:100', Rule::unique('departments')->ignore($id)]];
    }
}
