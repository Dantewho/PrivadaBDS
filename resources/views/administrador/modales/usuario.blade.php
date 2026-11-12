<div class="modal-backdrop" id="modalUsuario" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalUsuarioTitle">
        <div class="modal-heading">
            <div>
                <span class="eyebrow">ACCESO A PRIVADABDS</span>
                <h2 id="modalUsuarioTitle">Crear invitación</h2>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">×</button>
        </div>
        <p class="modal-description">El residente recibirá un enlace para crear su cuenta.</p>
        <form method="post" action="{{ route('admin.usuarios.invitar') }}">@csrf
            <label>Correo electrónico<input name="email" type="email" placeholder="residente@correo.com" required></label>
            <label>Vivienda
                <select name="vivienda_id" required>
                    <option value="">Selecciona una vivienda disponible</option>
                    @foreach($viviendas->where('estado','activa') as $home)
                        @if($home->usuarios_count < 2)
                            <option value="{{ $home->id }}">Casa #{{ $home->numero }} · {{ 2-$home->usuarios_count }} espacio(s)</option>
                        @endif
                    @endforeach
                </select>
            </label>
            <label class="owner-option">
                <input type="checkbox" name="es_propietario" value="1">
                <span class="owner-option-mark" aria-hidden="true">✓</span>
                <span class="owner-option-copy"><strong>Designar como propietario</strong><small>Responsable principal de esta vivienda.</small></span>
                <span class="owner-option-switch" aria-hidden="true"></span>
            </label>
            <div class="modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cancelar</button>
                <button class="button button-primary" type="submit">Enviar invitación</button>
            </div>
        </form>
    </section>
</div>
