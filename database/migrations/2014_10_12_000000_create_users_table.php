<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('password');
            $table->timestamp('last_login_at')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('last_login_ip')->nullable();
            $table->string('avatar')->nullable(); //->default('placeholder.jpg');
            $table->enum('gender', ['MALE', 'FEMALE', 'OTHER'])->nullable();
            $table->date('birth_date')->nullable();
            // $table->string('currency')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        DB::statement('ALTER TABLE users ADD FULLTEXT fulltext_index (username, name, email)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
