<div class="modal-backdrop" id="modalMulta" aria-hidden="true"><section class="modal-card"><div class="modal-heading"><div><span class="eyebrow">REGISTRO DE SANCIÓN</span><h2>Crear multa</h2></div><button class="modal-close" type="button" data-modal-close>×</button></div>
<form method="post" action="{{ route('admin.multas.store') }}" enctype="multipart/form-data" data-validate>@csrf
<label>Vivienda<select name="vivienda_id" data-fine-home required><option value="">Selecciona vivienda</option>@foreach($homes as $home)<option value="{{ $home->id }}">Casa #{{ $home->numero }}</option>@endforeach</select></label>
<label>Residente (opcional)<select name="usuario_id" data-fine-resident><option value="">Aplicar a toda la vivienda</option>@foreach($homes as $home)@foreach($home->usuarios as $resident)@if($resident->rol==='residente' && $resident->estado==='activo')<option value="{{ $resident->id }}" data-vivienda="{{ $home->id }}">{{ $resident->name }} · Casa #{{ $home->numero }}</option>@endif @endforeach @endforeach</select></label>
<label>Motivo<select name="motivo_id" data-fine-reason required><option value="">Selecciona motivo configurado</option>@foreach($reasons as $reason)<option value="{{ $reason->id }}" data-base="{{ $reason->monto_base }}" data-incremento="{{ $reason->incremento }}">{{ $reason->nombre }} · base ${{ number_format((float)$reason->monto_base,2) }}</option>@endforeach</select></label>
<p class="modal-note" data-fine-price>El monto y el incremento se toman del catálogo de Reglamento.</p>
<label>Descripción de los hechos<textarea name="descripcion" rows="3" maxlength="2000"></textarea></label>
<label>Evidencia (opcional, imagen/PDF/Word; máximo 8 MB)<input type="file" name="evidencia" accept="image/jpeg,image/png,image/webp,.pdf,.doc,.docx"></label>
<p class="modal-note">Cada multa previa del mismo motivo aumenta el importe según el porcentaje configurado en Reglamento. Se registrará el cargo correspondiente en Adeudos.</p>
<div class="modal-actions"><button type="button" class="button button-outline" data-modal-close>Cancelar</button><button class="button button-primary" type="submit">Registrar multa</button></div>
</form></section></div>
