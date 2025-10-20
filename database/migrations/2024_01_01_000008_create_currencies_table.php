<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('currencies', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3)->unique(); // USD, EUR, etc.
            $table->string('symbol', 10); // $, €, etc.
            $table->decimal('exchange_rate', 10, 4)->default(1.0000); // Rate to USD
            $table->boolean('is_active')->default(1);
            $table->timestamp('rate_updated_at')->nullable();
            $table->timestamps();
            
            $table->index(['code']);
            $table->index(['is_active']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('currencies');
    }
};
