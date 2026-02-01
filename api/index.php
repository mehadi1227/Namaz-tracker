<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #fff;
        }
        
        .container {
            text-align: center;
            padding: 20px;
        }
        
        .error-code {
            font-size: 150px;
            font-weight: bold;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .error-message {
            font-size: 28px;
            margin-bottom: 15px;
        }
        
        .error-description {
            font-size: 16px;
            margin-bottom: 30px;
            opacity: 0.9;
        }
        
        .home-btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: #fff;
            color: #667eea;
            text-decoration: none;
            border-radius: 25px;
            font-weight: bold;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .home-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-code">404</div>
        <h1 class="error-message">Page Not Found</h1>
        <p class="error-description">The page you are looking for doesn't exist or has been moved.</p>
        <a href="/Namaz Scheduling APP/" class="home-btn">Go Back Home</a>
    </div>
</body>
</html>