<form id="editRoleForm" action="{{ route('roles.update', $role->id) }}" method="post">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="editRoleName" class="form-label">Role Name</label>
        <input type="text" class="form-control" id="editRoleName" name="name" value="{{ $role->name }}" required>
    </div>

    <button type="submit" class="btn btn-primary">Update Role</button>
</form>

@section('scripts')

<script>
    // Submit the edit role form via AJAX
    $('#editRoleForm').submit(function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (data) {
                // Close the modal
                $('#editRoleModal').modal('hide');

                // Reload the page or update the roles table
                // window.location.reload();
            },
            error: function (xhr, status, error) {
                // Handle errors if needed
                console.error(xhr.responseText);
            }
        });
    });
</script>


@endsection