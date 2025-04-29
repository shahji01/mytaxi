<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('locations', function (Blueprint $table) {
            $table->string('latitude_north')->nullable()->after('latitude');
            $table->string('latitude_south')->nullable()->after('latitude_north');
            $table->string('longitude_east')->nullable()->after('latitude_south');
            $table->string('longitude_west')->nullable()->after('longitude_east');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('locations', function (Blueprint $table) {
            //
        });
    }
}
