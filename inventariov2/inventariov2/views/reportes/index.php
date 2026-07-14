<?php require_once 'views/layout/header.php'; ?>

<div class="card p-20">
    <div class="flex-between">
        <div>
            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin: 0;">Reporte Histórico de Ventas</h2>
            <p style="font-size: 13px; color: #64748b; margin-top: 4px;">Datos consultados de forma asíncrona mediante la API REST del sistema.</p>
        </div>
        <span style="font-size: 0.85em; color: var(--text-light); background: #f3f4f6; padding: 6px 12px; border-radius: 20px; font-weight: 500; display: flex; align-items: center; gap: 6px; border: 1px solid #e2e8f0;">
            <i class='bx bx-network-chart' style="font-size: 16px; color: var(--primary-color);"></i> API REST Activa
        </span>
    </div>

    <!-- Cargador -->
    <div id="reports-loader" class="spinner-container" style="display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 50px 0; color: var(--text-light);">
        <div class="spinner" style="border: 4px solid rgba(0, 0, 0, 0.1); width: 36px; height: 36px; border-radius: 50%; border-left-color: var(--primary-color); animation: spin 1s linear infinite; margin-bottom: 12px;"></div>
        <p style="font-weight: 500;">Obteniendo datos de la API...</p>
    </div>

    <!-- Mensaje de Error -->
    <div id="reports-error" style="display:none; background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; font-weight: 500; align-items: center; gap: 8px; margin: 20px 0;">
        <i class='bx bx-error-circle' style="font-size: 20px;"></i> <span id="error-message">Ocurrió un error al consumir la API.</span>
    </div>

    <!-- Contenido del Reporte: Tabla Resumen Directa y JSON -->
    <div id="reports-content" style="display: none; margin-top: 25px;">
        <!-- 1. Tabla de Resumen -->
        <table class="table">
            <thead>
                <tr>
                    <th style="width: 100px;">ID Venta</th>
                    <th>Cliente</th>
                    <th>Vendedor</th>
                    <th style="text-align: right;">Subtotal</th>
                    <th style="text-align: right;">Descuento</th>
                    <th style="text-align: right;">ITBMS (7%)</th>
                    <th style="text-align: right;">Total Neto</th>
                    <th>Fecha</th>
                    <th style="width: 140px; text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody id="tabla-body">
                <!-- Se poblará con JS -->
            </tbody>
        </table>

        <!-- 2. Visor JSON Completo de la API debajo de la tabla -->
        <div style="margin-top: 35px; border-top: 1px solid #e2e8f0; padding-top: 25px;">
            <h3 style="font-size: 16px; font-weight: 700; color: #1e293b; margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                <i class='bx bx-code-block' style="color: var(--primary-color);"></i> Respuesta JSON Completa de la API
            </h3>
            <p style="font-size: 12.5px; color: #64748b; margin-bottom: 15px;">
                Carga cruda e intercambio de datos devueltos por el endpoint: <code>GET api/index.php?endpoint=ventas/reportes</code>
            </p>
            <pre style="background: #111827; color: #10b981; padding: 20px; border-radius: 12px; overflow: auto; max-height: 350px; font-size: 13px; font-family: monospace; line-height: 1.5; border: 1px solid #1f2937;" id="json-viewer"></pre>
        </div>
    </div>
</div>

<style>
/* Animación spinner local */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", () => {
    fetchVentasReport();
});

async function fetchVentasReport() {
    const loader = document.getElementById("reports-loader");
    const errorContainer = document.getElementById("reports-error");
    const errorMessage = document.getElementById("error-message");
    const content = document.getElementById("reports-content");
    const tablaBody = document.getElementById("tabla-body");
    const jsonViewer = document.getElementById("json-viewer");

    try {
        const response = await fetch('api/index.php?endpoint=ventas/reportes');
        if (!response.ok) {
            throw new Error(`HTTP Error Status: ${response.status}`);
        }

        const result = await response.json();

        if (result.success) {
            // 1. Mostrar JSON completo formateado
            jsonViewer.textContent = JSON.stringify(result, null, 2);

            // 2. Poblar la tabla de resumen
            tablaBody.innerHTML = '';
            if (result.data.length === 0) {
                tablaBody.innerHTML = `<tr><td colspan="9" style="text-align: center; color: var(--text-light); padding: 25px;">No hay transacciones de ventas registradas en el historial.</td></tr>`;
            } else {
                result.data.forEach(v => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>#${v.venta_id}</strong></td>
                        <td>${escapeHtml(v.cliente)}</td>
                        <td>${escapeHtml(v.vendedor)}</td>
                        <td style="text-align: right;">$${parseFloat(v.subtotal).toFixed(2)}</td>
                        <td style="text-align: right;">$${parseFloat(v.descuento).toFixed(2)}</td>
                        <td style="text-align: right;">$${parseFloat(v.itbms).toFixed(2)}</td>
                        <td style="text-align: right;"><strong style="color: var(--secondary-color);">$${parseFloat(v.total).toFixed(2)}</strong></td>
                        <td>${v.fecha}</td>
                        <td style="text-align: center;">
                            <button type="button" class="btn btn-edit" style="display: inline-flex; align-items: center; gap: 4px; padding: 5px 10px; font-size: 12px; cursor: pointer;" onclick="fetchFacturaJSON(${v.venta_id})">
                                <i class='bx bx-printer'></i> Factura PDF
                            </button>
                        </td>
                    `;
                    tablaBody.appendChild(tr);
                });
            }

            // Ocultar cargador y mostrar la tabla y el visor JSON
            loader.style.display = 'none';
            content.style.display = 'block';

        } else {
            throw new Error(result.error || "La API reportó un fallo al procesar la respuesta.");
        }

    } catch (err) {
        loader.style.display = 'none';
        errorMessage.textContent = `Error al conectar con la API: ${err.message}`;
        errorContainer.style.display = 'flex';
        console.error("API error:", err);
    }
}

// Función auxiliar para sanitizar HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}
</script>

<?php require_once 'views/layout/footer.php'; ?>
