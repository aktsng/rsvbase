<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $monthlyReservations = Reservation::confirmed()
            ->whereBetween('created_at', [$monthStart, $monthEnd]);

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'total_facilities' => Facility::count(),
                'total_owners' => User::where('role', 'owner')->count(),
                'monthly_gmv' => $monthlyReservations->sum('total_amount'),
                'monthly_platform_fees' => $monthlyReservations->sum('platform_fee'),
                'monthly_reservations_count' => $monthlyReservations->count(),
            ],
        ]);
    }
}
