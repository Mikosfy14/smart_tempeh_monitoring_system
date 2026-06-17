<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\SensorLog;
use App\Models\User;
use Illuminate\Http\Request;

class SensorLogController extends Controller
{
    /**
     * Show sensor logs with pagination and filters.
     */
    public function index(Request $request)
    {
        $query = SensorLog::with('device.user')
            ->orderBy('created_at', 'desc');

        // Filter by date range
        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->where('created_at', '>=', $request->date_from . ' 00:00:00');
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->where('created_at', '<=', $request->date_to . ' 23:59:59');
        });

        // Filter by device
        $query->when($request->filled('device_id'), function ($q) use ($request) {
            $q->where('device_id', $request->device_id);
        });

        // Filter by user (through device relationship)
        $query->when($request->filled('user_id'), function ($q) use ($request) {
            $q->whereHas('device', function ($dq) use ($request) {
                $dq->where('user_id', $request->user_id);
            });
        });

        $logs = $query->paginate(50)->appends($request->query());

        // Data for filter dropdowns
        $devices = Device::select('id', 'device_id', 'label_rak', 'device_name')
            ->orderBy('device_id')
            ->get();

        $users = User::select('id', 'name')
            ->orderBy('name')
            ->get();

        return view('admin.sensor-logs', compact('logs', 'devices', 'users'));
    }

    /**
     * Purge sensor logs older than 30 days.
     */
    public function purgeOldLogs(Request $request)
    {
        $cutoff = now()->subDays(30);
        $deleted = SensorLog::where('created_at', '<', $cutoff)->delete();

        $message = "Berhasil menghapus {$deleted} log sensor yang lebih lama dari 30 hari.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
            ]);
        }

        return redirect()->route('admin.sensor-logs')->with('success', $message);
    }

    /**
     * Delete selected sensor logs by IDs (bulk delete via checkbox).
     */
    public function deleteSelected(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:sensor_logs,id',
        ]);

        $deleted = SensorLog::whereIn('id', $request->ids)->delete();

        $message = "Berhasil menghapus {$deleted} log sensor yang dipilih.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
            ]);
        }

        return redirect()->route('admin.sensor-logs')->with('success', $message);
    }

    /**
     * Delete all sensor logs (optionally filtered by device/date).
     */
    public function deleteAll(Request $request)
    {
        $query = SensorLog::query();

        // Optional: filter by device before deleting
        if ($request->filled('device_id')) {
            $query->where('device_id', $request->device_id);
        }

        // Optional: filter by date range before deleting
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
        }
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $deleted = $query->delete();

        $message = "Berhasil menghapus {$deleted} log sensor.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted' => $deleted,
            ]);
        }

        return redirect()->route('admin.sensor-logs')->with('success', $message);
    }
}
