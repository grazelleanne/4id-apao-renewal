<?php
declare(strict_types=1);

final class VanillaView
{
    public function __construct(private string $name, private array $data = []) {}

    public function render(): string
    {
        return render_view($this->name, $this->data);
    }

    public function with(array|string $key, mixed $value = null): self
    {
        $this->data = array_merge($this->data, is_array($key) ? $key : [$key => $value]);
        return $this;
    }
}

final class VanillaViewEnvironment
{
    /** @var array<int,array{index:int,count:int}> */
    private array $loops = [];

    public function make(string $name, array $data = []): VanillaView
    {
        return new VanillaView($name, $data);
    }

    public function addLoop(iterable $data): void
    {
        $count = is_countable($data) ? count($data) : 0;
        $this->loops[] = ['index' => -1, 'count' => $count];
    }

    public function incrementLoopIndices(): void
    {
        $last = array_key_last($this->loops);
        if ($last !== null) {
            $this->loops[$last]['index']++;
        }
    }

    public function getLastLoop(): object
    {
        $loop = end($this->loops) ?: ['index' => 0, 'count' => 0];
        $index = $loop['index'];
        return (object) [
            'index' => $index,
            'iteration' => $index + 1,
            'remaining' => max(0, $loop['count'] - $index - 1),
            'count' => $loop['count'],
            'first' => $index === 0,
            'last' => $loop['count'] > 0 && $index === $loop['count'] - 1,
            'even' => ($index + 1) % 2 === 0,
            'odd' => ($index + 1) % 2 === 1,
        ];
    }

    public function popLoop(): void
    {
        array_pop($this->loops);
    }
}

final class VanillaCollection implements IteratorAggregate, Countable
{
    public function __construct(private array $items = []) {}

    public function getIterator(): Traversable { return new ArrayIterator($this->items); }
    public function count(): int { return count($this->items); }
    public function all(): array { return $this->items; }
    public function get(string|int $key, mixed $default = null): mixed { return $this->items[$key] ?? $default; }
    public function last(): mixed { return $this->items === [] ? null : $this->items[array_key_last($this->items)]; }
    public function take(int $limit): self { return new self(array_slice($this->items, 0, $limit, true)); }
    public function where(string $key, mixed $value): self
    {
        return new self(array_filter($this->items, fn ($item) => data_get($item, $key) === $value));
    }
    public function countBy(callable $callback): self
    {
        $counts = [];
        foreach ($this->items as $item) {
            $key = (string) $callback($item);
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }
        return new self($counts);
    }
}

final class Js
{
    public static function from(mixed $value): string
    {
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_THROW_ON_ERROR);
    }
}

final class VanillaDate extends DateTimeImmutable
{
    public function toDateString(): string { return $this->format('Y-m-d'); }
}

function collect(mixed $items = []): VanillaCollection
{
    if ($items instanceof VanillaCollection) {
        return $items;
    }
    return new VanillaCollection(is_array($items) ? $items : iterator_to_array($items));
}

function e(mixed $value, bool $doubleEncode = true): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8', $doubleEncode);
}

function asset(string $path): string
{
    return '/' . ltrim($path, '/');
}

function public_path(string $path = ''): string
{
    return APP_ROOT . '/public' . ($path === '' ? '' : '/' . ltrim($path, '/'));
}

function route(string $name, mixed $parameters = []): string
{
    $routes = [
        'login' => '/login', 'login.post' => '/login', 'login.captcha' => '/login/captcha',
        'logout' => '/logout', 'register' => '/register', 'forgot.password' => '/forgot-password',
        'session.status' => '/session/status',
        'admin.dashboard' => '/admin/dashboard', 'admin.dashboard.data' => '/admin/dashboard-data',
        'admin.personnel' => '/admin/personnel', 'admin.personnel.data' => '/admin/personnel-data',
        'admin.personnel.store' => '/admin/personnel',
        'admin.inspection' => '/admin/inspection', 'admin.reports' => '/admin/reports',
        'admin.archive' => '/admin/archive', 'admin.archive.data' => '/admin/archive-data',
        'admin.archive.restore' => '/admin/archive/restore',
        'admin.users' => '/admin/users', 'admin.users.data' => '/admin/users-data',
        'admin.users.store' => '/admin/users', 'admin.users.update' => '/admin/users/update',
        'admin.audit' => '/admin/audit', 'admin.audit.data' => '/admin/audit-data',
        'admin.notifications' => '/admin/notifications', 'admin.notifications.read' => '/admin/notifications/read',
        'admin.profile.update' => '/admin/profile', 'admin.profile.password' => '/admin/profile/password',
        'staff.dashboard' => '/staff/dashboard', 'staff.dashboard.data' => '/staff/dashboard-data',
        'staff.personnel.store' => '/staff/personnel', 'staff.personnel.availability' => '/staff/personnel/availability',
        'staff.notifications' => '/staff/notifications', 'staff.notifications.read' => '/staff/notifications/read',
        'staff.profile.update' => '/staff/profile', 'staff.profile.password' => '/staff/profile/password',
    ];

    $url = $routes[$name] ?? '#';
    if ($name === 'staff.par.pdf') {
        $id = is_object($parameters) ? ($parameters->id ?? '') : (is_array($parameters) ? ($parameters['id'] ?? reset($parameters)) : $parameters);
        return '/par/' . rawurlencode((string) $id) . '/pdf';
    }
    return $url;
}

function data_get(mixed $target, string|int|null $key, mixed $default = null): mixed
{
    if ($key === null) {
        return $target;
    }
    foreach (explode('.', (string) $key) as $segment) {
        if (is_array($target) && array_key_exists($segment, $target)) {
            $target = $target[$segment];
        } elseif (is_object($target) && isset($target->{$segment})) {
            $target = $target->{$segment};
        } else {
            return $default;
        }
    }
    return $target;
}

function old(string $key, mixed $default = null): mixed { return $default; }
function session(string $key, mixed $default = null): mixed { return data_get($_SESSION, $key, $default); }
function now(): VanillaDate { return new VanillaDate('now'); }

function render_view(string $name, array $data = []): string
{
    $path = APP_ROOT . '/views/' . str_replace('.', '/', $name) . '.php';
    if (!is_file($path)) {
        throw new RuntimeException("View not found: {$name}");
    }
    $defaults = [
        'user' => (object) (current_user() ?? []),
        'initialDashboardData' => ['personnel' => []],
        'initialActiveTab' => 'registration',
        'initialFocusItem' => null,
    ];
    extract(array_merge($defaults, $data), EXTR_SKIP);
    $__env = new VanillaViewEnvironment();
    ob_start();
    try {
        include $path;
        return (string) ob_get_clean();
    } catch (Throwable $error) {
        ob_end_clean();
        throw $error;
    }
}
