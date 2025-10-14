<?php

use Core\Schema;

return new class {
    /**
     * Run the migration
     */
    public function up($db)
    {
        Schema::create('invites', function ($table) {
            $table->id();
            $table->string('email');
            $table->string('code', 64)->unique();
            $table->bigInteger('reseller_id')->default(0);
            $table->bigInteger('invited_by');
            $table->boolean('accepted')->default(0);
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();
            
            $table->index(['email']);
            $table->index(['code']);
            $table->index(['reseller_id']);
            $table->index(['invited_by']);
        });
    }
    
    /**
     * Reverse the migration
     */
    public function down($db)
    {
        Schema::drop('invites');
    }
};
