document.addEventListener('DOMContentLoaded', function() {
    // ==============================================
    // 1. MENÚ DE USUARIO DESPLEGABLE
    // ==============================================
    const userMenu = document.querySelector('.user-menu');
    if (userMenu) {
        userMenu.addEventListener('click', function(e) {
            e.stopPropagation();
            this.classList.toggle('open');
        });
        document.addEventListener('click', function() {
            userMenu.classList.remove('open');
        });
    }

    // ==============================================
    // 2. CARGAR CATEGORÍAS (ÁREAS PROFESIONALES)
    // ==============================================
    const categorias = [
        { nombre: 'Tecnología y Sistemas', icono: 'fa-code', cat: 'tecnologia' },
        { nombre: 'Administración y Finanzas', icono: 'fa-calculator', cat: 'administracion' },
        { nombre: 'Diseño y Creatividad', icono: 'fa-paint-brush', cat: 'diseno' },
        { nombre: 'Ingeniería y Producción', icono: 'fa-cogs', cat: 'ingenieria' },
        { nombre: 'Marketing y Ventas', icono: 'fa-bullhorn', cat: 'marketing' },
        { nombre: 'Recursos Humanos', icono: 'fa-users', cat: 'rrhh' },
        { nombre: 'Idiomas y Traducción', icono: 'fa-language', cat: 'idiomas' },
        { nombre: 'Salud y Bienestar', icono: 'fa-hand-holding-heart', cat: 'salud' },
        { nombre: 'Educación', icono: 'fa-book', cat: 'educacion' },
        { nombre: 'Redacción y Contenido', icono: 'fa-pen', cat: 'redaccion' },
        { nombre: 'Leyes y Legal', icono: 'fa-gavel', cat: 'legal' },
        { nombre: 'Arquitectura', icono: 'fa-building', cat: 'arquitectura' }
    ];

    const categoryGrid = document.getElementById('category-grid-container');
    let activeCategory = null;

    if (categoryGrid) {
        categoryGrid.innerHTML = '';
        categorias.forEach(cat => {
            const card = document.createElement('div');
            card.className = 'category-card';
            card.dataset.cat = cat.cat;
            card.innerHTML = `
                <div class="category-icon">
                    <i class="fas ${cat.icono}"></i>
                </div>
                <span class="category-name">${cat.nombre}</span>
            `;
            categoryGrid.appendChild(card);
        });

        // Eventos para las tarjetas de categoría
        document.querySelectorAll('.category-card').forEach(card => {
            card.addEventListener('click', function() {
                if (this.classList.contains('active')) {
                    this.classList.remove('active');
                    activeCategory = null;
                } else {
                    document.querySelectorAll('.category-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');
                    activeCategory = this.dataset.cat;
                }
                renderFilteredOffers();
            });
        });
    }

    // ==============================================
    // 3. BASE DE DATOS DE OFERTAS (PASANTÍAS / TRABAJOS)
    // ==============================================
    const offers = [
        { 
            id: 1, 
            nombre: 'Pasante de Desarrollo Web (Frontend)', 
            vendedor: 'TechSolutions C.A.', 
            precio: 150.00, 
            categoria: 'tecnologia',
            imagen: 'https://images.unsplash.com/photo-1531403009284-440f080d1e12?q=80&w=600&auto=format&fit=crop', 
            badge: 'Urgente',
            ubicacion: 'Caracas (Chacao)',
            modalidad: 'Híbrido',
            descripcion: 'Apoyo al equipo en el desarrollo de la interfaz de usuario utilizando HTML5, CSS3, JS y React. Se requiere excelente proactividad y nociones de git/GitHub. Duración mínima: 6 meses.'
        },
        { 
            id: 2, 
            nombre: 'Pasantía en Contabilidad y Auditoría', 
            vendedor: 'Banesco Banco Universal', 
            precio: 180.00, 
            categoria: 'administracion',
            imagen: 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?q=80&w=600&auto=format&fit=crop',
            badge: 'Popular',
            ubicacion: 'Caracas (El Rosal)',
            modalidad: 'Presencial',
            descripcion: 'Apoyo en la conciliación de cuentas bancarias, registro de asientos contables en sistema SAP y organización de expedientes de auditoría. Excelente beneficio de almuerzo y transporte.'
        },
        { 
            id: 3, 
            nombre: 'Asistente de Marketing Digital', 
            vendedor: 'Creativa Digital', 
            precio: 120.00, 
            categoria: 'marketing',
            imagen: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?q=80&w=600&auto=format&fit=crop', 
            badge: 'Remoto',
            ubicacion: 'Venezuela (Nacional)',
            modalidad: 'Remoto',
            descripcion: 'Gestión de calendario editorial para redes sociales, redacción de copys publicitarios y apoyo en el análisis de métricas semanales. Conocimiento intermedio de Figma es un plus.'
        },
        { 
            id: 4, 
            nombre: 'Pasante de Ingeniería de Software (Backend)', 
            vendedor: 'Mercantil Banco', 
            precio: 160.00, 
            categoria: 'tecnologia',
            imagen: 'https://images.unsplash.com/photo-1573164713988-8665fc963095?q=80&w=600&auto=format&fit=crop',
            badge: 'Nueva',
            ubicacion: 'Caracas (La Candelaria)',
            modalidad: 'Híbrido',
            descripcion: 'Trabajarás junto a programadores experimentados en la creación de APIs REST robustas usando Java, Spring Boot y bases de datos relacionales PostgreSQL. Oportunidad de contratación permanente.'
        },
        { 
            id: 5, 
            nombre: 'Pasantía en Diseño Gráfico & Branding', 
            vendedor: 'Diseño & Marca C.A.', 
            precio: 110.00, 
            categoria: 'diseno',
            imagen: 'https://images.unsplash.com/photo-1561070791-26c113006238?q=80&w=600&auto=format&fit=crop', 
            badge: 'Flexible',
            ubicacion: 'Caracas (Las Mercedes)',
            modalidad: 'Híbrido',
            descripcion: 'Colaborarás en la ideación gráfica de campañas corporativas, retoque fotográfico digital y maquetación de manuales de identidad visual. Imprescindible portafolio en línea.'
        },
        { 
            id: 6, 
            nombre: 'Pasante de Atracción del Talento (RRHH)', 
            vendedor: 'Empresas Polar', 
            precio: 170.00, 
            categoria: 'rrhh',
            imagen: 'https://images.unsplash.com/photo-1521791136368-1a8b27477f15?q=80&w=600&auto=format&fit=crop',
            badge: 'Destacada',
            ubicacion: 'Caracas (Los Cortijos)',
            modalidad: 'Presencial',
            descripcion: 'Soporte en la publicación de vacantes, primer filtro curricular telefónico y coordinación logística para pruebas técnicas presenciales. Beneficios de alimentación y plan de salud.'
        }
    ];

    // Sincronización del estado de postulaciones y favoritos
    const postuladoIds = new Set();
    const favoritoIds = new Set();

    // ==============================================
    // 4. BÚSQUEDA Y FILTRADO REAL
    // ==============================================
    const searchInput = document.getElementById('search-input');
    let queryText = '';

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            queryText = this.value.trim().toLowerCase();
            renderFilteredOffers();
        });
    }

    function renderFilteredOffers() {
        const grid = document.getElementById('products-grid');
        if (!grid) return;

        // Filtrar
        const filtered = offers.filter(off => {
            const matchesCategory = !activeCategory || off.categoria === activeCategory;
            const matchesSearch = !queryText || 
                off.nombre.toLowerCase().includes(queryText) || 
                off.vendedor.toLowerCase().includes(queryText) ||
                off.ubicacion.toLowerCase().includes(queryText);
            return matchesCategory && matchesSearch;
        });

        grid.innerHTML = '';
        
        if (filtered.length === 0) {
            grid.innerHTML = '<div class="loading-products">No se encontraron ofertas que coincidan con tu búsqueda.</div>';
            return;
        }

        filtered.forEach(prod => {
            const card = document.createElement('div');
            card.className = 'product-card';
            card.dataset.id = prod.id;
            
            const badgeHtml = prod.badge ? `<span class="product-badge">${prod.badge}</span>` : '';
            const isFav = favoritoIds.has(prod.id);
            const isApplied = postuladoIds.has(prod.id);

            card.innerHTML = `
                <div class="product-image">
                    <img src="${prod.imagen}" alt="${prod.nombre}">
                    ${badgeHtml}
                    <button class="wishlist-btn" data-id="${prod.id}">
                        <i class="${isFav ? 'fas' : 'far'} fa-heart" style="${isFav ? 'color: #f87171;' : ''}"></i>
                    </button>
                </div>
                <div class="product-info">
                    <h4>${prod.nombre}</h4>
                    <p class="product-seller"><i class="fas fa-building"></i> ${prod.vendedor}</p>
                    <div class="product-price">
                        <span class="current-price">Apoyo: $${prod.precio.toFixed(2)}/mes</span>
                        <span class="old-price"><i class="fas fa-map-marker-alt"></i> ${prod.ubicacion} (${prod.modalidad})</span>
                    </div>
                    <button class="btn-add-cart ${isApplied ? 'applied' : ''}" data-id="${prod.id}">
                        <i class="fas ${isApplied ? 'fa-check' : 'fa-paper-plane'}"></i> 
                        ${isApplied ? 'Postulado' : 'Postularme'}
                    </button>
                </div>
            `;
            grid.appendChild(card);
        });

        asignarEventosBotones();
    }

    // ==============================================
    // 5. ASIGNACIÓN DE EVENTOS A TARJETAS Y MODALES
    // ==============================================
    const modal = document.getElementById('offer-modal');
    const modalBody = document.getElementById('modal-body-content');
    const closeModalBtn = document.getElementById('close-modal-btn');

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    // Cerrar modal al hacer clic afuera
    window.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    function closeModal() {
        if (modal) {
            modal.classList.remove('open');
        }
    }

    function openModal(prod) {
        if (!modal || !modalBody) return;

        const isFav = favoritoIds.has(prod.id);
        const isApplied = postuladoIds.has(prod.id);

        modalBody.innerHTML = `
            <h3>${prod.nombre}</h3>
            <div class="modal-company"><i class="fas fa-building"></i> ${prod.vendedor}</div>
            
            <div class="modal-meta">
                <span class="modal-tag"><i class="fas fa-map-marker-alt"></i> ${prod.ubicacion}</span>
                <span class="modal-tag"><i class="fas fa-briefcase"></i> ${prod.modalidad}</span>
                <span class="modal-tag"><i class="fas fa-hand-holding-usd"></i> Estipendio: $${prod.precio.toFixed(2)}/mes</span>
            </div>

            <div class="modal-description">
                <h4>Descripción de la Pasantía/Vacante</h4>
                <p>${prod.descripcion}</p>
                <br>
                <h4>Requisitos Principales</h4>
                <ul>
                    <li>Ser estudiante regular activo en UCV, UCAB, USB, UNIMET u otras universidades del país.</li>
                    <li>Tener aprobado más del 70% de la carga académica (preferiblemente).</li>
                    <li>Disponibilidad de horario adecuada según modalidad de trabajo.</li>
                    <li>Ganas de aprender y trabajar colaborativamente en equipo.</li>
                </ul>
            </div>

            <div class="modal-footer">
                <button class="btn-secondary-modal" id="modal-cancel-btn">Cerrar</button>
                <button class="btn-primary-modal btn-apply-modal ${isApplied ? 'applied' : ''}" data-id="${prod.id}" style="${isApplied ? 'background: #22c55e; border-color: #22c55e;' : ''}">
                    <i class="fas ${isApplied ? 'fa-check' : 'fa-paper-plane'}"></i> 
                    ${isApplied ? 'Postulado' : 'Postularme ahora'}
                </button>
            </div>
        `;

        modal.classList.add('open');

        // Eventos del pie del modal
        const cancelBtn = document.getElementById('modal-cancel-btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', closeModal);
        }

        const applyModalBtn = modalBody.querySelector('.btn-apply-modal');
        if (applyModalBtn) {
            applyModalBtn.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                togglePostulacion(id, this);
            });
        }
    }

    function asignarEventosBotones() {
        document.querySelectorAll('.wishlist-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                const icon = this.querySelector('i');
                if (favoritoIds.has(id)) {
                    favoritoIds.delete(id);
                    icon.classList.replace('fas', 'far');
                    icon.style.color = '';
                } else {
                    favoritoIds.add(id);
                    icon.classList.replace('far', 'fas');
                    icon.style.color = '#f87171';
                }
            });
        });

        document.querySelectorAll('.btn-add-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                const id = parseInt(this.dataset.id);
                togglePostulacion(id, this);
            });
        });

        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                const id = parseInt(this.dataset.id);
                const prod = offers.find(o => o.id === id);
                if (prod) {
                    openModal(prod);
                }
            });
        });
    }

    function togglePostulacion(id, buttonElement) {
        const isApplied = postuladoIds.has(id);
        
        if (isApplied) {
            // Cancelar postulación
            postuladoIds.delete(id);
            buttonElement.classList.remove('applied');
            buttonElement.innerHTML = '<i class="fas fa-paper-plane"></i> Postularme';
            if (buttonElement.classList.contains('btn-apply-modal')) {
                buttonElement.style.background = '';
                buttonElement.style.borderColor = '';
                buttonElement.innerHTML = '<i class="fas fa-paper-plane"></i> Postularme ahora';
            }
        } else {
            // Postularse
            postuladoIds.add(id);
            buttonElement.classList.add('applied');
            buttonElement.innerHTML = '<i class="fas fa-check"></i> Postulado';
            if (buttonElement.classList.contains('btn-apply-modal')) {
                buttonElement.style.background = '#22c55e';
                buttonElement.style.borderColor = '#22c55e';
            }
            
            // Efecto feedback visual rápido
            const originalText = buttonElement.innerHTML;
            buttonElement.innerHTML = '<i class="fas fa-check-double"></i> ¡Postulado con éxito!';
            setTimeout(() => {
                buttonElement.innerHTML = originalText;
            }, 1200);
        }

        // Volver a renderizar ofertas en el grid principal para mantener sincronizados los botones
        // Guardando el estado actual de los inputs
        const currentModalOpen = modal.classList.contains('open');
        renderFilteredOffers();
    }

    // Inicializar carga de ofertas
    renderFilteredOffers();
});