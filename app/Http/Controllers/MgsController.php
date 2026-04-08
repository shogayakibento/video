<?php

namespace App\Http\Controllers;

use App\Models\MgsVideo;
use Illuminate\Http\Request;

class MgsController extends Controller
{
    public function index(Request $request)
    {
        $sort  = $request->input('sort', 'new');
        $query = MgsVideo::query();

        $query = match ($sort) {
            'popular' => $query->orderBy('mgs_rank'),
            default   => $query->orderByDesc('release_date'),
        };

        $videos     = $query->paginate(20)->withQueryString();
        $totalCount = MgsVideo::count();

        return view('mgs.index', compact('videos', 'sort', 'totalCount'));
    }

    public function show(string $productCode)
    {
        $video = MgsVideo::where('product_code', $productCode)->firstOrFail();

        // view count tracking removed (click_count replaced by mgs_rank)

        // 同じ女優の他作品（最大12件）
        $related = collect();
        if ($video->actress) {
            $firstActress = trim(preg_split('/[,\s]+/u', $video->actress)[0]);
            $related = MgsVideo::where('product_code', '!=', $productCode)
                ->where('actress', 'like', "%{$firstActress}%")
                ->orderByDesc('release_date')
                ->limit(12)
                ->get();
        }

        return view('mgs.show', compact('video', 'related'));
    }
}
