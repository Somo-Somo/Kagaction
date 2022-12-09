<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('line_user_id', 255)->unique();
            $table->integer('condition_id')->nullable();
            $table->integer('feeling_id')->nullable();
            $table->integer('operation_type')->nullable();
            $table->integer('order_number')->nullable();
            $table->timestamps();
            $table->foreign('line_user_id')->references('line_id')->on('users');
            $table->foreign('condition_id')->references('id')->on('conditions');
            $table->foreign('feeling_id')->references('id')->on('feelings');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
