<?php

namespace App\Models\HR;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobTitle extends Model
{
    protected $fillable = ['title'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }
}
