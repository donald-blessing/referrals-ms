<?php

namespace App\Models;

use App\Services\ReferralCodeService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\UuidTrait;
use Illuminate\Support\Str;

class ReferralCode extends MainModel
{
    use HasFactory;
    use UuidTrait;

    const CAMPAIGN = 'Referral Program';
    const MEDIUM = 'Invite Friends';

    //const ANDROID_MIN_PACKAGE_VERSION = '20040902';

    protected $appends = [
        'resource_url'
    ];

    protected $fillable = [
        'code',
        'application_id',
        'user_id',
        'referral_link',
        'is_default',
        'note'
    ];

    /**
     * Boot the model.
     *
     * @return  void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($obj) {
            do {
                // generate a random string using Laravel's str_random helper
                $referralCode = Str::random(8);
            } //check if the token already exists and if it does, try again
            while (self::where('code', $referralCode)->first());

            $obj->code = (string)$referralCode;
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function getUserByReferralCode($referral_code, $application_id)
    {
        return $referral_code ? self::where('code', $referral_code)->where('application_id', $application_id)
            ->first() : NULL;
    }

    /* ************************ ACCESSOR ************************* */

    public function getResourceUrlAttribute()
    {
        return url('/admin/links/'.$this->getKey());
    }
}
