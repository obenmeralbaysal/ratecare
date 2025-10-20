<?php

namespace App\Http\Controllers\Customer\Statistic;

use App\Models\Country;
use App\Models\Statistic;
use App\Models\Widget;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticController extends Controller
{
  public function index(Request $request)
  {
    $hotel = user()
      ->hotels()
      ->first();
    $widget = $hotel->widgets()->first();

    $beginDate = "";
    $endDate = "";
    $type = "";
    $departure = "";
    $arrival = "";
    $country = "";

    if ($request->get("filter")) {
      $type = $request->get("type");
      $country = $request->get("country");
      $beginDateInput = $request->get("beginDate");
      $endDateInput = $request->get("endDate");

      $s = Statistic::where('widget_code', $widget->code);

      if ($beginDateInput) {
        $beginDate = date("Y-m-d", strtotime($beginDateInput));

        if ($endDateInput) {
          $endDate = date("Y-m-d", strtotime($endDateInput . "+1 days"));
          $statistics = $s->whereBetween('created_at', [$beginDate, $endDate]);
        } else {
          $statistics = $s->where(function ($query) use ($beginDate) {
            $query->whereDate('created_at', $beginDate);
          });
        }
      }

      if ($country) {
        $statistics = $s->where('country', $country);
      }

      if ($type) {
        $statistics = $s->where('type', $type);
      }

      if ($request->get("arrival")) {
        $arrival = date("Y-m-d", strtotime($request->get("arrival")));

        $statistics = $s->where(function ($query) use ($arrival) {
          $query->whereDate('arrival', $arrival);
        });
      }

      if ($request->get("departure")) {
        $departure = date("Y-m-d", strtotime($request->get("departure")));

        $statistics = $s->where(function ($query) use ($departure) {
          $query->whereDate('departure', $departure);
        });
      }

      $statistics = $s->orderBy("created_at", "DESC")->get();
    } else {
      $statistics = Statistic::where('widget_code', $widget->code)
        ->latest()
        ->take(50)
        ->get();
    }

    $date = DB::select(
      DB::raw(
        "SELECT
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::today()->toDateString() .
          "' AND '" .
          Carbon::tomorrow()->toDateString() .
          "')) AS TODAY,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::yesterday()->toDateString() .
          "' AND '" .
          Carbon::today()->toDateString() .
          "')) AS YESTERDAY,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()->startOfWeek() .
          "' AND '" .
          Carbon::now()->endOfWeek() .
          "')) AS THISWEEK,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()
            ->startOfWeek()
            ->subWeek(1) .
          "' AND '" .
          Carbon::today()->startOfWeek() .
          "')) AS LASTWEEK,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()->startOfMonth() .
          "' AND '" .
          Carbon::now()->endOfMonth() .
          "')) AS THISMONTH,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()
            ->startOfMonth()
            ->subMonth(1) .
          "' AND '" .
          Carbon::today()->startOfMonth() .
          "')) AS LASTMONTH,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()->startOfYear() .
          "' AND '" .
          Carbon::now()->endOfYear() .
          "')) AS THISYEAR,
(SELECT COUNT(id) FROM statistics WHERE widget_code = '$widget->code' AND (created_at BETWEEN '" .
          Carbon::now()
            ->startOfYear()
            ->subYear(1) .
          "' AND '" .
          Carbon::today()->startOfYear() .
          "')) AS LASTYEAR
        "
      )
    );

    $statisticsToday = $date[0]->TODAY;
    $statisticsYesterday = $date[0]->YESTERDAY;
    $statisticsThisWeek = $date[0]->THISWEEK;
    $statisticsLastWeek = $date[0]->LASTWEEK;
    $statisticsThisMonth = $date[0]->THISMONTH;
    $statisticsLastMonth = $date[0]->LASTMONTH;
    $statisticsThisYear = $date[0]->THISYEAR;
    $statisticsLastYear = $date[0]->LASTYEAR;

    $countries = Country::all();

    $beginDate = $beginDate != "" ? date("d-m-Y", strtotime($beginDate)) : "";
    $endDate = $endDate != "" ? date("d-m-Y", strtotime($endDate)) : "";
    $arrival = $arrival != "" ? date("d-m-Y", strtotime($arrival)) : "";
    $departure = $departure != "" ? date("d-m-Y", strtotime($departure)) : "";

    return view('customer.statistics.index')
      ->with("statistics", $statistics)
      ->with("beginDate", $beginDate)
      ->with("endDate", $endDate)
      ->with("arrival", $arrival)
      ->with("departure", $departure)
      ->with("country", $country)
      ->with("type", $type)
      ->with("statisticsToday", shortNumber($statisticsToday))
      ->with("statisticsThisWeek", shortNumber($statisticsThisWeek))
      ->with("statisticsThisMonth", shortNumber($statisticsThisMonth))
      ->with("statisticsThisYear", shortNumber($statisticsThisYear))
      ->with("statisticsYesterday", shortNumber($statisticsYesterday))
      ->with("statisticsLastWeek", shortNumber($statisticsLastWeek))
      ->with("statisticsLastMonth", shortNumber($statisticsLastMonth))
      ->with("statisticsLastYear", shortNumber($statisticsLastYear))
      ->with("countries", $countries);
  }
}
