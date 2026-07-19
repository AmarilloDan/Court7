<?php
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function render_auth_header(string $title, string $pageClass = ''): void
{
    $cls = trim($pageClass);
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($title); ?></title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="auth-page<?php echo $cls !== '' ? ' ' . e($cls) : ''; ?>">
            <div class="auth-shell">
    <?php
}

function render_auth_footer(): void
{
    ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function render_header(string $title, array $nav = [], array $actions = []): void
{
    $role = current_user_role();
    $current = basename($_SERVER['PHP_SELF'] ?? '');

    if (empty($nav)) {
        if ($role === 'admin') {
            $nav = [
                ['label' => 'Dashboard', 'href' => 'admin_dashboard.php'],
                ['label' => 'Manage Reservations', 'href' => 'manage_reservations.php'],
                ['label' => 'Court Management', 'href' => 'manage_courts.php'],
                ['label' => 'Reports', 'href' => 'reports.php'],
            ];
        } elseif ($role === 'user') {
            $nav = [
                ['label' => 'Dashboard', 'href' => 'user_dashboard.php'],
                ['label' => 'Booking Calendar', 'href' => 'booking_calendar.php'],
                ['label' => 'Booking History', 'href' => 'booking_history.php'],
            ];
        } else {
            $nav = [
                ['label' => 'Home', 'href' => 'index.php'],
                ['label' => 'Register', 'href' => 'register.php'],
            ];
        }
    }

    foreach ($nav as $i => $item) {
        if (isset($nav[$i]['active'])) {
            continue;
        }
        $nav[$i]['active'] = (basename((string)($item['href'] ?? '')) === $current);
    }

    if (empty($actions)) {
        if ($role === 'user') {
            $actions = [
                ['label' => '', 'href' => '', 'variant' => 'primary'],
                ['label' => '', 'href' => '', 'variant' => 'secondary'],
            ];
        } elseif ($role === 'admin') {
            $actions = [
                ['label' => '', 'href' => '', 'variant' => 'primary'],
                ['label' => '', 'href' => '', 'variant' => 'secondary'],
                ['label' => '', 'href' => '', 'variant' => 'secondary'],
            ];
        } else {
            $actions = [
                ['label' => 'Member Login', 'href' => 'index.php', 'variant' => 'primary'],
                ['label' => 'Create Account', 'href' => 'register.php', 'variant' => 'secondary'],
            ];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo e($title); ?></title>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <div class="app-shell">
            <aside class="sidebar">  
        <div class="logo" style="text-align:center; margin-bottom:15px; line-height:0;">
        <img src="images/yeah.png"
         alt="Court 7 Logo"
         style="width:150px; height:auto; display:block; margin:0 auto; object-fit:contain;">
        </div>

                <div class="sidebar-brand">GAME ON</div>
                <?php if ($role !== '') : ?>
                    <div class="sidebar-user">
                        <div class="sidebar-user-name"><?php echo e(current_user_name()); ?></div>
                        <div class="sidebar-user-role"><?php echo e(strtoupper($role)); ?></div>
                    </div>
                <?php endif; ?>

                <nav class="sidebar-nav">
                    <?php foreach ($nav as $item) : ?>
                        <a class="sidebar-link <?php echo !empty($item['active']) ? 'active' : ''; ?>"
                           href="<?php echo e($item['href']); ?>">
                            <?php echo e($item['label']); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="sidebar-footer">
                    <?php if ($role !== '') : ?>
                        <a href="logout.php" class="sidebar-link danger">Logout</a>
                    <?php else : ?>
                        <a href="login.php" class="sidebar-link">Login</a>
                        <a href="register.php" class="sidebar-link">Register</a>
                    <?php endif; ?>
                </div>
            </aside>

            <main class="main">
                <header class="main-topbar">
                    <div class="main-topbar-title"><?php echo e($title); ?></div>
                    <div class="main-topbar-actions">
                        <?php foreach ($actions as $a) : ?>
                            <?php
                            $variant = (string)($a['variant'] ?? 'secondary');
                            $btnClass = $variant === 'primary' ? 'btn btn-primary btn-xs' : 'btn btn-secondary btn-xs';
                            ?>
                            <a class="<?php echo e($btnClass); ?>" href="<?php echo e((string)$a['href']); ?>">
                                <?php echo e((string)$a['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </header>
                <div class="main-content">
    <?php
}

function render_footer(): void
{
    ?>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
}

