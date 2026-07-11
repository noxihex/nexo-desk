<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V2\MeResource;
use App\Http\Resources\Api\V2\UserResource;
use App\Http\Resources\Api\V2\EmpresaResource;
use App\Http\Resources\Api\V2\SetorResource;
use App\Http\Resources\Api\V2\CategoriaResource;
use App\Http\Resources\Api\V2\GrupoResource;
use App\Models\Categoria;
use App\Models\Empresa;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\User;
use Illuminate\Http\Request;

class LookupController extends Controller
{
    public function usuarios()
    {
        return UserResource::collection(User::with('roles')->paginate(100));
    }

    public function empresas()
    {
        return EmpresaResource::collection(Empresa::paginate(100));
    }

    public function setores()
    {
        return SetorResource::collection(Setor::paginate(100));
    }

    public function categorias()
    {
        return CategoriaResource::collection(Categoria::paginate(100));
    }

    public function grupos()
    {
        return GrupoResource::collection(Grupo::paginate(100));
    }

    public function me(Request $request)
    {
        return new MeResource($request->user()->load(['roles', 'permissions', 'empresa', 'setor', 'grupo']));
    }
}
