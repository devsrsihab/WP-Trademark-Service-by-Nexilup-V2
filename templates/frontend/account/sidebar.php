<!-- MOBILE MENU TRIGGER -->
<button class="tm-hamburger-btn">
    <svg class="tm-hamburger-icon" width="26" height="26" viewBox="0 0 24 24" fill="none"
        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <line x1="3" y1="6" x2="21" y2="6"></line>
        <line x1="3" y1="12" x2="21" y2="12"></line>
        <line x1="3" y1="18" x2="21" y2="18"></line>
    </svg>
</button>

<!-- MOBILE DROPDOWN MENU -->
<div class="tm-mobile-menu">
    <ul>
        <li><a href="?section=dashboard" class="<?php echo $section=='dashboard'?'active':''; ?>">Dashboard</a></li>
        <li><a href="?section=info" class="<?php echo $section=='info'?'active':''; ?>">User Information</a></li>
        <li><a href="?section=active" class="<?php echo $section=='active'?'active':''; ?>">Active Trademarks</a></li>
        <li><a href="?section=settings" class="<?php echo $section=='settings'?'active':''; ?>">Settings</a></li>

        <!-- 🔥 LOGOUT LINK (MOBILE MENU) -->
        <li>
            <a href="<?php echo wp_logout_url(home_url()); ?>" class="tm-logout-btn">
                Logout
            </a>
        </li>
    </ul>
</div>

<!-- Desktop Sidebar -->
<div class="tm-account-sidebar desktop-only">
    <ul>
        <li><a href="?section=dashboard" class="<?php echo $section=='dashboard'?'active':''; ?>">Dashboard</a></li>
        <li><a href="?section=info" class="<?php echo $section=='info'?'active':''; ?>">User Information</a></li>
        <li><a href="?section=active" class="<?php echo $section=='active'?'active':''; ?>">Active Trademarks</a></li>
        <li><a href="?section=settings" class="<?php echo $section=='settings'?'active':''; ?>">Settings</a></li>
    </ul>

    <!-- 🔥 LOGOUT BUTTON (DESKTOP) -->
    <div class="tm-logout-area">
        <a href="<?php echo wp_logout_url(home_url()); ?>" class="tm-logout-btn">
            Logout
        </a>
    </div>
</div>
