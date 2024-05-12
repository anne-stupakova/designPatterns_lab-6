<header>
    <nav>
        <a class="logo" href="index.php">Memento</a>
        <ul>
            <li><a href="index.php">Головна</a></li>
            <li><a href="profile.php">Профіль</a></li>
            <li><a href="about.php">Про нас / Контакти</a></li>
        </ul>
    </nav>
</header>
<?php
$self = htmlspecialchars($_SERVER["PHP_SELF"]);
define('SELF_URL', $self);