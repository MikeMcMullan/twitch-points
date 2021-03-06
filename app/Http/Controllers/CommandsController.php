<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Channel;

class CommandsController extends Controller
{
    public function __construct()
    {
        $this->middleware(['featureDetection:commands']);
    }

    public function index(Channel $channel)
    {
        if (\Gate::allows('admin-channel', $channel)) {
            return view('commands');
        }

        return view('commands-public');
    }
}
