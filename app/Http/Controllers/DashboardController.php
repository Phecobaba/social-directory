<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Member;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalMembers = Member::count();
        $totalBranches = Branch::count();
        $activeBranches = Branch::query()->where('is_active', true)->count();
        $membersAddedThisMonth = Member::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $recentMembers = Member::query()
            ->with('branch')
            ->latest()
            ->take(6)
            ->get();

        $branchDistribution = Branch::query()
            ->withCount('members')
            ->orderByDesc('members_count')
            ->orderBy('name')
            ->get(['id', 'name', 'is_active'])
            ->map(function (Branch $branch) use ($totalMembers) {
                $branch->member_percentage = $totalMembers > 0
                    ? round(($branch->members_count / $totalMembers) * 100, 1)
                    : 0;

                return $branch;
            });

        return view('dashboard.index', compact(
            'totalMembers',
            'totalBranches',
            'activeBranches',
            'membersAddedThisMonth',
            'recentMembers',
            'branchDistribution'
        ));
    }
}
