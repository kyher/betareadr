<?php

namespace App\Http\Controllers;

use App\Actions\AuthorProfiles\CreateAuthorProfile;
use App\Actions\AuthorProfiles\UpdateAuthorProfile;
use App\Http\Requests\StoreAuthorProfileRequest;
use App\Http\Requests\UpdateAuthorProfileRequest;
use App\Models\AuthorProfile;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthorProfileController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (request()->user()->authorProfile()->exists()) {
            return to_route('author-profile.edit');
        }

        return Inertia::render('author-profiles/Create');
    }

    public function store(
        StoreAuthorProfileRequest $request,
        CreateAuthorProfile $createAuthorProfile,
    ): RedirectResponse {
        if ($request->user()->authorProfile()->exists()) {
            return to_route('author-profile.edit');
        }

        try {
            $createAuthorProfile->handle($request->user(), $request->profileAttributes());
        } catch (UniqueConstraintViolationException $exception) {
            if (in_array('user_id', $exception->columns, true)) {
                return to_route('author-profile.edit');
            }

            throw ValidationException::withMessages([
                'handle' => __('This handle has already been taken.'),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Author profile created.')]);

        return to_route('dashboard');
    }

    public function edit(): Response|RedirectResponse
    {
        $profile = request()->user()->authorProfile;

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        return Inertia::render('author-profiles/Edit', [
            'authorProfile' => $profile->only(['display_name', 'handle', 'bio']),
        ]);
    }

    public function update(
        UpdateAuthorProfileRequest $request,
        UpdateAuthorProfile $updateAuthorProfile,
    ): RedirectResponse {
        $profile = $request->user()->authorProfile;

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        $updateAuthorProfile->handle($profile, $request->profileAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Author profile updated.')]);

        return to_route('dashboard');
    }

    public function show(AuthorProfile $authorProfile): Response
    {
        return Inertia::render('author-profiles/Show', [
            'authorProfile' => $authorProfile->only(['display_name', 'handle', 'bio']),
            'books' => $authorProfile->books()
                ->latest()
                ->latest('id')
                ->paginate(12)
                ->through(fn ($book): array => $book->only(['id', 'title', 'description'])),
        ]);
    }
}
