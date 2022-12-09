<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Condition extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id',
        'user_uuid',
        'evaluation',
        'date',
        'time',
        'created_at'
    ];

    /**
     * アガトンからユーザーへの質問を記録するテーブル
     *
     */
    public function question()
    {
        return $this->hasOne(Question::class, 'condition_id', 'id');
    }

    /**
     * 気分と紐づく
     *
     */
    public function feelings()
    {
        return $this->hasMany(Feeling::class, 'condition_id', 'id');
    }

    /**
     * 日記と紐づく
     *
     */
    public function diary()
    {
        return $this->hasOne(Diary::class, 'condition_id', 'id');
    }
}
