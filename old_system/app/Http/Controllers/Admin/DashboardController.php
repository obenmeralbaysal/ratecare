<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Models\Widget;
use App\Models\Hotel;

class DashboardController extends Controller
{
    public function index(){
        $userCount = count(User::all());
        $widgetCount = count(Widget::all());
        $hotelCount = count(Hotel::all());

        return view("admin.dashboard")
            ->with("userCount", $userCount)
            ->with("widgetCount", $widgetCount)
            ->with("hotelCount", $hotelCount);
    }
}
