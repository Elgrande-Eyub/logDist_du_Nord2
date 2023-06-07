<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function getImage($attachement,$type){
        $storagePath = '/storage/attachements/'.$type.'/' . $attachement;
        $link = public_path($storagePath);
        return response()->file($link);

    }
}
