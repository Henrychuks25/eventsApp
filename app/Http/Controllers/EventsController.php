<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Country;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EventsController extends Controller
{
    public function __construct()
    {
        // language change
        $this->middleware('common');

        $this->event    = new Event;
        $this->category = new Category;
        $this->country  = new Country;
    }

    /* ==================  EVENT LISTING ===================== */

    /**
     * Show all events
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function index()
    {
        // get prefix from eventmie config
        $path = false;
        if(!empty(config('eventmie.route.prefix')))
            $path = config('eventmie.route.prefix');

        return view('events.index', compact($path) );
    }


    // filters for get_events function
    private function event_filters(Request $request)
    {
        $request->validate([
            'category'          => 'max:256|String|nullable',
            'search'            => 'max:256|String|nullable',
            'start_date'        => 'date_format:Y-m-d|nullable',
            'end_date'          => 'date_format:Y-m-d|nullable',
        ]);

        $category_id            = null;
        $category               = urldecode($request->category);
        $search                 = $request->search;

        // search category id
        if(!empty($category))
        {
            $categories  = $this->category->get_categories();

            foreach($categories as $key=> $value)
            {
                if($value['name'] == $category)
                    $category_id = $value['id'];
            }
        }

        $filters                    = [];
        $filters['category_id']     = $category_id;
        $filters['search']          = $search;
        $filters['start_date']      = $request->start_date;
        $filters['end_date']        = $request->end_date;

        // in case of today and tomorrow and weekand
        if($request->start_date == $request->end_date)
            $filters['end_date']     = null;

        return $filters;
    }

    // EVENT LISTING APIs
    // get all events
    public function events(Request $request)
    {
        $filters         = [];
        // call event fillter function
        $filters         = $this->event_filters($request);

        $events          = $this->event->events($filters);

        $event_ids       = [];

        foreach($events as $key => $value)
            $event_ids[] = $value->id;

        $events_data             = [];
        foreach($events as $key => $value)
            $events_data[$key]             = $value;

        // set pagination values
        $event_pagination = $events->jsonSerialize();

        return response([
            'events'=> [
                'data' => $events_data,
                'total' => $event_pagination['total'],
                'per_page' => $event_pagination['per_page'],
                'current_page' => $event_pagination['current_page'],
                'last_page' => $event_pagination['last_page'],
                'from' => $event_pagination['from'],
                'to' => $event_pagination['to']
            ],
        ], Response::HTTP_OK);
    }

    /**
     * Show single event
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function show(Event $event)
    {
        // it is calling from model because used subquery
        $event = $this->event->get_event($event->slug);

        if(!$event->status || !$event->publish)
            abort('404');

        // event category
        $category            = $this->category->get_event_category($event['category_id']);

        $max_ticket_qty      = (int) setting('booking.max_ticket_qty');

        // event country
        $country            = $this->country->get_event_country($event['country_id']);

        // check event and or not
        $ended  = false;

        // check event is ended or not
        if(\Carbon\Carbon::now()->format('Y/m/d') > \Carbon\Carbon::createFromFormat('Y-m-d', $event['start_date'])->format('Y/m/d'))
            $ended = true;

        return view('events.show', compact('event', 'ended', 'category', 'country', 'max_ticket_qty'));
    }


    // get all categories
    public function categories()
    {
        $categories  = $this->category->get_categories();

        if(empty($categories))
        {
            return response()->json(['status' => false]);
        }
        return response()->json(['status' => true, 'categories' => $categories ]);
    }
}
