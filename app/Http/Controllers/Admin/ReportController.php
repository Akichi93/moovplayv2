<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function Reports()
    {
        $respo = Admin::select('role')->where('id', Auth::guard('admin')->user()->id)->value('role');
        $reports = Report::join("users", 'reports.user_id', '=', 'users.id')->get();
        return view('admin.report.reports')->with(compact('reports', 'respo'));
    }
}
