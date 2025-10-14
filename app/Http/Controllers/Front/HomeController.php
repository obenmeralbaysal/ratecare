<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateAccountRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Models\Invite;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    public function login()
    {
//        get_sabee_price(1, 4, 4, "EUR", "2019-03-15", "2019-03-16");
        
        if (loggedIn()) {
            if (isReseller()) {
                return redirect()->route('reseller.dashboard');
            } else if (user()->is_admin) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect("customer/widget/edit");
            }
        }
        
        return view("front.login.login");
    }
    
    public function forgotPassword()
    {
        return view("front.login.forgot-password");
    }
    
    public function postForgotPassword(ForgotPasswordRequest $request)
    {
        $user = User::all()->where('email', $request->email);
        
        if ($user) {
        
        }
    }
    
    public function invite($code)
    {
        $invitation = Invite::all()->where("code", $code)->first();
        
        if ($invitation) {
            return view("front.login.new-user")->with("invitation", $invitation);
        } else {
            return redirect("/")->with("error", "Invalid invitation code...");
        }
    }
    
    public function createAccount(CreateAccountRequest $request)
    {
        $invitation = Invite::all()->where("code", $request->code)->first();
        
        if ($invitation) {
            if ($invitation->accepted) {
                return redirect("/")->with("error", "Used invitation code...");
            }
            
            $user = new User();
            
            $user->namesurname = $request->namesurname;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->reseller_id = $invitation->reseller_id;
            
            $user->save();
            
            $invitation->accepted = 1;
            
            $invitation->save();
            
            return redirect("/")->with("success", "Please log in...");
            
        } else {
            return redirect("/")->with("error", "Invalid invitation code...");
        }
    }
}
