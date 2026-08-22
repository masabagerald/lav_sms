@if($fees->isEmpty())
    <div class="empty-state my-4">
        <i class="icon-cash2"></i>
        <div class="empty-title">No fee structures for this selection</div>
        <span class="text-muted">Create a fee setup to start billing this class or session.</span>
    </div>
@else
<div class="table-responsive">
<table class="table table-hover datatable-button-html5-columns">
    <thead>
    <tr>
        <th>#</th>
        <th>Fee Title</th>
        <th class="text-right">Amount (UGX)</th>
        <th>Reference</th>
        <th>Class</th>
        <th>Method</th>
        <th>Description</th>
        <th class="text-center">Action</th>
    </tr>
    </thead>
    <tbody>
    @foreach($fees as $p)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td><span class="font-weight-semibold">{{ $p->title }}</span></td>
            <td class="text-right money-cell">{{ number_format($p->amount) }}</td>
            <td><code>{{ $p->ref_no }}</code></td>
            <td>{{ $p->my_class_id ? $p->my_class->name : 'All Classes' }}</td>
            <td>{{ ucwords(str_replace('_', ' ', $p->method)) }}</td>
            <td class="text-muted">{{ Str::limit($p->description, 40) }}</td>
            <td class="text-center">
                <div class="list-icons">
                    <div class="dropdown">
                        <a href="#" class="list-icons-item" data-toggle="dropdown" aria-label="Actions for {{ $p->title }}">
                            <i class="icon-menu9"></i>
                        </a>

                        <div class="dropdown-menu dropdown-menu-right">
                            {{--Edit--}}
                        <a href="{{ route('payments.edit', $p->id) }}" class="dropdown-item"><i class="icon-pencil"></i> Edit</a>
                            {{--Delete--}}
                            <a id="{{ $p->id }}" onclick="confirmDelete(this.id)" href="#" class="dropdown-item text-danger"><i class="icon-trash"></i> Delete</a>
                            <form method="post" id="item-delete-{{ $p->id }}" action="{{ route('payments.destroy', $p->id) }}" class="hidden">@csrf @method('delete')</form>

                        </div>
                    </div>
                </div>
            </td>
        </tr>
    @endforeach
    </tbody>
</table>
</div>
@endif
