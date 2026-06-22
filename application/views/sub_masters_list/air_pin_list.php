<?php include('header.php') ?>

<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    #calender_container {
        opacity: 0;
    }

    .flatpickr-day.today {
        border-color: #959ea9;
        background: red;
        color: white;
    }

    span[aria-label='February 19, 2025'] {
        background: yellow;
    }

    span[aria-label='February 20, 2025'] {
        background: green;
    }

    .schedule-popup {
        width: 17%;
        height: auto;

    }

    input[type="time"] {
        padding: 5px 8px;
    }

    .schedule-popup label {

        width: 120px;
        font-size: 16px;
    }

    .fc-daygrid-day-frame {
        height: 115px;
    }

    .fc-daygrid-day-frame {
        min-height: 80px;
    }

    .swal-form-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: center;
        padding: 10px 0;
    }

    .swal-custom-input {
        width: 80%;
    }

    .swal-wide {
        max-width: 500px;
    }
</style>
<div class="right_col" role="main">

    <div class="page-title">
        <div class="title_left">
            <h3>Production Schedule </h3>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="row">
        <div class="x_panel">
            <div class="x_content">
                <div class="container">
                    <div class="row">


                        <div class="col-lg-4 col-md-4">
                            <label for="plant">Plant<b class="require">*</b></label>
                            <select class="form-control" name="plant_id" id="plant_id">
                                <option value="">Please select plant</option>
                                <?php if (!empty($plant)) {
                                    foreach ($plant as $plant_result) { ?>
                                        <option value="<?= $plant_result->id ?>" <?php if (!empty($single) && $single->plant_id == $plant_result->id) { ?>selected<?php } ?>>
                                            <?= $plant_result->plant_name ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                            <label id="plant_id-error" class="error" for="plant_id" style="display:none"></label>
                        </div>

                        <div class="col-lg-4 col-md-4">
                            <label for="machine">Machine<b class="require">*</b></label>
                            <select class="form-control" name="machine_id" id="machine_id">
                                <option value="">Please select machine</option>
                                <?php if (!empty($machine)) {
                                    foreach ($machine as $machine_result) { ?>
                                        <option value="<?= $machine_result->id ?>" <?php if (!empty($single) && $single->machine_id == $machine_result->id) { ?>selected<?php } ?>>
                                            <?= $machine_result->machine_name ?>
                                        </option>
                                    <?php }
                                } ?>
                            </select>
                        </div>

                        <div class="col-lg-4 col-md-4">
                            <div class="form-group">
                                <label for="date_picker">Select Date<b class="require">*</b></label>
                                <input autocomplete="off" type="text" class="form-control" placeholder="Select Date"
                                    name="filter_date" id="filter_date">
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="x_panel" id="calender_container">
            <div class="x_content">
                <div class="container">
                    <div id="calendar"></div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <a class="btn btn-primary excute_btn mt-3">Execute</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        $('.right_col').addClass('active_right');
        $('.production_schedule').addClass('active_cc');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>

    $(function () {
        var calendar;
        var selectedScheduleData = null;
        var selectedSlots = [];
        var $dateInput = $('#filter_date');
        var $plantSelect = $('#plant_id');
        var $machineSelect = $('#machine_id');
        var $calendarWrap = $('#calender_container');
        var calendarEl = document.getElementById('calendar');

        var fpInstance;

        flatpickr("#filter_date", {
            dateFormat: "d-m-Y",
            onChange: onFilterChange,
            onChange: function (selectedDates, dateStr, instance) {
                fpInstance = instance;
                fetchAndApplyScheduleColor();
            },
            onMonthChange: function (selectedDates, dateStr, instance) {
                fpInstance = instance;
                fetchAndApplyScheduleColor();
            }
        });
        $plantSelect.add($machineSelect).on('change', function () {
            fetchAndApplyScheduleColor();
        });
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridDay',
            eventDisplay: 'auto',
            headerToolbar: {
                left: 'prev,next today viewMonthHistoryBtn',
                center: 'title',
                right: 'switchToDayViewBtn'
            },
            customButtons: {
                viewMonthHistoryBtn: {
                    text: 'View Month History',
                    click: onViewMonthHistoryClick
                },
                switchToDayViewBtn: {
                    text: 'Day View',
                    click: function () {
                        calendar.changeView('timeGridDay');
                        onFilterChange();
                    }
                }
            },
            buttonText: {
                today: 'Today'
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '33:00:00',
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: '08:00',
                endTime: '33:00'
            },
            dateClick: onDateClick,
            eventDidMount: onEventRender
        });
        calendar.render();



        $plantSelect.on('change', onFilterChange);
        $machineSelect.on('change', onFilterChange);
        $dateInput.on('change', onFilterChange);

        function onFilterChange() {
            var dateStr = $dateInput.val();
            var plantId = $plantSelect.val();
            var machineId = $machineSelect.val();

            if (!dateStr || !plantId || !machineId) {
                $calendarWrap.css('opacity', 0.5);
                $('.fc-button').prop('disabled', true);
                return;
            }

            var parts = dateStr.split('-').map(Number);
            var dateObj = new Date(parts[2], parts[1] - 1, parts[0]);

            $calendarWrap.css('opacity', 1);
            $('.fc-button').prop('disabled', false);
            calendar.gotoDate(dateObj);
            fetchScheduleData(dateObj, plantId, machineId);
        }

        function fetchScheduleData(dateObj, plantId, machineId) {
            var isoDate = formatDateToYMD(dateObj);

            calendar.getEvents().forEach(function (evt) { evt.remove(); });

            $.ajax({
                url: '<?= base_url() ?>admin/Ajax_controller/get_schedule_data',
                method: 'POST',
                dataType: 'json',
                data: {
                    start_date: isoDate,
                    plant_id: plantId,
                    machine_id: machineId
                },
                success: function (data) {
                    data.forEach(function (evt) {
                        calendar.addEvent(evt);
                    });
                },
                error: function (xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                }
            });
        }
        function fetchAndApplyScheduleColor() {
            const plantId = $plantSelect.val();
            const machineId = $machineSelect.val();

            if (!plantId || !machineId) {
                return;
            }

            const fp = $dateInput[0]._flatpickr;
            const selectedMonth = fp.currentMonth + 1;
            const selectedYear = fp.currentYear;

            const month = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;

            $.ajax({
                url: '<?= base_url("admin/Ajax_controller/get_month_schedule_data") ?>',
                method: 'POST',
                dataType: 'json',
                data: {
                    month: month,
                    plant_id: plantId,
                    machine_id: machineId
                },
                success: function (data) {
                    fullScheduleData = data;
                    applyColorsOnFlatpickr();
                },
                error: function (xhr, status, error) {
                    console.error('Error fetching schedule:', error);
                }
            });
        }

        function applyColorsOnFlatpickr() {
            const fp = $dateInput[0]._flatpickr;
            const coveredDates = {};

            fullScheduleData.forEach(function (serverEvt) {
                const startTime = new Date(serverEvt.start);
                const endTime = new Date(serverEvt.end);
                const normalizedStartDate = normalizeDate(startTime);
                if (!coveredDates[normalizedStartDate]) {
                    const isEightToEight = (
                        startTime.getHours() === 8 && startTime.getMinutes() === 0 &&
                        endTime.getHours() === 8 && endTime.getMinutes() === 0
                    );
                    coveredDates[normalizedStartDate] = isEightToEight ? 'green' : 'yellow';
                }
            });
            fp.redraw();
            setTimeout(() => {
                const currentDate = new Date();
                const currentDateNormalized = normalizeDate(currentDate);
                fp.calendarContainer.querySelectorAll('.flatpickr-day').forEach(dayElem => {
                    const normalizedDateStr = normalizeDate(dayElem.dateObj);
                    const isPastDate = new Date(normalizedDateStr) < currentDate;
                    if (coveredDates[normalizedDateStr] === 'green') {
                        dayElem.style.backgroundColor = '#4CAF50';
                        dayElem.style.color = '#FFFFFF';
                    } else if (coveredDates[normalizedDateStr] === 'yellow') {
                        dayElem.style.backgroundColor = '#FFC107';
                        dayElem.style.color = '#212121';
                    } else if (isPastDate) {
                        dayElem.style.backgroundColor = '#FF6B6B';
                        dayElem.style.color = '#FFFFFF';
                    }
                    //  else {
                    //     dayElem.style.backgroundColor = '#FF6B6B';
                    //     dayElem.style.color = '#FFFFFF';
                    // }
                });
            }, 100);
        }

        function normalizeDate(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            return `${year}-${month}-${day}`;
        }
        function formatDateToYMD(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            console.log(`Formatted Date: ${year}-${month}-${day}`);
            return `${year}-${month}-${day}`;
        }
        function onViewMonthHistoryClick() {
            Swal.fire({
                title: 'Select Month, Plant & Machine',
                html: `
                    <div style="display: flex; flex-direction: column; gap: 15px; align-items: center; padding: 10px 0;">
                        <input type="month" id="swal-input-month" class="swal2-input" style="width: 80%;" placeholder="Select Month" />
                        <select id="swal-input-plant" class="swal2-input" style="width: 80%;">
                            <option value="">Select Plant</option>
                            <?php foreach ($plant as $p): ?>
                                <option value="<?= $p->id ?>"><?= $p->plant_name ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="swal-input-machine" class="swal2-input" style="width: 80%;">
                            <option value="">Select Machine</option>
                        </select>
                    </div>
                `,
                focusConfirm: false,
                customClass: {
                    popup: 'swal-wide'
                },
                width: 400,
                didOpen: () => {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = (now.getMonth() + 1).toString().padStart(2, '0');
                    $('#swal-input-month').val(`${year}-${month}`);

                    $('#swal-input-plant').on('change', function () {
                        const plantId = $(this).val();
                        const $machine = $('#swal-input-machine');
                        $machine.html('<option>Loading…</option>');
                        if (!plantId) {
                            return $machine.html('<option value="">Select Machine</option>');
                        }
                        $.ajax({
                            url: '<?= base_url("admin/Ajax_controller/get_all_machines") ?>',
                            type: 'POST',
                            dataType: 'json',
                            data: { plant_id: plantId },
                            success: function (data) {
                                let opts = '<option value="">Select Machine</option>';
                                $.each(data, (i, m) => {
                                    opts += `<option value="${m.id}">${m.machine_name}</option>`;
                                });
                                $machine.html(opts);
                            },
                            error: function () {
                                $machine.html('<option value="">Error loading</option>');
                            }
                        });
                    });
                },
                preConfirm: () => {
                    const month = $('#swal-input-month').val();
                    const plantId = $('#swal-input-plant').val();
                    const machineId = $('#swal-input-machine').val();
                    if (!month) { Swal.showValidationMessage('Please select a month'); return false; }
                    if (!plantId) { Swal.showValidationMessage('Please select a plant'); return false; }
                    if (!machineId) { Swal.showValidationMessage('Please select a machine'); return false; }
                    return { month, plant_id: plantId, machine_id: machineId };
                },
                confirmButtonText: 'Show Schedule',
                showCancelButton: true,
                cancelButtonText: 'Cancel'
            }).then(result => {
                if (!result.isConfirmed) return;
                const { month, plant_id, machine_id } = result.value;
                $.ajax({
                    url: '<?= base_url("admin/Ajax_controller/get_month_schedule_data") ?>',
                    method: 'POST',
                    dataType: 'json',
                    data: { month, plant_id, machine_id },
                    success(data) {
                        calendar.removeAllEvents();
                        const coveredDates = {};

                        data.forEach(function (serverEvt) {
                            const startTime = new Date(serverEvt.start);
                            const endTime = new Date(serverEvt.end);

                            const startStr = startTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                            const endStr = endTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });

                            const title = `Schedule: ${startStr} - ${endStr}`;
                            console.log('Event Title:', title);

                            calendar.addEvent({
                                id: serverEvt.id,
                                title: title,
                                start: startTime.toISOString().split('T')[0],
                                end: endTime.toISOString().split('T')[0],
                                allDay: true,
                                display: 'auto'
                            });

                            const startDateStr = startTime.toISOString().split('T')[0];

                            if (!coveredDates[startDateStr]) {
                                const isEightToEight = (
                                    startTime.getHours() === 8 && startTime.getMinutes() === 0 &&
                                    endTime.getHours() === 8 && endTime.getMinutes() === 0 &&
                                    startTime.getDate() !== endTime.getDate()
                                );

                                coveredDates[startDateStr] = isEightToEight ? 'green' : 'yellow';
                            }
                        });

                        const [year, monthNum] = month.split('-').map(Number);
                        const datesInMonth = getDatesInMonth(year, monthNum).map(d => d.toISOString().split('T')[0]);
                        calendar.getEvents().forEach(serverEvt => {
                            if (serverEvt.display === 'background') {
                                serverEvt.remove();
                            }
                        });
                        datesInMonth.forEach(dateStr => {
                            let color = 'red';
                            if (coveredDates[dateStr]) {
                                color = coveredDates[dateStr];
                            }

                            calendar.addEvent({
                                start: dateStr,
                                allDay: true,
                                display: 'background',
                                backgroundColor: color
                            });
                        });

                        calendar.changeView('dayGridMonth');
                        calendar.gotoDate(month + '-01');
                    },
                    error() {
                        Swal.fire('Error', 'Could not load schedule', 'error');
                    }
                });
            });
        }
        function getDatesInMonth(year, month) {
            const date = new Date(year, month - 1, 1);
            const dates = [];
            while (date.getMonth() === month - 1) {
                dates.push(new Date(date));
                date.setDate(date.getDate() + 1);
            }
            return dates;
        }


        function getDatesCoveredByEvent(start, end) {
            const dates = [];
            let current = new Date(start);
            current.setHours(0, 0, 0, 0);
            const endDate = new Date(end);
            endDate.setHours(0, 0, 0, 0);
            while (current <= endDate) {
                dates.push(current.toISOString().split('T')[0]);
                current.setDate(current.getDate() + 1);
            }
            return dates;
        }
        function onDateClick(info) {
            var today = new Date(); today.setHours(0, 0, 0, 0);
            if (calendar.view.type === 'dayGridMonth') {
                return;
            }
            if (!$plantSelect.val() || !$machineSelect.val()) {
                alert('Please select date, plant, and machine first.');
                return;
            }
            if (info.date < today) {
                alert('Cannot book past dates.');
                return;
            }
            var isoDate = info.dateStr.split('T')[0];
            var timeStr = info.dateStr.slice(11, 19);
            openSchedulePopup(isoDate, timeStr);
        }

        function onEventRender(info) {
            if (!info.event.classNames.includes('selected-slot')) return;

            var start = info.event.extendedProps.displayStart ||
                info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            var end = info.event.extendedProps.displayEnd ||
                info.event.end.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            var label = document.createElement('div');
            label.className = 'time-display';
            label.textContent = start + ' – ' + end;
            info.el.appendChild(label);
        }

        function openSchedulePopup(data, time) {
            if (calendar.view.type === 'dayGridMonth') {
                return;
            }
            const popup = document.createElement('div');
            popup.className = 'schedule-popup';
            popup.style.position = 'fixed';
            popup.style.top = '50%';
            popup.style.left = '50%';
            popup.style.transform = 'translate(-50%, -50%)';
            popup.style.backgroundColor = 'white';
            popup.style.padding = '20px';
            popup.style.borderRadius = '8px';
            popup.style.boxShadow = '0 4px 8px rgba(0,0,0,0.2)';
            popup.style.zIndex = '1000';
            popup.style.width = '90%';
            popup.style.maxWidth = '400px';
            popup.style.boxSizing = 'border-box';

            const closeBtn = document.createElement('button');
            closeBtn.textContent = '×';
            closeBtn.style.position = 'absolute';
            closeBtn.style.right = '10px';
            closeBtn.style.top = '10px';
            closeBtn.style.background = 'none';
            closeBtn.style.border = 'none';
            closeBtn.style.fontSize = '24px';
            closeBtn.style.cursor = 'pointer';

            const overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0,0,0,0.5)';
            overlay.style.zIndex = '999';

            const closePopup = () => {
                document.body.removeChild(overlay);
                document.body.removeChild(popup);
            };

            closeBtn.onclick = closePopup;
            overlay.onclick = closePopup;

            const dateInfo = document.createElement('div');
            dateInfo.textContent = `Date: ${data || 'Not selected'}`;
            dateInfo.style.fontSize = '18px';
            dateInfo.style.marginBottom = '20px';
            dateInfo.style.fontWeight = 'bold';

            const timeContainer = document.createElement('div');
            timeContainer.style.display = 'flex';
            timeContainer.style.flexDirection = 'column';
            timeContainer.style.gap = '20px';
            timeContainer.style.marginBottom = '20px';

            const fromTimeDiv = document.createElement('div');
            const fromLabel = document.createElement('label');
            fromLabel.textContent = 'From:';
            fromLabel.style.marginRight = '10px';
            const fromTime = document.createElement('input');
            fromTime.type = 'time';
            fromTime.value = time ? time.substring(0, 5) : '09:00';
            fromTimeDiv.appendChild(fromLabel);
            fromTimeDiv.appendChild(fromTime);

            const toTimeDiv = document.createElement('div');
            const toLabel = document.createElement('label');
            toLabel.textContent = 'To:';
            toLabel.style.marginRight = '10px';
            const toTime = document.createElement('input');
            toTime.type = 'time';
            toTime.value = '20:00';
            toTimeDiv.appendChild(toLabel);
            toTimeDiv.appendChild(toTime);

            timeContainer.appendChild(fromTimeDiv);
            timeContainer.appendChild(toTimeDiv);

            const submitBtn = document.createElement('button');
            submitBtn.textContent = 'Set Schedule';
            submitBtn.style.padding = '8px 16px';
            submitBtn.style.fontSize = '16px';
            submitBtn.style.backgroundColor = '#4CAF50';
            submitBtn.style.color = 'white';
            submitBtn.style.border = 'none';
            submitBtn.style.borderRadius = '4px';
            submitBtn.style.cursor = 'pointer';

            submitBtn.onclick = () => {


                function getEndDate(startDate, startTime, endTime) {
                    const [startHours, startMins] = startTime.split(':').map(Number);
                    const [endHours, endMins] = endTime.split(':').map(Number);

                    const startIsPM = startHours >= 12;
                    const endIsAM = endHours < 12;

                    if (startTime === "08:00" && endTime === "08:00") {
                        const date = new Date(startDate);
                        date.setDate(date.getDate() + 1);
                        return date.toISOString().split('T')[0];
                    }

                    if (endHours < startHours || (endHours === startHours && endMins < startMins)) {
                        const date = new Date(startDate);
                        date.setDate(date.getDate() + 1);
                        return date.toISOString().split('T')[0];
                    }

                    return startDate;
                }


                const dateValue = data.date || data;
                const fromTime24 = fromTime.value;
                const toTime24 = toTime.value;

                const endDateValue = getEndDate(dateValue, fromTime24, toTime24);

                const startDateTime = new Date(`${dateValue}T${fromTime24}:00`);
                const endDateTime = new Date(`${endDateValue}T${toTime24}:00`);

                if (startDateTime >= endDateTime) {
                    alert("End time must be after start time.");
                    return;
                }

                const hasOverlap = calendar.getEvents().some(event => {
                    return (
                        !event.classNames.includes('selected-slot') &&
                        startDateTime < event.end &&
                        endDateTime > event.start
                    );
                });
                const now = new Date();
                const today8AM = new Date(now.getFullYear(), now.getMonth(), now.getDate(), 8, 0, 0);
                const tomorrow8AM = new Date(today8AM);
                tomorrow8AM.setDate(today8AM.getDate() + 1);

                if (startDateTime < today8AM || endDateTime > tomorrow8AM) {
                    alert("You can only schedule between today 8 AM to tomorrow 8 AM.");
                    return;
                }
                if (hasOverlap) {
                    alert('This time slot overlaps with an existing slot!');
                    return;
                }
                calendar.getEvents().forEach(event => {
                    if (event.classNames.includes('selected-slot')) {
                        event.remove();
                    }
                });

                calendar.addEvent({
                    title: 'Selected Slot',
                    start: startDateTime,
                    end: endDateTime,
                    color: 'green',
                    display: 'background',
                    classNames: ['selected-slot'],
                    extendedProps: {
                        isSelected: true,
                        isOvernight: endDateValue !== dateValue
                    }
                });

                selectedScheduleData = {
                    'production_schedule_start_date': dateValue,
                    'production_schedule_end_date': endDateValue,
                    'production_schedule_start_time': fromTime24,
                    'production_schedule_end_time': toTime24,
                    'is_overnight': endDateValue !== dateValue,
                    'plant': $('#plant_id option:selected').text(),
                    'machine': $('#machine_id option:selected').text(),
                    'save_to_session': true
                };
                closePopup();
            };

            popup.appendChild(closeBtn);
            popup.appendChild(dateInfo);
            popup.appendChild(timeContainer);
            popup.appendChild(submitBtn);

            document.body.appendChild(overlay);
            document.body.appendChild(popup);
        }
        $plantSelect.add($machineSelect).on('change', function () {
            var $this = $(this);
            if ($this.val()) {
                $this.removeClass('is-invalid')
                    .siblings('.error').hide();
            } else {
                $this.addClass('is-invalid')
                    .siblings('.error').show();
            }
        });

        $('.excute_btn').on('click', function (e) {
            e.preventDefault();
            var isValid = true;

            $('#plant_id-error').hide();
            $('#plant_id, #machine_id').removeClass('is-invalid');

            if (!$('#plant_id').val()) {
                $('#plant_id-error').show().text('Please select a plant!');
                $('#plant_id').addClass('is-invalid');
                isValid = false;
            }
            if (!$('#machine_id').val()) {
                if (!$('#machine_id-error').length) {
                    $('#machine_id').after('<label class="error" id="machine_id-error">Please select a machine!</label>');
                }
                $('#machine_id').addClass('is-invalid');
                isValid = false;
            }

            if (!isValid) {
                $('html, body').animate({ scrollTop: 0 }, 500);
                return;
            }
            if (!selectedScheduleData) {
                alert('Please select a schedule before executing.');
                return;
            }

            var $btn = $(this).prop('disabled', true).text('Saving...');
            var ajaxData = $.extend({
                plant_id: $('#plant_id').val(),
                machine_id: $('#machine_id').val()
            }, selectedScheduleData);
            $.post('<?= base_url() ?>admin/Ajax_controller/save_schedule', ajaxData)
                .done(function () {
                    window.location.href = '<?= base_url() ?>production_schedule_form';
                })
                .fail(function () {
                    alert('Error saving schedule, please try again.');
                    $btn.prop('disabled', false).text('Execute');
                });
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#plant_id').change(function () {
            var plant_id = $(this).val();
            if (plant_id) {
                $.ajax({
                    url: '<?= base_url() ?>admin/Ajax_controller/get_all_machines',
                    type: 'POST',
                    data: {
                        plant_id: plant_id
                    },
                    dataType: 'json',
                    success: function (data) {
                        $('#machine_id').empty();
                        $('#machine_id').append('<option value="">Select machine</option>');
                        $.each(data, function (index, machine) {
                            $('#machine_id').append('<option value="' + machine.id + '">' + machine.machine_name + '</option>');
                        });
                    },
                    error: function () {
                        alert('Error retrieving machines. Please try again.');
                    }
                });
            } else {
                $('#machine_id').empty();
                $('#machine_id').append('<option value="">Select Machine</option>');
            }
        });
    });
</script>