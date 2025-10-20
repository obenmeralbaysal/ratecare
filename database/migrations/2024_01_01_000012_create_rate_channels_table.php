<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('rate_channels', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->string('api_url')->nullable();
            $table->text('api_credentials')->nullable(); // JSON
            $table->boolean('is_active')->default(1);
            $table->integer('priority')->default(0); // Higher priority = checked first
            $table->timestamps();
            
            $table->index(['code']);
            $table->index(['is_active']);
            $table->index(['priority']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('rate_channels');
    }
};
