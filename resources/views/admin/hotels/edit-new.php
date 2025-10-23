@extends('layouts.admin-new')

@section('title', 'Property Setup')

@section('content')
<div class="block-header">
    <div class="row clearfix mb-3">
        <div class="col-lg-5 col-md-5 col-sm-12">
            <h2 class="float-left">Property Setup</h2>
        </div>
        <div class="col-lg-7 col-md-7 col-sm-12">
            <ul class="breadcrumb float-md-right padding-0">
                <li><a href="<?php echo url('/dashboard'); ?>"><i class="zmdi zmdi-home"></i></a></li>
                <li><a href="<?php echo url('/admin/users'); ?>">Users</a></li>
                <li class="active">Property Setup</li>
            </ul>
        </div>
    </div>
</div>

<?php if(flash('error')): ?>
    <div class="alert alert-danger"><?php echo flash('error'); ?></div>
<?php endif; ?>

<?php if(flash('success')): ?>
    <div class="alert alert-success"><?php echo flash('success'); ?></div>
<?php endif; ?>

<div class="row clearfix">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="card">
            <div class="body">
                <form method="POST" action="<?php echo url('/admin/hotels/update/' . ($hotel['id'] ?? '')); ?>">
                    <?php echo csrfField(); ?>

                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Property Name</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="name"
                                               placeholder="Example Hotel"
                                               value="<?php echo htmlspecialchars($hotel['name'] ?? ''); ?>" required/>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <h2 class="card-inside-title">Website Url</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="web_url"
                                               placeholder="https://example.com"
                                               value="<?php echo htmlspecialchars($hotel['web_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Opening Language</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <select class="form-control" name="opening_language">
                                            <option value="auto" <?php echo ($hotel['opening_language'] ?? '') == 'auto' ? 'selected' : ''; ?>>Auto</option>
                                            <option value="native" <?php echo ($hotel['opening_language'] ?? '') == 'native' ? 'selected' : ''; ?>>Native</option>
                                            <option value="english" <?php echo ($hotel['opening_language'] ?? '') == 'english' ? 'selected' : ''; ?>>English</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <hr>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h2 class="card-inside-title">Default IBE (Internet Booking Engine)</h2>
                            <div class="form-group">
                                <input type="radio" id="sabeeapp" value="sabeeapp" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'sabeeapp' ? 'checked' : ''; ?>>
                                <label for="sabeeapp">SabeeApp</label>
                                <input type="radio" id="reseliva" value="reseliva" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'reseliva' ? 'checked' : ''; ?>>
                                <label for="reseliva">Reseliva</label>
                                <input type="radio" id="hotelrunner" value="hotelrunner" name="default_ibe" <?php echo ($hotel['default_ibe'] ?? '') == 'hotelrunner' ? 'checked' : ''; ?>>
                                <label for="hotelrunner">HotelRunner</label>
                            </div>
                        </div>
                    </div>

                    <!-- SabeeApp Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">SabeeApp Hotel ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="sabee_hotel_id"
                                               placeholder="Hotel ID"
                                               value="<?php echo htmlspecialchars($hotel['sabee_hotel_id'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="card-inside-title">SabeeApp URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="sabee_is_active" value="1" <?php echo ($hotel['sabee_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="sabee_url"
                                               placeholder="https://ibe.sabeeapp.com/properties/Example-Hotel-booking/?p=bSpf44a337ea1a30a74"
                                               value="<?php echo htmlspecialchars($hotel['sabee_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reseliva Section -->
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Reseliva Hotel ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="checkbox" name="reseliva_is_active" value="1" <?php echo ($hotel['reseliva_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="reseliva_hotel_id"
                                               placeholder="7813" 
                                               value="<?php echo htmlspecialchars($hotel['reseliva_hotel_id'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="card-inside-title">HotelRunner URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <input type="checkbox" name="is_hotelrunner_active" value="1" <?php echo ($hotel['is_hotelrunner_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </div>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="hotelrunner_url"
                                               placeholder="https://hotel.hotelrunner.com" 
                                               value="<?php echo htmlspecialchars($hotel['hotelrunner_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Booking Platforms -->
                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Booking.com URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="booking_is_active" value="1" <?php echo ($hotel['booking_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="booking_url"
                                               placeholder="https://www.booking.com/hotel/tr/example.tr.html"
                                               value="<?php echo htmlspecialchars($hotel['booking_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Hotels.com URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="hotels_is_active" value="1" <?php echo ($hotel['hotels_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="hotels_url"
                                               placeholder="https://hotels.com/ho211277"
                                               value="<?php echo htmlspecialchars($hotel['hotels_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">TatilSepeti.com URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="tatilsepeti_is_active" value="1" <?php echo ($hotel['tatilsepeti_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="tatilsepeti_url"
                                               placeholder="https://www.tatilsepeti.com/example-hotel-526274"
                                               value="<?php echo htmlspecialchars($hotel['tatilsepeti_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="card-inside-title">Odamax URL</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="odamax_is_active" value="1" <?php echo ($hotel['odamax_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="url" class="form-control" name="odamax_url"
                                               placeholder="https://www.odamax.com/tr/hotel/example-hotel-287883"
                                               value="<?php echo htmlspecialchars($hotel['odamax_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h2 class="card-inside-title">OtelZ Tesis ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="otelz_is_active" value="1" <?php echo ($hotel['otelz_is_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="otelz_url"
                                               placeholder="4532"
                                               value="<?php echo htmlspecialchars($hotel['otelz_url'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h2 class="card-inside-title">ETSTur Hotel ID</h2>
                            <div class="row clearfix mb-3">
                                <div class="col-sm-12">
                                    <label class="form-group">
                                        <input type="checkbox" name="is_etstur_active" value="1" <?php echo ($hotel['is_etstur_active'] ?? false) ? 'checked' : ''; ?>>
                                        Active
                                    </label>
                                    <div class="form-group">
                                        <input type="text" class="form-control" name="etstur_hotel_id"
                                               placeholder="KZSAPT"
                                               value="<?php echo htmlspecialchars($hotel['etstur_hotel_id'] ?? ''); ?>"/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary pull-right">
                                <i class="zmdi zmdi-save"></i> Save Property
                            </button>
                            <a href="<?php echo url('/admin/users'); ?>" class="btn btn-secondary mr-2">
                                <i class="zmdi zmdi-arrow-left"></i> Back to Users
                            </a>
                            <div class="clearfix"></div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
        list-style: none;
    }
    .breadcrumb li {
        display: inline;
        font-size: 14px;
    }
    .breadcrumb li + li:before {
        content: "/";
        padding: 0 5px;
        color: #ccc;
    }
    .breadcrumb li a {
        color: #007bff;
        text-decoration: none;
    }
    .breadcrumb li.active {
        color: #6c757d;
    }
    .card-inside-title {
        font-size: 16px;
        font-weight: 600;
        margin-bottom: 10px;
        color: #333;
    }
    .padding-0 {
        padding: 0;
    }
</style>
@endsection
