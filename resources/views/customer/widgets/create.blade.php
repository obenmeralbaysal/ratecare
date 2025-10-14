@extends("customer._partials.layout")


@section("content")

    <div class="container">
        <div class="block-header">
            <div class="row clearfix">
                <div class="col-lg-5 col-md-5 col-sm-12">
                    <h2>Last Step > Configure your Widget</h2>
                </div>
            </div>
        </div>

        @include("common.error-dump")

        <div class="row clearfix"></div>
        <div class="col-lg-12">
            <div class="card">
                <div class="body">

                    <h2 class="card-inside-title">Property Script Code</h2>
                    <div class="row clearfix">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" class="form-control"
                                       value="<script>var mainWidget = true; var exitWidget = true;</script><script src=&quot;https://ratecare.net/widget/{{ $main_tr_widget->code }}&quot;></script>"/>
                            </div>
                        </div>
                    </div>

                    Please insert this code before ending of the body tag


                    <h2 class="card-inside-title pt-4">Price Comparison Script Code</h2>
                    <div class="row clearfix">

                        <div class="col-sm-12">
                            <div class="form-group">
                                <input type="text" class="form-control" value="{{ $main_tr_widget->code }}"
                                />
                            </div>
                        </div>
                    </div>

                    <ul class="nav nav-tabs">
                        <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#home">NATIVE LANGUAGE</a></li>
                        <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#profile">ENGLISH</a></li>
                    </ul>
                    <!-- Tab panes -->
                    <form action="{{ $editing ? route('customer.widget.update') : route('customer.widget.store') }}" method="POST">
                        @csrf
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane in active" id="home">
                                <div class="panel-group" id="accordion_1" role="tablist" aria-multiselectable="true">

                                    @include('customer.widgets.languages.native.main')

                                    @include('customer.widgets.languages.native.exit')

                                </div>

                            </div>
                            <div role="tabpanel" class="tab-pane" id="profile">
                                <div class="panel-group" id="accordion_1" role="tablist" aria-multiselectable="true">

                                    @include('customer.widgets.languages.english.main')

                                    @include('customer.widgets.languages.english.exit')

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
@endsection

@section("scripts")
    <script>
        $('.datetimepicker').bootstrapMaterialDatePicker({
            format: 'DD-MM-YYYY',
            clearButton: true,
            weekStart: 1,
            time: false,
            switchOnClick: true
        })

        activation_date = "{{ $main_tr_widget->activation_date }}"
        var dateParts = activation_date.split('-')
        var activation_date_js = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0, 2))

        $('input[name ="activation_date"]').bootstrapMaterialDatePicker('setDate', activation_date_js)

        activation_date_en = "{{ $main_en_widget->activation_date }}"
        var dateParts = activation_date_en.split('-')
        var activation_date_en_js = new Date(dateParts[0], dateParts[1] - 1, dateParts[2].substr(0, 2))

        $('input[name ="activation_date_en"]').bootstrapMaterialDatePicker('setDate', activation_date_en_js)


        $(document).ready(function () {
            CKEDITOR.replace('exit_explanation');
            CKEDITOR.replace('exit_explanation_en');
        });
    </script>
@endsection