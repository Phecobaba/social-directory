@extends('layouts.app')

@section('title', 'D.S.F.C Dashboard')

@section('content')
    <div class="row">
        <div class="col-lg-4 col-12">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalMembers }}</h3>
                    <p>Total Members</p>
                </div>
                <div class="icon">
                    <i class="fas fa-users"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $membersAddedThisMonth }}</h3>
                    <p>New Members This Month</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-12">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $totalBranches }}</h3>
                    <p>Total Branches ({{ $activeBranches }} active)</p>
                </div>
                <div class="icon">
                    <i class="fas fa-code-branch"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Recent Members</h3>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Member</th>
                                <th>Branch</th>
                                <th>Phone</th>
                                <th>House Address</th>
                                <th>Added</th>
                                <th class="text-nowrap">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentMembers as $member)
                                <tr>
                                    <td>{{ $member->full_name }}</td>
                                    <td>{{ $member->branch?->name ?: 'N/A' }}</td>
                                    <td>{{ $member->phone_number }}</td>
                                    <td>{{ $member->house_address ?: 'N/A' }}</td>
                                    <td>{{ $member->created_at?->format('M d, Y') }}</td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye mr-1"></i>View
                                        </a>
                                        <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-edit mr-1"></i>Edit
                                        </a>
                                        <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this member?');">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="member_id" value="{{ $member->id }}">
                                            <button type="submit" class="btn btn-sm btn-danger">
                                                <i class="fas fa-trash mr-1"></i>Delete
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No members have been added yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Members by Branch</h3>
                    <a href="{{ route('branches.index') }}" class="btn btn-xs btn-outline-primary">Manage</a>
                </div>
                <div class="card-body">
                    @if ($branchDistribution->isEmpty())
                        <p class="text-muted mb-0">No branches available yet.</p>
                    @else
                        @foreach ($branchDistribution as $branch)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold">{{ $branch->name }}</span>
                                    <span class="text-muted">{{ $branch->members_count }} members</span>
                                </div>
                                <div class="progress progress-sm mb-1">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $branch->member_percentage }}%" aria-valuenow="{{ $branch->member_percentage }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">{{ $branch->member_percentage }}%</small>
                                    <a href="{{ route('members.index', ['branch_id' => $branch->id]) }}" class="btn btn-xs btn-outline-secondary">View Members</a>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Quick Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('members.create') }}" class="btn btn-primary btn-block mb-3">
                        <i class="fas fa-user-plus mr-2"></i>Add New Member
                    </a>
                    <a href="{{ route('members.index') }}" class="btn btn-outline-secondary btn-block">
                        <i class="fas fa-address-book mr-2"></i>View Member Directory
                    </a>
                    <a href="{{ route('members.backup') }}" class="btn btn-outline-dark btn-block mt-3">
                        <i class="fas fa-download mr-2"></i>Download Full Backup
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
