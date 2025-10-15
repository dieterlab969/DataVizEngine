<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Wikipedia Table Visualizer</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.x/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
<header class="bg-blue-800 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
        <a href="{{ route('page_requests.index') }}" class="text-lg font-bold hover:underline">
            Wikipedia Table Visualizer
        </a>
    </div>
</header>

<main class="flex-grow">
    @yield('content')
</main>

<footer class="bg-gray-200 text-center p-4 text-sm text-gray-600">
    &copy; {{ date('Y') }} Your Company
</footer>
</body>
</html>
