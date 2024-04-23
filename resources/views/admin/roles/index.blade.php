@extends('layouts.app')

@section('content')

<div class="card">
    <div class="card-header">
        <h2 class="card-title fw-bold">
            Roles
        </h2>

        <div class="card-toolbar">
            <button class="btn btn-flex btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#roleModal" data-action="create">
                <i class="ki-duotone ki-plus fs-2"></i>
                Add Role
            </button>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-row-dashed">
                <thead class="text-start bg-dark text-white fw-bold fs-7 text-uppercase gs-0">
                    <th class="rounded-start px-2">ID</th>
                    <th>Name</th>
                    <th>Permissions</th>
                    <th class="rounded-end px-2 text-end">Action</th>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ $role->id }}</td>
                            <td>{{ $role->name }}</td>
                            <td>{{ $role->permissions_count }}</td>
                            <td class="text-end">
                                <button class="btn btn-sm btn-icon btn-primary btn-active-dark" data-bs-toggle="modal" data-bs-target="#roleModal" data-action="edit" data-role-id="{{ $role->id }}"><i class="fa fa-pencil"></i></button>
                                <button class="btn btn-sm btn-icon btn-danger btn-active-dark" onclick="confirmDelete('{{ route('roles.destroy', $role->id) }}')"><i class="fa fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Role Modal -->
<div class="modal fade modalRight w-90" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-full-height">
        <div class="modal-content full-width">
            <div class="modal-header bg-white py-3">
                <h3 class="modal-title" id="roleModalLabel">Role Details</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="roleForm" action="{{ route('roles.store') }}" method="post">
                <div class="modal-body">
                    @csrf
                    <div class="mb-3 border-bottom pb-4">
                        <label for="roleName" class="form-label">Role Name</label>
                        <input type="text" class="form-control form-control-sm" id="roleName" name="name" required>
                    </div>
                    <!-- List of Permissions -->
                    <div class="mb-3 mt-5">
                        <div class="d-flex justify-content-between">
                            <span>Permissions</span>
                            <button class="btn btn-xs btn-primary select-all-global-btn" type="button">Select All Permissions</button>
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
                                            <button class="btn btn-xs btn-secondary select-all-btn" type="button" data-group="{{ $groupName }}">Select All</button>
                                        </div>
                                        <div class="card-body px-3 py-3">
                                            @foreach($groupPermissions as $permission)
                                                @php
                                                    // Remove the prefix before the first underscore
                                                    $permissionName = explode('_', $permission->name, 2)[1];
                                                @endphp
                                                <div class="form-check mb-2 form-check-sm">
                                                    <input type="checkbox" class="form-check-input" data-group="{{ $groupName }}" id="permission_{{ $permission->id }}" name="permissions[]" value="{{ $permission->id }}">
                                                    <label class="form-check-label" for="permission_{{ $permission->id }}">{{ ucfirst($permissionName) }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                </div>
                <div class="modal-footer bg-white py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Save Role</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('scripts')

<script>

    // Function to handle select/unselect all checkboxes in a group
    $('.select-all-btn').click(function() {
        var groupName = $(this).data('group');
        var checkboxes = $('input[type="checkbox"][data-group="' + groupName + '"]');
        var checked = $(this).text() === 'Select All'; // Check if currently "Select All" button is active

        checkboxes.prop('checked', checked);
        $(this).text(checked ? 'Unselect All' : 'Select All');
    });

    $('.select-all-global-btn').click(function() {
        var checkboxes = $('input[type="checkbox"]');
        var checked = $(this).text() === 'Select All Permissions'; // Check if currently "Select All" button is active

        checkboxes.prop('checked', checked);
        $(this).text(checked ? 'Unselect All Permissions' : 'Select All Permissions');
    });
    
    // $('#roleModal').on('hidden.bs.modal', function () {
    //     // Your code to execute on modal hide goes here
    //     $('#roleForm')[0].reset();

    //     // Uncheck all checkboxes
    //     $('input[type="checkbox"]').prop('checked', false);
    // });
    
    // Fetch role details form via AJAX and show in modal
    $('#roleModal').on('show.bs.modal', function (event) {

        $('#roleForm')[0].reset();

        // Uncheck all checkboxes
        $('input[type="checkbox"]').prop('checked', false);

        var button = $(event.relatedTarget);
        var action = button.data('action');
        var roleId = button.data('role-id');
        var form = $('#roleForm');
        var modalTitle = form.find('.modal-title');
        var modalAction = form.attr('action');

        // Set modal title and form action based on action
        if (action === 'create') {
            modalTitle.text('Create Role');
            modalAction = '{{ route('roles.store') }}';
        } else if (action === 'edit') {
            modalTitle.text('Edit Role');
            modalAction = '{{ route('roles.update', ':roleId') }}'.replace(':roleId', roleId);
        }

        form.attr('action', modalAction);

        // Fetch role data if editing
        if (action === 'edit') {
            
            $.ajax({
                url: '{{ route('roles.show', ':roleId') }}'.replace(':roleId', roleId),
                type: 'GET',
                success: function (data) {
                    if (data.role) {
                        $('#roleName').val(data.role.name);
                        
                        // Check checkboxes associated with the role's permissions
                        var rolePermissions = data.rolePermissions.map(permission => permission.id);

                        // Loop through each checkbox and set checked property
                        $('input[name="permissions[]"]').each(function() {
                            var permissionId = parseInt($(this).val());
                            var checked = rolePermissions.includes(permissionId);
                            $(this).prop('checked', checked);

                            // Use prop('checked', ...) to set the checked property
                            //$(this).prop('checked', rolePermissions.includes(permissionId));

                            // Update the text of the "Select All" button
                            var groupName = $(this).data('group');
                            var selectAllBtn = $('.select-all-btn[data-group="' + groupName + '"]');
                            selectAllBtn.text(checked ? 'Unselect All' : 'Select All');
                        });
                        // Update the text of the global "Select All" button
                        var globalSelectAllBtn = $('.select-all-global-btn');
                        globalSelectAllBtn.text(rolePermissions.length === $('input[type="checkbox"]').length ? 'Unselect All Permissions' : 'Select All Permissions');
                    } else {
                        console.error('Role data not available.');
                    }
                },

                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        }

    });

    // Submit the role form via AJAX
    $('#roleForm').submit(function (e) {
        e.preventDefault();
        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (data) {
                // Close the modal
                $('#roleModal').modal('hide');

                // Reload the page or update the roles table
                window.location.reload();
            },
            error: function (xhr, status, error) {
                // Handle errors if needed
                console.error(xhr.responseText);
            }
        });
    });

    // Function to confirm role deletion with SweetAlert
    function confirmDelete(deleteUrl) {
        Swal.fire({
            text: "Are you sure you want to delete this role?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            confirmButtonColor: "#dc3545",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                // Proceed with the delete action
                //window.location.href = deleteUrl;

                $.ajax({
                    url: deleteUrl,
                    type: 'DELETE',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function (data) {
                        // Reload the page or update the roles table
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
