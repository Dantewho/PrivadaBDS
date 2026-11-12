<div class="modal-backdrop" id="modalVivienda" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalViviendaTitle">
        <div class="modal-heading">
            <div>
                <span class="eyebrow">NUEVO REGISTRO</span>
                <h2 id="modalViviendaTitle">Registrar vivienda</h2>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">×</button>
        </div>
        <form method="post" action="{{ route('admin.viviendas.store') }}">@csrf
            <label>Número o identificador<input name="numero" placeholder="Ej. 125" required></label>
            <label>Dirección o referencia<input name="direccion" required> </label>
            <label>Estado
                <select name="estado">
                    <option value="activa">Activa</option>
                    <option value="inactiva">Inactiva</option>
                </select>
            </label>
            <div class="modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cancelar</button>
                <button class="button button-primary" type="submit">Guardar vivienda</button>
            </div>
        </form>
    </section>
</div>
