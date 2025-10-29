<?php if (session_status() == PHP_SESSION_NONE) { session_start(); } ?>
<header class="main-header">
    <div class="container">
        <a href="index.php" class="logo">VINTAGE STORE</a>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="index.php?kategori=Pria">Pria</a></li>
                <li><a href="index.php?kategori=Wanita">Wanita</a></li>
                <li><a href="index.php?kategori=Aksesoris">Aksesoris</a></li>
            </ul>
        </nav>
        <div class="header-extra">
            <form action="index.php" method="get" class="search-form">
                <input type="search" name="search" placeholder="Cari produk...">
                <button type="submit" class="search-button">
                    <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJsdWNpZGUgbHVjaWRlLXNlYXJjaCI+PGNpcmNsZSBjeD0iMTEiIGN5PSIxMSIgcj0iOCIvP3BhdGggZD0ibTIxIDIxLTQuMy00LjMiLz48L3N2Zz4=" alt="Search">
                </button>
            </form>
            <a href="cart.php" class="cart-link">
                <img src="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyNCIgaGVpZ2h0PSIyNCIgdmlld0JveD0iMCAwIDI0IDI0IiBmaWxsPSJub25lIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiIGNsYXNzPSJsdWNpZGUgbHVjaWRlLXNob3BwaW5nLWNhcnQiPjxjaXJjbGUgY3g9IjgiIGN5PSIyMSIgcj0iMSIvPjxjaXJjbGUgY3g9IjE5IiBjeT0iMjEiIHI9IjEiLz48cGF0aCBkPSJNMi4wNSA0LjA1aDIuMjFsMy4yNCAxMC43NGExIDEgMCAwIDAgMSAuNjVoOC40NmExIDEgMCAwIDAgMS0uNzVsMS43Mi02LjEzYTEgMSAwIDAgMC0xLS4yMkg1LjQzIi8+PC9zdmc+" alt="Cart">
                <?php
                // Display cart item count
                $cart_count = 0;
                if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
                    $cart_count = count($_SESSION['cart']);
                }
                if ($cart_count > 0) {
                    echo "<span class='cart-count'>$cart_count</span>";
                }
                ?>
            </a>
        </div>
    </div>
</header>"