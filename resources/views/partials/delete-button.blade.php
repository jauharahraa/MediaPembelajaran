<form method="POST" action="{{ $action }}" class="d-inline"
      onsubmit="return confirm({{ Js::from($message) }})">
    @csrf
    @method('DELETE')
    <button class="btn btn-sm btn-outline-danger rounded-pill" title="Hapus">
        <i class="bi bi-trash"></i>@isset($label)<span class="ms-1">{{ $label }}</span>@endisset
    </button>
</form>