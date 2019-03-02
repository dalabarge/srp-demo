<?php

declare(strict_types=1);

namespace App\User\Controllers;

use App\Http\Controllers\Controller;
use App\User\Auth\SRP;
use App\User\Model;
use Illuminate\Http\Request;

class Login extends Controller
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
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        return view('user.login')
            ->with('email', $request->old('email', session('email', $request->email)));
    }

    /**
     * Create and store a challenge for the user by identifier.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $user = $this->find($request->email);

        $challenge = $this->srp->load($user->email)
            ->challenge($user->email, $user->verifier, $user->salt);

        return response()->json([
            'identifier' => $user->email,
            'challenge'  => $challenge,
        ]);
    }

    /**
     * Verify the proof of password and authenticate the user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request)
    {
        $user = $this->find($request->email);

        $proof = $this->srp->load($user->email)
            ->verify($request->key, $request->proof);

        auth()->login($user);

        return response()->json([
            'identifier' => $user->email,
            'proof'      => $proof,
        ]);
    }

    /**
     * Store a challenge for the user matching the email identifier.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        auth()->logout();

        return redirect()->route('user.login')
            ->with('message', 'You have been logged out.');
    }

    /**
     * Find a user by the email identifier.
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
