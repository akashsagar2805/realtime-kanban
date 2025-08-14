<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['title', 'user_id', 'column_id', 'order'];
    public function column()
    {
        return $this->belongsTo(Column::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
