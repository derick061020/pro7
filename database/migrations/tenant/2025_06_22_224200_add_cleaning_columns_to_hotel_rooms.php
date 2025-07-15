<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCleaningColumnsToHotelRooms extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->boolean('is_clean')->default(false)->after('status');
            $table->string('cleaner_id')->nullable()->after('is_clean');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hotel_rooms', function (Blueprint $table) {
            $table->dropColumn(['is_clean', 'cleaner_id']);
        });
    }
}
