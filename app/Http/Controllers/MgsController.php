<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class MgsController extends Controller
{
    public function index(Request $request)
    {
        $sort   = $request->input('sort', 'new');
        $query  = Video::where('store', 'mgs');

        $query = match ($sort) {
            'popular' => $query->orderByDesc('click_count'),
            default   => $query->orderByDesc('id'),
        };

        $videos     = $query->paginate(20)->withQueryString();
        $totalCount = Video::where('store', 'mgs')->count();

        return view('mgs.index', compact('videos', 'sort', 'totalCount'));
    }
}
