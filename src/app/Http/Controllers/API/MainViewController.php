<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Articulo;
use App\Models\Ccmcpa;
use App\Models\Ccmcli;
use App\Models\Ccmven;
use App\Models\Ccmzon;
use App\Http\Controllers\Controller;

class MainViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($nombre)
    {
        $productos = Articulo::select('MCODART', 'MDESCRIP', 'MUNIDAD', 'MPROCEDE', 'MPVTAS05', 'MPVTAS06', 'MPVTAS07', 'MPVTAS08', 'MPVTAS09', 'MPVTAS10')->get();
        $clientes = Ccmcli::select('MCODCLI', 'MCODCADI', 'MCODSCADI', 'MCODVEN', 'MDIRDESP', 'MDIRECC', 'MLOCALID', 'MNOMBRE', 'MTELEF1', 'MUBIGEO', 'MCORREO', 'MCODZON')->get();
        $formasDePago = Ccmcpa::select('MCONDPAGO', 'MDESCRIP', 'MABREVI', 'MINDCRED', 'MDIAS', 'MTIPCRE')->get();
        $vendedores = Ccmven::where('MNOMBRE', '=', urldecode($nombre))->get();

        foreach ($clientes as $cliente) {
            $ccmzon = Ccmzon::where('MCODZON', $cliente['MCODZON'])->first();
            $cliente['MCODRVE'] = $ccmzon['MCODRVE'];
        }

        $codigos = [];

        foreach ($vendedores as $vendedor) {
            array_push($codigos, $vendedor['MCODVEN']);
        }

        $data = [
            'productos' => $productos,
            'clientes' => $clientes,
            'formasDePago' => $formasDePago,
            'codigos' => $codigos,
        ];

        return response()->json($data, 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
