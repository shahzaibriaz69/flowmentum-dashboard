<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
     public static function O_AUTH_URL() : string
    {
        return 'https://marketplace.gohighlevel.com/oauth/chooselocation?' . static::baseConnectQuery();
    }public function authenticate(Request $request){

        return redirect()->away(self::O_AUTH_URL());
    }
    private static function baseConnectQuery() : string
    {
        return http_build_query([
            'response_type' => 'code',
            'redirect_uri'  => route('marketplace.callback'),
            'client_id'     => env('MARKETPLACE_CLIENT_ID'),
            'scope'         => 'contacts.readonly contacts.write locations.readonly locations/customFields.readonly locations/customFields.write locations/customValues.readonly locations/customValues.write opportunities.readonly opportunities.write calendars.readonly calendars.write calendars/events.readonly calendars/events.write calendars/groups.readonly calendars/groups.write calendars/resources.readonly calendars/resources.write invoices/estimate.readonly invoices/template.write invoices/template.readonly invoices/schedule.write invoices/schedule.readonly invoices.write invoices.readonly users.readonly users.write locations/tags.readonly locations/tags.write',
        ]);
    }

    public function callback(Request $request){
        \Log::info($request->all());
    }
}
