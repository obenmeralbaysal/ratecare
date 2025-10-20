<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('rates', function ($table) {
            $table->id();
            $table->bigInteger('hotel_id');
            $table->string('room_type');
            $table->date('check_in');
            $table->date('check_out');
            $table->integer('adults')->default(2);
            $table->integer('children')->default(0);
            $table->decimal('price', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('source')->nullable(); // booking.com, expedia, etc.
            $table->text('room_details')->nullable();
            $table->boolean('breakfast_included')->default(0);
            $table->boolean('free_cancellation')->default(0);
            $table->string('booking_url')->nullable();
            $table->timestamps();
            
            $table->index(['hotel_id']);
            $table->index(['check_in', 'check_out']);
            $table->index(['currency']);
            $table->index(['source']);
            $table->index(['price']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('rates');
    }
};
