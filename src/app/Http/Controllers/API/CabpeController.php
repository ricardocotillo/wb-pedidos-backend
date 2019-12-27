<?php

namespace App\Http\Controllers\API;

use App\Models\Cabpe;
use App\Models\Detpe;
use App\Models\Ccmsedo;
use App\Models\Ccmcli;
use App\Models\Articulo;
use App\Models\Famdfa;
use App\Models\Ccmcpa;
use App\Mail\PedidoProcesado;
use Illuminate\Support\Facades\Mail;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CabpeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        $data = Cabpe::all();
        return response()->json($data, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cabeceras = $request->input('cabeceras');
        $articulos = array();
        $montoTotalFinal = 0;

        function nroped($numero)
        {
            $n = (int) $numero + 1;
            $n = (string) $n;
            while (strlen($n) < 6) {
                $n = '0' . $n;
            }
            return $n;
        }

        foreach ($cabeceras as $key => $cabe) {
            $articulos[$key] = array();
            $ccmsedo = Ccmsedo::orderBy('id', 'desc')->first();
            $cabpe = Cabpe::orderBy('id', 'desc')->first();
            $ccmcli = Ccmcli::where('MCODCLI', '=', $cabe['MCODCLI'])->first();
            $mtopventa = 0.0;
            $mdcto = 0.0;
            foreach ($cabe['pedidos'] as $pedido) {
                if ($pedido['mcoddfa'] == 'Bono') {
                    continue;
                } else {
                    $mtopventa = $mtopventa + ($pedido['cantidad'] * round($pedido['precio'] * 1.18, 2));
                }
                if ($pedido['mcoddfa'] != 'Sin descuento' && $pedido['mcoddfa'] != 'Bono') {
                    $mdcto = $mdcto + ($pedido['cantidad'] * round($pedido['precio'] * 1.18, 2) * ($pedido['mpordfa'] / 100));
                }
            }
            $mneto = $mtopventa - $mdcto;
            $migv = $mneto - ($mneto / 1.18);
            $mvalven = $mtopventa - $migv;
            $montoTotalFinal = $montoTotalFinal + $mneto;

            $mnroped = nroped($cabpe['MNROPED']);
            $cabecera = array(
                'MTIPODOC' => $ccmsedo['MTIPODOC'],
                'MNSERIE' => $ccmsedo['MNSERIE'],
                'MNROPED' => $mnroped,
                'MCODTPED' => '01',
                'MFECEMI' => date('Y-m-d'),
                'MPERIODO' => date('Ym'),
                'MCODCLI' => $cabe['MCODCLI'],
                'MCODCADI' => $ccmcli['MCODCADI'],
                'MCODCPA' => $cabe['MCONDPAGO'],
                'MCODVEN' => $cabe['MCODVEN'],
                'MCODZON' => $ccmcli['MCODZON'],
                'MCODMON' => '001',
                'MDOLINT' => 'S',
                'MFECENT' => date('Y-m-d'),
                'MLUGENT' => $ccmcli['MDIRDESP'],
                'MLOCALID' => $ccmcli['MLOCALID'],
                'MVALVEN' => round($mvalven, 2),
                'MDCTO' => round($mdcto, 2),
                'MIGV' => round($migv, 2),
                'MTOPVENTA' => round($mtopventa, 2),
                'MNETO' => round($mneto, 2),
                'MSALDO' => round($mneto, 2),
                'MPORIGV' => 18.00,
                'MINDORIG' => 1,
                'MIND_N_I' => 1,
                'MINDAPROB' => 'S',
                'MINDIMP' => 'N',
                'MINC_IGV' => 'S',
                'MANO_E' => date('Y'),
                'MMES_E' => date('m'),
                'MDIA_E' => date('d'),
                'MTEND' => 0,
                'MNORDCLI' => $ccmcli['MUBIGEO'],
                'MAMD' => date('Ymd'),
                'MINDFACT' => 'N',
                'MCODLPRE' => '03',
                'MNOMCLI' => $ccmcli['MNOMBRE'],
                'MLUGFAC' => $ccmcli['MDIRECC'],
                'MCODSITD' => '04',
                'MFECUACT' => date('Y-m-d'),
                'MCODUSER' => '600000000000001',
                'MHORAUACT' => date('h:i:s'),
                'MPESOKG' => 0.0,
                'MATEND' => 0,
                'estado' => $cabe['descuentoExtra'] ? 'pendiente' : 'procesado',
                'MOBSERV' => ''
            );
            $cab = Cabpe::create($cabecera);
            $cab->save();
            $mitem = 1;
            foreach ($cabe['pedidos'] as $value) {
                $art = Articulo::where('MCODART', '=', $value['mcodart'])->first();

                $mdescrip = $value['mpordfa'] != null ? Famdfa::where('MCODDFA', '=', $value['mcoddfa'])->select('MDESCRIP')->first()['MDESCRIP'] : null;

                $mprecio = round($value['precio'] * 1.18, 2);
                $mpordct1 = $value['mpordfa'] != null ? $value['mpordfa'] : 0.000;
                $mvalven = round($mprecio * $value['cantidad'], 2);
                $mdcto = round($mvalven * ($mpordct1 / 100), 2);
                $migv = round(($mvalven - $mdcto) - (($mvalven - $mdcto) / 1.18), 2);
                $mdetped = array(
                    'MTIPODOC' => 'C',
                    'MNSERIE' => $ccmsedo['MNSERIE'],
                    'MNROPED' => '0' . (string) ((int) $cabpe['MNROPED'] + 1),
                    'MITEM' => (string) $mitem,
                    'MCODART' => $value['mcodart'],
                    'MCANTIDAD' => (float) $value['cantidad'],
                    'MCANTPEN' => (float) $value['cantidad'],
                    'MUNIDAD' => $art['MUNIDAD'],
                    'MCODUMED' => 22,
                    'MFACTOR' => $art['MUNDENV1'],
                    'MDESCRI01' => $art['MDESCRIP'],
                    'MPORDCT1' => $mpordct1,
                    'MPORDCT2' => 0.0,
                    'MDCTOPRD' => $mdcto,
                    'MDCTO' => $mdcto,
                    'MPRECIO' => $mprecio,
                    'MVALVEN' => $mvalven,
                    'MIGV' => $migv,
                    'MCOSULCO' => 0.00,
                    'MINDORIG' => 1,
                    'MAMD' => date('Ymd'),
                    'MAFE_IGV' => 'S',
                    'MINDOBSQ' => $value['mcoddfa'] == 'Bono' ? 'D' : 'N',
                    'MCODUSER' => '600000000000001',
                    'MFECUACT' => date('Y-m-d'),
                    'MHORUACT' => date('h:i:s'),
                    'MPENFAC' => (float) $value['cantidad'],
                    'MCODDFA' => $value['mcoddfa'],
                );
                $det = Detpe::create($mdetped);
                $det->save();
                $mitem = $mitem + 1;
                $det['MDESCRIP'] = $mdescrip;
                array_push($articulos[$key], $det);
            }
        }
        info($request->input('enviarCorreo'));
        
        if ($request->input('enviarCorreo')) {
            $data = array('nombre' => $ccmcli['MNOMBRE']);


            $ccmcpa = Ccmcpa::where('MCONDPAGO', '=', $request->input('mcondpago'))->first();

            $info = [
                'fecha' => date('d/m/Y'),
                'periodo' => date('Y/m'),   
                'mnroped' => $ccmsedo['MNSERIE'] . '-' . $mnroped,
                'ruc' => $ccmcli['MCODCLI'],
                'cliente' => $ccmcli['MNOMBRE'],
                'canal' => $ccmcli['MCODCADI'],
                'direccion' => $ccmcli['MDIRECC'],
                'email' => $ccmcli['MCORREO'],
                'condicion' => $ccmcpa['MABREVI'],
                'articulos' => $articulos,
                'total' => round($montoTotalFinal, 2),
                'observaciones' => $request->input('observaciones')
            ];

            PDF::setOptions(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true, 'debugPng' => true, 'defaultFont' => 'sans-serif']);
            $document = PDF::loadView('attach.pedido', $info);
            $output = $document->output();

            Mail::send('emails.mail', $data, function ($message) use ($ccmcli, $output) {
                $message->to('rcotillo@cotillo.tech', $ccmcli['MNOMBRE'])->subject('Pedido en proceso');
                $message->from('recep_pedidos@filtroswillybusch.com.pe', 'Filtros Willy Busch');
                $message->attachData($output, 'pedido.pdf');
            });
        }

        return response()->json(201);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cabpe  $cabpe
     * @return \Illuminate\Http\Response
     */
    public function show(Request $req)
    {
        $cabpeds = [];
        foreach ($req->all() as $cod) {
            $cabs = Cabpe::where('MCODVEN', $cod)->select(['MNSERIE', 'MNROPED', 'MFECEMI', 'MCODVEN', 'MCODCLI', 'MTOPVENTA', 'MNOMCLI'])->orderBy('MNSERIE', 'desc')->orderBy('MNROPED', 'desc')->take(5)->get();
            foreach ($cabs as $cab) {
                array_push($cabpeds, $cab);
            }
        }
        return response()->json($cabpeds, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cabpe  $cabpe
     * @return \Illuminate\Http\Response
     */
    public function edit(Cabpe $cabpe)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cabpe  $cabpe
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Cabpe $cabpe)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cabpe  $cabpe
     * @return \Illuminate\Http\Response
     */
    public function destroy(Cabpe $cabpe)
    {
        //
    }
}
