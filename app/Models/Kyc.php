<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'input_form', 'status'];

    public $casts = ['input_form' => 'object'];

    public function userKyc()
    {
        return $this->hasMany(UserKyc::class, 'kyc_id', 'id');
    }

    public function kycPosition()
    {
        $isUserVerified = UserKyc::where('user_id', auth()->id())->where('kyc_id', $this->id)
            ->where('status', 1)->exists();
        if ($isUserVerified) {
            return 'verified';
        }
        return 'unverified';
    }

}
