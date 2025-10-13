@props(['action' => '#', 'q' => request('q')])
<form method="GET" action="{{ $action }}" class="d-flex gap-2 align-items-center">
  <div class="input-group">
    <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
    <input type="text" name="q" value="{{ $q }}" class="form-control border-start-0" placeholder="Buscar...">
  </div>
  <button class="btn btn-outline-secondary"><i class="bi bi-funnel"></i> Filtros</button>
</form>
