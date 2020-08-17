<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccommodationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('destination_id');
            $table->foreign('destination_id')->references('id')->on('destinations')->onDelete('cascade');
            $table->date('checkin_date')->nullable();
            $table->date('checkout_date')->nullable();
            $table->unsignedTinyInteger('checkin_hour')->nullable();
            $table->unsignedTinyInteger('checkout_hour')->nullable();
            $table->unsignedTinyInteger('checkin_minute')->nullable();
            $table->unsignedTinyInteger('checkout_minute')->nullable();
            $table->string('accommodation_name');
            // $table->unsignedDecimal('cost', 8, 2)->nullable();
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
        Schema::dropIfExists('accommodations');
    }
}
