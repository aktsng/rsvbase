<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $managedFacilities = [];
        $currentFacility = null;

        if ($user && $user->role === 'owner') {
            $managedFacilities = $user->facilities()
                ->orderBy('name')
                ->get(['uuid', 'name'])
                ->toArray();

            $currentUuid = $request->session()->get('current_facility_uuid');

            // セッションにない、または不正な場合は最初の施設をデフォルトにする
            if ($currentUuid) {
                $currentFacility = collect($managedFacilities)->firstWhere('uuid', $currentUuid);
            }

            if (!$currentFacility && !empty($managedFacilities)) {
                $currentFacility = $managedFacilities[0];
                $request->session()->put('current_facility_uuid', $currentFacility['uuid']);
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'managed_facilities' => $managedFacilities,
                'current_facility' => $currentFacility,
                'is_stripe_connected' => $user && $user->role === 'owner'
                    ? ($user->stripe_account_status === 'complete')
                    : null,
            ],

            'impersonating' => session()->has('impersonating_admin_id'),

            'ziggy' => fn() => [
                ...(new \Tighten\Ziggy\Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn() => $request->session()->get('message'),
                'success' => fn() => $request->session()->get('success'),
                'error' => fn() => $request->session()->get('error'),
            ],
        ];
    }
}
