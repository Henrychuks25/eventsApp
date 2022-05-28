<?php

namespace App\Models;

use App\Notifications\ForgotPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends \TCG\Voyager\Models\User
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    // get customer info
    public function get_customer($params = [])
    {
        return User::
        where([
            'id' => $params['customer_id'],
        ])
            ->first();
    }

    // total customers
    public function total_customers()
    {
        return User::where(['role_id' => 2])->count();
    }

    public function sendPasswordResetNotification($token)
    {

        // ====================== Notification ======================
        //forgot password notification

        // \Notification::send(new ForgotPasswordNotification($token));
        $this->notify(new ForgotPasswordNotification($token));

        // ====================== Notification ======================
    }
}
