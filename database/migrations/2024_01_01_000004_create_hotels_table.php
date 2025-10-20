<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('hotels', function ($table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->integer('star_rating')->nullable();
            $table->text('description')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('language', 2)->default('en');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            
            $table->index(['user_id']);
            $table->index(['code']);
            $table->index(['is_active']);
            $table->index(['country']);
            $table->index(['city']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('hotels');
    }
};
