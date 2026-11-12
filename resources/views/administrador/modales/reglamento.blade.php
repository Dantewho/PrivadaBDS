<div class="modal-backdrop" id="modalReglamento" aria-hidden="true">
    <section class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalReglamentoTitle">
        <div class="modal-heading">
            <div>
                <span class="eyebrow">ACTUALIZACIÓN DE DOCUMENTO</span>
                <h2 id="modalReglamentoTitle">Reemplazar reglamento</h2>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">×</button>
        </div>
        <p class="modal-description">El PDF nuevo quedará como versión vigente y el anterior se conservará en el historial.</p>
        <form data-demo-form>
            <label>Nombre del documento
                <input type="text" value="Reglamento de Bahía de Santiago" required>
            </label>
            <label>Versión
                <input type="text" placeholder="Ej. 2026.2" required>
            </label>
            <label>Archivo PDF
                <input type="file" accept="application/pdf" required>
            </label>
            <div class="modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cancelar</button>
                <button class="button button-primary" type="submit">Guardar versión</button>
            </div>
        </form>
    </section>
</div>
