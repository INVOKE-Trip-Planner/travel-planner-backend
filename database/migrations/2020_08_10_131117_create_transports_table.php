<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transports', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_id');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->enum('mode', ['FLIGHT', 'FERRY', 'BUS', 'TRAIN', 'OTHER']);
            $table->date('departure_date')->nullable();
            $table->date('arrival_date')->nullable();
            $table->unsignedTinyInteger('departure_hour')->nullable();
            $table->unsignedTinyInteger('arrival_hour')->nullable();
            $table->unsignedTinyInteger('departure_minute')->nullable();
            $table->unsignedTinyInteger('arrival_minute')->nullable();
            $table->string('origin');
            $table->string('destination');
            $table->string('operator')->nullable();
            $table->string('booking_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transports');
    }
}
