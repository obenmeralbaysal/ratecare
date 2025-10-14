<?php

namespace App\Http\Controllers\Reseller;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserStoreRequest;
use PHPExcel;
use PHPExcel_IOFactory;

class UserController extends Controller
{
    public function index()
    {
        $users = user()->subUsers()->filter()->paginate();

        return view("reseller.users.index")
            ->with("users", $users);
    }

    public function create()
    {
        $user = new User();

        return view("reseller.users.create")
            ->with("editing", false)
            ->with("user", $user);
    }

    public function store(UserStoreRequest $request)
    {
        $reseller = user();

        $user = new User();

        $user->namesurname = $request->namesurname;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->user_type = 0;
        $user->reseller_id = $reseller->id;


        $reseller->subUsers()->save($user);

        return back()->with('success', "Saved successfully !");
    }

    public function edit($id)
    {
        $user = User::find($id);

        return view("reseller.users.create")
            ->with("editing", true)
            ->with("user", $user);
    }

    public function update(UserUpdateRequest $request, $id)
    {
        $user = User::find($id);

        $user->namesurname = $request->namesurname;
        $user->email = $request->email;


        if($request->password)
            $user->password = bcrypt($request->password);

//        $user->is_admin = $request->is_admin;

        $user->user_type = 0;

        $user->save();

        return back()->with('success', "Saved successfully !");
    }

    public function destroy($id)
    {
        $user = User::find($id);

        $user->delete();

        return back()->with("success", "Successfully deleted !");
    }

    public function invite(){
        return view("reseller.users.invite");
    }

    public function deleteInvitation($id){
        $invitation = Invite::find($id);

        $invitation->delete();

        return back()->with("success", "Successfully deleted !");

    }

    public function postInvite(InviteStoreRequest $request){
        $request->validated();

        $code = gen_uuid();

        $invite = new Invite();
        $invite->namesurname = $request->namesurname;
        $invite->email = $request->email;
        $invite->code = $code;
        $invite->reseller_id = user()->id;

        $invite->save();

        $to      = $invite->email;
        $subject = 'Hoteldigilab Invitation';
        $message = view("email.invitation")->with("code", $code);

        // To send HTML mail, the Content-type header must be set
        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'To: Mary <'.$request->email.'>' . "\r\n";
        $headers .= 'From: Hoteldigilab <info@hoteldigilab.com>' . "\r\n";

        mail($to, $subject, $message, $headers);

        return back()->with("success", "Your invitation is successfully sent !");
    }

    public function switchUser(Request $request, $id){

        switchToSubUser($id);
        $redirectAfter = $request->get('redirect-after');

        return redirect($redirectAfter ?: url('/'));
    }

    public function exportExcel(){
        $excel = new PHPExcel();
        $excel->setActiveSheetIndex(0);

        $excel->getActiveSheet()->getStyle('A1:AB1')->getFont()->setBold(true);

        $excel->getActiveSheet()->getColumnDimension('A')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('B')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $excel->getActiveSheet()->getColumnDimension('K')->setWidth(30);

        $excel->getActiveSheet()->SetCellValue('A1', "Name");
        $excel->getActiveSheet()->SetCellValue('B1', "Sabee URL");
        $excel->getActiveSheet()->SetCellValue('C1', "Sabee Hotel ID");
        $excel->getActiveSheet()->SetCellValue('D1', "Booking URL");
        $excel->getActiveSheet()->SetCellValue('E1', "Hotels URL");
        $excel->getActiveSheet()->SetCellValue('F1', "Odamax URL");
        $excel->getActiveSheet()->SetCellValue('G1', "Otelz URL");
        $excel->getActiveSheet()->SetCellValue('H1', "Tatilsepeti URL");
        $excel->getActiveSheet()->SetCellValue('I1', "Reseliva Hotel ID");
        $excel->getActiveSheet()->SetCellValue('J1', "Hotelrunner URL");
        $excel->getActiveSheet()->SetCellValue('K1', "Etstur Hotel ID");

        $rowNum = 2;

        $users = user()->subUsers()->get();
        foreach ($users as $u){
            $hotels = $u->hotels()->get();
            foreach ($hotels as $h){
                $excel->getActiveSheet()->SetCellValue('A' . $rowNum, $h->name);
                $excel->getActiveSheet()->SetCellValue('B' . $rowNum, $h->sabee_url);
                $excel->getActiveSheet()->SetCellValue('C' . $rowNum, $h->sabee_hotel_id);
                $excel->getActiveSheet()->SetCellValue('D' . $rowNum, $h->booking_url);
                $excel->getActiveSheet()->SetCellValue('E' . $rowNum, $h->hotels_url);
                $excel->getActiveSheet()->SetCellValue('F' . $rowNum, $h->odamax_url);
                $excel->getActiveSheet()->SetCellValue('G' . $rowNum, $h->otelz_url);
                $excel->getActiveSheet()->SetCellValue('H' . $rowNum, $h->tatilsepeti_url);
                $excel->getActiveSheet()->SetCellValue('I' . $rowNum, $h->reseliva_hotel_id);
                $excel->getActiveSheet()->SetCellValue('J' . $rowNum, $h->hotelrunner_url);
                $excel->getActiveSheet()->SetCellValue('K' . $rowNum, $h->etstur_hotel_id);
                $rowNum++;
            }
        }

        // We'll be outputting an excel file
        header('Content-type: application/vnd.ms-excel');

        header('Content-Disposition: attachment; filename="hotels.xls"');

        $objWriter = $objWriter = PHPExcel_IOFactory::createWriter($excel, 'Excel2007');
        ob_end_clean();
        $objWriter->save('php://output');
    }
}
