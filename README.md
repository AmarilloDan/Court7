## Game On Court Reservation System (Capstone)

Modern online court booking and management system that replaces manual sign-up sheets and phone bookings with real-time availability, prevention of double bookings, and admin analytics/reporting.

### Features

- **Member booking**
  - Real-time day view court availability
  - Create reservation with conflict protection
  - Booking history + booking details
  - Member dashboard stats (sessions, active days, frequent player)

- **Admin management**
  - Static admin login (separate from members)
  - Admin dashboard analytics
  - Manage reservations (update status)
  - Reports (date range summary, top players, court utilization)

### Requirements

- XAMPP (Apache + MySQL)
- PHP 8+ recommended

### Setup

1. **Copy project** into:
   - `C:\xampp\htdocs\court_system`
2. **Start XAMPP**
   - Start **Apache**
   - Start **MySQL**
3. **Create database**
   - Open phpMyAdmin
   - Import `database_schema.sql`
4. **Run the app**
   - Open `http://localhost/court_system/`

### Accounts

#### Member account (database-based)
- Create from **Register** page.

#### Admin account (static)
Edit `config/admin.php`:
- `ADMIN_EMAIL`
- `ADMIN_PASSWORD`
- `ADMIN_NAME`

Then sign in at `admin_login.php`.

### Notes for defense/presentation

- Double bookings are prevented by a database constraint:
  - UNIQUE `(court, reservation_date, time_slot)`
- Member and admin logins are intentionally separated:
  - Members come from the database
  - Admin credentials are static for controlled access in demo environments

