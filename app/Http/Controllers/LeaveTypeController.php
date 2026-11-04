<?php
namespace App\Http\Controllers;
use App\Models\LeaveType;
use Illuminate\Validation\Rule;

class LeaveTypeController extends CrudController
{
    protected string $model = LeaveType::class;
    protected array $search = ['name'];
    protected function rules(?int $id = null): array
    {
        return [
            'name' => ['required', 'string', Rule::unique('leave_types')->ignore($id)],
            'days_per_year' => 'required|integer|min:0', 'paid' => 'required|boolean',
        ];
    }
}
