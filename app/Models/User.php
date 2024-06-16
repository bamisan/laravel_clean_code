<?php

namespace App\Models;

use App\Mail\ActivateUserEmail;
use App\Mail\SendEmail2FACode;
use Exception;
use Illuminate\Auth\Passwords\PasswordBrokerManager;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
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
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function activateUser(): array
    {
        $broker = app(PasswordBrokerManager::class)->broker();
        $token = $broker->createToken($this);
        $hash = hash('sha1', $this->email);
        $email = $this->email;
        // $url = config('app.frontend_url') . '/activate-account/' . $hash . '/' . $token . '?email=' . $email;
        $url = 'http://127.0.0.1:8000/api' . '/activate-account/' . $hash . '/' . $token . '?email=' . $email;


        try {

            $details = [
                // 'name' => $this->name,
                'email_verify_url' => $url,
            ];

            // Mail::to($this->email)->send(new ActivateUserEmail($details));

            return $details;

        } catch (Exception $e) {

            info('Error: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    public function generateCode(): array
    {
        $code = rand(100000, 999999);

        UserOtp::updateOrCreate(
            [
                'user_id' => $this->id,
                'otp' => $code,
                'expired_at' => now()->addMinutes(5),
            ]
        );

        try {

            $details = [
                // 'name' => $this->first_name,
                'otp' => $code,
            ];

            // Mail::to($this->email)->send(new SendEmail2FACode($details));

            return $details;

        } catch (Exception $e) {

            info('Error: ' . $e->getMessage());
        }
    }
}
