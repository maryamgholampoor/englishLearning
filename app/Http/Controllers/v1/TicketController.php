<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\Controller;
use App\Http\Utilities\Request as UtilityRequest;
use App\Http\Utilities\Response;
use App\Models\Book;
use App\Models\BookSeason;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Utilities\StatusCode;
use App\Models\BookCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Tymon\JWTAuth\Facades\JWTAuth;

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
        $this->validate($request, [
            'subject' => 'required|string',
            'ticket_text' => 'required|string',
            'date' => 'required|date',
            'user_id' => 'required|integer',
            'category_id' => 'required|integer',
        ]);
        try {
            // Start a transaction
            DB::beginTransaction();
            $ticket = new Ticket();
            $ticket->subject = $request->subject;
            $ticket->ticket_text = $request->ticket_text;
            $ticket->date = $request->date;
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
            'subject' => 'required|string',
            'ticket_text' => 'required|string',
            'date' => 'required|date',
            'user_id' => 'required|integer',
            'category_id' => 'required|integer',
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

    public function showUserTicket(Request $request)
    {
        try {
            $user_id = $request->user_id;
            $tickets = Ticket::where('user_id', $user_id)->get();
            DB::commit();

            return $this->sendJsonResponse($tickets, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        }
        catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }
    }

    public function showAllTickets(Request $request)
    {
        try {
            $tickets = Ticket::with('user')->get();
            DB::commit();

            return $this->sendJsonResponse($tickets, trans('message.result_is_ok'), $this->getStatusCodeByCodeName('Created'));
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->sendJsonResponse([], $exception->getMessage(), $this->getStatusCodeByCodeName('Internal Server Error'));
        }

    }


}

