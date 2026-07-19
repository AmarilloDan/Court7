require_once __DIR__ . '/../Controllers/InventoryController.php';
use App\Controllers\InventoryController;
    case 'inventory':
        (new InventoryController())->index();
        break;
require_once __DIR__ . '/../Controllers/ExpenseController.php';
use App\Controllers\ExpenseController;
    case 'expenses':
        (new ExpenseController())->index();
        break;
require_once __DIR__ . '/../Controllers/AdminController.php';
use App\Controllers\AdminController;
    case 'admin_dashboard':
        (new AdminController())->dashboard();
        break;
    case 'admin_bookings':
        (new AdminController())->bookings();
        break;
    case 'admin_check_time':
        (new AdminController())->checkTime();
        break;
    case 'admin_login':
        (new AdminController())->login();
        break;
require_once __DIR__ . '/../Controllers/BookingController.php';
use App\Controllers\BookingController;
    case 'booking_calendar':
        (new BookingController())->calendar();
        break;
    case 'booking_history':
        (new BookingController())->history();
        break;
    case 'create_reservation':
        (new BookingController())->create();
        break;
    case 'view_booking':
        (new BookingController())->view();
        break;
require_once __DIR__ . '/../Controllers/ProfileController.php';
use App\Controllers\ProfileController;
    case 'profile':
        (new ProfileController())->index();
        break;
require_once __DIR__ . '/../Controllers/AuthController.php';
use App\Controllers\AuthController;
    case 'login':
        (new AuthController())->login();
        break;
    case 'register':
        (new AuthController())->register();
        break;
    case 'logout':
        (new AuthController())->logout();
        break;
require_once __DIR__ . '/../Controllers/ReportsController.php';
use App\Controllers\ReportsController;
    case 'reports':
        (new ReportsController())->index();
        break;
require_once __DIR__ . '/../Controllers/CourtsController.php';
use App\Controllers\CourtsController;
    case 'manage_courts':
        (new CourtsController())->index();
        break;
require_once __DIR__ . '/../Controllers/ProductsController.php';
use App\Controllers\ProductsController;
    case 'products':
        (new ProductsController())->index();
        break;
<?php
// Basic router for MVC structure
// Example: /manage_reservations -> ManageReservationsController@index

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

require_once __DIR__ . '/../Controllers/ManageReservationsController.php';
require_once __DIR__ . '/../Controllers/UserDashboardController.php';
use App\Controllers\ManageReservationsController;
use App\Controllers\UserDashboardController;

switch ($uri) {
    case 'manage_reservations':
        (new ManageReservationsController())->index();
        break;
    case 'user_dashboard':
        (new UserDashboardController())->index();
        break;
    // Add more routes as needed
    default:
        require __DIR__ . '/../Views/404.php';
        break;
}
