<?php

declare(strict_types=1);

namespace App\User\Auth;

use App\Http\Controller as BaseController;
use App\User\Auth\Requests\Show;
use App\User\Auth\Requests\Store;
use App\User\Auth\Requests\Update;
use App\User\Model;
use ArtisanSdk\SRP\Exceptions\InvalidKey;
use ArtisanSdk\SRP\Exceptions\PasswordMismatch;
use ArtisanSdk\SRP\Exceptions\StepReplay;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    /**
     * The user model.
     *
     * @param \App\User\Model $model
     */
    protected $model;

    /**
     * The SRP server.
     *
     * @var \ArtisanSdk\SRP\Contracts\Server
     */
    protected $srp;

    /**
     * Inject the dependencies.
     *
     * @param \App\User\Model    $model
     * @param \App\User\Auth\SRP $srp
     */
    public function __construct(Model $model, SRP $srp)
    {
        $this->model = $model;
        $this->srp = $srp;
    }

    /**
     * Show the login form.
     *
     * @param \App\User\Auth\Requests\Show $request
     *
     * @return \Illuminate\View\View
     */
    public function show(Show $request)
    {
        return view('user.login')
            ->with('email', $request->old('email', session('email', $request->email)));
    }

    /**
     * Create and store a challenge for the user by identity.
     *
     * @param \App\User\Auth\Requests\Store $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Store $request)
    {
        try {
            $user = $this->find($request->email);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'The email address is not yet registered.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $key = $this->srp->load($user->email)
                ->challenge($user->email, $user->verifier, $user->salt);
        } catch (StepReplay $exception) {
            return response()->json([
                'message' => 'Please login in again, a little faster this time.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'salt' => $user->salt,
            'key'  => $key,
        ]);
    }

    /**
     * Verify the proof of password and authenticate the user.
     *
     * @param \App\User\Auth\Requests\Update $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Update $request)
    {
        try {
            $user = $this->find($request->email);
        } catch (ModelNotFoundException $exception) {
            return response()->json([
                'message' => 'The email address is not yet registered.',
            ], Response::HTTP_NOT_FOUND);
        }

        try {
            $proof = $this->srp->load($user->email)
                ->verify($request->key, $request->proof);
        } catch (InvalidKey $exception) {
            return response()->json([
                'errors'  => ['key' => $exception->getMessage()],
                'message' => 'This client is not configured correctly.',
            ], Response::HTTP_BAD_REQUEST);
        } catch (PasswordMismatch $exception) {
            return response()->json([
                'message' => 'The password does not match. Please try again.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $this->srp->reset();

        auth()->login($user);

        return response()->json([
            'proof' => $proof,
        ]);
    }

    /**
     * Store a challenge for the user matching the email identity.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete()
    {
        auth()->logout();

        return redirect()->route('user.login')
            ->with('message', 'You have been logged out.');
    }

    /**
     * Find a user by the email identity.
     *
     * @param string $email
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     *
     * @return \App\User\Model
     */
    protected function find(string $email): Model
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->firstOrFail();
    }
}
