<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        $branches = Branch::query()
            ->withCount('members')
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Branch::create([
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()
            ->route('branches.index')
            ->with('status', 'Branch created successfully.');
    }

    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:branches,name,' . $branch->id],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $branch->update([
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('branches.index')
            ->with('status', 'Branch updated successfully.');
    }

    public function destroy(Branch $branch): RedirectResponse
    {
        if ($branch->members()->exists()) {
            return redirect()
                ->route('branches.index')
                ->withErrors([
                    'branch' => 'This branch has members. Reassign members before deleting it.',
                ]);
        }

        $branch->delete();

        return redirect()
            ->route('branches.index')
            ->with('status', 'Branch deleted successfully.');
    }
}
