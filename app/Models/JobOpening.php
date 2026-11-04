<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class JobOpening extends Model
{
    protected $guarded = [];
    public function department() { return $this->belongsTo(Department::class); }
    public function applicants() { return $this->hasMany(Applicant::class); }
}
