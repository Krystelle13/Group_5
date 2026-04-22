<?php
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="3;url=login.php">
    <title>Leaving the Island</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(to bottom, #87CEEB 0%, #E0F7FA 70%, #FFD54F 100%); /* Sky to Sand */
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow: hidden;
        }
        .island-container {
            text-align: center;
            background: rgba(255, 255, 255, 0.8);
            padding: 40px;
            border-radius: 50% 50% 20% 20%;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .palm-tree { font-size: 50px; }
        h1 { color: #00796B; margin: 10px 0; }
        p { color: #004D40; }
        .boat {
            font-size: 40px;
            animation: sail 3s linear infinite;
        }
        @keyframes sail {
            0% { transform: translateX(-100px); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: translateX(100px); opacity: 0; }
        }
    </style>
</head>
<body>
    <div class="island-container">
        <div class="palm-tree">🏝️</div>
        <h1>Fair Winds!</h1>
        <p>Sailing back to the login page...</p>
        <div class="boat">⛵</div>
    </div>
</body>
</html>
