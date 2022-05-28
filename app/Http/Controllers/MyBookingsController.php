<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class MyBookingsController extends Controller
{
    public function __construct()
    {
        // language change
        $this->middleware('common');

        $this->middleware(['customer']);

        $this->event        = new Event;
        $this->booking      = new Booking;
    }

    /**
     * Customer bookings
     *
     * @return array|\Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // get prefix from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');

        return view('bookings.customer_bookings', compact('path'));
    }

    /**
     * Get customer bookings
     *  */
    public function mybookings()
    {
        $params     = [
            'customer_id'  => Auth::id(),
        ];

        $bookings    = $this->booking->get_my_bookings($params);

        if($bookings->isEmpty())
            return error(__('eventmie::em.booking').' '.__('eventmie::em.not_found'), Response::HTTP_BAD_REQUEST );

        return response([
            'bookings'  => $bookings->jsonSerialize(),
        ], Response::HTTP_OK);

    }

}
