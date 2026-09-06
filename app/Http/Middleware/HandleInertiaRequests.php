<?php

namespace App\Http\Middleware;

use App\Enums\ContentLanguage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
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
        $translations = lang_path(app()->getLocale().'.json');
        $user = Auth::guard(User::AUTH_GUARD)->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => app()->getLocale(),
            'locales' => config('app.supported_locales'),
            'contentLanguages' => collect(ContentLanguage::cases())
                ->mapWithKeys(fn (ContentLanguage $language) => [$language->value => $language->label()]),
            'translations' => is_file($translations)
                ? File::json($translations)
                : [],
            'auth' => [
                'user' => $user,
                'has_author_profile' => $user?->authorProfile()->exists() ?? false,
                'has_beta_reader_profile' => $user?->betaReaderProfile()->exists() ?? false,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
