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

    public function show(string $productCode)
    {
        $video = Video::where('store', 'mgs')
            ->where('dmm_content_id', $productCode)
            ->firstOrFail();

        // 同じ女優の他作品（最大12件）
        $related = collect();
        if ($video->actress) {
            // 女優名で部分一致検索（複数女優がいる場合も対応）
            $firstActress = explode(',', $video->actress)[0];
            $firstActress = trim($firstActress);
            $related = Video::where('store', 'mgs')
                ->where('dmm_content_id', '!=', $productCode)
                ->where('actress', 'like', "%{$firstActress}%")
                ->orderByDesc('id')
                ->limit(12)
                ->get();
        }

        return view('mgs.show', compact('video', 'related'));
    }
}
