// Datos de las reseñas (6 en total: 2 estudiantes, 2 empresas, 2 universidades)
const reviewsData = [
    {
        rating: 5,
        text: "Gracias a Ineova conseguí mi primera pasantía en una empresa de tecnología mientras estudiaba. El proceso fue súper fácil y rápido. Totalmente recomendado para estudiantes que buscan experiencia real.",
        avatarImg: "https://randomuser.me/api/portraits/women/68.jpg",
        avatarIcon: "fas fa-user-graduate",
        name: "María González",
        role: "Estudiante de Ingeniería - UCV",
        badgeClass: "badge-student",
        badgeIcon: "fas fa-graduation-cap",
        badgeText: "Estudiante"
    },
    {
        rating: 5,
        text: "Encontrar talento joven y comprometido solía ser complicado. Ineova nos ha permitido conectar con estudiantes excelentes que se han convertido en grandes colaboradores. Plataforma eficiente y profesional.",
        avatarImg: "https://randomuser.me/api/portraits/men/32.jpg",
        avatarIcon: "fas fa-building",
        name: "Carlos Méndez",
        role: "Gerente de RRHH - TechSolutions C.A.",
        badgeClass: "badge-company",
        badgeIcon: "fas fa-building",
        badgeText: "Empresa"
    },
    {
        rating: 4.5,
        text: "Ineova se ha convertido en un aliado estratégico para nuestra universidad. Los datos y estadísticas que nos comparten nos ayudan a ajustar nuestros planes de estudio a lo que el mercado realmente necesita.",
        avatarImg: "https://randomuser.me/api/portraits/women/95.jpg",
        avatarIcon: "fas fa-university",
        name: "Dra. Elena Rivas",
        role: "Directora de Vinculación - UNIMET",
        badgeClass: "badge-university",
        badgeIcon: "fas fa-university",
        badgeText: "Universidad"
    },
    {
        rating: 5,
        text: "La mejor plataforma para encontrar pasantías en Venezuela. En menos de un mes ya estaba trabajando en una empresa increíble. El soporte al estudiante es excelente.",
        avatarImg: "https://randomuser.me/api/portraits/men/45.jpg",
        avatarIcon: "fas fa-user-graduate",
        name: "Andrés Rojas",
        role: "Estudiante de Administración - USB",
        badgeClass: "badge-student",
        badgeIcon: "fas fa-graduation-cap",
        badgeText: "Estudiante"
    },
    {
        rating: 5,
        text: "Contratamos a 5 pasantes este año gracias a Ineova. El nivel de los estudiantes superó nuestras expectativas. Sin duda seguiremos usando la plataforma.",
        avatarImg: "https://randomuser.me/api/portraits/women/33.jpg",
        avatarIcon: "fas fa-chart-line",
        name: "Valentina Sánchez",
        role: "Fundadora - Creativa Digital",
        badgeClass: "badge-company",
        badgeIcon: "fas fa-building",
        badgeText: "Empresa"
    },
    {
        rating: 5,
        text: "Como universidad, recomendar Ineova a nuestros estudiantes fue la mejor decisión. La tasa de colocación en pasantías aumentó un 40%.",
        avatarImg: "https://randomuser.me/api/portraits/men/52.jpg",
        avatarIcon: "fas fa-university",
        name: "Dr. Ricardo Morales",
        role: "Decano - UCAB",
        badgeClass: "badge-university",
        badgeIcon: "fas fa-university",
        badgeText: "Universidad"
    }
];

// Convierte un número (4.5) en estrellas HTML
function renderStars(rating) {
    let stars = '';
    const fullStars = Math.floor(rating);
    const hasHalfStar = rating % 1 !== 0;
    
    for (let i = 0; i < fullStars; i++) stars += '<i class="fas fa-star"></i>';
    if (hasHalfStar) stars += '<i class="fas fa-star-half-alt"></i>';
    for (let i = 0; i < 5 - Math.ceil(rating); i++) stars += '<i class="far fa-star"></i>';
    
    return stars;
}

// Genera el HTML de una tarjeta de reseña
function createReviewCard(review, id) {
    // Si hay imagen real, la usamos; si no, usamos el ícono de Font Awesome
    const avatarHtml = review.avatarImg 
        ? `<img src="${review.avatarImg}" alt="${review.name}" style="width:52px;height:52px;border-radius:50%;object-fit:cover;">`
        : `<i class="${review.avatarIcon}"></i>`;
    
    return `
        <div class="review-card-original" id="${id}">
            <i class="fas fa-quote-right quote-icon-original"></i>
            <div class="rating-original">${renderStars(review.rating)}</div>
            <p class="review-text-original">"${review.text}"</p>
            <div class="review-author-original">
                <div class="author-avatar-original">
                    ${avatarHtml}
                </div>
                <div class="author-info-original">
                    <div class="author-name-original">${review.name}</div>
                    <div class="author-role-original">${review.role}</div>
                    <span class="user-badge-original ${review.badgeClass}"><i class="${review.badgeIcon}"></i> ${review.badgeText}</span>
                </div>
            </div>
        </div>
    `;
}

// Variables del carrusel
let currentPairIndex = 0;
let autoTimer = null;
let isTransitioning = false;
const pairsCount = Math.ceil(reviewsData.length / 2); // 3 pares de reseñas

// Obtiene un par de reseñas (izquierda y derecha) según el índice
function getReviewPair(index) {
    const start = index * 2;
    return { left: reviewsData[start], right: reviewsData[start + 1] };
}

// Actualiza las dos tarjetas visibles (con o sin efecto fade)
function updateReviewPair(index, withFade = true) {
    if (isTransitioning) return;
    
    const grid = document.getElementById('reviewsGrid');
    const leftCard = document.getElementById('reviewLeft');
    const rightCard = document.getElementById('reviewRight');
    const pair = getReviewPair(index);
    
    if (withFade && leftCard && rightCard) {
        isTransitioning = true;
        leftCard.classList.add('fade-out');
        rightCard.classList.add('fade-out');
        
        setTimeout(() => {
            grid.innerHTML = createReviewCard(pair.left, 'reviewLeft') + createReviewCard(pair.right, 'reviewRight');
            isTransitioning = false;
            updateActiveDot(index);
        }, 400);
    } else {
        grid.innerHTML = createReviewCard(pair.left, 'reviewLeft') + createReviewCard(pair.right, 'reviewRight');
        updateActiveDot(index);
    }
}

// Marca el dot correspondiente como activo
function updateActiveDot(index) {
    const dots = document.querySelectorAll('.dot-original');
    dots.forEach((dot, i) => {
        if (i === index) {
            dot.classList.add('active');
        } else {
            dot.classList.remove('active');
        }
    });
}

// Avanza al siguiente par de reseñas
function nextPair() {
    if (isTransitioning) return;
    currentPairIndex = (currentPairIndex + 1) % pairsCount;
    updateReviewPair(currentPairIndex, true);
}

// Navega a un par específico (cuando se hace clic en un dot)
function goToPair(index) {
    if (isTransitioning || index === currentPairIndex) return;
    
    // Reinicia el timer automático
    if (autoTimer) clearInterval(autoTimer);
    currentPairIndex = index;
    updateReviewPair(currentPairIndex, true);
    autoTimer = setInterval(nextPair, 7000);
}

// Inicializa el carrusel
function initCarousel() {
    const grid = document.getElementById('reviewsGrid');
    const dotsContainer = document.getElementById('carouselDotsOriginal');
    const firstPair = getReviewPair(0);
    
    // Renderiza el primer par de reseñas
    grid.innerHTML = createReviewCard(firstPair.left, 'reviewLeft') + createReviewCard(firstPair.right, 'reviewRight');
    
    // Crea los dots (uno por cada par de reseñas)
    for (let i = 0; i < pairsCount; i++) {
        const dot = document.createElement('div');
        dot.classList.add('dot-original');
        if (i === 0) dot.classList.add('active');
        dot.addEventListener('click', () => goToPair(i));
        dotsContainer.appendChild(dot);
    }
    
    // Inicia el cambio automático cada 7 segundos
    autoTimer = setInterval(nextPair, 7000);
}

// Espera a que el DOM esté completamente cargado antes de ejecutar
document.addEventListener('DOMContentLoaded', initCarousel);