<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Location;
use App\Models\User;
use App\Models\PhoneNumber;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Str;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use App\Events\EventUpdated;
use Illuminate\Support\Facades\Crypt;
use App\Models\EventLog;

class EventController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->string('search')->toString(),
            'status' => $request->string('status')->toString(), // draft | active | '' (all)
            'signup_needed' => $request->has('signup_needed') ? $request->boolean('signup_needed') : null,
            'trashed' => $request->string('trashed')->toString(), // all | only | without
        ];
        $sort = [
            'by' => $request->string('sort_by')->toString() ?: 'event_start',
            'dir' => strtolower($request->string('sort_dir')->toString() ?: 'desc'), // asc | desc
        ];

        $query = Event::query();

        // trashed filter
        switch ($filters['trashed'] === 'without' ? '' : $filters['trashed']) {
            case 'only':
                $query->onlyTrashed();
                break;
            case 'all':
                $query->withTrashed();
                break;
            default:
                // without trashed by default
                break;
        }

        // search title/slug/location_id
        if ($filters['search']) {
            $q = '%' . $filters['search'] . '%';
            $query->where(function ($sub) use ($q) {
                $sub->where('title', 'like', $q)
                    ->orWhere('slug', 'like', $q)
                    ->orWhere('excerpt', 'like', $q);
            });
        }

        // status filter
        if (in_array($filters['status'], ['draft', 'active'], true)) {
            $query->where('status', $filters['status']);
        }

        // signup_needed filter
        if ($filters['signup_needed'] !== null) {
            $query->where('signup_needed', $filters['signup_needed']);
        }

        // whitelist sortable columns
        $sortable = ['title', 'event_start', 'event_end', 'signup_start', 'signup_end', 'created_at'];
        $by = in_array($sort['by'], $sortable, true) ? $sort['by'] : 'event_start';
        $dir = $sort['dir'] === 'asc' ? 'asc' : 'desc';

        // Special case for title to sort case-insensitive
        if ($by === 'title') {
            $query->orderByRaw('LOWER(title) ' . ($dir === 'asc' ? 'ASC' : 'DESC'));
        } else {
            $query->orderBy($by, $dir);
        }

        $events = $query->paginate(15)->withQueryString();

        return Inertia::render('Admin/Event/Index', [
            'events' => $events,
            'filters' => $filters,
            'sort' => [
                'by' => $by,
                'dir' => $dir,
            ],
        ]);
    }

    public function create(): Response
    {
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Admin/Event/Edit', [
            'event' => null,
            'locations' => $locations,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateData($request);
        //$data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $event = Event::create($data);
        return redirect()->route('admin.events.index')->with('success', 'Event created');
    }

    public function show(Event $event): Response
    {
        // Cast datetimes to ISO strings for Inertia (only when present)
        foreach (['event_start', 'event_end', 'signup_start', 'signup_end'] as $dt) {
            if ($event->{$dt}) {
                $event->{$dt} = $event->{$dt}->toIso8601String();
            }
        }
        // Load related location (only id and name)
        $event->loadMissing(['location:id,name']);

        // Fetch related users for columns (minimal fields)
        $reserved = $event->reservations()->select('users.id', 'users.name', 'users.email')->get();
        $attendees = $event->attendees()->select('users.id', 'users.name', 'users.email')->get();
        $inside = $event->inside()->select('users.id', 'users.name', 'users.email')->get();

        return Inertia::render('Admin/Event/Show', [
            'event' => $event,
            'reserved' => $reserved,
            'attendees' => $attendees,
            'inside' => $inside,
        ]);
    }

    public function edit(Event $event): Response
    {
        // Cast datetimes to ISO strings for Inertia (only when present)
        foreach (['event_start', 'event_end', 'signup_start', 'signup_end'] as $dt) {
            if ($event->{$dt}) {
                $event->{$dt} = $event->{$dt}->toIso8601String();
            }
        }
        $locations = Location::query()->select('id', 'name')->orderBy('name')->get();
        return Inertia::render('Admin/Event/Edit', [
            'event' => $event,
            'locations' => $locations,
        ]);
    }

    public function update(Request $request, Event $event): RedirectResponse
    {
        $data = $this->validateData($request, $event->id);
        //$data['slug'] = $data['slug'] ?: Str::slug($data['title']);
        $event->update($data);
        return redirect()->route('admin.events.index')->with('success', 'Event updated');
    }

    public function destroy(Event $event): RedirectResponse
    {
        $event->delete();
        return back()->with('success', 'Event deleted');
    }

    public function restore(string $id): RedirectResponse
    {
        $event = Event::withTrashed()->findOrFail($id);
        if ($event->trashed()) {
            $event->restore();
        }
        return redirect()->route('admin.events.index')
            ->with('success', 'Event restored.');
    }

    public function forceDestroy(string $id): RedirectResponse
    {
        $event = Event::withTrashed()->findOrFail($id);
        $event->forceDelete();
        return redirect()->route('admin.events.index')
            ->with('success', 'Event permanently deleted.');
    }

    protected function validateData(Request $request, ?int $id = null): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('events', 'slug')->ignore($id)],
            'excerpt' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'image_path' => ['nullable', 'string', 'max:2048'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'event_start' => ['required', 'date'],
            'event_end' => ['nullable', 'date', 'after_or_equal:event_start'],
            'signup_needed' => ['sometimes', 'boolean'],
            'signup_start' => ['nullable', 'date'],
            'signup_end' => ['nullable', 'date', 'after_or_equal:signup_start'],
            'age_min' => ['nullable', 'integer', 'min:0', 'max:120'],
            'age_max' => ['nullable', 'integer', 'min:0', 'max:120'],
            'number_of_seats' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'status' => ['nullable', Rule::in(['draft', 'active'])],
        ]);
    }

    // ----- Attendance flow -----
    public function attendLookup(Request $request, Event $event)
    {
        $identifier = trim((string) $request->input('identifier'));
        if ($identifier === '') {
            return response()->json(['ok' => false, 'error' => 'Identifier is required.'], 422);
        }

        $type = str_contains($identifier, '@') ? 'email' : 'phone';
        $user = null;
        $normalized = null;

        if ($type === 'email') {
            $user = User::query()->where('email', $identifier)->first();
        } else {
            $normalized = $this->normalizePhone($identifier);
            if (!$normalized) {
                return response()->json(['ok' => false, 'error' => 'Invalid phone number.'], 422);
            }
            $pn = PhoneNumber::query()->where('e164', $normalized['e164'])->first();
            if ($pn) {
                $users = $pn->users()->select('users.id','users.name','users.email')->get();
                if ($users->count() === 1) {
                    $user = $users->first();
                } elseif ($users->count() > 1) {
                    return response()->json([
                        'ok' => true,
                        'found' => true,
                        'users' => $users,
                    ]);
                }
            }
        }

        if ($user) {
            return response()->json([
                'ok' => true,
                'found' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }

        return response()->json([
            'ok' => true,
            'found' => false,
            'type' => $type,
            'normalized' => $normalized,
        ]);
    }

    public function attend(Request $request, Event $event)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        // Attach existing user flow
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user && method_exists($user, 'isBanned') && $user->isBanned()) {
                $until = $user->banned_to ? $user->banned_to->toDateTimeString() : null;
                $reason = $user->ban_reason ?? null;
                return response()->json(['ok' => false, 'error' => $until
                    ? ("User is banned until {$until}." . ($reason ? " Reason: {$reason}" : ''))
                    : ("User is currently banned." . ($reason ? " Reason: {$reason}" : ''))], 422);
            }
            $event->attendees()->syncWithoutDetaching([$user->id]);
            $user = User::select('id','name','email')->find($user->id);
            $this->broadcastEventUpdate($event);
            return response()->json(['ok' => true, 'created' => false, 'user' => $user]);
        }

        $identifier = trim($data['identifier']);
        $type = str_contains($identifier, '@') ? 'email' : 'phone';

        // Recheck if user exists to avoid races
        if ($type === 'email') {
            $existing = User::query()->where('email', $identifier)->first();
            if ($existing) {
                if (method_exists($existing, 'isBanned') && $existing->isBanned()) {
                    $until = $existing->banned_to ? $existing->banned_to->toDateTimeString() : null;
                    $reason = $existing->ban_reason ?? null;
                    return response()->json(['ok' => false, 'error' => $until
                        ? ("User is banned until {$until}." . ($reason ? " Reason: {$reason}" : ''))
                        : ("User is currently banned." . ($reason ? " Reason: {$reason}" : ''))], 422);
                }
                $event->attendees()->syncWithoutDetaching([$existing->id]);
                $this->broadcastEventUpdate($event);
                return response()->json(['ok' => true, 'created' => false, 'user' => $existing]);
            }
        }

        // Create new user
        $name = $data['name'] ?? null;
        if (!$name) {
            return response()->json(['ok' => false, 'error' => 'Name is required to create a new user.'], 422);
        }

        $userAttrs = [
            'name' => $name,
        ];
        if ($type === 'email') {
            $userAttrs['email'] = $identifier;
        } else {
            // generate placeholder email to satisfy non-null/unique constraints if any
            $userAttrs['email'] = 'user+'.uniqid().'@example.invalid';
        }
        // set a random password so the record is valid
        $userAttrs['password'] = bcrypt(Str::random(16));

        $user = User::create($userAttrs);

        if ($type === 'phone') {
            $normalized = $this->normalizePhone($identifier);
            if ($normalized) {
                $pn = PhoneNumber::firstOrCreate(['e164' => $normalized['e164']], ['raw' => $normalized['raw']]);
                // attach as primary=false by default
                $user->phoneNumbers()->syncWithoutDetaching([$pn->id => ['primary' => true]]);
            }
        }

        $event->attendees()->syncWithoutDetaching([$user->id]);

        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true, 'created' => true, 'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]]);
    }

    // ----- Reservation flow (same as Attend, but using reservations pivot) -----
    public function reserveLookup(Request $request, Event $event)
    {
        $identifier = trim((string) $request->input('identifier'));
        if ($identifier === '') {
            return response()->json(['ok' => false, 'error' => 'Identifier is required.'], 422);
        }

        $type = str_contains($identifier, '@') ? 'email' : 'phone';
        $user = null;
        $normalized = null;

        if ($type === 'email') {
            $user = User::query()->where('email', $identifier)->first();
        } else {
            $normalized = $this->normalizePhone($identifier);
            if (!$normalized) {
                return response()->json(['ok' => false, 'error' => 'Invalid phone number.'], 422);
            }
            $pn = PhoneNumber::query()->where('e164', $normalized['e164'])->first();
            if ($pn) {
                $users = $pn->users()->select('users.id','users.name','users.email')->get();
                if ($users->count() === 1) {
                    $user = $users->first();
                } elseif ($users->count() > 1) {
                    return response()->json([
                        'ok' => true,
                        'found' => true,
                        'users' => $users,
                    ]);
                }
            }
        }

        if ($user) {
            return response()->json([
                'ok' => true,
                'found' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);
        }

        return response()->json([
            'ok' => true,
            'found' => false,
            'type' => $type,
            'normalized' => $normalized,
        ]);
    }

    public function reserve(Request $request, Event $event)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        // Attach existing user flow
        if (!empty($data['user_id'])) {
            $user = User::find($data['user_id']);
            if ($user && method_exists($user, 'isBanned') && $user->isBanned()) {
                $until = $user->banned_to ? $user->banned_to->toDateTimeString() : null;
                $reason = $user->ban_reason ?? null;
                return response()->json(['ok' => false, 'error' => $until
                    ? ("User is banned until {$until}." . ($reason ? " Reason: {$reason}" : ''))
                    : ("User is currently banned." . ($reason ? " Reason: {$reason}" : ''))], 422);
            }
            $event->reservations()->syncWithoutDetaching([$user->id]);
            $user = User::select('id','name','email')->find($user->id);
            $this->broadcastEventUpdate($event);
            return response()->json(['ok' => true, 'created' => false, 'user' => $user]);
        }

        $identifier = trim($data['identifier']);
        $type = str_contains($identifier, '@') ? 'email' : 'phone';

        // Recheck if user exists to avoid races
        if ($type === 'email') {
            $existing = User::query()->where('email', $identifier)->first();
            if ($existing) {
                $event->reservations()->syncWithoutDetaching([$existing->id]);
                $this->broadcastEventUpdate($event);
                return response()->json(['ok' => true, 'created' => false, 'user' => $existing]);
            }
        }

        // Create new user
        $name = $data['name'] ?? null;
        if (!$name) {
            return response()->json(['ok' => false, 'error' => 'Name is required to create a new user.'], 422);
        }

        $userAttrs = [
            'name' => $name,
        ];
        if ($type === 'email') {
            $userAttrs['email'] = $identifier;
        } else {
            // generate placeholder email to satisfy non-null/unique constraints if any
            $userAttrs['email'] = 'user+'.uniqid().'@example.invalid';
        }
        // set a random password so the record is valid
        $userAttrs['password'] = bcrypt(Str::random(16));

        $user = User::create($userAttrs);

        if ($type === 'phone') {
            $normalized = $this->normalizePhone($identifier);
            if ($normalized) {
                $pn = PhoneNumber::firstOrCreate(['e164' => $normalized['e164']], ['raw' => $normalized['raw']]);
                // attach as primary=true to mirror attend flow
                $user->phoneNumbers()->syncWithoutDetaching([$pn->id => ['primary' => true]]);
            }
        }

        $event->reservations()->syncWithoutDetaching([$user->id]);

        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true, 'created' => true, 'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
        ]]);
    }

    private function normalizePhone(string $input): ?array
    {
        $input = trim($input);
        try {
            $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
            $region = null;
            // If doesn’t start with +, use default region (SG by default)
            if (!str_starts_with($input, '+')) {
                $region = config('app.phone_region', 'SG');
            }
            $proto = $phoneUtil->parse($input, $region);
            if (!$phoneUtil->isValidNumber($proto)) {
                return null;
            }
            $e164 = $phoneUtil->format($proto, \libphonenumber\PhoneNumberFormat::E164);
            return ['e164' => $e164, 'raw' => $input];
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function broadcastEventUpdate(Event $event): void
    {
        // Refresh and broadcast current snapshots for the three lists
        $reserved = $event->reservations()->select('users.id', 'users.name', 'users.email')->get()->toArray();
        $attendees = $event->attendees()->select('users.id', 'users.name', 'users.email')->get()->toArray();
        $inside = $event->inside()->select('users.id', 'users.name', 'users.email')->get()->toArray();

        // Best-effort broadcasting: do not let websocket misconfiguration break the request
        try {
            event(new EventUpdated($event->id, $reserved, $attendees, $inside));
        } catch (\Throwable $e) {
            // Log/report but continue
            if (function_exists('report')) {
                report($e);
            }
        }
    }

    // ----- Removal handlers -----
    public function removeAttendee(Event $event, User $user)
    {
        // When removing from attendees, also remove from inside
        $event->attendees()->detach($user->id);
        $event->inside()->detach($user->id);
        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true]);
    }

    public function removeReservation(Event $event, User $user)
    {
        // When removing from reserved, also remove from attendees and inside
        $event->reservations()->detach($user->id);
        $event->attendees()->detach($user->id);
        $event->inside()->detach($user->id);
        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true]);
    }

    public function removeInside(Event $event, User $user)
    {
        $event->inside()->detach($user->id);
        // Log admin-initiated removal as an 'out' action
        EventLog::create(['event_id' => $event->id, 'user_id' => $user->id, 'action' => 'out']);
        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true]);
    }

    // ----- Copy from reserved to attendees -----
    public function copyReservationToAttendee(Event $event, User $user)
    {
        // Attach user to attendees without removing them from reservations
        $event->attendees()->syncWithoutDetaching([$user->id]);
        // Log admin-initiated attendee addition
        EventLog::create(['event_id' => $event->id, 'user_id' => $user->id, 'action' => 'attend']);
        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true]);
    }

    // ----- Copy from attendees to inside -----
    public function copyAttendeeToInside(Event $event, User $user)
    {
        // Attach user to inside without removing them from attendees
        $event->inside()->syncWithoutDetaching([$user->id]);
        // Log admin-initiated check-in
        EventLog::create(['event_id' => $event->id, 'user_id' => $user->id, 'action' => 'in']);
        $this->broadcastEventUpdate($event);
        return response()->json(['ok' => true]);
    }

    // ----- Force time controls -----
    public function forceSignupBegin(Event $event)
    {
        $event->signup_start = now();
        // If signup_needed is false, enabling makes sense when forcing begin
        if ($event->signup_needed === false) {
            $event->signup_needed = true;
        }
        $event->save();
        return response()->json(['ok' => true, 'signup_start' => $event->signup_start?->toIso8601String()]);
    }

    public function forceSignupEnd(Event $event)
    {
        $event->signup_end = now();
        // Ensure signup_needed true if forcing window end
        if ($event->signup_needed === false) {
            $event->signup_needed = true;
        }
        $event->save();
        return response()->json(['ok' => true, 'signup_end' => $event->signup_end?->toIso8601String()]);
    }

    public function forceEventBegin(Event $event)
    {
        $event->event_start = now();
        $event->save();
        return response()->json(['ok' => true, 'event_start' => $event->event_start?->toIso8601String()]);
    }

    public function forceEventEnd(Event $event)
    {
        $event->event_end = now();
        $event->save();
        return response()->json(['ok' => true, 'event_end' => $event->event_end?->toIso8601String()]);
    }

    public function scanner(Request $request,Event $event)
    {
        if ($request->isMethod('post')) {
            $data = $request->validate([
                'code' => ['required','string']
            ]);

            try {
                $decoded = $this->decodeScanCode($data['code']);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['ok' => false, 'error' => $e->getMessage()], 422);
            }

            // Resolve or create the user from decoded info
            $user = $this->resolveUserFromDecoded($decoded);
            if (!$user) {
                return response()->json(['ok' => false, 'error' => 'User not found and cannot be created from code.'], 422);
            }

            // Ensure user is marked as attendee
            $event->attendees()->syncWithoutDetaching([$user->id]);

            // Toggle inside presence: if already inside, remove; if not, admit (subject to capacity rules)
            $isInside = $event->inside()->where('users.id', $user->id)->exists();
            $isReserved = $event->reservations()->where('users.id', $user->id)->exists();

            if ($isInside) {
                // Checkout: remove from inside but keep attendee status
                $event->inside()->detach($user->id);
                // Log checkout
                EventLog::create(['event_id' => $event->id, 'user_id' => $user->id, 'action' => 'out']);
                $this->broadcastEventUpdate($event);
                return response()->json([
                    'ok' => true,
                    'message' => 'Checked out successfully.',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'reserved' => $isReserved,
                ]);
            }

            // Not inside yet: capacity check before admitting
            $capacity = $event->number_of_seats; // null or int
            if ($capacity) {
                $insideCount = $event->inside()->count();
                $reservationsCount = $event->reservations()->count();
                // how many reserved are already inside
                $reservedInsideCount = $event->inside()
                    ->whereIn('users.id', $event->reservations()->select('users.id'))
                    ->count();
                $reservationsNotInside = max(0, $reservationsCount - $reservedInsideCount);

                // For non-reserved walk-ins, ensure we keep room for reservations not yet inside
                $protected = $insideCount + $reservationsNotInside;
                if (!$isReserved && $protected >= $capacity) {
                    return response()->json([
                        'ok' => false,
                        'error' => 'No seats left (reserved seats are being held).'
                    ], 422);
                }
            }

            // Admit inside
            $event->inside()->syncWithoutDetaching([$user->id]);
            // Log check-in
            EventLog::create(['event_id' => $event->id, 'user_id' => $user->id, 'action' => 'in']);

            $this->broadcastEventUpdate($event);
            return response()->json([
                'ok' => true,
                'message' => 'Checked in successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'reserved' => $isReserved,
            ]);
        }
        return Inertia::render('Admin/Event/Scanner', [
            'event' => $event,
        ]);
    }

    public function userLogsForEvent(Event $event, User $user)
    {
        $logs = EventLog::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'asc')
            ->get(['id', 'action', 'created_at']);

        return response()->json([
            'ok' => true,
            'logs' => $logs->map(function ($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'at' => $log->created_at?->toIso8601String(),
                ];
            }),
        ]);
    }

    /**
     * Decode a scanned code into structured identity data.
     * Supports:
     * - base64 JSON: {"user_id":123}|{"email":"a@b.com"}|{"phone":"+659..."}
     * - plain numeric user id
     * - email string
     * - phone number string
     */
    private function decodeScanCode(string $code): array
    {
        $code = trim($code);
        if ($code === '') {
            throw new \InvalidArgumentException('Empty code.');
        }

        // 1) Try Laravel-encrypted payload directly (e.g., base64 string that decrypt() understands)
        try {
            // First try decrypting string tokens (Crypt::encryptString)
            try {
                $plain = Crypt::decryptString($code);
            } catch (\Throwable $e) {
                // Fallback to legacy decrypt (Crypt::decrypt)
                $plain = decrypt($code);
            }
            if (is_string($plain) && $plain !== '') {
                $json = json_decode($plain, true);
                if (is_array($json)) {
                    return $json;
                }
                if (ctype_digit($plain)) {
                    return ['user_id' => (int) $plain];
                }
                if (filter_var($plain, FILTER_VALIDATE_EMAIL)) {
                    return ['email' => $plain];
                }
                // Treat any other plaintext as phone or opaque id
                return ctype_digit(str_replace([' ', '+', '-', '(', ')'], '', $plain))
                    ? ['phone' => $plain]
                    : ['raw' => $plain];
            }
        } catch (\Throwable $e) {
            // Not a decryptable string, continue
        }

        // 2) Try base64 JSON (may include Laravel payload structure or direct identity JSON)
        if (preg_match('/^[A-Za-z0-9+\/=]+$/', $code)) {
            $decoded = base64_decode($code, true);
            if ($decoded !== false && $decoded !== '') {
                $json = json_decode($decoded, true);
                if (is_array($json)) {
                    // If it looks like Laravel-encrypted payload, attempt decrypt using original $code
                    if (array_key_exists('iv', $json) && array_key_exists('value', $json) && array_key_exists('mac', $json)) {
                        try {
                            try {
                                $plain = Crypt::decryptString($code);
                            } catch (\Throwable $e2) {
                                $plain = decrypt($code);
                            }
                            if (is_string($plain) && $plain !== '') {
                                $jsonPlain = json_decode($plain, true);
                                if (is_array($jsonPlain)) {
                                    return $jsonPlain;
                                }
                                if (ctype_digit($plain)) {
                                    return ['user_id' => (int) $plain];
                                }
                                if (filter_var($plain, FILTER_VALIDATE_EMAIL)) {
                                    return ['email' => $plain];
                                }
                                return ctype_digit(str_replace([' ', '+', '-', '(', ')'], '', $plain))
                                    ? ['phone' => $plain]
                                    : ['raw' => $plain];
                            }
                        } catch (\Throwable $e) {
                            // fallthrough
                        }
                    }
                    // Otherwise assume the base64 contained identity JSON directly
                    return $json;
                }
            }
        }

        // 3) Numeric id as plain text
        if (ctype_digit($code)) {
            return ['user_id' => (int) $code];
        }

        // 4) Email as plain text
        if (filter_var($code, FILTER_VALIDATE_EMAIL)) {
            return ['email' => $code];
        }

        // 5) Fallback treat as phone or raw
        return ctype_digit(str_replace([' ', '+', '-', '(', ')'], '', $code))
            ? ['phone' => $code]
            : ['raw' => $code];
    }

    /**
     * Resolve a user record from decoded data. May create minimal record if needed.
     */
    private function resolveUserFromDecoded(array $data): ?User
    {
        // Support common aliases and nested structures
        $id = $data['user_id'] ?? $data['id'] ?? ($data['user']['id'] ?? null);
        $email = $data['email'] ?? ($data['user']['email'] ?? null);
        $phone = $data['phone'] ?? ($data['user']['phone'] ?? null);
        $name = $data['name'] ?? ($data['user']['name'] ?? null);

        // By id
        if (!empty($id) && is_numeric($id)) {
            $found = User::find((int) $id);
            if ($found) return $found;
        }

        // By email
        if (!empty($email)) {
            $email = trim((string) $email);
            $user = User::where('email', $email)->first();
            if ($user) return $user;

            // Create minimal user if not found
            $derivedName = $name ? (string) $name : (function ($e) {
                $local = strstr($e, '@', true) ?: $e;
                $local = trim(preg_replace('/[^A-Za-z0-9 _.-]+/', ' ', $local)) ?: 'Guest';
                return ucfirst($local);
            })($email);

            return User::create([
                'name' => $derivedName,
                'email' => $email,
                'password' => bcrypt(Str::random(16)),
            ]);
        }

        // By phone
        if (!empty($phone)) {
            $normalized = $this->normalizePhone((string) $phone);
            if (!$normalized) return null;

            $pn = PhoneNumber::where('e164', $normalized['e164'])->first();
            if ($pn) {
                $user = $pn->users()->first();
                if ($user) return $user;
            }

            // Create minimal user if not found
            $derivedName = $name ? (string) $name : ('Guest '.substr(preg_replace('/\D+/', '', $normalized['e164']), -4));
            $placeholderEmail = 'user+'.preg_replace('/\D+/', '', $normalized['e164']).'@example.invalid';

            $user = User::create([
                'name' => $derivedName,
                'email' => $placeholderEmail,
                'password' => bcrypt(Str::random(16)),
            ]);

            $pn = PhoneNumber::firstOrCreate(['e164' => $normalized['e164']], ['raw' => $normalized['raw']]);
            $user->phoneNumbers()->syncWithoutDetaching([$pn->id => ['primary' => true]]);
            return $user;
        }

        return null;
    }
}
