<div class="modal-backdrop" id="modalDocumento" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalDocumentoTitle">
        <div class="modal-heading">
            <div>
                <span class="eyebrow">CATÁLOGO DE EXTRAS</span>
                <h2 id="modalDocumentoTitle">Agregar documento</h2>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">×</button>
        </div>
        <p class="modal-description">El documento aparecerá como opción en el apartado Extras de los residentes.</p>
        <form data-demo-form>
            <label>Nombre del documento<input type="text" placeholder="Ej. Carta de no adeudo" required></label>
            <label>Descripción<textarea rows="3" placeholder="Explica qué puede solicitar el residente"></textarea></label>
            <label class="inline-check"><input type="checkbox" checked> Visible para residentes</label>
            <div class="modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cancelar</button>
                <button class="button button-primary" type="submit">Guardar documento</button>
            </div>
        </form>
    </section>
</div>
