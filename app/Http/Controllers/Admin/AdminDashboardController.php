<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Device;
use App\Models\SensorLog;

class AdminDashboardController extends Controller
{
    /**
     * Show the admin overview page.
     */
    public function index()
    {
        $totalUsers = User::count();
        $totalDevices = Device::count();
        $totalLogs = SensorLog::count();

        // Recent users
        $recentUsers = User::withCount('devices')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('admin.index', compact('totalUsers', 'totalDevices', 'totalLogs', 'recentUsers'));
    }
}
