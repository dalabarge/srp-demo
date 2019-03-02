<?php

declare(strict_types=1);

namespace App\User\Controllers;

use App\Http\Controllers\Controller;
use App\User\Model;
use Illuminate\Http\Request;

class Register extends Controller
{
    /**
     * The user model.
     *
     * @param \App\User\Model $model
     */
    protected $model;

    /**
     * Inject the dependencies.
     *
     * @param \App\User\Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Show the register form.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\View\View
     */
    public function show(Request $request)
    {
        return view('user.register')
            ->with('email', $request->old('email', session('email', $request->email)))
            ->with('name', $request->old('name', session('name', $request->name)));
    }

    /**
     * Create a user by the identifier with the stored password verifier and salt.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        if ($this->find($request->email)) {
            return redirect()->route('user.login')
                ->with('error', 'Looks like you\'ve already register. Please sign in to your account.')
                ->with('email', $request->email);
        }

        $user = $this->model->create($request->only('name', 'email', 'verifier', 'salt'));

        return redirect()->route('user.login')
            ->with('message', 'You have been successfully registered.')
            ->with('email', $user->email);
    }

    /**
     * Find the user by the email identifier if it exists.
     *
     * @param string $email
     *
     * @return \App\User\Model
     */
    protected function find(string $email): ?Model
    {
        return $this->model->newQuery()
            ->where('email', $email)
            ->first();
    }
}
