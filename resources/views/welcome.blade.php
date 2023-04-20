<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Laravel</title>
</head>

<body class="antialiased">
  <fieldset>
    <legend>MENU</legend>
    <ul>
      <li><a href="/login">LOGIN</a></li>
      <li><a href="/logout">LOGOUT</a></li>
    </ul>
  </fieldset>

  @if ($loggedIn)
  <fieldset>
    <legend>USER</legend>
    <ul>
      <li>Sub: {{ @$userInfo['sub'] }}</li>
      <li>name: {{ @$userInfo['name'] }}</li>
    </ul>
  </fieldset>
  @endif

  <p>Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }})</p>
</body>

</html>
