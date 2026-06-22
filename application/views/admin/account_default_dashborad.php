<?php include('header.php'); ?>
<link rel="stylesheet" href="<?= base_url() ?>assets/css/dashboard.css">

<style>
    .dashboard-container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .filter-select {
        display: none;
    }

    .select2-container {
        max-width: 100% !important;
    }

/* Modern & Premium Aesthetic Overrides for Account Dashboard Metric Cards */
.account-dashboard .metrics-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.account-dashboard .metric-card {
    position: relative;
    border-radius: 16px;
    padding: 22px;
    color: #ffffff !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.2) !important;
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1) !important;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    min-height: 140px !important;
    height: 100%;
    text-align: left;
}

/* Decorative radial glow */
.account-dashboard .metric-card::before {
    content: "";
    position: absolute;
    top: -20px;
    right: -20px;
    width: 100px;
    height: 100px;
    background: rgba(255, 255, 255, 0.12);
    border-radius: 50%;
    pointer-events: none;
    transition: all 0.5s ease;
    z-index: 1;
}

.account-dashboard .metric-card:hover::before {
    transform: scale(1.8);
    background: rgba(255, 255, 255, 0.18);
}

.account-dashboard .metric-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 20px 35px rgba(0, 0, 0, 0.14), 0 4px 12px rgba(0, 0, 0, 0.08) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
}

/* Card Gradients */
.account-dashboard .metric-card.auto-tasks {
    background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important;
}
.account-dashboard .metric-card.manual-tasks {
    background: linear-gradient(135deg, #0ea5e9 0%, #1d4ed8 100%) !important;
}

/* Typography */
.account-dashboard .metric-card h3 {
    font-size: 13px;
    font-weight: 600;
    color: rgba(255, 255, 255, 0.9) !important;
    margin: 0 0 14px 0 !important;
    line-height: 1.3 !important;
    letter-spacing: 0.01em;
    max-width: 75%;
    z-index: 2;
    text-transform: uppercase;
}

.account-dashboard .metric-card .metric-value {
    font-size: 32px !important;
    font-weight: 800 !important;
    color: #ffffff !important;
    margin: 0 !important;
    line-height: 1 !important;
    letter-spacing: -0.02em;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
    z-index: 2;
}

/* Glass Icons */
.account-dashboard .metric-card .metric-icon {
    position: absolute !important;
    top: 18px !important;
    right: 18px !important;
    background: rgba(255, 255, 255, 0.18) !important;
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(4px);
    border: 1px solid rgba(255, 255, 255, 0.25) !important;
    transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    z-index: 2;
}

.account-dashboard .metric-card .metric-icon i {
    font-size: 18px !important;
    color: #ffffff !important;
}

.account-dashboard .metric-card:hover .metric-icon {
    background: rgba(255, 255, 255, 0.28) !important;
    transform: rotate(8deg) scale(1.08);
}

.account-dashboard .metric-card a.metric-link {
    display: flex !important;
    flex-direction: column !important;
    justify-content: space-between !important;
    height: 100%;
    width: 100%;
    text-decoration: none !important;
    color: #ffffff !important;
}
</style>

<div class="right_col">
    <div class="dashboard account-dashboard">
        <div class="metrics-row">
            <div class="metric-card auto-tasks">
                <a href="<?= base_url('auto_task_list?date=&account_pending_task=1') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-cogs"></i></div>
                    <h3>Auto Pending Tasks Counter</h3>
                    <div class="metric-value"><?= $metrics['pending_account_task'] ?></div>
                </a>
            </div>
            <div class="metric-card manual-tasks">
                <a href="<?= base_url('task_list?date=&status_of_work=1&account=12') ?>" class="metric-link">
                    <div class="metric-icon"><i class="fas fa-user-clock"></i></div>
                    <h3>Manual Pending Tasks Counter</h3>
                    <div class="metric-value"><?= $metrics['manual_pending_account_task'] ?></div>
                </a>
            </div>
        </div>
        <div class="mb-4">
            <h5>TAT Calculater for each task</h5>
            <div class="data-table">
                <div class="table-container">
                    <table style="width: 100%;" class="table table-striped table-bordered material_list" id="tatTable">
                        <thead class="thead">
                            <tr>
                                <th>SR. NO.</th>
                                <th>Order No</th>
                                <th>Party Name</th>
                                <th>Forwarded Date</th>
                                <th>Completed Date</th>
                                <th>Total Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $count = 1;
                            foreach ($task_tat as $task): ?>
                                <tr data-order-id="<?= $task->order_id; ?>" style="cursor:pointer;">
                                    <td><?= $count++; ?></td>
                                    <td><?= $task->order_id; ?></td>
                                    <td><?= htmlspecialchars($task->party_name); ?></td>
                                    <td><?= date('d-m-Y', strtotime($task->order_date)); ?></td>
                                    <td><?= date('d-m-Y', strtotime($task->last_updated_date)); ?></td>
                                    <td><?= $task->task_duration_days; ?> days</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


    </div>
</div>
<?php include('footer.php'); ?>
<script>
    $(document).ready(function () {
        var table = $('#tatTable').DataTable({
            searching: true,
            ordering: false,
            scrollCollapse: true,
            dom: "Blfrtip",
            buttons: [{
                extend: 'excel',
                footer: true,
                title: 'TAT Calculator List',
                filename: 'tat_calculator_list',
                exportOptions: { columns: [0, 1, 2, 3, 4,5] }
            }]
        });

        $('#tatTable tbody').on('click', 'tr', function () {
            var orderId = $(this).data('order-id');
            if (orderId) {
                window.location.href = "<?= base_url('auto_task_list') ?>?order_id=" + orderId;
            }
        });
    });
</script>