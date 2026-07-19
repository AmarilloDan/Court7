<?php
require_once __DIR__ . '/includes/init.php';
require_role('admin');
$conn = db();

$result = mysqli_query($conn, "
    SELECT reservation_date, time_slot 
    FROM reservations 
    WHERE reservation_date = CURDATE()
    AND status = 'reserved'
");

$count = 0;

while($row = mysqli_fetch_assoc($result)){
    $timeSlot = explode('-', $row['time_slot']);
    $endTime = trim($timeSlot[1]);

    $endDateTime = strtotime($row['reservation_date'].' '.$endTime);
    $now = time();

    $diff = ($endDateTime - $now) / 60;

    if($diff <= 20 && $diff > 0){
        $count++;
    }
}

echo json_encode(['count'=>$count]);