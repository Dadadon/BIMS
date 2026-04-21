<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HR\Employee;
use App\Models\HR\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeamController extends Controller
{
    public function index(): View
    {
        $teams = Team::with(['leader', 'members'])
            ->withCount('sales')
            ->orderBy('name')
            ->get();

        $employees = Employee::active()->orderBy('lastname')->get();

        return view('admin.teams.index', compact('teams', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:teams,name'],
            'leader_id'   => ['nullable', 'exists:employees,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $data['is_active'] = true;
        Team::create($data);

        return redirect()->route('admin.teams.index')->with('success', 'Team created.');
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:teams,name,' . $team->id],
            'leader_id'   => ['nullable', 'exists:employees,id'],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $data['is_active'] ?? false;
        $team->update($data);

        return redirect()->route('admin.teams.index')->with('success', 'Team updated.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();
        return redirect()->route('admin.teams.index')->with('success', 'Team deleted.');
    }
}
