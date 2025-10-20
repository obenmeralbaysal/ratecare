<?php

use App\Libraries\SabeeClient;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Setting;

use SebastianBergmann\CodeCoverage\Driver\Xdebug;

use function GuzzleHttp\json_decode;

function user($force_recheck = false)
{
  static $user = null;

  if (is_null($user) || $force_recheck) {
    $user = request()->user();

    if (!$user) {
      $user = new User();
    }
  }

  return $user;
}

function hotel()
{
  return user()->hotels();
}

function loggedIn($force_recheck = false)
{
  return user()->id;
}

/* ------------------------------------------------------------------------------------------------------------------------- switch To Sub User -+- */
/**
 * Switch to a sub user of a reseller or an admin.
 *
 * @param $subUserId
 *
 * @return false
 */
function switchToSubUser($subUserId)
{
  $subUser = User::findOrFail($subUserId);

  // resellers can only switch to their sub users
  // while admins can switch to any user
  if (isReseller() && $subUser->reseller_id != user()->id) {
    return false;
  }

  // save original super user to the session so we can revert it
  session()->put('original-superuser-id', user()->id);

  return Auth::loginUsingId($subUser->id);
}

/* ------------------------------------------------------------------------------------------------------------------ switch Back To Super User -+- */
/**
 * Reverse of the `switchToSubUser()` function.
 * Upon switching to a sub user, super user is logged to the session, and is reverted here.
 *
 * @return false
 */
function switchBackToSuperUser()
{
  $superUser = getImpersonatingSuperUser();

  if (!$superUser) {
    return false;
  }

  session()->forget('original-superuser-id');

  return Auth::loginUsingId($superUser->id);
}

/* --------------------------------------------------------------------------------------------------------------- get Impersonating Super User -+- */
/**
 * If a super user has temporarily switched to a sub user _(logged in as a sub user)_ using `switchToSubUser()` function,
 * this function will return the original super user.
 *
 * Otherwise this function will return false.
 *
 * @return User|false
 */
function getImpersonatingSuperUser()
{
  static $superUser = null;

  if ($superUser !== null) {
    return $superUser;
  }

  $superUserId = session()->get('original-superuser-id');

  if (!$superUserId) {
    return false;
  }

  $superUser = User::find($superUserId);

  return $superUser;
}

/* -------------------------------------------------------------------------------------------------------------------------------- is Reseller -+- */
/**
 * Check if the user is a reseller
 *
 *
 * @param  int|User  $id  if this is left blank, current logged in user will be taken
 *
 * @return bool
 */

function isReseller($id = null)
{
  if ($id === null) {
    $user = $id === null ? user() : User::findOrFail($id);
  } else {
    if (is_object($id)) {
      $user = $id;
    } else {
      $user = user();
    }
  }

  return $user->user_type === 2;
}

function generateWidgetCode()
{
  $chars = "abcdefghijkmnopqrstuvwxyz023456789";
  srand((float) microtime() * 1000000);
  $i = 0;
  $pass = '';

  while ($i <= 10) {
    $num = rand() % 33;
    $tmp = substr($chars, $num, 1);
    $pass = $pass . $tmp;
    $i++;
  }

  return $pass;
}

function gen_uuid()
{
  return sprintf(
    '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
    // 32 bits for "time_low"
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),

    // 16 bits for "time_mid"
    mt_rand(0, 0xffff),

    // 16 bits for "time_hi_and_version",
    // four most significant bits holds version number 4
    mt_rand(0, 0x0fff) | 0x4000,

    // 16 bits, 8 bits for "clk_seq_hi_res",
    // 8 bits for "clk_seq_low",
    // two most significant bits holds zero and one for variant DCE1.1
    mt_rand(0, 0x3fff) | 0x8000,

    // 48 bits for "node"
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff),
    mt_rand(0, 0xffff)
  );
}


function getHTML($url, $timeout, $type = 0)
{
  $header = [];
  $header[0] = "Accept: text/xml,application/xml,application/xhtml+xml,";
  $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
  $header[0] = "Cache-Control: max-age=0";
  $header[] = "Connection: keep-alive";
  $header[] = "Keep-Alive: 300";
  $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
  $header[] = "Pragma: "; // browsers keep this blank.

  $ch = curl_init($url); // initialize curl with given url
  curl_setopt(
    $ch,
    CURLOPT_USERAGENT,
    "Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0"
  ); // set  useragent
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // write the response to a variable
  curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // follow redirects if any
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout); // max. seconds to execute
  curl_setopt($ch, CURLOPT_FAILONERROR, 1); // stop when it encounters an error
  curl_setopt($ch, CURLOPT_COOKIESESSION, true);
  curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

  if ($type == 2) {
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_TIMEOUT, 0);
    curl_setopt(
      $ch,
      CURLOPT_USERAGENT,
      'Mozilla/5.0 (Windows NT 10; Win64; x64; rv:56.0) Gecko/20100101 Firefox/56.0'
    );
    curl_setopt($ch, CURLOPT_POST, 0);
    curl_setopt($ch, CURLOPT_PROXY, 'brd.superproxy.io:22225');
    curl_setopt(
      $ch,
      CURLOPT_PROXYUSERPWD,
      'brd-customer-hl_e5f2315f-zone-datacenter_proxy1-country-nl:uvmqoi66peju'
    );
  }

  return @curl_exec($ch);
}

function search($begin, $end, $text)
{
  @preg_match_all(
    '/' . preg_quote($begin, '/') . '(.*?)' . preg_quote($end, '/') . '/i',
    $text,
    $m
  );

  return @$m[1];
}

function getBookingUrl($url, $currency, $checkinDate, $checkoutDate)
{
  if (substr($url, -8) != ".tr.html") {
    $url = str_replace(".html", ".tr.html", $url);
  }

  $search_url =
    $url .
    "?selected_currency=" .
    $currency .
    "&checkin=" .
    $checkinDate .
    "&checkout=" .
    $checkoutDate .
    "";

  return $search_url;
}

function getBookingPrice($url, $currency, $checkinDate, $checkoutDate)
{
  if (substr($url, -8) != ".tr.html") {
    $url = str_replace(".html", ".tr.html", $url);
  }

  $cacheKey = md5("$url-$currency-$checkinDate,$checkoutDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $url,
    $currency,
    $checkinDate,
    $checkoutDate
  ) {
    $search_url =
      $url .
      "?selected_currency=" .
      $currency .
      "&checkin=" .
      $checkinDate .
      "&checkout=" .
      $checkoutDate .
      "";

    $html = getHTML($search_url,30,2);

    switch ($currency) {
      case "EUR":
        $currency_symbol = "€";
        break;
      case "USD":
        $currency_symbol = "US$";
        break;
      default:
        $currency_symbol = "TL";
    }

    $price = search('"b_price":"' . $currency_symbol, '"', $html);

    $alternativePrice = search("tarihlerinizde", "gibi", $html);

    if ($price != []) {
      return round(
        preg_replace('/\xc2\xa0/', "", str_replace(".", "", trim($price[0])))
      );
    } else {
      if ($alternativePrice != []) {
        switch ($currency) {
          case "EUR":
            $alternativePrice = search(
              "tarihlerinizde \xE2\x82\xAc",
              "gibi",
              $html
            );
            if ($alternativePrice == []) {
              return "NA";
            }

            return round(
              preg_replace(
                '/\xc2\xa0/',
                "",
                str_replace(".", "", trim($alternativePrice[0]))
              )
            );
          case "USD":
            $alternativePrice = search("tarihlerinizde US$", "gibi", $html);
            if ($alternativePrice == []) {
              return "NA";
            }

            return round(
              preg_replace(
                '/\xc2\xa0/',
                "",
                str_replace(".", "", trim($alternativePrice[0]))
              )
            );
          default:
            $alternativePrice = search("tarihlerinizde TL", "gibi", $html);
            if ($alternativePrice == []) {
              return "NA";
            }

            return round(
              preg_replace(
                '/\xc2\xa0/',
                "",
                str_replace(".", "", trim($alternativePrice[0]))
              )
            );
        }
      } else {
        return "NA";
      }
    }
  });

  return $price;
}

function get_currency()
{
  $currency_xml = simplexml_load_file(
    'http://www.tcmb.gov.tr/kurlar/today.xml'
  );
  $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
  $usdSelling = $currency_xml->Currency[0]->BanknoteBuying;

  return $currency_xml;
}

function getSabeeRates($sabeeHotelId)
{
  $sabeeApp = new SabeeClient(ENV("SABEE_API_KEY"));
  $hotelInventory = $sabeeApp->hotelInventory();
  $hotels = $hotelInventory->rawResponse->data->hotels;
  $hotelIndex = array_search(
    $sabeeHotelId,
    array_column($hotelInventory->rawResponse->data->hotels, 'hotel_id')
  );
  $rateplans = $hotels[$hotelIndex]->rateplans;

  return $rateplans;
}

function getSabeeRooms($sabeeHotelId)
{
  $sabeeApp = new SabeeClient(ENV("SABEE_API_KEY"));
  $hotelInventory = $sabeeApp->hotelInventory();
  if (!$hotelInventory->rawResponse->success) {
    return "";
  }

  $hotels = $hotelInventory->rawResponse->data->hotels;
  $hotelIndex = array_search(
    $sabeeHotelId,
    array_column($hotelInventory->rawResponse->data->hotels, 'hotel_id')
  );
  $roomTypes = $hotels[$hotelIndex]->room_types;

  return $roomTypes;
}

function getReselivaPriceApi($hotelID, $currency, $startDate, $endDate)
{
  $username = "uKucukOteller";
  $passwd = "138Rs!5g8SD";

  $headers = [
    'Content-Type: application/x-www-form-urlencoded',
    'Authorization: Basic ' . base64_encode("$username:$passwd"),
  ];

  $data = [
    'api_version' => 1,
    'start_date' => $startDate,
    'end_date' => $endDate,
    'hotels' => '[{"ta_id":1,"partner_id":' . $hotelID . '}]',
    'currency' => $currency,
    'user_country' => "TR",
    'device_type' => "d",
    'party' => '[{"adults":2, "children":[]}]',
    'source' => "kucukotellercomtr",
  ];

  $postData = http_build_query($data);

  $ch = curl_init(
    'https://www.reseliva.com/siteBase/REST/kucukotellercomtr/service/hotel_availability'
  );
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  $decodedResponse = json_decode($response);

  if ($decodedResponse->num_hotels > 0) {
    $roomTypes = $decodedResponse->hotels[0]->room_types;
    $prices = [];

    foreach ($roomTypes as $roomType) {
      array_push($prices, [
        "price" => round($roomType->final_price),
        "url" => $roomType->url,
      ]);
    }

    $minPrice = min($prices);

    return $minPrice;
  }

  return "NA";
}

//function getETSTurPrice($hotelID, $currency, $startDate, $endDate)
//{
//  $postData = [
//    "hotelId" => $hotelID,
//    "checkIn" => $startDate,
//    "checkOut" => $endDate,
//    "adults" => 2,
//    "currency" => $currency,
//  ];
//  $postData = json_encode($postData);
//
//  $ch = curl_init('https://mapi.etstur.com/api/kucukoteller/availability');
//  curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
//  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
//  curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
//  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//
//  if (curl_exec($ch) == "") {
//    return ['price' => "", 'url' => ""];
//  }
//
//  $response = json_decode(curl_exec($ch));
//
//  return ['price' => round($response->totalRate), 'url' => $response->deeplink];
//}




function getReselivaPrice($url, $currency, $startDate, $endDate)
{
  $cacheKey = md5("$url-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $url,
    $currency,
    $startDate,
    $endDate
  ) {
    $url .= "?&pCurrency=" . $currency;

    $startDate = date_create($startDate);
    $endDate = date_create($endDate);

    $data = [
      'pCheckInDate' => date_format($startDate, 'd/m/Y'),
      'pCheckOutDate' => date_format($endDate, 'd/m/Y'),
      'numRooms' => 1,
      'numAdults' => 2,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $result = curl_exec($ch);
    $prices = search('<b class="p_price_c">', '</b>', $result);

    if ($prices != []) {
      $price = str_replace([" TL", " US$", " €"], "", $prices[0]);
      $price = str_replace(",", ".", $price);
      $price = round($price, 0);

      if ($price == 0.0) {
        return "NA";
      }

      return $price;
    } else {
      return "NA";
    }
  });

  return $price;
}

function getOdamaxUrl($url, $currency, $startdate, $enddate)
{
  $startdate = date("d.m.Y", strtotime($startdate));
  $enddate = date("d.m.Y", strtotime($enddate));

  $search_url = "$url?check_in=$startdate&check_out=$enddate&adult_1=2&currency=$currency";

  return $search_url;
}

function getOdamaxPrice($url, $currency, $startDate, $endDate)
{
  $cacheKey = md5("$url-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $url,
    $currency,
    $startDate,
    $endDate
  ) {
    $startDate = date("d.m.Y", strtotime($startDate));
    $endDate = date("d.m.Y", strtotime($endDate));

    if (strpos($url, 'kucukoteller') !== false) {
      $search_url = "$url&check_in=$startDate&check_out=$endDate&adult_1=2&type=HOTEL&currency=$currency";
    } else {
      $search_url = "$url?check_in=$startDate&check_out=$endDate&adult_1=2&type=HOTEL&currency=$currency";
    }

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $search_url);
    curl_setopt($ch, CURLOPT_PROXY, 'http://brd.superproxy.io:22225');
    curl_setopt(
      $ch,
      CURLOPT_PROXYUSERPWD,
      'brd-customer-hl_e5f2315f-zone-datacenter_proxy1-country-nl:uvmqoi66peju'
    );
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 1);

    $html = curl_exec($ch);
    $html = str_replace(["\n", "\r", "\n"], ' ', $html);
    $contextOptions = [
      "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
      ],
    ];
    $file = file_get_contents(
      'https://www.tcmb.gov.tr/kurlar/today.xml',
      false,
      stream_context_create($contextOptions)
    );
    $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
    $currency_xml = simplexml_load_string($temp);

    $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
    $usdSelling = $currency_xml->Currency[0]->BanknoteBuying;
    $usdEur = $currency_xml->Currency[3]->CrossRateOther;
    $tryPrice = search('<i class="integers ">', '</i>', $html);
    if ($tryPrice) {
      $tryPrice = str_replace(",", "", trim($tryPrice[0]));
      $tryPrice = str_replace(".", "", $tryPrice);

      switch ($currency) {
        case "TRY":
          return round($tryPrice * (float) $eurSelling);
        case "USD":
          $usdPrice = round($tryPrice / (float) $usdEur);

          return $usdPrice;
        case "EUR":
          $eurPrice = $tryPrice;

          return $eurPrice;
      }

      return $tryPrice;
    } else {
      return "NA";
    }
  });

  return $price;
}

function getTatilSepetiPrice($url, $currency, $startDate, $endDate)
{
  $cacheKey = md5("$url-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $url,
    $currency,
    $startDate,
    $endDate
  ) {
    $checkIn = date("d.m.Y", strtotime($startDate));
    $checkOut = date("d.m.Y", strtotime($endDate));

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt(
      $ch,
      CURLOPT_POSTFIELDS,
      "Search=oda%3A2%3Btarih%3A" .
        $checkIn .
        "%2C" .
        $checkOut .
        "%3Bclick%3Atrue"
    );
    curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

    $headers = [];
    $headers[] =
      'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/116.0';
    $headers[] = 'Accept: application/json, text/javascript, */*; q=0.01';
    $headers[] = 'Accept-Language: en-US,en;q=0.5';
    $headers[] = 'Accept-Encoding: gzip, deflate, br';
    $headers[] =
      'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
    $headers[] =
      'Verificationtoken: dFPICZuYEJi3w1IjnKoKW7_QYP2V0-YjpwuFtzpBnz40Xeas7vZ2Jg6wkwkpXwCvXOrpfEnozuxqqMMEXMTWUQzy1eg1,rp97xoAp3FxNcH3w0gLLz19Ffreuv2jVHi1MgD3lH9hVuuU-2sAeZ2eMI6loVlhI8gxzNVpWUwEHBTWgzrcXwg0WlrE1';
    $headers[] = 'X-Requested-With: XMLHttpRequest';
    $headers[] = 'Origin: https://www.tatilsepeti.com';
    $headers[] = 'Dnt: 1';
    $headers[] = 'Connection: keep-alive';
    $headers[] =
      'Referer: https://www.tatilsepeti.com/palace-hotel-olive-odore?ara=oda:2;tarih:22.09.2023,23.09.2023';
    $headers[] =
      'Cookie: _ENV["ASP.NET_SessionId=0zvnb0o2ctqtkwskt153ambi; HotelSearchCriteriaCookie=%7B%22SearchTitle%22%3Anull%2C%22CheckInDate%22%3A%222023-09-22T00%3A00%3A00%22%2C%22CheckOutDate%22%3A%222023-09-23T00%3A00%3A00%22%2C%22AdultCount%22%3A2%2C%22ChildCount%22%3A0%2C%22ChildAges%22%3A%5B%5D%2C%22RoomDetails%22%3Anull%2C%22IsUndated%22%3Afalse%7D; TourSearchCriteriaCookie=%7B%22SearchTitle%22%3Anull%2C%22CheckInDate%22%3A%222023-08-11T00%3A00%3A00%22%2C%22CheckOutDate%22%3A%222024-08-11T00%3A00%3A00%22%2C%22AdultCount%22%3A0%2C%22ChildCount%22%3A0%2C%22ChildAges%22%3Anull%2C%22RoomDetails%22%3Anull%2C%22IsUndated%22%3Afalse%7D; FlightSearchCriteriaCookie=%7B%22CacheKey%22%3Anull%2C%22UserId%22%3A0%2C%22FromModel%22%3Anull%2C%22From%22%3Anull%2C%22ToModel%22%3Anull%2C%22To%22%3Anull%2C%22IsFromCity%22%3Afalse%2C%22IsToCity%22%3Afalse%2C%22DepartureDate%22%3A%220001-01-01T00%3A00%3A00%22%2C%22ReturnDate%22%3A%220001-01-01T00%3A00%3A00%22%2C%22PassengerCount%22%3Anull%2C%22RoundTrip%22%3A0%2C%22FlightClass%22%3A0%2C%22NegotiatedFareCode%22%3Anull%2C%22CorporatePINNumber%22%3Anull%2C%22IsCip%22%3Afalse%2C%22Provider%22%3Anull%2C%22DirectFlightOnly%22%3Afalse%2C%22IsAdmin%22%3Afalse%2C%22ShowTotalPrice%22%3Afalse%2C%22IsPackage%22%3Afalse%2C%22IsFlightPocket%22%3Afalse%2C%22FromTitle%22%3Anull%2C%22FromLink%22%3Anull%2C%22FromValue%22%3Anull%2C%22FromType%22%3Anull%2C%22ToTitle%22%3Anull%2C%22ToLink%22%3Anull%2C%22ToValue%22%3Anull%2C%22ToType%22%3Anull%2C%22HotelId%22%3Anull%2C%22AgeList%22%3Anull%7D; UserName=; UpdateReferrer=11.08.2023 13:04:49|Ana Sayfa|||11.08.2023 14:55:51|Ana Sayfa|||; oneDayDontShowCookiePolicy=1; cf_clearance=hcIDmxzEGNcZlXFMbYUCYyc1ggqvXWAjWXipezIplvY-1691758040-0-1-c6e6e016.2ff25df4.91e7bff6-0.2.1691758040; MyVisits=\"Tesis4284\"=11.08.2023 15:56:34; lastSearchreaCriteriaCookie={\"CheckInDate\":\"22.09.2023\",\"CheckOutDate\":\"23.09.2023\"}"]';
    $headers[] = 'Sec-Fetch-Dest: empty';
    $headers[] = 'Sec-Fetch-Mode: cors';
    $headers[] = 'Sec-Fetch-Site: same-origin';
    $headers[] = 'Sec-Gpc: 1';
    $headers[] = 'Te: trailers';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = curl_exec($ch);
    if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);

    $contextOptions = [
      "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
      ],
    ];
    $file = file_get_contents(
      'https://www.tcmb.gov.tr/kurlar/today.xml',
      false,
      stream_context_create($contextOptions)
    );
    $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
    $currency_xml = simplexml_load_string($temp);

    $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
    $usdSelling = $currency_xml->Currency[3]->CrossRateOther;
    $roomList = json_decode($result, true)["roomList"];

    $availability = search('<div class="alert', '--error', $roomList);
    if (in_array(0, $availability)) {
      return "NA";
    }

    $tryPrice = search(
      '<span class="Prices--Price">',
      '<small class=\'price-currency\'>',
      $roomList
    );

    if ($tryPrice == []) {
      return "NA";
    }

    $tryPrice = str_replace([".", ","], "", trim($tryPrice[0]));

    $eurPrice = round(str_replace(".", ",", $tryPrice / (float) $eurSelling));
    $usdPrice = round(str_replace(".", ",", $tryPrice / (float) $usdSelling));

    switch ($currency) {
      case "TRY":
        return $tryPrice;
      case "USD":
        return $usdPrice;
      case "EUR":
        return $eurPrice;
    }
  });

  return $price;
}

function getHotelsPrice($url, $currency, $startDate, $endDate)
{
  return "NA";

  $cacheKey = md5("$url-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $url,
    $currency,
    $startDate,
    $endDate
  ) {
    $pathInfo = parse_url($url);
    if (!array_key_exists("query", $pathInfo)) {
      return "NA";
    }
    $queryString = $pathInfo["query"];
    parse_str($queryString, $queryArray);
    $queryArray["chkin"] = $startDate;
    $queryArray["chkout"] = $endDate;
    $newQueryStr = http_build_query($queryArray);
    $newUrl =
      "https://" . $pathInfo["host"] . $pathInfo["path"] . "?" . $newQueryStr;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://tr.hotels.com/graphql');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/117.0',
      'Accept: */*',
      'Accept-Language: en-US,en;q=0.5',
      'Accept-Encoding: gzip, deflate, br',
      'Referer: https://tr.hotels.com/ho534033/baldan-suites-hotel-restaurant-marmaris-turkiye/?bedroom_count_gt=&chain=&chkin=5.10.2023&chkout=6.10.2023&daysInFuture=&destType=MARKET&destination=Marmaris%2C%20Mu%C4%9Fla%2C%20T%C3%BCrkiye&endDate=6.10.2023&expediaPropertyId=5959083&group=&guestRating=&highlightedPropertyId=&hotelName=&latLong=36.854936%2C28.270878&misId=&neighborhood=&petsIncluded=false&poi=&pricing_group=&pwa_ts=1696449372447&referrerUrl=aHR0cHM6Ly90ci5ob3RlbHMuY29tL0hvdGVsLVNlYXJjaA%3D%3D&regionId=6054843&rm1=a2&roomIndex=&searchId=a39d1db9-55bd-4283-b7a5-8ea1ee74a5bd&selected=5959083&selectedRatePlan=383329240&selectedRoomType=314377699&sort=RECOMMENDED&startDate=5.10.2023&stayLength=&theme=&us_bathroom_count_gt=&useRewards=false&userIntent=&vacationRentalsOnly=false&x_pwa=1',
      'content-type: application/json',
      'client-info: shopping-pwa,743f46197f3b193f505182af7292a80dd14e979b,us-west-2',
      'x-product-line: lodging',
      'x-page-id: page.Hotels.Infosite.Information,H,30',
      'Origin: https://tr.hotels.com',
      'DNT: 1',
      'Connection: keep-alive',
      'Sec-Fetch-Dest: empty',
      'Sec-Fetch-Mode: cors',
      'Sec-Fetch-Site: same-origin',
      'Sec-GPC: 1',
      'TE: trailers',
    ]);
    curl_setopt(
      $ch,
      CURLOPT_COOKIE,
      'linfo=v.4,|0|0|255|1|0||||||||1055|0|0||0|0|0|-1|-1; CRQSS=e|28; CRQS=t|3115`s|300000028`l|tr_TR`c|TRY; currency=TRY; iEAPID=28; tpid=v.1,3115; cesc=%7B%22lpe%22%3A%5B%224f79fb3a-2b6a-404d-ba9b-f4f67e3c68f4%22%2C1696449380895%5D%2C%22marketingClick%22%3A%5B%22false%22%2C1696449380895%5D%2C%22lmc%22%3A%5B%22SEO.U.GOOGLE.COM%22%2C1696449380895%5D%2C%22hitNumber%22%3A%5B%223%22%2C1696449380895%5D%2C%22amc%22%3A%5B%22SEO.U.GOOGLE.COM%22%2C1696449380895%5D%2C%22visitNumber%22%3A%5B%221%22%2C1696449358920%5D%2C%22ape%22%3A%5B%224f79fb3a-2b6a-404d-ba9b-f4f67e3c68f4%22%2C1696449380895%5D%2C%22cidVisit%22%3A%5B%22SEO.U.google.com%22%2C1696449380895%5D%2C%22entryPage%22%3A%5B%22noonewillmatchthis%22%2C1696449380895%5D%2C%22cid%22%3A%5B%22SEO.U.google.com%22%2C1696449358920%5D%7D; HMS=8fd7862b-d939-41be-9836-40cb4a030150; MC1=GUID=1d4f2cc456714a9cb92e8260bb637b06; DUAID=1d4f2cc4-5671-4a9c-b92e-8260bb637b06; akacd_pr_20=1701633358~rv=22~id=3717ac3b4b5d212ae22d840d9060142a; _abck=ABF0087CE26932AFC93F4C58A00FD98D~0~YAAQLLOvwyUEzu6KAQAA3/VC/ArGlFZlzw5wYoI8u0ZhxgWvZhmIcMJ7h0+jiCmwQSkq3BoCKl3Hb+yCHBd5IZZ+NU+OdZnGlFdIlosAbHhcBDjrtgmBcKnjbu6CorcQ+KxMLL1P8eNoo2epmLQwpaev7J+f43WoIRWkj/4s0but5mMFgFjxry4butFaHzR1gIG3oMnp9quCQPcXf0ymggH2RKWXIyBLB20s6tDPB0n05UrDJO0iumqXbe5MguuBRBxQtGG20zhvS/V8jfrzxfYDpdq6O41p2mCSF0kCUi3tYg5/8aTd0fKEkYtF3iaxAR7Z8rGoWP+jcRTlDKGKmENc+SSrTJwqYGLJ/rKzADmYv461JUwOLY5XYmgkbTE4LeeePETJp2cTaTzzrJDInYAzwnYlF4h1GQ==~-1~-1~-1; bm_sz=5E4DC40E6B404B766FB7B0422C3883EB~YAAQLLOvw+cCzu6KAQAAluxC/BVp4bi3f3u39cfgGtGvTvdiByhDUJGtHz9s4e9T4NcDysdcz8uc2IwF/wp1bcdCyzTdfXwgebeLJnjyRhb3SZ2+/tggIU5r3bs3Qii64E/qziN3o/i7WQiLEguduji9Pv1BAIBHTIRfK0wVnsnPv4v1spJXmhagmhN1qjmNtBHKdii9C2SyafM5dafOEHedI+W4Gjay7QcuhuwM8JFrB6fTEQvtoHF8KkReE3gguTap6YXTsbOKYPrAg5UksNOPYf5mcLv91CeJfgx7j9xvBcU=~3551544~3228471; AMCV_C00802BE5330A8350A490D4C%40AdobeOrg=1585540135%7CMCIDTS%7C19635%7CMCMID%7C55914039809474859292735886322720816865%7CMCAID%7CNONE%7CMCOPTOUT-1696456585s%7CNONE%7CvVersion%7C4.4.0; s_ppn=page.Hotels.Infosite.Information; s_ppv=%5B%5BB%5D%5D; s_ips=1; s_tp=6335; AMCVS_C00802BE5330A8350A490D4C%40AdobeOrg=1; s_cc=true; _dd_s=rum=0&expire=1696450923505; EG_SESSIONTOKEN=dFwBXsOjMvjsxQA_RMXu6kjVUx47x2ywr6-T2CvBe6cars:fgi6xdr_3IBerJpp0vQdERdwyyhri5bAbll5cJDRsMRZ80fLIg2bHT8oDc9BlIOjCU7IWPPUmroO2p8X_XsYOw; session_id=8fd7862b-d939-41be-9836-40cb4a030150; page_name=page.Hotel-Search'
    );
    curl_setopt(
      $ch,
      CURLOPT_POSTFIELDS,
      "[{\"operationName\":\"PropertyOffersQuery\",\"variables\":{\"propertyId\":\"5959083\",\"searchCriteria\":{\"primary\":{\"dateRange\":{\"checkInDate\":{\"day\":5,\"month\":10,\"year\":2023},\"checkOutDate\":{\"day\":6,\"month\":10,\"year\":2023}},\"destination\":{\"regionName\":\"Marmaris, Muğla, Türkiye\",\"regionId\":\"6054843\",\"coordinates\":{\"latitude\":36.854936,\"longitude\":28.270878},\"pinnedPropertyId\":\"5959083\",\"propertyIds\":null,\"mapBounds\":null},\"rooms\":[{\"adults\":2,\"children\":[]}]},\"secondary\":{\"counts\":[],\"booleans\":[],\"selections\":[{\"id\":\"sort\",\"value\":\"RECOMMENDED\"},{\"id\":\"privacyTrackingState\",\"value\":\"CAN_NOT_TRACK\"},{\"id\":\"useRewards\",\"value\":\"SHOP_WITHOUT_POINTS\"}],\"ranges\":[]}},\"shoppingContext\":{\"multiItem\":null},\"travelAdTrackingInfo\":null,\"searchOffer\":{\"offerPrice\":null,\"roomTypeId\":\"314377699\",\"ratePlanId\":\"383329240\",\"offerDetails\":[]},\"referrer\":null,\"context\":{\"siteId\":300000028,\"locale\":\"tr_TR\",\"eapid\":28,\"currency\":\"TRY\",\"device\":{\"type\":\"DESKTOP\"},\"identity\":{\"duaid\":\"1d4f2cc4-5671-4a9c-b92e-8260bb637b06\",\"expUserId\":\"-1\",\"tuid\":\"-1\",\"authState\":\"ANONYMOUS\"},\"privacyTrackingState\":\"CAN_TRACK\",\"debugContext\":{\"abacusOverrides\":[],\"alterMode\":\"RELEASED\"}}},\"extensions\":{\"persistedQuery\":{\"version\":1,\"sha256Hash\":\"593bde3027fb0475dd6e126fd6ba54df87f2813b660a6f23b3b8ef8f2ec91a8a\"}}}]"
    );

    dd(curl_exec($ch));

    $response = json_decode(curl_exec($ch), true);

    curl_close($ch);
    if ($response[0]["data"]["propertyOffers"]["stickyBar"] == null) {
      return "NA";
    }

    $price =
      $response[0]["data"]["propertyOffers"]["stickyBar"]["price"][
        "formattedDisplayPrice"
      ];
    $price = trim(str_replace("TL", "", $price));
    $price = str_replace(".", "", $price);

    $contextOptions = [
      "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
      ],
    ];
    $file = file_get_contents(
      'https://www.tcmb.gov.tr/kurlar/today.xml',
      false,
      stream_context_create($contextOptions)
    );
    $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
    $currency_xml = simplexml_load_string($temp);

    $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
    $usdSelling = $currency_xml->Currency[3]->CrossRateOther;

    if ($price != []) {
      switch ($currency) {
        case "TRY":
          return (int) $price;
        case "USD":
          $usdPrice = round(
            str_replace(".", ",", (int) $price / (float) $usdSelling)
          );

          return $usdPrice;
        case "EUR":
          $eurPrice = round(
            str_replace(".", ",", (int) $price / (float) $eurSelling)
          );

          return $eurPrice;
      }
    }
  });

  return $price ?: "NA";
}

function getOtelzUrl($otelzUrl, $currency, $startDate, $endDate)
{
  if (is_nan($otelzUrl)) {
    return "";
  }

  $facilityID = $otelzUrl;

  $data = [
    "partner_id" => 1316,
    "filter" => [
      "facility_references" => [$facilityID],
      "type" => "HotelIdList",
    ],

    "start_date" => $startDate,
    "end_date" => $endDate,
    "party" => [
      [
        "adults" => 2,
        "children" => [],
      ],
    ],

    "lang" => "tr",
    "currency" => $currency,
    "price_formatter" => [
      "decimal_digit_number" => 2,
    ],
    "user_country" => "TR",
    "device_type" => 1,
    "request_type" => 1,
    "web_hook_url" => "",
  ];

  $json = json_encode($data);

  $username = "kucukoteller";
  $passwd = "4;q)Dx9#";

  $headers = [
    'Content-Type:application/json',
    'Authorization: Basic ' . base64_encode("$username:$passwd"),
  ];

  $ch = curl_init('https://fullconnect.otelz.com/v2/search/availability');

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
  curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  if (curl_exec($ch) == "") {
    return "";
  }

  $result = json_decode(curl_exec($ch));

  if (isset($result->errors)) {
    return "";
  }

  if ($result) {
    $url = $result->availability->search_items[0]->link;

    return $url;
  }

  return "";
}

function getOtelzPriceAPI($otelzUrl, $currency, $startDate, $endDate)
{
  $cacheKey = md5("$otelzUrl-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $otelzUrl,
    $currency,
    $startDate,
    $endDate
  ) {
    if (!is_numeric($otelzUrl)) {
      return "";
    }

    $facilityID = $otelzUrl;

    $data = [
      "api_version" => "1.0.0",
      "partner_id" => 1316,
      "facility_reference" => $facilityID,
      "start_date" => $startDate,
      "end_date" => $endDate,
      "party" => [
        [
          "adults" => 2,
          "children" => [],
        ],
      ],

      "lang" => "tr",
      "currency" => $currency,
      "price_formatter" => [
        "decimal_digit_number" => 2,
      ],
      "user_country" => "TR",
      "device_type" => 1,
      "request_type" => 1,
      "web_hook_url" => "",
    ];

    $json = json_encode($data);

    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    $headers = [
      'Content-Type:application/json',
      'Authorization: Basic ' . base64_encode("$username:$passwd"),
    ];

    $ch = curl_init('https://fullconnect.otelz.com/detail/availability');

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = json_decode(curl_exec($ch));

    if (isset($result->errors)) {
      return "NA";
    }

    if ($result) {
      if ($result->detail_result) {
        if ($result->detail_result->min_price->total_room == 0) {
          return "NA";
        }
        $price = $result->detail_result->min_price->net_total->amount;

        return round($price);
      }
    } else {
      return "NA";
    }
  });

  return $price;
}

function getOtelZPrice($otelzUrl, $currency, $startDate, $endDate)
{
  $cacheKey = md5("$otelzUrl-$currency-$startDate,$endDate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $otelzUrl,
    $currency,
    $startDate,
    $endDate
  ) {
    if (!ctype_digit($otelzUrl)) {
      return "";
    }

    $facilityID = $otelzUrl;

    $data = [
      "api_version" => "1.0.0",
      "partner_id" => 1316,
      "facility_reference" => $facilityID,
      "start_date" => $startDate,
      "end_date" => $endDate,
      "party" => [
        [
          "adults" => 2,
          "children" => [],
        ],
      ],

      "lang" => "tr",
      "currency" => $currency,
      "price_formatter" => [
        "decimal_digit_number" => 2,
      ],
      "user_country" => "TR",
      "device_type" => 1,
      "request_type" => 1,
      "web_hook_url" => "",
    ];

    $json = json_encode($data);

    $username = "kucukoteller";
    $passwd = "4;q)Dx9#";

    $headers = [
      'Content-Type:application/json',
      'Authorization: Basic ' . base64_encode("$username:$passwd"),
    ];

    $ch = curl_init('https://fullconnect.otelz.com/detail/availability');

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $result = json_decode(curl_exec($ch));

    if (isset($result->errors)) {
      return "NA";
    }

    if ($result) {
      if ($result->detail_result) {
        if ($result->detail_result->min_price->total_room == 0) {
          return "NA";
        }
        $price = $result->detail_result->min_price->net_total->amount;

        return round($price);
      }
    } else {
      return "NA";
    }
  });

  return $price;
}

function getHotelRunnerPrice($url, $currency, $startDate, $endDate)
{
  return "NA";

  $baseUrl = parse_url($url);

  // Generated by curl-to-PHP: http://incarnate.github.io/curl-to-php/
  $ch = curl_init();

  curl_setopt(
    $ch,
    CURLOPT_URL,
    'https://payava-butik-otel.hotelrunner.com/api/v1/bv3/search/availabilities.json?api_key=7518391247a27ce412d421ffe241c6ffd3f52e7c4b26e993&checkin_date=2023-08-23&checkout_date=2023-08-24&day_count=1&room_count=1&total_adult=2&total_child=0&rooms%5B%5D%5Badult_count%5D=2&rooms%5B%5D%5Bguest_count%5D=2&rooms%5B%5D%5Bchild_count%5D=0&guest_rooms%5B0%5D%5Badult_count%5D=2&guest_rooms%5B0%5D%5Bguest_count%5D=2&guest_rooms%5B0%5D%5Bchild_count%5D=0&currency=EUR&locale=en-US'
  );
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');

  curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

  $headers = [];
  $headers[] =
    'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10.15; rv:109.0) Gecko/20100101 Firefox/116.0';
  $headers[] = 'Accept: application/json, text/plain, */*';
  $headers[] = 'Accept-Language: en-US,en;q=0.5';
  $headers[] = 'Accept-Encoding: gzip, deflate, br';
  $headers[] = 'X-Hr-Challenge: 691182';
  $headers[] = 'Content-Type: text/plain';
  $headers[] = 'Dnt: 1';
  $headers[] = 'Connection: keep-alive';
  $headers[] =
    'Referer: https://payava-butik-otel.hotelrunner.com/bv3/search?search=%7B%22checkin_date%22:%222023-08-23%22,%22checkout_date%22:%222023-08-24%22,%22day_count%22:1,%22room_count%22:1,%22total_adult%22:2,%22total_child%22:0,%22rooms%22:%5B%7B%22adult_count%22:2,%22guest_count%22:2,%22child_count%22:0,%22child_ages%22:%5B%5D%7D%5D,%22guest_rooms%22:%7B%220%22:%7B%22adult_count%22:2,%22guest_count%22:2,%22child_count%22:0,%22child_ages%22:%5B%5D%7D%7D%7D';
  $headers[] =
    'Cookie: locale=en-US; currency=BAhJIghFVVIGOgZFVA%3D%3D--ee0da01ef7ac09e00574b2107efb3ff1079fad4e; loccur=TRY; country_code=BAhJIgdUUgY6BkVU--4b046995e58c082ff009e67f439c797f7188bfc7; checkout_currency=BAhJIghFVVIGOgZFVA%3D%3D--ee0da01ef7ac09e00574b2107efb3ff1079fad4e';
  $headers[] = 'Sec-Fetch-Dest: empty';
  $headers[] = 'Sec-Fetch-Mode: cors';
  $headers[] = 'Sec-Fetch-Site: same-origin';
  $headers[] = 'Sec-Gpc: 1';
  $headers[] = 'Te: trailers';
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $availabilityResult = curl_exec($ch);
  if (curl_errno($ch)) {
    echo 'Error:' . curl_error($ch);
  }
  curl_close($ch);

  dd(json_decode($availabilityResult));

  $availabilities = json_decode($availabilityResult)->available_room_types;

  $reqUrl = $url . "/api/v1/bv3/search/prices.json?";

  foreach ($availabilities as $a) {
    foreach ($a->available_rate_ids as $id) {
      $reqUrl .= "ids[]=" . $id . "&";
    }
  }

  $ch = curl_init($reqUrl . urldecode(http_build_query($queryArray)));

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_ENCODING, "gzip");
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = json_decode(curl_exec($ch));

  dd($result);

  $prices = [];
  foreach ($result->prices as $priceStack) {
    foreach ($priceStack as $room) {
      foreach ($room->daily_prices as $price) {
        array_push($prices, $price->amount);
      }
    }
  }
  if ($prices == []) {
    return "NA";
  }

  $minPrice = min($prices);

  $contextOptions = [
    "ssl" => [
      "verify_peer" => false,
      "verify_peer_name" => false,
    ],
  ];
  $file = file_get_contents(
    'https://www.tcmb.gov.tr/kurlar/today.xml',
    false,
    stream_context_create($contextOptions)
  );
  $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
  $currency_xml = simplexml_load_string($temp);

  $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
  $usdSelling = $currency_xml->Currency[3]->BanknoteBuying;
  $usdCrossRate = $currency_xml->Currency[3]->CrossRateOther;

  switch ($currency) {
    case "USD":
      if ($result->base_currency == "EUR") {
        $price = sprintf("%.0f", $minPrice * (string) $usdCrossRate);
      } else {
        if ($result->base_currency == "TRY") {
          $price = sprintf("%.0f", $minPrice / (string) $usdSelling);
        } else {
          $price = $minPrice;
        }
      }
      break;
    case "TRY":
      if ($result->base_currency == "EUR") {
        $price = sprintf("%.0f", $minPrice * (string) $eurSelling);
      } else {
        if ($result->base_currency == "USD") {
          $price = sprintf("%.0f", $minPrice * (string) $usdSelling);
        } else {
          $price = $minPrice;
        }
      }
      break;
    case "EUR":
      if ($result->base_currency == "TRY") {
        $price = sprintf("%.0f", $minPrice / (string) $eurSelling);
      } else {
        if ($result->base_currency == "USD") {
          $price = sprintf("%.0f", $minPrice * (string) $usdCrossRate);
        } else {
          $price = $minPrice;
        }
      }
      break;
  }

  $searchUrl =
    "https://" .
    $baseUrl['host'] .
    "/bv3/search?search=" .
    json_encode($queryArray);

  return ['price' => round($price), 'url' => $searchUrl];
}

function getSabeeRoomsPrice($sabeeHotelId, $currency, $startdate, $enddate)
{
  $cacheKey = md5("$sabeeHotelId-$currency-$startdate,$enddate");
  $cachingTime = Setting::where("key", "caching-time")->firstOrFail();
  //Cache::forget($requestToken);

  $price = Cache::remember($cacheKey, $cachingTime->value, function () use (
    $sabeeHotelId,
    $currency,
    $startdate,
    $enddate
  ) {
    $sabeeApp = new SabeeClient(ENV("SABEE_API_KEY"));

    $roomTypes = getSabeeRooms($sabeeHotelId);
    if ($roomTypes == "") {
      return "NA";
    }

    $rooms = [];

    foreach ($roomTypes as $roomType) {
      $room = [
        "room_id" => $roomType->room_id,
        "guest_count" => ["adults" => 2],
      ];
      array_push($rooms, $room);
    }

    $parameters = [
      'hotel_id' => $sabeeHotelId,
      'start_date' => $startdate,
      'end_date' => $enddate,
      'rooms' => $rooms,
    ];

    $response = $sabeeApp->request('booking/availability', $parameters, 'POST');
    if ($response->success && $response->data->room_rates) {
      $prices = [];
      $pricesArray = array_column($response->data->room_rates, "prices");
      foreach ($pricesArray as $priceArray) {
        if ($priceArray != null) {
          foreach ($priceArray as $p) {
            if ($p->rateplan_id == 0) {
              continue;
            }
            array_push($prices, $p);
          }
        }
      }
      //            $available_rooms = $response->data->room_rates[0]->available_rooms;
      //
      //            if ($available_rooms > 0) {

      $priceColumn = array_column($prices, 'amount');
      if ($priceColumn == []) {
        return "NA";
      }
      $minArray = $prices[array_search(min($priceColumn), $priceColumn)];

      $sabeeCurrency = $minArray->currency;
      $price = $minArray->amount;

      $contextOptions = [
        "ssl" => [
          "verify_peer" => false,
          "verify_peer_name" => false,
        ],
      ];
      $file = file_get_contents(
        'https://www.tcmb.gov.tr/kurlar/today.xml',
        false,
        stream_context_create($contextOptions)
      );
      $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
      $currency_xml = simplexml_load_string($temp);

      $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
      $usdSelling = $currency_xml->Currency[3]->CrossRateOther;

      if ($sabeeCurrency == "TRY") {
        $price = $price / (string) $eurSelling;
      }

      if ($sabeeCurrency == "USD") {
        $price = $price / (string) $usdSelling;
      }

      switch ($currency) {
        case "USD":
          return sprintf("%.0f", $price * (string) $usdSelling);
        case "TRY":
          return sprintf("%.0f", $price * (string) $eurSelling);
        case "EUR":
          return sprintf("%.0f", $price);
      }
    } else {
      return "NA";
    }
  });

  return $price;
}

function getSabeePrice(
  $sabeeHotelId,
  $roomId,
  $rateplanId,
  $currency,
  $startdate,
  $enddate
) {
  $sabeeApp = new SabeeClient(ENV("SABEE_API_KEY"));

  $parameters = [
    'hotel_id' => $sabeeHotelId,
    'start_date' => $startdate,
    'end_date' => $enddate,
    'rooms' => [["room_id" => $roomId, "guest_count" => ["adults" => 2]]],
  ];

  $response = $sabeeApp->request('booking/availability', $parameters, 'POST');
  if ($response->success && $response->data->room_rates) {
    $available_rooms = $response->data->room_rates[0]->available_rooms;

    if ($available_rooms > 0) {
      $prices = $response->data->room_rates[0]->prices;

      $key = array_search($rateplanId, array_column($prices, 'rateplan_id'));

      $price = $prices[$key]->amount;
      $sabeeCurrency = $prices[$key]->currency;

      $contextOptions = [
        "ssl" => [
          "verify_peer" => false,
          "verify_peer_name" => false,
        ],
      ];
      $file = file_get_contents(
        'https://www.tcmb.gov.tr/kurlar/today.xml',
        false,
        stream_context_create($contextOptions)
      );
      $temp = preg_replace('/&(?!(quot|amp|pos|lt|gt);)/', '&amp;', $file);
      $currency_xml = simplexml_load_string($temp);

      $eurSelling = $currency_xml->Currency[3]->BanknoteBuying;
      $usdSelling = $currency_xml->Currency[3]->CrossRateOther;

      if ($sabeeCurrency == "TRY") {
        $price = $price / (string) $eurSelling;
      }

      if ($sabeeCurrency == "USD") {
        $price = $price / (string) $usdSelling;
      }

      switch ($currency) {
        case "USD":
          return sprintf("%.0f", $price * (string) $usdSelling);
        case "TRY":
          return sprintf("%.0f", $price * (string) $eurSelling);
        case "EUR":
          return sprintf("%.0f", $price);
      }
    } else {
      return "NA";
    }
  } else {
    return "NA";
  }
}

function shortNumber($n, $precision = 0, $divisors = null)
{
  if ($n < 1000) {
    // Anything less than a million
    $n_format = number_format($n);
  } else {
    if ($n < 1000000) {
      // Anything less than a million
      $n_format = number_format($n / 1000, $precision) . 'K';
    } else {
      if ($n < 1000000000) {
        // Anything less than a billion
        $n_format = number_format($n / 1000000, $precision) . 'M';
      } else {
        // At least a billion
        $n_format = number_format($n / 1000000000, $precision) . 'B';
      }
    }
  }

  return $n_format;
}

function sabeeUrlLanguageChange($lang, $url)
{
  $search = "";
  $replace = "";

  if ($lang == "tr") {
    $search = ["&l=en", "booking"];
    $replace = ["&l=tr", "rezervasyon"];
  }

  if ($lang == "en") {
    $search = ["&l=tr", "rezervasyon"];
    $replace = ["&l=en", "booking"];
  }

  return str_replace($search, $replace, $url);
}

/* --------------------------------------------------------------------------------------------------------------------------- format Date Time -+- */
/**
 * Wrapper for `formatCarbon()` with project default date time format
 *
 * @param      $carbonInstance
 * @param  null  $default
 *
 * @return Carbon|string|null
 */
function formatDateTime($carbonInstance, $default = null)
{
  return formatCarbon($carbonInstance, DATETIME_FORMAT, $default);
}

/* -------------------------------------------------------------------------------------------------------------------------------- format Date -+- */
/**
 * Wrapper for `formatCarbon()` with project default date format
 *
 * @param      $carbonInstance
 * @param  null  $default
 *
 * @return Carbon|string|null
 */
function formatDate($carbonInstance, $default = null)
{
  return formatCarbon($carbonInstance, DATE_FORMAT, $default);
}

/* -------------------------------------------------------------------------------------------------------------------------------- format Time -+- */
/**
 * Wrapper for `formatCarbon()` with project default time format
 *
 * @param      $carbonInstance
 * @param  null  $default
 *
 * @return Carbon|string|null
 */
function formatTime($carbonInstance, $default = null)
{
  return formatCarbon($carbonInstance, TIME_FORMAT, $default);
}

/* ------------------------------------------------------------------------------------------------------------------------------ format Carbon -+- */
/**
 * Safely format Carbon instances.
 *
 * If input is not a Carbon instance, format and return secondary (`$default`) Carbon instance.
 *
 * Otherwise return the `$default` value.
 *
 * <br>
 * Example usage;
 * <code>
 * // format and return model deletion time, format and return current time if model is not deleted.
 * formatCarbon($model->deleted_at, 'd-m-Y H:i', Carbon::now());
 *
 * // show either formatted approval date, or a message stating it is not approved.
 * formatCarbon($model->approved_at, 'd-m-Y', 'Not Approved');
 * </code>
 *
 * @param  Carbon|mixed  $carbonInstance
 * @param  string  $format  Carbon format to use. See Carbon and PHP docs for more.
 * @param  Carbon|string  $default  default value. Will be returned if string given. Will be formatted if a Carbon instance given.
 *
 * @return string|null
 *
 * @see https://carbon.nesbot.com/docs/#api-formatting      - Formatting from Carbon docs
 * @see https://www.php.net/manual/en/datetime.format.php   - Fatetime format on php.net
 */
function formatCarbon($carbonInstance, $format, $default = null)
{
  if (!$carbonInstance instanceof Carbon) {
    if ($default instanceof Carbon) {
      return $default->format($format);
    }

    return $default;
  }

  return $carbonInstance->format($format);
}
