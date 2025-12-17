<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestOrderController extends Controller
{
    public function testStore(Request $request)
    {
        // Réponse simple pour tester
        return response()->json([
            'success' => true,
            'message' => 'Test réussi!',
            'data' => $request->all(),
            'test' => 'Ça fonctionne!'
        ]);
    }
}