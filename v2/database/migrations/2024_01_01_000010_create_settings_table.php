<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('settings', function ($table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('type')->default('string'); // string, integer, boolean, json
            $table->string('group')->default('general'); // general, email, api, etc.
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(0); // Can be accessed by frontend
            $table->timestamps();
            
            $table->index(['key']);
            $table->index(['group']);
            $table->index(['is_public']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('settings');
    }
};
