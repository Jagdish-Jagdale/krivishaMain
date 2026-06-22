<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=krivisha;charset=utf8', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "--- tbl_auto_task_list for ORD-2486 ---\n";
    $stmt = $pdo->prepare("SELECT * FROM tbl_auto_task_list WHERE task_id = 'ORD-2486'");
    $stmt->execute();
    print_r($stmt->fetch(PDO::FETCH_ASSOC));

    echo "\n--- tbl_outward_orders for ORD-2486 ---\n";
    $stmt = $pdo->prepare("SELECT * FROM tbl_outward_orders WHERE order_id = 'ORD-2486'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- tbl_order_sub_details for ORD-2486 ---\n";
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_sub_details WHERE order_id = 'ORD-2486'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

    echo "\n--- tbl_order_container_details for ORD-2486 ---\n";
    $stmt = $pdo->prepare("SELECT * FROM tbl_order_container_details WHERE order_id = 'ORD-2486'");
    $stmt->execute();
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
