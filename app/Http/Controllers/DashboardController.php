<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalMembers = Member::count();
        $membersAddedThisMonth = Member::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $recentMembers = Member::latest()->take(6)->get();

        return view('dashboard.index', compact(
            'totalMembers',
            'membersAddedThisMonth',
            'recentMembers'
        ));
    }
}
