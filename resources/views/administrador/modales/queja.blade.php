<div class="modal-backdrop" id="modalQueja" aria-hidden="true">
    <section class="modal-card modal-wide" role="dialog" aria-modal="true" aria-labelledby="modalQuejaTitle">
        <div class="modal-heading">
            <div>
                <span class="eyebrow">CASA #124 · 07/10/2026</span>
                <h2 id="modalQuejaTitle">Fuga de agua en área común</h2>
            </div>
            <button type="button" class="modal-close" data-modal-close aria-label="Cerrar">×</button>
        </div>
        <div class="message-thread">
            <div class="thread-message resident-message">
                <small>María Rodríguez · 09:14</small>
                <p>Noté una fuga de agua junto al jardín. Parece que viene de una tubería subterránea.</p>
            </div>
            <div class="thread-message admin-message">
                <small>Administración · 09:32</small>
                <p>Gracias por reportarlo. Vamos a enviar al equipo de mantenimiento para revisarlo.</p>
            </div>
        </div>
        <form data-demo-form>
            <label>Responder a María
                <textarea rows="3" placeholder="Escribe un mensaje..."></textarea>
            </label>
            <label>Estado del reporte
                <select>
                    <option>Abierta</option>
                    <option>En proceso</option>
                    <option>Resuelta</option>
                    <option>Cerrada</option>
                </select>
            </label>
            <div class="modal-actions">
                <button class="button button-outline" type="button" data-modal-close>Cerrar</button>
                <button class="button button-primary" type="submit">Enviar respuesta</button>
            </div>
        </form>
    </section>
</div>
