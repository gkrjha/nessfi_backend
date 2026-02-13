<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->timestamps();
        });

        // Insert default sections
        DB::table('sections')->insert([
            ['section' => 'Basic', 'created_at' => now(), 'updated_at' => now()],
            ['section' => 'Self Feedback', 'created_at' => now(), 'updated_at' => now()],
            ['section' => 'Office Feedback', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
