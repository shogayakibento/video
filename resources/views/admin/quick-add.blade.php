@extends('admin.layout')
@section('title', 'クイック登録')

@section('content')
    <h1 class="text-2xl font-bold mb-2">クイック登録</h1>
    <p class="text-gray-500 text-sm mb-6">品番といいね数を入力してください。</p>

    <div class="bg-white rounded-lg shadow p-6 max-w-lg">
        <form method="POST" action="{{ route('admin.quick-add.store') }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">FANZA品番 <span class="text-red-500">*</span></label>
                    <input type="text" name="dmm_content_id" value="{{ old('dmm_content_id') }}" required autofocus
                           placeholder="abc00123"
                           class="w-full px-4 py-2 rounded border border-gray-300 focus:border-blue-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">いいね数 <span class="text-red-500">*</span></label>
                        <input type="number" name="like_count" value="{{ old('like_count', 0) }}" min="0"
                               class="digit-picker-input">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">RT数</label>
                        <input type="number" name="retweet_count" value="{{ old('retweet_count', 0) }}" min="0"
                               class="digit-picker-input">
                    </div>
                </div>
            </div>
            <button type="submit" class="mt-6 w-full bg-accent hover:bg-red-600 text-white font-bold px-8 py-3 rounded transition">
                登録する
            </button>
        </form>
    </div>
@endsection

@push('scripts')
<style>
    .digit-picker { display: flex; align-items: flex-end; gap: 2px; }
    .digit-picker .digit-unit { text-align: center; }
    .digit-picker .digit-label { font-size: 0.65rem; color: #9ca3af; line-height: 1; margin-bottom: 2px; }
    .digit-picker select {
        display: block; width: 2.25rem; padding: 6px 0;
        border: 1px solid #d1d5db; border-radius: 4px;
        text-align: center; font-size: 1rem; font-family: monospace;
        cursor: pointer; background: #fff;
        appearance: none; -webkit-appearance: none;
    }
    .digit-picker select:hover { border-color: #93c5fd; }
    .digit-picker select:focus { border-color: #3b82f6; outline: none; }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input.digit-picker-input').forEach(function (input) {
            var name = input.name;
            var initVal = parseInt(input.value) || 0;
            var units = ['万', '千', '百', '十', '一'];
            var valStr = Math.min(initVal, 99999).toString().padStart(5, '0');

            var picker = document.createElement('div');
            picker.className = 'digit-picker';

            var selects = units.map(function (unit, i) {
                var div = document.createElement('div');
                div.className = 'digit-unit';

                var label = document.createElement('div');
                label.className = 'digit-label';
                label.textContent = unit;

                var sel = document.createElement('select');
                for (var d = 0; d <= 9; d++) {
                    var opt = document.createElement('option');
                    opt.value = d;
                    opt.textContent = d;
                    sel.appendChild(opt);
                }
                sel.value = parseInt(valStr[i]);

                div.appendChild(label);
                div.appendChild(sel);
                picker.appendChild(div);
                return sel;
            });

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = name;
            hidden.value = initVal;
            picker.appendChild(hidden);

            selects.forEach(function (sel) {
                sel.addEventListener('change', function () {
                    hidden.value = parseInt(selects.map(function (s) { return s.value; }).join('')) || 0;
                });
            });

            input.parentNode.replaceChild(picker, input);
        });
    });
</script>
@endpush
