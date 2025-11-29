<?php
require 'functions.php';

$pdo = pdo_connect_mysql();

// get page or default to home
$page = $_GET['page'] ?? 'home';

// verify the page file exists
if (!file_exists("$page.php")) {
    $page = 'home';
}

// send page name to the header
template_header($page);

// include page content
include $page . '.php';

// footer
template_footer();
