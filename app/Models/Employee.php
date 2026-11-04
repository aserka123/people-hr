<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Employee extends Model
{
    protected $guarded = [];
    protected $appends = ['name'];
    protected $casts = ['hire_date' => 'date:Y-m-d'];
    public function getNameAttribute(): string { return "{$this->first_name} {$this->last_name}"; }
    public function department() { return $this->belongsTo(Department::class); }
    public function user() { return $this->belongsTo(User::class); }
}
