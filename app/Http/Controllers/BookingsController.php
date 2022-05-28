<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class BookingsController extends Controller
{
    public function __construct()
    {
        // language change
        $this->middleware('common');

        $this->middleware('auth')->except('get_customers');

        $this->event        = new Event;
        $this->booking      = new Booking;
        $this->user         = new User;
        $this->customer_id  = null;
    }

    // book tickets
    public function book_tickets(Request $request)
    {
        // 1. If admin is making booking then it will be for a customer
        $this->is_admin($request);

        // 2. General validation
        $data = $this->general_validation($request);
        if(!$data['status'])
            return error($data['error'], Response::HTTP_BAD_REQUEST);

        // 3. Timing & Date check
        $pre_time_booking   =  $this->time_validation($data);
        if(!$pre_time_booking['status'])
            return error($pre_time_booking['error'], Response::HTTP_BAD_REQUEST);

        // 4. Check if it's a valid customer
        $params  = ['customer_id' => $this->customer_id,];
        $customer   = $this->user->get_customer($params);
        if(empty($customer))
            return error($pre_time_booking['error'], Response::HTTP_BAD_REQUEST);

        // 5. Create booking
        $booking                        = [];
        $booking['customer_id']         = $this->customer_id;
        $booking['customer_name']       = $customer['name'];
        $booking['customer_email']      = $customer['email'];
        $booking['event_id']            = $data['event']['id'];
        $booking['quantity']            = $data['quantity'];
        $booking['status']              = 1;
        $booking['created_at']          = Carbon::now();
        $booking['updated_at']          = Carbon::now();
        $booking['event_title']         = $data['event']['title'];
        $booking['event_category']      = $data['event']['category_name'];
        $booking['event_start_date']    = $data['event']['start_date'];
        $booking['event_end_date']      = $data['event']['end_date'];
        $booking['event_start_time']    = $data['event']['start_time'];
        $booking['event_end_time']      = $data['event']['end_time'];
        $booking['price']               = 0;
        $booking['net_price']           = 0;
        $booking['order_number']        = time().rand(1,988);

        // Free events only
        $flag                           =  $this->booking->make_booking($booking);

        // in case of database failure
        if(empty($flag))
            return error('Database failure!', Response::HTTP_REQUEST_TIMEOUT);

        // 6. Send notifications
        $this->finish_booking($booking);

        // redirect no matter what so that it never turns backreturn response
        return response([
            'status'    => true,
            'url'       => Auth::user()->hasRole('admin') ? eventmie_url(config('eventmie.route.admin_prefix').'/bookings') : route('eventmie.mybookings_index'),
            'message'   => __('eventmie::em.congrats').' '.__('eventmie::em.booking').' '.__('eventmie::em.successful'),
        ], Response::HTTP_OK);
    }

    // get customers
    public function get_customers(Request $request)
    {
        $customers     = [];
        // get customers when admin is making booking
        if(Auth::check())
            if(Auth::user()->hasRole('admin'))
                $customers  = $this->event->get_customers();

        return response()->json([
            'customers' => $customers,
            'status' => true,
        ]);
    }



}
