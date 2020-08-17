<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransportBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('destination_id')->nullable();
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
            $table->unsignedDecimal('cost', 8, 2)->nullable();
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
        Schema::dropIfExists('transport_bookings');
    }
}
