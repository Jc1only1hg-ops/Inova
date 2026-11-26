document.addEventListener('DOMContentLoaded', function () {
    const reviewsGrid = document.getElementById('reviewsGrid');
    const dotsContainer = document.getElementById('carouselDotsOriginal');
    if (!reviewsGrid) return;

    const reviews = [
        {
            name: 'María González',
            role: 'Estudiante · UCV',
            text: 'Conseguí mi primer proyecto como becaria web y sumé 30 créditos académicos directamente a mi perfil académico.',
            stars: 5
        },
        {
            name: 'Carlos Mendoza',
            role: 'Empresa · TechNova C.A.',
            text: 'Publicar vacantes por créditos académicos nos permitió encontrar estudiantes muy preparados y ver su GitHub al instante.',
            stars: 5
        },
        {
            name: 'Coordinación Académica',
            role: 'Universidad Aliada',
            text: 'Ineova simplifica el seguimiento entre la universidad, el becario y la empresa en un solo lugar.',
            stars: 5
        }
    ];

    reviewsGrid.innerHTML = reviews.map((r) => `
        <div class="review-card-original">
            <div style="color:#f59e0b; font-size:15px; margin-bottom:14px; display:flex; gap:4px;">
                ${'<i class="fas fa-star"></i>'.repeat(r.stars)}
            </div>
            <p style="font-size:15px; line-height:1.7; color:#334155; flex:1; margin-bottom:20px;">"${r.text}"</p>
            <div style="border-top:1px solid #e2ecf8; padding-top:14px;">
                <strong style="display:block; color:#0e1d34; font-size:15px;">${r.name}</strong>
                <span style="color:#64748b; font-size:13px;">${r.role}</span>
            </div>
        </div>
    `).join('');

    if (dotsContainer) dotsContainer.style.display = 'none';
});
