<!-- resources/views/auth/register.blade.php -->

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <!-- Add your CSS styling here -->
</head>
<body>
    <h1>Register</h1>
    
    <!-- Display Validation Errors -->
    @if ($errors->any())
        <div>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <!-- Registration Form -->
    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div>
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
        </div>

        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" required>
        </div>

        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
        </div>

        <div>
            <label for="password_confirmation">Confirm Password:</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit">Register</button>
    </form>

    <!-- Optional: Link to Login Page -->
    <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
</body>
</html>
