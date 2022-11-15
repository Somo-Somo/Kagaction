<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param User $line_user
     * @param string $content
     * @return void
     */
    public function __construct(User $line_user, string $content)
    {
        $this->line_user = $line_user;
        $this->content = $content;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(env('MAIL_FROM_ADDRESS')) // 送信元
            ->subject('【とげるくん】感想・要望') // メールタイトル
            ->view('emails.contact') // どのテンプレートを呼び出すか
            ->with([
                'id' => $this->line_user->id,
                'name' => $this->line_user->name,
                'content' => $this->content
            ]); // withオプションでセットしたデータをテンプレートへ受け渡す
    }
}
