<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Ticket;
use App\Models\TicketCategory;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Morilog\Jalali\Jalalian;
use Carbon\Carbon;

class TicketController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    use Response, StatusCode, UtilityRequest;

    public function __construct()
    {
    }

    public function addTicket(Request $request)
    {
        $gregorianDate = '2025-03-09'; // تاریخ میلادی
//      $tehranTime = Carbon::now('Asia/Tehran');
//      return $tehranTime;

        $jalaliDateNow = Jalalian::fromDateTime(Date::now())->format('Y-m-d');

        $this->validate($request, [
            'subject' => 'required|string|min:3|max:255',
            'ticket_text' => 'required|string|min:5|max:5000',
            'date' => 'date',
            'user_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:ticket_category,id',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();
            $date=$request->date;

            $ticket = new Ticket();
            $ticket->subject = $request->subject;
            $ticket->ticket_text = $request->ticket_text;
            if(isset($request->date))
            {
                $ticket->date = $date;
            }
            else
            {
                $ticket->date = $jalaliDateNow;
            }
            $ticket->user_id = $request->user_id;
            $ticket->category_id = $request->category_id;
            $ticket->save();
            DB::commit();

            return $this->sendJsonResponse($ticket, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function editTicket(Request $request, $id)
    {
        $this->validate($request, [
            'subject' => 'required|string|min:3|max:255',
            'ticket_text' => 'required|string|min:5|max:5000',
            'date' => 'date',
            'user_id' => 'required|integer|exists:users,id',
            'category_id' => 'required|integer|exists:ticket_category,id',
        ]);

        try {
            // Start a transaction
            DB::beginTransaction();
            $ticket = Ticket::find($id);
            $ticket->subject = $request->subject;
            $ticket->ticket_text = $request->ticket_text;
            $ticket->date = $request->date;
            $ticket->user_id = $request->user_id;
            $ticket->category_id = $request->category_id;
            $ticket->save();
            DB::commit();

            return $this->sendJsonResponse($ticket, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showTicketCategory()
    {
        try {
            $ticketCategory = TicketCategory::get();
            DB::commit();
            return $this->sendJsonResponse($ticketCategory, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showUserTicket(Request $request)
    {
        $this->validate($request, [
            'user_id' => 'required|integer|exists:users,id',
        ]);

        try {
            $user_id = $request->user_id;
            $tickets = Ticket::where('user_id', $user_id)->with('ticketCategory')->get();
            DB::commit();

            return $this->sendJsonResponse($tickets, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showAllTickets()
    {
        try {
            $tickets = Ticket::with('user', 'ticketCategory')->get();
            DB::commit();

            return $this->sendJsonResponse($tickets, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

    public function editTicketStatus(Request $request, $id)
    {

        $this->validate($request, [
            'status' => 'required|integer',
        ]);

        $status = $request->status;

        try {
            // Start a transaction
            DB::beginTransaction();
            $ticket = Ticket::where('id', $id)->update(['status'=>$status]);
            DB::commit();

            return $this->sendJsonResponse([], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('OK'));

        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function downloadFile(Request $request, $id)
    {
        try {
            $tickets = Ticket::where('id',$id)->select('file_path')->get();
            DB::commit();

            return $this->sendJsonResponse($tickets[0], trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception)
        {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }

}

