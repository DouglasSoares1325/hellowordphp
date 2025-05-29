@php use Illuminate\Support\Str; @endphp

@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="mb-0">Lista de Equipamentos</h4>
  <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addEquipamentoModal">
    <i class="fas fa-plus"></i> Adicionar Equipamento
  </button>
</div>

<div class="mb-4">
  <input type="text" id="filtroBusca" class="form-control" placeholder="Buscar itens..." onkeyup="filtrarItens()">
</div>

@foreach ($categorias as $categoria)
  @php
    $equipamentosCategoria = $equipamentosIndividuais->where('categoria_id', $categoria->id);
    $agrupados = $equipamentosCategoria->groupBy('item')->map(function($grupo) {
      return [
        'quantidade_total' => $grupo->sum('quantidade'),
        'detalhes' => $grupo
      ];
    });
  @endphp

  <div class="card shadow-sm mb-4" >
    <div class="card-header d-flex justify-content-between align-items-center bg-dark text-white">
      <strong>{{ $categoria->nome }}</strong>
      <button class="btn btn-primary btn-sm text-white" data-bs-toggle="collapse" data-bs-target="#cat-{{ $categoria->id }}">
        Ver detalhes
      </button>
    </div>
    <div class="card-body p-0 table-responsive">
      <table class="table table-hover table-dark table-striped mb-0">
        <thead>
          <tr>
            <th class="text-start">Descrição</th>
            <th class="text-center">Quantidade</th>
            <th class="text-center">Status</th>
            <th class="text-end">Ações</th>
          </tr>
        </thead>
        <tbody>
          @if($agrupados->isEmpty())
            <tr>
              <td colspan="4" class="text-center">Nenhum equipamento cadastrado.</td>
            </tr>
          @endif

          @foreach ($agrupados as $item => $grupo)
            <tr class="linha-item" data-item="{{ strtolower($item) }}">
              <td class="text-start"><strong>{{ $item }}</strong></td>
              <td class="text-center">{{ $grupo['detalhes']->count() }}</td>
              <td class="text-center">
                @php $min = $grupo['detalhes']->first()->quantidade_minima ?? 0; @endphp
                @if ($grupo['quantidade_total'] <= $min)
                  <span class="badge bg-danger">Estoque baixo</span>
                @else
                  <span class="badge bg-success">Normal</span>
                @endif
              </td>
              <td class="text-end">Bombrito</td>
              <!-- <td class="text-end">
                
                Botão Editar 
                <button class="btn btn-sm btn-outline-secondary me-1" title="Editar" data-bs-toggle="tooltip">
                  <i class="fas fa-edit"></i>
                </button>

                 Botão Excluir 
                <button class="btn btn-sm btn-outline-danger" title="Excluir" data-bs-toggle="tooltip">
                  <i class="fas fa-trash-alt"></i>
                </button>
              </td> -->
            </tr>
            <tr>
              <td colspan="4" class="p-0">
                <div  class="collapse" id="cat-{{ $categoria->id }}">
                  <table class="table table-hover table-bordered table-sm text-center mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Marca/Modelo</th>
                        <th>Nº Série</th>
                        <th>MAC</th>
                        <th>Responsável</th>
                        <th>Status</th>
                        <th>Setor</th>
                        <th>Quantidade Mínima</th>
                        <th>Termo</th>
                        <th class="text-end">Ações</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($grupo['detalhes'] as $equipamento)
                        <tr>
                          <td>{{ $equipamento->marca_modelo }}</td>
                          <td>{{ $equipamento->numero_serie }}</td>
                          <td>{{ $equipamento->mac }}</td>
                          <td>{{ $equipamento->responsavel }}</td>
                          <td>{{ $equipamento->status }}</td>
                          <td>{{ $equipamento->setor ?? '-'  }}</td>
                          <td>{{ $equipamento->quantidade_minima }}</td>
                          <td>
                            @if ($equipamento->termo)
                              <a href="{{ asset('storage/termos/' . $equipamento->termo) }}" target="_blank">Ver</a>
                            @else
                              Sem Termo
                            @endif
                          </td>
                          <td class="text-end">
                            <!-- Botão Editar com tooltip -->
                            <button type="button"
                              class="btn btn-sm btn-outline-secondary me-1"
                              data-bs-toggle="modal"
                              data-bs-target="#editEquipamentoModal{{ $equipamento->id }}"
                              title="Editar">
                              <i class="fas fa-edit"></i>
                            </button>

                            <!-- Botão Excluir com tooltip-->
                            <form method="POST" action="{{ route('equipamentos.destroy', $grupo['detalhes']->first()) }}"
                                  class="d-inline" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger" 
                                title="Excluir">                                
                                <i class="fas fa-trash-alt"></i>
                              </button>
                            </form>
                            <!-- Modal de edição individual -->
                            <div class="modal fade" id="editEquipamentoModal{{ $equipamento->id }}" tabindex="-1" aria-labelledby="editLabel{{ $equipamento->id }}" aria-hidden="true">
                              <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                  <form action="{{ route('equipamentos.update', $equipamento->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="modal-header">
                                      <h5 class="modal-title" id="editLabel{{ $equipamento->id }}">Editar Equipamento</h5>
                                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                    </div>

                                    <div class="modal-body">
                                      <div class="row">
                                        <div class="col-md-4 mb-3">
                                          <label class="form-label text-start w-100">Item</label>
                                          <input type="text" name="item" class="form-control" required value="{{ $equipamento->item }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <label class="form-label text-start w-100">Marca/Modelo</label>
                                          <input type="text" name="marca_modelo" class="form-control" required value="{{ $equipamento->marca_modelo }}">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                          <label class="form-label text-start w-100">Responsável</label>
                                          <input type="text" name="responsavel" class="form-control" required value="{{ $equipamento->responsavel }}">
                                        </div>
                                      </div>

                                      <div class="row">
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">MAC</label>
                                          <input type="text" name="mac" class="form-control" value="{{ $equipamento->mac }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">Nº de Série</label>
                                          <input type="text" name="numero_serie" class="form-control" required value="{{ $equipamento->numero_serie }}">
                                        </div>
                                      </div>

                                      <div class="row">
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">Status</label>
                                          <select name="status" class="form-select" required>
                                            <option value="">Selecione</option>
                                            <option value="Ativo" {{ $equipamento->status == 'Ativo' ? 'selected' : '' }}>Ativo</option>
                                            <option value="Inativo" {{ $equipamento->status == 'Inativo' ? 'selected' : '' }}>Inativo</option>
                                            <option value="Manutenção" {{ $equipamento->status == 'Manutenção' ? 'selected' : '' }}>Manutenção</option>
                                          </select>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">Categoria</label>
                                          <select name="categoria_id" class="form-select" required>
                                            @foreach ($categorias as $cat)
                                              <option value="{{ $cat->id }}" {{ $equipamento->categoria_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->nome }}
                                              </option>
                                            @endforeach
                                          </select>
                                        </div>
                                      </div>
                                      <div class="mb-3">
                                        <label class="form-label text-start w-100">Setor</label>
                                        <input type="text" name="setor" class="form-control" value="{{ old('setor') }}">
                                      </div>


                                      <div class="row">
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">Quantidade</label>
                                          <input type="number" name="quantidade" class="form-control" required value="{{ $equipamento->quantidade }}">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                          <label class="form-label text-start w-100">Quantidade mínima</label>
                                          <input type="number" name="quantidade_minima" class="form-control" value="{{ $equipamento->quantidade_minima }}">
                                        </div>
                                      </div>

                                      <div class="mb-3">
                                        <label class="form-label text-start w-100">Termo (Link do google Drive)</label>
                                        <input type="text" name="termo" class="form-control" value="{{ $equipamento->termo }}">
                                      </div>
                                    </div>

                                    <div class="modal-footer">
                                      <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                      <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>

                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
@endforeach

<!-- Modal de adicionar equipamento -->
<div class="modal fade" id="addEquipamentoModal" tabindex="-1" aria-labelledby="addEquipamentoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" action="{{ route('equipamentos.store') }}">
        @csrf

        <div class="modal-header">
          <h5 class="modal-title" id="addEquipamentoModalLabel">Adicionar Equipamento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          {{-- Mensagens de erro únicas --}}
          @if ($errors->any())
            <div class="alert alert-danger">
              <ul class="mb-0">
                @foreach ($errors->all() as $error)
                  @if (Str::contains($error, 'já está'))
                    <li> {{ $error }}</li>
                  @endif
                @endforeach
              </ul>
            </div>
          @endif

          <div class="row">
            <div class="col-md-4 mb-3">
              <label class="form-label text-start w-100">Item</label>
              <input type="text" name="item" class="form-control" required value="{{ old('item') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label text-start w-100">Marca/Modelo</label>
              <input type="text" name="marca_modelo" class="form-control" required value="{{ old('marca_modelo') }}">
            </div>
            <div class="col-md-4 mb-3">
              <label class="form-label text-start w-100">Responsável</label>
              <input type="text" name="responsavel" class="form-control" required value="{{ old('responsavel') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">MAC</label>
              <input type="text" name="mac" class="form-control" required value="{{ old('mac') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">Nº de Série</label>
              <input type="text" name="numero_serie" class="form-control" required value="{{ old('numero_serie') }}">
            </div>
          </div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">Status</label>
              <select name="status" class="form-select" required>
                <option value="">Selecione</option>
                <option value="Ativo">Ativo</option>
                <option value="Inativo">Inativo</option>
                <option value="Manutenção">Manutenção</option>
              </select>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">Categoria</label>
              <select name="categoria_id" class="form-select" required>
                <option value="">Selecione</option>
                @foreach ($categorias as $cat)
                  <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label text-start w-100">Setor</label>
            <input type="text" name="setor" class="form-control" value="{{ old('setor') }}">
          </div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">Quantidade</label>
              <input type="number" name="quantidade" class="form-control" required value="{{ old('quantidade') }}">
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label text-start w-100">Quantidade mínima</label>
              <input type="number" name="quantidade_minima" class="form-control" required value="{{ old('quantidade_minima') }}">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label text-start w-100">Termo (Link do Google Drive)</label>
            <input type="text" name="termo" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-success">Salvar</button>
        </div>
      </form>
    </div>
  </div>
</div>

  @if (session('success'))
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 1055">
      <div id="toastSucesso" class="toast align-items-center text-bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
          <div class="toast-body">
            {{ session('success') }}
          </div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
        </div>
      </div>
    </div>
  @endif

<script>
function filtrarItens() {
  const valor = document.getElementById("filtroBusca").value.toLowerCase();

  // Para cada categoria (card)
  document.querySelectorAll(".card").forEach(card => {
    let encontrouNaCategoria = false;

    const cardHeader = card.querySelector(".card-header strong");
    const nomeCategoria = cardHeader ? cardHeader.textContent.toLowerCase() : "";

    const linhaItem = card.querySelectorAll(".linha-item");
    const collapseDiv = card.querySelector(".collapse");
    const sublinhas = collapseDiv ? collapseDiv.querySelectorAll("tbody tr") : [];

    // Esconde tudo inicialmente
    linhaItem.forEach(linha => linha.style.display = "none");
    if (collapseDiv) collapseDiv.classList.remove("show");
    if (sublinhas) sublinhas.forEach(linha => linha.style.display = "none");

    // Verifica se nome da categoria bate
    if (nomeCategoria.includes(valor)) {
      encontrouNaCategoria = true;
    }

    // Verifica nas linhas agrupadas
    let encontrouPrincipal = false;
    linhaItem.forEach(linha => {
      const texto = linha.textContent.toLowerCase();
      if (texto.includes(valor)) {
        linha.style.display = "";
        encontrouPrincipal = true;
      }
    });

    // Verifica nas subtabelas
    let encontrouSub = false;
    sublinhas.forEach(linha => {
      const texto = linha.textContent.toLowerCase();
      if (texto.includes(valor)) {
        linha.style.display = "";
        encontrouSub = true;
      }
    });

    // Se encontrou algo, exibe o card e abre subtabela
    if (encontrouNaCategoria || encontrouPrincipal || encontrouSub) {
      card.style.display = "";
      if (encontrouSub && collapseDiv) {
        collapseDiv.classList.add("show");
      }
    } else {
      card.style.display = "none";
    }
  });
}
</script>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const toastEl = document.getElementById('toastSucesso');
    if (toastEl) {
      const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
      toast.show();
    }
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>

@endsection

<script>
  document.addEventListener("DOMContentLoaded", function () {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  });
</script>


@if ($errors->any())
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const modal = new bootstrap.Modal(document.getElementById('addEquipamentoModal'));
    modal.show();
  });
</script>
@endif

