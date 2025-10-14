<?php

namespace App\Http\Controllers\Admin\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\InviteStoreRequest;
use App\Http\Requests\UserStoreRequest;
use App\Http\Requests\UserUpdateRequest;
use Illuminate\Http\Request;
use App\Models\Invite;
use App\User;

class UserController extends Controller
{

    public function index()
    {
        $users = User::with('reseller')->filter()->paginate();
        $invited = Invite::all()->where("accepted", 0);

        return view("admin.users.index")
            ->with("users", $users)
            ->with("invited", $invited);
    }

    public function create()
    {
        $user = new User();

        return view("admin.users.create")
            ->with("editing", false)
            ->with("user", $user);
    }

    public function store(UserStoreRequest $request)
    {
        $user = new User();

        $user->namesurname = $request->namesurname;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->user_type = $request->userType;

        if ($request->resellerLogo) {
            $imageName = time() . '.' . $request->resellerLogo->getClientOriginalExtension();
            $request->resellerLogo->move(public_path('logo'), $imageName);
            $user->logo = $imageName;
        }

        $user->is_rate_comparison_active = $request->rateComparison ? 1 : 0;


        $user->save();

        return back()->with('success', "Saved successfully !");
    }

    public function edit($id)
    {
        $user = User::find($id);

        return view("admin.users.create")
            ->with("editing", true)
            ->with("user", $user);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        $user->namesurname = $request->namesurname;
        $user->email = $request->email;


        if ($request->password)
            $user->password = bcrypt($request->password);

//        $user->is_admin = $request->is_admin;

        if ($request->resellerLogo) {
            if ($user->resellerLogo) {
                \File::delete(public_path('logo') . "/" . $user->resellerLogo);
            }

            $imageName = time() . '.' . $request->resellerLogo->getClientOriginalExtension();
            $request->resellerLogo->move(public_path('logo'), $imageName);
            $user->logo = $imageName;
        }

        $user->is_rate_comparison_active = $request->rateComparison ? 1 : 0;

        $user->save();

        return back()->with('success', "Saved successfully !");
    }

    public function destroy($id)
    {
        $user = User::find($id);

        $user->delete();

        return back()->with("success", "Successfully deleted !");
    }

    public function invite()
    {
        return view("admin.users.invite");
    }

    public function deleteInvitation($id)
    {
        $invitation = Invite::find($id);

        $invitation->delete();

        return back()->with("success", "Successfully deleted !");

    }

    public function postInvite(InviteStoreRequest $request)
    {
        $request->validated();

        $code = gen_uuid();

        $invite = new Invite();
        $invite->namesurname = $request->namesurname;
        $invite->email = $request->email;
        $invite->code = $code;

        $invite->save();

        $to = $invite->email;
        $subject = 'Hoteldigilab Invitation';
        $message = view("email.invitation")->with("code", $code);

        // To send HTML mail, the Content-type header must be set
        $headers = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'To: Mary <' . $request->email . '>' . "\r\n";
        $headers .= 'From: Hoteldigilab <info@hoteldigilab.com>' . "\r\n";

        mail($to, $subject, $message, $headers);

        return back()->with("success", "Your invitation is successfully sent !");
    }

    public function switchUser(Request $request, $id){

        switchToSubUser($id);
        $redirectAfter = $request->get('redirect-after');

        return redirect($redirectAfter ?: url('/'));
    }
}
