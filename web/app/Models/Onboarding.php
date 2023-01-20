<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use LINE\LINEBot\MessageBuilder\TemplateMessageBuilder;
use LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder;
use LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder;

class Onboarding extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = ['user_uuid'];


    /**
     * 最初の挨拶
     *
     * @return string $first_greeting
     */
    public static function firstGreeting()
    {
        return  [
            new TextMessageBuilder('はじめまして。アガトンです！' . "\n" . 'これからよろしくお願いします🙇‍♂️'),
            new TextMessageBuilder('あなたのことを何とお呼びしたらいいですか？' . "\n" . 'ニックネームを教えてください！'),
        ];
    }
}
