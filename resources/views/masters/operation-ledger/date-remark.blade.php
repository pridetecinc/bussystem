@extends('layouts.win')

@section('title', '予定登録')

@section('content')
<div class="container-fluid px-3 py-3" style="font-size: 0.8rem;">
    <form id="dateRemarkForm">
        @csrf
        <div class="mb-2">
            <label class="form-label">日付：</label>
            {{ $date }}
            <input type="hidden" id="remark_date_value" name="date" value="{{ $date }}">
        </div>
        
        <div class="mb-2">
            <label class="form-label">予定</label>
            <textarea class="form-control" id="remark_text" name="remark" rows="3" maxlength="500" placeholder="例：車両点検、会社休業日など">{{ $remark }}</textarea>
        </div>
        
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="stop_order" name="stop_order" value="1" {{ $stopOrder == '1' ? 'checked' : '' }}>
            <label class="form-check-label" for="stop_order">
                受注停止
            </label>
        </div>
        
        <div class="d-flex gap-4">
            <button type="submit" class="btn btn-primary">保存</button>
            <button type="button" class="btn btn-danger" onclick="parent.closeIframeModal()">キャンセル</button>
            <button type="button" class="btn btn-secondary" onclick="parent.closeIframeModal()" style="background-color: #33a64c;">閉じる</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const remarkText = document.getElementById('remark_text');
    const remarkCharCount = document.getElementById('remark_char_count');
    const remarkDateValue = document.getElementById('remark_date_value');
    const stopOrderCheckbox = document.getElementById('stop_order');
    const dateRemarkForm = document.getElementById('dateRemarkForm');
    const deleteRemarkBtn = document.getElementById('deleteRemarkBtn');
    const csrfToken = document.querySelector('input[name="_token"]').value;
    
    function updateCharCount() {
        if (remarkText && remarkCharCount) {
            remarkCharCount.textContent = remarkText.value.length;
        }
    }
    
    if (remarkText) {
        remarkText.addEventListener('input', updateCharCount);
        updateCharCount();
    }
    
    if (dateRemarkForm) {
        dateRemarkForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData();
            formData.append('date', remarkDateValue ? remarkDateValue.value : '');
            formData.append('remark', remarkText ? remarkText.value : '');
            formData.append('stop_order', stopOrderCheckbox && stopOrderCheckbox.checked ? 1 : 0);
            formData.append('_token', csrfToken);
            
            fetch('{{ route("masters.group-info-date-remarks.store") }}', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.parent && window.parent.postMessage) {
                        window.parent.postMessage({
                            action: 'close-iframe-and-reload'
                        }, '*');
                    }
                } else {
                    alert('保存失敗：' + (data.message || '不明なエラー'));
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('保存中にエラーが発生しました。');
            });
        });
    }
    
    if (deleteRemarkBtn) {
        deleteRemarkBtn.addEventListener('click', function() {
            const date = remarkDateValue ? remarkDateValue.value : '';
            
            if (!date) {
                return;
            }
            
            if (!confirm('この予定を削除してもよろしいですか？')) {
                return;
            }
            
            fetch('{{ url("masters/group-info-date-remarks") }}/' + date, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (window.parent && window.parent.postMessage) {
                        window.parent.postMessage({
                            action: 'close-iframe-and-reload'
                        }, '*');
                    }
                } else {
                    alert('削除失敗：' + (data.message || '不明なエラー'));
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                alert('削除中にエラーが発生しました。');
            });
        });
    }
});
</script>
@endpush