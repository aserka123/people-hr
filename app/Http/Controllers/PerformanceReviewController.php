<?php
namespace App\Http\Controllers;
use App\Models\PerformanceReview;
use Illuminate\Http\Request;

class PerformanceReviewController extends CrudController
{
    protected string $model = PerformanceReview::class;
    protected array $with = ['employee'];
    protected array $search = ['period'];
    protected bool $ownedByEmployee = true;

    protected function rules(?int $id = null): array
    {
        return [
            'employee_id' => 'required|exists:employees,id', 'period' => 'required|string|max:20',
            'rating' => 'required|integer|between:1,5', 'goals' => 'nullable|string', 'comments' => 'nullable|string',
            'status' => 'required|in:draft,submitted,acknowledged',
        ];
    }
    public function store(Request $r)
    {
        $data = $r->validate($this->rules()) + ['reviewer_id' => $r->user()->id];
        return PerformanceReview::create($data)->load($this->with);
    }
}
