<?php

namespace App\Models;

use App\Policies\BoardPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
#[UsePolicy(BoardPolicy::class)]
class Board extends Model
{
    protected $fillable = ['name', 'created_by'];

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function columns()
    {
        return $this->hasMany(Column::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }
}
