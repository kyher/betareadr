<?php

namespace App\Http\Controllers;

use App\Actions\BetaReaderProfiles\CreateBetaReaderProfile;
use App\Actions\BetaReaderProfiles\UpdateBetaReaderProfile;
use App\Http\Requests\StoreBetaReaderProfileRequest;
use App\Http\Requests\UpdateBetaReaderProfileRequest;
use App\Models\BetaReaderProfile;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class BetaReaderProfileController extends Controller
{
    public function create(): Response|RedirectResponse
    {
        if (request()->user()->betaReaderProfile()->exists()) {
            return to_route('beta-reader-profile.edit');
        }

        return Inertia::render('beta-reader-profiles/Create');
    }

    public function store(
        StoreBetaReaderProfileRequest $request,
        CreateBetaReaderProfile $createBetaReaderProfile,
    ): RedirectResponse {
        if ($request->user()->betaReaderProfile()->exists()) {
            return to_route('beta-reader-profile.edit');
        }

        try {
            $createBetaReaderProfile->handle($request->user(), $request->profileAttributes());
        } catch (UniqueConstraintViolationException $exception) {
            if (in_array('user_id', $exception->columns, true)) {
                return to_route('beta-reader-profile.edit');
            }

            throw ValidationException::withMessages([
                'handle' => __('This handle has already been taken.'),
            ]);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta reader profile created.')]);

        return to_route('dashboard');
    }

    public function edit(): Response|RedirectResponse
    {
        $profile = request()->user()->betaReaderProfile;

        if ($profile === null) {
            return to_route('beta-reader-profile.create');
        }

        return Inertia::render('beta-reader-profiles/Edit', [
            'betaReaderProfile' => $profile->only(['display_name', 'handle', 'bio']),
        ]);
    }

    public function update(
        UpdateBetaReaderProfileRequest $request,
        UpdateBetaReaderProfile $updateBetaReaderProfile,
    ): RedirectResponse {
        $profile = $request->user()->betaReaderProfile;

        if ($profile === null) {
            return to_route('beta-reader-profile.create');
        }

        $updateBetaReaderProfile->handle($profile, $request->profileAttributes());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Beta reader profile updated.')]);

        return to_route('dashboard');
    }

    public function show(BetaReaderProfile $betaReaderProfile): Response
    {
        return Inertia::render('beta-reader-profiles/Show', [
            'betaReaderProfile' => $betaReaderProfile->only(['display_name', 'handle', 'bio']),
        ]);
    }
}
