<?php

namespace App\Http\Controllers;

use App\Models\Row;

class RowController extends Controller
{
    public function index()
    {
        $rows = Row::all()->groupBy('date');

        return response()->json($rows);
    }
}
