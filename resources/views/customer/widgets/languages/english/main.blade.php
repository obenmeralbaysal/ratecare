<div class="panel panel-primary">
    <div class="panel-heading" role="tab" id="headingOne_1">
        <h4 class="panel-title">
            <a role="button" data-toggle="collapse" data-parent="#accordion_1" href="#main_en_widget"
               aria-expanded="true" aria-controls="collapseOne_1">
                Main Widget </a></h4>
    </div>
    <div id="main_en_widget" class="panel-collapse collapse in" role="tabpanel"
         aria-labelledby="headingOne_1">
        <div class="row">
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="card-inside-title">Status</h2>
                        <div class="row clearfix">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select class="form-control show-tick" name="main_status_en">
                                        <option value="1"
                                                @if($main_en_widget->is_active == "1") selected @endif>
                                            Active
                                        </option>
                                        <option value="0"
                                                @if($main_en_widget->is_active == "0") selected @endif>
                                            Passive
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h2 class="card-inside-title">Mobile</h2>
                        <div class="row clearfix">

                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select class="form-control show-tick" name="main_mobile_en">
                                        <option value="1"
                                                @if($main_en_widget->show_mobile == "1") selected @endif>
                                            Show
                                        </option>
                                        <option value="0"
                                                @if($main_en_widget->show_mobile == "0") selected @endif>
                                            Hide
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <h2 class="card-inside-title">OC Duration (s)</h2>
                <div class="row clearfix">

                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="duration_en"
                                   value="{{ $main_en_widget->duration }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <h2 class="card-inside-title">Activation Date</h2>
                <div class="input-group">
                                                                        <span class="input-group-addon">
                                                                            <i class="zmdi zmdi-calendar"></i>
                                                                        </span>
                    <input type="text" name="activation_date_en" class="form-control datetimepicker"
                           placeholder="Choose a Date">
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <h2 class="card-inside-title">Main Title</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="main_title_en"
                                   value="{{ $main_en_widget->main_title }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-6">
                        <h2 class="card-inside-title">Currency</h2>
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <select name="main_currency_en">
                                        @foreach($currencies as $currency)
                                            <option value="{{ $currency->id }}" {{ $main_en_widget->currency_id == $currency->id ? 'selected="selected"' : "" }}>
                                                {{ $currency->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <h2 class="card-inside-title">Minimum Stay</h2>
                        <div class="row clearfix">
                            <div class="col-sm-12">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="minimum_stay_en"
                                           value="{{ $main_en_widget->minimum_stay }}"/>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h2 class="card-inside-title">Reservation Button Text</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control"
                                   name="main_reservation_button_text_en"
                                   value="{{ $main_en_widget->reservation_button_text }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="row">
{{--                    <div class="col-md-6">--}}
{{--                        <h2 class="card-inside-title">Discount Code Percentage</h2>--}}
{{--                        <div class="row clearfix">--}}
{{--                            <div class="col-sm-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <input type="text" class="form-control"--}}
{{--                                           name="main_discount_percentage_en"--}}
{{--                                           value="{{ $main_en_widget->discount_code_percentage }}"/>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}


                </div>
            </div>
        </div>

        <h2 class="card-inside-title">Direct Reservation Text</h2>
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="main_direct_reservation_text_en"
                           value="{{ $main_en_widget->direct_reservation_text }}"/>
                </div>
            </div>
        </div>

        <h2 class="card-inside-title">Features</h2>
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="form-group">
                                                                    <textarea rows="4" class="form-control no-resize"
                                                                              name="features_text_en">{{ $main_en_widget->features_text }}</textarea>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-6">
                <h2 class="card-inside-title">Color</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="main_color_en"
                                   value="{{ $main_en_widget->color }}"/>
                        </div>
                    </div>
                </div>
            </div>

{{--            <div class="col-md-6">--}}
{{--                <h2 class="card-inside-title">Discount (based on channel)</h2>--}}
{{--                <div class="row clearix">--}}
{{--                    <div class="col-md-6 float-left">--}}
{{--                        <div class="row clearfix">--}}

{{--                            <div class="col-sm-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <select class="form-control show-tick"--}}
{{--                                            name="main_discount_type_en">--}}
{{--                                        <option value="1">Percentage</option>--}}
{{--                                        <option value="0">Value</option>--}}
{{--                                    </select>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                    <div class="col-md-6 float-left">--}}
{{--                        <div class="row clearfix">--}}
{{--                            <div class="col-sm-12">--}}
{{--                                <div class="form-group">--}}
{{--                                    <input type="text" class="form-control" name="main_discount_en"--}}
{{--                                           value="{{ $main_en_widget->discount }}"/>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        </div>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--            </div>--}}


        </div>

        <h2 class="card-inside-title">Font</h2>

        <div class="row font-selection">
            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Arial"
                                                @if($main_en_widget->font == "Arial") checked @endif>
                    Arial
                </div>

                <span style="font-family: Arial; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en"
                                                value="Times New Roman"
                                                @if($main_en_widget->font == "Times New Roman") checked @endif>
                    Times New Roman
                </div>

                <span style="font-family: Times New Roman; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Abel"
                                                @if($main_en_widget->font == "Abel") checked @endif >
                    Abel
                </div>

                <span style="font-family: Abel; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Cairo"
                                                @if($main_en_widget->font == "Cairo") checked @endif >
                    Cairo
                </div>

                <span style="font-family: Cairo; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Dosis"
                                                @if($main_en_widget->font == "Dosis") checked @endif >
                    Dosis
                </div>

                <span style="font-family: Dosis; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en"
                                                value="Open Sans Condensed"
                                                @if($main_en_widget->font == "Open Sans Condensed") checked @endif >
                    Open Sans Condensed
                </div>

                <span style="font-family: Open Sans Condensed; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en"
                                                value="PT Sans Narrow"
                                                @if($main_en_widget->font == "PT Sans Narrow") checked @endif >
                    PT Sans Narrow
                </div>

                <span style="font-family: PT Sans Narrow; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Rajdhani"
                                                @if($main_en_widget->font == "Rajdhani") checked @endif >
                    Rajdhani
                </div>

                <span style="font-family: Rajdhani; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Roboto"
                                                @if($main_en_widget->font == "Roboto") checked @endif >
                    Roboto
                </div>

                <span style="font-family: Roboto; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en"
                                                value="Roboto Condensed"
                                                @if($main_en_widget->font == "Roboto Condensed") checked @endif >
                    Roboto Condensed
                </div>

                <span style="font-family: Roboto Condensed; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en"
                                                value="Saira Semi Condensed"
                                                @if($main_en_widget->font == "Saira Semi Condensed") checked @endif >
                    Saira Semi Condensed
                </div>

                <span style="font-family: Saira Semi Condensed; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="main_font_en" value="Teko"
                                                @if($main_en_widget->font == "Teko") checked @endif >
                    Teko
                </div>

                <span style="font-family: Teko; font-size: 32px;">Lorem ipsum</span>

            </div>
        </div>
    </div>
</div>
