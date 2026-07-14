        </div>
    </section>

    <!-- Modal Base (Para notificaciones premium y Alertas de Stock) -->
    <div id="alertModal" class="modal">
        <div class="modal-content">
            <div class="modal-header" id="modalHeader">
                <h2 id="modalTitle"><i class='bx bx-error-circle'></i> Notificación</h2>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <p id="modalMessage"></p>
                <!-- Opcional contenedor dinámico para listas -->
                <ul id="modalList"></ul>
                <!-- Contenedor para contenidos personalizados de API (JSON, botones PDF) -->
                <div id="modalCustomContent"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary close-modal-btn">Entendido</button>
            </div>
        </div>
    </div>

    <!-- Biblioteca html2pdf.js para exportar a PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <!-- Referencia al archivo JavaScript principal con la lógica asíncrona -->
    <script src="public/js/app.js"></script>
</body>
</html>
