<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Create Dynamic Dashboard</title>

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
        @endif
    </head>
    <body class="min-h-screen bg-gray-50">
        <div class="max-w-xl mx-auto py-12 px-4">
            @if (session('status'))
                <div class="mb-6 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                    {{ session('status') }}
                </div>
            @endif

            <h1 class="text-2xl font-semibold mb-6">Create Dynamic Dashboard</h1>

            <form action="{{ route('dynamic-dashboard.store') }}" method="POST" class="space-y-5 bg-white rounded-lg shadow p-6">
                @csrf

                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
                    <input id="title" name="title" type="text" value="{{ old('title') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" />
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea id="description" name="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex items-center px-5 py-2.5 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                        Save Dashboard
                    </button>
                    <a href="{{ url('/') }}" class="text-sm text-gray-600 hover:text-gray-900">Cancel</a>
                </div>
            </form>
        </div>
    </body>
</html>


