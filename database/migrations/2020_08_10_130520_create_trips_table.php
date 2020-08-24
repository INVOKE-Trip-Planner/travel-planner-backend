<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTripsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('trip_name')->nullable();
            // $table->boolean('trip_name_overwritten')->default(false);
            $table->string('origin')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->foreign('created_by')->references('id')->on('users');
            // $table->date('start_date')->nullable();
            // $table->date('end_date')->nullable();
            // $table->unsignedDecimal('cost', 8, 2)->nullable();
            $table->enum('group_type', ['SOLO', 'COUPLE', 'FAMILY', 'FRIENDS'])->nullable();
            $table->enum('trip_type', ['WORK', 'LEISURE'])->nullable();
            $table->string('trip_banner')->nullable();
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
        Schema::dropIfExists('trips');
    }
}
