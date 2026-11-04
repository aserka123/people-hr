<?php
namespace App\Http\Controllers;
use App\Models\Employee;
use Illuminate\Validation\Rule;

class EmployeeController extends CrudController
{
    protected string $model = Employee::class;
    protected array $with = ['department'];
    protected array $search = ['first_name', 'last_name', 'email', 'employee_no', 'position'];
    protected bool $ownedByEmployee = true;
    protected function ownerColumn(): string { return 'id'; }

    protected function rules(?int $id = null): array
    {
        return [
            'department_id' => 'nullable|exists:departments,id',
            'user_id' => 'nullable|exists:users,id',
            'employee_no' => ['required', Rule::unique('employees')->ignore($id)],
            'first_name' => 'required|string|max:100', 'last_name' => 'required|string|max:100',
            'email' => ['required', 'email', Rule::unique('employees')->ignore($id)],
            'phone' => 'nullable|string|max:30', 'position' => 'required|string|max:100',
            'hire_date' => 'required|date', 'basic_salary' => 'required|numeric|min:0',
            'allowances' => 'nullable|numeric|min:0', 'status' => 'required|in:active,inactive',
        ];
    }
}
