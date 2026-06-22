<?php
$data = array('draw' => 1, 'start' => 0, 'length' => 10, 'search' => array('value' => ''));
$ch = curl_init('http://localhost/krivisha/admin/Ajax_controller/get_all_outward_order_list');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
curl_close($ch);
echo $response;
