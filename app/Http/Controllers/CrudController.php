<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

/** Shared list/create/update/delete behaviour. Child controllers set $model, $with, $search and rules(). */
abstract class CrudController extends Controller
{
    protected string $model;
    protected array $with = [];
    protected array $search = [];
    protected bool $ownedByEmployee = false; // non-HR users only see their own rows

    abstract protected function rules(?int $id = null): array;

    protected function ownerColumn(): string { return 'employee_id'; }

    protected function query(Request $r)
    {
        $q = ($this->model)::with($this->with)->latest('id');
        if ($this->ownedByEmployee && !$r->user()->isHr()) {
            $q->where($this->ownerColumn(), $r->user()->employee?->id ?? 0);
        }
        return $q;
    }

    public function index(Request $r)
    {
        $q = $this->query($r);
        if ($s = $r->query('q')) {
            $q->where(function ($w) use ($s) {
                foreach ($this->search as $c) $w->orWhere($c, 'like', "%$s%");
            });
        }
        return $q->paginate(min(max((int) $r->query('per_page', 15), 1), 100));
    }
    public function show(Request $r, $id) { return $this->query($r)->findOrFail($id); }
    public function store(Request $r)
    {
        return ($this->model)::create($r->validate($this->rules()))->load($this->with);
    }
    public function update(Request $r, $id)
    {
        $m = ($this->model)::findOrFail($id);
        $m->update($r->validate($this->rules($m->id)));
        return $m->load($this->with);
    }
    public function destroy($id) { ($this->model)::findOrFail($id)->delete(); return response()->noContent(); }
}
