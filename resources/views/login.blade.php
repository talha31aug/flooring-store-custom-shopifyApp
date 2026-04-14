<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopify App</title>
    <link rel="stylesheet" href="https://cdn.shopify.com/s/assets/external/app.css">
    <style>
        body {
            background-color: #f4f6f8;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .card {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            width: 400px;
        }

        .card h2 {
            margin-bottom: 20px;
            font-size: 24px;
            color: #333333;
        }

        .card p {
            margin-bottom: 20px;
            font-size: 16px;
            color: #666666;
        }

        .card input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        .card button {
            width: 100%;
            padding: 10px;
            background-color: #5a67d8;
            border: none;
            border-radius: 5px;
            color: #ffffff;
            font-size: 16px;
            cursor: pointer;
        }

        .card button:hover {
            background-color: #4c51bf;
        }
    </style>
</head>
<body>
    <div class="card">
        <h2>Install Your Shopify App</h2>
        <p>Enter your shop domain to log in or install this app.</p>
        <form method="GET" action="{{ route('authenticate') }}">
            <input type="text" name="shop" placeholder="example.myshopify.com" required>
            <button type="submit">Log in</button>
        </form>
    </div>
    <script>
        // Fetch the active theme
        fetch(`https://${shop}/admin/api/2023-04/themes.json`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'X-Shopify-Access-Token': accessToken
        }
        })
        .then(response => response.json())
        .then(data => {
            // Find the active theme
            const activeTheme = data.themes.find(theme => theme.role === 'main');
            console.log('Active Theme ID:', activeTheme.id);
        })
        .catch(error => console.error('Error fetching themes:', error));

    </script>
</body>
</html>
