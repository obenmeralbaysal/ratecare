<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('rate_comparisons', function ($table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->string('room_type')->nullable();
            $table->text('comparison_data'); // JSON with all rates
            $table->decimal('best_price', 10, 2);
            $table->string('best_source');
            $table->string('currency', 3)->default('USD');
            $table->timestamp('cached_at');
            $table->timestamps();
            
            $table->index(['hotel_id']);
            $table->index(['check_in', 'check_out']);
            $table->index(['cached_at']);
            $table->index(['best_price']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('rate_comparisons');
    }
};
