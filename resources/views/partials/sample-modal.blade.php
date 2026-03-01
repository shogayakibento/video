<div id="sampleModal" class="sample-modal-overlay" aria-hidden="true" data-affiliate-id="{{ config('fanza.affiliate_id') }}">
    <div class="sample-modal" role="dialog" aria-modal="true" aria-labelledby="sampleModalTitle">
        <button class="sample-modal-close" id="sampleModalClose" aria-label="閉じる">&times;</button>
        <div class="sample-modal-player">
            <iframe id="sampleModalIframe" src="" frameborder="0" allowfullscreen scrolling="no"></iframe>
        </div>
        <div class="sample-modal-info">
            <h2 class="sample-modal-title" id="sampleModalTitle"></h2>
            <p class="sample-modal-actress" id="sampleModalActress"></p>
            <div class="sample-modal-footer">
                <span class="sample-modal-price" id="sampleModalPrice"></span>
                <a href="#" id="sampleModalLink" target="_blank" rel="nofollow noopener" class="sample-modal-btn">
                    FANZAで詳細を見る &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
