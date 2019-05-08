<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Investment extends Model
{
    protected $fillable =['returns'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getLateReturnsAttribute()
    {
        return bcdiv($this->attributes['late_returns'], 1, 2);
    }

     public function getReturnsWaitAttribute()
    {
        return bcdiv($this->attributes['returns_wait'], 1, 2);
    }
}
