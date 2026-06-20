# EZRide — Project Handoff & Overview

> Read this first, then skim the files/schema listed below to understand both projects before making changes.

## Projects
- **Backend (Laravel 12 + Passport):** `/Users/devdimensions/PhpstormProjects/ezride-backend/Ride-App-Backend`
- **Mobile (React Native 0.84):** `/Users/devdimensions/PhpstormProjects/mobile-app/EZRide`

## Architecture & patterns (follow these exactly)
**Backend:** `Route → Controller → Action → Repository → Model`.
- Actions hold business logic inside `DB::transaction()`; **no Eloquent queries in Actions** — always go through Repositories.
- Validation via Form Requests. Responses via API Resources extending `ApiResource` / `ApiResourceCollection` → envelope `{ success, message, data, meta }`.
- ⚠️ `ApiResource` has a `$responseMessage` property (the envelope message). Never name it `$message` (it shadowed model `message` fields — already fixed).
- Pagination: `BaseRepository::paginatedList()` + `config/pagination.php` (default 20, max 100). Resource collections expose `data.meta.{current_page,last_page,total,per_page}`.
- Auth: **Passport** Bearer tokens. Routes grouped under `auth:api`.

**Mobile:** `Screen → hook (TanStack Query v5) → service (axios) → backend`.
- State: **Zustand** (`authStore`, `userStore`, AsyncStorage-persisted). `AppContext` holds `role` ('rider'|'driver').
- Navigation: React Navigation native-stack (`AppNavigator`) + bottom tabs (`MainNavigator`).
- Real-time: **laravel-echo + pusher-js** → Laravel **Reverb**. `services/echo.js` (token-aware), `hooks/useRealtime.js`.
- Lists use `useInfiniteQuery` (browse, history, notifications). Polling only as a fallback when the Reverb socket is disconnected (`useRealtimeConnected`).

## The full city-to-city flow (lifecycle)
1. **Auth:** signup → email OTP verify → login (Passport). `/auth/me`, `/auth/logout`, `/auth/profile` (update).
2. **Driver onboarding** (`POST /driver/onboard`): CNIC/license/vehicle → `user_type='driver'`, `verification_status=pending`.
3. **Driver posts a ride** (`ride_posts`): from/to city, addresses, departure, price_per_seat, seats, `post_type` shared|private.
4. **Rider browses** (infinite scroll, filters from/to/date) and **sends multiple pending booking requests** to different drivers (one *confirmed* ride at a time).
5. **Driver accepts/rejects.** On accept: seats decremented (locked), and the rider's **other pending requests auto-cancel**; when the ride fills, remaining pendings auto-reject.
6. **Driver Start Ride** → post `in_progress` (rider can still cancel — driver may not have arrived).
7. **Driver End Ride** → all accepted bookings → `completed` (+1 trip each), leftover pendings rejected, post closed regardless of empty seats. Driver gets a **review queue** (one sheet per rider).
8. **Rider** sees live status on the browse screen; on completion confirms + reviews the driver.
9. **Auto-close** scheduled command `rides:close-stale` (every 10 min, 2h grace) settles forgotten rides.

**Statuses** — `ride_posts`: `active → full → in_progress → completed` (or `cancelled`). `ride_bookings`: `pending → accepted → completed` / `rejected` / `cancelled`.

## Modules — DONE ✅
- **Auth & profiles** — signup/OTP/login/me/logout, driver onboarding, profile update (name + basic info; driver photo locked).
- **City-to-city ride flow** — post, browse, multi-pending booking, accept/reject, start/end, cancel-anytime-until-completed, re-book after cancel/decline, auto-reject-on-full, auto-close job. All edge cases handled.
- **Reviews** — per-booking, bidirectional; driver received reviews + aggregates (rating_avg, total_trips, review count); `RideDetail` shows real recent trips + reviews (paginated on scroll) + live current-status stepper + seat-based fare.
- **In-app notifications** — fired on book/accept/reject/start/end/cancel + ride alerts; compact FB-style screen (time on right, skeleton loading), tab unread badge, tap → deep-link (RideDetail or Review screen).
- **Ride alerts** — "Notify me" toggle (route + optional date) → notifies on matching new post.
- **Optimizations** — infinite scroll (browse/history/notifications), `ride_bookings` indexes, cities cache, driver requests scoped to active post.
- **Real-time (Reverb)** — live new posts on browse, live booking status, live notifications; socket-down polling fallback. Reverb installed/configured (port 8090).
- **UX** — profile edit, role-aware footer (rider 🔍 / driver ➕), home search → filter, review-from-notification screen, reusable `Skeleton` component, AM/PM times, back headers.

## DB schema — active tables
`users`, `user_profiles`, `driver_profiles`, `vehicles`, `vehicle_makes`, `vehicle_models`, `cities`,
`ride_posts`, `ride_bookings`, `ratings` (polymorphic, `type='ride'`, `rated_as` driver|passenger),
`notifications` (generic: type + data JSON + read_at), `ride_alerts`, `email_otps`, `oauth_*` (Passport).
Indexes: `ride_posts(from,to,departure,status)`, `ride_bookings(passenger_id,status)` + `(ride_post_id,status)`, `ride_alerts(from,to,is_active)`, `notifications(user_id,read_at)`.
**Legacy/unused (ignore or drop):** `ride_requests`, `ride_offers`, `trips`, `user_locations`, `payments`, `subscription_plans`, `user_subscriptions`, `chat_rooms`.

## Key files to review
**Backend:** `routes/api.php`, `routes/api/{auth,driver,ride,vehicle,notification}.php`, `routes/channels.php`, `routes/console.php`;
`app/Actions/Ride/{BookingAction,RideAlertAction}.php`, `app/Actions/Driver/{RidePostAction,DriverOnboardingAction,DriverPublicAction}.php`, `app/Actions/Notification/NotificationAction.php`, `app/Actions/User/UpdateProfileAction.php`, `app/Actions/Auth/*`;
`app/Repositories/**`; `app/Services/Notification/NotificationService.php`; `app/Events/{RidePostCreated,NotificationCreated}.php`;
`app/Http/Resources/Api/V1/**` (esp. `ApiResource`, `ApiResourceCollection`, `RidePostResource`, `RideBookingResource`, `NotificationResource`);
`app/Constants/ResourceFields.php`, `config/pagination.php`, `config/reverb.php`, `database/migrations/**`.
**Mobile:** `src/navigation/{AppNavigator,MainNavigator}.jsx`; `src/services/{api,rideService,authService,notificationService,rideAlertService,echo}.js`;
`src/hooks/*` (useAvailableRides, useMyBookings, useDriverBookings, useRidePosts, useReview, useRideDetail, useRideHistory, useNotifications, useRideAlerts, useRealtime, useUpdateProfile, useMe);
`src/screens/{auth,user,driver,notifications,settings}/*`; `src/store/{authStore,userStore}.js`; `src/context/AppContext.jsx`; `src/components/{ReviewSheet,Skeleton,BottomSheet,SelectSheet}.jsx`; `src/config.js`.

## PENDING / deferred
- **FCM push** (notifications when app is closed) — needs a Firebase project (`google-services.json` + service-account JSON). Reverb already covers app-open real-time.
- **Production Reverb** — `wss://` + supervisor + nginx; at scale switch events `ShouldBroadcastNow` → `ShouldBroadcast` + a `queue:work` worker.
- **Queue the ride-alert fan-out** for very hot routes.
- **EditProfile** — email/phone change + driver photo re-verification flow (currently locked).
- **Not tracked / static:** vehicle "Verified" flag, "Completion %" stat.
- **Out of scope / not built:** Marketplace, Chat, Payments/TopUp, Subscriptions, location/`user_locations`.
- Optional: per-route Reverb channels; cleanup migration to drop legacy tables.

## Run / config
- **Backend:** `php artisan migrate`; API `php artisan serve --host=0.0.0.0 --port=8000`; WebSockets `php artisan reverb:start --host=0.0.0.0 --port=8090`; scheduler for `rides:close-stale`; `php artisan optimize:clear` after route/config edits.
- **Mobile:** set `src/config.js` `BASE_URL` + `REVERB_KEY`/`REVERB_PORT` to match backend `.env`; reload Metro. Native deps already linked on Android (`@react-native-firebase` NOT yet added — that's FCM). iOS: `pod install` when building iOS.

## Verified state
Backend boots, 39 `api/v1` routes, all migrations ran. Mobile `src` lints with 0 errors. City-to-city flow + notifications + reviews + history + ride alerts + Reverb all functional end-to-end.
