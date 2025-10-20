<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('languages', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique(); // en, tr, de, etc.
            $table->string('native_name')->nullable(); // English, Türkçe, Deutsch
            $table->boolean('is_active')->default(1);
            $table->boolean('is_default')->default(0);
            $table->timestamps();
            
            $table->index(['code']);
            $table->index(['is_active']);
            $table->index(['is_default']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('languages');
    }
};
