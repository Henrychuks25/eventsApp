<?php

use App\Http\Controllers\BookingsController;
use App\Http\Controllers\EventsAppController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\MyEventsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Models\User;
use Classiebit\Eventmie\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use TCG\Voyager\Voyager;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
$namespace = 'App\Http\Controllers';

//Route::get('/', function () {
//    return view('welcome');
//});
//Route::get('/welcome', [WelcomeController::class, 'index']);



Route::group(['prefix' => 'admin'], function () {
    (new TCG\Voyager\Voyager)->routes();
});
// Welcome Routes -------------------------------------------------------------
//Route::get('/', "WelcomeController@index")->name('welcome');
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

Route::get('/home', function() {
    return redirect()->route('welcome');
});
//
//// customer redirect to welcome page
//return redirect()->route('eventmie.welcome');
//
//})->name('notify_read');


Route::group(['prefix' => 'admin'], function () {
    (new TCG\Voyager\Voyager)->routes();
});

//Route::get('eventmie-assets', $namespace.'\EventsAppController@assets')->name('eventmie_assets');
Route::get('/eventmie-assets', [EventsAppController::class, 'assets'])->name('eventmie_assets');


// Auth route --------------------------------------------------------------------
Auth::routes();

// Login --------------------------------------------------------------------------

Route::get('login', [LoginController::class,'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login'])->name('login_post');
// --------------------------------------------------------------------------

// Events Routes -----------------------------------------------------------------
//Route::prefix('/events')->group(function () use ($namespace) {
//    $controller = $namespace.'\EventsController';

//    Route::get('/', "$controller@index")->name('events_index');
//    Route::get('/', [EventsController::class, 'index'])->name('index');
    Route::get('/events', [EventsController::class, 'index'])->name('events_index');

    Route::get('/api/get_events', [EventsController::class, 'events'])->name('events');

    // Wildcard routes
    Route::get('/{event}', [EventsController::class, 'show'])->name('events_show');

    Route::get('/api/categories', [EventsController::class, 'categories'])->name('myevents_categories');

//});
// --------------------------------------------------------------------------

// Bookings Routes -----------------------------------------------------------------
Route::prefix('/bookings')->group(function () use ($namespace)  {

    $controller = $namespace.'\BookingsController';
    Route::get('/api/get_customers', [BookingsController::class, 'get_customers'])->name('bookings_get_customers');
    Route::get('/api/book_tickets', [BookingsController::class, 'book_tickets'])->name('bookings_book_tickets');

});
// --------------------------------------------------------------------------

// My Bookings Routes For Customer  -----------------------------------------------------------------
Route::prefix('/mybookings')->group(function () use($namespace) {

    $controller = $namespace.'\MybookingsController';

    Route::get('/', [BookingsController::class, 'index'])->name('mybookings_index');
    Route::get('/api/get_mybookings', [BookingsController::class, 'mybookings'])->name('mybookings');


});
// --------------------------------------------------------------------------


// Events Routes -----------------------------------------------------------------
Route::prefix('/myevents')->group(function () use ($namespace) {
    $controller = $namespace.'\MyEventsController';

    Route::get('/', [MyEventsController::class,'index'])->name('myevents_index');
    Route::get('/api/get_myevents', [MyEventsController::class,'get_myevents'])->name('myevents');                  // axios route

    // event create and edit
    Route::get('/manage/{slug?}', [MyEventsController::class,'form'])->name('myevents_form');

    Route::post('/api/store', [MyEventsController::class,'store'])->name('myevents_store');                             // axios route
    Route::post('/api/store_media', [MyEventsController::class,'store_media'])->name('myevents_store_media');           // axios route
    Route::post('/api/store_location', [MyEventsController::class,'store_location'])->name('myevents_store_location');  // axios route
    Route::post('/api/store_timing', [MyEventsController::class,'store_timing'])->name('myevents_store_timing');        // axios route
    Route::post('/api/store_seo', [MyEventsController::class,'store_seo'])->name('myevents_store_seo');  // axios route
    Route::get('/api/countries', [MyEventsController::class,'countries'])->name('myevents_countries');      // axios route
    Route::post('/api/get_myevent', [MyEventsController::class,'get_user_event'])->name('get_myevent');      // axios route
    Route::post('/api/publish_myevent', [MyEventsController::class,'event_publish'])->name('publish_myevent');      // axios route

    // event delete
    Route::get('/delete/{slug}', [MyEventsController::class,'delete_event'])->name('delete_event');       // axios route

});
//---------------------------------------------------------------------------------------------------------------


// Notification Route ----------------------------------------------------------------------
Route::prefix('/notifications')->group(function () use ($namespace)  {

    // make read notification
    Route::get('/read/{n_type}', function($n_type){

        if($n_type) {

            $id   = Auth::id();
            $user = User::find($id);
            $user->unreadNotifications->where('n_type', $n_type)->markAsRead();

        }
        // admin redirect to dashboard
        if(Auth::user()->hasRole('admin'))
        {
            if($n_type == "user")
                return redirect()->route('voyager.users.index');
            else
                return redirect()->route('voyager.dashboard');
        }

        // customer redirect to notification's related page
        if(Auth::user()->hasRole('customer'))
        {
            // create events notification
            if($n_type == "user")
                return redirect()->route('eventmie.profile');

            // create booking notification
            if($n_type == "bookings")
                return redirect()->route('eventmie.mybookings_index');
        }

        // customer redirect to welcome page
        return redirect()->route('eventmie.welcome');

    })->name('notify_read');


});
//------------------------------------------------------------------------------------------------------------------

//Profile Route ----------------------------------------------------------------------------
Route::prefix('/profile')->group(function () use ($namespace) {

    $controller = $namespace.'\ProfileController';
    // view
    Route::get('/', [ProfileController::class,'index'])->name('profile');

    Route::post('/updateAuthUser',[ProfileController::class, 'updateAuthUser'])->name('updateAuthUser');
});
//------------------------------------------------------------------------------------------

//Contact Route ----------------------------------------------------------------------------
Route::prefix('/contact')->group(function () use ($namespace) {

    $controller = $namespace.'\ContactController';

    // contact view page load
    Route::get('/', [ProfileController::class, 'index'])->name('contact');

    // contact save into contacts tables
    Route::post('/save', [ProfileController::class, 'store_contact'])->name('store_contact');

});
//-------------------------------------------------------------------------------------------
