@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header px-3">
        <h2 class="card-title fw-bold">
            Permissions
        </h2>

        <div class="card-toolbar">
            <button class="btn btn-flex btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#permissionModal" data-action="create">
                <i class="ki-duotone ki-plus fs-2"></i>
                Add Permission
            </button>
        </div>
    </div>
    <div class="card-body p-0">
        <!-- <div class="table-responsive">
            <table class="table table-hover table-row-dashed">
                <thead class="text-start bg-dark text-white fw-bold fs-7 text-uppercase gs-0">
                    <th class="px-2 rounded-start">#</th>
                    <th>Name</th>
                    <th>Resource</th>
                    <th class="text-end px-2 rounded-end">Action</th>
                </thead>
                <tbody>
                    @php $count = 0; @endphp
                    @foreach($permissions as $key => $permission)
                        @php $count++; @endphp
                        <tr>
                            <td>{{ $count }}</td>
                            <td>{{ $permission->name }}</td>
                            <td>{{ $permission->group_name }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-icon btn-primary btn-active-dark" data-bs-toggle="modal" data-bs-target="#permissionModal" data-action="edit" data-permission-id="{{ $permission->id }}"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-icon btn-danger btn-active-dark" onclick="confirmDelete('{{ route('permissions.destroy', $permission->id) }}')"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div> -->
    </div>
</div>

@php
    $groupedPermissions = $permissions->groupBy('group_name');
@endphp

<div class="row mt-3">
    @foreach($groupedPermissions as $groupName => $groupPermissions)
        <div class="col-md-3 mb-3">
            <div class="card mb-3 h-100 shadow">
                <div class="card-header bg-white py-3 px-3">
                    <h6 class="card-title m-0">{{ ucfirst($groupName) }}</h6>
                </div>
                <div class="card-body px-3 py-3">
                    @foreach($groupPermissions as $key => $permission)
                        @php
                            // Remove the prefix before the first underscore
                            $permissionName = explode('_', $permission->name, 2)[1];
                        @endphp
                        <div class="d-flex justify-content-between @if (!$loop->last) pb-2 border-bottom mb-2 @endif">
                            <h6>
                                {{ ucfirst($permissionName) }} <br>
                                <span>{{ $permission->name }}</span>
                            </h6>
                            <div>
                                <button class="btn btn-xs btn-secondary btn-active-dark ms-2" data-bs-toggle="modal" data-bs-target="#permissionModal" data-action="edit" data-permission-id="{{ $permission->id }}">
                                    <i class="fa fa-pencil fs-9 text-primary"></i>
                                </button>
                                <button class="btn btn-xs btn-secondary btn-active-dark" onclick="confirmDelete('{{ route('permissions.destroy', $permission->id) }}')">
                                    <i class="fa fa-trash text-danger"></i>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Permission Modal -->
<div class="modal fade" id="permissionModal" tabindex="-1" aria-labelledby="permissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="permissionModalLabel">Permission Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="permissionForm" action="{{ route('permissions.store') }}" method="post">
                    @csrf
                    <div class="mb-3">
                        <label for="permissionName" class="form-label">Permission Name</label>
                        <input type="text" class="form-control form-control-sm" id="permissionName" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="group_name" class="form-label">Resource Name</label>
                        <input type="text" class="form-control form-control-sm" id="group_name" name="group_name" required placeholder="eg: Users">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Save Permission</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection


@section('scripts')
<script>
    // $('#permissionModal').on('hidden.bs.modal', function () {
    //     // Your code to execute on modal hide goes here
    //     $('#permissionForm')[0].reset();
    // });
    // Fetch permission details form via AJAX and show in modal
    $('#permissionModal').on('show.bs.modal', function (event) {
        $('#permissionForm')[0].reset();
        
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var permissionId = button.data('permission-id');
        var form = $('#permissionForm');
        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');

        // Set modal title and form action based on action
        if (action === 'create') {
            modalTitle.text('Create Permission');
            modalAction = '{{ route('permissions.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Permission');
            modalAction = '{{ route('permissions.update', ':permissionId') }}'.replace(':permissionId', permissionId);
        }

        form.attr('action', modalAction);

        // Fetch permission data if editing
        if (action === 'edit') {
            $.ajax({
                url: '{{ route('permissions.show', ':permissionId') }}'.replace(':permissionId', permissionId),
                type: 'GET',
                success: function (data) {
                    if (data.permission) {
                        $('#permissionName').val(data.permission.name);
                        $('#group_name').val(data.permission.group_name);
                    } else {
                        console.error('Permission data not available.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }
    });

    // Submit the permission form via AJAX
    $('#permissionForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (data) {
                // Close the modal
                $('#permissionModal').modal('hide');

                // Reload the page or update the permissions table
                window.location.reload();
            },
            error: function (xhr, status, error) {
                // Handle errors if needed
                console.error(xhr.responseText);
            }
        });
    });

    // Function to confirm permission deletion with SweetAlert
    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this permission?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with the delete action
                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        // Reload the page or update the permissions table
                        window.location.reload();
                    },
                    error: function (xhr, status, error) {
                        // Handle errors if needed
                        console.error(xhr.responseText);
                    }
                });
            }
        });
    }
</script>

@endsection
