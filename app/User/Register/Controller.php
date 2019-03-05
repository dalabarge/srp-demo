<?php

declare(strict_types=1);

namespace App\User\Register;

use App\Http\Controller as BaseController;
use App\User\Model;
use App\User\Register\Requests\Show;
use App\User\Register\Requests\Store;

class Controller extends BaseController
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
     * @param \App\User\Register\Requests\Show $request
     *
     * @return \Illuminate\View\View
     */
    public function show(Show $request)
    {
        return view('user.register')
            ->with('email', $request->old('email', session('email', $request->email)))
            ->with('name', $request->old('name', session('name', $request->name)));
    }

    /**
     * Create a user by the identifier with the stored password verifier and salt.
     *
     * @param \App\User\Register\Requests\Store $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Store $request)
    {
        $user = $this->model->create($request->only('name', 'email', 'verifier', 'salt'));

        return redirect()->route('user.login')
            ->with('message', 'You have been successfully registered.')
            ->with('email', $user->email);
    }
}
