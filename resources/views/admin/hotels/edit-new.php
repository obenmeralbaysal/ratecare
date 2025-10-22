@extends('layouts.admin-new')

@section('title', 'Edit Hotel')

@section('content')
<div class="row">
    <div class="col-md-10 offset-md-1">
        <div class="card">
            <div class="card-header">
                <h4>Edit Hotel Settings</h4>
            </div>
            <div class="card-body">
                <form method="POST" action="<?php echo url('/admin/hotels/update/' . ($hotel['id'] ?? '')); ?>">
                    <?php echo csrfField(); ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hotel Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo $hotel['name'] ?? ''; ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Country</label>
                                <input type="text" name="country" class="form-control" value="<?php echo $hotel['country'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>City</label>
                                <input type="text" name="city" class="form-control" value="<?php echo $hotel['city'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Currency</label>
                                <select name="currency" class="form-control">
                                    <option value="TRY">TRY</option>
                                    <option value="EUR">EUR</option>
                                    <option value="USD">USD</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 mb-3">Platform URLs</h5>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Booking.com URL</label>
                                <input type="text" name="booking_url" class="form-control" value="<?php echo $hotel['booking_url'] ?? ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Hotels.com URL</label>
                                <input type="text" name="hotels_url" class="form-control" value="<?php echo $hotel['hotels_url'] ?? ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="zmdi zmdi-check"></i> Update Hotel
                        </button>
                        <a href="<?php echo url('/admin/users/switch/' . ($hotel['user_id'] ?? '')); ?>" class="btn btn-secondary">
                            <i class="zmdi zmdi-arrow-left"></i> Back
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
