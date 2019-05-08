<?php

namespace App;

use Carbon\Carbon;
use DateInterval;
use DatePeriod;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Cviebrock\EloquentSluggable\Sluggable;
use App;

class Project extends Model
{

    public function translations() {
      return $this->hasMany('App\Translation');

    }
    public function payments() {
      return $this->hasMany(PaymentPaysera::class);
    }

    public function getTotalInvestorsAttribute()
    {
        return $this->hasMany(Investment::class)->count();
    }

    public function getTotalCollectedAttribute()
    {
        return $this->hasMany(Investment::class)->sum('amount');
    }

    public function user()
    {
        return $this->hasOne(User::class);
    }

    public function investments()
    {
        return $this->hasMany(Investment::class)->where('user_id', '!=', 'null');
    }

    public function borrowerUser()
    {
        return $this->belongsTo(User::class, 'borrower');
    }

    public function lateReturnInvestments()
    {
        return $this->hasMany(Investment::class)->where('user_id', '!=', 'null')->where('late_returns', '>', 0);
    }

    public function missedPeriods()
    {
        return $this->hasMany(PaymentPeriods::class)->where('status', 'missed');
    }

    public function waitingPeriods()
    {
        return $this->hasMany(PaymentPeriods::class)->where('status', 'waiting');
    }

    public function scopeInvesting($query)
    {
        return $query->where('status', 2);
    }


}
