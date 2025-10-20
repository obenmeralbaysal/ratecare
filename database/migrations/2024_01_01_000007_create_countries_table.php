<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('countries', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->unique(); // ISO 2-letter code
            $table->string('code3', 3)->unique(); // ISO 3-letter code
            $table->string('phone_code', 10)->nullable();
            $table->string('currency', 3)->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            
            $table->index(['code']);
            $table->index(['code3']);
            $table->index(['is_active']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('countries');
    }
};
