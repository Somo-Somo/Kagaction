<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
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
        'uuid',
        'line_user_id',
        'created_at'
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

    /**
     * ユーザー(LINE)に関連する質問テーブルの取得
     *
     */
    public function question()
    {
        return $this->hasOne(LineUsersQuestion::class, 'line_user_id', 'line_user_id');
    }

    /**
     * ユーザー(LINE)に紐づくTodo全ての取得
     *
     */
    public function todo()
    {
        return $this->hasMany(Todo::class, 'user_uuid', 'uuid');
    }

    /**
     * ユーザー(LINE)に紐づくオンボーディング
     *
     */
    public function onboarding()
    {
        return $this->hasMany(Onboarding::class, 'user_uuid', 'uuid');
    }

    /**
     * ユーザー(LINE)に振り返り通知設定
     *
     */
    public function todo_check_notifications()
    {
        return $this->hasOne(TodoCheckNotificationDateTime::class, 'user_uuid', 'uuid');
    }

    /**
     * ユーザー(LINE)に紐づく習慣
     *
     */
    public function habits()
    {
        return $this->hasMany(TodoCheckNotificationDateTime::class, 'user_uuid', 'uuid');
    }
}
