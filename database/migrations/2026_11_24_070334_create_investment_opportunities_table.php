<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('investment_opportunities', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('category', 40)->index();
            $table->string('status', 20)->default('draft')->index();
            $table->string('location');
            $table->string('state')->nullable()->index();
            $table->string('summary', 300);
            $table->text('description');
            $table->unsignedBigInteger('required_investment')->nullable();
            $table->unsignedBigInteger('minimum_participation')->nullable();
            $table->string('timeline')->nullable();
            $table->text('structure')->nullable();
            $table->text('risks')->nullable();
            $table->string('documentation')->nullable();
            $table->string('verification_status', 30)->default('pending');
            $table->string('image_path')->nullable();
            $table->boolean('is_featured')->default(false);

            // Land banking details.
            $table->string('land_use', 20)->nullable();
            $table->string('land_sizes')->nullable();
            $table->unsignedInteger('land_size_sqm')->nullable();
            $table->unsignedBigInteger('acquisition_price')->nullable();
            $table->string('access')->nullable();
            $table->string('estate_status')->nullable();
            $table->string('rationale', 300)->nullable();

            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('investment_opportunities');
    }
};
