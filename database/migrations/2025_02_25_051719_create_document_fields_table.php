<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDocumentFieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('document_fields', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Field Type ka Name (singleLineText, textarea, etc.)
            $table->timestamps();
        });

        // Default Field Types Insert Karna
        DB::table('document_fields')->insert([
            ['name' => 'Single-Line Text'],
            ['name' => 'Multiple-Line Text'],
            ['name' => 'Checkbox'],
            ['name' => 'WholeNumber'],
            ['name' => 'Date'],
            ['name' => 'Currency'],
            ['name' => 'File'],
        ]);

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('document_fields');
    }
}
