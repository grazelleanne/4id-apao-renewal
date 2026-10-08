<?php
namespace App\Support;

/** Request-local business state persisted by Laravel's session middleware. */
final class SessionState
{
    public static array $data = [];
}
