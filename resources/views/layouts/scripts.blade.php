<script>
    function initializeDateRange(className, firstDate, type = null) {
        if(type == 'single'){

            $("." + className).daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                minYear: 1901,
                maxYear: parseInt(moment().format("YYYY"),12),
                format: 'YYYY-MM-DD',
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD',
                }
            });

            $('.'+className).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('.' + className).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        else if(type == 'singleTime'){
            $("." + className).daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                timePicker: true,
                minYear: 1901,
                maxYear: parseInt(moment().format("YYYY"),12),
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD hh:mm A',
                }
            });

            $('.'+className).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD hh:mm A'));
            });
            $('.' + className).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        else{
            $("." + className).daterangepicker({
                startDate: moment.utc(firstDate, 'YYYY-MM-DD'),
                endDate: moment.utc(),
                ranges: {
                    "Today": [moment().startOf('day'), moment().endOf('day')],
                    "Yesterday": [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    "Last 7 Days": [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    "Last 30 Days": [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                    "This Month": [moment().startOf('month'), moment().endOf('month')],
                    "Last Month": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
                },
                format: 'YYYY-MM-DD'
            });
        }
        
    }

    function initializeDateRangeTwo(className, firstDate = null, type = null) {
        if(type == 'single'){

            $("." + className).daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                minYear: 1901,
                maxYear: parseInt(moment().format("YYYY"),12),
                format: 'YYYY-MM-DD',
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD',
                }
            });

            $('.'+className).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD'));
            });
            $('.' + className).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        else if(type == 'singleTime'){
            $("." + className).daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                autoUpdateInput: false,
                timePicker: true,
                minYear: 1901,
                maxYear: parseInt(moment().format("YYYY"),12),
                locale: {
                    cancelLabel: 'Clear',
                    format: 'YYYY-MM-DD hh:mm A',
                }
            });

            $('.'+className).on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('YYYY-MM-DD hh:mm A'));
            });
            $('.' + className).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        else{
            $("." + className).daterangepicker({
                //startDate: moment.utc(firstDate, 'YYYY-MM-DD'),
                //endDate: moment.utc(),
                autoUpdateInput: false,
                ranges: {
                    "Today": [moment().startOf('day'), moment().endOf('day')],
                    "Yesterday": [moment().subtract(1, 'days').startOf('day'), moment().subtract(1, 'days').endOf('day')],
                    "Last 7 Days": [moment().subtract(6, 'days').startOf('day'), moment().endOf('day')],
                    "Last 30 Days": [moment().subtract(29, 'days').startOf('day'), moment().endOf('day')],
                    "This Month": [moment().startOf('month'), moment().endOf('month')],
                    "Last Month": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
                    "Reset": [null, null] // Add a custom option for canceling the selection
                },
                format: 'YYYY-MM-DD',
            });

            // $('.'+className).on('apply.daterangepicker', function(ev, picker) {
            //     $(this).val(picker.startDate.format('YYYY-MM-DD hh:mm A'));
            // });
            $('.' + className).on('apply.daterangepicker', function(ev, picker) {
                var startDate = picker.startDate.format('YYYY-MM-DD');
                var endDate = picker.endDate.format('YYYY-MM-DD');
                $(this).val(startDate + ' - ' + endDate);
            });
            $('.' + className).on('cancel.daterangepicker', function (ev, picker) {
                $(this).val('');
            });
        }
        
    }
</script>