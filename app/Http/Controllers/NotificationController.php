<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use DB;

class NotificationController extends Controller
{
    public function index()
    {
        $model = Notification::select('id', 'title', 'text', 'date')->whereNull('read_at')->get();

        $arr = [];
        if (isset($model) && count($model) > 0)
            foreach ($model as $key => $value)
                $value->date = date('d.m.Y H:i', strtotime($value->date));

        return response()->json([
            'data' => $model,
            'status' => true,
            'message' => "success"
        ], 200);
    }
}