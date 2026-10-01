<?php

namespace App\Http\Controllers;

use App\Actions\AuthorProfiles\CreateAuthorProfile;
use App\Actions\AuthorProfiles\UpdateAuthorProfile;
use App\Http\Requests\StoreAuthorProfileRequest;
use App\Http\Requests\UpdateAuthorProfileRequest;
use App\Models\AuthorProfile;
use App\Models\Book;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class AuthorProfileController extends AuthenticatedUserController
{
    public function create(): Response|RedirectResponse
    {
        if ($this->user()->authorProfile()->exists()) {
            return to_route('author-profile.edit');
        }

        return Inertia::render('author-profiles/Create');
    }

    public function store(
        StoreAuthorProfileRequest $request,
        CreateAuthorProfile $createAuthorProfile,
    ): RedirectResponse {
        if ($this->user()->authorProfile()->exists()) {
            return to_route('author-profile.edit');
        }

        try {
            $createAuthorProfile->handle($this->user(), $request->profileAttributes(), $request->file('avatar'));
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
        $profile = $this->user()->authorProfile;

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        return Inertia::render('author-profiles/Edit', [
            'authorProfile' => $profile->only(['display_name', 'handle', 'bio']) + ['avatar_url' => $profile->avatarUrl()],
        ]);
    }

    public function update(
        UpdateAuthorProfileRequest $request,
        UpdateAuthorProfile $updateAuthorProfile,
    ): RedirectResponse {
        $profile = $this->user()->authorProfile;

        if ($profile === null) {
            return to_route('author-profile.create');
        }

        $updateAuthorProfile->handle($profile, $request->profileAttributes(), $request->file('avatar'), $request->boolean('remove_avatar'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Author profile updated.')]);

        return to_route('dashboard');
    }

    public function show(AuthorProfile $authorProfile): Response
    {
        return Inertia::render('author-profiles/Show', [
            'authorProfile' => $authorProfile->only(['display_name', 'handle', 'bio']) + ['avatar_url' => $authorProfile->avatarUrl()],
            'books' => $authorProfile->books()
                ->latest()
                ->latest('id')
                ->paginate(12)
                ->through(fn (Book $book): array => $book->only(['id', 'title', 'description', 'language', 'genre', 'content_warnings']) + ['cover_image_url' => $book->coverImageUrl()]),
        ]);
    }
}
