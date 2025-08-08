<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['title', 'user_id', 'column_id'];
    public function column()
    {
        return $this->belongsTo(Column::class);
    }
}
