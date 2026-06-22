<?php include('header.php') ?>

<style type="text/css">
    .error {
        color: red;
        float: left;
    }

    #calender_container {
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .flatpickr-day.today {
        border-color: #959ea9;
        background: red;
        color: white;
    }

    .fc-day-today {
        background-color: transparent !important;
    }

    span[aria-label='February 19, 2025'] {
        background: yellow;
    }

    /* a {
        color: #5A738E;
        margin-right: -45px;
        text-decoration: none;
    } */

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

    .fc-event[style*="background-color: green"] {
        color: #fff !important;
        font-weight: bold !important;
        text-shadow: 1px 1px 2px black !important;
    }

    .fc-timegrid-event .fc-event-time {
        font-size: var(--fc-small-font-size);
        margin-bottom: 1px;
        white-space: nowrap;
        font-size: 14px;
    }

    .fc-v-event .fc-event-title {
        bottom: 0px;
        max-height: 100%;
        overflow: hidden;
        top: 0px;
        font-size: 14px;
    }

    /* Hover Tooltip for month view */
    #schedule-hover-tooltip {
        display: none;
        position: fixed;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.18);
        padding: 12px 16px;
        min-width: 220px;
        max-width: 320px;
        pointer-events: none;
        font-size: 13px;
        line-height: 1.6;
    }
    #schedule-hover-tooltip .tooltip-date {
        font-weight: bold;
        font-size: 14px;
        margin-bottom: 8px;
        color: #333;
        border-bottom: 1px solid #eee;
        padding-bottom: 5px;
    }
    #schedule-hover-tooltip .tooltip-item {
        padding: 5px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    #schedule-hover-tooltip .tooltip-item:last-child { border-bottom: none; }
    #schedule-hover-tooltip .no-schedule { color: #999; font-style: italic; }

    /* Inline schedule name tags in calendar cells */
    .fc-schedule-names-injected {
        padding: 2px 3px 3px 3px;
    }
    .fc-schedule-name-tag {
        font-size: 11px;
        font-weight: 600;
        color: #1a1a2e;
        background: rgba(255,255,255,0.72);
        border-left: 3px solid #5c6bc0;
        border-radius: 3px;
        padding: 2px 5px;
        margin-bottom: 2px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 100%;
        cursor: default;
        display: block;
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
                            <label for="plant">Plant Name<b class="require">*</b></label>
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
                            <label for="machine">Machine Name<b class="require">*</b></label>
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

<!-- Hover Tooltip -->
<div id="schedule-hover-tooltip"></div>

<!-- Schedule Modal (kept for reference, no longer used in month view) -->
<div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="scheduleModalLabel">Schedules for <span id="modalDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Schedule list will be populated here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        // $('#product_master .child_menu').show();
        $('#product_master').addClass('nv active');
        // $('.right_col').addClass('active_right');
        $('.production_schedule').addClass('active_cc');
        // $('#product_master').addClass('nv active-color');
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="
https://cdn.jsdelivr.net/npm/@dmuy/timepicker@2.0.2/dist/mdtimepicker.min.js
"></script>

<script>
    let currentRescheduleEvent = null;
    let editingSlotIndex = -1;
    isRescheduled = false;
    let rech_id = '';
    let globalScheduleDetails = {};
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
                left: '',
                center: 'title',
                right: 'switchToDayViewBtn'
            },
            customButtons: {
                switchToDayViewBtn: {
                    text: 'Day View',
                    click: function () {
                        var dateStr = $('#filter_date').val();
                        var plantId = $('#plant_id').val();
                        var machineId = $('#machine_id').val();

                        if (!dateStr || !plantId || !machineId) {
                            alert("Please select plant, machine and date first");
                            return;
                        }

                        var parts = dateStr.split('-');
                        var d = new Date(parts[2], parts[1] - 1, parts[0]);

                        calendar.changeView('timeGridDay');
                        calendar.gotoDate(d);
                        fetchScheduleData(d, plantId, machineId);
                    }
                },
            },
            dayCellDidMount: function(info) {
                // Attach hover tooltip to each day cell in month view
                $(info.el).on('mouseenter', function(e) {
                    if (calendar.view.type !== 'dayGridMonth') return;
                    const dateStr = info.date.toLocaleDateString('sv-SE');
                    const schedules = globalScheduleDetails[dateStr] || [];
                    const $tip = $('#schedule-hover-tooltip');
                    let html = `<div class="tooltip-date">📅 ${dateStr}</div>`;
                    if (schedules.length > 0) {
                        schedules.forEach(function(s) {
                            html += `<div class="tooltip-item">
                                <strong>${s.name}</strong><br>
                                Qty: ${s.qty} &nbsp;|&nbsp; Color: ${s.color_name}<br>
                                🕒 ${s.start} – ${s.end}
                            </div>`;
                        });
                    } else {
                        html += '<div class="no-schedule">No schedules for this day.</div>';
                    }
                    $tip.html(html).show();
                }).on('mousemove', function(e) {
                    if (calendar.view.type !== 'dayGridMonth') return;
                    var tipW = $('#schedule-hover-tooltip').outerWidth();
                    var tipH = $('#schedule-hover-tooltip').outerHeight();
                    var left = e.clientX + 15;
                    var top  = e.clientY + 10;
                    // Keep tooltip inside viewport
                    if (left + tipW > window.innerWidth - 10) left = e.clientX - tipW - 10;
                    if (top + tipH > window.innerHeight - 10) top = e.clientY - tipH - 10;
                    $('#schedule-hover-tooltip').css({ left: left, top: top });
                }).on('mouseleave', function() {
                    $('#schedule-hover-tooltip').hide();
                });
            },
            // ... other options ...
            eventContent: function (arg) {
                if (arg.event.display === 'background') {
                    return { html: `<div style="text-align: center; font-weight: bold; color: #212121;">${arg.event.title}</div>` };
                }
                if (arg.view.type === 'dayGridMonth') {
                    const props = arg.event.extendedProps;
                    const startTimes = props.startDateTime || '';
                    alert(startTimes);
                    const name = props.name || '';
                    const qty = props.qty || '';
                    const start = props.startStr || '';
                    const end = props.endStr || '';

                    let html = '<div style="white-space: normal; font-size: 12px;">';
                    if (startTimes){
                        html += `<div><strong>Start Date:</strong> ${startTimes}</div>`;
                    }
                    if (name || qty) {
                        html += `<div><strong>Article:</strong> ${name}${qty ? ' Qty: ' + qty : ''}</div>`;
                    }
                    if (start && end) {
                        html += `<div><strong>Time:</strong> ${start} - ${end}</div>`;
                    }
                    html += '</div>';
                    return { html };
                }
                return true;
            },
            // ... other options ...
            eventDidMount: function (info) {
                if (info.event.classNames.includes('selected-slot')) {
                    info.el.style.fontSize = '16px';
                    info.el.style.fontWeight = 'bold';
                    info.el.style.color = 'white';
                }
            },
            slotMinTime: '08:00:00',
            slotMaxTime: '33:00:00',
            businessHours: {
                daysOfWeek: [1, 2, 3, 4, 5, 6],
                startTime: '08:00',
                endTime: '33:00'
            },
            dateClick: onDateClick,
            eventDidMount: onEventRender,
            datesSet: function (info) {
                if (info.view.type === 'dayGridMonth') {
                    $('.excute_btn').hide();
                    // Re-inject names after month navigation
                    setTimeout(injectScheduleNamesInCells, 200);
                } else {
                    $('.excute_btn').show();
                    $('.fc-schedule-names-injected').remove();
                }
            }
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

            $calendarWrap.css('opacity', 1);
            $('.fc-button').prop('disabled', false);

            // Auto-show the month calendar whenever all 3 fields are filled
            onViewMonthHistoryClick();
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
                    console.log(data);
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

                // Determine base date for 8 AM to next 8 AM window
                const scheduleDate = new Date(startTime);
                if (startTime.getHours() < 8) {
                    scheduleDate.setDate(scheduleDate.getDate() - 1);
                }
                const normalizedStartDate = normalizeDate(scheduleDate);

                // Duration in hours
                const duration = (endTime - startTime) / (1000 * 60 * 60);

                if (!coveredDates[normalizedStartDate]) {
                    coveredDates[normalizedStartDate] = 0;
                }
                coveredDates[normalizedStartDate] += duration;
            });
            fp.redraw();
            setTimeout(() => {
                const currentDate = new Date();
                const currentDateNormalized = normalizeDate(currentDate);
                const firstOfMonth = new Date(fp.currentYear, fp.currentMonth, 1);
                const firstOfMonthNorm = normalizeDate(firstOfMonth);

                fp.calendarContainer.querySelectorAll('.flatpickr-day').forEach(dayElem => {
                    const normalizedDateStr = normalizeDate(dayElem.dateObj);
                    const dateObj = new Date(normalizedDateStr);
                    const isPastDate = dateObj < currentDate;

                    const totalScheduledHours = coveredDates[normalizedDateStr] || 0;

                    if (totalScheduledHours >= 24) {
                        dayElem.style.backgroundColor = '#4CAF50'; // Green
                        // dayElem.style.color = '#FFFFFF';
                    } else if (totalScheduledHours > 0) {
                        dayElem.style.backgroundColor = '#FFC107'; // Yellow
                        // dayElem.style.color = '#212121';
                    } else if (isPastDate && normalizedDateStr >= firstOfMonthNorm) {
                        dayElem.style.backgroundColor = '#FF6B6B'; // Red
                        // dayElem.style.color = '#FFFFFF';
                    }
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
        // function onViewMonthHistoryClick() {
        //     const plant_id = $('#plant_id').val();
        //     const machine_id = $('#machine_id').val();
        //     const fp = $('#filter_date')[0]._flatpickr;
        //     const selectedMonth = fp.currentMonth + 1;
        //     const selectedYear = fp.currentYear;
        //     const month = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;
        //     const currentDate = new Date();
        //     currentDate.setHours(0, 0, 0, 0);

        //     $.ajax({
        //         url: '<?= base_url("admin/Ajax_controller/get_month_schedule_data") ?>',
        //         method: 'POST',
        //         dataType: 'json',
        //         data: { month, plant_id, machine_id },
        //         success(data) {
        //             calendar.removeAllEvents();
        //             const coveredDates = {}; // Total hours per date
        //             const scheduleCountMap = {}; // Schedule count per date
        //             const scheduleDetails = {}; // Schedule details for modal

        //             // Process schedules
        //             data.forEach(function (serverEvt) {
        //                 const startTime = new Date(serverEvt.start);
        //                 const endTime = new Date(serverEvt.end);

        //                 // Normalize date to 8:00 AM window
        //                 let displayDate = new Date(startTime);
        //                 if (startTime.getHours() < 8) {
        //                     displayDate.setDate(displayDate.getDate() - 1);
        //                 }
        //                 const dateStr = displayDate.toLocaleDateString('sv-SE');

        //                 // Initialize data structures
        //                 if (!coveredDates[dateStr]) coveredDates[dateStr] = 0;
        //                 if (!scheduleCountMap[dateStr]) scheduleCountMap[dateStr] = 0;
        //                 if (!scheduleDetails[dateStr]) scheduleDetails[dateStr] = [];

        //                 // Increment schedule count
        //                 scheduleCountMap[dateStr] += 1;

        //                 // Store schedule details for modal
        //                 scheduleDetails[dateStr].push({
        //                     id: serverEvt.id,
        //                     name: serverEvt.name || 'N/A',
        //                     qty: serverEvt.qty || 'N/A',
        //                     start: startTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
        //                     end: endTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true })
        //                 });
        //                 console.log("scheduleDetails",scheduleDetails);
        //                 // Calculate hours for coverage
        //                 const tStart = new Date(displayDate);
        //                 tStart.setHours(8, 0, 0, 0);
        //                 const tEnd = new Date(tStart);
        //                 tEnd.setDate(tEnd.getDate() + 1);

        //                 const overlapStart = startTime > tStart ? startTime : tStart;
        //                 const overlapEnd = endTime < tEnd ? endTime : tEnd;

        //                 if (overlapEnd > overlapStart) {
        //                     const duration = (overlapEnd - overlapStart) / (1000 * 60 * 60);
        //                     coveredDates[dateStr] += duration;
        //                 }
        //             });

        //             // Get all dates in the month
        //             const datesInMonth = getDatesInMonth(selectedYear, selectedMonth).map(d => d.toLocaleDateString('sv-SE'));

        //             // Add background events for all dates
        //             datesInMonth.forEach(dateStr => {
        //                 const totalHours = coveredDates[dateStr] || 0;
        //                 const scheduleCount = scheduleCountMap[dateStr] || 0;
        //                 const isPastDate = new Date(dateStr) < currentDate;

        //                 // Default to red for no schedules or past dates
        //                 let color = '#FF6B6B'; // Red
        //                 if (totalHours >= 1) {
        //                     // Build timeline for color logic
        //                     const coverageStart = new Date(dateStr + 'T08:00:00');
        //                     const coverageEnd = new Date(coverageStart);
        //                     coverageEnd.setDate(coverageEnd.getDate() + 1);

        //                     const matchingEvents = data.filter(evt => {
        //                         const s = new Date(evt.start);
        //                         const e = new Date(evt.end);
        //                         return s < coverageEnd && e > coverageStart;
        //                     });

        //                     let timeline = matchingEvents.map(evt => {
        //                         const s = new Date(evt.start);
        //                         const e = new Date(evt.end);
        //                         const start = s > coverageStart ? s : coverageStart;
        //                         const end = e < coverageEnd ? e : coverageEnd;
        //                         return [start, end];
        //                     }).filter(([s, e]) => e > s);

        //                     timeline.sort((a, b) => a[0] - b[0]);
        //                     let mergedTimeline = [];
        //                     for (let segment of timeline) {
        //                         if (!mergedTimeline.length) {
        //                             mergedTimeline.push(segment);
        //                         } else {
        //                             let last = mergedTimeline[mergedTimeline.length - 1];
        //                             if (segment[0].getTime() <= last[1].getTime()) {
        //                                 last[1] = new Date(Math.max(last[1].getTime(), segment[1].getTime()));
        //                             } else {
        //                                 mergedTimeline.push(segment);
        //                             }
        //                         }
        //                     }

        //                     if (mergedTimeline.length === 1 &&
        //                         mergedTimeline[0][0].getTime() === coverageStart.getTime() &&
        //                         mergedTimeline[0][1].getTime() === coverageEnd.getTime()) {
        //                         color = '#4CAF50'; // Green (fully covered)
        //                     } else {
        //                         color = '#FFC107'; // Yellow (partially covered)
        //                     }
        //                 } else if (isPastDate) {
        //                     color = '#FF6B6B'; // Red for past dates without schedules
        //                 }

        //                 // Add background event with schedule count
        //                 calendar.addEvent({
        //                     start: dateStr,
        //                     allDay: true,
        //                     display: 'background',
        //                     backgroundColor: color,
        //                     title: scheduleCount ? `${scheduleCount} schedule${scheduleCount !== 1 ? 's' : ''}` : 'No schedules'
        //                 });
        //             });

        //             // Store schedule details for modal
        //             calendar.setOption('custom', { scheduleDetails });

        //             calendar.changeView('dayGridMonth');
        //             calendar.gotoDate(month + '-01');
        //         },
        //         error() {
        //             Swal.fire('Error', 'Could not load schedule', 'error');
        //         }
        //     });
        // }
        function onViewMonthHistoryClick() {
            const plant_id = $('#plant_id').val();
            const machine_id = $('#machine_id').val();
            const fp = $('#filter_date')[0]._flatpickr;
            // Use the actual selected date's month if available, else flatpickr's current view
            let selectedMonth, selectedYear;
            if (fp.selectedDates && fp.selectedDates.length > 0) {
                selectedMonth = fp.selectedDates[0].getMonth() + 1;
                selectedYear  = fp.selectedDates[0].getFullYear();
            } else {
                selectedMonth = fp.currentMonth + 1;
                selectedYear  = fp.currentYear;
            }
            const month = `${selectedYear}-${selectedMonth.toString().padStart(2, '0')}`;
            const currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0);

            $.ajax({
                url: '<?= base_url("admin/Ajax_controller/get_month_schedule_data") ?>',
                method: 'POST',
                dataType: 'json',
                data: { month, plant_id, machine_id },
                success(data) {
                    console.log(data); 
                    calendar.removeAllEvents();
                    const coveredDates = {};
                    const scheduleCountMap = {};
                    globalScheduleDetails = {}; 

                    data.forEach(function (serverEvt) {
                        const startTime = new Date(serverEvt.start);
                        const endTime = new Date(serverEvt.end);

                        let displayDate = new Date(startTime);
                        if (startTime.getHours() < 8) {
                            displayDate.setDate(displayDate.getDate() - 1);
                        }
                        const dateStr = displayDate.toLocaleDateString('sv-SE');

                        // Initialize data structures
                        if (!coveredDates[dateStr]) coveredDates[dateStr] = 0;
                        if (!scheduleCountMap[dateStr]) scheduleCountMap[dateStr] = 0;
                        if (!globalScheduleDetails[dateStr]) globalScheduleDetails[dateStr] = [];

                        // Increment schedule count
                        scheduleCountMap[dateStr] += 1;

                        // Store schedule details
                        globalScheduleDetails[dateStr].push({
                            id: serverEvt.id,
                            name: serverEvt.name || serverEvt.title || 'N/A', // Fallback to title if name is missing
                            qty: serverEvt.qty || serverEvt.extendedProps?.qty || 'N/A',
                            start: startTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
                            end: endTime.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true }),
                            startDateTime: serverEvt.start,
                            color_name: serverEvt.color_name || 'N/A'
                        });

                        // Calculate hours
                        const tStart = new Date(displayDate);
                        tStart.setHours(8, 0, 0, 0);
                        const tEnd = new Date(tStart);
                        tEnd.setDate(tEnd.getDate() + 1);

                        const overlapStart = startTime > tStart ? startTime : tStart;
                        const overlapEnd = endTime < tEnd ? endTime : tEnd;

                        if (overlapEnd > overlapStart) {
                            const duration = (overlapEnd - overlapStart) / (1000 * 60 * 60);
                            coveredDates[dateStr] += duration;
                        }
                    });

                    // Get all dates in the month
                    const datesInMonth = getDatesInMonth(selectedYear, selectedMonth).map(d => d.toLocaleDateString('sv-SE'));

                    // Add background events for all dates
                    datesInMonth.forEach(dateStr => {
                        const totalHours = coveredDates[dateStr] || 0;
                        const scheduleCount = scheduleCountMap[dateStr] || 0;
                        const isPastDate = new Date(dateStr) < currentDate;

                        // Color logic
                        let color = '#FF6B6B'; // Red default
                        if (totalHours >= 1) {
                            const coverageStart = new Date(dateStr + 'T08:00:00');
                            const coverageEnd = new Date(coverageStart);
                            coverageEnd.setDate(coverageEnd.getDate() + 1);

                            const matchingEvents = data.filter(evt => {
                                const s = new Date(evt.start);
                                const e = new Date(evt.end);
                                return s < coverageEnd && e > coverageStart;
                            });

                            let timeline = matchingEvents.map(evt => {
                                const s = new Date(evt.start);
                                const e = new Date(evt.end);
                                const start = s > coverageStart ? s : coverageStart;
                                const end = e < coverageEnd ? e : coverageEnd;
                                return [start, end];
                            }).filter(([s, e]) => e > s);

                            timeline.sort((a, b) => a[0] - b[0]);
                            let mergedTimeline = [];
                            for (let segment of timeline) {
                                if (!mergedTimeline.length) {
                                    mergedTimeline.push(segment);
                                } else {
                                    let last = mergedTimeline[mergedTimeline.length - 1];
                                    if (segment[0].getTime() <= last[1].getTime()) {
                                        last[1] = new Date(Math.max(last[1].getTime(), segment[1].getTime()));
                                    } else {
                                        mergedTimeline.push(segment);
                                    }
                                }
                            }

                            if (mergedTimeline.length === 1 &&
                                mergedTimeline[0][0].getTime() === coverageStart.getTime() &&
                                mergedTimeline[0][1].getTime() === coverageEnd.getTime()) {
                                color = '#4CAF50'; // Green
                            } else {
                                color = '#FFC107'; // Yellow
                            }
                        } else if (isPastDate) {
                            color = '#FF6B6B'; // Red for past dates
                        }

                        // Add background event
                        calendar.addEvent({
                            start: dateStr,
                            allDay: true,
                            display: 'background',
                            backgroundColor: color,
                            title: scheduleCount ? `${scheduleCount} schedule${scheduleCount !== 1 ? 's' : ''}` : 'No schedules'
                        });
                    });

                    calendar.changeView('dayGridMonth');
                    calendar.gotoDate(month + '-01');
                    // Inject article names in cells after render
                    setTimeout(injectScheduleNamesInCells, 200);
                },
                error() {
                    Swal.fire('Error', 'Could not load schedule', 'error');
                }
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
        function injectScheduleNamesInCells() {
            // Remove any previously injected labels to avoid duplicates
            $('.fc-schedule-names-injected').remove();
            Object.keys(globalScheduleDetails).forEach(function(dateStr) {
                const schedules = globalScheduleDetails[dateStr];
                if (!schedules || schedules.length === 0) return;
                const cell = $('.fc-daygrid-day[data-date="' + dateStr + '"]');
                if (!cell.length) return;
                const frame = cell.find('.fc-daygrid-day-frame').first();
                let html = '<div class="fc-schedule-names-injected">';
                schedules.forEach(function(s) {
                    html += '<div class="fc-schedule-name-tag" title="' + s.name + '">' + s.name + '</div>';
                });
                html += '</div>';
                frame.append(html);
            });
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
            if (isRescheduled) {
                alert('You have a pending rescheduled event. Please execute it before adding a new one');
                return;
            }
            var today = new Date(); today.setHours(0, 0, 0, 0);
            if (calendar.view.type === 'dayGridMonth') {
                // Month view: tooltip on hover handles schedule display — click does nothing
                return;
            }
            var selectedDate = $dateInput.val();
            var plantId = $plantSelect.val();
            var machineId = $machineSelect.val();
            if (!plantId) {
                alert('⚠️ Please select a plant.');
                return;
            }

            if (!machineId) {
                alert('⚠️ Please select a machine.');
                return;
            }

            if (!selectedDate) {
                alert('⚠️ Please select a date.');
                return;
            }

            if (info.date < today) {
                alert('❌ Cannot book past dates.');
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

            label.style.color = 'white';
            label.style.fontWeight = 'bold';
            label.style.marginTop = '5px';
            label.style.marginBottom = '5px';
            label.style.marginLeft = '6px';
            label.style.marginRight = '6px';
            info.el.appendChild(label);
        }


        const currentDte = new Date().toISOString().split('T')[0];
        calendar.on('eventClick', function (info) {
            if (calendar.view.type === 'dayGridMonth') {
                return;
            }
            var today = info.event.start.toISOString().split('T')[0];
            if (today < currentDte) {
                alert('Cannot reschedule past dates.');
                return;
            }
            const event = info.event;
            var scheduleId = info.event.id;
            console.log('terte', scheduleId);

            if (!scheduleId) return;
            if(rech_id != scheduleId )
                if(isRescheduled) {
                    alert('You have a pending rescheduled event. Please execute it before adding a new one');
                    return;
                }
            const userConfirmed = confirm('Do you want to reschedule this slot?');
            
            if (userConfirmed) {
                currentRescheduleEvent = event;
                isRescheduled = true;
                rech_id = info.event.id;
                const eventStartDate = event.startStr.split('T')[0];
                const eventStartTime = event.start.toTimeString().substring(0, 5);
                openSchedulePopup(eventStartDate, eventStartTime, event, true);
            }
        });
        function showScheduleModal(selectedDate) {
            const schedulesForDay = globalScheduleDetails[selectedDate] || [];
            const modalBody = $('#scheduleModal .modal-body');
            modalBody.empty();

            // Update modal title
            $('#modalDate').text(selectedDate);

            if (schedulesForDay.length > 0) {
                let scheduleListHtml = '<ul class="list-group">';
                schedulesForDay.forEach((schedule) => {
                    scheduleListHtml += `
                <li class="list-group-item">
                    <strong>Article:</strong> ${schedule.name}<br>
                    <strong>Quantity:</strong> ${schedule.qty}<br>
                    <strong>Color:</strong> ${schedule.color_name}<br>
                    <strong>Time:</strong> ${schedule.start} - ${schedule.end}
                </li>`;
                });
                scheduleListHtml += '</ul>';
                modalBody.html(scheduleListHtml);
            } else {
                modalBody.html('<p class="text-muted">No schedules for this date.</p>');
            }

            $('#scheduleModal').modal('show');
        }
        function createTimeInput(labelText, defaultValue) {
            const div = document.createElement('div');
            const label = document.createElement('label');
            label.textContent = labelText;
            label.style.marginRight = '10px';
            const input = document.createElement('input');
            input.type = 'time';
            input.value = defaultValue;
            div.appendChild(label);
            div.appendChild(input);
            return { div, input };
        }

        function openSchedulePopup(data, time, event, isReschedule = false) {
            if (calendar.view.type === 'dayGridMonth') {
                return;
            }
            if (isReschedule && !event && currentRescheduleEvent) {
                event = currentRescheduleEvent;
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
            let formattedDate = 'Not selected';
            if (data) {
                const dateObj = new Date(data);
                const day = String(dateObj.getDate()).padStart(2, '0');
                const month = String(dateObj.getMonth() + 1).padStart(2, '0'); // Months are 0-based
                const year = dateObj.getFullYear();
                formattedDate = `${day}-${month}-${year}`;
            }
            dateInfo.textContent = `Date: ${formattedDate}`;
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
            fromTime.classList.add("mdtimepicker1");
            fromTimeDiv.appendChild(fromLabel);
            fromTimeDiv.appendChild(fromTime);

            const toTimeDiv = document.createElement('div');
            const toLabel = document.createElement('label');
            toLabel.textContent = 'To:';
            toLabel.style.marginRight = '10px';
            const toTime = document.createElement('input');
            toTime.type = 'time';
            toTime.classList.add("mdtimepicker2");
            toTimeDiv.appendChild(toLabel);
            toTimeDiv.appendChild(toTime);

            timeContainer.appendChild(fromTimeDiv);
            timeContainer.appendChild(toTimeDiv);

            let defaultStartTime = time ? time.substring(0, 5) : '08:00';
            let defaultEndTime = '08:00';
            if (isReschedule && event) {
                defaultStartTime = event.start.toTimeString().substring(0, 5);
                defaultEndTime = event.end.toTimeString().substring(0, 5);
            }
            fromTime.value = defaultStartTime;
            toTime.value = defaultEndTime;

            const submitBtn = document.createElement('button');
            submitBtn.textContent = isReschedule ? 'Reschedule' : 'Set Schedule';
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
                const flatpickrDate = fpInstance && fpInstance.selectedDates[0];
                const incrementedDate = new Date(flatpickrDate);
                incrementedDate.setDate(incrementedDate.getDate() + 1);
                flatpickrDateStr = incrementedDate.toISOString().split('T')[0];

                //This date Only use For storing in databse scheduled start date 
                const dateValuee = data.date || data;

                const dateValue = flatpickrDateStr;
                const fromTime24 = fromTime.value;
                const toTime24 = toTime.value;
                console.log('dateValue:', dateValue);
                const [fromHour] = fromTime24.split(':').map(Number);
                let adjustedDateValue = dateValue;
                let aDateValue = dateValuee;
                if (isReschedule) {
                    if (fromHour >= 0 && fromHour < 8) {
                        const adjustedStartDarte = new Date(dateValue);
                        adjustedStartDarte.setDate(adjustedStartDarte.getDate() + 1);
                        aDateValue = adjustedStartDarte.toISOString().split('T')[0];
                    }
                } else {
                    if (fromHour >= 0 && fromHour < 8) {
                        const adjustedStartDarte = new Date(dateValuee);
                        adjustedStartDarte.setDate(adjustedStartDarte.getDate());
                        aDateValue = adjustedStartDarte.toISOString().split('T')[0];
                    }
                }
                if (fromHour >= 0 && fromHour < 8) {
                    const nextDate = new Date(dateValue);
                    nextDate.setDate(nextDate.getDate() + 1);
                    adjustedDateValue = nextDate.toISOString().split('T')[0];
                }

                const endDateValue = getEndDate(adjustedDateValue, fromTime24, toTime24);
                const startDateTime = new Date(`${adjustedDateValue}T${fromTime24}:00`);
                const endDateTime = new Date(`${endDateValue}T${toTime24}:00`);



                const selected_date = fpInstance.selectedDates[0] || new Date(dateValue);
                const schedule_WindowStart = new Date(dateValue);
                schedule_WindowStart.setHours(8, 0, 0, 0);
                const schedule_WindowEnd = new Date(schedule_WindowStart);

                schedule_WindowEnd.setDate(schedule_WindowEnd.getDate() + 1);
                if (endDateTime > schedule_WindowEnd) {
                    alert("Start and End times must be between 8 AM of selected date and 8 AM of next day.");
                    return;
                }
                console.log('dateValuee:', dateValuee);

                console.log('schedule_WindowEnd:', schedule_WindowEnd);
                console.log('endDateValue:', endDateValue);
                console.log('startDateTimestartDatemodifyed:', aDateValue);
                console.log('endDateTime:', endDateTime);
                const hasOverlap = calendar.getEvents().some(e => {
                    if (isReschedule && event && e.id === event.id) {
                        return false;
                    }
                    const existingStart = e.start;
                    const existingEnd = e.end;
                    const isOverlapping = (
                        !e.classNames.includes('selected-slot') &&
                        startDateTime < existingEnd &&
                        endDateTime > existingStart
                    );
                    return isOverlapping;
                });
                if (hasOverlap) {
                    alert('This time slot overlaps with an existing slot!');
                    return;
                }
                if (isReschedule && event) {
                    event.setStart(startDateTime);
                    event.setEnd(endDateTime);
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
                    'production_schedule_start_date': aDateValue,
                    'production_schedule_end_date': endDateValue,
                    'production_schedule_start_time': fromTime24,
                    'production_schedule_end_time': toTime24,
                    'is_overnight': endDateValue !== dateValue,
                    'plant': $('#plant_id option:selected').text(),
                    'machine': $('#machine_id option:selected').text(),
                    'schedule_id': isReschedule ? event.id : null,
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
            mdtimepicker('.mdtimepicker1', { is24hour: true, });
            mdtimepicker('.mdtimepicker2', { is24hour: true, });

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
            isRescheduled = false;
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
                machine_id: $('#machine_id').val(),
                date: $('#filter_date').val()
            }, selectedScheduleData);
            console.log(ajaxData);
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