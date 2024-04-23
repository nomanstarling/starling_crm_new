<style>
    /* .fc-header-toolbar, .fc-toolbar {
        margin:0px !important;
    } */
    .justify-content-between{
        justify-content: space-between;
    }
    .d-none{
        display:none;
    }
    .event-details h6, .event-details p, .event-details .badge{
        margin: 0px;
        margin-left:15px;
    }
    .event-details h6{
        font-size: 14px;
        font-weight: bold;
        color: black;
    }
    .modal-dialog-centered{
        display: -ms-flexbox;
        display: flex;
        -ms-flex-align: center;
        align-items: center;
        min-height: calc(100% - (.5rem * 2));
    }
    .p-0{
        padding:0px;
    }
    .modal-header{
        display: -ms-flexbox;
        display: flex;
        -ms-flex-align: start;
        align-items: flex-start;
        -ms-flex-pack: justify;
        justify-content: space-between;
        padding: 1rem;
        border-bottom: 1px solid #e9ecef;
        border-top-left-radius: .3rem;
        border-top-right-radius: .3rem;
    }
    .w-100{
        width:100%;
    }
    .align-center {
        display: flex;
        align-items: center;
    }
    .modal .modal-dialog .modal-content .modal-header{
        padding:10px !important;
    }
    .modal .modal-body{
        padding:20px !important;
    }
    .event-success{
        color:green !important;
    }
    .event-danger{
        color:red !important;
    }
</style>

<div class="card mt-5">
    <div class="card-header py-4">
        <h2 class="card-title fw-bold">
            Calendar
        </h2>

        <!-- <div class="card-toolbar">
            <button class="btn btn-flex btn-primary btn-sm" data-kt-calendar="add">
                <i class="ki-duotone ki-plus fs-2"></i> 
                Add Event
            </button>
        </div> -->
    </div>
    <div class="card-body">
        <div id="kt_calendar_app"></div>
    </div>
</div>

<!-- <div role="dialog" aria-modal="true" class="fade modal show" tabindex="-1" style="display: block;">
    <div id="event-modal" class="modal-dialog modal-dialog-centered"> -->
<div id="eventModal" class="modal bs-example-modal-md" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered" style="width:30%;">
        <div class="modal-content p-0 w-100">
            <h4 class="modal-header align-center" style="background: #e4f3f6;">
                <span><span class="eventTitle text-capitalize"></span> Details</span>
                
                <div>
                    <!-- <button type="button" class="btn btn-success btn-sm markComplete"> Mark as Completed </button>
                    <a class="btn btn-sm btn-primary d-inline" id="edit-event-btn" href="/steex/react/default/apps-calendar"> Edit </a> -->
                    <button type="button" class="btn btn-sm btn-black bg-dark text-white" data-bs-dismiss="modal" aria-hidden="true">Close</button>
                </div>
            </h4>
            <div class="modal-body">
                <form name="event-form" id="form-event" class="needs-validation view-event">
                    <div class="event-details">

                        <div class="d-flex mb-2">
                            <div class="flex-grow-1 d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fa fa-signal text-success fs-lg eventIcon"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <span class="badge badge-success eventBadge"></span>
                                </div>
                            </div>
                        </div>

                        @if(auth()->user()->getRoleNames()->first() == 'Super Admin' || auth()->user()->is_teamleader == true)
                            <div class="d-flex mb-2 eventAgentDiv">
                                <div class="flex-grow-1 d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="fa fa-user text-muted fs-lg"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="d-block fw-semibold mb-0">Agent: <span class="fw-bold eventAgent"> </span></h6>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <div class="d-flex mb-2">
                            <div class="flex-grow-1 d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fa fa-refresh text-muted fs-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="d-block fw-semibold mb-0">Lead Ref# <span><a href="" target="_blank" class="leadRefNo fw-bold"></a></span></h6>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex mb-2">
                            <div class="flex-grow-1 d-flex align-items-center">
                                <div class="flex-shrink-0 me-3">
                                    <i class="fa fa-calendar text-muted fs-lg"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="d-block fw-semibold mb-0 eventDate"></h6>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center mb-2">
                            <div class="flex-shrink-0 me-3">
                                <i class="fa fa-clock text-muted fs-lg"></i>
                            </div>
                            <div class="flex-grow-1">
                                <h6 class="d-block fw-semibold mb-0 eventTime"></h6>
                            </div>
                        </div>
                        <div class="d-flex mb-3">
                            <div class="flex-shrink-0 me-3"><i class="fa fa-message text-muted fs-lg"></i></div>
                            <div class="flex-grow-1">
                                <p class="d-block text-muted mb-0 eventContent"></p>
                            </div>
                        </div>
                    </div>
                    <div class="event-form row eventUpdateForm d-none" style="border-top:1px solid #E9ECEE; margin-top:10px; padding-top:10px;">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="hidden" name="eventType" class="eventType" value="">
                                <input type="hidden" name="eventID" class="eventID" value="">
                                <input type="hidden" name="eventStatus" class="eventStatus" value="">
                                <!-- <input class="form-control flatpickr-input w-100" data-enable-time="true" id="event-start-date" name="defaultDate" placeholder="Select Date" value="" type="datetime-local"> -->
                                <input class="form-control w-100 eventDateInput" data-enable-time="true" data-date-format="Y-m-d H:i" name="eventDate" placeholder="Select Date" type="datetime-local">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label form-label">Description</label>
                                <textarea name="description" placeholder="Enter a description" rows="3" id="event-description" class="form-control form-control description"></textarea>
                            </div>
                        </div>
                        <div class="hstack gap-2 d-flex justify-content-end" style="margin-top:10px;">
                            <button type="submit" class="btn btn-primary updateEventBtn"> Update Event</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@section('scripts_component')
<script>

    $('#edit-event-btn').click(function(e){
        e.preventDefault();
        $('.event-form').toggleClass('d-none');
    });

    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('kt_calendar_app');

        function calendarAjax() {
            // Fetch calendar data using AJAX
            $.ajax({
                url: '{{ route('getCalendarData') }}',
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    //console.log(data);
                    initializeCalendar(data);
                },
                error: function(error) {
                    console.error('Error fetching calendar data:', error);
                }
            });
        }

        function initializeCalendar(data) {
            const dateOptions = { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' };
            const timeOptions = { hour: 'numeric', minute: '2-digit', hour12: true };

            var events = [];

            // console.log(data);

            data.calendar_data.forEach(function(note) {
                // Extracting lead details
                var lead = note.lead;
                var agentName = (lead && lead.lead_agent) ? lead.lead_agent.name : 'Unknown';
                var refno = (lead) ? lead.refno : '';

                events.push({
                    //title: 'Agent: ' + agentName + '. Details: ' + note.note,
                    title: note.note,
                    eventContent: note.note,
                    start: note.event_date,
                    end: note.event_date,
                    refno: refno,
                    eventID: note.id,
                    status: note.status,
                    eventType: note.type,
                    className: (note.status == false) ? 'text-primary' : 'text-success',
                    agentName: agentName
                });
            });

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                //themeSystem: 'bootstrap5',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
                },
                views: {
                    dayGridMonth: { buttonText: 'month' },
                    timeGridWeek: { buttonText: 'week' },
                    timeGridDay: { buttonText: 'day' }
                },
                aspectRatio: 3,
                nowIndicator: true,
                eventTimeFormat: {
                    hour: 'numeric',
                    minute: '2-digit',
                    meridiem: 'short'
                },
                navLinks: true,
                events: events,

                eventClick: function(info) {
                    var eventDetails = "Event: " + info.event.title;
                    var eventStatus = info.event.extendedProps.status;
                    if (info.event.extendedProps.refno) {
                        eventDetails += "\nRefNo#: " + info.event.extendedProps.refno;
                    }
                    $('#eventModal .leadRefNo').text(info.event.extendedProps.refno);
                    $('#eventModal .eventDate').text(info.event.start.toLocaleDateString(undefined, dateOptions));
                    $('#eventModal .eventTime').text(info.event.start.toLocaleTimeString(undefined, timeOptions));
                    $('#eventModal .eventContent').text(info.event.extendedProps.eventContent);
                    $('#eventModal .description').text(info.event.extendedProps.eventContent);

                    $('#eventModal .eventAgent').text(info.event.extendedProps.agentName);

                    if (eventStatus == true) {
                        $('#eventModal .markComplete').text("Mark as Completed");
                        $('#eventModal .markComplete').removeClass("btn-warning");
                        $('#eventModal .markComplete').addClass("btn-success");

                        $('#eventModal .eventBadge').text("Activity Pending");
                        $('#eventModal .eventBadge').removeClass("btn-success");
                        $('#eventModal .eventBadge').addClass("btn-primary");

                        $('#eventModal .eventIcon').removeClass("text-success");
                        $('#eventModal .eventIcon').addClass("text-primary");

                    } else {
                        $('#eventModal .markComplete').text("Undo Completed");

                        $('#eventModal .markComplete').addClass("btn-warning");
                        $('#eventModal .markComplete').removeClass("btn-success");

                        $('#eventModal .eventBadge').text("Activity Completed");
                        $('#eventModal .eventBadge').removeClass("btn-primary");
                        $('#eventModal .eventBadge').addClass("btn-success");

                        $('#eventModal .eventIcon').removeClass("text-primary");
                        $('#eventModal .eventIcon').addClass("text-success");
                    }

                    $('#eventModal .eventTitle').text(info.event.extendedProps.eventType);

                    $('#eventModal .eventType').val(info.event.extendedProps.eventType);
                    $('#eventModal .eventStatus').val(info.event.extendedProps.status);
                    $('#eventModal .eventID').val(info.event.extendedProps.eventID);
                    $('#eventModal .eventDateInput').val(info.event.start.toISOString().slice(0, 16));

                    var formattedTime = ('0' + info.event.start.getHours()).slice(-2) + ':' + ('0' + info.event.start.getMinutes()).slice(-2);
                    $('#eventModal .eventDateInput').val(info.event.start.toISOString().slice(0, 10) + 'T' + formattedTime);

                    $('#eventModal .leadRefNo').attr('href', '{{route('leads.index')}}?refno=' + info.event.extendedProps.refno);
                    $('#eventModal').modal('show');
                }
            });

            calendar.render();
        }

        calendarAjax();
    });

</script>
@endsection