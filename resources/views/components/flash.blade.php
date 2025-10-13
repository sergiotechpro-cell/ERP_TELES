@if(session('ok') || session('error') || $errors->any())
  <div class="alert {{ session('error') ? 'alert-danger' : 'alert-success' }} border-0 shadow-sm d-flex align-items-center"
       style="border-radius:14px;">
    <i class="bi {{ session('error') ? 'bi-x-circle' : 'bi-check-circle' }} me-2"></i>
    <div class="flex-fill">
      <strong>{{ session('error') ? 'Ups' : 'Listo' }}:</strong>
      {{ session('ok') ?? session('error') }}
      @if($errors->any())
        <ul class="mb-0 mt-1">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      @endif
    </div>
    <button type="button" class="btn-close ms-2" data-bs-dismiss="alert"></button>
  </div>
@endif
