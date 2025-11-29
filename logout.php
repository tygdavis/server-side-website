<?php
session_unset();
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Logged Out</title>
    <link rel="stylesheet" href="styles/logout.css">
</head>
<body>
    <main class="logout-wrapper">
        <div class="logout-card">

            <h1>
                Logging Out
                <span class="dots">
                    <span class="dot">.</span>
                    <span class="dot">.</span>
                    <span class="dot">.</span>
                </span>
            </h1>
            <p class="subtitle">Thanks for visiting Bread Co.</p>

            <p class="redirect-msg">Redirecting in <span id="count">2</span>…</p>
        </div>
    </main>

    <script>
        let c = 2;
        const el = document.getElementById("count");

        const timer = setInterval(() => {
            c--;
            el.textContent = c;
            if (c === 0) {
                clearInterval(timer);
                window.location.href = "index.php?page=home";
            }
        }, 1000);
    </script>
</body>
</html>
