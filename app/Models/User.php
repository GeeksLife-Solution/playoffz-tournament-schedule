<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\Notify;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, Notify;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = ['last-seen-activity'];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
    public $allusers = [];
    protected $dates = ['deleted_at', 'fullname'];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('userRecord');
        });
    }

    public function getFullnameAttribute()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function funds()
    {
        return $this->hasMany(Fund::class)->latest()->where('status', '!=', 0);
    }


    public function transaction()
    {
        return $this->hasOne(Transaction::class)->latest();
    }

    public function payout()
    {
        return $this->hasMany(Payout::class, 'user_id');
    }


    public function getLastSeenActivityAttribute()
    {
        if (Cache::has('user-is-online-' . $this->id) == true) {
            return true;
        } else {
            return false;
        }
    }


    public function inAppNotification()
    {
        return $this->morphOne(InAppNotification::class, 'inAppNotificationable', 'in_app_notificationable_type', 'in_app_notificationable_id');
    }

    public function fireBaseToken()
    {
        return $this->morphMany(FireBaseToken::class, 'tokenable');
    }

    public function profilePicture()
    {
        $image = $this->image;
        if (!$image) {
            $active = $this->LastSeenActivity == false ? 'warning' : 'success';
            $firstLetter = substr($this->firstname, 0, 1);
            return '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                        <span class="avatar-initials">' . $firstLetter . '</span>
                        <span class="avatar-status avatar-sm-status avatar-status-' . $active . '"></span>
                     </div>';

        } else {
            $url = getFile($this->image_driver, $this->image);
            $active = $this->LastSeenActivity == false ? 'warning' : 'success';
            return '<div class="avatar avatar-sm avatar-circle">
                        <img class="avatar-img" src="' . $url . '" alt="Image Description">
                        <span class="avatar-status avatar-sm-status avatar-status-' . $active . '"></span>
                     </div>';

        }
    }

    public function sendPasswordResetNotification($token)
    {
        $this->mail($this, 'PASSWORD_RESET', $params = [
            'message' => '<a href="' . url('password/reset', $token) . '?email=' . $this->email . '" target="_blank">Click To Reset Password</a>'
        ]);
    }

    public function getReferralLinkAttribute()
    {
        return $this->referral_link = route('register', ['ref' => $this->username]);
    }

    public function referral()
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function referralBonusLog()
    {
        return $this->hasMany(ReferralBonus::class, 'from_user_id', 'id');
    }

    public function scopeLevel()
    {
        $count = 0;
        $user_id = $this->id;
        while ($user_id != null) {
            $user = User::where('referral_id', $user_id)->first();
            if (!$user) {
                break;
            } else {
                $user_id = $user->id;
                $count++;
            }
        }
        return $count;
    }

    public function referralUsers($id, $currentLevel = 1)
    {
        $users = $this->getUsers($id);
        if ($users['status']) {
            $this->allusers[$currentLevel] = $users['user'];
            $currentLevel++;
            $this->referralUsers($users['ids'], $currentLevel);
        }
        return $this->allusers;
    }

    public function getUsers($id)
    {
        if (isset($id)) {
            $data['user'] = User::whereIn('referral_id', $id)->get(['id', 'firstname', 'lastname', 'username', 'email', 'phone_code', 'phone', 'referral_id', 'created_at']);
            if (count($data['user']) > 0) {
                $data['status'] = true;
                $data['ids'] = $data['user']->pluck('id');
                return $data;
            }
        }
        $data['status'] = false;
        return $data;
    }
}
