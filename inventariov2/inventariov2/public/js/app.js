document.addEventListener("DOMContentLoaded", () => {
    // 1. Inicializar Eventos Globales
    initSidebar();
    initModal();
    initPOS();

    // 2. Reto: Iniciar chequeo asíncrono de alertas de stock al cargar
    // Usamos Fetch API para consumir nuestra API REST de forma asíncrona
    checkStockAlerts();
    // Opcional: configurar chequeo periódico cada minuto
    // setInterval(checkStockAlerts, 60000);
});

// ==========================================
// Lógica de UI Global
// ==========================================
function initSidebar() {
    let sidebar = document.querySelector(".sidebar");
    let sidebarBtn = document.querySelector(".sidebarBtn");
    if (sidebarBtn && sidebar) {
        sidebarBtn.onclick = function () {
            sidebar.classList.toggle("active");
            if (sidebar.classList.contains("active")) {
                sidebarBtn.classList.replace("bx-menu", "bx-menu-alt-right");
            } else {
                sidebarBtn.classList.replace("bx-menu-alt-right", "bx-menu");
            }
        }
    }
}

function initModal() {
    const modal = document.getElementById("alertModal");
    const closeBtns = document.querySelectorAll(".close-modal, .close-modal-btn");
    if (modal) {
        closeBtns.forEach(btn => {
            btn.onclick = () => modal.style.display = "none";
        });
        window.onclick = (e) => {
            if (e.target == modal) {
                modal.style.display = "none";
            }
        }
    }
}

function showModal(title, message, type = "danger", list = []) {
    const modal = document.getElementById("alertModal");
    const header = document.getElementById("modalHeader");
    const titleEl = document.getElementById("modalTitle");
    const messageEl = document.getElementById("modalMessage");
    const listEl = document.getElementById("modalList");

    if (!modal) return;

    header.className = `modal-header ${type}`;
    let icon = type === 'success' ? 'bx-check-circle' : (type === 'info' ? 'bx-info-circle' : 'bx-error-circle');
    titleEl.innerHTML = `<i class='bx ${icon}'></i> ${title}`;
    messageEl.textContent = message;

    listEl.innerHTML = '';
    if (list.length > 0) {
        list.forEach(item => {
            let li = document.createElement("li");
            li.textContent = item;
            listEl.appendChild(li);
        });
    }

    // Limpiar contenedor personalizado para evitar solapamientos
    const customEl = document.getElementById("modalCustomContent");
    if (customEl) customEl.innerHTML = '';

    modal.style.display = "block";
}

// ==========================================
// RETO: Consumo API REST de Alertas Stock
// ==========================================
async function checkStockAlerts(isPostSale = false) {
    try {
        const response = await fetch('api/index.php?endpoint=inventario/alertas');
        if (!response.ok) throw new Error('Error al consultar alertas');

        const result = await response.json();

        if (result.success && result.count > 0) {
            displayStockToasts(result.data);
        }
    } catch (err) {
        console.error("Error en checkStockAlerts:", err);
    }
}

function displayStockToasts(productos) {
    const container = document.getElementById('stock-alerts-container');
    if (!container) return;

    container.innerHTML = ''; // Limpiar anteriores

    productos.forEach(p => {
        const toast = document.createElement('div');
        toast.className = 'stock-alert-toast';
        toast.innerHTML = `
            <div>
                <i class='bx bx-alarm-exclamation'></i>
            </div>
            <div>
                <div style="font-size: 14px;"><strong>¡Stock Bajo!</strong></div>
                <div style="font-size: 12px; margin-top: 3px;">${p.nombre} (Quedan: ${p.stock})</div>
            </div>
            <div style="cursor:pointer;" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></div>
        `;
        container.appendChild(toast);
    });
}

// ==========================================
// Lógica de Punto de Venta (POS)
// ==========================================
let cart = [];
const ITBMS_RATE = 0.07;

function initPOS() {
    const btnProcess = document.getElementById('btn-process-sale');
    const inputDesc = document.getElementById('input_descuento');
    const buscarCliente = document.getElementById('buscar_cliente');
    const buscarProducto = document.getElementById('buscar_producto');

    // Si no existe buscar_producto, no estamos en la vista de POS
    if (!buscarProducto && !btnProcess) return;

    if (inputDesc) inputDesc.addEventListener('input', updateTotals);
    if (btnProcess) btnProcess.addEventListener('click', processSale);

    // Buscador interactivo de clientes
    if (buscarCliente) {
        buscarCliente.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase().trim();
            const select = document.getElementById('cliente_id');
            const options = select.options;
            let firstMatch = null;

            for (let i = 0; i < options.length; i++) {
                const text = options[i].text.toLowerCase();
                if (text.includes(term)) {
                    options[i].style.display = 'block';
                    if (firstMatch === null) firstMatch = options[i].value;
                } else {
                    options[i].style.display = 'none';
                }
            }
            if (firstMatch !== null && term !== '') {
                select.value = firstMatch;
            }
        });
    }

    // Buscador interactivo de productos (filtra filas en la Tabla)
    if (buscarProducto) {
        buscarProducto.addEventListener('input', function (e) {
            const term = e.target.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.catalog-item-row');

            rows.forEach(row => {
                const nombre = row.getAttribute('data-nombre') || '';
                const codigo = row.getAttribute('data-codigo') || '';
                if (nombre.includes(term) || codigo.includes(term)) {
                    row.style.display = 'table-row';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }
}

function addProductFromCard(id, nombre, precio, stock) {
    // Verificar si ya existe en carrito
    const existing = cart.find(item => item.producto_id === id);
    if (existing) {
        if (existing.cantidad + 1 > stock) {
            showModal('Stock Insuficiente', `No hay suficiente stock para agregar más de "${nombre}".`, 'danger');
            return;
        }
        existing.cantidad++;
        existing.subtotal = existing.cantidad * existing.precio;
    } else {
        if (stock < 1) {
            showModal('Stock Insuficiente', `El producto "${nombre}" está agotado.`, 'danger');
            return;
        }
        cart.push({
            producto_id: id,
            nombre: nombre,
            cantidad: 1,
            precio: parseFloat(precio),
            subtotal: parseFloat(precio)
        });
    }

    renderCart();
}

function removeProduct(id) {
    cart = cart.filter(item => item.producto_id !== id);
    renderCart();
}

function adjustQty(id, amount) {
    const item = cart.find(item => item.producto_id === id);
    if (item) {
        let newQty = item.cantidad + amount;
        if (newQty < 1) {
            removeProduct(id);
            return;
        }

        // Buscar el stock disponible en la fila del producto en el DOM
        const row = document.querySelector(`.catalog-item-row[data-id="${id}"]`);
        let stock = 9999;
        if (row) {
            stock = parseInt(row.getAttribute('data-stock'));
        }

        if (newQty > stock) {
            showModal('Stock Insuficiente', `Solo hay ${stock} unidades disponibles en inventario para este producto.`, 'danger');
            newQty = stock;
        }

        item.cantidad = newQty;
        item.subtotal = item.cantidad * item.precio;
        renderCart();
    }
}

function renderCart() {
    const tbody = document.getElementById('cart-body');
    const emptyMsg = document.getElementById('cart-empty-message');
    if (!tbody) return;

    tbody.innerHTML = '';

    if (cart.length === 0) {
        if (emptyMsg) emptyMsg.style.display = 'flex';
    } else {
        if (emptyMsg) emptyMsg.style.display = 'none';

        cart.forEach(item => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div style="font-weight: 600; color: #1e293b;">${item.nombre}</div>
                    <div style="font-size: 11px; color: #64748b;">$${item.precio.toFixed(2)} c/u</div>
                </td>
                <td style="text-align: center;">
                    <div class="quantity-control-group">
                        <button type="button" onclick="adjustQty('${item.producto_id}', -1)">-</button>
                        <span class="qty-value">${item.cantidad}</span>
                        <button type="button" onclick="adjustQty('${item.producto_id}', 1)">+</button>
                    </div>
                </td>
                <td style="text-align: right; font-weight: 600; color: #1e293b;">$${item.subtotal.toFixed(2)}</td>
                <td style="text-align: center;">
                    <button type="button" class="cart-remove-btn" onclick="removeProduct('${item.producto_id}')">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    updateTotals();
}

function updateTotals() {
    let subtotal = cart.reduce((sum, item) => sum + item.subtotal, 0);
    let descuento = parseFloat(document.getElementById('input_descuento').value) || 0;

    // Validar descuento
    if (descuento > subtotal) {
        descuento = subtotal;
        document.getElementById('input_descuento').value = descuento;
    }

    let baseItbms = subtotal - descuento;
    let itbms = baseItbms * ITBMS_RATE;
    let total = baseItbms + itbms;

    document.getElementById('res_subtotal').innerText = `$${subtotal.toFixed(2)}`;
    document.getElementById('res_itbms').innerText = `$${itbms.toFixed(2)}`;
    document.getElementById('res_total').innerText = `$${total.toFixed(2)}`;

    return { subtotal, descuento, itbms, total };
}

async function processSale() {
    if (cart.length === 0) {
        showModal('Error', 'El carrito está vacío.', 'danger');
        return;
    }

    const cliente_id = document.getElementById('cliente_id').value;
    if (!cliente_id) {
        showModal('Error', 'Seleccione un cliente.', 'danger');
        return;
    }

    const totals = updateTotals();

    const payload = {
        cliente_id: cliente_id,
        subtotal: totals.subtotal.toFixed(2),
        itbms: totals.itbms.toFixed(2),
        descuento: totals.descuento.toFixed(2),
        total: totals.total.toFixed(2),
        detalles: cart.map(item => ({
            producto_id: item.producto_id,
            cantidad: item.cantidad,
            precio: item.precio.toFixed(2),
            subtotal: item.subtotal.toFixed(2)
        }))
    };

    try {
        // Bloquear botón
        document.getElementById('btn-process-sale').disabled = true;

        const response = await fetch('index.php?action=pos_store', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            // Mostrar Factura en formato JSON en Modal
            // Para cumplir con: "Generación, intercambio y visualización de facturas exclusivamente en JSON"
            fetchFacturaJSON(result.venta_id);

            // Limpiar POS
            cart = [];
            document.getElementById('input_descuento').value = 0;
            renderCart();

            // RETO: Comprobar alertas de stock inmediatamente después de la venta exitosa
            setTimeout(() => checkStockAlerts(true), 500); // 500ms delay para dar tiempo a la BD

        } else {
            showModal('Error al procesar', result.mensaje, 'danger');
        }
    } catch (err) {
        showModal('Error fatal', 'Ocurrió un error al conectar con el servidor.', 'danger');
        console.error(err);
    } finally {
        document.getElementById('btn-process-sale').disabled = false;
    }
}

// Variable global para almacenar el JSON de la última factura procesada
let latestFacturaData = null;

async function fetchFacturaJSON(venta_id) {
    try {
        const response = await fetch(`api/index.php?endpoint=facturas/${venta_id}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.mensaje || "Error al obtener factura de la API.");
        }

        // Guardar el JSON en la variable global
        latestFacturaData = result;

        const pre = document.createElement('pre');
        pre.style.background = '#111827';
        pre.style.color = '#10b981';
        pre.style.padding = '15px';
        pre.style.borderRadius = '8px';
        pre.style.overflow = 'auto';
        pre.style.fontSize = '12px';
        pre.style.marginTop = '15px';
        pre.textContent = JSON.stringify(result, null, 2); // Pretty print JSON

        // Botón para exportar a PDF
        const btnPDF = document.createElement('button');
        btnPDF.className = 'btn btn-primary';
        btnPDF.style.marginTop = '15px';
        btnPDF.style.width = '100%';
        btnPDF.innerHTML = `<i class='bx bx-printer'></i> Ver/Imprimir Factura PDF`;
        btnPDF.onclick = () => exportarFacturaPDF(venta_id);

        // Primero mostrar el modal vacío
        showModal('Venta Exitosa', '', 'success');

        // Luego colocar en el contenedor personalizado para que showModal no lo borre al inicializar
        const customEl = document.getElementById('modalCustomContent');
        if (customEl) {
            customEl.innerHTML = '<h3>Factura JSON Generada:</h3>';
            customEl.appendChild(pre);
            customEl.appendChild(btnPDF);
        }

    } catch (e) {
        console.error("fetchFacturaJSON failed:", e);
        // Fallback: mostrar al menos el modal de éxito de la venta
        showModal('Venta Exitosa', `La venta #${venta_id} se registró con éxito en la base de datos, pero no se pudo cargar el JSON interactivo. Detalle: ${e.message}`, 'success');
    }
}

function exportarFacturaPDF(venta_id) {
    if (!latestFacturaData || !latestFacturaData.success) {
        alert("No se cargaron los datos de la factura.");
        return;
    }

    const f = latestFacturaData.data;

    // Crear contenedor temporal para renderizar la factura
    const invoiceDiv = document.createElement('div');
    invoiceDiv.style.padding = '30px';
    invoiceDiv.style.background = '#ffffff';
    invoiceDiv.style.color = '#334155';
    invoiceDiv.style.fontFamily = "'Inter', sans-serif";
    invoiceDiv.style.fontSize = '14px';
    invoiceDiv.style.lineHeight = '1.5';

    // Contenido HTML de la factura comercial
    invoiceDiv.innerHTML = `
        <div style="border-bottom: 2px solid #6366f1; padding-bottom: 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="font-size: 24px; font-weight: 700; color: #4f46e5; margin: 0;">MINIEMPRESA S.A.</h1>
                <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Gestión de Inventario & Facturación</p>
            </div>
            <div style="text-align: right;">
                <h2 style="font-size: 16px; font-weight: 700; color: #0f172a; margin: 0;">FACTURA DE VENTA</h2>
                <p style="font-size: 14px; font-weight: 600; color: #6366f1; margin: 4px 0 0 0;">Nº: #${f.venta_id}</p>
            </div>
        </div>
        
        <div style="display: flex; justify-content: space-between; margin-bottom: 25px; gap: 40px;">
            <div style="flex: 1;">
                <h3 style="font-size: 12px; font-weight: 700; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-transform: uppercase; margin-bottom: 8px;">Datos de Emisión</h3>
                <p style="margin: 0; font-size: 13px;"><strong>Fecha:</strong> ${f.fecha}</p>
                <p style="margin: 3px 0 0 0; font-size: 13px;"><strong>Vendedor:</strong> ${escapeHtml(f.vendedor)}</p>
            </div>
            <div style="flex: 1; text-align: right;">
                <h3 style="font-size: 12px; font-weight: 700; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 4px; text-transform: uppercase; margin-bottom: 8px;">Facturado A</h3>
                <p style="margin: 0; font-size: 13px;"><strong>Cliente:</strong> ${escapeHtml(f.cliente.nombre)}</p>
                <p style="margin: 3px 0 0 0; font-size: 13px;"><strong>Documento:</strong> ${escapeHtml(f.cliente.documento)}</p>
                ${f.cliente.telefono ? `<p style="margin: 3px 0 0 0; font-size: 13px;"><strong>Teléfono:</strong> ${escapeHtml(f.cliente.telefono)}</p>` : ''}
            </div>
        </div>
        
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 25px;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #cbd5e1;">
                    <th style="padding: 10px; text-align: left; font-size: 12px; font-weight: 700; color: #475569;">Descripción del Producto</th>
                    <th style="padding: 10px; text-align: center; font-size: 12px; font-weight: 700; color: #475569; width: 80px;">Cant.</th>
                    <th style="padding: 10px; text-align: right; font-size: 12px; font-weight: 700; color: #475569; width: 100px;">Prec. Unit</th>
                    <th style="padding: 10px; text-align: right; font-size: 12px; font-weight: 700; color: #475569; width: 110px;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                ${f.detalles.map((d, index) => `
                    <tr style="border-bottom: 1px solid #e2e8f0; background: ${index % 2 === 0 ? '#ffffff' : '#fcfdfe'};">
                        <td style="padding: 12px 10px; font-size: 13px; font-weight: 600; color: #0f172a;">${escapeHtml(d.producto)}</td>
                        <td style="padding: 12px 10px; text-align: center; font-size: 13px;">${d.cantidad}</td>
                        <td style="padding: 12px 10px; text-align: right; font-size: 13px;">$${parseFloat(d.precio_unitario).toFixed(2)}</td>
                        <td style="padding: 12px 10px; text-align: right; font-size: 13px; font-weight: 600; color: #0f172a;">$${parseFloat(d.subtotal).toFixed(2)}</td>
                    </tr>
                `).join('')}
            </tbody>
        </table>
        
        <div style="display: flex; justify-content: flex-end;">
            <div style="width: 250px;">
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                    <span style="color: #64748b;">Subtotal:</span>
                    <span style="font-weight: 600;">$${parseFloat(f.subtotal).toFixed(2)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f1f5f9; font-size: 13px;">
                    <span style="color: #64748b;">Descuento:</span>
                    <span style="font-weight: 600;">$${parseFloat(f.descuento).toFixed(2)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #cbd5e1; font-size: 13px;">
                    <span style="color: #64748b;">Impuesto ITBMS (7%):</span>
                    <span style="font-weight: 600;">$${parseFloat(f.itbms).toFixed(2)}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 10px 0; font-size: 16px;">
                    <span style="font-weight: 700; color: #0f172a;">Total Neto:</span>
                    <span style="font-weight: 700; color: #10b981;">$${parseFloat(f.total).toFixed(2)}</span>
                </div>
            </div>
        </div>
        
        <div style="border-top: 1px dashed #cbd5e1; margin-top: 40px; padding-top: 20px; text-align: center; color: #94a3b8; font-size: 12px;">
            <p style="margin: 4px 0 0 0;">Generada por Sistema de Gestión de Inventario</p>
        </div>
    `;

    // Configuración de html2pdf
    const opt = {
        margin: 10,
        filename: `factura_${venta_id}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { unit: 'mm', format: 'letter', orientation: 'portrait' }
    };

    // Abrir pestaña vacía de inmediato para evitar bloqueadores de pop-ups
    const printWindow = window.open('', '_blank');
    if (printWindow) {
        printWindow.document.write('<p style="font-family: sans-serif; text-align: center; margin-top: 50px; color: #475569;">Generando vista previa de la factura...</p>');
    }
    
    // Generar el PDF y cargar la URL blob en la pestaña
    html2pdf().set(opt).from(invoiceDiv).output('bloburl').then(function(blobUrl) {
        if (printWindow) {
            printWindow.location.href = blobUrl;
        } else {
            window.open(blobUrl, '_blank');
        }
    }).catch(err => {
        if (printWindow) printWindow.close();
        console.error("Error al abrir PDF:", err);
    });
}

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function (m) { return map[m]; });
}
