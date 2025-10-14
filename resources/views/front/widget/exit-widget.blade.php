<div class="hoteldigilab-exit-background-cover"></div>
<div class="hoteldigilab-top-exit-trigger"></div>

<div class="hoteldigilab-exit-widget" style="font-family: '{{ $exit_widget->font }}', sans-serif !important;">
    <span class="hoteldigilab-close-exit-widget">X</span>

    <div class="hoteldigilab-exit-widget-top-section">
        <div class="hoteldigilab-exit-widget-main-head" style="color: {{ $exit_widget->color }}">
            {{ $exit_widget->main_title }}
        </div>

        <div class="hoteldigilab-exit-widget-sub-text">
            {{ $exit_widget->explanation_text }}
        </div>
    </div>

    <div class="hoteldigilab-exit-widget-middle-section">
        </hr>

        <div class="hoteldigilab-exit-widget-discount-code-title">{{ $exit_widget->promotion_text }}</div>

        <div class="hoteldigilab-exit-widget-discount-code">{{ $exit_widget->promotion_code }}</div>

        <div class="hoteldigilab-best-offers">
            {!! $exit_widget->features_text !!}
        </div>
    </div>

    <a href="{{ $hotel->sabee_url }}">
        <div class="hoteldigilab-exit-widget-bottom-button" style="background-color: #3696C9">
            {{ $exit_widget->reservation_button_text }}
        </div>
    </a>
</div>