<!-- resources/views/welcome.blade.php -->

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Application</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-900">

    <div class="flex items-center justify-center min-h-screen">
        <div class="text-center bg-white p-10 rounded-lg shadow-lg">
            <h1 class="text-4xl font-bold mb-4">Welcome to Our Application!</h1>
            <p class="text-lg text-gray-700 mb-6">We're glad to have you here. Get started by logging in or creating an account.</p>
            <div class="space-x-4">
                <!-- <a href="{{ route('login') }}" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Login</a> -->
                <!-- <a href="{{ route('register') }}" class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600">Register</a> -->
            </div>
        </div>
    </div>

</body>
</html>
