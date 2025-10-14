<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('widgets', function ($table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->string('type', 50); // 'booking', 'rates', 'availability'
            $table->text('settings')->nullable(); // JSON settings
            $table->text('style_settings')->nullable(); // JSON style settings
            $table->boolean('is_active')->default(1);
            $table->timestamps();
            
            $table->index(['hotel_id']);
            $table->index(['code']);
            $table->index(['type']);
            $table->index(['is_active']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('widgets');
    }
};
