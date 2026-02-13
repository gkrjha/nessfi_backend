<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Question;

class Section extends Model
{
    protected $fillable = ['section'];

    public function question()
    {
        return $this->hasMany(Question::class);
    }
}
