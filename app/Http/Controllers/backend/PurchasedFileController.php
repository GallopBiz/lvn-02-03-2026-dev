<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class PurchasedFileController extends Controller
{
    public function index()
    {
    	$path = public_path('for_pro_members.zip');
    	$fileName = 'purchase_files.zip';

    	return Response::download($path, $fileName, ['Content-Type: application/zip']);
    }
}