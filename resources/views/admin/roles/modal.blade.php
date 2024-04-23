<!-- Role Modal -->
<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="roleModalLabel">Role Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="roleContent"></div>
            </div>
        </div>
    </div>
</div>

@section('scripts')

<script>
    // Fetch role details form via AJAX and show in modal
    $('#roleModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var action = button.data('action');
        var roleId = button.data('role-id');

        $.ajax({
            url: action === 'create' ? '{{ route('roles.create') }}' : '{{ route('roles.edit', ':roleId') }}'.replace(':roleId', roleId),
            type: 'GET',
            success: function (data) {
                $('#roleContent').html(data);
            }
        });
    });

    // Submit the role form via AJAX
    $('#roleContent').on('submit', '#roleForm', function (e) {
        e.preventDefault();

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: $(this).serialize(),
            success: function (data) {
                // Close the modal
                $('#roleModal').modal('hide');

                // Show success message
                alert(data.message);

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