<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Services\DmmApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExtensionApiController extends Controller
{
    public function store(Request $request, DmmApiService $dmmApi): JsonResponse
    {
        $request->validate([
            'content_id' => 'required|string',
            'likes' => 'required|integer|min:0',
            'retweets' => 'nullable|integer|min:0',
        ]);

        $rawContentId = $request->input('content_id');

        // 品番でDB検索（柔軟なマッチング）
        $video = $this->findVideo($rawContentId);

        // 見つからなければDMM APIから自動取得
        if (!$video && $dmmApi->isConfigured()) {
            $dmmApi->importVideos($rawContentId, 5);
            $video = $this->findVideo($rawContentId);
        }

        if (!$video) {
            return response()->json([
                'error' => "品番「{$rawContentId}」が見つかりません。DMM APIキーが未設定か、該当する作品がありません。",
                'content_id' => $rawContentId,
            ], 404);
        }

        // いいね数を更新
        $video->update([
            'total_likes' => (int) $request->input('likes'),
            'total_retweets' => (int) $request->input('retweets', 0),
        ]);

        return response()->json([
            'ok' => true,
            'title' => $video->title,
            'likes' => $video->total_likes,
            'content_id' => $video->dmm_content_id,
        ]);
    }

    /**
     * 柔軟な品番マッチング
     * ABC-123 → abc00123, SSIS-001 → ssis00001 のような変換を試みる
     */
    private function findVideo(string $rawContentId): ?Video
    {
        $input = trim($rawContentId);

        // 1. そのまま検索
        $video = Video::where('dmm_content_id', $input)->first();
        if ($video) return $video;

        // 2. 小文字・ハイフン除去で検索
        $normalized = strtolower(str_replace(['-', ' '], '', $input));
        $video = Video::where('dmm_content_id', $normalized)->first();
        if ($video) return $video;

        // 3. 英字部分と数字部分に分解してゼロパディングを試す
        if (preg_match('/^([a-zA-Z_]+)-?0*(\d+)$/', $input, $m)) {
            $label = strtolower($m[1]);
            $num = $m[2];

            // 3〜7桁のゼロパディングを試す
            foreach ([3, 4, 5, 6, 7] as $pad) {
                if ($pad < strlen($num)) continue;
                $candidate = $label . str_pad($num, $pad, '0', STR_PAD_LEFT);
                $video = Video::where('dmm_content_id', $candidate)->first();
                if ($video) return $video;
            }
        }

        // 4. LIKE検索（最終手段）
        $video = Video::where('dmm_content_id', 'like', '%' . $normalized . '%')->first();
        if ($video) return $video;

        return null;
    }
}
