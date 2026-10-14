@extends('backend.app')
@section('title', 'Learning Groups')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="card-title mb-1">Learning Groups</h4>
                    <p class="text-muted mb-0">Manage groups for Case Studies and FNH Architecture (e.g. Foundation, Intermediate, Advanced). Premium flag at the group level controls access to all learnings within the group.</p>
                </div>
                <a href="{{ route('learning-group.create') }}" class="btn btn-primary">Add New Group</a>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                <div class="table-responsive">
                    <table class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Category</th>
                                <th>Label</th>
                                <th>Color</th>
                                <th>Premium</th>
                                <th>Learnings</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $i => $group)
                            <tr>
                                <td>{{ $i + 1 }}</td>
                                <td>{{ ucwords(str_replace('_', ' ', $group->category)) }}</td>
                                <td>
                                    <span class="badge" style="background:{{ $group->color ?? '#6c757d' }}; color:#fff;">
                                        {{ $group->label }}
                                    </span>
                                </td>
                                <td><code>{{ $group->color }}</code></td>
                                <td>
                                    @if($group->is_premium)
                                        <span class="badge bg-danger"><i class="fa fa-lock"></i> Premium</span>
                                    @else
                                        <span class="badge bg-success"><i class="fa fa-unlock"></i> Foundation</span>
                                    @endif
                                </td>
                                <td>{{ $group->learnings()->count() }}</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('learning-group.edit', $group->id) }}" class="btn btn-primary" title="Edit">
                                            <i class="fe fe-edit"></i>
                                        </a>
                                        <form action="{{ route('learning-group.destroy', $group->id) }}" method="POST" onsubmit="return confirm('Delete this group? Learnings will be disassociated but not deleted.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Delete">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                            @if($groups->isEmpty())
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No groups yet. <a href="{{ route('learning-group.create') }}">Create your first group</a>.</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
