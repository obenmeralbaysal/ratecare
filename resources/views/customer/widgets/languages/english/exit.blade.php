<div class="panel panel-primary">
    <div class="panel-heading" role="tab" id="headingTwo_1">
        <h4 class="panel-title">
            <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion_1"
               href="#exit_en_widget" aria-expanded="false"
               aria-controls="collapseTwo_1"> Exit Widget </a></h4>
    </div>
    <div id="exit_en_widget" class="panel-collapse collapse" role="tabpanel"
         aria-labelledby="headingTwo_1">
        <h2 class="card-inside-title">Status</h2>
        <div class="row clearfix">

            <div class="col-sm-12">
                <div class="form-group">
                    <select class="form-control show-tick" name="exit_status_en">
                        <option value="1" @if($exit_en_widget->is_active == "1") selected @endif>
                            Active
                        </option>
                        <option value="0" @if($exit_en_widget->is_active == "0") selected @endif>
                            Passive
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <h2 class="card-inside-title">Main Title</h2>
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="form-group">
                    <input type="text" class="form-control" name="exit_main_title_en"
                           value="{{ $exit_en_widget->main_title }}"/>
                </div>
            </div>
        </div>

        <h2 class="card-inside-title">Explanation</h2>
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="form-group">
                                                                    <textarea rows="4" class="form-control no-resize editor"
                                                                              name="exit_explanation_en">{{ $exit_en_widget->explanation_text }}</textarea>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-6">
                <h2 class="card-inside-title">Promotion Text</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="exit_promotion_text_en"
                                   value="{{ $exit_en_widget->promotion_text }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h2 class="card-inside-title">Promotion Code</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="exit_promotion_code_en"
                                   value="{{ $exit_en_widget->promotion_code }}"/>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <h2 class="card-inside-title">Features</h2>
        <div class="row clearfix">
            <div class="col-sm-12">
                <div class="form-group">
                                                                    <textarea rows="4" class="form-control no-resize"
                                                                              name="exit_features_en">{{ $exit_en_widget->features_text }}</textarea>
                </div>
            </div>
        </div>

        <div class="row">

            <div class="col-md-6">
                <h2 class="card-inside-title">Reservation Button Text</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="exit_reservation_button_en"
                                   value="{{ $exit_en_widget->reservation_button_text }}"/>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <h2 class="card-inside-title">Color</h2>
                <div class="row clearfix">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <input type="text" class="form-control" name="exit_color_en"
                                   value="{{ $exit_en_widget->color }}"/>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <h2 class="card-inside-title">Font</h2>

        <div class="row font-selection">
            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Arial"
                                                @if($exit_en_widget->font == "Arial") checked @endif>
                    Arial
                </div>

                <span style="font-family: Arial; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en"
                                                value="Times New Roman"
                                                @if($exit_en_widget->font == "Times New Roman") checked @endif>
                    Times New Roman
                </div>

                <span style="font-family: Times New Roman; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Abel"
                                                @if($exit_en_widget->font == "Abel") checked @endif >
                    Abel
                </div>

                <span style="font-family: Abel; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Cairo"
                                                @if($exit_en_widget->font == "Cairo") checked @endif >
                    Cairo
                </div>

                <span style="font-family: Cairo; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Dosis"
                                                @if($exit_en_widget->font == "Dosis") checked @endif >
                    Dosis
                </div>

                <span style="font-family: Dosis; font-size: 32px;">Lorem ipsum</span>

            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en"
                                                value="Open Sans Condensed"
                                                @if($exit_en_widget->font == "Open Sans Condensed") checked @endif >
                    Open Sans Condensed
                </div>

                <span style="font-family: Open Sans Condensed; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en"
                                                value="PT Sans Narrow"
                                                @if($exit_en_widget->font == "PT Sans Narrow") checked @endif >
                    PT Sans Narrow
                </div>

                <span style="font-family: PT Sans Narrow; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Rajdhani"
                                                @if($exit_en_widget->font == "Radjhani") checked @endif >
                    Rajdhani
                </div>

                <span style="font-family: Rajdhani; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Roboto"
                                                @if($exit_en_widget->font == "Roboto") checked @endif >
                    Roboto
                </div>

                <span style="font-family: Roboto; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en"
                                                value="Roboto Condensed"
                                                @if($exit_en_widget->font == "Roboto Condensed") checked @endif >
                    Roboto Condensed
                </div>

                <span style="font-family: Roboto Condensed; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en"
                                                value="Saira Semi Condensed"
                                                @if($exit_en_widget->font == "Saira Semi Condensed") checked @endif >
                    Saira Semi Condensed
                </div>

                <span style="font-family: Saira Semi Condensed; font-size: 32px;">Lorem ipsum</span>
            </div>

            <div class="col-md-3">
                <div class="font-header"><input type="radio" name="exit_font_en" value="Teko"
                                                @if($exit_en_widget->font == "Teko") checked @endif >
                    Teko
                </div>

                <span style="font-family: Teko; font-size: 32px;">Lorem ipsum</span>
            </div>
        </div>
    </div>
</div>