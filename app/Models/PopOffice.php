<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PopOffice extends Model
{
    protected $fillable = ['name', 'location', 'is_active'];

    public function tickets()
    {
        return $this->hasMany(Ticket::class, 'pop_office_id');
    }

    public function pendingCount(): int
    {
        return $this->tickets()->whereNotIn('status', ['resolved'])->count();
    }
}
