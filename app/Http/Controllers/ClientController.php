<?php

namespace App\Http\Controllers;

use App\Models\Client;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }
    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255', // Agora opcional
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone1' => 'nullable|string|max:15',
            'phone2' => 'nullable|string|max:15',
            'birth_date' => 'nullable|date',
            'document' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
    
        DB::beginTransaction();
    
        try {
            // Verifica se o cliente já existe pelo documento
            $client = Client::where('document', $request->document)->first();
    
            if (!$client) {
                // Se o cliente não existe, cria um novo
                $client = Client::create($request->all());
                $message = 'Cliente cadastrado com sucesso!';
            } else {
                // Se o cliente já existe, atualiza os dados
                $client->update($request->all());
                $message = 'Cliente atualizado com sucesso!';
            }
    
            // Verifica se há um usuário autenticado
            $user = Auth::user();
            if ($user) {
                // Associa o cliente ao usuário atual, se ainda não estiver associado
                if (!$user->clients()->where('client_id', $client->id)->exists()) {
                    $user->clients()->attach($client->id);
                    $message .= ' E associado ao seu usuário.';
                }
            } else {
                \Log::warning('Tentativa de associar cliente sem usuário autenticado. Client ID: ' . $client->id);
            }
    
            DB::commit();
            return redirect()->route('home')->with('success', __($message));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao cadastrar/atualizar cliente: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Ocorreu um erro ao processar a solicitação.'))->withInput();
        }    
    }

    public function edit($id)
    {
        $client = Client::findOrFail($id);
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, $id)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'nullable|string|max:255,',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone1' => 'nullable|string|max:15',
            'phone2' => 'nullable|string|max:15',
            'birth_date' => 'nullable|date',
            'status' => 'nullable|integer|in:1,2',
            'document' => 'required|string|max:20|unique:clients,document,'.$id,
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $client = Client::findOrFail($id);
        $client->update($request->all());

        return redirect()->route('home')->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy($id)
    {
        $client = Client::with(['charges', 'users'])->findOrFail($id);

        if ($client->charges->count() > 0) {
            return redirect()->route('home')
                ->with('error', __('Cliente não pode ser excluído pois possui cobranças vinculadas.'));
        }

        DB::beginTransaction();

        try {
            // Remove todas as associações com usuários
            $client->users()->detach();

            DB::commit();
            return redirect()->route('home')->with('success', __('Cliente excluído com sucesso!'));
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erro ao excluir cliente: ' . $e->getMessage());
            return redirect()->route('home')->with('error', __('Ocorreu um erro ao tentar excluir o cliente.'));
        }
    }
}
