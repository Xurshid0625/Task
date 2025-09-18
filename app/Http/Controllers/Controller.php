<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function authorResponse($author, $message, $status = 200)
    {
        return response()->json(compact('message', 'author'), $status);
    }

    protected function bookResponses($book, $message, $status = 200)
    {
        return response()->json(compact('message', 'book'), $status);
    }

    protected function rentResponses($rent, $message, $status = 200)
    {
        return response()->json(compact('message', 'rent'), $status);
    }

    protected function statsResponses($stats, $message, $status = 200)
    {
        return response()->json(compact('message', 'stats'), $status);
    }

    protected function ActiveRentalsResponses($activeRentals, $message, $status = 200)
    {
        return response()->json(compact('message', 'activeRentals'), $status);
    }
}
