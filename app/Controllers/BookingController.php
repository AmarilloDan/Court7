<?php
namespace App\Controllers;

class BookingController {
    public function calendar() {
        require __DIR__ . '/../Views/booking_calendar.php';
    }
    public function history() {
        require __DIR__ . '/../Views/booking_history.php';
    }
    public function create() {
        require __DIR__ . '/../Views/create_reservation.php';
    }
    public function view() {
        require __DIR__ . '/../Views/view_booking.php';
    }
}
