<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineUsersQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('line_users_questions', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id', 255)->unique()->nullable(false);
            $table->integer('question_number');
            $table->integer('checked_todo')->nullable();
            $table->string('parent_uuid', 255)->nullable();
            $table->string('project_uuid', 255)->nullable();
            $table->timestamps();

            $table->foreign('line_user_id')->references('line_user_id')->on('users');
            $table->foreign('project_uuid')->references('uuid')->on('projects');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('line_users_questions');
    }
}
