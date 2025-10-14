<?php

namespace App\Http\Controllers\Customer\Widget;

use App\Http\Requests\WidgetStoreRequest;
use App\Models\Currency;
use App\Models\Hotel;
use App\Models\Language;
use App\Models\Widget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class WidgetController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    public function index()
    {
        $widgets = Widget::all();

        return view("customer.widgets.index")->with("widgets", $widgets);
    }

    public function create()
    {
        $hotel = user()->hotels()->first();
        $hotels = Hotel::all();


        if(!$hotel)
            return view("customer.hotels.create")->with("editing", false)->with("hotels", $hotels)->with("hotel", new Hotel())->with("warning", "Please set up your hotels credentials");

        return view("customer.widgets.create")->with("editing", false);
    }

    public function store(WidgetStoreRequest $request)
    {
        $hotel = user()->hotels()->first();

        $hotel->widgets()->create([
            'type' => 'main'
        ]);

        return back()->with('success', 'Successfully saved !');
    }

    public function edit()
    {
        $hotel = user()->hotels()->first();
        $hotels = Hotel::all();

        if(!$hotel)
            return view("customer.hotels.create")
                ->with("editing", false)
                ->with("hotels", $hotels)
                ->with("hotel", new Hotel())
                ->with("warning", "Please set up your hotels credentials");

        $main_tr_widget = $hotel->widgets()->where("language_id", 1)->where("type", "main")->first();
        $main_en_widget = $hotel->widgets()->where("language_id", 2)->where("type", "main")->first();
        $exit_tr_widget = $hotel->widgets()->where("language_id", 1)->where("type", "exit")->first();
        $exit_en_widget = $hotel->widgets()->where("language_id", 2)->where("type", "exit")->first();

        $code = generateWidgetCode();

        if($main_tr_widget == null){
            $main_tr_widget = new Widget();
            $main_tr_widget->language_id = 1;
            $main_tr_widget->type = "main";
            $main_tr_widget->code = $code;

            $main_tr_widget->duration = 5;
            $main_tr_widget->is_active = 1;
            $main_tr_widget->main_title = "En İyi Fiyat Garantisi. Güvenli Online Rezervasyon!";
            $main_tr_widget->reservation_button_text = "REZERVASYON";
            $main_tr_widget->direct_reservation_text = "En İyi Fiyat için Tıkla:";
            $main_tr_widget->features_text = "<ul><li>En İyi Fiyat Garantisi</li><li>Güvenli Online Rezervasyon</li><li>Ücretsiz Kahvaltı ve Wi-Fi</li></ul>";
            $main_tr_widget->color = "#740202";
            $main_tr_widget->discount = 5;
            $main_tr_widget->minimum_stay = 1;
            $main_tr_widget->discount_type = 1;
            $main_tr_widget->currency_id = 3;

            $hotel->widgets()->save($main_tr_widget);
        }

        if($main_en_widget == null){
            $main_en_widget = new Widget();
            $main_en_widget->language_id = 2;
            $main_en_widget->type = "main";
            $main_en_widget->code = $code;

            $main_en_widget->duration = 5;
            $main_en_widget->is_active = 1;
            $main_en_widget->main_title = "Best Rate Guaranteed. Don't Miss the Opportunity!";
            $main_en_widget->reservation_button_text = "RESERVATION";
            $main_en_widget->direct_reservation_text = "Book Now for";
            $main_en_widget->features_text = "<ul><li>Best Rate Guaranteed</li><li>Free Breakfast</li><li>Free Wifi</li></ul>";
            $main_en_widget->color = "#740202";
            $main_en_widget->discount = 5;
            $main_en_widget->discount_type = 1;
            $main_en_widget->minimum_stay = 1;
            $main_en_widget->currency_id = 3;

            $hotel->widgets()->save($main_en_widget);
        }

        if($exit_tr_widget == null){
            $exit_tr_widget = new Widget();
            $exit_tr_widget->language_id = 1;
            $exit_tr_widget->type = "exit";
            $exit_tr_widget->code = $code;

            $exit_tr_widget->is_active = 1;
            $exit_tr_widget->main_title = "Sitemizden Ayrılmak İstediğinize Emin Misiniz?";
            $exit_tr_widget->explanation_text = "Web sitemiz üzerinden yapacağınız güvenli online rezervasyon ile her zaman en iyi fiyat garantisini yakalayacağınızı hatırlatmak isteriz. Sizi aramızda görmek adına yapacağınız tüm rezervasyonlarda geçerli olmak üzere %5 değerindeki indirim kuponuna ait bilgileri aşağıda bulabilirsiniz. Sadece www.fudaotel.com.tr sitemiz üzerinden yapılacak online rezervasyonlar için geçerlidir.";
            $exit_tr_widget->features_text = "<ul><li>En İyi Fiyat Garantisi</li><li>Güvenli Online Rezervasyon</li><li>Ücretsiz Kahvaltı ve Wi-Fi</li></ul>";
            $exit_tr_widget->reservation_button_text = "REZERVE ET";
            $exit_tr_widget->promotion_text = "İndirim Kodunuz";
            $exit_tr_widget->promotion_code = "LOVEISTANBUL";
            $exit_tr_widget->color = "#740202";


            $hotel->widgets()->save($exit_tr_widget);
        }

        if($exit_en_widget == null){
            $exit_en_widget = new Widget();
            $exit_en_widget->language_id = 2;
            $exit_en_widget->type = "exit";
            $exit_en_widget->code = $code;

            $exit_en_widget->is_active = 1;
            $exit_en_widget->main_title = "Hey, Don't Leave!";
            $exit_en_widget->explanation_text = "We have a special offer for you. Please use the below discount code to get your special rate for your accommodation in Istanbul. Only available for bookings made on our official website.";
            $exit_en_widget->features_text = "<ul><li>Best Rate Guaranteed</li><li>Free Breakfast</li><li>Free Wifi</li></ul>";
            $exit_en_widget->reservation_button_text = "Book Now";
            $exit_en_widget->promotion_text = "Discount Code";
            $exit_en_widget->promotion_code = "LOVEISTANBUL";
            $exit_en_widget->color = "#740202";

            $hotel->widgets()->save($exit_en_widget);
        }

        $currencies = Currency::all();


        return view("customer.widgets.create")
            ->with("editing", true)
            ->with("currencies", $currencies)
            ->with("main_tr_widget", $main_tr_widget)
            ->with("exit_tr_widget", $exit_tr_widget)
            ->with("main_en_widget", $main_en_widget)
            ->with("exit_en_widget", $exit_en_widget);
    }

    public function update(WidgetStoreRequest $request)
    {
        $hotel = user()->hotels()->first();
        $hotels = Hotel::all();


        if(!$hotel)
            return view("customer.hotels.create")->with("editing", false)->with("warning", "Please set up your hotels credentials")->with("hotels", $hotels);

        $main_tr_widget = $hotel->widgets()->where("language_id", 1)->where("type", "main")->first();
        $main_en_widget = $hotel->widgets()->where("language_id", 2)->where("type", "main")->first();
        $exit_tr_widget = $hotel->widgets()->where("language_id", 1)->where("type", "exit")->first();
        $exit_en_widget = $hotel->widgets()->where("language_id", 2)->where("type", "exit")->first();

        $main_tr_widget->duration = $request->get("duration");
        $main_tr_widget->is_active = $request->get("main_status");
        $main_tr_widget->show_mobile = $request->get("main_mobile");
        $main_tr_widget->main_title = $request->get("main_title");
        $main_tr_widget->reservation_button_text = $request->get("main_reservation_button_text");
        $main_tr_widget->direct_reservation_text = $request->get("main_direct_reservation_text");
        $main_tr_widget->features_text = $request->get("features_text");
        $main_tr_widget->color = $request->get("main_color");
        $main_tr_widget->discount = $request->get("main_discount");
        $main_tr_widget->discount_type = $request->get("main_discount_type");
        $main_tr_widget->currency_id = $request->get("main_currency");
        $main_tr_widget->font = $request->get("main_font");
        $main_tr_widget->minimum_stay = $request->get("minimum_stay");
//        $main_tr_widget->discount_code = $request->get("main_discount_code");
        $main_tr_widget->discount_code_percentage = $request->get("main_discount_code_percentage");
        $main_tr_widget->activation_date = Carbon::parse($request->get("activation_date"))->format('Y-m-d');


        $hotel->widgets()->save($main_tr_widget);

        $main_en_widget->duration = $request->get("duration_en");
        $main_en_widget->is_active = $request->get("main_status_en");
        $main_en_widget->show_mobile = $request->get("main_mobile_en");
        $main_en_widget->main_title = $request->get("main_title_en");
        $main_en_widget->reservation_button_text = $request->get("main_reservation_button_text_en");
        $main_en_widget->direct_reservation_text = $request->get("main_direct_reservation_text_en");
        $main_en_widget->features_text = $request->get("features_text_en");
        $main_en_widget->color = $request->get("main_color_en");
        $main_en_widget->discount = $request->get("main_discount_en");
        $main_en_widget->discount_type = $request->get("main_discount_type_en");
        $main_en_widget->currency_id = $request->get("main_currency_en");
        $main_en_widget->font = $request->get("main_font_en");
        $main_en_widget->minimum_stay = $request->get("minimum_stay_en");
//        $main_en_widget->discount_code = $request->get("main_discount_code_en");
        $main_en_widget->discount_code_percentage = $request->get("main_discount_code_percentage_en");
        $main_en_widget->activation_date = Carbon::parse($request->get("activation_date_en"))->format('Y-m-d');


        $hotel->widgets()->save($main_en_widget);

        $exit_tr_widget->is_active = $request->get('exit_status');
        $exit_tr_widget->main_title = $request->get('exit_main_title');
        $exit_tr_widget->explanation_text = $request->get('exit_explanation');
        $exit_tr_widget->features_text = $request->get('exit_features');
        $exit_tr_widget->reservation_button_text = $request->get('exit_reservation_button');
        $exit_tr_widget->promotion_text = $request->get('exit_promotion_text');
        $exit_tr_widget->promotion_code = $request->get('exit_promotion_code');
        $exit_tr_widget->color = $request->get('exit_color');
        $exit_tr_widget->font = $request->get('exit_font');

        $hotel->widgets()->save($exit_tr_widget);

        $exit_en_widget->is_active = $request->get('exit_status_en');
        $exit_en_widget->main_title = $request->get('exit_main_title_en');
        $exit_en_widget->explanation_text = $request->get('exit_explanation_en');
        $exit_en_widget->features_text = $request->get('exit_features_en');
        $exit_en_widget->reservation_button_text = $request->get('exit_reservation_button_en');
        $exit_en_widget->promotion_text = $request->get('exit_promotion_text_en');
        $exit_en_widget->promotion_code = $request->get('exit_promotion_code_en');
        $exit_en_widget->color = $request->get('exit_color_en');
        $exit_en_widget->font = $request->get('exit_font_en');

        $hotel->widgets()->save($exit_en_widget);

        return back()->with("success", "Successfully saved !");
    }

    public function destroy($id)
    {
        //
    }
}
