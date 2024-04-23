@extends('layouts.app')

@section('content')
<style>
    .ribbon.ribbon-start .ribbon-label{
        top:20px !important;
    }
</style>
<div>
    <p id="refreshTimer" class="fs-9 text-gray-700 m-0"> Refreshing in: <span id="timer-value">0:30</span></p>
</div>
<div class="row teamsContainer mt-2" id="teamsContainer">

</div>


@endsection

@section('scripts')

<script>
    // Function to load teams and initialize Salvattore
    function loadTeams() {
        // Assuming you have a route named 'getCalls'
        $.ajax({
            url: '{{ route('stats.getCalls') }}',
            type: 'GET',
            success: function (data) {
                var teams = data.teams;
                var $teamsContainer = $('#teamsContainer');
                $teamsContainer.html('');

                // Sort teams by total_calls in descending order
                teams.sort(function (a, b) {
                    return b.total_calls - a.total_calls;
                });

                $.each(teams, function (index, team) {
                    // Sort users by total_calls in descending order
                    team.users.sort(function (a, b) {
                        return b.total_calls - a.total_calls;
                    });

                    var teamCard = '<div class="col-md-3 mb-3 grid-item"><div class="card card-stretch pt-5 card-flush shadow ribbon ribbon-start ribbon-clip"><div class="ribbon-label fw-bold text-shadow">' + team.name + ' <span class="badge badge-secondary mx-3">' + team.users.length + '</span> <span class="ribbon-inner bg-primary"></span></div>' +
                        '<div class="card-body px-3 overflow-hidden">' +
                        '<div class="w-100">' +
                        '<table class="table table-responsive">' +
                        '<thead>' +
                        '<tr class="bg-dark text-white">' +
                        '<th class="rounded-start px-2 py-1">Agent</th>' +
                        '<th class="py-1">Answered</th>' +
                        '<th class="py-1">Total</th>' +
                        '<th class="rounded-end px-2 py-1">Target</th>' +
                        '</tr>' +
                        '</thead>' +
                    '<tbody>';

                    $.each(team.users, function (userIndex, user) {

                        var fullName = user.name;
                        var nameParts = fullName.split(' ');
                        var firstName = nameParts[0];
                        var lastName = nameParts.length > 1 ? nameParts[nameParts.length - 1] : ''; // Get the last part as the last name
                        
                        var initial = lastName.length > 0 ? lastName.charAt(0) + '.' : ''; // Get the first letter of the last name followed by a period

                        
                        var username = user.name;
                        username = username.replace(/\s/g, '');
                        var storedTotalCalls = localStorage.getItem('user_' + username + '_total_calls');
                        var isNewUser = storedTotalCalls === null || user.total_calls !== parseInt(storedTotalCalls);

                        //console.log('user_' + username + '_total_calls | new calls: '+ user.total_calls + ' | old calls: '+storedTotalCalls);
                        var $userRow = $('<tr style="' + (isNewUser ? 'display: none;' : '') + '">' +
                            '<td>' +
                            '<div class="d-flex align-items-center">' +
                            '<div class="symbol symbol-20px symbol-md-20px me-3">' +
                            '<span class="symbol-label">' +
                            '<img src="' + user.profile_image + '" class="img-fluid rounded shadow-sm">' +
                            '</span>' +
                            '</div>' +
                            '<div class="flex-grow-1">' +
                            '<a href="#" class="text-gray-800 fw-bold text-hover-primary fs-8">' + firstName + ' ' + initial + '</a>' +
                            '</div>' +
                            '</div>' +
                            '</td>' +
                            '<td class="text-gray-800 fw-bold text-hover-primary fs-8">' + user.answered_calls + '</td>' +
                            '<td class="text-gray-800 fw-bold text-hover-primary fs-8">' + user.total_calls + '</td>' +
                            '<td class="text-gray-800 fw-bold text-hover-primary fs-8">' + user.calls_goal + '</td>' +
                            '</tr>');

                        teamCard += $userRow.prop('outerHTML');
                        //teamCard += '<tr><td colspan="4" class="p-0"><div class="separator separator-dotted my-0"></div></td></tr>';

                        // Add separator after each user row except the last one
                        if (userIndex < team.users.length - 1) {
                            teamCard += '<tr><td colspan="4" class="p-0"><div class="separator separator-dotted my-0"></div></td></tr>';
                        }
                        // Update stored total calls value
                        localStorage.setItem('user_' + username + '_total_calls', user.total_calls);
                    });
                    
                    // Add footer with totals
                    var answeredTotal = team.users.reduce(function (acc, user) {
                        return acc + user.answered_calls;
                    }, 0);
                    var totalTotal = team.users.reduce(function (acc, user) {
                        return acc + user.total_calls;
                    }, 0);
                    var targetTotal = team.users.reduce(function (acc, user) {
                        return acc + user.calls_goal;
                    }, 0);

                    teamCard += '</tbody>' +
                        '<tfoot>' +
                        '<tr class="bg-grey">' +
                        '<td colspan="1" class="rounded-start py-1"></td>' +
                        '<td class="fw-bold py-1">' + answeredTotal + '</td>' +
                        '<td class="fw-bold py-1">' + totalTotal + '</td>' +
                        '<td class="fw-bold rounded-end py-1">' + targetTotal + '</td>' +
                        '</tr>' +
                        '</tfoot>' +
                        '</table>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';

                    teamCard += '</tbody></table></div></div></div></div></div>';
                    $teamsContainer.append(teamCard);

                     // Add swap effect for changed user order
                    var $currentTeam = $teamsContainer.find('.grid-item:last');
                    $currentTeam.find('tbody tr:hidden').each(function (i) {
                        $(this).delay(i * 100).fadeIn(300);
                    });
                    
                });

                // Add animation to user order changes
                $('.grid-item tbody tr:hidden').each(function (index) {
                    $(this).delay(900).fadeIn(500);
                });
            },
            error: function (xhr, status, error) {
                console.error('Error fetching teams:', error);
            }
        });
    }

    // Initial load
    loadTeams();

    var timer_val = 10;
    var secondsLeft = 1 * timer_val; // 1 minutes in seconds
    var timerInterval = setInterval(function () {
        // Update timer value
        var minutes = Math.floor(secondsLeft / timer_val);
        var seconds = secondsLeft % timer_val;
        $('#timer-value').text(minutes + ':' + (seconds < 10 ? '0' : '') + seconds);

        // Decrease seconds left
        secondsLeft--;

        // If the timer reaches 0, update data and reset the timer
        if (secondsLeft < 0) {
            loadTeams();
            secondsLeft = 1 * timer_val; // Reset to 1 minutes
        }
    }, 1000);
    
</script>

@endsection
