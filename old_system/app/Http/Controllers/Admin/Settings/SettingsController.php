<?php

namespace App\Http\Controllers\Admin\Settings;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Artisan;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index(){
        $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
        $isWidgetsOpen = Setting::where("key", "widgets-open")->firstOrFail();
        return view("admin.settings.index")
            ->with("widgetsOpen", $isWidgetsOpen->value)
            ->with("cachingTime", $cachingTime->value);
    }

    public function clearCache(){
        Artisan::call('cache:clear');
        return back()->with("success", "Caches are cleared!");
    }

    public function setWidgetsStatus(){
        $setting = Setting::where("key", "widgets-open");
        $isWidgetsOpen = $setting->firstOrFail();
        if($isWidgetsOpen->value == "true"){
            $setting->update(['value' => 'false']);
        }

        else{
            $setting->update(['value' => 'true']);
        }

        return back()->with("success", "Successfully set!");
    }

    public function setCachingTime(Request $request){
        if($request->has("cache-time")){
            Setting::where("key", "caching-time")->update(['value' => $request->get("cache-time")]);
            return back()->with("success", "Caching time set!");
        }

        else{
            return back()->with("error", "Caching time field is empty!");
        }
    }
}
