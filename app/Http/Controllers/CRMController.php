<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CRMController extends Controller
{
    public function index($page = 'people')
    {
        $locationId = auth()->user()->location_id;

        return view('workspace', [
            'page' => $page,
            'location_id' => $locationId
        ]);
    }

    public function people() { return $this->index('people'); }
    public function inbox() { return $this->index('inbox'); }
    public function pipeline() { return $this->index('pipeline'); }
    public function marketing() { return $this->index('marketing'); }
    public function automations() { return $this->index('automations'); }
    public function sites() { return $this->index('sites'); }
}