<!-- Display messages -->
@foreach ($messages as $message)
    {!! $message !!}
@endforeach

<!-- <script>
    // Check if there is a message indicating a reload
    var reloadMessage = @json(end($messages));

    // if (reloadMessage && reloadMessage.includes('Reloading')) {
    //     //alert(reloadMessage); // You can use any other way to notify the user
    //     setTimeout(function () {
    //         location.reload();
    //     }, 5000);
    // }
</script> -->
