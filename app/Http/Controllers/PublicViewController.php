<?php

namespace Quotebot\Http\Controllers;

use Illuminate\Support\Facades\Config;

class PublicViewController extends Controller {

    /*
    |--------------------------------------------------------------------------
    | Welcome Controller
    |--------------------------------------------------------------------------
    |
    | This controller renders the "marketing page" for the application and
    | is configured to only allow guests. Like most of the other sample
    | controllers, you are free to modify or remove it as you desire.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Show the application welcome screen to the user.
     *
     * @return Response
     */
    public function index()
    {
        $scripts = ['public-combined.js'];
        return view('quotes.public', [
            'apiToken'  => env('QUOTEBOT_API_TOKEN'),
            'pusherUrl' => Config::get('tokenlyPusher.clientUrl'),
            'scripts'   => $scripts,
        ]);
    }

}
