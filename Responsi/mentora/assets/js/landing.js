// Mentora - landing.js
// Penanganan interaksi navbar landing dan scroll anchor

document.addEventListener('DOMContentLoaded', () => {
    const nav = document.querySelector('.landing-nav');
    const navCollapse = document.getElementById('landingNavContent');

    // Tambah shadow saat scroll
    if (nav) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 20) {
                nav.classList.add('shadow-sm');
            } else {
                nav.classList.remove('shadow-sm');
            }
        });
    }

    // Auto-tutup menu mobile setelah klik link anchor
    if (navCollapse) {
        const navLinks = navCollapse.querySelectorAll('a.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (navCollapse.classList.contains('show')) {
                    const bsCollapse = bootstrap.Collapse.getInstance(navCollapse);
                    if (bsCollapse) {
                        bsCollapse.hide();
                    }
                }
            });
        });
    }
});
