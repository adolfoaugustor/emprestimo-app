<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        // Validação dos dados
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:clients,email',
            'description' => 'nullable|string',
            'address' => 'nullable|string',
            'phone1' => 'nullable|string|max:15',
            'phone2' => 'nullable|string|max:15',
            'birth_date' => 'nullable|date',
            'document' => 'required|string|max:20|unique:clients,document',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Criação do cliente
        Client::create($request->all());

        return redirect()->route('home')->with('success', 'Cliente cadastrado!');
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
            'email' => 'required|email|unique:clients,email,'.$id,
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

        // Atualização do cliente
        $client = Client::findOrFail($id);
        $client->update($request->all());

        return redirect()->route('home')->with('success', 'Cliente atualizado com sucesso!');
    }
}