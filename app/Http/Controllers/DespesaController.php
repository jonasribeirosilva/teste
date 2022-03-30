<?php

namespace App\Http\Controllers;

use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use App\Http\Requests\{StoreDespesaRequest, UpdateDespesaRequest};
use App\Models\Despesa;
use App\Models\User;
use App\Notifications\NovaDespesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class DespesaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::get(['id', 'name','email']);
        return view('despesas.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::get(['id', 'name','email']);
        return view('despesas.form', ['isNew' => true, 'users' => $users, 'despesa' => new Despesa()]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Despesa  $despesa
     * @return \Illuminate\Http\Response
     */
    public function edit(Despesa $despesa)
    {
        $users = User::get(['id', 'name','email']);
        // $tokenBasic = base64_encode($users . ":" . );
        return view('despesas.form', ['isNew' => false, 'users' => $users, 'despesa' => $despesa]);
    }

    /**
     * Lista todas as despesas
     *
     * @param ListDespesaRequest $request
     * @return \Illuminate\Http\Response
     */
    public function list(Request $request)
    {
        $userId = (int)$request->query('user', 0);
        $page = (int)$request->query('page', 0);

        try {
            $query = Despesa::query();

            if ($userId > 0) {
                $query->where('usuario', $userId);
            }

            $query->join('users', 'despesas.usuario', '=', 'users.id');

            $countTotal = $query->count();

            $query->select('despesas.*', 'users.id as user_id', 'users.name as user_name');

            $despesas = $query->offset($page * 50)->limit(50)->get();

            if (!$despesas) {
                throw new \Exception("Error Processing Request", 2);
            }

            return response()->json([
                'status' => 'success',
                'numResults' => $despesas->count(),
                'numResultsTotals' => $countTotal,
                'data' => $despesas->all()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreDespesaRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreDespesaRequest $request)
    {
        try {
            $validator = $request->validated();
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                "errors" => $e->errors()
            ], 400);
        }

        try {
            $despesa            = new Despesa();
            $despesa->descricao = $request->input('descricao');
            $despesa->usuario   = $request->input('usuario');
            $despesa->data      = $request->input('data');
            $despesa->valor     = $request->input('valor');
            $despesa->save();

            $user = User::find($despesa->usuario);

            Notification::route('mail', $user->email)->notify(new NovaDespesa($despesa));

            return response()->json([
                'status' => 'success',
                'despesa' => $despesa->toArray()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateDespesaRequest  $request
     * @param  \App\Models\Despesa  $despesa
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateDespesaRequest $request, Despesa $despesa)
    {
        try {
            $validator = $request->validated();
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                "errors" => $e->errors()
            ], 400);
        }

        try {
            if ($request->has('descricao')) {
                $despesa->descricao = $request->input('descricao');
            }
            if ($request->has('usuario')) {
                $despesa->usuario = $request->input('usuario');
            }
            if ($request->has('data')) {
                $despesa->data = $request->input('data');
            }
            if ($request->has('valor')) {
                $despesa->valor = $request->input('valor');
            }
            $despesa->update();
            return response()->json([
                'status' => 'success',
                'despesa' => $despesa->toArray()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Despesa  $despesa
     * @return \Illuminate\Http\Response
     */
    public function destroy(Despesa $despesa)
    {
        try {
            $despesa->delete();
            return response()->json([
                'status' => 'success'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => 'error',
                'error' => $th->getMessage(),
                'code' => $th->getCode()
            ], 400);
        }
    }
}
