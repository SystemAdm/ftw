@php /** @var string $name *//** @var string $email *//** @var string $subjectLine *//** @var string $body */ @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, 'Noto Sans', 'Liberation Sans', sans-serif; color:#111827; }
        .wrap { max-width: 640px; margin: 0 auto; padding: 16px; }
        .muted { color:#6b7280; }
        .box { border:1px solid #e5e7eb; border-radius:12px; padding:16px; background:#fff; }
    </style>
    </head>
<body>
<div class="wrap">
    <h1>Contact form submission</h1>
    <p class="muted">From: {{ $name }} &lt;{{ $email }}&gt;</p>
    <div class="box">
        <h2 style="margin-top:0">{{ $subjectLine }}</h2>
        <pre style="white-space:pre-wrap; font-family: inherit; line-height:1.5;">{{ $body }}</pre>
    </div>
</div>
</body>
</html>
